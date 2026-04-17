<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">État du Stock</h2>
        <p class="text-sm text-muted">Contrôle de l'inventaire en temps réel</p>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-secondary">Mouvement Interne</button>
        <button class="btn btn-primary">Nouvel Inventaire</button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Entrepôt</th>
            <th style="text-align:right">Stock Physique</th>
            <th style="text-align:right">Réservé</th>
            <th style="text-align:right">Disponible</th>
            <th>Alerte</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in stock" :key="s.id">
             <td style="font-weight:500;">
               <div style="font-size:0.85rem">{{ s.produit?.designation }}</div>
               <div class="font-mono text-muted text-sm">{{ s.produit?.reference }}</div>
             </td>
             <td>{{ s.entrepot?.nom }}</td>
             <td style="text-align:right; font-weight:600;">{{ s.quantite_physique }}</td>
             <td style="text-align:right; color:var(--text-muted)">{{ s.quantite_reservee }}</td>
             <td style="text-align:right; font-weight:600; color:var(--success);">{{ s.quantite_disponible }}</td>
             <td>
               <span v-if="s.quantite_disponible <= s.produit?.stock_min" class="badge badge-danger">Alerte</span>
               <span v-else class="badge badge-success">OK</span>
             </td>
             <td>
               <router-link :to="`/stock/${s.id}/historique`" class="btn btn-secondary btn-sm" style="display:inline-block; text-decoration:none;">Historique</router-link>
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

const stock = ref([])

onMounted(async () => {
  try {
    const { data } = await api.get('/stock')
    stock.value = data.data || data || []
  } catch {
    stock.value = [
      { id: 1, quantite_physique: 145, quantite_reservee: 15, quantite_disponible: 130, produit: { reference: 'PRD-001', designation: 'Écran LED 27"', stock_min: 20 }, entrepot: { nom: 'Principal' } },
      { id: 2, quantite_physique: 12, quantite_reservee: 5, quantite_disponible: 7, produit: { reference: 'PRD-012', designation: 'Clavier Mécanique Pro', stock_min: 10 }, entrepot: { nom: 'A' } },
      { id: 3, quantite_physique: 450, quantite_reservee: 0, quantite_disponible: 450, produit: { reference: 'PRD-045', designation: 'Câble HDMI 2.1', stock_min: 100 }, entrepot: { nom: 'B' } },
    ]
  }
})
</script>
