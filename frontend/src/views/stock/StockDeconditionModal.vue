<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-card decondition-card">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon-bg decondition-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="7.5 4.21 12 6.81 16.5 4.21"/>
                <polyline points="7.5 19.79 7.5 14.6 3 12"/>
                <polyline points="21 12 16.5 14.6 16.5 19.79"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Déconditionnement & Vente au Détail / Vrac</h3>
              <p class="modal-subtitle">Déballez ou fractionnez vos conditionnements (sacs, cartons, fûts) en articles au détail (Kg, Grammes, Litres).</p>
            </div>
          </div>
          <button class="close-btn" @click="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <!-- ENTREPOT -->
          <div class="form-group-custom mb-5">
            <label>Entrepôt de l'opération *</label>
            <select v-model="form.entrepot_id" class="form-select-custom">
              <option v-for="e in entrepots" :key="e.id" :value="e.id">{{ e.nom }}</option>
            </select>
          </div>

          <!-- GRILLE TRANSFORMATION -->
          <div class="conversion-grid">
            <!-- PRODUIT SOURCE -->
            <div class="conversion-col source-col">
              <div class="col-header">
                <span class="badge-direction badge-source">Sortie (-)</span>
                <span class="col-title">Article Conditionné / Source</span>
              </div>

              <div class="form-group-custom mt-2">
                <label>Article Source *</label>
                <select v-model="form.produit_source_id" class="form-select-custom" @change="onSourceChange">
                  <option value="" disabled>Sélectionner le produit...</option>
                  <option v-for="p in productList" :key="p.id" :value="p.id">
                    {{ p.designation }} ({{ p.reference }}) — Stock: {{ formatQty(p.stock_actuel) }} {{ p.unite || 'U' }}
                  </option>
                </select>
              </div>

              <div v-if="selectedSourceProduct" class="stock-info-pill">
                <span>Stock dispo : <strong>{{ formatQty(selectedSourceProduct.stock_actuel) }} {{ selectedSourceProduct.unite || 'U' }}</strong></span>
              </div>

              <div class="form-group-custom mt-3">
                <label>Quantité à déconditionner *</label>
                <div class="input-with-unit">
                  <input
                    v-model.number="form.quantite_source"
                    type="number"
                    step="0.001"
                    min="0.001"
                    class="form-input-custom font-bold"
                    placeholder="ex: 1"
                  />
                  <span class="unit-tag">{{ selectedSourceProduct?.unite || 'Sac' }}</span>
                </div>
              </div>
            </div>

            <!-- SEPARATEUR AVEC FLÈCHE -->
            <div class="conversion-arrow-wrapper">
              <div class="arrow-circle">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                  <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
              </div>
              <span class="conversion-label">Fractionner en</span>
            </div>

            <!-- PRODUIT DESTINATION -->
            <div class="conversion-col dest-col">
              <div class="col-header">
                <span class="badge-direction badge-dest">Entrée (+)</span>
                <span class="col-title">Article au Détail / Vrac</span>
              </div>

              <div class="form-group-custom mt-2">
                <label>Article Destination *</label>
                <select v-model="form.produit_dest_id" class="form-select-custom" @change="onDestChange">
                  <option value="" disabled>Sélectionner le produit détail...</option>
                  <option v-for="p in destinationCandidates" :key="p.id" :value="p.id">
                    {{ p.designation }} ({{ p.reference }}) — Stock: {{ formatQty(p.stock_actuel) }} {{ p.unite || 'U' }}
                  </option>
                </select>
              </div>

              <div v-if="selectedDestProduct" class="stock-info-pill">
                <span>Stock dispo : <strong>{{ formatQty(selectedDestProduct.stock_actuel) }} {{ selectedDestProduct.unite || 'U' }}</strong></span>
              </div>

              <div class="form-group-custom mt-3">
                <label>Quantité détail obtenue *</label>
                <div class="input-with-unit">
                  <input
                    v-model.number="form.quantite_dest"
                    type="number"
                    step="0.001"
                    min="0.001"
                    class="form-input-custom font-bold"
                    placeholder="ex: 25.000"
                  />
                  <span class="unit-tag">{{ selectedDestProduct?.unite || 'Kg' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- SUMMARY PREVIEW -->
          <div v-if="selectedSourceProduct && selectedDestProduct && form.quantite_source > 0 && form.quantite_dest > 0" class="preview-recap mt-4">
            <div class="recap-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Impact sur vos stocks :
            </div>
            <div class="recap-details">
              <div class="recap-item">
                <span class="recap-name">{{ selectedSourceProduct.designation }} :</span>
                <span class="recap-change minus">- {{ formatQty(form.quantite_source) }} {{ selectedSourceProduct.unite || 'U' }}</span>
                <span class="recap-next">➔ Nouveau stock : {{ formatQty(selectedSourceProduct.stock_actuel - form.quantite_source) }}</span>
              </div>
              <div class="recap-item">
                <span class="recap-name">{{ selectedDestProduct.designation }} :</span>
                <span class="recap-change plus">+ {{ formatQty(form.quantite_dest) }} {{ selectedDestProduct.unite || 'U' }}</span>
                <span class="recap-next">➔ Nouveau stock : {{ formatQty(Number(selectedDestProduct.stock_actuel) + Number(form.quantite_dest)) }}</span>
              </div>
            </div>
          </div>

          <!-- MOTIF -->
          <div class="form-group-custom mt-4">
            <label>Motif / Justification</label>
            <input
              v-model="form.motif"
              type="text"
              class="form-input-custom"
              placeholder="ex: Déconditionnement sac 25kg pour vente au détail au rayon droguerie"
            />
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary-custom" @click="close">Annuler</button>
          <button
            class="btn-primary-custom decondition-submit-btn"
            :disabled="loading || !isValid"
            @click="submit"
          >
            <span v-if="loading" class="loader-inline"></span>
            Valider le Déconditionnement
          </button>
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
  preselectedProduct: Object,
  entrepots: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const productList = ref([])

const form = ref({
  entrepot_id: '',
  produit_source_id: '',
  produit_dest_id: '',
  quantite_source: 1,
  quantite_dest: 1,
  motif: 'Déconditionnement pour vente au détail / vrac'
})

const selectedSourceProduct = computed(() => {
  return productList.value.find(p => p.id === form.value.produit_source_id) || null
})

const selectedDestProduct = computed(() => {
  return productList.value.find(p => p.id === form.value.produit_dest_id) || null
})

const destinationCandidates = computed(() => {
  return productList.value.filter(p => p.id !== form.value.produit_source_id)
})

const isValid = computed(() => {
  return form.value.produit_source_id &&
         form.value.produit_dest_id &&
         form.value.produit_source_id !== form.value.produit_dest_id &&
         form.value.quantite_source > 0 &&
         form.value.quantite_dest > 0
})

const formatQty = (v) => {
  if (v === null || v === undefined) return '0'
  const num = parseFloat(v)
  return isNaN(num) ? '0' : Number(num.toFixed(3)).toString()
}

const loadProducts = async () => {
  try {
    const res = await api.get('/produits?is_service=false')
    productList.value = Array.isArray(res.data) ? res.data : (res.data?.data || [])
  } catch (err) {
    console.error('Erreur chargement produits:', err)
  }
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    loadProducts()
    if (props.entrepots && props.entrepots.length > 0) {
      form.value.entrepot_id = props.entrepots[0].id
    }
    if (props.preselectedProduct) {
      const pid = props.preselectedProduct.produit_id || props.preselectedProduct.id
      form.value.produit_source_id = pid
    }
  }
})

