<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <router-link to="/produits" class="btn btn-secondary btn-sm">← Retour</router-link>
        <h2 style="font-size:1.1rem; font-weight:600;">{{ isNew ? 'Nouveau Produit' : 'Modifier le Produit' }}</h2>
      </div>
      <button class="btn btn-primary" @click="save">Enregistrer</button>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
      <form @submit.prevent>
        <h3 class="text-sm text-muted mb-2 text-uppercase" style="border-bottom:1px solid var(--border-color); padding-bottom:0.5rem;">Informations Générales</h3>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Référence *</label>
            <input v-model="form.reference" type="text" class="form-input" placeholder="PRD-000" />
          </div>
          <div class="form-group">
            <label class="form-label">Désignation *</label>
            <input v-model="form.designation" type="text" class="form-input" placeholder="Nom du produit ou service" />
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Type</label>
            <select v-model="form.type_produit" class="form-select">
              <option value="bien">Bien matériel</option>
              <option value="service">Service</option>
              <option value="matiere_premiere">Matière Première</option>
              <option value="produit_fini">Produit Fini (Assemblé)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Code Barre (EAN/UPC)</label>
            <input v-model="form.code_barre" type="text" class="form-input" placeholder="1234567890123" />
          </div>
        </div>

        <h3 class="text-sm text-muted mb-2 mt-3 text-uppercase" style="border-bottom:1px solid var(--border-color); padding-bottom:0.5rem;">Tarification</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Prix d'Achat HT</label>
            <input v-model="form.prix_achat_ht" type="number" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Prix de Vente HT *</label>
            <input v-model="form.prix_vente_ht" type="number" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">TVA (%)</label>
            <select class="form-select">
              <option value="20">20%</option>
              <option value="14">14%</option>
              <option value="10">10%</option>
              <option value="0">0% (Exonéré)</option>
            </select>
          </div>
        </div>

        <h3 class="text-sm text-muted mb-2 mt-3 text-uppercase" style="border-bottom:1px solid var(--border-color); padding-bottom:0.5rem;">Stock & Description</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group flex items-center gap-2" style="margin-top: 1.5rem;">
            <input type="checkbox" v-model="form.gerer_stock" id="gerer_stock" />
            <label for="gerer_stock" style="cursor:pointer">Gérer le stock pour cet article</label>
          </div>
          <div class="form-group" v-if="form.gerer_stock">
            <label class="form-label">Alerte Stock Minimum</label>
            <input v-model="form.stock_min" type="number" class="form-input" />
          </div>
        </div>
        <div class="form-group mt-2">
          <label class="form-label">Description détaillée</label>
          <textarea v-model="form.description" class="form-textarea" rows="3" placeholder="Caractéristiques techniques, notes..."></textarea>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const isNew = computed(() => route.params.id === 'new')

const form = ref({
  reference: '',
  designation: '',
  type_produit: 'bien',
  code_barre: '',
  prix_achat_ht: 0,
  prix_vente_ht: 0,
  gerer_stock: true,
  stock_min: 5,
  description: ''
})

function save() {
  // Demo simulate save
  alert('Produit enregistré avec succès (Mode Démo)')
  router.push('/produits')
}
</script>
