<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Avoirs Clients</h2>
        <p class="text-sm text-muted">Retours marchands & Réductions</p>
      </div>
      <router-link to="/avoirs-clients/new" class="btn btn-primary">Nouvel Avoir Client</router-link>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>N° Avoir</th>
            <th>Date</th>
            <th>Client</th>
            <th>Réf. Facture</th>
            <th style="text-align:right">Montant TTC</th>
            <th>État</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in avoirs" :key="a.id">
            <td><span class="font-mono text-accent" style="font-weight:600;">{{ a.numero }}</span></td>
            <td>{{ a.date_avoir }}</td>
            <td style="font-weight:500;">{{ a.client }}</td>
            <td class="text-muted">{{ a.facture }}</td>
            <td style="text-align:right; font-weight:600; color:var(--danger)">{{ formatMoney(a.total_ttc) }}</td>
            <td><span class="badge badge-warning">Actif</span></td>
            <td>
              <router-link :to="`/avoirs-clients/${a.id}`" class="btn btn-secondary btn-sm">Détail</router-link>
            </td>
          </tr>
          <tr v-if="avoirs.length === 0">
             <td colspan="7" class="text-center text-muted">Aucun avoir client.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const avoirs = ref([
  { id: 1, numero: 'AVR-2026-0001', date_avoir: '2026-04-16', client: 'TechnoPlus SARL', facture: 'FAC-202604-0001', total_ttc: 1200 }
])

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}
</script>
