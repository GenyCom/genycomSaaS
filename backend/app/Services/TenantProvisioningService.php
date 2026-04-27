<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class TenantProvisioningService
{
    /**
     * Vérifie si une base de données existe.
     */
    public function databaseExists(string $dbName): bool
    {
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        $exists = DB::connection('central')->select($query, [$dbName]);
        return count($exists) > 0;
    }

    /**
     * Provisionne un nouveau tenant : User Global -> Base de données -> Schémas SQL.
     */
    public function provisionner(array $data, bool $forceExisting = false)
    {
        $dbName = $data['database_name'];
        $dbExists = $this->databaseExists($dbName);

        // --- ADAPTATION HOSTINGER ---
        // On exige que la base de données soit déjà créée via le hPanel.
        // Si elle n'existe pas, on bloque le processus.
        if (!$dbExists) {
            throw new \Exception("La base de données '{$dbName}' est introuvable. Veuillez la créer depuis le hPanel Hostinger avant de lancer le provisionning.");
        }

        // 1. Création des enregistrements en base centrale (Transactionnelle)
        $provisioning = DB::connection('central')->transaction(function () use ($data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                ]
            );

            $tenant = Tenant::create([
                'nom' => $data['nom_entreprise'],
                'database_name' => $data['database_name'],
                'db_username'   => $data['db_username'] ?? null,
                'db_password'   => $data['db_password'] ?? null,
                'statut' => 'actif',
            ]);

            $adminRole = Role::where('name', 'admin')->first();
            $user->tenants()->attach($tenant->id, [
                'role_id' => $adminRole ? $adminRole->id : 1,
                'is_owner' => true
            ]);

            return ['user' => $user, 'tenant' => $tenant];
        });

        // 2. Opérations DDL
        // SUPPRIMÉ : Hostinger bloque le CREATE DATABASE via PHP.
        // La base est déjà créée et vérifiée plus haut.

        // 3. Exécution des schémas SQL (002 à 008) sur la nouvelle base
        $this->runTenantMigrations($provisioning['tenant']);

        // 4. Insertion des données d'entreprise
        $this->setTenantConnection($provisioning['tenant']);
        DB::connection('tenant')->table('entreprise')->updateOrInsert(
            ['email' => $data['email']],
            [
                'raison_sociale'  => $data['nom_entreprise'],
                'forme_juridique' => $data['forme_juridique'] ?? null,
                'adresse'         => $data['adresse'],
                'ville'           => $data['ville'],
                'telephone'       => $data['telephone'] ?? null,
                'email'           => $data['email'],
                'site_web'        => $data['site_web'] ?? null,
                'logo_path'       => $data['logo_url'] ?? null,
                'pays'            => 'Maroc',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );

        return $provisioning;
    }

    protected function setTenantConnection(Tenant $tenant)
    {
        Config::set('database.connections.tenant.database', $tenant->database_name);
        if ($tenant->db_username) {
            Config::set('database.connections.tenant.username', $tenant->db_username);
        }
        if ($tenant->db_password) {
            Config::set('database.connections.tenant.password', $tenant->db_password);
        }
        DB::purge('tenant');
    }

    /**
     * Bascule sur la nouvelle connexion et exécute les fichiers SQL.
     */
    protected function runTenantMigrations(Tenant $tenant)
    {
        $this->setTenantConnection($tenant);

        $schemaPath = database_path('schema');
        $files = collect(File::files($schemaPath))
            ->filter(function($f) {
                $filename = $f->getFilename();
                // On prend les fichiers qui commencent par 3 chiffres (002-999) et finissent par .sql
                // On exclut explicitement le 001 qui est le schéma central
                return preg_match('/^\d{3}_.*\.sql$/', $filename) && !str_starts_with($filename, '001');
            })
            ->sortBy(fn($f) => $f->getFilename());

        foreach ($files as $file) {
            DB::connection('tenant')->unprepared(File::get($file->getPathname()));
        }
    }
}