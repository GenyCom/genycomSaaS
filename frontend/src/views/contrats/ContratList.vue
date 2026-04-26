<template>
  <div class="contrats-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement des abonnements…</p>
      </div>
    </Transition>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer l'Abonnement"
      message="Êtes-vous sûr de vouloir supprimer ce contrat ? La facturation automatique sera immédiatement arrêtée."
      confirmText="Arrêter et supprimer"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Abonnements & Contrats</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/contrats/create" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouveau Contrat</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar contrat-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Facturation Automatique
        </div>
        <h1 class="hero-name">Abonnements & Contrats</h1>
        <p class="hero-sub">Vous gérez <strong>{{ contrats.length }}</strong> abonnement(s) actif(s) ou en pause.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Rechercher par titre, client..." />
      </div>
      <div class="filter-group">
        <select v-model="filters.statut" class="filter-select">
          <option value="">Tous les statuts</option>
          <option value="ACTIF">Actifs</option>
          <option value="SUSPENDU">Suspendus</option>
          <option value="RESILIE">Résiliés</option>
        </select>
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Client</th>
              <th>Titre de l'abonnement</th>
              <th>Fréquence</th>
              <th>Prochaine Échéance</th>
              <th class="text-right">Montant TTC</th>
              <th class="text-center">Statut</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="contrat in filteredContrats" :key="contrat.id" class="table-row">
              <td class="client-cell">
                <div class="client-name">{{ contrat.client?.societe || contrat.client?.display_name || 'Client Inconnu' }}</div>
              </td>
              <td>
                <span class="contrat-titre">{{ contrat.titre }}</span>
              </td>
              <td>
                <span class="freq-badge">{{ contrat.frequence }}</span>
              </td>
              <td>
                <span class="echeance-date" :class="{'overdue': isOverdue(contrat.prochaine_echeance)}">
                  {{ contrat.prochaine_echeance ? formatDate(contrat.prochaine_echeance) : 'Non définie' }}
                </span>
              </td>
              <td class="text-right">
                <div class="amount-cell">
                  {{ formatMoney(contrat.total_ttc) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="getStatutClass(contrat.statut)">
                  {{ contrat.statut }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/contrats/${contrat.id}/edit`" class="action-btn edit" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </router-link>
                  <button @click="deleteContrat(contrat.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredContrats.length === 0 && !loading">
              <td colspan="7" class="empty-row">
                <div class="empty-content">
                  <p>Aucun contrat ne correspond à votre recherche.</p>
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

const contrats = ref([])
const searchQuery = ref('')
const loading = ref(true)

const filters = reactive({ statut: '' })

const filteredContrats = computed(() => {
  let res = contrats.value

  if (searchQuery.value) {
    const s = searchQuery.value.toLowerCase()
    res = res.filter(c =>
      (c.titre || '').toLowerCase().includes(s) ||
      (c.client?.societe || c.client?.display_name || '').toLowerCase().includes(s)
    )
  }

  if (filters.statut) {
    res = res.filter(c => c.statut === filters.statut)
  }

  return res
})

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

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('fr-FR')
}

function isOverdue(date) {
  if (!date) return false
  return new Date(date) < new Date(new Date().setHours(0,0,0,0))
}

function getStatutClass(statut) {
  if (statut === 'ACTIF') return 'status-success'
  if (statut === 'SUSPENDU') return 'status-warning'
  if (statut === 'RESILIE') return 'status-danger'
  return 'status-neutral'
}

function deleteContrat(id) {
  itemToDelete.value = id
  showConfirm.value = true
}

async function confirmDelete() {
  const id = itemToDelete.value
  if (!id) return
  
  showConfirm.value = false
  loading.value = true
  try {
    await api.delete(`/contrats/${id}`)
    contrats.value = contrats.value.filter(c => c.id !== id)
    showToast('Contrat supprimé avec succès !', 'success')
  } catch (error) {
    showToast(error.response?.data?.message || 'Erreur lors de la suppression.', 'error')
  } finally {
    loading.value = false
    itemToDelete.value = null
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/contrats')
    contrats.value = data || []
  } catch (error) {
    console.error('Erreur de chargement:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.contrats-list-view {
  --c-bg: #F7F8FA;
  --c-surface: #FFFFFF;
  --c-border: #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text: #1A1D23;
  --c-muted: #6B7280;
  --c-accent: #8B5CF6; /* Theme Violet pour les contrats */
  --c-accent-bg: #F5F3FF;
  --c-danger: #DC2626;
  --c-success: #10B981;
  
  
  background: var(--c-bg);
  min-height: 100vh;
  padding: 12px 28px 48px;
  color: var(--c-text);
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-primary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px;
  background: var(--c-accent); color: #fff; border-radius: 8px;
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25); transition: transform .2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); background: #7C3AED; }

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.contrat-theme { background: linear-gradient(135deg, #8B5CF6, #6D28D9); color: #fff; }
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 1.1rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }

/* ─── Filters ─── */
.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.search-wrapper {
  flex: 1; display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: 8px; max-width: 480px;
}
.search-wrapper svg { color: var(--c-muted); }
.search-wrapper input {
  flex: 1; padding: 12px 0; border: none; background: transparent;
  font-size: .9rem; color: var(--c-text); outline: none;
}
.filter-select { padding: 8px 12px; border-radius: 8px; border: 1px solid var(--c-border); font-size: .85rem; outline: none; }

/* ─── Table ─── */
.table-card {
  background: #fff; border: 1px solid var(--c-border); border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden;
}
.table-container-custom { overflow-x: auto; }

.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th {
  background: #F9FAFB; padding: 14px 20px; font-size: .72rem;
  font-weight: 700; text-transform: uppercase; color: var(--c-muted);
  border-bottom: 1px solid var(--c-border); letter-spacing: .03em;
}
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }

.table-row { transition: background .15s; }
.table-row:hover { background: #F9FAFB; }

.client-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.contrat-titre { font-size: .85rem; color: var(--c-muted); font-weight: 500; }

.freq-badge {
  font-family: 'JetBrains Mono', monospace; font-size: .75rem; font-weight: 700;
  color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px;
}

.echeance-date { font-size: .85rem; font-weight: 600; color: var(--c-text); }
.echeance-date.overdue { color: var(--c-danger); font-weight: 800; }

.amount-cell { font-size: .95rem; font-weight: 800; color: var(--c-text); }
.amount-cell .currency { font-size: .65rem; font-weight: 600; margin-left: 2px; opacity: .7; }

.text-right { text-align: right; }
.text-center { text-align: center; }

/* Status Pills */
.status-pill {
  display: inline-block; padding: 4px 12px; border-radius: 100px;
  font-size: 0.68rem; font-weight: 800; letter-spacing: 0.5px;
  text-transform: uppercase;
}
.status-success { background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; }
.status-warning { background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; }
.status-danger { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
.status-neutral { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }

/* Actions */
.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn {
  width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border);
  background: #fff; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .2s; color: var(--c-muted);
}
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); transform: translateY(-1px); }
.action-btn.delete:hover { color: var(--c-danger); border-color: var(--c-danger); background: #FEF2F2; }

/* States */
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 12px; font-size: .9rem; }

.loading-overlay {
  position: fixed; inset: 0; z-index: 100; background: rgba(247,248,250,0.8);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
  backdrop-filter: blur(4px);
}
.loader-ring { display: inline-block; width: 40px; height: 40px; position: relative; }
.loader-ring div {
  position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent);
  border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Toast */
.toast-notification {
  position: fixed; top: 1.5rem; right: 1.5rem; padding: 1rem 1.5rem; border-radius: 10px;
  color: #fff; font-weight: 700; z-index: 1100; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.toast-notification.success { background: var(--c-success); }
.toast-notification.error { background: var(--c-danger); }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateX(20px); opacity: 0; }
</style>