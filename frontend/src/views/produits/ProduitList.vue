<template>
  <div class="product-list-view">
    <Teleport to="body">
      <Transition name="zoom-fade">
        <div v-if="hoveredImage" class="global-image-zoom" :style="{ top: zoomPos.y + 'px', left: zoomPos.x + 'px' }">
          <img :src="hoveredImage" alt="Zoom Produit" />
        </div>
      </Transition>
    </Teleport>

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
      :title="isDeletingFini ? 'Supprimer le Produit Composé' : 'Supprimer le Produit'"
      :message="isDeletingFini ? 'Êtes-vous sûr de vouloir supprimer ce produit composé du catalogue ? Cette action est irréversible.' : 'Êtes-vous sûr de vouloir supprimer ce produit du catalogue ? Cette action est irréversible.'"
      :confirmText="isDeletingFini ? 'Supprimer le composé' : 'Supprimer le produit'"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
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
        <router-link to="/produits/fini/new" class="btn-primary-custom" style="background: linear-gradient(135deg, #0f766e, #0d9488); box-shadow: 0 4px 12px rgba(13,148,136,0.2); margin-left: 10px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
          </svg>
          <span>Nouveau Produit Composé</span>
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
        <p class="hero-sub" v-if="currentTab === 'Composés'"><strong>{{ produitsFinis.length }}</strong> produits composés enregistrés.</p>
        <p class="hero-sub" v-else><strong>{{ produits.length }}</strong> références enregistrées dans votre base.</p>
      </div>
    </div>

    <!-- Advanced Filters -->
    <div class="filters-card">
      <div class="quick-tabs">
        <button 
          v-for="tab in ['Tous', 'Actifs', 'Inactifs', 'Stock faible', 'Composés']" 
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
            <template v-for="parent in hierarchicalFamilles" :key="parent.id">
              <option :value="parent.id" style="font-weight: bold; background-color: #f1f5f9;">
                {{ parent.libelle }}
              </option>
              <option v-for="child in parent.children" :key="child.id" :value="child.id">
                &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ child.libelle }}
              </option>
            </template>
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
              <th style="width: 50px;">Img</th>
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
            <template v-for="(subGroups, parentFamName) in groupedProduits" :key="parentFamName">
              <!-- Parent Group Header -->
              <tr class="group-header" @click="toggleGroup(parentFamName)">
                <td colspan="8">
                  <div class="group-header-content">
                    <svg :class="{ 'rotated': collapsedGroups[parentFamName] }" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    <span class="parent-fam-icon">📁</span>
                    <strong>{{ parentFamName }}</strong>
                    <span class="group-count">{{ getProductCount(subGroups) }} produit(s)</span>
                  </div>
                </td>
              </tr>
              
              <template v-if="!collapsedGroups[parentFamName]">
                <!-- Loop over Sub-groups -->
                <template v-for="(items, subFamName) in subGroups" :key="subFamName">
                  <!-- Sub-family Header (only shown if it is not '_direct') -->
                  <tr v-if="subFamName !== '_direct'" class="sub-group-header">
                    <td colspan="8" class="pl-sub-header">
                      <div class="sub-group-header-content">
                        <span class="sub-fam-arrow">└─</span>
                        <span class="sub-fam-icon">📁</span>
                        <span class="sub-fam-title">{{ subFamName }}</span>
                        <span class="sub-group-count">{{ items.length }} produit(s)</span>
                      </div>
                    </td>
                  </tr>
                  
                  <!-- Product Rows -->
                  <template v-for="produit in items" :key="produit.id">
                    <tr class="table-row" :class="{ 'child-product-row': subFamName !== '_direct' }">
                      <td class="text-center">
                        <div class="product-thumb" :class="{ 'has-image': produit.image_path }"
                             @mouseenter="e => handleImageHover(e, getImageUrl(produit.image_path))"
                             @mouseleave="handleImageLeave"
                             @mousemove="e => hoveredImage && updateZoomPos(e)"
                        >
                          <img v-if="produit.image_path" :src="getImageUrl(produit.image_path)" alt="Image" class="thumb-img" />
                          <div v-else class="thumb-placeholder" :class="produit.is_composite ? 'bg-composite' : (produit.is_service ? 'bg-service' : 'bg-produit')">
                            {{ produit.is_composite ? 'PF' : (produit.is_service ? 'SR' : 'PR') }}
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="product-ref-badge">{{ produit.reference }}</span>
                      </td>
                      <td class="designation-cell">
                        <div style="display: flex; align-items: center; gap: 8px;">
                          <button v-if="produit.is_composite" @click.stop="toggleFiniExpanded(produit.id)" class="btn-expand-components" :class="{ 'expanded': expandedFinis[produit.id] }" title="Voir les composants">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                          </button>
                          <div class="product-name">
                            {{ produit.designation }}
                            <span v-if="produit.is_composite" class="badge-components-count">
                              {{ produit.nomenclature?.length || 0 }} comp.
                            </span>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span v-if="produit.is_composite" class="type-pill composite">
                          COMPOSÉ
                        </span>
                        <span v-else class="type-pill" :class="produit.is_service ? 'service' : 'goods'">
                          {{ produit.is_service ? 'SERVICE' : 'PRODUIT' }}
                        </span>
                      </td>
                      <td class="text-right">
                        <div class="price-cell">
                          {{ formatMoney(produit.is_composite ? produit.prix_ht : produit.prix_ht_vente) }}
                          <span class="currency">DH</span>
                        </div>
                      </td>
                      <td class="text-center">
                        <div v-if="produit.is_composite">
                          <div v-if="getVirtualStock(produit) === null" class="stock-n-a">-</div>
                          <div v-else class="stock-display" :class="{ 'stock-low': getVirtualStock(produit) <= 0 }">
                            <span class="stock-actual" title="Stock virtuel calculé à partir des composants">⚙️ {{ formatNumber(getVirtualStock(produit)) }}</span>
                          </div>
                        </div>
                        <div v-else-if="produit.is_service" class="stock-n-a">-</div>
                        <div v-else class="stock-display" :class="{ 'stock-low': produit.stock_actuel <= (produit.stock_min || produit.seuil_alerte || 0) }">
                          <span class="stock-actual">{{ formatNumber(produit.stock_actuel) }}</span>
                          <span class="stock-separator">/</span>
                          <span class="stock-min" title="Stock Min">{{ formatNumber(produit.stock_min || 0) }}</span>
                        </div>
                      </td>
                      <td class="text-center">
                        <span class="status-indicator active">
                          Actif
                        </span>
                      </td>
                      <td class="text-right">
                        <div class="actions-group">
                          <router-link :to="produit.is_composite ? `/produits/fini/${produit.id}` : `/produits/${produit.id}`" class="action-btn view" title="Consulter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          </router-link>
                          <router-link :to="produit.is_composite ? `/produits/fini/${produit.id}/edit` : `/produits/${produit.id}/edit`" class="action-btn edit" title="Modifier">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          </router-link>
                          <button @click="produit.is_composite ? deleteProduitFini(produit.id) : deleteProduit(produit.id)" class="action-btn delete" title="Supprimer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2 2h2"/></svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                    
                    <!-- Nested Accordion row showing components of a finished product -->
                    <tr v-if="produit.is_composite && expandedFinis[produit.id]" class="components-expanded-row">
                      <td colspan="8">
                        <div class="components-panel">
                          <div class="components-panel-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            <span>Composants de {{ produit.designation }}</span>
                          </div>
                          <table class="components-subtable">
                            <thead>
                              <tr>
                                <th>Référence</th>
                                <th>Désignation</th>
                                <th>Type</th>
                                <th class="text-right">Qté</th>
                                <th class="text-right">P.U HT Vente</th>
                                <th class="text-right">Total HT</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="n in produit.nomenclature" :key="n.id">
                                <td><span class="product-ref-badge">{{ n.produit?.reference }}</span></td>
                                <td>{{ n.produit?.designation }}</td>
                                <td>
                                  <span class="type-pill" :class="n.produit?.is_service ? 'service' : 'goods'">
                                    {{ n.produit?.is_service ? 'SERVICE' : 'PRODUIT' }}
                                  </span>
                                </td>
                                <td class="text-right font-bold">{{ formatNumber(n.quantite) }}</td>
                                <td class="text-right">{{ formatMoney(n.produit?.prix_ht_vente) }} DH</td>
                                <td class="text-right font-bold text-accent">{{ formatMoney(n.montant_ht) }} DH</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </td>
                    </tr>
                  </template>
                </template>
              </template>
            </template>
            <tr v-if="Object.keys(groupedProduits).length === 0">
              <td colspan="8" class="empty-row">
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
import { useRoute } from 'vue-router'
import api from '../../services/api'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const route = useRoute()
const produits = ref([])
const familles = ref([])
const produitsFinis = ref([])
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

