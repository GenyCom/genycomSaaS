<template>
  <div class="product-fini-form-view">
    <Transition name="fade">
      <div v-if="saving || loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">{{ saving ? 'Enregistrement du produit composé…' : 'Chargement...' }}</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/produits" class="back-btn" title="Retour au catalogue">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Inventaire</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouveau Produit Composé' : form.reference }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-save" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          <span>Enregistrer le composé</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>PF</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Produit Fini / Composé
        </div>
        <h1 class="hero-name">{{ form.designation || 'Nouveau Produit Composé' }}</h1>
        <p class="hero-sub" v-if="!isNew">Réf : <strong>{{ form.reference }}</strong></p>
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Prix Total HT</p>
          <p class="kpi-value">{{ formatMoney(totalHT) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Total TVA</p>
          <p class="kpi-value">{{ formatMoney(totalTVA) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item success">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Total TTC de Vente</p>
          <p class="kpi-value highlighted-kpi">{{ formatMoney(totalTTC) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <h3>Identification & Classification</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Désignation du produit composé *</label>
              <input v-model="form.designation" type="text" class="input-lg" :class="{ 'input-error': errors.designation }" />
              <span v-if="errors.designation" class="error-msg">{{ errors.designation }}</span>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Référence Interne</label>
                <div class="input-with-action">
                  <input v-model="form.reference" type="text" class="mono" :class="{ 'input-error': errors.reference }" placeholder="Laissé vide pour génération automatique..." />
                  <button type="button" @click="generateUniqueReference" class="btn-action-inline" title="Générer une référence unique">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                  </button>
                </div>
                <span v-if="errors.reference" class="error-msg">{{ errors.reference }}</span>
              </div>
              <div class="form-group-custom">
                <label>Famille / Catégorie</label>
                <select v-model="form.famille_id">
                  <option value="">-- Sans Famille --</option>
                  <template v-for="parent in hierarchicalFamilles" :key="parent.id">
                    <option :value="parent.id" style="font-weight: bold; background-color: #f1f5f9;">
                      {{ parent.libelle }}
                    </option>
                    <option v-for="child in parent.children" :key="child.id" :value="child.id">
                      &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ child.libelle }}
                    </option>
                  </template>
                </select>
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Taux TVA Global (%)</label>
                <select v-model="form.taux_tva">
                  <option v-for="t in tauxTvaList" :key="t.id" :value="parseFloat(t.taux)">
                    {{ parseFloat(t.taux) }}% {{ t.libelle ? '(' + t.libelle + ')' : '' }}
                  </option>
                </select>
              </div>
              <div class="form-group-custom">
                <label>Description du produit</label>
                <input v-model="form.detail" type="text" placeholder="Détails, contenu ou spécificités..." />
              </div>
            </div>
          </div>
        </section>

        <!-- Composition Section -->
        <section class="info-card" style="overflow: visible;">
          <div class="card-header table-header-actions">
            <div class="flex-align-center">
              <div class="card-header-icon composite-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
              </div>
              <h3>Composition du Produit Fini</h3>
            </div>
          </div>

          <div class="card-body p-0" style="overflow: visible;">
            <!-- Search products to add as components -->
            <div class="product-search-bar-container flex-align-center gap-12">
              <div class="search-input-wrapper" style="flex: 1;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  @input="onSearchInput"
                  @keydown.enter.prevent="selectFirstProduct"
                  placeholder="Scanner ou rechercher des produits & services à intégrer..." 
                  class="search-input"
                />
                
                <ul v-if="searchResults.length > 0" class="search-dropdown">
                  <li 
                    v-for="prod in searchResults" 
                    :key="prod.id" 
                    @click="addComponent(prod)"
                    class="search-item"
                  >
                    <div class="prod-info">
                      <span class="prod-ref">[{{ prod.reference }}]</span>
                      <span class="prod-name">{{ prod.designation }}</span>
                      <span class="type-badge" :class="prod.is_service ? 'service' : 'goods'">
                        {{ prod.is_service ? 'Service' : 'Stock : ' + formatNumber(prod.stock_actuel) }}
                      </span>
                    </div>
                    <div class="prod-price">{{ formatMoney(prod.prix_ht_vente) }} DH HT</div>
                  </li>
                </ul>

                <div v-if="searchQuery.length >= 2 && searchResults.length === 0" class="search-dropdown empty-result">
                  Aucun résultat trouvé pour "{{ searchQuery }}"
                </div>
              </div>

              <button type="button" @click="showAdvancedModal = true" class="btn-primary-mini">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter produit/service
              </button>
            </div>

            <!-- Components Table -->
            <div class="table-container-custom">
              <table class="saas-table">
                <thead>
                  <tr>
                    <th style="width: 15%">Référence</th>
                    <th style="width: 35%">Article / Prestation</th>
                    <th style="width: 10%" class="text-center">Type</th>
                    <th style="width: 12%" class="text-center">Qté</th>
                    <th style="width: 13%" class="text-right">P.U HT Vente</th>
                    <th style="width: 13%" class="text-right">Total HT</th>
                    <th style="width: 5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(comp, idx) in form.components" :key="idx" class="ligne-row">
                    <td><span class="product-ref-badge">{{ comp.reference }}</span></td>
                    <td class="font-semibold">{{ comp.designation }}</td>
                    <td class="text-center">
                      <span class="type-pill" :class="comp.is_service ? 'service' : 'goods'">
                        {{ comp.is_service ? 'SERVICE' : 'PRODUIT' }}
                      </span>
                    </td>
                    <td class="td-center">
                      <input v-model="comp.quantite" type="number" step="0.01" min="0.01" class="input-inline-table text-center" />
                    </td>
                    <td class="text-right mono font-semibold">
                      {{ formatMoney(comp.prix_ht_vente) }} DH
                    </td>
                    <td class="text-right font-bold mono text-accent">
                      {{ formatMoney(comp.quantite * comp.prix_ht_vente) }} DH
                    </td>
                    <td class="td-action">
                      <button @click="removeComponent(idx)" class="btn-row-delete" title="Retirer ce composant">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="form.components.length === 0">
                    <td colspan="7" class="text-center empty-components-placeholder">
                      <div class="empty-placeholder-content">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        <p>Ajoutez des composants en les recherchant ci-dessus pour construire votre produit fini.</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>

      <!-- Sidebar -->
      <div class="col-side">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <h3>Image du composé</h3>
          </div>
          <div class="card-body">
            <div class="image-preview-zone" @click="triggerUpload">
              <input type="file" ref="fileInput" @change="handleImageUpload" accept="image/*" style="display: none;" />
              <template v-if="form.image_path">
                <img :src="imageUrl" alt="Produit Composé" />
                <button class="btn-remove-image" @click.stop="removeImage">×</button>
              </template>
              <div v-else class="image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>Ajouter une image</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Technical breakdown -->
        <section class="info-card" v-if="form.components.length > 0">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
            <h3>Répartition des composants</h3>
          </div>
          <div class="card-body breakdown-body">
            <div v-for="c in form.components" :key="c.produit_id" class="breakdown-row">
              <div class="breakdown-info">
                <span class="breakdown-name">{{ c.designation }}</span>
                <span class="breakdown-pct">({{ formatPercent(c) }}%)</span>
              </div>
              <div class="breakdown-bar-bg">
                <div class="breakdown-bar-fill" :style="{ width: formatPercent(c) + '%', backgroundColor: c.is_service ? '#eab308' : '#0891b2' }"></div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- Advanced Product Selection Modal -->
  <div v-if="showAdvancedModal" class="modal-overlay" @click.self="showAdvancedModal = false">
    <div class="advanced-modal">
      <div class="modal-header">
        <h3>Sélectionner des Produits & Services</h3>
        <button class="btn-close-modal" @click="showAdvancedModal = false">×</button>
      </div>
      
      <div class="modal-filters">
        <div class="filter-group search">
          <label>Rechercher</label>
          <input v-model="modalSearch" type="text" placeholder="Référence, désignation..." />
        </div>
        <div class="filter-group">
          <label>Famille</label>
          <select v-model="modalFamille">
            <option value="">Tous</option>
            <template v-for="parent in hierarchicalFamilles" :key="parent.id">
              <option :value="parent.id" style="font-weight: bold; background-color: #f1f5f9;">
                {{ parent.libelle }}
              </option>
              <option v-for="child in parent.children" :key="child.id" :value="child.id">
                &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ child.libelle }}
              </option>
            </template>
          </select>
        </div>
        <div class="filter-group">
          <label>Type</label>
          <select v-model="modalType">
            <option value="">Tous</option>
            <option value="goods">Articles Physiques</option>
            <option value="service">Services</option>
          </select>
        </div>
      </div>

      <div class="modal-table-container">
        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 40px;" class="text-center">
                <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
              </th>
              <th>Référence</th>
              <th>Désignation</th>
              <th>Type</th>
              <th class="text-right">Prix HT</th>
              <th class="text-right">Stock</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="prod in paginatedModalProducts" :key="prod.id" class="ligne-row" @click="toggleProductSelection(prod.id)">
              <td class="text-center" @click.stop>
                <input type="checkbox" :value="prod.id" v-model="modalSelectedIds" />
              </td>
              <td><span class="product-ref-badge">{{ prod.reference }}</span></td>
              <td class="font-bold">{{ prod.designation }}</td>
              <td>
                <span class="type-pill" :class="prod.is_service ? 'service' : 'goods'">
                  {{ prod.is_service ? 'Service' : 'Physique' }}
                </span>
              </td>
              <td class="text-right font-bold">{{ formatMoney(prod.prix_ht_vente) }} DH</td>
              <td class="text-right font-semibold">
                <span v-if="prod.is_service" style="color: #9CA3AF;">-</span>
                <span v-else :style="{ color: getStockColor(prod.stock_actuel) }">
                  {{ formatNumber(prod.stock_actuel) }}
                </span>
              </td>
            </tr>
            <tr v-if="filteredModalProducts.length === 0">
              <td colspan="6" class="empty-result">Aucun produit ne correspond aux filtres.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="modal-footer">
        <div class="selected-count" v-if="modalSelectedIds.length > 0">
          {{ modalSelectedIds.length }} produit(s) sélectionné(s)
        </div>
        <div class="flex-spacer"></div>
        <button class="btn-cancel" @click="showAdvancedModal = false">Annuler</button>
        <button 
          class="btn-submit" 
          :disabled="modalSelectedIds.length === 0" 
          @click="addSelectedModalProducts"
        >
          Ajouter à la composition
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'

const props = defineProps({
  isNew: {
    type: Boolean,
    default: false
  }
})

const router = useRouter()
const route = useRoute()
const isNew = computed(() => props.isNew || !route.params.id || route.params.id === 'new')
const saving = ref(false)
const loading = ref(true)

const form = ref({
  famille_id: '',
  reference: '',
  designation: '',
  detail: '',
  image_path: '',
  taux_tva: 20,
  components: []
})

const familles = ref([])
const productsList = ref([])
const tauxTvaList = ref([])
const errors = reactive({})

// Search products to add
const searchQuery = ref('')
const searchResults = ref([])

// Advanced modal state & logic
const showAdvancedModal = ref(false)
const modalSearch = ref('')
const modalFamille = ref('')
const modalType = ref('')
const modalSelectedIds = ref([])

const filteredModalProducts = computed(() => {
  return productsList.value.filter(p => {
    const matchSearch = !modalSearch.value || 
      (p.designation && p.designation.toLowerCase().includes(modalSearch.value.toLowerCase().trim())) ||
      (p.reference && p.reference.toLowerCase().includes(modalSearch.value.toLowerCase().trim()))
      
    const matchFamille = !modalFamille.value || p.famille_id === parseInt(modalFamille.value)
    
    const matchType = !modalType.value || 
      (modalType.value === 'service' && p.is_service) ||
      (modalType.value === 'goods' && !p.is_service)
      
    return matchSearch && matchFamille && matchType
  })
})

const paginatedModalProducts = computed(() => {
  return filteredModalProducts.value.slice(0, 100)
})

const isAllSelected = computed(() => {
  if (paginatedModalProducts.value.length === 0) return false
  return paginatedModalProducts.value.every(p => modalSelectedIds.value.includes(p.id))
})

function toggleSelectAll() {
  if (isAllSelected.value) {
    const pageIds = paginatedModalProducts.value.map(p => p.id)
    modalSelectedIds.value = modalSelectedIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedModalProducts.value.map(p => p.id)
    const newSelected = [...modalSelectedIds.value]
    pageIds.forEach(id => {
      if (!newSelected.includes(id)) {
        newSelected.push(id)
      }
    })
    modalSelectedIds.value = newSelected
  }
}

function toggleProductSelection(id) {
  const index = modalSelectedIds.value.indexOf(id)
  if (index === -1) {
    modalSelectedIds.value.push(id)
  } else {
    modalSelectedIds.value.splice(index, 1)
  }
}

function addSelectedModalProducts() {
  let addedCount = 0
  let alreadyExistsCount = 0
  
  modalSelectedIds.value.forEach(id => {
    const prod = productsList.value.find(p => p.id === id)
    if (prod) {
      const exists = form.value.components.some(c => c.produit_id === prod.id)
      if (exists) {
        alreadyExistsCount++
      } else {
        form.value.components.push({
          produit_id: prod.id,
          reference: prod.reference,
          designation: prod.designation,
          is_service: prod.is_service,
          prix_ht_vente: parseFloat(prod.prix_ht_vente) || 0,
          taux_tva: parseFloat(prod.taux_tva) || 0,
          quantite: 1,
          unite: prod.unite || 'Unité'
        })
        addedCount++
      }
    }
  })
  
  if (addedCount > 0) {
    toast.success(`${addedCount} composant(s) ajouté(s) avec succès.`)
  }
  if (alreadyExistsCount > 0) {
    toast.error(`${alreadyExistsCount} composant(s) déjà présent(s) dans la composition.`)
  }
  
  showAdvancedModal.value = false
  modalSelectedIds.value = []
  modalSearch.value = ''
  modalFamille.value = ''
  modalType.value = ''
}

function getStockColor(stock) {
  const qty = parseFloat(stock || 0)
  if (qty <= 0) return '#EF4444'
  if (qty <= 5) return '#F59E0B'
  return '#10B981'
}

function onSearchInput() {
  const q = searchQuery.value.toLowerCase().trim()
  if (q.length < 2) {
    searchResults.value = []
    return
  }
  searchResults.value = productsList.value.filter(p => 
    (p.designation && p.designation.toLowerCase().includes(q)) || 
    (p.reference && p.reference.toLowerCase().includes(q))
  ).slice(0, 10) 
}

function selectFirstProduct() {
  if (searchResults.value.length > 0) {
    addComponent(searchResults.value[0])
  } else if (searchQuery.value.length > 0) {
    toast.error("Article introuvable.")
    searchQuery.value = ''
  }
}

function addComponent(prod) {
  // Check if component already exists
  const exists = form.value.components.some(c => c.produit_id === prod.id)
  if (exists) {
    toast.error("Ce composant est déjà dans la liste. Modifiez sa quantité.")
    searchQuery.value = ''
    searchResults.value = []
    return
  }

  form.value.components.push({
    produit_id: prod.id,
    reference: prod.reference,
    designation: prod.designation,
    is_service: prod.is_service,
    prix_ht_vente: parseFloat(prod.prix_ht_vente) || 0,
    taux_tva: parseFloat(prod.taux_tva) || 0,
    quantite: 1,
    unite: prod.unite || 'Unité'
  })

  searchQuery.value = ''
  searchResults.value = []
}

function removeComponent(idx) {
  form.value.components.splice(idx, 1)
}

// Computations
const totalHT = computed(() => {
  return form.value.components.reduce((sum, c) => sum + ((parseFloat(c.quantite) || 0) * c.prix_ht_vente), 0)
})

const totalTVA = computed(() => {
  return form.value.components.reduce((sum, c) => {
    const amtHt = (parseFloat(c.quantite) || 0) * c.prix_ht_vente
    const tvaPct = parseFloat(c.taux_tva) || 0
    return sum + (amtHt * (tvaPct / 100))
  }, 0)
})

const totalTTC = computed(() => {
  return totalHT.value + totalTVA.value
})

function formatPercent(c) {
  if (totalHT.value === 0) return 0
  const rowHt = (parseFloat(c.quantite) || 0) * c.prix_ht_vente
  return Math.round((rowHt / totalHT.value) * 100)
}

// Image handling
const fileInput = ref(null)
const imageUrl = ref('')

function triggerUpload() {
  fileInput.value.click()
}

async function handleImageUpload(e) {
  const file = e.target.files[0]
  if (!file) return
  
  imageUrl.value = URL.createObjectURL(file)
  
  // Upload image immediately
  const formData = new FormData()
  formData.append('image_upload', file)
  
  saving.value = true
  try {
    const { data } = await api.post('/produits/upload-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    form.value.image_path = data.image_path
    toast.success("Image téléversée avec succès !")
  } catch (err) {
    console.error(err)
    toast.error("Erreur de téléversement de l'image.")
    imageUrl.value = ''
  } finally {
    saving.value = false
  }
}

function removeImage() {
  form.value.image_path = ''
  imageUrl.value = ''
}

// unique reference generation
async function generateUniqueReference() {
  if (!form.value.designation) {
    toast.error("Veuillez saisir une désignation pour pouvoir générer une référence.")
    return
  }
  try {
    const { data } = await api.get('/produits-finis-next-ref', {
      params: { designation: form.value.designation }
    })
    form.value.reference = data.reference
  } catch (err) {
    toast.error("Erreur de génération de référence.")
  }
}

// Form Validation
function validate() {
  Object.keys(errors).forEach(k => delete errors[k])
  let isValid = true
  
  if (!form.value.designation || !form.value.designation.trim()) {
    errors.designation = "La désignation est obligatoire."
    isValid = false
  }
  
  if (form.value.components.length === 0) {
    toast.error("Veuillez ajouter au moins un composant à la liste.")
    isValid = false
  }
  
  form.value.components.forEach((c, idx) => {
    if ((parseFloat(c.quantite) || 0) <= 0) {
      toast.error(`Quantité invalide pour le composant ${c.designation} (doit être supérieure à 0).`)
      isValid = false
    }
  })
  
  return isValid
}

// Save finished product
async function save() {
  if (!validate()) return
  
  saving.value = true
  try {
    const payload = {
      ...form.value,
      components: form.value.components.map(c => ({
        produit_id: c.produit_id,
        quantite: parseFloat(c.quantite) || 1
      }))
    }
    
    if (isNew.value) {
      await api.post('/produits-finis', payload)
      toast.success("Produit composé créé avec succès !")
    } else {
      await api.put(`/produits-finis/${route.params.id}`, payload)
      toast.success("Produit composé mis à jour !")
    }
    
    router.push({ path: '/produits', query: { tab: 'Composés' } })
  } catch (err) {
    console.error(err)
    toast.error("Erreur lors de l'enregistrement.")
  } finally {
    saving.value = false
  }
}

// Helpers
function formatMoney(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatNumber(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { maximumFractionDigits: 2 })
}

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

onMounted(async () => {
  loading.value = true
  try {
    const [famRes, prodRes, tvaRes] = await Promise.all([
      api.get('/parametrage/referentiels/familles-produit'),
      api.get('/produits', { params: { per_page: 1000 } }),
      api.get('/parametrage/referentiels/taux-tva').catch(() => ({ data: { data: [] } }))
    ])
    
    familles.value = famRes.data.data || famRes.data || []
    productsList.value = (prodRes.data.data || prodRes.data || []).filter(p => p.is_actif !== false)
    tauxTvaList.value = tvaRes.data.data || tvaRes.data || []

    if (!isNew.value) {
      const { data } = await api.get(`/produits-finis/${route.params.id}`)
      const raw = data.data || data
      form.value = {
        famille_id: raw.famille_id || '',
        reference: raw.reference || '',
        designation: raw.designation || '',
        detail: raw.detail || '',
        image_path: raw.image_path || '',
        taux_tva: parseFloat(raw.taux_tva) || 20,
        components: (raw.nomenclature || []).map(n => ({
          produit_id: n.produit_id,
          reference: n.produit?.reference,
          designation: n.produit?.designation,
          is_service: n.produit?.is_service,
          prix_ht_vente: parseFloat(n.produit?.prix_ht_vente) || 0,
          taux_tva: parseFloat(n.produit?.taux_tva) || 0,
          quantite: parseFloat(n.quantite) || 1,
          unite: n.produit?.unite || 'Unité'
        }))
      }
      if (raw.image_path) {
        imageUrl.value = raw.image_path.startsWith('http') ? raw.image_path : '/' + raw.image_path
      }
    }
  } catch (err) {
    console.error(err)
    toast.error("Erreur de chargement des données.")
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.product-fini-form-view {
  background: #F7F8FA;
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: #1A1D23;
  --c-accent: #0f766e;
  --c-accent-bg: #f0fdf4;
}

/* topbar */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.back-btn {
  display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px;
  border-radius: 8px; border: 1px solid #E8EAEE; background: #fff; color: #6B7280;
  margin-right: 12px; transition: all 0.2s;
}
.back-btn:hover { background: #F3F4F6; color: #111827; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: #6B7280; font-weight: 500; }
.breadcrumb-current { color: #1A1D23; font-weight: 700; }

.btn-save {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: 8px;
  font-size: .85rem; font-weight: 600; border: none; cursor: pointer;
  box-shadow: 0 4px 12px rgba(15,118,110,0.2); transition: transform .2s;
}
.btn-save:hover { transform: translateY(-1px); background: #0d5c56; }

/* hero */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: 16px; border: 1px solid #E8EAEE;
  margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #0d9488, #0f766e);
  display: flex; align-items: center; justify-content: center; color: #fff;
  font-weight: 800; font-size: 1.1rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-sub { font-size: .82rem; color: #6B7280; margin: 4px 0 0; }

/* kpi strip */
.kpi-strip {
  display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; align-items: center;
  background: #fff; border-radius: 16px; border: 1px solid #E8EAEE;
  padding: 16px 32px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.kpi-item { display: flex; align-items: center; gap: 16px; }
.kpi-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}
.kpi-item.neutral .kpi-icon { background: #F3F4F6; color: #4B5563; }
.kpi-item.accent .kpi-icon { background: #ECFEFF; color: #0891B2; }
.kpi-item.success .kpi-icon { background: #F0FDF4; color: #16A34A; }
.kpi-body { display: flex; flex-direction: column; }
.kpi-label { font-size: .75rem; color: #6B7280; margin: 0; font-weight: 600; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 2px 0 0; display: flex; align-items: baseline; gap: 4px; }
.kpi-value span { font-size: .78rem; font-weight: 600; opacity: .7; }
.highlighted-kpi { color: #16A34A; }
.kpi-divider { width: 1px; height: 36px; background: #E8EAEE; }

/* grid */
.content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }
@media (max-width: 1024px) {
  .content-grid { grid-template-columns: 1fr; }
}

.info-card {
  background: #fff; border: 1px solid #E8EAEE; border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 24px; overflow: hidden;
}
.card-header {
  display: flex; align-items: center; gap: 10px; padding: 16px 24px;
  border-bottom: 1px solid #E8EAEE;
}
.card-header-icon {
  width: 28px; height: 28px; border-radius: 8px; background: var(--c-accent-bg);
  color: var(--c-accent); display: flex; align-items: center; justify-content: center;
}
.card-header-icon.composite-icon {
  background: #f0fdf4; color: #0d9488;
}
.card-header-icon.notes {
  background: #fdf2f8; color: #db2777;
}
.card-header h3 { font-size: 0.92rem; font-weight: 800; margin: 0; color: #1A1D23; }
.card-body { padding: 24px; }

/* edit form layout */
.edit-form { display: flex; flex-direction: column; gap: 20px; }
.form-group-custom { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.form-group-custom label { font-size: 0.78rem; font-weight: 700; color: #4B5563; text-transform: uppercase; }
.form-group-custom input, .form-group-custom select {
  padding: 10px 14px; border-radius: 8px; border: 1px solid #D5D9E2;
  font-size: 0.88rem; transition: border-color .2s; outline: none; background: #fff;
}
.form-group-custom input:focus, .form-group-custom select:focus {
  border-color: var(--c-accent);
}
.input-lg { font-size: 1.1rem !important; font-weight: 700; color: #111827; }
.input-error { border-color: #EF4444 !important; }
.error-msg { font-size: 0.75rem; color: #EF4444; font-weight: 600; }
.form-row-custom { display: flex; gap: 20px; }
@media (max-width: 640px) {
  .form-row-custom { flex-direction: column; gap: 20px; }
}

.input-with-action { display: flex; position: relative; }
.input-with-action input { width: 100%; padding-right: 46px !important; }
.btn-action-inline {
  position: absolute; right: 4px; top: 4px; bottom: 4px; width: 36px;
  border-radius: 6px; border: none; background: #f3f4f6; color: #4b5563;
  display: flex; align-items: center; justify-content: center; cursor: pointer;
  transition: background .2s;
}
.btn-action-inline:hover { background: #e5e7eb; color: #111827; }
.mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; text-transform: uppercase; }

/* search bar */
.product-search-bar-container { padding: 14px 20px; border-bottom: 1px solid #E8EAEE; background: #F9FAFB; }
.search-input-wrapper { display: flex; align-items: center; position: relative; width: 100%; }
.search-icon { position: absolute; left: 16px; color: #9CA3AF; pointer-events: none; }
.search-input {
  width: 100%; padding: 11px 16px 11px 48px; border-radius: 8px; border: 1px solid #D5D9E2;
  font-size: 0.88rem; outline: none; background: #fff; transition: border-color 0.2s;
}
.search-input:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(15,118,110,0.1); }
.search-dropdown {
  position: absolute; top: calc(100% + 8px); left: 0; right: 0; background: #fff;
  border: 1px solid #D5D9E2; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  z-index: 100; max-height: 220px; overflow-y: auto; list-style: none; padding: 0; margin: 0;
}

/* Button styling */
.btn-primary-mini {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--c-accent); color: #fff; border: none;
  border-radius: 8px; padding: 10px 16px; font-size: 0.85rem; font-weight: 700;
  cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.btn-primary-mini:hover { background: #0d9488; }

/* Modal overlay & advanced modal styles */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px); display: flex; align-items: center;
  justify-content: center; z-index: 1050;
}
.advanced-modal {
  width: 90%; max-width: 850px; background: #fff; border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.3); display: flex; flex-direction: column;
  max-height: 85vh;
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 24px; border-bottom: 1px solid #E8EAEE;
}
.modal-header h3 { font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0; }
.btn-close-modal {
  background: none; border: none; font-size: 24px; color: #6b7280;
  cursor: pointer; padding: 4px; line-height: 1;
}
.modal-filters {
  display: flex; gap: 16px; padding: 16px 24px; background: #F9FAFB;
  border-bottom: 1px solid #E8EAEE;
}
.filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.filter-group.search { flex: 2; }
.filter-group label { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #4b5563; }
.filter-group input, .filter-group select {
  padding: 8px 12px; border-radius: 8px; border: 1px solid #D5D9E2;
  font-size: 0.85rem; outline: none; background: #fff;
}
.filter-group input:focus, .filter-group select:focus { border-color: #0f766e; }
.modal-table-container { flex: 1; overflow-y: auto; padding: 0; }
.modal-footer {
  display: flex; align-items: center; padding: 16px 24px;
  border-top: 1px solid #E8EAEE; gap: 12px; background: #F9FAFB;
  border-radius: 0 0 16px 16px;
}
.selected-count { font-size: 0.85rem; font-weight: 700; color: #0f766e; }
.flex-spacer { flex: 1; }
.btn-cancel {
  background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db;
  border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 700;
  cursor: pointer; transition: background 0.2s;
}
.btn-cancel:hover { background: #e5e7eb; }
.btn-submit {
  background: #0f766e; color: #fff; border: none;
  border-radius: 8px; padding: 8px 20px; font-size: 0.85rem; font-weight: 700;
  cursor: pointer; transition: background 0.2s;
}
.btn-submit:hover { background: #0d9488; }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.gap-12 { gap: 12px; }
.search-item {
  display: flex; align-items: center; justify-content: space-between; padding: 12px 18px;
  border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s;
}
.search-item:last-child { border-bottom: none; }
.search-item:hover { background: #F3F4F6; }
.prod-info { display: flex; flex-direction: column; gap: 2px; }
.prod-ref { font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; font-weight: 700; color: #6b7280; }
.prod-name { font-size: 0.88rem; font-weight: 700; color: #1e293b; }
.type-badge {
  display: inline-block; font-size: 0.68rem; font-weight: 800; text-transform: uppercase;
  margin-top: 2px;
}
.type-badge.service { color: #b45309; }
.type-badge.goods { color: #0891b2; }
.prod-price { font-size: 0.88rem; font-weight: 800; color: #1e293b; }
.empty-result { padding: 16px; font-size: 0.85rem; color: #6b7280; font-style: italic; text-align: center; }

/* table components */
.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th {
  background: #F8FAFC; padding: 12px 20px; font-size: 0.72rem; font-weight: 800;
  text-transform: uppercase; color: #4B5563; border-bottom: 2px solid #E8EAEE; text-align: left;
}
.saas-table td { padding: 12px 20px; border-bottom: 1px solid #E8EAEE; vertical-align: middle; }
.ligne-row:hover { background: #F9FAFB; }

.product-ref-badge {
  font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 700;
  color: #475569; background-color: #F1F5F9; border: 1px solid #E2E8F0;
  padding: 3px 6px; border-radius: 4px; display: inline-block;
}

.type-pill {
  display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: .65rem;
  font-weight: 800; text-transform: uppercase;
}
.type-pill.service { background: #fef9c3; color: #a16207; }
.type-pill.goods { background: #e0f2fe; color: #0369a1; }

.input-inline-table {
  width: 70px; padding: 6px; border-radius: 6px; border: 1px solid #D5D9E2;
  font-size: 0.82rem; font-weight: 700; outline: none; background: #fff;
}
.input-inline-table:focus { border-color: var(--c-accent); }
.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.mono { font-family: 'JetBrains Mono', monospace; }
.text-accent { color: var(--c-accent); }

.btn-row-delete {
  background: none; border: none; color: #EF4444; cursor: pointer;
  padding: 4px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.btn-row-delete:hover { background: #fee2e2; transform: scale(1.05); }

.empty-components-placeholder { padding: 60px 20px !important; color: #9CA3AF; }
.empty-placeholder-content { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-placeholder-content p { font-size: 0.82rem; margin: 0; font-style: italic; }

/* sidebar visuel */
.image-preview-zone {
  width: 100%; height: 180px; border-radius: 12px; border: 2px dashed #D5D9E2;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  cursor: pointer; position: relative; overflow: hidden; background: #F9FAFB;
  transition: border-color .2s, background .2s;
}
.image-preview-zone:hover { border-color: var(--c-accent); background: #F3F4F6; }
.image-preview-zone img { width: 100%; height: 100%; object-fit: cover; }
.image-placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; color: #6B7280; font-size: .8rem; font-weight: 600; }
.btn-remove-image {
  position: absolute; top: 10px; right: 10px; width: 24px; height: 24px;
  background: rgba(0,0,0,0.6); border: none; border-radius: 50%; color: #fff;
  font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.btn-remove-image:hover { background: rgba(0,0,0,0.8); }

/* breakdown */
.breakdown-body { display: flex; flex-direction: column; gap: 14px; }
.breakdown-row { display: flex; flex-direction: column; gap: 4px; }
.breakdown-info { display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; }
.breakdown-name { color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
.breakdown-pct { color: var(--c-accent); font-family: 'JetBrains Mono', monospace; font-weight: 700; }
.breakdown-bar-bg { width: 100%; height: 6px; background-color: #F3F4F6; border-radius: 3px; overflow: hidden; }
.breakdown-bar-fill { height: 100%; border-radius: 3px; }

/* loader overlay */
.loading-overlay {
  position: fixed; inset: 0; background: rgba(255,255,255,0.7);
  backdrop-filter: blur(4px); display: flex; flex-direction: column;
  align-items: center; justify-content: center; z-index: 1000;
}
.loader-ring {
  display: inline-block; position: relative; width: 64px; height: 64px;
}
.loader-ring div {
  box-sizing: border-box; display: block; position: absolute;
  width: 48px; height: 48px; margin: 8px; border: 4px solid var(--c-accent);
  border-radius: 50%; animation: loader-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: var(--c-accent) transparent transparent transparent;
}
.loader-ring div:nth-child(1) { animation-delay: -0.45s; }
.loader-ring div:nth-child(2) { animation-delay: -0.3s; }
.loader-ring div:nth-child(3) { animation-delay: -0.15s; }
@keyframes loader-ring {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.loading-label { margin-top: 16px; font-size: 0.88rem; font-weight: 700; color: var(--c-accent); }

/* toast */
.toast-notification {
  position: fixed; top: 24px; right: 24px; padding: 14px 24px; border-radius: 12px;
  color: #fff; font-weight: 700; z-index: 1100; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.toast-notification.success { background: #16A34A; }
.toast-notification.error { background: #DC2626; }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateX(20px); opacity: 0; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.flex-align-center { display: flex; align-items: center; }
.p-0 { padding: 0 !important; }
</style>
