<template>
  <div class="app-layout">
    <!-- Overlay for mobile -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-logo">
        <img src="/logo.png" alt="GenyCom" style="height: 36px; object-fit: contain; margin-right: 0.5rem;" onerror="this.outerHTML='<div class=\'logo-icon\'>G</div>'" />
        <div class="logo-text-wrapper" style="display: flex; flex-direction: column; justify-content: center;">
          <div class="logo-text" style="line-height: 1;">Geny<span>Com</span></div>
          <div v-if="auth.user?.app_version" style="font-size: 0.65rem; color: #64748B; font-weight: 700; margin-top: 2px;">v{{ auth.user.app_version }}</div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <!-- Principal -->
        <div class="nav-section">
          <div class="nav-section-label">Principal</div>
          <router-link to="/dashboard" class="nav-item" active-class="active" exact>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <span>Tableau de bord</span>
          </router-link>
        </div>

        <!-- Gestion Commerciale -->
        <div class="nav-section">
          <div class="nav-section-label">Gestion Commerciale</div>
          <router-link to="/clients" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Clients</span>
          </router-link>
          <router-link to="/fournisseurs" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
            <span>Fournisseurs</span>
          </router-link>
          <router-link to="/produits" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <span>Produits</span>
          </router-link>
          <router-link to="/projets" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            <span>Projets</span>
          </router-link>
        </div>

        <!-- Ventes -->
        <div class="nav-section">
          <div class="nav-section-label">Ventes</div>
          <router-link to="/devis" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Devis</span>
          </router-link>
          <router-link to="/bons-commande-client" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
            <span>Commandes Client</span>
          </router-link>
          <router-link to="/bons-livraison" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polyline points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span>Bons de Livraison</span>
          </router-link>
          <router-link to="/factures" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Factures</span>
          </router-link>
          <router-link to="/contrats" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8"/><path d="M8 17h8"/><path d="M8 9h2"/></svg>
            <span>Abonnements</span>
          </router-link>
          <router-link to="/avoirs-clients" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 15h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 17"/><path d="m7 21-4-4 4-4"/><path d="M15 9h-2a2 2 0 1 0 0 4h3c.6 0 1.1-.2 1.4-.6L21 7"/><path d="m17 3 4 4-4 4"/></svg>
            <span>Avoirs Clients</span>
          </router-link>
        </div>

        <!-- Achats -->
        <div class="nav-section">
          <div class="nav-section-label">Achats</div>
          <router-link to="/commandes" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <span>Commandes</span>
          </router-link>
          <router-link to="/bons-reception" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></svg>
            <span>Bons de Réception</span>
          </router-link>
          <router-link to="/factures-achats" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Factures d'Achat</span>
          </router-link>
          <router-link to="/dettes" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Dettes Fournisseur</span>
          </router-link>
          <router-link to="/avoirs-fournisseurs" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 15h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 17"/><path d="m7 21-4-4 4-4"/><path d="M15 9h-2a2 2 0 1 0 0 4h3c.6 0 1.1-.2 1.4-.6L21 7"/><path d="m17 3 4 4-4 4"/></svg>
            <span>Avoirs Fournisseurs</span>
          </router-link>
        </div>

        <!-- Stock -->
        <div class="nav-section">
          <div class="nav-section-label">Stock</div>
          <router-link to="/stock" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span>Stock</span>
          </router-link>
        </div>

        <!-- Finances -->
        <div class="nav-section">
          <div class="nav-section-label">Finances</div>
          <router-link to="/depenses" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Dépenses</span>
          </router-link>
        </div>

        <!-- Paramétrage -->
        <div class="nav-section">
          <div class="nav-section-label">Système</div>
          <router-link to="/parametrage" class="nav-item" active-class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span>Paramétrage</span>
          </router-link>
        </div>
      </nav>

      <!-- User info bottom -->
      <div style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.05);">
        <div class="flex items-center gap-2">
          <div style="width: 34px; height: 34px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; color: white; flex-shrink: 0; box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);">
            {{ auth.user?.prenom?.[0] }}{{ auth.user?.nom?.[0] }}
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #F8FAFC;">{{ auth.fullName }}</div>
            <div style="font-size: 0.7rem; color: #94A3B8;">{{ auth.tenant?.nom }}</div>
          </div>
          <router-link to="/profile" class="btn btn-secondary btn-sm" style="padding: 0.35rem;" title="Mon Profil">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </router-link>
          <button @click="handleLogout" class="btn btn-secondary btn-sm" style="padding: 0.35rem;" title="Déconnexion">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
      <header class="main-header">
        <div style="display: flex; align-items: center;">
          <button class="mobile-toggle-btn" @click="sidebarOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
          <div class="page-title">{{ pageTitle }}</div>
        </div>
        <div class="header-actions">
          <NotificationBell />
          <button @click="toggleTheme" class="btn btn-secondary btn-sm" style="border-radius:50%; padding:0.4rem; height: 32px; width: 32px; display:flex; justify-content:center; align-items:center;" title="Basculer le thème">
            <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
          </button>
          <span class="text-sm text-muted" style="margin-left: 0.5rem">{{ currentDate }}</span>
        </div>
      </header>
      <main class="main-body">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import NotificationBell from '../components/shared/NotificationBell.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)
const theme = ref(localStorage.getItem('genycom_theme') || 'light')

// Fermer le sidebar lors du changement de route (mobile)
watch(() => route.path, () => {
  sidebarOpen.value = false
})

onMounted(() => {
  if (!auth.user?.app_version) {
    auth.fetchUser()
  }
})

// Apply stored theme on mount
document.documentElement.setAttribute('data-theme', theme.value)

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  document.documentElement.setAttribute('data-theme', theme.value)
  localStorage.setItem('genycom_theme', theme.value)
}

const pageTitle = computed(() => {
  const titles = {
    Dashboard: 'Tableau de bord',
    Clients: 'Gestion des Clients', Fournisseurs: 'Gestion des Fournisseurs',
    Produits: 'Catalogue Produits', Projets: 'Projets',
    Devis: 'Devis', Factures: 'Factures', Commandes: 'Commandes Fournisseur',
    BCCList: 'Bons de Commande Client', BCCDetail: 'Bon de Commande Client',
    BLList: 'Bons de Livraison', BLDetail: 'Bon de Livraison',
    BRList: 'Bons de Réception', BRDetail: 'Bon de Réception',
    FactureAchatList: 'Factures d\'Achat', FactureAchatDetail: 'Facture d\'Achat',
    DetteList: 'Dettes Fournisseur', DetteDetail: 'Dette Fournisseur',
    Stock: 'Gestion de Stock', Depenses: 'Dépenses', Parametrage: 'Paramétrage',
    Contrats: 'Abonnements & Contrats', ContratForm: 'Fiche Contrat',
  }
  return titles[route.name] || route.name || 'GenyCom'
})

const currentDate = computed(() => {
  return new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
