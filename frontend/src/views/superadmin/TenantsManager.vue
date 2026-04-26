<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Gestion des Comptes SaaS (Tenants)</h2>
        <p class="text-sm text-muted">Création des bases de données isolées</p>
      </div>
      <button class="btn btn-primary" @click="showForm = true">
        + Provisionner un nouveau SaaS
      </button>
    </div>

    <!-- Formulaire de provisioning -->
    <div v-if="showForm" class="card mb-3 animate-slide-left">
      <h3 class="form-section-title">Création d'une nouvelle instance</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
         <div class="form-group mb-0">
           <label class="form-label">Raison Sociale / Nom du Client *</label>
           <input v-model="form.nom_entreprise" class="form-input" placeholder="Ex: Groupe Auto" required />
         </div>
         <div class="form-group mb-0">
            <label class="form-label">Nom de la Base (MySQL) *</label>
            <input v-model="form.database_name" class="form-input" placeholder="Ex: genycom_groupeauto" required />
            <p class="text-xs text-muted mt-1">La base doit déjà exister si votre hébergeur ne permet pas le CREATE DATABASE.</p>
         </div>

         <div class="form-group mb-0">
            <label class="form-label">Utilisateur DB (Optionnel)</label>
            <input v-model="form.db_username" class="form-input" placeholder="Utilisateur spécifique" />
         </div>
         <div class="form-group mb-0">
            <label class="form-label">Mot de passe DB (Optionnel)</label>
            <input v-model="form.db_password" type="password" class="form-input" placeholder="Mot de passe spécifique" />
         </div>

         <div class="form-group mb-0" style="grid-column: span 2;">
           <label class="form-label">Email de l'Administrateur (Owner) *</label>
           <input v-model="form.email" class="form-input" placeholder="Ex: boss@groupe.com" required />
           <p class="text-muted text-sm mt-1">Si l'email existe déjà dans le système, cet utilisateur obtiendra instantanément l'accès à cette nouvelle instance.</p>
         </div>
         
         <!-- Nouveaux champs d'entreprise -->
         <h4 class="text-muted mt-2" style="grid-column: span 2; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Informations de l'entreprise</h4>
         <div class="form-group mb-0">
           <label class="form-label">Forme Juridique</label>
           <input v-model="form.forme_juridique" class="form-input" placeholder="Ex: SARL, SA..." />
         </div>
         <div class="form-group mb-0">
           <label class="form-label">Téléphone</label>
           <input v-model="form.telephone" class="form-input" placeholder="+212..." />
         </div>
         <div class="form-group mb-0" style="grid-column: span 2;">
           <label class="form-label">Adresse *</label>
           <input v-model="form.adresse" class="form-input" placeholder="Adresse complète" required />
         </div>
         <div class="form-group mb-0">
           <label class="form-label">Ville *</label>
           <input v-model="form.ville" class="form-input" placeholder="Ex: Casablanca" required />
         </div>
         <div class="form-group mb-0">
           <label class="form-label">Site Web / URL ou Logo URL</label>
           <input v-model="form.site_web" class="form-input" placeholder="https://..." />
         </div>
      </div>

      <!-- Messages de retour -->
      <div v-if="provisionSuccess" class="mb-2" style="padding:0.75rem 1rem; border-radius:8px; background: rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color: var(--success);">
        ✅ {{ provisionSuccess }}
      </div>
      <div v-if="provisionError" class="mb-2" style="padding:0.75rem 1rem; border-radius:8px; background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color: var(--danger);">
        ❌ {{ provisionError }}
      </div>

      <div v-if="showConfirmExisting" class="mb-3" style="padding:1rem; border-radius:8px; background: rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3);">
        <p class="mb-2 font-semibold" style="color: #B45309;">⚠️ La base de données existe déjà !</p>
        <p class="text-sm mb-3">Voulez-vous utiliser cette base existante et y exécuter les scripts d'initialisation (migrations) ?</p>
        <div class="flex gap-2">
           <button class="btn" style="background: #D97706; color: white;" @click="provision(true)">Oui, utiliser cette base</button>
           <button class="btn btn-secondary" @click="showConfirmExisting = false">Abandonner</button>
        </div>
      </div>

      <div class="flex gap-2" v-if="!showConfirmExisting">
         <button class="btn btn-primary" @click="provision(false)" :disabled="provisioning">
           <span v-if="provisioning">⏳ Génération en cours...</span>
           <span v-else>Lancer la génération de la Base !</span>
         </button>
         <button class="btn btn-secondary" @click="resetForm">Annuler</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card" style="text-align:center; padding:2rem;">
      <p class="text-muted">Chargement des tenants...</p>
    </div>

    <!-- Tableau des tenants -->
    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom de l'Instance</th>
            <th>Domaine</th>
            <th>Base de données (Isolée)</th>
            <th>Statut du compte</th>
            <th>Utilisateurs</th>
            <th>Créé le</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="tenants.length === 0">
            <td colspan="7" style="text-align:center; padding:2rem;" class="text-muted">
              Aucun tenant provisionné. Cliquez sur le bouton ci-dessus pour en créer un.
            </td>
          </tr>
          <tr v-for="t in tenants" :key="t.id">
            <td style="font-weight:600;">{{ t.nom }}</td>
            <td class="text-muted">{{ t.domain || '—' }}</td>
            <td class="font-mono text-sm">{{ t.database_name }}</td>
            <td>
              <span class="badge" :class="t.statut === 'actif' ? 'badge-success' : t.statut === 'suspendu' ? 'badge-danger' : 'badge-warning'">
                {{ t.statut }}
              </span>
            </td>
            <td>
              <div class="flex items-center gap-2">
                <span class="status-dot"></span>
                <span class="text-sm" style="color: var(--success); font-weight: 500;">{{ t.users_count }} utilisateur(s)</span>
              </div>
            </td>
            <td class="text-sm text-muted">{{ formatDate(t.created_at) }}</td>
            <td>
              <router-link :to="`/superadmin/tenants/${t.id}`" class="btn btn-secondary btn-sm">Gérer</router-link>
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

