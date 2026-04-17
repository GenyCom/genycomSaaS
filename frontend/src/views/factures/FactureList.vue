<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Factures</h2>
        <p class="text-sm text-muted">{{ factures.length }} facture(s)</p>
      </div>
      <router-link to="/factures/new" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle Facture
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card" style="padding: 1rem; margin-bottom: 1rem;">
      <div class="flex gap-2 items-center" style="flex-wrap: wrap;">
        <input v-model="search" class="form-input" placeholder="N° facture, client..." style="max-width: 250px;" />
        <select v-model="filterEtat" class="form-select" style="max-width: 180px;">
          <option value="">Tous les états</option>
          <option value="brouillon">Brouillon</option>
          <option value="ouverte">Ouverte</option>
          <option value="payee">Payée</option>
          <option value="retard">En retard</option>
        </select>
      </div>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>N° Facture</th>
            <th>Date</th>
            <th>Client</th>
            <th>État</th>
            <th style="text-align:right">Total TTC</th>
            <th style="text-align:right">Réglé</th>
            <th style="text-align:right">Reste</th>
            <th>Échéance</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="f in factures" :key="f.id">
            <td><span class="font-mono" style="font-weight:600; color:var(--accent);">{{ f.numero }}</span></td>
            <td>{{ f.date_facture }}</td>
            <td style="font-weight:500;">{{ f.client }}</td>
            <td>
              <span class="badge" :class="getEtatClass(f.etat)">{{ f.etat }}</span>
            </td>
            <td style="text-align:right; font-weight:600;">{{ formatMoney(f.total_ttc) }}</td>
            <td style="text-align:right; color:var(--success);">{{ formatMoney(f.montant_regle) }}</td>
            <td style="text-align:right; font-weight:600;" :style="{ color: f.reste > 0 ? 'var(--danger)' : 'var(--text-secondary)' }">{{ formatMoney(f.reste) }}</td>
            <td :style="{ color: isOverdue(f.date_echeance) ? 'var(--danger)' : 'var(--text-secondary)' }">{{ f.date_echeance || '—' }}</td>
            <td>
              <router-link :to="`/factures/${f.id}`" class="btn btn-secondary btn-sm">Détail</router-link>
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

const factures = ref([])
const search = ref('')
const filterEtat = ref('')

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

function getEtatClass(etat) {
  const map = { 'Brouillon': 'badge-default', 'Ouverte': 'badge-info', 'Payée': 'badge-success', 'Partielle': 'badge-warning', 'En retard': 'badge-danger', 'Annulée': 'badge-default' }
  return map[etat] || 'badge-default'
}

function isOverdue(date) {
  if (!date) return false
  return new Date(date) < new Date()
}

onMounted(async () => {
  try {
    const { data } = await api.get('/factures')
    factures.value = (data.data || data || []).map(f => ({
      ...f, client: f.client?.societe, etat: f.etat?.libelle, reste: f.total_ttc - f.montant_regle
    }))
  } catch {
    factures.value = [
      { id: 1, numero: 'FAC-202604-0001', date_facture: '2026-04-15', client: 'TechnoPlus SARL', etat: 'Ouverte', total_ttc: 58000, montant_regle: 20000, reste: 38000, date_echeance: '2026-05-15' },
      { id: 2, numero: 'FAC-202604-0002', date_facture: '2026-04-10', client: 'Digital Factory', etat: 'Payée', total_ttc: 24500, montant_regle: 24500, reste: 0, date_echeance: '2026-04-25' },
      { id: 3, numero: 'FAC-202603-0008', date_facture: '2026-03-20', client: 'MediaCom Group', etat: 'En retard', total_ttc: 45000, montant_regle: 15000, reste: 30000, date_echeance: '2026-04-05' },
      { id: 4, numero: 'FAC-202604-0003', date_facture: '2026-04-17', client: 'InnoTech SA', etat: 'Brouillon', total_ttc: 12800, montant_regle: 0, reste: 12800, date_echeance: null },
    ]
  }
})
</script>