// Zoom state
const hoveredImage = ref(null)
const zoomPos = reactive({ x: 0, y: 0 })

function handleImageHover(e, imagePath) {
  if (!imagePath) return
  hoveredImage.value = imagePath
  updateZoomPos(e)
}

function updateZoomPos(e) {
  const popupWidth = 320
  const popupHeight = 320
  let x = e.clientX + 20
  let y = e.clientY - popupHeight / 2

  if (x + popupWidth > window.innerWidth) {
    x = e.clientX - popupWidth - 20
  }
  if (y < 20) y = 20
  if (y + popupHeight > window.innerHeight) {
    y = window.innerHeight - popupHeight - 20
  }

  zoomPos.x = x
  zoomPos.y = y
}

function handleImageLeave() {
  hoveredImage.value = null
}

// Group collapse state
const collapsedGroups = reactive({})
const expandedFinis = ref({})

function toggleFiniExpanded(id) {
  expandedFinis.value[id] = !expandedFinis.value[id]
}

const toast = reactive({ show: false, message: '', type: 'success' })
const showConfirm = ref(false)
const itemToDelete = ref(null)
const isDeletingFini = ref(false)

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
  isDeletingFini.value = false
  showConfirm.value = true
}

function deleteProduitFini(id) {
  itemToDelete.value = id
  isDeletingFini.value = true
  showConfirm.value = true
}

