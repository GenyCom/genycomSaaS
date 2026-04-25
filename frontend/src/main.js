import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/main.css'

// Apply saved theme (or default to light) before first render
document.documentElement.setAttribute('data-theme', localStorage.getItem('genycom_theme') || 'light')

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')
