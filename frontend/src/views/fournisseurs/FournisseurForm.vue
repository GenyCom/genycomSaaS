<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/fournisseurs" class="btn btn-secondary btn-sm" style="padding: 0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <h2 style="font-size:1.2rem; font-weight:600;">Enregistrer un fournisseur</h2>
      </div>
      <div>
        <button class="btn btn-primary" @click="saveFournisseur">Sauvegarder le partenaire</button>
      </div>
    </div>

    <div class="card max-w-3xl">
      <h3 class="mb-3 text-warning border-b border-gray-700 pb-2">Informations Standard</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom: 2rem;">
        
        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Nom du Fournisseur / Raison Sociale</label>
          <input v-model="form.societe" class="form-input" placeholder="Ex: Fournitures PRO SAS" required />
        </div>
        
        <div class="form-group">
          <label class="form-label">Domaine (Type)</label>
          <select v-model="form.type_fournisseur_id" class="form-input">
            <option value="1">Grossiste Matériel</option>
            <option value="2">Importateur</option>
            <option value="3">Prestataire de Services</option>
            <option value="4">Fournitures bureau</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Identifiant Fiscal (ICE)</label>
          <input v-model="form.ice" class="form-input" placeholder="15 chiffres" />
        </div>
      </div>

      <h3 class="mb-3 text-warning border-b border-gray-700 pb-2">Contacts & Adresse</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
        <div class="form-group">
          <label class="form-label">Email Comptabilité/Vente</label>
          <input v-model="form.email" type="email" class="form-input" placeholder="contact@fournisseur.com" />
        </div>
        
        <div class="form-group">
          <label class="form-label">Ligne directe</label>
          <input v-model="form.telephone" class="form-input" placeholder="+212 ..." />
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Adresse Complète</label>
          <input v-model="form.adresse" class="form-input" placeholder="..." />
        </div>
        
        <div class="form-group">
          <label class="form-label">Ville</label>
          <input v-model="form.ville" class="form-input" placeholder="Ex: Tanger" />
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
  societe: '',
  type_fournisseur_id: '1',
  ice: '',
  email: '',
  telephone: '',
  adresse: '',
  ville: ''
})

function saveFournisseur() {
  alert('Démonstration de création du Fournisseur validée !')
  router.push('/fournisseurs')
}
</script>
