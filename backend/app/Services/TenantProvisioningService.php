<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TenantProvisioningService
{
    // ─────────────────────────────────────────────────────────────────────────
    // CANAL DE LOG DÉDIÉ AU PROVISIONING
    // Tous les messages passent par Log::channel('provisioning') si configuré,
    // sinon par le canal par défaut (storage/logs/laravel.log).
    // Pour activer un canal dédié, ajouter dans config/logging.php :
    //   'provisioning' => ['driver' => 'single', 'path' => storage_path('logs/provisioning.log'), 'level' => 'debug']
    // ─────────────────────────────────────────────────────────────────────────
    private function log(string $level, string $message, array $context = []): void
    {
        $prefix = '[TENANT-PROVISIONING]';
        $fullMessage = "{$prefix} {$message}";

        try {
            Log::channel('provisioning')->{$level}($fullMessage, $context);
        } catch (\Throwable $e) {
            // Fallback : canal par défaut si 'provisioning' n'est pas configuré
            Log::$level($fullMessage, $context);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ÉTAPE 0 — VÉRIFICATION DE L'EXISTENCE DE LA BASE DE DONNÉES
    //
    // CORRECTION DU BUG ORIGINAL :
    //   ❌ Ancien code : `USE \`{$dbName}\`` via la connexion 'central'
    //      → Problème 1 : l'utilisateur MySQL 'central' n'a pas les privilèges
    //        sur les bases créées séparément dans hPanel Hostinger.
    //      → Problème 2 : le catch lançait une Exception au lieu de
    //        retourner false, ce qui cassait tout le flux.
    //
    //   ✅ Nouveau code :
    //      Stratégie 1 : INFORMATION_SCHEMA (sans changer de base active)
    //      Stratégie 2 : connexion directe avec les credentials propres au tenant
    //      → On essaie dans l'ordre, on loggue chaque tentative.
    // ─────────────────────────────────────────────────────────────────────────
    public function databaseExists(string $dbName, array $tenantCredentials = []): bool
    {
        $this->log('info', "🔍 Vérification de l'existence de la base de données.", [
            'database' => $dbName,
        ]);

        // ── Stratégie 1 : interroger INFORMATION_SCHEMA via la connexion centrale ──
        // Fonctionne si l'utilisateur 'central' a les droits SELECT sur INFORMATION_SCHEMA.
        // Sur Hostinger shared, cela couvre uniquement les bases du même compte MySQL.
        try {
            $this->log('debug', "📡 Tentative via INFORMATION_SCHEMA (connexion centrale).", [
                'database' => $dbName,
                'connection' => 'central',
            ]);

            $result = DB::connection('central')->select(
                "SELECT SCHEMA_NAME
                 FROM INFORMATION_SCHEMA.SCHEMATA
                 WHERE SCHEMA_NAME = ?",
                [$dbName]
            );

            if (count($result) > 0) {
                $this->log('info', "✅ Base trouvée via INFORMATION_SCHEMA.", [
                    'database' => $dbName,
                ]);
                return true;
            }

            $this->log('warning', "⚠️  INFORMATION_SCHEMA ne retourne aucun résultat pour cette base.", [
                'database' => $dbName,
                'hint'     => "Soit la base n'existe pas, soit l'utilisateur central n'a pas les droits de visibilité.",
            ]);

        } catch (\Throwable $e) {
            $this->log('warning', "⚠️  Échec de la stratégie INFORMATION_SCHEMA.", [
                'database' => $dbName,
                'error'    => $e->getMessage(),
                'hint'     => "L'utilisateur MySQL de la connexion 'central' ne peut pas interroger INFORMATION_SCHEMA. On passe à la stratégie 2.",
            ]);
        }

        // ── Stratégie 2 : connexion directe avec les credentials propres au tenant ──
        // Sur Hostinger, chaque base créée dans hPanel a son propre user/password MySQL.
        // Il faut absolument passer ces credentials dans $tenantCredentials.
        if (!empty($tenantCredentials['db_username']) && !empty($tenantCredentials['db_password'])) {
            $this->log('debug', "📡 Tentative de connexion directe avec les credentials tenant.", [
                'database' => $dbName,
                'username' => $tenantCredentials['db_username'],
            ]);

            $tempConnectionName = 'tenant_check_' . uniqid();

            try {
                Config::set("database.connections.{$tempConnectionName}", [
                    'driver'      => config('database.connections.central.driver', 'mysql'),
                    'host'        => config('database.connections.central.host', '127.0.0.1'),
                    'port'        => config('database.connections.central.port', '3306'),
                    'database'    => $dbName,
                    'username'    => $tenantCredentials['db_username'],
                    'password'    => $tenantCredentials['db_password'],
                    'charset'     => 'utf8mb4',
                    'collation'   => 'utf8mb4_unicode_ci',
                    'strict'      => false,
                ]);

                DB::connection($tempConnectionName)->getPdo();

                $this->log('info', "✅ Connexion directe réussie. La base existe et est accessible.", [
                    'database' => $dbName,
                    'username' => $tenantCredentials['db_username'],
                ]);

                return true;

            } catch (\Throwable $e) {
                $this->log('error', "❌ Connexion directe échouée.", [
                    'database' => $dbName,
                    'username' => $tenantCredentials['db_username'],
                    'error'    => $e->getMessage(),
                    'hint'     => "Vérifiez que la base '{$dbName}' existe dans hPanel et que les credentials sont corrects.",
                ]);
            } finally {
                // Toujours purger la connexion temporaire pour éviter les fuites
                try {
                    DB::purge($tempConnectionName);
                } catch (\Throwable $ignored) {}
            }
        } else {
            $this->log('warning', "⚠️  Aucun credentials tenant fournis. Stratégie 2 ignorée.", [
                'database' => $dbName,
                'hint'     => "Passez db_username et db_password dans les données de provisioning pour permettre la vérification directe.",
            ]);
        }

        // Les deux stratégies ont échoué → la base est introuvable ou inaccessible
        $this->log('error', "❌ Impossible de confirmer l'existence de la base de données.", [
            'database' => $dbName,
            'strategies_tried' => ['INFORMATION_SCHEMA', 'connexion directe'],
            'action_requise'   => "Créez la base depuis le hPanel Hostinger, puis assurez-vous de fournir les credentials corrects (db_username / db_password).",
        ]);

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POINT D'ENTRÉE PRINCIPAL — PROVISIONING D'UN NOUVEAU TENANT
    // ─────────────────────────────────────────────────────────────────────────
    public function provisionner(array $data, bool $forceExisting = false)
    {
        $dbName = $data['database_name'];

        $this->log('info', "🚀 Démarrage du provisioning.", [
            'database'      => $dbName,
            'email'         => $data['email'],
            'nom_entreprise' => $data['nom_entreprise'],
        ]);

        // ── Étape 0 : Vérification de la base ────────────────────────────────
        $this->log('info', "📋 ÉTAPE 0 — Vérification de la base de données '{$dbName}'.");

        $tenantCredentials = [
            'db_username' => $data['db_username'] ?? null,
            'db_password' => $data['db_password'] ?? null,
        ];

        $dbExists = $this->databaseExists($dbName, $tenantCredentials);

        if (!$dbExists) {
            $message = "La base de données '{$dbName}' est introuvable ou inaccessible. "
                . "Veuillez : 1) La créer depuis le hPanel Hostinger. "
                . "2) Créer un utilisateur MySQL dédié et l'assigner à cette base. "
                . "3) Fournir db_username et db_password corrects dans le formulaire de provisioning.";

            $this->log('critical', "🛑 Provisioning annulé — base de données inaccessible.", [
                'database' => $dbName,
                'message'  => $message,
            ]);

            throw new \Exception($message);
        }

        $this->log('info', "✅ ÉTAPE 0 — Base de données confirmée.", ['database' => $dbName]);

        // ── Étape 1 : Enregistrements en base centrale (transactionnel) ───────
        $this->log('info', "📋 ÉTAPE 1 — Création des enregistrements en base centrale.");

        try {
            $provisioning = DB::connection('central')->transaction(function () use ($data) {

                // Création ou récupération de l'utilisateur global
                $this->log('debug', "👤 firstOrCreate User.", ['email' => $data['email']]);

                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'nom'       => $data['nom'],
                        'prenom'    => $data['prenom'],
                        'password'  => Hash::make($data['password']),
                        'is_active' => true,
                    ]
                );

                $userStatus = $user->wasRecentlyCreated ? 'créé' : 'existant (récupéré)';
                $this->log('info', "✅ User global {$userStatus}.", [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'status'  => $userStatus,
                ]);

                // Création du tenant
                $this->log('debug', "🏢 Création du Tenant.", [
                    'nom_entreprise' => $data['nom_entreprise'],
                    'database'       => $data['database_name'],
                ]);

                $tenant = Tenant::create([
                    'nom'           => $data['nom_entreprise'],
                    'database_name' => $data['database_name'],
                    'db_username'   => $data['db_username'] ?? null,
                    'db_password'   => $data['db_password'] ?? null,
                    'statut'        => 'actif',
                ]);

                $this->log('info', "✅ Tenant créé.", [
                    'tenant_id'    => $tenant->id,
                    'nom'          => $tenant->nom,
                    'database'     => $tenant->database_name,
                    'has_own_user' => !empty($tenant->db_username),
                ]);

                // Assignation du rôle admin
                $this->log('debug', "🔑 Recherche du rôle 'admin'.");

                $adminRole = Role::where('name', 'admin')->first();

                if (!$adminRole) {
                    $this->log('warning', "⚠️  Rôle 'admin' introuvable en base. Utilisation du role_id=1 par défaut.");
                } else {
                    $this->log('debug', "✅ Rôle 'admin' trouvé.", ['role_id' => $adminRole->id]);
                }

                $user->tenants()->attach($tenant->id, [
                    'role_id'  => $adminRole ? $adminRole->id : 1,
                    'is_owner' => true,
                ]);

                $this->log('info', "✅ User attaché au Tenant avec le rôle admin (owner=true).", [
                    'user_id'   => $user->id,
                    'tenant_id' => $tenant->id,
                    'role_id'   => $adminRole ? $adminRole->id : 1,
                ]);

                return ['user' => $user, 'tenant' => $tenant];
            });

            $this->log('info', "✅ ÉTAPE 1 — Transaction centrale réussie.");

        } catch (\Throwable $e) {
            $this->log('critical', "❌ ÉTAPE 1 — Échec de la transaction centrale. Rollback effectué.", [
                'database' => $dbName,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            throw new \Exception("Erreur lors de la création des enregistrements centraux : " . $e->getMessage(), 0, $e);
        }

        // ── Étape 2 : Exécution des schémas SQL sur la base tenant ──────────
        $this->log('info', "📋 ÉTAPE 2 — Exécution des migrations SQL sur la base tenant '{$dbName}'.");

        try {
            $this->runTenantMigrations($provisioning['tenant']);
            $this->log('info', "✅ ÉTAPE 2 — Migrations SQL terminées avec succès.");
        } catch (\Throwable $e) {
            $this->log('critical', "❌ ÉTAPE 2 — Échec lors de l'exécution des fichiers SQL.", [
                'database' => $dbName,
                'error'    => $e->getMessage(),
                'hint'     => "Les enregistrements centraux ont été créés (étape 1). Vous pouvez relancer uniquement les migrations ou nettoyer manuellement.",
            ]);
            throw new \Exception("Erreur lors des migrations tenant : " . $e->getMessage(), 0, $e);
        }

        // ── Étape 3 : Insertion des données d'entreprise ─────────────────────
        $this->log('info', "📋 ÉTAPE 3 — Insertion des données d'entreprise dans la table 'entreprise'.");

        try {
            $this->setTenantConnection($provisioning['tenant']);

            $entrepriseData = [
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
            ];

            $this->log('debug', "📝 Données entreprise à insérer/mettre à jour.", $entrepriseData);

            DB::connection('tenant')->table('entreprise')->updateOrInsert(
                ['email' => $data['email']],
                $entrepriseData
            );

            $this->log('info', "✅ ÉTAPE 3 — Données d'entreprise insérées/mises à jour.", [
                'email'          => $data['email'],
                'raison_sociale' => $data['nom_entreprise'],
            ]);

        } catch (\Throwable $e) {
            $this->log('critical', "❌ ÉTAPE 3 — Échec de l'insertion des données d'entreprise.", [
                'database' => $dbName,
                'error'    => $e->getMessage(),
                'hint'     => "Les étapes 1 et 2 ont réussi. La table 'entreprise' est peut-être absente ou les colonnes ont changé.",
            ]);
            throw new \Exception("Erreur lors de l'insertion des données entreprise : " . $e->getMessage(), 0, $e);
        }

        // ── Fin du provisioning ───────────────────────────────────────────────
        $this->log('info', "🎉 Provisioning terminé avec succès !", [
            'tenant_id'      => $provisioning['tenant']->id,
            'user_id'        => $provisioning['user']->id,
            'database'       => $dbName,
            'nom_entreprise' => $data['nom_entreprise'],
            'email'          => $data['email'],
        ]);

        return $provisioning;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIGURATION DE LA CONNEXION TENANT
    //
    // Modifie dynamiquement la connexion 'tenant' pour pointer
    // vers la base du tenant en cours. Purge la connexion pour
    // forcer Laravel à recréer le PDO avec les nouveaux paramètres.
    // ─────────────────────────────────────────────────────────────────────────
    protected function setTenantConnection(Tenant $tenant): void
    {
        $this->log('debug', "🔧 Configuration de la connexion 'tenant'.", [
            'database'     => $tenant->database_name,
            'has_own_user' => !empty($tenant->db_username),
        ]);

        Config::set('database.connections.tenant.database', $tenant->database_name);

        if ($tenant->db_username) {
            Config::set('database.connections.tenant.username', $tenant->db_username);
            $this->log('debug', "🔧 Username tenant personnalisé appliqué.", [
                'username' => $tenant->db_username,
            ]);
        }

        if ($tenant->db_password) {
            Config::set('database.connections.tenant.password', $tenant->db_password);
            $this->log('debug', "🔧 Password tenant personnalisé appliqué (valeur masquée).");
        }

        DB::purge('tenant');

        $this->log('debug', "✅ Connexion 'tenant' purgée et prête à être réinitialisée.", [
            'database' => $tenant->database_name,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXÉCUTION DES FICHIERS SQL DE SCHÉMA SUR LA BASE TENANT
    //
    // Convention des fichiers dans database/schema/ :
    //   001_central.sql  → IGNORÉ (schéma de la base centrale)
    //   002_xxx.sql      → EXÉCUTÉ sur la base tenant
    //   003_xxx.sql      → EXÉCUTÉ sur la base tenant
    //   ...
    // ─────────────────────────────────────────────────────────────────────────
    protected function runTenantMigrations(Tenant $tenant): void
    {
        $this->setTenantConnection($tenant);

        $schemaPath = database_path('schema');

        $this->log('info', "📂 Lecture des fichiers SQL dans : {$schemaPath}");

        if (!File::isDirectory($schemaPath)) {
            $this->log('critical', "❌ Dossier 'schema' introuvable.", [
                'path' => $schemaPath,
                'hint' => "Créez le dossier database/schema/ et placez-y vos fichiers SQL numérotés (002_xxx.sql, etc.).",
            ]);
            throw new \Exception("Le dossier de schémas SQL est introuvable : {$schemaPath}");
        }

        $files = collect(File::files($schemaPath))
            ->filter(function ($f) {
                $filename = $f->getFilename();
                // ✅ On inclut uniquement les fichiers xxx_*.sql (3 chiffres)
                // ✅ On exclut le 001 qui appartient au schéma central
                return preg_match('/^\d{3}_.*\.sql$/', $filename)
                    && !str_starts_with($filename, '001');
            })
            ->sortBy(fn($f) => $f->getFilename())
            ->values();

        $totalFiles = $files->count();

        if ($totalFiles === 0) {
            $this->log('warning', "⚠️  Aucun fichier SQL tenant trouvé dans le dossier schema.", [
                'path'    => $schemaPath,
                'hint'    => "Assurez-vous que vos fichiers sont nommés 002_xxx.sql, 003_xxx.sql, etc.",
            ]);
            return;
        }

        $this->log('info', "📋 {$totalFiles} fichier(s) SQL tenant à exécuter.", [
            'fichiers' => $files->map(fn($f) => $f->getFilename())->toArray(),
        ]);

        foreach ($files as $index => $file) {
            $filename = $file->getFilename();
            $current  = $index + 1;

            $this->log('info', "⚙️  [{$current}/{$totalFiles}] Exécution : {$filename}");

            try {
                $sql = File::get($file->getPathname());

                if (empty(trim($sql))) {
                    $this->log('warning', "⚠️  [{$current}/{$totalFiles}] Fichier vide, ignoré.", [
                        'fichier' => $filename,
                    ]);
                    continue;
                }

                DB::connection('tenant')->unprepared($sql);

                $this->log('info', "✅ [{$current}/{$totalFiles}] {$filename} exécuté avec succès.");

            } catch (\Throwable $e) {
                $this->log('critical', "❌ [{$current}/{$totalFiles}] Échec de l'exécution de {$filename}.", [
                    'fichier'  => $filename,
                    'database' => $tenant->database_name,
                    'error'    => $e->getMessage(),
                    'hint'     => "Vérifiez la syntaxe SQL du fichier. Les fichiers précédents ont déjà été exécutés.",
                ]);
                throw new \Exception("Erreur lors de l'exécution du fichier SQL '{$filename}' : " . $e->getMessage(), 0, $e);
            }
        }

        $this->log('info', "✅ Toutes les migrations SQL tenant ont été exécutées.", [
            'total'    => $totalFiles,
            'database' => $tenant->database_name,
        ]);
    }
}