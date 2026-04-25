<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-icon-bg">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div>
              <h3 class="modal-title">Historique des Mouvements</h3>
              <p class="modal-subtitle">{{ stock?.produit?.designation || 'Chargement...' }} — {{ stock?.entrepot?.nom || 'Dépôt' }}</p>
            </div>
          </div>
          <button class="close-btn" @click="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <div v-if="loading" class="loading-state">
            <div class="loader"></div>
            <p>Chargement de l'historique...</p>
          </div>

          <div v-else-if="errorMessage" class="error-alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p>{{ errorMessage }}</p>
          </div>

          <div v-else-if="mouvements.length === 0" class="empty-state">
            <p>Aucun mouvement enregistré pour cet article.</p>
          </div>

          <div v-else class="history-container">
            <table class="history-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Opération</th>
                  <th>Document</th>
                  <th class="text-right">Quantité</th>
                  <th class="text-right">Auteur</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in mouvements" :key="m.id">
                  <td class="date-col">
                    <span class="day">{{ formatDate(m.created_at, 'DD/MM') }}</span>
                    <span class="time">{{ formatDate(m.created_at, 'HH:mm') }}</span>
                  </td>
                  <td>
                    <div class="type-badge" :class="getTypeClass(m.type_mouvement)">
                      {{ formatType(m.type_mouvement) }}
                    </div>
                  </td>
                  <td>
                    <span class="doc-tag">{{ m.document_type }} #{{ m.document_id || '—' }}</span>
                  </td>
                  <td class="text-right font-bold" :class="isPositive(m.type_mouvement) ? 'text-green' : 'text-red'">
                    {{ isPositive(m.type_mouvement) ? '+' : '-' }}{{ m.quantite }}
                  </td>
                  <td class="text-right author-col">
                    {{ m.auteur ? `${m.auteur.nom} ${m.auteur.prenom || ''}` : 'Système' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <div class="current-stock-info">
            Stock actuel : <strong>{{ stock?.quantite }}</strong>
          </div>
          <button class="btn-secondary-custom" @click="close">Fermer</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '../../services/api'

const props = defineProps({
  isOpen: Boolean,
  stockId: [Number, String]
})

const emit = defineEmits(['close'])

const loading = ref(false)
const errorMessage = ref(null)
const stock = ref(null)
const mouvements = ref([])

const close = () => emit('close')

const fetchData = async () => {
  if (!props.stockId) return
  loading.value = true
  try {
    console.log('Appel API Historique pour Stock ID:', props.stockId)
    const res = await api.get(`/stock/${props.stockId}`)
    console.log('Données reçues:', res.data)
    stock.value = res.data.stock
    mouvements.value = res.data.mouvements
    errorMessage.value = null
  } catch (error) {
    console.error('Erreur API Historique:', error)
    errorMessage.value = error.response?.data?.message || "Impossible de récupérer l'historique."
  } finally {
    loading.value = false
  }
}

watch([() => props.isOpen, () => props.stockId], ([open, id]) => {
  if (open && id) {
    fetchData()
  }
}, { immediate: true })

const formatDate = (dateString, format) => {
  if (!dateString) return '—'
  const date = new Date(dateString)
  if (format === 'DD/MM') {
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
  }
  if (format === 'HH:mm') {
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
  }
  return date.toLocaleString('fr-FR')
}

const isPositive = (type) => {
  return ['entree_achat', 'entree_retour', 'ajustement_positif', 'transfert_in'].includes(type)
}

const formatType = (type) => {
  const types = {
    entree_achat: 'Achat',
    sortie_vente: 'Vente',
    ajustement_positif: 'Ajustement (+)',
    ajustement_negatif: 'Ajustement (-)',
    transfert_in: 'Transfert IN',
    transfert_out: 'Transfert OUT',
    entree_retour: 'Retour Vente',
    sortie_retour: 'Retour Achat'
  }
  return types[type] || type
}

const getTypeClass = (type) => {
  if (isPositive(type)) return 'badge-green'
  return 'badge-red'
}
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; z-index: 2100;
}

.modal-card {
  background: #FFFFFF; width: 100%; max-width: 750px; border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;
  max-height: 90vh; display: flex; flex-direction: column;
}

.modal-header {
  padding: 20px 28px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;
  display: flex; align-items: center; justify-content: space-between;
}

.modal-header-left { display: flex; align-items: center; gap: 14px; }
.modal-icon-bg {
  width: 42px; height: 42px; border-radius: 12px; background: #EEF2FF;
  color: #4F46E5; display: flex; align-items: center; justify-content: center;
}

.modal-title { margin: 0; font-size: 1.1rem; font-weight: 800; color: #1A1D23; }
.modal-subtitle { margin: 0; font-size: 0.8rem; color: #64748B; font-weight: 500; }

.modal-body { padding: 0; overflow-y: auto; flex: 1; }

.history-container { width: 100%; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th {
  position: sticky; top: 0; background: #F8FAFC; padding: 12px 24px;
  text-align: left; font-size: 0.65rem; font-weight: 800; color: #64748B;
  text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #E2E8F0;
}

.history-table td { padding: 14px 24px; border-bottom: 1px solid #F1F5F9; font-size: 0.85rem; }

.date-col { display: flex; flex-direction: column; }
.day { font-weight: 700; color: #1A1D23; }
.time { font-size: 0.7rem; color: #94A3B8; }

.type-badge {
  display: inline-flex; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;
}
.badge-green { background: #DCFCE7; color: #15803D; }
.badge-red { background: #FEE2E2; color: #B91C1C; }

.doc-tag { font-family: monospace; color: #64748B; background: #F1F5F9; padding: 2px 6px; border-radius: 4px; }

.text-green { color: #10B981; }
.text-red { color: #EF4444; }
.text-right { text-align: right; }
.font-bold { font-weight: 700; }
.author-col { color: #64748B; font-size: 0.8rem; }

.modal-footer {
  padding: 16px 28px; background: #F9FAFB; border-top: 1px solid #E5E7EB;
  display: flex; justify-content: space-between; align-items: center;
}

.current-stock-info { color: #475569; font-size: 0.9rem; }
.current-stock-info strong { color: #0D9488; font-size: 1.1rem; }

.btn-secondary-custom {
  background: #fff; color: #64748B; border: 1.5px solid #D1D5DB; padding: 8px 18px;
  border-radius: 10px; font-weight: 600; font-size: 0.85rem; cursor: pointer;
}

.loading-state, .empty-state { padding: 60px; text-align: center; color: #94A3B8; }
.loader {
  width: 30px; height: 30px; border: 3px solid #E2E8F0; border-top-color: #4F46E5;
  border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

.error-alert { padding: 40px; text-align: center; color: #DC2626; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.error-alert svg { color: #EF4444; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