const showForm = ref(false)
const loading = ref(true)
const provisioning = ref(false)
const showConfirmExisting = ref(false)
const provisionSuccess = ref('')
const provisionError = ref('')
const tenants = ref([])
const form = ref({ 
  nom_entreprise: '', 
  database_name: '', 
  db_username: '',
  db_password: '',
  email: '',
  forme_juridique: '',
  adresse: '',
  ville: '',
  telephone: '',
  site_web: ''
})

onMounted(async () => {
  await loadTenants()
})

async function loadTenants() {
  loading.value = true
  try {
    const { data } = await api.get('/superadmin/tenants')
    tenants.value = data
  } catch (err) {
    console.error('Erreur chargement tenants:', err)
  } finally {
    loading.value = false
  }
}

async function provision(force = false) {
  provisionSuccess.value = ''
  provisionError.value = ''
  showConfirmExisting.value = false

  if (!form.value.nom_entreprise || !form.value.database_name || !form.value.email) {
    provisionError.value = 'Veuillez remplir tous les champs.'
    return
  }

  provisioning.value = true
  try {
    const { data } = await api.post('/superadmin/tenants', {
      ...form.value,
      confirm_existing: force
    })
    provisionSuccess.value = data.message
    form.value = { 
      nom_entreprise: '', 
      database_name: '', 
      db_username: '',
      db_password: '',
      email: '',
      forme_juridique: '',
      adresse: '',
      ville: '',
      telephone: '',
      site_web: '' 
    }
    // Rafraichir la liste
    await loadTenants()
  } catch (err) {
    if (err.response?.status === 409 && err.response?.data?.code === 'DATABASE_EXISTS') {
      showConfirmExisting.value = true
      provisionError.value = err.response.data.message
    } else {
      provisionError.value = err.response?.data?.message || 'Erreur inconnue lors du provisioning.'
    }
  } finally {
    provisioning.value = false
  }
}

function resetForm() {
  showForm.value = false
  showConfirmExisting.value = false
  provisionSuccess.value = ''
  provisionError.value = ''
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
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
