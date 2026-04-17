<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/projets" class="btn btn-secondary btn-sm" style="padding: 0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <h2 style="font-size:1.2rem; font-weight:600;">Détails du Projet : {{ projet.reference }}</h2>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-secondary" @click="editMode = !editMode">
          {{ editMode ? 'Annuler' : 'Modifier' }}
        </button>
        <button v-if="editMode" class="btn btn-primary" @click="saveProjet">Enregistrer</button>
      </div>
    </div>

    <div class="card max-w-4xl">
      <div v-if="!editMode" class="grid-layout">
        <div>
          <p class="text-sm text-muted">Nom du Projet</p>
          <p class="font-medium text-lg">{{ projet.nom }}</p>
        </div>
        <div>
          <p class="text-sm text-muted">Statut</p>
          <span class="badge" :class="projet.etat === 'En cours' ? 'badge-info' : 'badge-success'">{{ projet.etat }}</span>
        </div>
        <div>
          <p class="text-sm text-muted">Période</p>
          <p>{{ projet.date_debut }} → {{ projet.date_fin_prevue }}</p>
        </div>
        <div>
          <p class="text-sm text-muted">Budget Estimé</p>
          <p class="font-medium">{{ formatMoney(projet.budget_estime) }}</p>
        </div>
        <div style="grid-column: span 2">
          <p class="text-sm text-muted">Client</p>
          <p v-if="projet.client">{{ projet.client.societe }}</p>
          <p v-else class="text-muted italic">Non défini</p>
        </div>
      </div>

      <div v-else class="grid-layout" style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Nom du Projet</label>
          <input v-model="form.nom" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Date de Début</label>
          <input v-model="form.date_debut" type="date" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Date de Fin Prévue</label>
          <input v-model="form.date_fin_prevue" type="date" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Budget Estimé</label>
          <input v-model="form.budget_estime" type="number" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select v-model="form.etat" class="form-input">
            <option value="A faire">A faire</option>
            <option value="En cours">En cours</option>
            <option value="Terminé">Terminé</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const editMode = ref(false)

const projet = ref({ // Valeurs par defaut en attendant l'API
  id: route.params.id,
  reference: 'PRJ-' + route.params.id,
  nom: 'Informations du Projet',
  client: { societe: 'Client Exemple' },
  date_debut: '2026-01-01',
  date_fin_prevue: '2026-12-31',
  budget_estime: 150000,
  etat: 'En cours'
})

const form = ref({ ...projet.value })

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

function saveProjet() {
  projet.value = { ...form.value }
  editMode.value = false
  alert('Modifications enregistrées !')
}

onMounted(() => {
  // Ici on pourrait charger les données via l'API
})
</script>

<style scoped>
.grid-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}
</style>
