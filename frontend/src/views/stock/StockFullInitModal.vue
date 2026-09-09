<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-card modal-large">
        <div class="modal-header danger-header">
          <div class="modal-header-left">
            <div class="modal-icon-bg danger-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 12a9 9 0 1 1-9 9m9-9a9 9 0 0 0-9-9m9 9H3m9 9a9 9 0 0 1-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Initialisation Complète du Stock</h3>
              <p class="modal-subtitle">Réinitialisez et ajustez les quantités de l'ensemble des produits du catalogue</p>
            </div>
          </div>
          <button class="close-btn" @click="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <!-- Banner Warning -->
          <div class="danger-banner">
            <div class="danger-banner-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="danger-banner-text">
              <strong>Attention : Initialisation globale du stock</strong>
              <p>Par défaut, la quantité de l'ensemble des produits du catalogue est réglée à <strong>0</strong>. Vous pouvez modifier la quantité souhaitée ligne par ligne pour chaque entrepôt.</p>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="fetching" class="loading-box">
            <span class="loader-inline-danger"></span>
            <span>Chargement du catalogue complet et des dépôts...</span>
          </div>

          <!-- Content when loaded -->
          <div v-else>
            <!-- Search & Filter Bar -->
            <div class="filter-toolbar">
              <div class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  placeholder="Rechercher par référence ou désignation..." 
                />
              </div>

              <div class="warehouse-filter">
                <select v-model="selectedWarehouseFilter" class="form-select-custom">
                  <option value="">Tous les dépôts</option>
                  <option v-for="e in entrepots" :key="e.id" :value="e.id">{{ e.nom }}</option>
                </select>
              </div>

              <div class="quick-actions">
                <button class="btn-secondary-sm" @click="resetAllQuantitiesToZero" title="Fixer toutes les nouvelles quantités sélectionnées à 0">
                  ⚡ Tout mettre à 0
                </button>
                <button class="btn-secondary-sm" @click="toggleSelectAll">
                  {{ isAllSelected ? 'Tout désélectionner' : 'Tout sélectionner' }}
                </button>
              </div>
            </div>

            <!-- Stats Bar -->
            <div class="section-divider">
              <span class="section-title">Catalogue Produits & Dépôts</span>
              <span class="badge-count-danger">
                {{ selectedItems.length }} / {{ items.length }} ligne(s) sélectionnée(s)
              </span>
            </div>

            <!-- Table of All Product Lines -->
            <div class="init-table-container">
              <table class="saas-table compact-table">
                <thead>
                  <tr>
                    <th style="width: 5%" class="text-center">
                      <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
                    </th>
                    <th style="width: 32%">Produit</th>
                    <th style="width: 18%">Entrepôt / Dépôt</th>
                    <th style="width: 12%" class="text-right">Actuel</th>
                    <th style="width: 15%">Nouvelle Qty *</th>
                    <th style="width: 18%">Emplacement</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="(item, index) in filteredItems" 
                    :key="item.produit_id + '_' + item.entrepot_id + '_' + index" 
                    class="table-row" 
                    :class="{ 'row-selected': item.selected }"
                  >
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
                      <span class="warehouse-pill">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M3 7v14"/><path d="M21 7v14"/><path d="M6 21V11"/><path d="M10 21V11"/><path d="M14 21V11"/><path d="M18 21V11"/><path d="M12 3L2 7h20L12 3z"/></svg>
                        {{ item.entrepot_nom }}
                      </span>
                    </td>
                    <td class="text-right">
                      <span class="qty-badge-current">{{ item.quantite_actuelle }}</span>
                    </td>
                    <td>
                      <div class="input-with-unit">
                        <input 
                          v-model.number="item.quantite" 
                          type="number" 
                          step="1" 
                          min="0" 
                          :disabled="!item.selected"
                          class="form-input-custom font-bold text-danger-input" 
                        />
                        <span class="unit-tag">unités</span>
                      </div>
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
                  <tr v-if="filteredItems.length === 0">
                    <td colspan="6" class="text-center empty-cell">
                      Aucune ligne de stock ne correspond à votre recherche.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Motif input -->
            <div class="form-group-custom mt-4">
              <label>Motif de réinitialisation</label>
              <input 
                v-model="motif" 
                type="text" 
                placeholder="Ex: Initialisation complète du stock catalogue" 
                class="form-input-custom" 
              />
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary-custom" @click="close">Annuler</button>
          <button 
            class="btn-danger-submit" 
            :disabled="loading || selectedItems.length === 0" 
            @click="promptConfirmation"
          >
            <span v-if="loading" class="loader-inline"></span>
            Valider l'initialisation complète ({{ selectedItems.length }})
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Confirmation Modal Dialog -->
  <Transition name="modal-fade">
    <div v-if="showConfirmDialog" class="modal-overlay confirm-overlay" @click.self="showConfirmDialog = false">
      <div class="modal-card confirm-card">
        <div class="confirm-header">
          <div class="confirm-icon-bg">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <h3>Confirmer l'initialisation complète</h3>
          <p>
            Vous êtes sur le point de réinitialiser le stock de 
            <strong>{{ selectedItems.length }} ligne(s)</strong> de produit(s).
          </p>
        </div>
        <div class="confirm-body">
          <p class="confirm-warning-text">
            Les quantités physiques seront ajustées selon vos saisies (quantité par défaut : 0). Un mouvement d'ajustement de stock sera généré pour chaque produit.
          </p>
        </div>
        <div class="confirm-footer">
          <button class="btn-secondary-custom" @click="showConfirmDialog = false">Annuler</button>
          <button class="btn-danger-submit" :disabled="loading" @click="executeFullInitialize">
            <span v-if="loading" class="loader-inline"></span>
            Oui, procéder à la réinitialisation
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Success Confirmation Modal -->
  <Transition name="modal-fade">
    <div v-if="showSuccessDialog" class="modal-overlay confirm-overlay" @click.self="closeSuccess">
      <div class="modal-card success-card">
        <div class="success-header">
          <div class="success-icon-bg">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3>Initialisation Réussie !</h3>
          <p class="success-msg">{{ successMessage }}</p>
        </div>
        <div class="success-footer">
          <button class="btn-primary-custom" @click="closeSuccess">Fermer et afficher le stock</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'

