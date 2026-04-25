<template>
  <div class="devis-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement des devis…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Liste des Devis</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/devis/new" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouveau Devis</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar quote-theme">
        <span>DV</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Pipeline Commercial
        </div>
        <h1 class="hero-name">Gestion des Devis</h1>
        <p class="hero-sub">Vous avez <strong>{{ devis.length }}</strong> devis actif(s) en cours de négociation.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="filters.search" type="text" placeholder="Rechercher par numéro ou client..." />
      </div>
      <div class="filter-group">
        <select v-model="filters.statut" class="filter-select">
          <option value="">Tous les états</option>
          <option value="Brouillon">Brouillon</option>
          <option value="Accepté">Accepté</option>
          <option value="Refusé">Refusé</option>
        </select>
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Date Émission</th>
              <th>Client</th>
              <th>Validité</th>
              <th class="text-right">Montant TTC</th>
              <th class="text-center">État</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in filteredDevis" :key="d.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ d.numero }}</span>
              </td>
              <td>{{ d.date_devis }}</td>
              <td class="client-cell">
                <div class="client-name">{{ d.client_name }}</div>
              </td>
              <td>
                <span :class="{'text-danger': isExpired(d.date_validite)}">
                  {{ d.date_validite || '—' }}
                </span>
              </td>
              <td class="text-right">
                <div class="amount-cell">
                  {{ formatMoney(d.total_ttc) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-center">
                <span class="status-pill-dynamic" 
                      :style="{ backgroundColor: (d.etat?.couleur || '#94a3b8') + '20', color: d.etat?.couleur || '#94a3b8' }">
                  {{ d.etat?.libelle || 'Brouillon' }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/devis/${d.id}`" class="action-btn view" title="Détails">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button @click="handleDelete(d.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredDevis.length === 0">
              <td colspan="7" class="empty-row">
                <div class="empty-content">
                  <p>Aucun devis ne correspond à vos critères.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de Confirmation Élégant -->
    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le Devis"
      message="Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible et pourrait impacter le suivi commercial."
      confirmText="Supprimer définitivement"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const devis = ref([])
const loading = ref(true)
const filters = reactive({ search: '', statut: '' })

// State pour le modal de confirmation
const showConfirm = ref(false)
const devisToDelete = ref(null)

const filteredDevis = computed(() => {
  return devis.value.filter(d => {
    const matchSearch = !filters.search || 
      d.numero?.toLowerCase().includes(filters.search.toLowerCase()) || 
      d.client_name?.toLowerCase().includes(filters.search.toLowerCase())
    const matchStatut = !filters.statut || d.etat?.libelle === filters.statut
    return matchSearch && matchStatut
  })
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// getStatusClass supprimé car remplacé par des styles dynamiques

function isExpired(date) {
  if (!date) return false
  return new Date(date) < new Date()
}

async function handleDelete(id) {
  devisToDelete.value = id
  showConfirm.value = true
}

async function confirmDelete() {
  if (!devisToDelete.value) return
  
  showConfirm.value = false
  loading.value = true
  
  try {
    const { data } = await api.delete(`/devis/${devisToDelete.value}`)
    devis.value = devis.value.filter(d => d.id !== devisToDelete.value)
    toast.success(data.message || 'Devis supprimé avec succès.')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de la suppression.')
  } finally {
    loading.value = false
    devisToDelete.value = null
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/devis')
    devis.value = (data.data || data || []).map(d => ({ 
      ...d, 
      client_name: d.client?.societe || d.client?.display_name, 
    }))
  } catch (error) {
    console.error('Erreur:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.devis-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #2563EB;
  --c-accent-bg: #EEF4FF;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-primary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: 8px;
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: transform .2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); }

/* ─── Hero ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.quote-theme { background: linear-gradient(135deg, #2563EB, #4F46E5); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }

/* ─── Filters ─── */
.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.search-wrapper {
  flex: 1; display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: 8px;
}
.search-wrapper input { flex: 1; padding: 12px 0; border: none; background: transparent; outline: none; font-size: .9rem; }
.filter-select { padding: 10px 14px; border-radius: 8px; border: 1.5px solid #D5D9E2; font-size: .85rem; font-weight: 600; outline: none; }

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px; }
.client-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.amount-cell { font-size: .95rem; font-weight: 800; }
.currency { font-size: .65rem; opacity: .7; margin-left: 2px; }

.status-pill-dynamic { 
  display: inline-block;
  padding: 4px 12px; 
  border-radius: 100px; 
  font-size: .72rem; 
  font-weight: 800; 
  text-transform: uppercase;
}

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn.delete:hover { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>