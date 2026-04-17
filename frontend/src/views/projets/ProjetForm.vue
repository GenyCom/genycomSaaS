<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/projets" class="btn btn-secondary btn-sm" style="padding: 0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <h2 style="font-size:1.2rem; font-weight:600;">Ajouter un nouveau projet</h2>
      </div>
      <div>
        <button class="btn btn-primary" @click="saveProjet">Enregistrer le projet</button>
      </div>
    </div>

    <div class="card max-w-3xl">
      <h3 class="mb-3 text-warning border-b border-gray-700 pb-2">Informations Générales</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom: 2rem;">
        
        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Nom du Projet</label>
          <input v-model="form.nom" class="form-input" placeholder="Ex: Déploiement Infrastructure" required />
        </div>
        
        <div class="form-group">
          <label class="form-label">Référence</label>
          <input v-model="form.reference" class="form-input" placeholder="Généré automatiquement si vide" />
        </div>

        <div class="form-group">
          <label class="form-label">Client</label>
          <select v-model="form.client_id" class="form-input">
            <option value="">Sélectionner un client...</option>
            <option value="1">TechnoPlus SARL</option>
            <option value="2">MediaCom Group</option>
            <option value="3">Digital Factory</option>
          </select>
        </div>
      </div>

      <h3 class="mb-3 text-warning border-b border-gray-700 pb-2">Planification et Budget</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
        <div class="form-group">
          <label class="form-label">Date de Début</label>
          <input v-model="form.date_debut" type="date" class="form-input" />
        </div>
        
        <div class="form-group">
          <label class="form-label">Date de Fin Prévue</label>
          <input v-model="form.date_fin_prevue" type="date" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">Budget Estimé (DH)</label>
          <input v-model="form.budget_estime" type="number" class="form-input" placeholder="0.00" />
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  nom: '',
  reference: '',
  client_id: '',
  date_debut: '',
  date_fin_prevue: '',
  budget_estime: '',
  etat: 'A faire'
})

function saveProjet() {
  alert('Projet créé avec succès !')
  router.push('/projets')
}
</script>
