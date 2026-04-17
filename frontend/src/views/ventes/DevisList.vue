<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Devis</h2>
        <p class="text-sm text-muted">{{ devis.length }} devis en cours</p>
      </div>
      <router-link to="/devis/new" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau Devis
      </router-link>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>N° Devis</th>
            <th>Date</th>
            <th>Client</th>
            <th>Validité</th>
            <th style="text-align:right">Montant TTC</th>
            <th>État</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in devis" :key="d.id">
             <td><span class="font-mono" style="font-weight:600; color:var(--accent);">{{ d.numero }}</span></td>
             <td>{{ d.date_devis }}</td>
             <td style="font-weight:500;">{{ d.client }}</td>
             <td>{{ d.date_validite || '—' }}</td>
             <td style="text-align:right; font-weight:600;">{{ formatMoney(d.total_ttc) }}</td>
             <td>
               <span class="badge" :class="d.etat === 'Accepté' ? 'badge-success' : (d.etat === 'Refusé' ? 'badge-danger' : 'badge-info')">{{ d.etat }}</span>
             </td>
             <td>
               <router-link :to="'/devis/' + d.id" class="btn btn-secondary btn-sm">Détails</router-link>
             </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const devis = ref([])

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/devis')
    devis.value = (data.data || data || []).map(d => ({ ...d, client: d.client?.societe, etat: d.etat?.libelle }))
  } catch {
    devis.value = [
      { id: 1, numero: 'DEV-202604-0012', date_devis: '2026-04-16', date_validite: '2026-05-16', client: 'TechnoPlus SARL', total_ttc: 95000, etat: 'En attente' },
      { id: 2, numero: 'DEV-202604-0011', date_devis: '2026-04-12', date_validite: '2026-05-12', client: 'MediaCom Group', total_ttc: 12400, etat: 'Accepté' },
      { id: 3, numero: 'DEV-202604-0008', date_devis: '2026-04-05', date_validite: '2026-05-05', client: 'Digital Factory', total_ttc: 45000, etat: 'Refusé' },
    ]
  }
})
</script>
