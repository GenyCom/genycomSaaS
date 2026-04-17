<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Projets Clients</h2>
        <p class="text-sm text-muted">Suivi des activités et rentabilité</p>
      </div>
      <router-link to="/projets/create" class="btn btn-primary" style="display:inline-block; text-decoration:none; text-align:center;">Nouveau Projet</router-link>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Réf</th>
            <th>Nom du Projet</th>
            <th>Client</th>
            <th>Période</th>
            <th style="text-align:right">Budget</th>
            <th>Statut</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in projets" :key="p.id">
             <td><span class="font-mono text-sm" style="color:var(--accent);">{{ p.reference }}</span></td>
             <td style="font-weight:500;">{{ p.nom }}</td>
             <td>{{ p.client?.societe }}</td>
             <td class="text-muted text-sm">{{ p.date_debut }} → {{ p.date_fin_prevue }}</td>
             <td style="text-align:right; font-weight:600;">{{ formatMoney(p.budget_estime) }}</td>
             <td><span class="badge" :class="p.etat === 'En cours' ? 'badge-info' : 'badge-success'">{{ p.etat }}</span></td>
             <td><router-link :to="`/projets/${p.id}`" class="btn btn-secondary btn-sm" style="display:inline-block; text-decoration:none; text-align:center;">Ouvrir</router-link></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const projets = ref([])

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/projets')
    projets.value = (data.data || data || []).map(p => ({ ...p, etat: p.etat?.libelle }))
  } catch {
    projets.value = [
      { id: 1, reference: 'PRJ-26-001', nom: 'Déploiement Infrastructure Réseau', client: { societe: 'TechnoPlus SARL' }, date_debut: '2026-01-10', date_fin_prevue: '2026-06-30', budget_estime: 250000, etat: 'En cours' },
      { id: 2, reference: 'PRJ-26-002', nom: 'Fourniture Matériel IT Annuel', client: { societe: 'MediaCom Group' }, date_debut: '2026-02-01', date_fin_prevue: '2026-12-31', budget_estime: 180000, etat: 'En cours' },
      { id: 3, reference: 'PRJ-25-045', nom: 'Installation Serveurs', client: { societe: 'Digital Factory' }, date_debut: '2025-11-15', date_fin_prevue: '2025-12-20', budget_estime: 85000, etat: 'Terminé' },
    ]
  }
})
</script>
