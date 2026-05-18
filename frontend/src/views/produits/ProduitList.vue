<template>
  <div class="product-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement du catalogue…</p>
      </div>
    </Transition>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

    <!-- Modal de confirmation élégant -->
    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le Produit"
      message="Êtes-vous sûr de vouloir supprimer ce produit du catalogue ? Cette action est irréversible."
      confirmText="Supprimer le produit"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Inventaire</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Catalogue Produits</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/produits/new" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouveau Produit</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>PR</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Gestion des stocks
        </div>
        <h1 class="hero-name">Catalogue Produits</h1>
        <p class="hero-sub"><strong>{{ produits.length }}</strong> références enregistrées dans votre base.</p>
      </div>
    </div>

    <!-- Advanced Filters -->
    <div class="filters-card">
      <div class="quick-tabs">
        <button 
          v-for="tab in ['Tous', 'Actifs', 'Inactifs', 'Stock faible']" 
          :key="tab"
          class="tab-btn"
          :class="{ active: currentTab === tab }"
          @click="currentTab = tab"
        >
          {{ tab }}
        </button>
      </div>

      <div class="advanced-filters">
        <div class="search-wrapper">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" placeholder="Rechercher par référence, désignation..." />
        </div>
        
        <div class="filter-group">
          <select v-model="filters.famille_id" class="filter-select">
            <option value="">Toutes les familles</option>
            <option v-for="fam in familles" :key="fam.id" :value="fam.id">{{ fam.libelle }}</option>
          </select>
          
          <select v-model="filters.type" class="filter-select">
            <option value="">Tous les types</option>
            <option value="produit">Produits</option>
            <option value="service">Services</option>
          </select>

          <select v-model="filters.etat" class="filter-select">
            <option value="">Tous les états</option>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>

          <div class="price-range">
            <input type="number" v-model="filters.prixMin" placeholder="Prix Min" class="filter-input-small" />
            <span>-</span>
            <input type="number" v-model="filters.prixMax" placeholder="Prix Max" class="filter-input-small" />
          </div>
        </div>
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th @click="handleSort('reference')" class="sortable">
                Référence
                <span class="sort-icon" v-if="sortBy === 'reference'">{{ sortDesc ? '↓' : '↑' }}</span>
              </th>
              <th @click="handleSort('designation')" class="sortable">
                Désignation
                <span class="sort-icon" v-if="sortBy === 'designation'">{{ sortDesc ? '↓' : '↑' }}</span>
              </th>
              <th>Type</th>
              <th @click="handleSort('prix')" class="text-right sortable">
                Prix Vente HT
                <span class="sort-icon" v-if="sortBy === 'prix'">{{ sortDesc ? '↓' : '↑' }}</span>
              </th>
              <th @click="handleSort('stock')" class="text-center sortable">
                Stock
                <span class="sort-icon" v-if="sortBy === 'stock'">{{ sortDesc ? '↓' : '↑' }}</span>
              </th>
              <th class="text-center">État</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(groupProduits, familleName) in groupedProduits" :key="familleName">
              <tr class="group-header" @click="toggleGroup(familleName)">
                <td colspan="7">
                  <div class="group-header-content">
                    <svg :class="{ 'rotated': collapsedGroups[familleName] }" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    <strong>{{ familleName }}</strong>
                    <span class="group-count">{{ groupProduits.length }} produit(s)</span>
                  </div>
                </td>
              </tr>
              
              <template v-if="!collapsedGroups[familleName]">
                <tr v-for="produit in groupProduits" :key="produit.id" class="table-row">
                  <td>
                    <span class="product-ref-badge">{{ produit.reference }}</span>
                  </td>
                  <td class="designation-cell">
                    <div class="product-name">{{ produit.designation }}</div>
                  </td>
                  <td>
                    <span class="type-pill" :class="produit.is_service ? 'service' : 'goods'">
                      {{ produit.is_service ? 'SERVICE' : 'PRODUIT' }}
                    </span>
                  </td>
                  <td class="text-right">
                    <div class="price-cell">
                      {{ formatMoney(produit.prix_ht_vente) }}
                      <span class="currency">DH</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <div v-if="produit.is_service" class="stock-n-a">-</div>
                    <div v-else class="stock-display" :class="{ 'stock-low': produit.stock_actuel <= (produit.stock_min || produit.seuil_alerte || 0) }">
                      <span class="stock-actual">{{ formatNumber(produit.stock_actuel) }}</span>
                      <span class="stock-separator">/</span>
                      <span class="stock-min" title="Stock Min">{{ formatNumber(produit.stock_min || 0) }}</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="status-indicator" :class="produit.is_actif ? 'active' : 'inactive'">
                      {{ produit.is_actif ? 'Actif' : 'Inactif' }}
                    </span>
                  </td>
                  <td class="text-right">
                    <div class="actions-group">
                      <router-link :to="`/produits/${produit.id}`" class="action-btn view" title="Consulter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      </router-link>
                      <router-link :to="`/produits/${produit.id}/edit`" class="action-btn edit" title="Modifier">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </router-link>
                      <button @click="deleteProduit(produit.id)" class="action-btn delete" title="Supprimer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </template>
            <tr v-if="Object.keys(groupedProduits).length === 0">
              <td colspan="7" class="empty-row">
                <div class="empty-content">
                  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" color="var(--c-border-mid)"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                  <p>Aucun produit ne correspond à votre recherche.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '../../services/api'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const produits = ref([])
