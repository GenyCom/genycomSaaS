import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/superadmin',
    component: () => import('../layouts/SuperAdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'SuperAdminHome', component: () => import('../views/superadmin/TenantsManager.vue') },
      { path: 'tenants', name: 'SuperAdminTenants', component: () => import('../views/superadmin/TenantsManager.vue') },
      { path: 'tenants/:id', name: 'SuperAdminTenantDetail', component: () => import('../views/superadmin/TenantDetail.vue'), props: true },
      { path: 'users', name: 'SuperAdminUsers', component: () => import('../views/superadmin/GlobalUsersList.vue') },
      { path: 'users/create', name: 'SuperAdminUserCreate', component: () => import('../views/superadmin/GlobalUserForm.vue') },
      { path: 'users/:id/edit', name: 'SuperAdminUserEdit', component: () => import('../views/superadmin/GlobalUserForm.vue'), props: true }
    ],
  },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '',              name: 'Dashboard',    component: () => import('../views/Dashboard.vue') },
      { path: 'clients',       name: 'Clients',      component: () => import('../views/clients/ClientList.vue') },
      { path: 'clients/create', name: 'ClientCreate', component: () => import('../views/clients/ClientForm.vue') },
      { path: 'clients/:id',   name: 'ClientDetail', component: () => import('../views/clients/ClientDetail.vue'), props: true },
      { path: 'fournisseurs',  name: 'Fournisseurs', component: () => import('../views/fournisseurs/FournisseurList.vue') },
      { path: 'fournisseurs/create', name: 'FournisseurCreate', component: () => import('../views/fournisseurs/FournisseurForm.vue') },
      { path: 'fournisseurs/:id',   name: 'FournisseurDetail', component: () => import('../views/fournisseurs/FournisseurDetail.vue'), props: true },
      { path: 'produits',      name: 'Produits',     component: () => import('../views/produits/ProduitList.vue') },
      { path: 'produits/:id',  name: 'ProduitDetail',component: () => import('../views/produits/ProduitDetail.vue'), props: true },
      { path: 'devis',         name: 'Devis',        component: () => import('../views/ventes/DevisList.vue') },
      { path: 'devis/:id',     name: 'DevisDetail',  component: () => import('../views/ventes/DevisDetail.vue'), props: true },
      { path: 'factures',      name: 'Factures',     component: () => import('../views/factures/FactureList.vue') },
      { path: 'factures/:id',  name: 'FactureDetail',component: () => import('../views/factures/FactureDetail.vue'), props: true },
      { path: 'commandes',     name: 'Commandes',    component: () => import('../views/achats/CommandeList.vue') },
      { path: 'commandes/:id', name: 'CommandeDetail', component: () => import('../views/achats/CommandeDetail.vue'), props: true },
      { path: 'stock',         name: 'Stock',        component: () => import('../views/stock/StockList.vue') },
      { path: 'projets',       name: 'Projets',      component: () => import('../views/projets/ProjetList.vue') },
      { path: 'projets/create', name: 'ProjetCreate', component: () => import('../views/projets/ProjetForm.vue') },
      { path: 'projets/:id',   name: 'ProjetDetail', component: () => import('../views/projets/ProjetDetail.vue'), props: true },
      { path: 'depenses',      name: 'Depenses',     component: () => import('../views/finances/DepenseList.vue') },
      { path: 'depenses/:id',  name: 'DepenseDetail',component: () => import('../views/finances/DepenseDetail.vue'), props: true },
      { path: 'avoirs-clients', name: 'AvoirsClients', component: () => import('../views/ventes/AvoirClientList.vue') },
      { path: 'avoirs-clients/:id', name: 'AvoirClientDetail', component: () => import('../views/ventes/AvoirClientDetail.vue'), props: true },
      { path: 'avoirs-fournisseurs', name: 'AvoirsFournisseurs', component: () => import('../views/achats/AvoirFournisseurList.vue') },
      { path: 'avoirs-fournisseurs/:id', name: 'AvoirFournisseurDetail', component: () => import('../views/achats/AvoirFournisseurDetail.vue'), props: true },
      { path: 'stock/:id/historique', name: 'StockHistorique', component: () => import('../views/stock/StockHistorique.vue'), props: true },
      { path: 'parametrage',   name: 'Parametrage',  component: () => import('../views/parametrage/ParametrageIndex.vue') },
    ],
  },
  {
    path: '/print/:type/:id',
    name: 'DocumentPrint',
    component: () => import('../views/shared/DocumentPrint.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  
  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      return next({ name: 'Login' })
    }
    // Sécurité de cloisonnement des périmètres :
    if (auth.user?.is_superadmin && !to.path.startsWith('/superadmin')) {
      return next({ name: 'SuperAdminHome' })
    }
    if (!auth.user?.is_superadmin && to.path.startsWith('/superadmin')) {
      return next({ name: 'Dashboard' })
    }
  }
  
  if (to.meta.guest && auth.isAuthenticated) {
    if (auth.user?.is_superadmin) {
      return next({ name: 'SuperAdminHome' })
    }
    return next({ name: 'Dashboard' })
  }
  
  next()
})

export default router
