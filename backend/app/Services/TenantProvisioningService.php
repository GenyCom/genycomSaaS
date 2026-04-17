<?php
namespace App\Services;

use App\Models\{Tenant, User, Role, Entreprise, Devise, TauxTva, 
    ModeReglement, ModeLivraison, ConditionReglement, EtatDocument,
    TypeClient, CategorieDepense, Entrepot};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TenantProvisioningService
{
    public function provisionner(array $data): array
    {
        // 1. Création du Tenant + DB
        $dbName = $data['database_name'] ?? ('genycom_client_' . str_replace('-', '_', substr(Str::uuid(), 0, 8)));
        
        DB::connection('central')->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        $tenant = Tenant::create([
            'nom'           => $data['nom_entreprise'],
            'database_name' => $dbName,
            'domain'        => Str::slug($data['nom_entreprise']) . '.genycom.ma',
            'statut'        => 'actif',
        ]);

        // 2. Gestion de l'Utilisateur Central
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'nom'      => $data['nom'],
                'prenom'   => $data['prenom'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
                'is_superadmin' => false,
            ]
        );

        // 3. Liaison Utilisateur <-> Tenant (avec rôle Admin)
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrateur', 'is_system' => true]);
            Role::create(['name' => 'commercial', 'description' => 'Commercial', 'is_system' => true]);
            Role::create(['name' => 'comptable', 'description' => 'Comptable', 'is_system' => true]);
            Role::create(['name' => 'magasinier', 'description' => 'Magasinier', 'is_system' => true]);
        }
        
        $tenant->users()->attach($user->id, ['role_id' => $adminRole->id, 'is_owner' => true]);

        // 4. Migration de l'architecture SaaS
        $this->migrerSchemaSaaS($dbName);

        // 5. Basculer la connexion sur le nouveau tenant pour provisionner la data
        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');

        // 6. Créer les données d'entreprise
        Entreprise::create([
            'raison_sociale' => $data['nom_entreprise'],
            'adresse'        => $data['adresse'] ?? '',
            'ville'          => $data['ville'] ?? '',
            'pays'           => $data['pays'] ?? 'Maroc',
        ]);

        $this->creerDevise();
        $this->creerTauxTva();
        $this->creerModesReglement();
        $this->creerModesLivraison();
        $this->creerConditionsReglement();
        $this->creerEtatsDocument();
        $this->creerTypesClient();
        $this->creerCategoriesDepense();
        $this->creerEntrepotDefaut();

        return [
            'tenant' => $tenant,
            'user'   => $user,
        ];
    }

    private function migrerSchemaSaaS(string $dbName): void
    {
        $schemaPath = database_path('schema');
        $files = collect(File::files($schemaPath))
            ->filter(fn($file) => preg_match('/^00[2-7].*\.sql$/', $file->getFilename()))
            ->sortBy(fn($file) => $file->getFilename());

        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');

        foreach ($files as $file) {
            $sql = File::get($file->getPathname());
            DB::connection('tenant')->unprepared($sql);
        }
    }

    private function creerDevise(): void
    {
        $d = config('genycom.devise_defaut', ['nom' => 'Dirham', 'code_iso' => 'MAD', 'symbole' => 'DH']);
        Devise::create(['nom' => $d['nom'], 'code_iso' => $d['code_iso'], 'symbole' => $d['symbole'], 'is_principale' => true]);
    }

    private function creerTauxTva(): void
    {
        TauxTva::insert([
            ['taux' => 20.00, 'libelle' => 'TVA Normale (20%)'],
            ['taux' => 14.00, 'libelle' => 'TVA (14%)'],
            ['taux' => 10.00, 'libelle' => 'TVA (10%)'],
            ['taux' => 7.00,  'libelle' => 'TVA (7%)'],
            ['taux' => 0.00,  'libelle' => 'Exonéré (0%)'],
        ]);
    }

    private function creerModesReglement(): void
    {
        $modes = ['Virement bancaire', 'Chèque', 'Espèces', 'Carte bancaire', 'Effet / Traite'];
        foreach ($modes as $lib) ModeReglement::create(['libelle' => $lib]);
    }

    private function creerModesLivraison(): void
    {
        $modes = ['Livraison Standard', 'Express', 'Retrait sur place'];
        foreach ($modes as $lib) ModeLivraison::create(['libelle' => $lib]);
    }

    private function creerConditionsReglement(): void
    {
        ConditionReglement::insert([
            ['libelle' => 'Paiement comptant', 'nombre_jours' => 0],
            ['libelle' => '30 jours fin de mois', 'nombre_jours' => 30],
            ['libelle' => '60 jours net', 'nombre_jours' => 60],
        ]);
    }

    private function creerEtatsDocument(): void
    {
        // Simplification for generating initial data
        $etats = [
            ['type_document' => 'devis', 'code' => 'brouillon', 'libelle' => 'Brouillon', 'couleur' => 'secondary', 'ordre' => 1],
            ['type_document' => 'devis', 'code' => 'envoye', 'libelle' => 'Envoyé', 'couleur' => 'primary', 'ordre' => 2],
            ['type_document' => 'devis', 'code' => 'accepte', 'libelle' => 'Accepté', 'couleur' => 'success', 'ordre' => 3],
        ];
        EtatDocument::insert($etats);
    }

    private function creerTypesClient(): void
    {
        TypeClient::insert([
            ['libelle' => 'Normal', 'detail' => 'Client standard', 'vip' => false],
            ['libelle' => 'VIP', 'detail' => 'Client privilégié', 'vip' => true]
        ]);
    }

    private function creerCategoriesDepense(): void
    {
        $parent = CategorieDepense::create(['libelle' => 'Frais Généraux']);
        CategorieDepense::create(['libelle' => 'Loyer', 'parent_id' => $parent->id]);
        CategorieDepense::create(['libelle' => 'Internet & Tel', 'parent_id' => $parent->id]);
    }

    private function creerEntrepotDefaut(): void
    {
        Entrepot::create(['code' => 'PRINCIPAL', 'nom' => 'Entrepôt Principal', 'is_default' => true]);
    }
}
