<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-card modal-large">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon-bg stock-init-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="12 8 12 12 12 16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </div>
            <div>
              <h3 class="modal-title">Initialiser les Nouveaux Produits</h3>
              <p class="modal-subtitle">Initialisez le stock des produits nouvellement créés</p>
            </div>
          </div>
          <button class="close-btn" @click="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <!-- Banner for choice: Manuel vs Bon de Réception -->
          <div class="choice-banner">
            <div class="choice-banner-text">
              <strong>Initialisation du stock</strong>
              <p>Vous pouvez initialiser le stock manuellement ci-dessous ou créer un Bon de Réception pour vos réceptions fournisseurs.</p>
            </div>
            <button class="btn-br-link" @click="goToBonReception">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
              <span>Via Bon de Réception</span>
            </button>
          </div>

          <!-- Loading state -->
          <div v-if="fetching" class="loading-box">
            <span class="loader-inline-dark"></span>
            <span>Recherche des produits non initialisés...</span>
          </div>

          <!-- State 1: All products already initialized -->
          <div v-else-if="uninitializedCount === 0" class="empty-state-card">
            <div class="empty-icon-circle">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h4>Tous vos produits ont déjà une entrée en stock !</h4>
            <p>Il n'y a aucun nouveau produit en attente d'initialisation dans votre catalogue.</p>
            <div class="empty-actions mt-4">
              <button class="btn-secondary-custom" @click="close">Fermer</button>
              <button class="btn-primary-custom" @click="goToBonReception">
                Accéder aux Bons de Réception
              </button>
            </div>
          </div>

          <!-- State 2: Uninitialized products exist -->
          <div v-else>
            <!-- Step 1: Entrepot selector -->
            <div class="config-row">
              <div class="form-group-custom flex-1">
                <label class="required-label">Dépôt / Entrepôt de destination</label>
                <select v-model="selectedEntrepotId" class="form-select-custom">
                  <option value="" disabled>-- Sélectionner un entrepôt --</option>
                  <option v-for="e in entrepots" :key="e.id" :value="e.id">
                    {{ e.nom }} {{ e.is_default ? '(Par défaut)' : '' }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Section Produits -->
            <div class="section-divider mt-4">
              <span class="section-title">Nouveaux Produits à Initialiser</span>
              <span class="badge-count">{{ selectedItems.length }} / {{ uninitializedCount }} sélectionné(s)</span>
            </div>

            <!-- Search & Filter Bar -->
            <div class="product-picker-bar">
              <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input 
                  v-model="productSearch" 
                  type="text" 
                  placeholder="Filtrer par désignation ou référence..." 
                />
              </div>
              <button class="btn-secondary-sm" @click="toggleSelectAll">
                {{ selectedItems.length === filteredUninitializedProducts.length ? 'Tout désélectionner' : 'Tout sélectionner' }}
              </button>
            </div>

            <!-- Table of Uninitialized Items -->
            <div class="init-table-container mt-4">
              <table class="saas-table compact-table">
                <thead>
                  <tr>
                    <th style="width: 6%" class="text-center">
                      <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
                    </th>
                    <th style="width: 30%">Nouveau Produit</th>
                    <th style="width: 20%">Quantité Initiale *</th>
                    <th style="width: 20%">Seuil Alerte</th>
                    <th style="width: 24%">Emplacement</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredUninitializedProducts" :key="item.produit_id" class="table-row" :class="{ 'row-selected': item.selected }">
                    <td class="text-center">
                      <input type="checkbox" v-model="item.selected" />
                    </td>
                    <td>
                      <div class="product-cell-info">
                        <span class="code-badge mono">{{ item.reference }}</span>
                        <span class="product-title">{{ item.designation }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="input-with-unit">
                        <input 
                          v-model.number="item.quantite" 
                          type="number" 
                          step="1" 
                          min="0" 
                          :disabled="!item.selected"
                          class="form-input-custom font-bold text-accent" 
                        />
                        <span class="unit-tag">unités</span>
                      </div>
                    </td>
                    <td>
                      <input 
                        v-model.number="item.seuil_alerte" 
                        type="number" 
                        step="1" 
                        min="0" 
                        placeholder="Ex: 5" 
                        :disabled="!item.selected"
                        class="form-input-custom" 
                      />
                    </td>
                    <td>
                      <input 
                        v-model="item.emplacement_stock" 
                        type="text" 
                        placeholder="Ex: Rayon A-1" 
                        :disabled="!item.selected"
                        class="form-input-custom" 
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Motif -->
            <div class="form-group-custom mt-4">
              <label>Motif / Note d'initialisation</label>
              <input v-model="motif" type="text" placeholder="Ex: Inventaire initial des nouveaux produits" class="form-input-custom" />
            </div>
          </div>
        </div>

        <div v-if="uninitializedCount > 0" class="modal-footer">
          <button class="btn-secondary-custom" @click="close">Annuler</button>
          <button 
            class="btn-primary-custom stock-btn" 
            :disabled="loading || selectedItems.length === 0 || !selectedEntrepotId" 
            @click="submit"
          >
            <span v-if="loading" class="loader-inline"></span>
            Initialiser le stock ({{ selectedItems.length }})
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'

const props = defineProps({
  isOpen: Boolean,
  entrepots: Array
})

const emit = defineEmits(['close', 'success'])
const router = useRouter()

const fetching = ref(false)
const loading = ref(false)
const selectedEntrepotId = ref('')
const productSearch = ref('')
const uninitializedItems = ref([])
const uninitializedCount = ref(0)
const motif = ref('Initialisation du stock')

const fetchUninitializedProducts = async () => {
  fetching.value = true
  try {
    const res = await api.get('/stock/uninitialized-products')
    const list = res.data.data || []
    uninitializedCount.value = res.data.total || list.length

    uninitializedItems.value = list.map(p => ({
      produit_id: p.id,
      reference: p.reference,
      designation: p.designation,
      quantite: 1,
      seuil_alerte: p.seuil_alerte ?? 5,
      emplacement_stock: p.emplacement_stock ?? '',
      selected: true
    }))
  } catch (err) {
    console.error('Erreur chargement produits non initialisés:', err)
  } finally {
    fetching.value = false
  }
}

watch(() => props.isOpen, (val) => {
  if (val) {
    fetchUninitializedProducts()
    productSearch.value = ''
    motif.value = 'Initialisation du stock'
    
    if (props.entrepots && props.entrepots.length > 0) {
      const def = props.entrepots.find(e => e.is_default)
      selectedEntrepotId.value = def ? def.id : props.entrepots[0].id
    }
  }
})

const filteredUninitializedProducts = computed(() => {
  if (!productSearch.value) return uninitializedItems.value
  const q = productSearch.value.toLowerCase()
  return uninitializedItems.value.filter(p => 
    p.designation?.toLowerCase().includes(q) || 
    p.reference?.toLowerCase().includes(q)
  )
})

const selectedItems = computed(() => {
  return uninitializedItems.value.filter(i => i.selected)
})

const isAllSelected = computed(() => {
  return filteredUninitializedProducts.value.length > 0 && filteredUninitializedProducts.value.every(i => i.selected)
})

const toggleSelectAll = () => {
  const targetState = !isAllSelected.value
  filteredUninitializedProducts.value.forEach(i => i.selected = targetState)
}

const goToBonReception = () => {
  close()
  router.push('/bons-reception')
}

const close = () => {
  emit('close')
}

const submit = async () => {
  if (!selectedEntrepotId.value) {
    toast.error('Veuillez sélectionner un entrepôt de destination.')
    return
  }
  if (selectedItems.value.length === 0) {
    toast.error('Veuillez sélectionner au moins un produit à initialiser.')
    return
  }

  loading.value = true
  try {
    const payload = {
      entrepot_id: selectedEntrepotId.value,
      items: selectedItems.value.map(item => ({
        produit_id: item.produit_id,
        quantite: parseFloat(item.quantite) || 0,
        seuil_alerte: item.seuil_alerte !== '' ? parseFloat(item.seuil_alerte) : null,
        emplacement_stock: item.emplacement_stock || null
      })),
      motif: motif.value
    }

    const res = await api.post('/stock/initialize', payload)
    toast.success(res.data.message || 'Stock initialisé avec succès !')
    emit('success')
    close()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de l\'initialisation du stock.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 2000;
}

.modal-card {
  background: #FFFFFF; width: 100%; max-width: 580px; border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;
  --c-accent: #0D9488; --c-accent-bg: #F0FDFA;
}

.modal-large { max-width: 840px; }

.modal-header {
  padding: 20px 24px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;
  display: flex; align-items: center; justify-content: space-between;
}

.modal-header-left { display: flex; align-items: center; gap: 14px; }
.stock-init-icon {
  width: 44px; height: 44px; border-radius: 12px; background: var(--c-accent-bg);
  color: var(--c-accent); display: flex; align-items: center; justify-content: center;
}

.modal-title { margin: 0; font-size: 1.2rem; font-weight: 800; color: #1A1D23; }
.modal-subtitle { margin: 2px 0 0; font-size: 0.8rem; color: #6B7280; }

.close-btn { background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 4px; }
.close-btn:hover { color: #1A1D23; }

.modal-body { padding: 24px; max-height: 75vh; overflow-y: auto; }

/* Choice Banner */
.choice-banner {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  background: #F0FDFA; border: 1.5px solid #CCFBF1; border-radius: 12px;
  padding: 14px 18px; margin-bottom: 20px;
}
.choice-banner-text strong { display: block; color: #0F766E; font-size: 0.9rem; margin-bottom: 2px; }
.choice-banner-text p { margin: 0; font-size: 0.8rem; color: #134E4A; }

.btn-br-link {
  display: flex; align-items: center; gap: 8px; background: #0D9488; color: #fff;
  border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 700;
  cursor: pointer; whitespace-nowrap: nowrap; transition: background 0.2s;
}
.btn-br-link:hover { background: #0F766E; }

.loading-box { display: flex; align-items: center; justify-content: center; gap: 12px; padding: 40px; color: #64748B; font-weight: 600; }

.empty-state-card { text-align: center; padding: 30px 20px; }
.empty-icon-circle { width: 56px; height: 56px; border-radius: 50%; background: #DCFCE7; color: #166534; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.empty-state-card h4 { font-size: 1.1rem; font-weight: 800; color: #1E293B; margin: 0 0 6px; }
.empty-state-card p { color: #64748B; font-size: 0.88rem; margin: 0; }
.empty-actions { display: flex; justify-content: center; gap: 12px; }

.required-label::after { content: " *"; color: #DC2626; }

.config-row { display: flex; gap: 16px; }
.flex-1 { flex: 1; }

.section-divider { display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #E5E7EB; padding-bottom: 8px; margin-bottom: 12px; }
.section-title { font-size: 0.85rem; font-weight: 800; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; }
.badge-count { font-size: 0.75rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 3px 10px; border-radius: 100px; }

.product-picker-bar { display: flex; gap: 12px; align-items: center; }
.search-box {
  flex: 1; display: flex; align-items: center; gap: 10px; background: #F8FAFC;
  border: 1.5px solid #D1D5DB; border-radius: 10px; padding: 0 14px;
}
.search-box input { flex: 1; padding: 10px 0; border: none; background: transparent; outline: none; font-size: 0.88rem; }

.init-table-container { border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; background: #fff; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F8FAFC; padding: 10px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748B; border-bottom: 1px solid #E2E8F0; }
.saas-table td { padding: 10px 14px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }

.row-selected { background: #F0FDFA; }

.product-cell-info { display: flex; flex-direction: column; gap: 2px; }
.code-badge { font-size: 0.72rem; font-weight: 700; color: var(--c-accent); }
.product-title { font-size: 0.88rem; font-weight: 700; color: #1E293B; }

.form-input-custom, .form-select-custom {
  width: 100%; padding: 8px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px;
  font-size: 0.85rem; background: #FDFDFF; outline: none; transition: border-color 0.2s;
}
.form-input-custom:disabled, .form-select-custom:disabled { opacity: 0.5; background: #F3F4F6; }
.form-input-custom:focus, .form-select-custom:focus {
  border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.08);
}

.input-with-unit { position: relative; width: 100%; }
.input-with-unit input { padding-right: 50px; }
.unit-tag { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.65rem; font-weight: 800; color: #9CA3AF; text-transform: uppercase; }

.modal-footer {
  padding: 16px 24px; background: #F9FAFB; border-top: 1px solid #E5E7EB;
  display: flex; justify-content: flex-end; gap: 12px;
}

.btn-primary-custom {
  background: var(--c-accent); color: #fff; border: none; padding: 10px 22px;
  border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer;
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: all 0.2s;
}
.btn-primary-custom:hover:not(:disabled) { transform: translateY(-1px); background: #0F766E; }
.btn-primary-custom:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary-custom {
  background: #fff; color: #64748B; border: 1.5px solid #D1D5DB; padding: 10px 18px;
  border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer;
}

.btn-secondary-sm {
  background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; padding: 8px 14px;
  border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer;
}
.btn-secondary-sm:hover { background: #E2E8F0; }

.mono { font-family: 'JetBrains Mono', monospace; }
.font-bold { font-weight: 700; }
.text-accent { color: var(--c-accent); }
.text-center { text-align: center; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.loader-inline {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; margin-right: 8px;
}
.loader-inline-dark {
  width: 16px; height: 16px; border: 2px solid #CBD5E1; border-top-color: #0D9488;
  border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