const props = defineProps({
  isOpen: Boolean,
  entrepots: Array
})

const emit = defineEmits(['close', 'success'])

const fetching = ref(false)
const loading = ref(false)
const searchQuery = ref('')
const selectedWarehouseFilter = ref('')
const items = ref([])
const motif = ref('Initialisation complète du stock catalogue')

const showConfirmDialog = ref(false)
const showSuccessDialog = ref(false)
const successMessage = ref('')

const fetchFullInitData = async () => {
  fetching.value = true
  try {
    const res = await api.get('/stock/full-init-data')
    const list = res.data.data || []
    items.value = list.map(item => ({
      ...item,
      quantite: item.quantite ?? 0, // Force default 0 as requested
      selected: true
    }))
  } catch (err) {
    console.error('Erreur chargement données initialisation complète:', err)
    toast.error('Erreur lors du chargement de l\'inventaire.')
  } finally {
    fetching.value = false
  }
}

watch(() => props.isOpen, (val) => {
  if (val) {
    fetchFullInitData()
    searchQuery.value = ''
    selectedWarehouseFilter.value = ''
    motif.value = 'Initialisation complète du stock catalogue'
    showConfirmDialog.value = false
    showSuccessDialog.value = false
  }
})

const filteredItems = computed(() => {
  let list = items.value
  if (selectedWarehouseFilter.value) {
    list = list.filter(i => String(i.entrepot_id) === String(selectedWarehouseFilter.value))
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(i => 
      i.designation?.toLowerCase().includes(q) || 
      i.reference?.toLowerCase().includes(q)
    )
  }
  return list
})

const selectedItems = computed(() => {
  return items.value.filter(i => i.selected)
})

const isAllSelected = computed(() => {
  return filteredItems.value.length > 0 && filteredItems.value.every(i => i.selected)
})

const toggleSelectAll = () => {
  const targetState = !isAllSelected.value
  filteredItems.value.forEach(i => i.selected = targetState)
}

