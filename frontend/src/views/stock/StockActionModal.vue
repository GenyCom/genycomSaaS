<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon-bg">
              <svg v-if="mode === 'transfer'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <h3 class="modal-title">{{ title }}</h3>
          </div>
          <button class="close-btn" @click="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="info-group mb-6">
            <span class="info-label">Article Concerné</span>
            <div class="product-preview-box">
              <div class="product-main">{{ produit?.produit?.designation }}</div>
              <div class="product-ref mono">{{ produit?.produit?.reference }}</div>
            </div>
          </div>

          <div v-if="mode === 'transfer'" class="form-row-custom">
            <div class="form-group-custom">
              <label>Dépôt Source</label>
              <div class="read-only-box">{{ currentEntrepot?.nom }}</div>
            </div>
            <div class="form-group-custom">
              <label>Dépôt Destination *</label>
              <select v-model="form.entrepot_dest_id" class="form-select-custom">
                <option value="" disabled>Choisir l'entrepôt...</option>
                <option v-for="e in entrepots" :key="e.id" :value="e.id">{{ e.nom }}</option>
              </select>
            </div>
          </div>

          <div v-if="mode === 'adjust'" class="form-row-custom">
            <div class="form-group-custom">
              <label>Entrepôt (Optionnel)</label>
              <div class="read-only-box">{{ currentEntrepot?.nom || 'Dépôt Principal' }}</div>
            </div>
            <div class="form-group-custom">
              <label>Opération *</label>
              <select v-model="form.type" class="form-select-custom">
                <option value="ajustement_positif">Ajout Stock (+)</option>
                <option value="ajustement_negatif">Retrait Stock (-)</option>
              </select>
            </div>
          </div>

          <div class="form-row-custom mt-4">
            <div class="form-group-custom">
              <label>Quantité du mouvement *</label>
              <div class="input-with-unit">
                <input v-model.number="form.quantite" type="number" step="0.01" min="0.01" class="form-input-custom font-bold" />
                <span class="unit-tag">Unités</span>
              </div>
            </div>
          </div>

          <div class="form-group-custom mt-4">
            <label>Motif / Justification</label>
            <textarea v-model="form.motif" rows="3" class="textarea-custom" placeholder="Indiquez la raison de ce mouvement (ex: Inventaire, casse, transfert inter-sites...)"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary-custom" @click="close">Annuler</button>
          <button class="btn-primary-custom stock-btn" :disabled="loading" @click="submit">
            <span v-if="loading" class="loader-inline"></span>
            {{ mode === 'transfer' ? 'Confirmer le Transfert' : 'Appliquer l\'ajustement' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'

const props = defineProps({
  isOpen: Boolean,
  mode: String,
  produit: Object,
  currentEntrepot: Object,
  entrepots: Array
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const title = ref('')
const form = ref({
  produit_id: null,
  entrepot_id: null,
  entrepot_source_id: null,
  entrepot_dest_id: '',
  quantite: 1,
  type: 'ajustement_positif',
  motif: ''
})

watch(() => props.isOpen, (val) => {
  if (val) {
    title.value = props.mode === 'transfer' ? 'Transférer du Stock' : 'Ajuster l\'Inventaire'
    form.value.produit_id = props.produit?.produit_id || props.produit?.id
    form.value.entrepot_id = props.currentEntrepot?.id
    form.value.entrepot_source_id = props.currentEntrepot?.id
    form.value.entrepot_dest_id = ''
    form.value.quantite = 1
    form.value.motif = ''
  }
})

const close = () => emit('close')

const submit = async () => {
  if (props.mode === 'transfer' && !form.value.entrepot_dest_id) {
    toast.error('Veuillez sélectionner un entrepôt de destination.'); return;
  }
  loading.value = true
  try {
    if (props.mode === 'adjust') {
      await api.post('/stock/adjust', form.value)
    } else {
      await api.post('/stock/transfer', form.value)
    }
    emit('success')
    close()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de l\'opération de stock.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ─── Design Tokens (Teal / Stock) ─── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 2000;
}

.modal-card {
  background: #FFFFFF; width: 100%; max-width: 520px; border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;
  --c-accent: #0D9488; --c-accent-bg: #F0FDFA;
}

/* ─── Header ─── */
.modal-header {
  padding: 24px 28px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;
  display: flex; align-items: center; justify-content: space-between;
}

.modal-header-left { display: flex; align-items: center; gap: 14px; }
.modal-icon-bg {
  width: 42px; height: 42px; border-radius: 12px; background: var(--c-accent-bg);
  color: var(--c-accent); display: flex; align-items: center; justify-content: center;
}

.modal-title { margin: 0; font-size: 1.25rem; font-weight: 800; color: #1A1D23; letter-spacing: -0.02em; }
.close-btn { background: none; border: none; color: #9CA3AF; cursor: pointer; transition: color 0.2s; padding: 4px; }
.close-btn:hover { color: #1A1D23; }

/* ─── Body ─── */
.modal-body { padding: 28px; }

.info-label {
  display: block; font-size: 0.68rem; font-weight: 800; color: #6B7280;
  text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;
}

.product-preview-box {
  background: #F8FAFC; padding: 14px 18px; border-radius: 12px; border: 1.5px solid #E2E8F0;
}
.product-main { font-weight: 700; color: #1A1D23; font-size: 0.95rem; }
.product-ref { color: var(--c-accent); font-size: 0.75rem; margin-top: 2px; }

.form-row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; }
.form-group-custom label { font-size: 0.72rem; font-weight: 700; color: #4B5563; }

.read-only-box {
  padding: 10px 14px; background: #F3F4F6; border: 1.5px solid #E5E7EB;
  border-radius: 10px; color: #6B7280; font-weight: 600; font-size: 0.9rem;
}

.form-input-custom, .form-select-custom, .textarea-custom {
  padding: 10px 14px; border: 1.5px solid #D1D5DB; border-radius: 10px;
  font-size: 0.9rem; background: #FDFDFF; outline: none; transition: border-color 0.2s;
}
.form-input-custom:focus, .form-select-custom:focus, .textarea-custom:focus {
  border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.08);
}

.input-with-unit { position: relative; }
.input-with-unit input { width: 100%; padding-right: 70px; }
.unit-tag {
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  font-size: 0.65rem; font-weight: 800; color: #9CA3AF; text-transform: uppercase;
}

.textarea-custom { resize: none; line-height: 1.5; }

/* ─── Footer ─── */
.modal-footer {
  padding: 20px 28px; background: #F9FAFB; border-top: 1px solid #E5E7EB;
  display: flex; justify-content: flex-end; gap: 12px;
}

.btn-primary-custom {
  background: var(--c-accent); color: #fff; border: none; padding: 10px 24px;
  border-radius: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer;
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: all 0.2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(13, 148, 136, 0.3); }

.btn-secondary-custom {
  background: #fff; color: #64748B; border: 1.5px solid #D1D5DB; padding: 10px 20px;
  border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer;
}

.mono { font-family: 'JetBrains Mono', monospace; }
.font-bold { font-weight: 700; }

/* ─── Transitions ─── */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.loader-inline {
  width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>