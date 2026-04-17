<template>
  <div class="app-layout">
    <aside class="sidebar" style="background-color: var(--bg-card);">
      <div class="sidebar-logo">
        <div class="logo-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">SA</div>
        <div class="logo-text">Super<span>Admin</span></div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-section-label">Central</div>
          <router-link to="/superadmin" class="nav-item" active-class="active" exact>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Accueil Système</span>
          </router-link>
          <router-link to="/superadmin/tenants" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <span>Bases SaaS (Tenants)</span>
          </router-link>
          <router-link to="/superadmin/users" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Utilisateurs Globaux</span>
          </router-link>
        </div>
      </nav>
      <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color);">
        <button @click="logout" class="btn btn-secondary w-full" style="justify-content:center;">Déconnexion Centrale</button>
      </div>
    </aside>

    <div class="main-content">
      <header class="main-header" style="border-bottom-color: rgba(245, 158, 11, 0.2);">
        <div class="page-title text-warning">{{ route.name || 'Console SuperAdmin' }}</div>
      </header>
      <main class="main-body">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>
