<template>
  <div class="animate-fade-in max-w-3xl mx-auto">
    <div class="mb-5">
      <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Mon Profil</h2>
      <p class="text-sm text-muted">Gérer vos informations personnelles et vos paramètres de sécurité.</p>
    </div>

    <!-- Messages globaux -->
    <div v-if="successMsg" class="mb-4" style="padding:0.75rem 1rem; border-radius:8px; background: rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color: var(--success);">
      ✅ {{ successMsg }}
    </div>
    <div v-if="errorMsg" class="mb-4" style="padding:0.75rem 1rem; border-radius:8px; background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color: var(--danger);">
      ❌ {{ errorMsg }}
    </div>

    <div class="card mb-4">
      <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Informations Personnelles</h3>
      <form @submit.prevent="updateProfile">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
          <div class="form-group mb-0">
            <label class="form-label">Prénom *</label>
            <input v-model="profileForm.prenom" class="form-input" required />
          </div>
          <div class="form-group mb-0">
            <label class="form-label">Nom *</label>
            <input v-model="profileForm.nom" class="form-input" required />
          </div>
          <div class="form-group mb-0">
            <label class="form-label">Email <span class="text-xs text-muted font-normal">(Non modifiable)</span></label>
            <input type="email" v-model="profileForm.email" class="form-input" disabled style="background-color: var(--bg-body); opacity: 0.7; cursor: not-allowed;" />
          </div>
          <div class="form-group mb-0">
            <label class="form-label">Téléphone</label>
            <input v-model="profileForm.telephone" class="form-input" placeholder="+212 6..." />
          </div>
        </div>
        
        <div class="mt-4 flex justify-end">
          <button type="submit" class="btn btn-primary" :disabled="loadingProfile">
            <span v-if="loadingProfile">⏳ Enregistrement...</span>
            <span v-else>Enregistrer les modifications</span>
          </button>
        </div>
      </form>
    </div>

    <div class="card">
      <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Changer le Mot de Passe</h3>
      <form @submit.prevent="updatePassword">
        <div class="form-group">
          <label class="form-label">Mot de passe actuel *</label>
          <input type="password" v-model="passwordForm.current_password" class="form-input" required />
        </div>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
          <div class="form-group mb-0">
            <label class="form-label">Nouveau mot de passe * <span class="text-xs text-muted font-normal">(Min 8 chars)</span></label>
            <input type="password" v-model="passwordForm.new_password" class="form-input" required minlength="8" />
          </div>
          <div class="form-group mb-0">
            <label class="form-label">Confirmer le nouveau mot de passe *</label>
            <input type="password" v-model="passwordForm.new_password_confirmation" class="form-input" required minlength="8" />
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button type="submit" class="btn btn-secondary" style="border-color: var(--danger); color: var(--danger);" :disabled="loadingPassword">
            <span v-if="loadingPassword">⏳ Changement...</span>
            <span v-else>Mettre à jour le mot de passe</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const authStore = useAuthStore()

const profileForm = ref({
  nom: '',
  prenom: '',
  email: '',
  telephone: ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const loadingProfile = ref(false)
const loadingPassword = ref(false)

const successMsg = ref('')
const errorMsg = ref('')

onMounted(() => {
  if (authStore.user) {
    profileForm.value = {
      nom: authStore.user.nom || '',
      prenom: authStore.user.prenom || '',
      email: authStore.user.email || '',
      telephone: authStore.user.telephone || ''
    }
  }
})

function clearMessages() {
  successMsg.value = ''
  errorMsg.value = ''
}

async function updateProfile() {
  clearMessages()
  loadingProfile.value = true
  try {
    const { data } = await api.put('/me/profile', profileForm.value)
    successMsg.value = data.message || 'Profil mis à jour'
    authStore.setUser(data.user) // Met à jour le store
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Erreur lors de la mise à jour du profil'
  } finally {
    loadingProfile.value = false
  }
}

async function updatePassword() {
  clearMessages()
  
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    errorMsg.value = 'Les nouveaux mots de passe ne correspondent pas.'
    return
  }

  loadingPassword.value = true
  try {
    const { data } = await api.put('/me/password', passwordForm.value)
    successMsg.value = data.message || 'Mot de passe modifié'
    passwordForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Erreur lors du changement de mot de passe'
  } finally {
    loadingPassword.value = false
  }
}
</script>