const onSourceChange = () => {
  // If destination matches source, reset destination
  if (form.value.produit_dest_id === form.value.produit_source_id) {
    form.value.produit_dest_id = ''
  }
}

const onDestChange = () => {
  // Automatic suggestion if source has unit and dest has Kg/g
}

const close = () => {
  emit('close')
}

const submit = async () => {
  if (!isValid.value) return
  loading.value = true
  try {
    const res = await api.post('/stock/deconditionner', {
      produit_source_id: form.value.produit_source_id,
      produit_dest_id: form.value.produit_dest_id,
      entrepot_id: form.value.entrepot_id || null,
      quantite_source: form.value.quantite_source,
      quantite_dest: form.value.quantite_dest,
      motif: form.value.motif
    })
    toast.success(res.data?.message || 'Déconditionnement effectué avec succès.')
    emit('success')
    close()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors du déconditionnement.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center;
  z-index: 1000; padding: 20px;
}

.decondition-card {
  background: #ffffff; border-radius: 20px; width: 100%; max-width: 780px;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.3); overflow: hidden;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.modal-header {
  padding: 24px 28px; border-bottom: 1px solid #E2E8F0; display: flex;
  justify-content: space-between; align-items: flex-start; background: #FAF5FF;
}

.modal-header-left { display: flex; gap: 16px; align-items: center; }

.modal-icon-bg.decondition-icon {
  width: 48px; height: 48px; border-radius: 14px; background: #8B5CF6;
  color: #fff; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 8px 18px rgba(139, 92, 246, 0.28); flex-shrink: 0;
}

.modal-title { font-size: 1.15rem; font-weight: 800; color: #1E1B4B; margin: 0 0 4px 0; }
.modal-subtitle { font-size: 0.8rem; color: #6B7280; margin: 0; }

.close-btn {
  background: transparent; border: none; color: #94A3B8; cursor: pointer;
  padding: 4px; border-radius: 8px; transition: all 0.2s;
}
.close-btn:hover { background: #EDE9FE; color: #5B21B6; }

.modal-body { padding: 24px 28px; max-height: 75vh; overflow-y: auto; }

.form-group-custom { display: flex; flex-direction: column; gap: 6px; }
.form-group-custom label { font-size: 0.75rem; font-weight: 700; color: #4B5563; }

.form-input-custom, .form-select-custom {
  padding: 10px 14px; border: 1.5px solid #D1D5DB; border-radius: 10px;
  font-size: 0.9rem; background: #FDFDFF; outline: none; transition: border-color 0.2s;
}
.form-input-custom:focus, .form-select-custom:focus {
  border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.12);
}

.conversion-grid {
  display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px;
  align-items: center; background: #F8FAFC; padding: 18px; border-radius: 14px;
  border: 1px solid #E2E8F0;
}

.conversion-col {
  background: #ffffff; padding: 16px; border-radius: 12px;
  border: 1px solid #E5E7EB; box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.source-col { border-top: 3px solid #EF4444; }
.dest-col { border-top: 3px solid #10B981; }

.col-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.col-title { font-size: 0.78rem; font-weight: 700; color: #374151; }

.badge-direction {
  font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 6px;
  text-transform: uppercase;
}
.badge-source { background: #FEE2E2; color: #DC2626; }
.badge-dest { background: #D1FAE5; color: #059669; }

.stock-info-pill {
  font-size: 0.72rem; color: #64748B; background: #F1F5F9; padding: 4px 10px;
  border-radius: 6px; margin-top: 6px; display: inline-block;
}

.input-with-unit { position: relative; }
.input-with-unit input { width: 100%; padding-right: 65px; }
.unit-tag {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  font-size: 0.7rem; font-weight: 800; color: #8B5CF6; text-transform: uppercase;
}

.conversion-arrow-wrapper {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
}

.arrow-circle {
  width: 40px; height: 40px; border-radius: 50%; background: #EDE9FE;
  color: #7C3AED; display: flex; align-items: center; justify-content: center;
  border: 2px solid #DDD6FE;
}

.conversion-label { font-size: 0.68rem; font-weight: 700; color: #7C3AED; }

.preview-recap {
  background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 14px 18px;
}
.recap-title {
  display: flex; align-items: center; gap: 8px; font-size: 0.8rem; font-weight: 800;
  color: #15803D; margin-bottom: 8px;
}
.recap-details { display: flex; flex-direction: column; gap: 6px; }
.recap-item { font-size: 0.82rem; color: #166534; display: flex; align-items: center; gap: 8px; }
.recap-change.minus { font-weight: 800; color: #DC2626; }
.recap-change.plus { font-weight: 800; color: #16A34A; }
.recap-next { color: #6B7280; font-size: 0.78rem; font-weight: 600; }

.modal-footer {
  padding: 18px 28px; background: #FAF5FF; border-top: 1px solid #E2E8F0;
  display: flex; justify-content: flex-end; gap: 12px;
}

.btn-secondary-custom {
  background: #fff; color: #64748B; border: 1.5px solid #D1D5DB; padding: 10px 20px;
  border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer;
}

.decondition-submit-btn {
  background: #8B5CF6; color: #fff; border: none; padding: 10px 24px;
  border-radius: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer;
  box-shadow: 0 4px 14px rgba(139, 92, 246, 0.3); transition: all 0.2s;
}
.decondition-submit-btn:hover:not(:disabled) {
  background: #7C3AED; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(139, 92, 246, 0.4);
}
.decondition-submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.font-bold { font-weight: 700; }
.mb-5 { margin-bottom: 20px; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
.mt-4 { margin-top: 16px; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.loader-inline {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .conversion-grid { grid-template-columns: 1fr; }
  .conversion-arrow-wrapper { transform: rotate(90deg); margin: 10px 0; }
}
</style>
