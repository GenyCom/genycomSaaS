<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/superadmin/users" class="btn btn-secondary btn-sm" style="padding: 0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <h2 style="font-size:1.2rem; font-weight:600;">
          {{ isEdit ? 'Editer un utilisateur global' : 'Nouvel utilisateur global' }}
        </h2>
      </div>
      <div>
        <button class="btn btn-primary" @click="saveUser" :disabled="saving">
          {{ saving ? 'Enregistrement...' : (isEdit ? 'Appliquer les modifications' : "Créer l'utilisateur") }}
        </button>
      </div>
    </div>

    <!-- Loading state for edit mode -->
    <div v-if="loadingUser" class="card" style="text-align:center; padding:3rem;">
      <p class="text-muted">Chargement des informations...</p>
    </div>

    <div v-else class="card max-w-3xl">
      <h3 class="form-section-title">Informations du compte Central</h3>

      <div v-if="formError" class="auth-error mb-3">{{ formError }}</div>
      <div v-if="formSuccess" class="mb-3" style="padding:0.75rem; background:var(--success-bg); color:var(--success); border-radius:var(--radius);">{{ formSuccess }}</div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
        
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input v-model="form.nom" class="form-input" placeholder="Dupont" />
        </div>
        
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input v-model="form.prenom" class="form-input" placeholder="Jean" />
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Adresse Email (Authentification Unique)</label>
          <input v-model="form.email" type="email" class="form-input" placeholder="jean.dupont@email.com" />
          <p class="text-sm text-muted mt-1">Cet e-mail lui permettra de se connecter sur l'ensemble de l'écosystème selon les droits qui lui seront accordés.</p>
        </div>

        <div class="form-group">
          <label class="form-label">Mot de passe {{ isEdit ? '(laisser vide pour ne pas modifier)' : '' }}</label>
          <input v-model="form.password" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label class="form-label">Confirmer le mot de passe</label>
          <input v-model="form.password_confirmation" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label class="form-label">Privilège SuperAdmin</label>
          <select v-model="form.is_superadmin" class="form-input">
            <option :value="false">NON - Simple Accès aux Bases Client</option>
            <option :value="true">OUI - Plein Accès au Portail SuperAdmin</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Statut du compte</label>
          <select v-model="form.is_active" class="form-input">
            <option :value="true">Actif</option>
            <option :value="false">Désactivé</option>
          </select>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()
const isEdit = ref(false)
const loadingUser = ref(false)
const saving = ref(false)
const formError = ref(null)
const formSuccess = ref(null)

const form = ref({
  nom: '',
  prenom: '',
  email: '',
  password: '',
  password_confirmation: '',
  is_superadmin: false,
  is_active: true,
})

onMounted(async () => {
  if (route.params.id) {
    isEdit.value = true
    loadingUser.value = true
    try {
      const { data } = await api.get(`/superadmin/users/${route.params.id}`)
      form.value.nom = data.nom
      form.value.prenom = data.prenom
      form.value.email = data.email
      form.value.is_superadmin = !!data.is_superadmin
      form.value.is_active = data.is_active !== undefined ? !!data.is_active : true
    } catch (err) {
      formError.value = 'Impossible de charger cet utilisateur.'
    } finally {
      loadingUser.value = false
    }
  }
})

async function saveUser() {
  saving.value = true
  formError.value = null
  formSuccess.value = null

  try {
    const payload = { ...form.value }

    // Ne pas envoyer le mot de passe s'il est vide (en mode édition)
    if (!payload.password) {
      delete payload.password
      delete payload.password_confirmation
    }

    if (isEdit.value) {
      await api.put(`/superadmin/users/${route.params.id}`, payload)
      formSuccess.value = 'Utilisateur mis à jour avec succès.'
    } else {
      await api.post('/superadmin/users', payload)
      router.push('/superadmin/users')
    }
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      formError.value = Object.values(errors).flat().join(' ')
    } else {
      formError.value = err.response?.data?.message || "Erreur lors de l'enregistrement."
    }
  } finally {
    saving.value = false
  }
}
</script>