function cancelDelete() {
  showConfirm.value = false
  isDeletingFini.value = false
  itemToDelete.value = null
}

async function confirmDelete() {
  const id = itemToDelete.value
  if (!id) return
  
  showConfirm.value = false
  loading.value = true
  try {
    if (isDeletingFini.value) {
      await api.delete(`/produits-finis/${id}`)
      produitsFinis.value = produitsFinis.value.filter(p => p.id !== id)
      showToast('Produit composé supprimé avec succès !', 'success')
    } else {
      await api.delete(`/produits/${id}`)
      produits.value = produits.value.filter(p => p.id !== id)
      showToast('Produit supprimé avec succès !', 'success')
    }
  } catch (error) {
    showToast(error.response?.data?.message || 'Erreur lors de la suppression.', 'error')
  } finally {
    loading.value = false
    itemToDelete.value = null
    isDeletingFini.value = false
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

function getVirtualStock(pf) {
  if (!pf.nomenclature || pf.nomenclature.length === 0) return 0;
  let minStock = null;
  let hasPhysical = false;
  pf.nomenclature.forEach(n => {
    const prod = n.produit;
    if (prod && !prod.is_service) {
      hasPhysical = true;
      const qtyNeeded = parseFloat(n.quantite) || 1;
      const currentStock = parseFloat(prod.stock_actuel) || 0;
      const virtualVal = Math.floor(currentStock / qtyNeeded);
      if (minStock === null || virtualVal < minStock) {
        minStock = virtualVal;
      }
    }
  });
  return hasPhysical ? minStock : null;
}

const processedProduits = computed(() => {
  let result = []
  if (currentTab.value === 'Composés') {
    result = produitsFinis.value
  } else {
    // Merge standard products and finished products
    result = [...produits.value, ...produitsFinis.value]
  }

  // Tabs
  if (currentTab.value === 'Actifs') {
    result = result.filter(p => p.is_actif !== false)
  } else if (currentTab.value === 'Inactifs') {
    result = result.filter(p => p.is_actif === false)
  } else if (currentTab.value === 'Stock faible') {
    result = result.filter(p => !p.is_composite && !p.is_service && p.stock_actuel <= (p.stock_min || p.seuil_alerte || 0))
  }

  // Advanced Filters
  if (filters.famille_id) {
    result = result.filter(p => p.famille_id == filters.famille_id)
  }
  
  if (currentTab.value !== 'Composés') {
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
  }

  const pMin = parseFloat(filters.prixMin)
  if (!isNaN(pMin)) {
    result = result.filter(p => parseFloat(currentTab.value === 'Composés' ? p.prix_ht : p.prix_ht_vente) >= pMin)
  }
  const pMax = parseFloat(filters.prixMax)
  if (!isNaN(pMax)) {
    result = result.filter(p => parseFloat(currentTab.value === 'Composés' ? p.prix_ht : p.prix_ht_vente) <= pMax)
  }

  // Search
  if (search.value) {
    const s = search.value.toLowerCase()
    result = result.filter(p =>
      (p.designation || '').toLowerCase().includes(s) ||
      (p.reference || '').toLowerCase().includes(s) ||
      (p.reference_oem || '').toLowerCase().includes(s)
    )
  }

  // Sort
  result.sort((a, b) => {
    let valA = a[sortBy.value]
    let valB = b[sortBy.value]

    if (sortBy.value === 'stock') {
      if (currentTab.value === 'Composés') {
        const vA = getVirtualStock(a)
        const vB = getVirtualStock(b)
        valA = vA === null ? -9999999 : vA
        valB = vB === null ? -9999999 : vB
      } else {
        valA = a.is_service ? -9999999 : (parseFloat(a.stock_actuel) || 0)
        valB = b.is_service ? -9999999 : (parseFloat(b.stock_actuel) || 0)
      }
    }
    if (sortBy.value === 'prix') {
      valA = parseFloat(currentTab.value === 'Composés' ? a.prix_ht : a.prix_ht_vente) || 0
      valB = parseFloat(currentTab.value === 'Composés' ? b.prix_ht : b.prix_ht_vente) || 0
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
    let parentName = 'Sans famille'
    let subName = '_direct'
    
    if (p.famille) {
      if (p.famille.parent) {
        parentName = p.famille.parent.libelle
        subName = p.famille.libelle
      } else {
        parentName = p.famille.libelle
        subName = '_direct'
      }
    }
    
    if (!groups[parentName]) {
      groups[parentName] = {}
      if (collapsedGroups[parentName] === undefined) {
        collapsedGroups[parentName] = false
      }
    }
    
    if (!groups[parentName][subName]) {
      groups[parentName][subName] = []
    }
    
    groups[parentName][subName].push(p)
  })
  
  const orderedGroups = {}
  Object.keys(groups).sort().forEach(parentKey => {
    const subs = groups[parentKey]
    const orderedSubs = {}
    
    const sortedSubKeys = Object.keys(subs).sort((a, b) => {
      if (a === '_direct') return -1
      if (b === '_direct') return 1
      return a.localeCompare(b)
    })
    
    sortedSubKeys.forEach(subKey => {
      orderedSubs[subKey] = subs[subKey]
    })
    
    orderedGroups[parentKey] = orderedSubs
  })
  
  return orderedGroups
})

const hierarchicalFamilles = computed(() => {
  const parents = familles.value.filter(f => !f.parent_id)
  return parents.map(parent => {
    const children = familles.value.filter(f => f.parent_id === parent.id)
    return {
      ...parent,
      children: children.sort((a, b) => a.libelle.localeCompare(b.libelle))
    }
  }).sort((a, b) => a.libelle.localeCompare(b.libelle))
})

const getProductCount = (subGroups) => {
  let count = 0
  Object.values(subGroups).forEach(items => {
    count += items.length
  })
  return count
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  if (imagePath.startsWith('blob:') || imagePath.startsWith('http')) {
    return imagePath
  }
  let path = imagePath
  if (!path.startsWith('/')) path = '/' + path
  return path
}

onMounted(async () => {
  loading.value = true
  try {
    const [prodRes, famRes, pfRes] = await Promise.all([
      api.get('/produits'),
      api.get('/parametrage/referentiels/familles-produit'),
      api.get('/produits-finis')
    ])
    produits.value = prodRes.data.data || prodRes.data || []
    familles.value = famRes.data.data || famRes.data || []
    produitsFinis.value = (pfRes.data.data || pfRes.data || []).map(p => ({ ...p, is_composite: true }))
    
    if (route.query.tab) {
      currentTab.value = route.query.tab
    }
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
.product-thumb {
  width: 40px; height: 40px; border-radius: 8px; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid var(--c-border); background: var(--c-bg);
  flex-shrink: 0; transition: transform 0.2s;
}
.product-thumb.has-image { cursor: pointer; }
.product-thumb.has-image:hover { transform: scale(1.05); }

.thumb-img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder {
  width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem; font-weight: 800; color: #fff;
}
.thumb-placeholder.bg-produit { background: linear-gradient(135deg, #64748b, #475569); }
.thumb-placeholder.bg-service { background: linear-gradient(135deg, #eab308, #ca8a04); }

.global-image-zoom {
  position: fixed;
  z-index: 99999;
  background: #fff;
  padding: 8px;
  border-radius: 12px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.25);
  pointer-events: none;
}
.global-image-zoom img {
  display: block;
  max-width: 320px;
  max-height: 320px;
  object-fit: contain;
  border-radius: 8px;
}
.zoom-fade-enter-active, .zoom-fade-leave-active {
  transition: all 0.15s ease-out;
}
.zoom-fade-enter-from, .zoom-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.product-ref-badge {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.78rem;
  font-weight: 700;
  color: #475569;
  background-color: #F1F5F9;
  border: 1px solid #E2E8F0;
  padding: 4px 8px;
  border-radius: 6px;
  display: inline-block;
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

/* ─── Hierarchical Grouping Specific Styles ─── */
.parent-fam-icon {
  margin-right: 6px;
  font-size: 1.1rem;
}
.sub-group-header {
  background-color: #F8FAFC;
  border-bottom: 1px solid var(--c-border);
}
.pl-sub-header {
  padding: 8px 24px !important;
}
.sub-group-header-content {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--c-muted);
}
.sub-fam-arrow {
  color: #CBD5E1;
  font-family: monospace;
  margin-right: 4px;
}
.sub-fam-icon {
  font-size: 0.95rem;
}
.sub-fam-title {
  color: #475569;
}
.sub-group-count {
  font-size: 0.74rem;
  font-weight: 600;
  color: var(--c-accent);
  background: var(--c-accent-bg);
  padding: 2px 8px;
  border-radius: 12px;
  margin-left: 8px;
}
.child-product-row {
  background-color: #FFFFFF;
}
.child-product-row td:first-child,
.child-product-row td:nth-child(2) {
  padding-left: 28px;
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

/* ─── Styles Produits Composés ─── */
.thumb-placeholder.bg-composite {
  background: linear-gradient(135deg, #0d9488, #0f766e);
}
.type-pill.composite {
  background: #ecfeff;
  color: #0f766e;
  border: 1px solid #cffafe;
}
.btn-expand-components {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 1px solid var(--c-border-mid);
  background: #fff;
  color: var(--c-muted);
  cursor: pointer;
  padding: 0;
  transition: all 0.2s ease;
  flex-shrink: 0;
}
.btn-expand-components:hover {
  background: var(--c-accent-bg);
  color: var(--c-accent);
  border-color: var(--c-accent);
}
.btn-expand-components.expanded {
  transform: rotate(180deg);
  background: var(--c-accent-bg);
  color: var(--c-accent);
  border-color: var(--c-accent);
}
.badge-components-count {
  font-size: 0.7rem;
  font-weight: 700;
  color: #0f766e;
  background-color: #f0fdf4;
  border: 1px solid #dcfce7;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 6px;
  display: inline-block;
  vertical-align: middle;
}
.components-expanded-row {
  background-color: #f8fafc;
}
.components-expanded-row td {
  padding: 0 !important;
}
.components-panel {
  padding: 20px 24px 24px 48px;
  border-left: 4px solid #0d9488;
  animation: slideDown 0.25s ease-out;
}
.components-panel-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 12px;
}
.components-panel-header svg {
  color: #0d9488;
}
.components-subtable {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.components-subtable th {
  background-color: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 8px 16px;
  border-bottom: 1px solid #e2e8f0;
}
.components-subtable td {
  padding: 10px 16px !important;
  font-size: 0.82rem;
  border-bottom: 1px solid #f1f5f9;
}
.components-subtable tr:last-child td {
  border-bottom: none;
}
.text-accent {
  color: #0d9488;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>