const familles = ref([])
const search = ref('')
const loading = ref(true)

// Quick tabs
const currentTab = ref('Tous')

// Advanced Filters
const filters = reactive({
  famille_id: '',
  type: '',
  etat: '',
  prixMin: null,
  prixMax: null
})

// Sort state
const sortBy = ref('designation')
const sortDesc = ref(false)

// Group collapse state
const collapsedGroups = reactive({})

const toast = reactive({ show: false, message: '', type: 'success' })
const showConfirm = ref(false)
const itemToDelete = ref(null)

function showToast(message, type = 'success') {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 4000)
}

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatNumber(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { maximumFractionDigits: 2 })
}

function deleteProduit(id) {
  itemToDelete.value = id
  showConfirm.value = true
}

async function confirmDelete() {
  const id = itemToDelete.value
  if (!id) return
  
  showConfirm.value = false
  loading.value = true
  try {
    await api.delete(`/produits/${id}`)
    produits.value = produits.value.filter(p => p.id !== id)
    showToast('Produit supprimé avec succès !', 'success')
  } catch (error) {
    showToast(error.response?.data?.message || 'Erreur lors de la suppression.', 'error')
  } finally {
    loading.value = false
    itemToDelete.value = null
  }
}

function toggleGroup(famName) {
  collapsedGroups[famName] = !collapsedGroups[famName]
}

function handleSort(col) {
  if (sortBy.value === col) {
    sortDesc.value = !sortDesc.value
  } else {
    sortBy.value = col
    sortDesc.value = false
  }
}

const processedProduits = computed(() => {
  let result = produits.value

  // Tabs
  if (currentTab.value === 'Actifs') {
    result = result.filter(p => p.is_actif)
  } else if (currentTab.value === 'Inactifs') {
    result = result.filter(p => !p.is_actif)
  } else if (currentTab.value === 'Stock faible') {
    result = result.filter(p => !p.is_service && p.stock_actuel <= (p.stock_min || p.seuil_alerte || 0))
  }

  // Advanced Filters
  if (filters.famille_id) {
    result = result.filter(p => p.famille_id == filters.famille_id)
  }
  if (filters.type === 'service') {
    result = result.filter(p => p.is_service)
  } else if (filters.type === 'produit') {
    result = result.filter(p => !p.is_service)
  }
  if (filters.etat === 'actif') {
    result = result.filter(p => p.is_actif)
  } else if (filters.etat === 'inactif') {
    result = result.filter(p => !p.is_actif)
  }
  if (filters.prixMin !== null && filters.prixMin !== '') {
    result = result.filter(p => parseFloat(p.prix_ht_vente) >= parseFloat(filters.prixMin))
  }
  if (filters.prixMax !== null && filters.prixMax !== '') {
    result = result.filter(p => parseFloat(p.prix_ht_vente) <= parseFloat(filters.prixMax))
  }

  // Search
  if (search.value) {
    const s = search.value.toLowerCase()
    result = result.filter(p =>
      (p.designation || '').toLowerCase().includes(s) ||
      (p.reference || '').toLowerCase().includes(s)
    )
  }

  // Sort
  result.sort((a, b) => {
    let valA = a[sortBy.value]
    let valB = b[sortBy.value]

    if (sortBy.value === 'stock') {
      valA = a.is_service ? -9999999 : (parseFloat(a.stock_actuel) || 0)
      valB = b.is_service ? -9999999 : (parseFloat(b.stock_actuel) || 0)
    }
    if (sortBy.value === 'prix') {
      valA = parseFloat(a.prix_ht_vente) || 0
      valB = parseFloat(b.prix_ht_vente) || 0
    }

    if (typeof valA === 'string') valA = valA.toLowerCase()
    if (typeof valB === 'string') valB = valB.toLowerCase()

    if (valA < valB) return sortDesc.value ? 1 : -1
    if (valA > valB) return sortDesc.value ? -1 : 1
    return 0
  })

  return result
})

