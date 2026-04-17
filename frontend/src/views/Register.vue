<template>
  <div class="auth-page">
    <div class="auth-container" style="max-width: 480px;">
      <div class="auth-card">
        <div class="auth-header">
          <div class="auth-logo">G</div>
          <h1>Créer votre compte</h1>
          <p>Lancez votre gestion commerciale en 1 minute</p>
        </div>

        <div v-if="auth.error" class="auth-error">{{ auth.error }}</div>

        <form @submit.prevent="handleRegister">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">Nom</label>
              <input v-model="form.nom" type="text" class="form-input" placeholder="Nom" required />
            </div>
            <div class="form-group">
              <label class="form-label">Prénom</label>
              <input v-model="form.prenom" type="text" class="form-input" placeholder="Prénom" required />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Nom de l'entreprise</label>
            <input v-model="form.nom_entreprise" type="text" class="form-input" placeholder="Ma Société SARL" required />
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-input" placeholder="votre@email.com" required />
          </div>
          <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input v-model="form.password" type="password" class="form-input" placeholder="Min. 8 caractères" required minlength="8" />
          </div>
          <div class="form-group">
            <label class="form-label">Confirmer le mot de passe</label>
            <input v-model="form.password_confirmation" type="password" class="form-input" placeholder="Confirmez" required />
          </div>
          <button type="submit" class="btn btn-primary w-full btn-lg" :disabled="auth.loading" style="justify-content: center;">
            <span v-if="auth.loading">Création...</span>
            <span v-else>Créer mon compte gratuit</span>
          </button>
        </form>

        <p class="text-center mt-3 text-sm text-muted">
          Déjà un compte ?
          <router-link to="/login" style="font-weight: 600;">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const form = reactive({
  nom: '', prenom: '', nom_entreprise: '',
  email: '', password: '', password_confirmation: '',
  plan: 'free',
})

async function handleRegister() {
  try {
    await auth.register(form)
    router.push('/')
  } catch {}
}
</script>
