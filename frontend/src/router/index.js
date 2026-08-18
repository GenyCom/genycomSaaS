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
    path: '/print/ticket/:id',
    name: 'FactureTicket',
    component: () => import('../views/factures/FactureTicket.vue'),
    meta: { requiresAuth: true }
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
      { path: '', name: 'SuperAdminHome', component: () => import('../views/superadmin/SuperAdminDashboard.vue') },
      { path: 'tenants', name: 'SuperAdminTenants', component: () => import('../views/superadmin/TenantsManager.vue') },
      { path: 'tenants/:id', name: 'SuperAdminTenantDetail', component: () => import('../views/superadmin/TenantDetail.vue'), props: true },
      { path: 'users', name: 'SuperAdminUsers', component: () => import('../views/superadmin/GlobalUsersList.vue') },
      { path: 'users/create', name: 'SuperAdminUserCreate', component: () => import('../views/superadmin/GlobalUserForm.vue') },
      { path: 'users/:id/edit', name: 'SuperAdminUserEdit', component: () => import('../views/superadmin/GlobalUserForm.vue'), props: true },
      { path: 'profile', name: 'SuperAdminProfile', component: () => import('../views/shared/ProfileSettings.vue') }
    ],
  },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '',              redirect: '/login' },
      { path: 'dashboard',     name: 'Dashboard',    component: () => import('../views/Dashboard.vue'), meta: { permission: 'dashboard.view' } },
      { path: 'clients',       name: 'Clients',      component: () => import('../views/clients/ClientList.vue'), meta: { permission: 'clients.view' } },
      { path: 'clients/create', name: 'ClientCreate', component: () => import('../views/clients/ClientForm.vue'), meta: { permission: 'clients.create' } },
      { path: 'clients/:id/edit', name: 'ClientEdit', component: () => import('../views/clients/ClientForm.vue'), props: true, meta: { permission: 'clients.edit' } },
      { path: 'clients/:id',   name: 'ClientDetail', component: () => import('../views/clients/ClientDetail.vue'), props: true, meta: { permission: 'clients.view' } },
      
      { path: 'fournisseurs',  name: 'Fournisseurs', component: () => import('../views/fournisseurs/FournisseurList.vue'), meta: { permission: 'fournisseurs.view' } },
      { path: 'fournisseurs/create', name: 'FournisseurCreate', component: () => import('../views/fournisseurs/FournisseurForm.vue'), meta: { permission: 'fournisseurs.create' } },
      { path: 'fournisseurs/:id/edit', name: 'FournisseurEdit', component: () => import('../views/fournisseurs/FournisseurForm.vue'), props: true, meta: { permission: 'fournisseurs.edit' } },
      { path: 'fournisseurs/:id',   name: 'FournisseurDetail', component: () => import('../views/fournisseurs/FournisseurDetail.vue'), props: true, meta: { permission: 'fournisseurs.view' } },
      
      { path: 'produits',      name: 'Produits',     component: () => import('../views/produits/ProduitList.vue'), meta: { permission: 'produits.view' } },
      { path: 'produits/new',  name: 'ProduitCreate', component: () => import('../views/produits/ProduitForm.vue'), props: { isNew: true }, meta: { permission: 'produits.create' } },
      { path: 'produits/:id/edit', name: 'ProduitEdit', component: () => import('../views/produits/ProduitForm.vue'), props: true, meta: { permission: 'produits.edit' } },
      { path: 'produits/:id',  name: 'ProduitDetail',component: () => import('../views/produits/ProduitDetail.vue'), props: true, meta: { permission: 'produits.view' } },
      { path: 'produits/fini/new', name: 'ProduitFiniCreate', component: () => import('../views/produits/ProduitFiniForm.vue'), props: { isNew: true }, meta: { permission: 'produits.create' } },
      { path: 'produits/fini/:id/edit', name: 'ProduitFiniEdit', component: () => import('../views/produits/ProduitFiniForm.vue'), props: true, meta: { permission: 'produits.edit' } },
      { path: 'produits/fini/:id', name: 'ProduitFiniDetail', component: () => import('../views/produits/ProduitFiniDetail.vue'), props: true, meta: { permission: 'produits.view' } },
      { path: 'devis',         name: 'Devis',        component: () => import('../views/ventes/DevisList.vue'), meta: { permission: 'devis.view' } },
      { path: 'devis/:id',     name: 'DevisDetail',  component: () => import('../views/ventes/DevisDetail.vue'), props: true, meta: { permission: 'devis.view' } },
      { path: 'factures',      name: 'Factures',     component: () => import('../views/factures/FactureList.vue'), meta: { permission: 'factures.view' } },
      { path: 'factures/:id',  name: 'FactureDetail',component: () => import('../views/factures/FactureDetail.vue'), props: true, meta: { permission: 'factures.view' } },
      { path: 'contrats',       name: 'Contrats',       component: () => import('../views/contrats/ContratList.vue'), meta: { permission: 'contrats.view' } },
      { path: 'contrats/create',name: 'ContratForm',    component: () => import('../views/contrats/ContratForm.vue'), meta: { permission: 'contrats.create' } },
      { path: 'contrats/:id/edit',name:'ContratEdit',   component: () => import('../views/contrats/ContratForm.vue'), props: true, meta: { permission: 'contrats.edit' } },
      { path: 'commandes',     name: 'Commandes',    component: () => import('../views/achats/CommandeList.vue'), meta: { permission: 'commandes.view' } },
      { path: 'commandes/:id', name: 'CommandeDetail', component: () => import('../views/achats/CommandeDetail.vue'), props: true, meta: { permission: 'commandes.view' } },
      
      { path: 'bons-reception',     name: 'BRList',    component: () => import('../views/achats/BRList.vue'), meta: { permission: 'bons-reception.view' } },
      { path: 'bons-reception/:id', name: 'BRDetail',  component: () => import('../views/achats/BRDetail.vue'), props: true, meta: { permission: 'bons-reception.view' } },
      
      { path: 'factures-achats',     name: 'FactureAchatList',    component: () => import('../views/achats/FactureAchatList.vue'), meta: { permission: 'factures-achats.view' } },
      { path: 'factures-achats/:id', name: 'FactureAchatDetail',  component: () => import('../views/achats/FactureAchatDetail.vue'), props: true, meta: { permission: 'factures-achats.view' } },

      { path: 'dettes',     name: 'DetteList',    component: () => import('../views/achats/DetteList.vue'), meta: { permission: 'dettes.view' } },
      { path: 'dettes/:id', name: 'DetteDetail',  component: () => import('../views/achats/DetteDetail.vue'), props: true, meta: { permission: 'dettes.view' } },
      
      { path: 'bons-commande-client',     name: 'BCCList',    component: () => import('../views/ventes/BCCList.vue'), meta: { permission: 'bons-commande-client.view' } },
      { path: 'bons-commande-client/:id', name: 'BCCDetail',  component: () => import('../views/ventes/BCCDetail.vue'), props: true, meta: { permission: 'bons-commande-client.view' } },
      
      { path: 'bons-livraison',     name: 'BLList',    component: () => import('../views/ventes/BLList.vue'), meta: { permission: 'bons-livraison.view' } },
      { path: 'bons-livraison/:id', name: 'BLDetail',  component: () => import('../views/ventes/BLDetail.vue'), props: true, meta: { permission: 'bons-livraison.view' } },
      
      { path: 'stock',         name: 'Stock',        component: () => import('../views/stock/StockList.vue'), meta: { permission: 'stock.view' } },
      { path: 'projets',       name: 'Projets',      component: () => import('../views/projets/ProjetList.vue'), meta: { permission: 'projets.view' } },
      { path: 'projets/create', name: 'ProjetCreate', component: () => import('../views/projets/ProjetForm.vue'), meta: { permission: 'projets.create' } },
      { path: 'projets/:id/edit', name: 'ProjetEdit', component: () => import('../views/projets/ProjetForm.vue'), props: true, meta: { permission: 'projets.edit' } },
      { path: 'projets/:id',   name: 'ProjetDetail', component: () => import('../views/projets/ProjetDetail.vue'), props: true, meta: { permission: 'projets.view' } },
      { path: 'depenses',      name: 'Depenses',     component: () => import('../views/DepenseList.vue'), meta: { permission: 'depenses.view' } },
      { path: 'depenses/:id',  name: 'DepenseDetail',component: () => import('../views/DepenseDetail.vue'), props: true, meta: { permission: 'depenses.view' } },
      { path: 'avoirs-clients', name: 'AvoirsClients', component: () => import('../views/ventes/AvoirClientList.vue'), meta: { permission: 'avoirs-clients.view' } },
      { path: 'avoirs-clients/:id', name: 'AvoirClientDetail', component: () => import('../views/ventes/AvoirClientDetail.vue'), props: true, meta: { permission: 'avoirs-clients.view' } },
      { path: 'avoirs-fournisseurs', name: 'AvoirsFournisseurs', component: () => import('../views/achats/AvoirFournisseurList.vue'), meta: { permission: 'avoirs-fournisseurs.view' } },
      { path: 'avoirs-fournisseurs/:id', name: 'AvoirFournisseurDetail', component: () => import('../views/achats/AvoirFournisseurDetail.vue'), props: true, meta: { permission: 'avoirs-fournisseurs.view' } },
      { path: 'stock/:id/historique', name: 'StockHistorique', component: () => import('../views/stock/StockHistorique.vue'), props: true, meta: { permission: 'stock.view' } },
      { path: 'parametrage',   name: 'Parametrage',  component: () => import('../views/parametrage/ParametrageIndex.vue'), meta: { permission: 'parametrage.view' } },
      { path: 'profile',       name: 'ProfileSettings', component: () => import('../views/shared/ProfileSettings.vue') },
      { path: 'reporting',     name: 'Reporting',    component: () => import('../views/reporting/Reporting.vue'), meta: { permission: 'reporting.view' } },
      { path: 'caisse',        name: 'Caisse',       component: () => import('../views/reporting/Caisse.vue'), meta: { permission: 'caisse.view' } },
    ],
  },
  {
    path: '/print/:type/:id',
    name: 'DocumentPrint',
    component: () => import('../views/shared/DocumentPrint.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/print-report/cash-flow',
    name: 'ReportingPrint',
    component: () => import('../views/reporting/ReportingPrint.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/print-report/unpaid-statement',
    name: 'UnpaidStatementPrint',
    component: () => import('../views/reporting/UnpaidStatementPrint.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
  },
]

export function getFirstAccessiblePath(auth) {
  if (!auth.user) return '/login'
  if (auth.user.is_superadmin) return '/superadmin'
  
  const priorityRoutes = [
    { perm: 'dashboard.view', path: '/dashboard' },
    { perm: 'clients.view', path: '/clients' },
    { perm: 'devis.view', path: '/devis' },
    { perm: 'factures.view', path: '/factures' },
    { perm: 'produits.view', path: '/produits' },
    { perm: 'fournisseurs.view', path: '/fournisseurs' },
    { perm: 'commandes.view', path: '/commandes' },
    { perm: 'bons-commande-client.view', path: '/bons-commande-client' },
    { perm: 'bons-livraison.view', path: '/bons-livraison' },
    { perm: 'bons-reception.view', path: '/bons-reception' },
    { perm: 'factures-achats.view', path: '/factures-achats' },
    { perm: 'stock.view', path: '/stock' },
    { perm: 'projets.view', path: '/projets' },
    { perm: 'depenses.view', path: '/depenses' },
    { perm: 'contrats.view', path: '/contrats' },
    { perm: 'reporting.view', path: '/reporting' },
    { perm: 'caisse.view', path: '/caisse' },
    { perm: 'parametrage.view', path: '/parametrage' },
  ]
  
  for (const item of priorityRoutes) {
    if (auth.hasPermission(item.perm)) {
      return item.path
    }
  }
  
  return '/profile'
}

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  
  if (to.meta.requiresAuth || to.matched.some(record => record.meta.requiresAuth)) {
    if (!auth.isAuthenticated) {
      return next({ name: 'Login' })
    }
    // Sécurité de cloisonnement des périmètres :
    if (auth.user?.is_superadmin && !to.path.startsWith('/superadmin')) {
      return next({ name: 'SuperAdminHome' })
    }
    if (!auth.user?.is_superadmin && to.path.startsWith('/superadmin')) {
      return next(getFirstAccessiblePath(auth))
    }
  }

  // Contrôle des habilitations granulaires :
  if (to.meta.permission && !auth.hasPermission(to.meta.permission)) {
    const fallbackPath = getFirstAccessiblePath(auth)
    
    // Éviter boucle infinie si la route cible est déjà la fallback ou non permise
    if (to.path === fallbackPath) {
      return next('/profile')
    }

    if (from.name && from.name !== 'Login') {
      alert(`Accès refusé. Vous n'avez pas l'habilitation requise (${to.meta.permission}).`)
      return next(false)
    } else {
      return next(fallbackPath)
    }
  }
  
  next()
})


export default router