const resetAllQuantitiesToZero = () => {
  items.value.forEach(i => {
    if (i.selected) {
      i.quantite = 0
    }
  })
  toast.info('Toutes les nouvelles quantités des lignes sélectionnées ont été réglées à 0.')
}

const close = () => {
  emit('close')
}

const promptConfirmation = () => {
  if (selectedItems.value.length === 0) {
    toast.error('Veuillez sélectionner au moins une ligne de stock à initialiser.')
    return
  }
  showConfirmDialog.value = true
}

const executeFullInitialize = async () => {
  loading.value = true
  showConfirmDialog.value = false
  try {
    const payload = {
      items: selectedItems.value.map(item => ({
        produit_id: item.produit_id,
        entrepot_id: item.entrepot_id,
        quantite: parseFloat(item.quantite) || 0,
        seuil_alerte: item.seuil_alerte !== '' ? parseFloat(item.seuil_alerte) : null,
        emplacement_stock: item.emplacement_stock || null
      })),
      motif: motif.value
    }

    const res = await api.post('/stock/full-initialize', payload)
    successMessage.value = res.data.message || 'Initialisation complète réalisée avec succès !'
    toast.success(successMessage.value)
    
    showSuccessDialog.value = true
  } catch (error) {
    console.error('Erreur initialisation complète:', error)
    toast.error(error.response?.data?.message || 'Erreur lors de l\'initialisation complète du stock.')
  } finally {
    loading.value = false
  }
}

const closeSuccess = () => {
  showSuccessDialog.value = false
  emit('success')
  close()
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 2000;
}

.confirm-overlay { z-index: 2100; }

.modal-card {
  background: #FFFFFF; width: 100%; max-width: 900px; border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;
  --c-danger: #DC2626; --c-danger-bg: #FEF2F2;
}

.modal-large { max-width: 960px; }

