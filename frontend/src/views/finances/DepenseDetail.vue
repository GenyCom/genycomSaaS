<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <router-link to="/depenses" class="btn btn-secondary btn-sm">← Retour</router-link>
        <h2 style="font-size:1.1rem; font-weight:600;">{{ isNew ? 'Saisir une Dépense' : 'Détails de la Dépense #' + form.id }}</h2>
      </div>
      <button v-if="isNew || editMode" class="btn btn-primary" @click="save">Enregistrer</button>
      <button v-else class="btn btn-primary" @click="editMode = true">Modifier</button>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <form @submit.prevent>
        <div class="form-group">
          <label class="form-label">Date de la dépense *</label>
          <input v-model="form.date_depense" type="date" class="form-input" />
        </div>
        
        <div class="form-group">
          <label class="form-label">Catégorie *</label>
          <select v-model="form.categorie_id" class="form-select">
            <option value="1">Énergie (Électricité, Eau)</option>
            <option value="2">Fournitures de bureau</option>
            <option value="3">Loyer</option>
            <option value="4">Abonnements SaaS / Logiciels</option>
            <option value="5">Carburant & Transport</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Description abrégée *</label>
          <input v-model="form.description" type="text" class="form-input" placeholder="Ex: Facture électricité Mars" />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Montant HT</label>
            <input v-model="form.montant_ht" type="number" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Montant TTC *</label>
            <input v-model="form.montant_ttc" type="number" step="0.01" class="form-input border-focus" />
          </div>
        </div>

        <div class="form-group flex items-center gap-2" style="margin-top: 1.5rem; background: var(--bg-primary); padding: 1rem; border-radius: var(--radius-sm);">
          <input type="checkbox" v-model="form.est_payee" id="est_payee" />
          <label for="est_payee" style="cursor:pointer; font-weight:600;">Cette dépense a déjà été payée</label>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const isNew = computed(() => !route.params.id || route.params.id === 'new')
const editMode = ref(isNew.value)

const form = ref({
  id: '',
  date_depense: new Date().toISOString().substring(0, 10),
  categorie_id: 2,
  description: '',
  montant_ht: 0,
  montant_ttc: 0,
  est_payee: false
})

onMounted(() => {
  if (!isNew.value) {
    // Mock load data
    form.value = {
      id: route.params.id,
      date_depense: '2026-04-10',
      categorie_id: 1,
      description: 'Facture Exemple',
      montant_ht: 4000,
      montant_ttc: 4500,
      est_payee: true
    }
  }
})

function save() {
  alert(isNew.value ? 'Dépense enregistrée (Mode Démo)' : 'Modifications enregistrées !')
  if (isNew.value) {
    router.push('/depenses')
  } else {
    editMode.value = false
  }
}
</script>
