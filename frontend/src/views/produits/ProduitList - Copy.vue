<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Catalogue Produits</h2>
        <p class="text-sm text-muted">{{ produits.length }} produit(s)</p>
      </div>
      <router-link to="/produits/new" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau Produit
      </router-link>
    </div>

    <!-- Search -->
    <div class="card" style="padding: 1rem; margin-bottom: 1rem;">
      <input v-model="search" class="form-input" placeholder="Rechercher référence, désignation..." style="max-width: 400px;" />
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Référence</th>
            <th>Désignation</th>
            <th>Type</th>
            <th>Famille</th>
            <th style="text-align:right">Prix Vente HT</th>
            <th style="text-align:right">Stock Min</th>
            <th style="text-align:center">État</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="produit in filteredProduits" :key="produit.id">
            <td><span class="font-mono text-sm" style="font-weight:600; color:var(--accent);">{{ produit.reference }}</span></td>
            <td style="font-weight:500;">{{ produit.designation }}</td>
            <td>{{ produit.is_service ? 'Service' : 'Marchandise' }}</td>
            <td>{{ produit.famille?.libelle || '—' }}</td>
            <td style="text-align:right; font-weight:600;">{{ formatMoney(produit.prix_ht_vente) }}</td>
            <td style="text-align:right;">{{ produit.stock_min || '0' }}</td>
            <td style="text-align:center">
              <span class="badge" :class="produit.is_actif ? 'badge-success' : 'badge-danger'">
                {{ produit.is_actif ? 'Actif' : 'Inactif' }}
              </span>
            </td>
            <td style="text-align: right;">
              <div class="flex items-center gap-1" style="justify-content: flex-end;">
                <router-link :to="`/produits/${produit.id}/edit`" class="btn btn-secondary btn-sm" title="Modifier">✏️</router-link>
                <button @click="deleteProduit(produit.id)" class="btn btn-danger btn-sm" title="Supprimer">🗑️</button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredProduits.length === 0">
            <td colspan="8" class="text-center text-muted" style="padding:2rem;">Aucun produit trouvé</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const produits = ref([])
const search = ref('')

const filteredProduits = computed(() => {
  if (!search.value) return produits.value
  const s = search.value.toLowerCase()
  return produits.value.filter(p =>
    (p.designation || '').toLowerCase().includes(s) ||
    (p.reference || '').toLowerCase().includes(s)
  )
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

async function deleteProduit(id) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) return
  try {
    await api.delete(`/produits/${id}`)
    produits.value = produits.value.filter(p => p.id !== id)
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur lors de la suppression')
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/produits')
    produits.value = data.data || data || []
  } catch (error) {
    console.error('Erreur de chargement:', error)
  }
})
</script>
