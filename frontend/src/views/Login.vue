<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <img src="/logo.png" alt="GenyCom Logo" style="height: 64px; object-fit: contain; margin-bottom: 0.5rem; display: block; margin-left: auto; margin-right: auto;" onerror="this.outerHTML='<div class=\'auth-logo\'>G</div>'" />
          <h1>Geny<span style="color: var(--accent)">Com</span></h1>
          <p>Connectez-vous à votre espace</p>
        </div>

        <div v-if="auth.error" class="auth-error">{{ auth.error }}</div>

        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-input" placeholder="votre@email.com" required autofocus />
          </div>
          <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input v-model="form.password" type="password" class="form-input" placeholder="••••••••" required />
          </div>
          <button type="submit" class="btn btn-primary w-full btn-lg" :disabled="auth.loading" style="justify-content: center; margin-top: 0.5rem;">
            <span v-if="auth.loading">Connexion...</span>
            <span v-else>Se connecter</span>
          </button>
        </form>
        <p class="text-center mt-3 text-sm text-muted">
		  Pas encore de compte ?
		  <span style="font-weight: 600; color: #cbd5e0; cursor: not-allowed; opacity: 0.6;">Créer un compte</span>
		</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const form = reactive({ email: '', password: '' })

onMounted(() => {
  // Always clear any existing session when landing on the login page
  auth.token = null
  auth.user = null
  localStorage.removeItem('genycom_token')
  localStorage.removeItem('genycom_user')

  const lastEmail = localStorage.getItem('genycom_last_email')
  if (lastEmail) {
    form.email = lastEmail
  }
})

async function handleLogin() {
  try {
    await auth.login(form.email, form.password)
    localStorage.setItem('genycom_last_email', form.email)
    if (auth.user?.is_superadmin) {
      router.push('/superadmin')
    } else {
      router.push('/dashboard')
    }
  } catch {}
}
</script>
