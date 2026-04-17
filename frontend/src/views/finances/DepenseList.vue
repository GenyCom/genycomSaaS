<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Dépenses & Charges</h2>
        <p class="text-sm text-muted">Suivi des achats frais généraux</p>
      </div>
      <router-link to="/depenses/new" class="btn btn-primary">Saisir une Dépense</router-link>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Catégorie</th>
            <th>Description</th>
            <th>Fournisseur</th>
            <th style="text-align:right">Montant TTC</th>
            <th>Paiement</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in depenses" :key="d.id">
             <td>{{ d.date_depense }}</td>
             <td><span class="badge badge-default">{{ d.categorie?.libelle }}</span></td>
             <td style="font-weight:500;">{{ d.description }}</td>
             <td class="text-muted">{{ d.fournisseur?.societe || '—' }}</td>
             <td style="text-align:right; font-weight:600; color:var(--danger)">{{ formatMoney(d.montant_ttc) }}</td>
             <td>
               <span v-if="d.est_payee" class="text-success flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Payé</span>
               <span v-else class="text-warning flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> À payer</span>
             </td>
             <td><router-link :to="`/depenses/${d.id}`" class="btn btn-secondary btn-sm" style="display:inline-block; text-decoration:none; text-align:center;">Détails</router-link></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const depenses = ref([])

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/depenses')
    depenses.value = data.data || data || []
  } catch {
    depenses.value = [
      { id: 1, date_depense: '2026-04-10', description: 'Facture Électricité Mois Mars', montant_ttc: 4500, est_payee: true, categorie: { libelle: 'Énergie' }, fournisseur: null },
      { id: 2, date_depense: '2026-04-12', description: 'Achat Fournitures de Bureau', montant_ttc: 1250, est_payee: true, categorie: { libelle: 'Fournitures' }, fournisseur: { societe: 'Librairie Centrale' } },
      { id: 3, date_depense: '2026-04-15', description: 'Loyer Avril 2026', montant_ttc: 15000, est_payee: false, categorie: { libelle: 'Loyer' }, fournisseur: null },
    ]
  }
})
</script>
