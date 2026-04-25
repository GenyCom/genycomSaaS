import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/main.css'

// Apply saved theme (or default to light) before first render
document.documentElement.setAttribute('data-theme', localStorage.getItem('genycom_theme') || 'light')

import api from './services/api'

const app = createApp(App)

// --- Monitoring Global Frontend ---
app.config.errorHandler = (err, instance, info) => {
  console.error('[Vue Error Monitor]', err, info)
  
  // Envoi asynchrone pour ne pas bloquer l'UI
  setTimeout(() => {
    try {
      api.post('/monitoring/frontend-error', {
        message: err.message || String(err),
        stack: err.stack,
        url: window.location.href,
        component: instance?.$options?.name || 'Unknown',
        info: info
      }).catch(() => {}) // Silencieux si le reporting échoue
    } catch (e) {}
  }, 500)
}

window.addEventListener('unhandledrejection', event => {
  const err = event.reason
  try {
    api.post('/monitoring/frontend-error', {
      message: err?.message || String(err),
      stack: err?.stack || 'Promesse rejetée (pas de stack)',
      url: window.location.href,
      component: 'Global Promise Rejection',
      info: 'unhandledrejection'
    }).catch(() => {})
  } catch (e) {}
})

app.use(createPinia())
app.use(router)
app.mount('#app')
