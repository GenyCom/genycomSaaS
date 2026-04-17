<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Liste des Clients</h2>
        <p class="text-sm text-muted">{{ clients.length }} client(s) enregistré(s)</p>
      </div>
      <router-link to="/clients/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau Client
      </router-link>
    </div>

    <!-- Search -->
    <div class="card" style="padding: 1rem; margin-bottom: 1rem;">
      <input v-model="search" class="form-input" placeholder="Rechercher par nom, société, email, ICE..." style="max-width: 400px;" />
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Société / Nom</th>
            <th>Ville</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Type</th>
            <th style="text-align:right">Reste dû</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="client in filteredClients" :key="client.id">
            <td><span class="font-mono text-sm">{{ client.code_client || '—' }}</span></td>
            <td style="font-weight:500; color:var(--text-primary);">{{ client.societe }}</td>
            <td>{{ client.ville || '—' }}</td>
            <td>{{ client.telephone || client.mobile || '—' }}</td>
            <td>{{ client.email || '—' }}</td>
            <td><span class="badge" :class="client.type_client?.vip ? 'badge-warning' : 'badge-default'">{{ client.type_client?.libelle || 'Normal' }}</span></td>
            <td style="text-align:right; font-weight:600;" :style="{ color: (client.montant_rest_du > 0) ? 'var(--danger)' : 'var(--text-secondary)' }">
              {{ formatMoney(client.montant_rest_du) }}
            </td>
            <td>
              <router-link :to="`/clients/${client.id}`" class="btn btn-secondary btn-sm">Voir</router-link>
            </td>
          </tr>
          <tr v-if="filteredClients.length === 0">
            <td colspan="8" class="text-center text-muted" style="padding:2rem;">Aucun client trouvé</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const clients = ref([])
const search = ref('')

const filteredClients = computed(() => {
  if (!search.value) return clients.value
  const s = search.value.toLowerCase()
  return clients.value.filter(c =>
    (c.societe || '').toLowerCase().includes(s) ||
    (c.nom || '').toLowerCase().includes(s) ||
    (c.email || '').toLowerCase().includes(s) ||
    (c.code_client || '').toLowerCase().includes(s) ||
    (c.ice || '').toLowerCase().includes(s)
  )
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/clients')
    clients.value = data.data || data || []
  } catch {
    // Demo data
    clients.value = [
      { id: 1, code_client: 'CLI-001', societe: 'TechnoPlus SARL', ville: 'Casablanca', telephone: '0522-123456', email: 'contact@technoplus.ma', type_client: { libelle: 'VIP', vip: true }, montant_rest_du: 45000 },
      { id: 2, code_client: 'CLI-002', societe: 'Digital Factory', ville: 'Rabat', telephone: '0537-654321', email: 'info@digitalfactory.ma', type_client: { libelle: 'Normal', vip: false }, montant_rest_du: 0 },
      { id: 3, code_client: 'CLI-003', societe: 'MediaCom Group', ville: 'Marrakech', telephone: '0524-789012', email: 'admin@mediacom.ma', type_client: { libelle: 'Normal', vip: false }, montant_rest_du: 12500 },
    ]
  }
})
</script>
