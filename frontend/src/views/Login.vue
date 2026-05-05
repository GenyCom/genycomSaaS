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
            <div class="password-wrapper">
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-input" placeholder="••••••••" required />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword" tabindex="-1" title="Afficher / Masquer">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
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
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const form = reactive({ email: '', password: '' })
const showPassword = ref(false)

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

<style scoped>
.password-wrapper {
  position: relative;
}
.password-wrapper .form-input {
  padding-right: 44px;
}
.toggle-password {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted, #94A3B8);
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: color 0.2s, background 0.2s;
}
.toggle-password:hover {
  color: var(--accent, #3B82F6);
  background: var(--accent-subtle, rgba(59, 130, 246, 0.1));
}
</style>