.modal-header {
  padding: 20px 24px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;
  display: flex; align-items: center; justify-content: space-between;
}
.danger-header { background: #FEF2F2; border-bottom: 1px solid #FCA5A5; }

.modal-header-left { display: flex; align-items: center; gap: 14px; }
.danger-icon {
  width: 44px; height: 44px; border-radius: 12px; background: #FEE2E2;
  color: #DC2626; display: flex; align-items: center; justify-content: center;
}

.modal-title { margin: 0; font-size: 1.2rem; font-weight: 800; color: #991B1B; }
.modal-subtitle { margin: 2px 0 0; font-size: 0.8rem; color: #7F1D1D; }

.close-btn { background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 4px; }
.close-btn:hover { color: #1A1D23; }

.modal-body { padding: 24px; max-height: 72vh; overflow-y: auto; }

/* Danger Alert Banner */
.danger-banner {
  display: flex; align-items: flex-start; gap: 14px;
  background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 12px;
  padding: 14px 18px; margin-bottom: 20px;
}
.danger-banner-icon { color: #DC2626; flex-shrink: 0; margin-top: 2px; }
.danger-banner-text strong { display: block; color: #991B1B; font-size: 0.9rem; margin-bottom: 2px; }
.danger-banner-text p { margin: 0; font-size: 0.82rem; color: #7F1D1D; line-height: 1.4; }

.loading-box { display: flex; align-items: center; justify-content: center; gap: 12px; padding: 40px; color: #64748B; font-weight: 600; }

.filter-toolbar { display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap; }
.search-box {
  flex: 1; min-width: 260px; display: flex; align-items: center; gap: 10px; background: #F8FAFC;
  border: 1.5px solid #D1D5DB; border-radius: 10px; padding: 0 14px;
}
.search-box input { flex: 1; padding: 10px 0; border: none; background: transparent; outline: none; font-size: 0.88rem; }

.warehouse-filter { width: 200px; }

.quick-actions { display: flex; gap: 8px; }

.section-divider { display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #E5E7EB; padding-bottom: 8px; margin-bottom: 12px; }
.section-title { font-size: 0.85rem; font-weight: 800; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; }
.badge-count-danger { font-size: 0.75rem; font-weight: 700; color: #DC2626; background: #FEE2E2; padding: 3px 10px; border-radius: 100px; }

.init-table-container { border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; background: #fff; max-height: 380px; overflow-y: auto; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F8FAFC; padding: 10px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748B; border-bottom: 1px solid #E2E8F0; sticky: top; }
.saas-table td { padding: 10px 14px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }

.row-selected { background: #FFF5F5; }

.product-cell-info { display: flex; flex-direction: column; gap: 2px; }
.code-badge { font-size: 0.72rem; font-weight: 700; color: #DC2626; }
.product-title { font-size: 0.88rem; font-weight: 700; color: #1E293B; }

.warehouse-pill {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.75rem; font-weight: 700; color: #475569; background: #F1F5F9;
  padding: 4px 10px; border-radius: 6px; border: 1px solid #CBD5E1;
}

.qty-badge-current { font-family: 'JetBrains Mono', monospace; font-size: 0.88rem; font-weight: 800; color: #64748B; background: #F8FAFC; padding: 4px 8px; border-radius: 6px; border: 1px solid #E2E8F0; }

.text-danger-input { color: #DC2626 !important; }

.form-input-custom, .form-select-custom {
  width: 100%; padding: 8px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px;
  font-size: 0.85rem; background: #FDFDFF; outline: none; transition: border-color 0.2s;
}
.form-input-custom:disabled, .form-select-custom:disabled { opacity: 0.5; background: #F3F4F6; }
.form-input-custom:focus, .form-select-custom:focus {
  border-color: #DC2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.input-with-unit { position: relative; width: 100%; }
.input-with-unit input { padding-right: 50px; }
.unit-tag { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.65rem; font-weight: 800; color: #9CA3AF; text-transform: uppercase; }

.modal-footer {
  padding: 16px 24px; background: #F9FAFB; border-top: 1px solid #E5E7EB;
  display: flex; justify-content: flex-end; gap: 12px;
}

.btn-danger-submit {
  background: #DC2626; color: #fff; border: none; padding: 10px 22px;
  border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); transition: all 0.2s;
}
.btn-danger-submit:hover:not(:disabled) { transform: translateY(-1px); background: #B91C1C; }
.btn-danger-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary-custom {
  background: #fff; color: #64748B; border: 1.5px solid #D1D5DB; padding: 10px 18px;
  border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer;
}

.btn-primary-custom {
  background: #0D9488; color: #fff; border: none; padding: 10px 22px;
  border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer;
}

.btn-secondary-sm {
  background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; padding: 8px 14px;
  border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: background 0.2s;
}
.btn-secondary-sm:hover { background: #E2E8F0; }

.empty-cell { padding: 30px !important; color: #94A3B8; font-weight: 600; }

/* Confirmation Card */
.confirm-card { max-width: 480px; border-radius: 16px; padding: 24px; text-align: center; }
.confirm-icon-bg { width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.confirm-header h3 { margin: 0 0 6px; font-size: 1.15rem; font-weight: 800; color: #1E293B; }
.confirm-header p { margin: 0; color: #64748B; font-size: 0.9rem; }
.confirm-body { margin: 16px 0; background: #FFF5F5; padding: 12px; border-radius: 10px; border: 1px solid #FCA5A5; }
.confirm-warning-text { margin: 0; font-size: 0.82rem; color: #991B1B; line-height: 1.4; }
.confirm-footer { display: flex; justify-content: center; gap: 12px; margin-top: 20px; }

/* Success Card */
.success-card { max-width: 480px; border-radius: 16px; padding: 28px 24px; text-align: center; }
.success-icon-bg { width: 64px; height: 64px; border-radius: 50%; background: #DCFCE7; color: #166534; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.success-header h3 { margin: 0 0 8px; font-size: 1.25rem; font-weight: 800; color: #14532D; }
.success-msg { color: #374151; font-size: 0.92rem; margin: 0; line-height: 1.5; }
.success-footer { margin-top: 24px; display: flex; justify-content: center; }

.mono { font-family: 'JetBrains Mono', monospace; }
.font-bold { font-weight: 700; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.mt-4 { margin-top: 1rem; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.loader-inline {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; margin-right: 8px;
}
.loader-inline-danger {
  width: 16px; height: 16px; border: 2px solid #FCA5A5; border-top-color: #DC2626;
  border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
