<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <div class="auth-logo">G</div>
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
          <router-link to="/register" style="font-weight: 600;">Créer un compte</router-link>
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
      router.push('/')
    }
  } catch {}
}
</script>
