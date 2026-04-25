<template>
  <div class="app-layout sa-layout">
    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-logo">
        <div class="logo-icon sa-theme-icon">SA</div>
        <div class="logo-text">Super<span>Admin</span></div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-section-label">Central</div>
          <router-link to="/superadmin" class="nav-item sa-item" active-class="active" exact>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Tableau de bord</span>
          </router-link>
          
          <router-link to="/superadmin/tenants" class="nav-item sa-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <span>Bases SaaS (Tenants)</span>
          </router-link>

          <router-link to="/superadmin/users" class="nav-item sa-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Utilisateurs Globaux</span>
          </router-link>
        </div>
      </nav>

      <div class="sidebar-footer">
        <div class="user-chip sa-chip">
          <div class="user-avatar sa-avatar">AD</div>
          <div class="user-info">
            <div class="user-name">Super Admin</div>
            <div class="user-tenant">Accès Racine</div>
          </div>
        </div>
        <div class="footer-actions">
           <router-link to="/superadmin/profile" class="footer-btn" title="Mon Profil">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
           </router-link>
           <button @click="logout" class="footer-btn logout" title="Quitter la console">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
           </button>
        </div>
      </div>
    </aside>

    <div class="main-content">
      <header class="main-header sa-header">
        <div class="header-left">
          <div class="page-title text-warning">
             <span class="sa-indicator"></span>
             {{ route.name || 'Console SuperAdmin' }}
          </div>
        </div>
        <div class="header-right">
          <div class="sa-badge-status">ROOT ACCESS</div>
          <span class="date-display">{{ currentDate }}</span>
        </div>
      </header>
      <main class="main-body">
        <router-view v-slot="{ Component }">
          <transition name="page-fade" mode="out-in">
            <component :is="Component" :key="$route.path" />
          </transition>
        </router-view>
      </main>
    </div>

    <!-- Modal de Déconnexion -->
    <ConfirmModal 
      :show="showLogoutConfirm"
      title="Quitter la console"
      message="Voulez-vous vraiment quitter la console SuperAdmin ? Toute session active sera fermée."
      confirmText="Déconnexion"
      @confirm="executeLogout"
      @cancel="showLogoutConfirm = false"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import ConfirmModal from '../components/shared/ConfirmModal.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const sidebarOpen = ref(false)
const showLogoutConfirm = ref(false)

const currentDate = computed(() => {
  return new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

function logout() {
  showLogoutConfirm.value = true
}

async function executeLogout() {
  showLogoutConfirm.value = false
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
/* ── Layout Structure ── */
.app-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background-color: #F7F8FA;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

.main-body {
  flex: 1;
  overflow-y: auto;
  position: relative;
  padding: 0; /* Les vues internes gèrent leur propre padding pour le Hero Header */
}

/* ── Sidebar Style (Premium SA) ── */
.sidebar {
  width: 260px;
  background-color: #1E293B;
  color: #fff;
  display: flex;
  flex-direction: column;
}

.sidebar-logo {
  height: 64px;
  padding: 0 24px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}

.sa-theme-icon {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.75rem;
  margin-right: 12px;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.logo-text { font-weight: 700; font-size: 1.1rem; color: #fff; letter-spacing: -0.5px; }
.logo-text span { color: #f59e0b; }

/* ── Navigation ── */
.sidebar-nav { flex: 1; padding: 24px 0; overflow-y: auto; }
.nav-section { margin-bottom: 24px; padding: 0 16px; }
.nav-section-label { 
  font-size: 0.65rem; 
  text-transform: uppercase; 
  letter-spacing: 0.1em; 
  color: #64748B; 
  margin-bottom: 12px; 
  padding-left: 12px;
  font-weight: 700;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  color: #94A3B8;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s;
  margin-bottom: 4px;
}

.nav-item:hover { background-color: rgba(255,255,255,0.05); color: #fff; }
.nav-item.active { background-color: #f59e0b; color: #fff; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25); }

/* ── Footer Sidebar ── */
.sidebar-footer { padding: 16px; border-top: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.1); }
.user-chip { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.sa-avatar { 
  width: 34px; height: 34px; background: #334155; border: 1.5px solid #f59e0b; border-radius: 50%; 
  display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #f59e0b;
}
.user-info { flex: 1; min-width: 0; }
.user-name { font-size: 0.8rem; font-weight: 600; color: #fff; }
.user-tenant { font-size: 0.65rem; color: #64748B; font-weight: 500; }

.footer-actions { display: flex; gap: 8px; }
.footer-btn { 
  flex: 1; display: flex; justify-content: center; align-items: center; padding: 8px; 
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); 
  border-radius: 8px; color: #94A3B8; text-decoration: none; transition: all 0.2s;
}
.footer-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }
.footer-btn.logout:hover { border-color: #ef4444; color: #ef4444; }

/* ── Header ── */
.main-header {
  height: 64px;
  background: #fff;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  flex-shrink: 0;
}

.page-title { 
  font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;
}
.sa-indicator { width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 8px #f59e0b; }
.text-warning { color: #d97706 !important; }

.sa-badge-status { 
  padding: 4px 10px; background: #FEF3C7; color: #D97706; border-radius: 100px; 
  font-size: 0.65rem; font-weight: 800; margin-right: 12px;
}
.date-display { font-size: 0.8rem; color: #64748B; font-weight: 500; }

/* ── Transitions Anti-Flash ── */
.page-fade-enter-active, .page-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.page-fade-enter-from { opacity: 0; transform: translateY(5px); }
.page-fade-leave-to { opacity: 0; transform: translateY(-5px); }
</style>