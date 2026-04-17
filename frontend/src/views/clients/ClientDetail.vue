<template>
  <div class="animate-fade-in">
    <div class="flex items-center gap-2 mb-3">
      <router-link to="/clients" class="btn btn-secondary btn-sm">← Retour</router-link>
      <h2 style="font-size:1.1rem; font-weight:600;">Détail Client : {{ client.societe }}</h2>
    </div>

    <!-- KPI Client -->
    <div class="kpi-grid mb-3">
      <div class="kpi-card">
        <div class="kpi-label">Chiffre d'Affaires Brut</div>
        <div class="kpi-value">124 500,00 DH</div>
        <div class="kpi-sub">Depuis la création</div>
        <div class="kpi-icon info"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
      </div>
      <div class="kpi-card danger">
        <div class="kpi-label">Reste à Encaisser (Dû)</div>
        <div class="kpi-value text-danger">45 000,00 DH</div>
        <div class="kpi-sub">3 Factures en retard</div>
        <div class="kpi-icon danger"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
      </div>
      <div class="kpi-card success">
        <div class="kpi-label">Devis Acceptés</div>
        <div class="kpi-value">14</div>
        <div class="kpi-sub">Taux transfo : 85%</div>
        <div class="kpi-icon success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
      </div>
    </div>

    <!-- Informations & Contact -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
      <div class="card">
         <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Profil Commercial</h3>
         <div style="display:grid; grid-template-columns: 120px 1fr; gap:0.5rem; line-height:1.6;">
            <div class="text-muted">Code Client</div><div class="font-mono">{{ client.code_client }}</div>
            <div class="text-muted">Type</div><div><span class="badge badge-warning">VIP</span></div>
            <div class="text-muted">ICE</div><div>{{ client.ice || 'Non renseigné' }}</div>
            <div class="text-muted">Condition RC</div><div>30 jours fin du mois</div>
         </div>
      </div>
      <div class="card">
         <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Coordonnées</h3>
         <div style="display:grid; grid-template-columns: 80px 1fr; gap:0.5rem; line-height:1.6;">
            <div class="text-muted">Contact</div><div>M. Karim Bennani</div>
            <div class="text-muted">Téléphone</div><div>{{ client.telephone }}</div>
            <div class="text-muted">Email</div><div><a href="#" style="color:var(--info);">{{ client.email }}</a></div>
            <div class="text-muted">Adresse</div><div>Quartier Sidi Maarouf, Casablanca</div>
         </div>
         <div class="mt-3 flex gap-2">
           <button class="btn btn-primary btn-sm">Editer le Client</button>
         </div>
       </div>
    </div>

    <!-- Historique des actions -->
    <div class="card">
       <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Documents Récents</h3>
       <table class="data-table">
          <thead>
            <tr>
              <th>Réf. Document</th>
              <th>Type</th>
              <th>Date</th>
              <th style="text-align:right">Montant TTC</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="font-mono">FAC-24-0012</td>
              <td>Facture de Vente</td>
              <td>15 Avr 2026</td>
              <td style="text-align:right">45 000,00 DH</td>
              <td><span class="badge badge-danger">Impayée</span></td>
            </tr>
            <tr>
              <td class="font-mono">DEV-24-0089</td>
              <td>Devis</td>
              <td>10 Avr 2026</td>
              <td style="text-align:right">10 200,00 DH</td>
              <td><span class="badge badge-success">Accepté</span></td>
            </tr>
          </tbody>
        </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({ id: [String, Number] })

// Bouchon de donnée (Demo)
const client = ref({
  code_client: 'CLI-001',
  societe: 'TechnoPlus SARL',
  telephone: '0522-123456',
  email: 'contact@technoplus.ma',
  ice: '001122334455667',
  montant_rest_du: 45000
})

onMounted(() => {
  // Dans un vrai environnement on ferait un api.get(`/clients/${props.id}`)
})
</script>