const groupedProduits = computed(() => {
  const groups = {}
  processedProduits.value.forEach(p => {
    const famName = p.famille?.libelle || 'Sans famille'
    if (!groups[famName]) {
      groups[famName] = []
      if (collapsedGroups[famName] === undefined) {
        collapsedGroups[famName] = false
      }
    }
    groups[famName].push(p)
  })
  
  const orderedGroups = {}
  Object.keys(groups).sort().forEach(key => {
    orderedGroups[key] = groups[key]
  })
  return orderedGroups
})

onMounted(async () => {
  loading.value = true
  try {
    const [prodRes, famRes] = await Promise.all([
      api.get('/produits'),
      api.get('/parametrage/referentiels/familles-produit')
    ])
    produits.value = prodRes.data.data || prodRes.data || []
    familles.value = famRes.data.data || famRes.data || []
  } catch (error) {
    console.error('Erreur de chargement:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.product-list-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-accent:     #0891b2; /* Cyan pour l'inventaire */
  --c-accent-bg:  #ecfeff;
  --c-danger:     #DC2626;
  --c-success:    #16A34A;
  --radius-lg:    16px;
  --radius-md:    12px;
  --radius-sm:     8px;
  --shadow-sm:    0 1px 3px rgba(0,0,0,.06);

  
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: var(--c-text);
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-primary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(8,145,178,0.2); transition: transform .2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); background: #0e7490; }

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: var(--radius-lg); border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: var(--shadow-sm);
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #0891b2, #06b6d4);
  display: flex; align-items: center; justify-content: center; color: #fff;
  font-weight: 800; font-size: 1.1rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 4px 0 0; }

/* ─── Filters & Tabs ─── */
.filters-card {
  background: #fff; border-radius: var(--radius-md);
  border: 1px solid var(--c-border); margin-bottom: 20px; box-shadow: var(--shadow-sm);
  overflow: hidden;
}
.quick-tabs {
  display: flex; border-bottom: 1px solid var(--c-border);
  background: #F9FAFB;
}
.tab-btn {
  padding: 12px 20px; background: none; border: none; font-size: 0.9rem;
  font-weight: 600; color: var(--c-muted); cursor: pointer;
  border-bottom: 2px solid transparent; transition: all 0.2s;
}
.tab-btn:hover { color: var(--c-text); }
.tab-btn.active { color: var(--c-accent); border-bottom-color: var(--c-accent); background: #fff; }

.advanced-filters {
  padding: 16px; display: flex; flex-direction: column; gap: 16px;
}
.filter-group {
  display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
}
.search-wrapper {
  display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: var(--radius-sm); border: 1px solid var(--c-border);
}
.search-wrapper svg { color: var(--c-muted); }
.search-wrapper input {
  flex: 1; padding: 12px 0; border: none; background: transparent;
  font-size: .9rem; color: var(--c-text); outline: none;
}
.filter-select {
  padding: 10px 14px; border: 1px solid var(--c-border); border-radius: var(--radius-sm);
  font-size: 0.85rem; color: var(--c-text); background: #fff; outline: none;
  min-width: 140px; cursor: pointer;
}
.price-range {
  display: flex; align-items: center; gap: 8px;
}
.filter-input-small {
  width: 90px; padding: 10px 12px; border: 1px solid var(--c-border); border-radius: var(--radius-sm);
  font-size: 0.85rem; color: var(--c-text); background: #fff; outline: none;
}

/* ─── Table ─── */
.table-card {
  background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm); overflow: hidden;
}
.table-container-custom { overflow-x: auto; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th {
  background: #F9FAFB; padding: 14px 20px; font-size: .75rem;
  font-weight: 700; text-transform: uppercase; color: var(--c-muted);
  border-bottom: 1px solid var(--c-border); letter-spacing: .03em;
}
.saas-table th.sortable { cursor: pointer; user-select: none; transition: background 0.2s; }
.saas-table th.sortable:hover { background: #F1F5F9; color: var(--c-text); }
.sort-icon { display: inline-block; margin-left: 4px; font-size: 1rem; color: var(--c-accent); }

.saas-table td { padding: 14px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

/* Group Headers */
.group-header {
  background: #F8FAFC; cursor: pointer; user-select: none; transition: background 0.2s;
}
.group-header:hover { background: #F1F5F9; }
.group-header td { padding: 12px 20px; border-bottom: 1px solid var(--c-border); }
.group-header-content {
  display: flex; align-items: center; gap: 10px; color: var(--c-text); font-size: 0.95rem;
}
.group-header-content svg {
  color: var(--c-muted); transition: transform 0.2s;
}
.group-header-content svg.rotated { transform: rotate(-90deg); }
.group-count {
  font-size: 0.8rem; font-weight: 600; color: var(--c-accent);
  background: var(--c-accent-bg); padding: 2px 8px; border-radius: 12px; margin-left: 8px;
}

/* ─── Specific Cells ─── */
.product-ref-badge {
  font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700;
  color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px;
}
.product-name { font-size: .9rem; font-weight: 700; color: var(--c-text); margin-bottom: 2px; }

.type-pill {
  display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: .7rem;
  font-weight: 800; text-transform: uppercase;
}
.type-pill.service { background: #fef9c3; color: #a16207; }
.type-pill.goods { background: #f1f5f9; color: #475569; }

.price-cell { font-size: .95rem; font-weight: 800; color: var(--c-text); }
.price-cell .currency { font-size: .65rem; font-weight: 600; opacity: .7; margin-left: 2px; }

.stock-display {
  display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px;
  background: #f1f5f9; border-radius: 6px; font-weight: 700; font-size: .85rem;
  justify-content: center; min-width: 60px;
}
.stock-display.stock-low { background: #fee2e2; color: #991b1b; }
.stock-actual { font-weight: 800; }
.stock-separator { font-weight: 400; color: var(--c-muted); font-size: 0.8rem; }
.stock-min { color: var(--c-muted); font-size: 0.75rem; }
.stock-n-a { color: var(--c-muted); font-size: 0.85rem; font-weight: 600; }

.status-indicator {
  display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: .72rem; font-weight: 700;
}
.status-indicator.active { background: #dcfce7; color: #166534; }
.status-indicator.inactive { background: #fee2e2; color: #991b1b; }

/* ─── Actions ─── */
.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn {
  width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border);
  background: #fff; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .2s; color: var(--c-muted);
}
.action-btn:hover { background: var(--c-subtle); transform: translateY(-1px); }
.action-btn.view:hover { color: var(--c-accent); border-color: var(--c-accent); }
.action-btn.edit:hover { color: var(--c-success); border-color: var(--c-success); }
.action-btn.delete:hover { color: var(--c-danger); border-color: var(--c-danger); background: #FEF2F2; }

/* ─── Global ─── */
.text-right { text-align: right; }
.text-center { text-align: center; }
.empty-row { padding: 60px 0 !important; }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--c-muted); }

.loading-overlay {
  position: fixed; inset: 0; z-index: 100; background: rgba(247,248,250,0.8);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { display: inline-block; width: 40px; height: 40px; position: relative; }
.loader-ring div {
  position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent);
  border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 1024px) {
  .saas-table th:nth-child(4), .saas-table td:nth-child(4),
  .saas-table th:nth-child(5), .saas-table td:nth-child(5) { display: none; }
}

/* ─── Modal & Toast ──────────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
  animation: fadeIn 0.2s ease;
}
.modal-box {
  background: #fff; width: 400px; max-width: 90%; border-radius: 12px; padding: 24px;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}
.modal-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.modal-header h3 { font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--c-text); }
.text-danger { color: var(--c-danger); }
.modal-box p { font-size: 0.9rem; color: var(--c-muted); margin-bottom: 24px; line-height: 1.5; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
.btn-cancel {
  padding: 8px 16px; border-radius: 8px; border: 1px solid var(--c-border-mid);
  background: #fff; color: var(--c-text); font-weight: 600; cursor: pointer;
}
.btn-confirm-delete {
  padding: 8px 16px; border-radius: 8px; border: none; background: var(--c-danger);
  color: #fff; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px rgba(220, 38, 38, 0.2);
}
.btn-cancel:hover { background: var(--c-subtle); }
.btn-confirm-delete:hover { background: #b91c1c; }

.toast-notification {
  position: fixed; top: 24px; right: 24px; padding: 14px 24px; border-radius: 12px;
  color: #fff; font-weight: 700; z-index: 1100; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.toast-notification.success { background: var(--c-success); }
.toast-notification.error { background: var(--c-danger); }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateX(20px); opacity: 0; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>