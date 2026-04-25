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
     * Provisionne un nouveau tenant : User Global -> Base de données -> Schémas SQL.
     */
    public function provisionner(array $data)
    {
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
                'statut' => 'actif',
            ]);

            $adminRole = Role::where('name', 'admin')->first();
            $user->tenants()->attach($tenant->id, [
                'role_id' => $adminRole ? $adminRole->id : 1,
                'is_owner' => true
            ]);

            return ['user' => $user, 'tenant' => $tenant];
        });

        // 2. Opérations DDL (Hors transaction : MySQL ne supporte pas le rollback sur CREATE DATABASE/TABLE)
        DB::connection('central')->statement("CREATE DATABASE `{$provisioning['tenant']->database_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        // 3. Exécution des schémas SQL (002 à 008) sur la nouvelle base
        $this->runTenantMigrations($provisioning['tenant']->database_name);

        // 4. Insertion des données d'entreprise
        DB::connection('tenant')->table('entreprise')->insert([
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
        ]);

        return $provisioning;
    }

    /**
     * Bascule sur la nouvelle connexion et exécute les fichiers SQL.
     */
    protected function runTenantMigrations(string $dbName)
    {
        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');

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