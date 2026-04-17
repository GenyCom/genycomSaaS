<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Paramétrage de l'Entreprise</h2>
        <p class="text-sm text-muted">Personnalisez les réglages spécifiques à votre SaaS</p>
      </div>
      <button class="btn btn-primary">Enregistrer les modifications</button>
    </div>

    <!-- Interface par onglets -->
    <div style="display: flex; gap: 1rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
      <div class="tab active">Informations Générales</div>
      <div class="tab">Taxes (TVA) & Devises</div>
      <div class="tab">Entrepôts & Stock</div>
      <div class="tab">Règlements & Livraisons</div>
      <div class="tab">Familles de Produits</div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <!-- Bloc Configuration TVA -->
      <div class="card">
        <div class="flex items-center justify-between mb-2">
          <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--accent);">Taux de TVA Personnalisés</h3>
          <button class="btn btn-secondary btn-sm">+ Ajouter un Taux</button>
        </div>
        <p class="text-sm text-muted mb-2">Configurez les taux applicables pour vos devis et facturations.</p>
        <table class="data-table">
          <thead>
            <tr>
              <th>Libellé</th>
              <th>Taux (%)</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>TVA Normale</td>
              <td>20%</td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
            <tr>
              <td>TVA Réduite</td>
              <td>14%</td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
            <tr>
              <td>TVA Super Réduite</td>
              <td>7%</td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
            <tr>
              <td>Exonéré</td>
              <td>0%</td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
          </tbody>
        </table>
      </div>

       <!-- Bloc Famille de produits -->
      <div class="card">
        <div class="flex items-center justify-between mb-2">
          <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--accent);">Familles de Produits</h3>
          <button class="btn btn-secondary btn-sm">+ Ajouter une Famille</button>
        </div>
        <p class="text-sm text-muted mb-2">La hiérarchie commerciale de votre catalogue (Familles / Sous-Familles).</p>
        <table class="data-table">
          <thead>
            <tr>
              <th>Code Famille</th>
              <th>Libellé</th>
              <th>Parent</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="font-mono">INFO</td>
              <td>Matériel Informatique</td>
              <td class="text-muted">-</td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
            <tr>
              <td class="font-mono">ORD</td>
              <td>Ordinateurs Portables</td>
              <td><span class="badge badge-info">INFO</span></td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
            <tr>
              <td class="font-mono">SERV</td>
              <td>Prestations de Services</td>
              <td class="text-muted">-</td>
              <td><a href="#" class="text-danger">Supprimer</a></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bloc Modes de règlement -->
      <div class="card">
        <div class="flex items-center justify-between mb-2">
          <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--accent);">Modes de Règlements</h3>
          <button class="btn btn-secondary btn-sm">+ Ajouter</button>
        </div>
        <div class="flex gap-2" style="flex-wrap: wrap;">
          <span class="badge badge-default" style="font-size:0.85rem; padding: 0.4rem 0.8rem;">Virement Bancaire (Par défaut)</span>
          <span class="badge badge-default" style="font-size:0.85rem; padding: 0.4rem 0.8rem;">Chèque</span>
          <span class="badge badge-default" style="font-size:0.85rem; padding: 0.4rem 0.8rem;">Espèces</span>
          <span class="badge badge-default" style="font-size:0.85rem; padding: 0.4rem 0.8rem;">Effet / Traite</span>
        </div>
      </div>

      <!-- Bloc Entrepôts -->
      <div class="card">
        <div class="flex items-center justify-between mb-2">
          <h3 style="font-size: 0.95rem; font-weight: 600; color: var(--accent);">Lieux de Stockage (Entrepôts)</h3>
          <button class="btn btn-secondary btn-sm">+ Nouvel Entrepôt</button>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Désignation de l'entrepôt</th>
              <th>Principal</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="font-mono">PRINCIPAL</td>
              <td>Entrepôt Principal (Siège)</td>
              <td>Oui</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>

<style scoped>
.tab {
  padding: 0.75rem 1.25rem;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  border-bottom: 2px solid transparent;
}
.tab.active {
  color: var(--accent);
  border-bottom: 2px solid var(--accent);
}
.tab:hover:not(.active) {
  color: var(--text-primary);
}
</style>
