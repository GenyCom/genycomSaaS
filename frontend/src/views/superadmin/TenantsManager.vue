<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Gestion des Comptes SaaS (Tenants)</h2>
        <p class="text-sm text-muted">Création des bases de données isolées</p>
      </div>
      <button class="btn btn-primary" style="background: var(--warning); box-shadow: none;" @click="showForm = true">
        + Provisionner un nouveau SaaS
      </button>
    </div>

    <!-- Interface Démo de provisioning -->
    <div v-if="showForm" class="card mb-3 animate-slide-left">
      <h3 class="mb-2 text-warning">Création d'une nouvelle instance</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
         <div class="form-group mb-0">
           <label class="form-label">Raison Sociale / Nom du Client</label>
           <input v-model="form.nom_entreprise" class="form-input" placeholder="Ex: Groupe Auto" />
         </div>
         <div class="form-group mb-0">
           <label class="form-label">Nom de la Base de données (MySQL)</label>
           <input v-model="form.database_name" class="form-input" placeholder="Ex: genycom_groupeauto" />
         </div>
         <div class="form-group mb-0" style="grid-column: span 2;">
           <label class="form-label">Email de l'Administrateur (Owner)</label>
           <input v-model="form.email" class="form-input" placeholder="Ex: boss@groupe.com" />
           <p class="text-muted text-sm mt-1">Si l'email existe déjà dans le système, cet utilisateur obtiendra instantanément l'accès à cette nouvelle instance au lieu d'en recréer un nouveau.</p>
         </div>
      </div>
      <div class="flex gap-2">
         <button class="btn btn-primary" @click="provision">Lancer la génération de la Base !</button>
         <button class="btn btn-secondary" @click="showForm = false">Annuler</button>
      </div>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom de l'Instance</th>
            <th>Domaine</th>
            <th>Base de données (Isolée)</th>
            <th>Statut du compte</th>
            <th>Activité en direct</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="font-weight:600;">GenyCom Demo SARL</td>
            <td class="text-muted">demo.genycom.ma</td>
            <td class="font-mono text-sm">genycom_client_f9a8b1c</td>
            <td><span class="badge badge-success">Actif</span></td>
            <td>
              <div class="flex items-center gap-2">
                <span class="status-dot"></span>
                <span class="text-sm" style="color: var(--success); font-weight: 500;">1 en ligne</span>
              </div>
            </td>
            <td><router-link to="/superadmin/tenants/1" class="btn btn-secondary btn-sm">Gérer</router-link></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showForm = ref(false)
const form = ref({ nom_entreprise: '', email: '' })

function provision() {
  alert('Requête envoyée au serveur : "CREATE DATABASE genycom_client_xxx" et lancement des migrations.')
  showForm.value = false
}
</script>

<style scoped>
.status-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: var(--success);
  box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
  animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>
