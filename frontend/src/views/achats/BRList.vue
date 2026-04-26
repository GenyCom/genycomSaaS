<template>
  <div class="br-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement des réceptions…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Bons de Réception</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/bons-reception/new" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouveau BR</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar supply-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Logistique Achats
        </div>
        <h1 class="hero-name">Bons de Réception</h1>
        <p class="hero-sub">Suivi des livraisons fournisseurs et entrées en stock.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par N° BR, fournisseur ou commande..." @input="fetchData" />
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Date Réception</th>
              <th>Fournisseur</th>
              <th>Commande Liée</th>
              <th class="text-center">Statut</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="br in items" :key="br.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ br.numero }}</span>
              </td>
              <td>{{ formatDate(br.date_reception) }}</td>
              <td class="supplier-cell">
                <div class="supplier-name">{{ br.fournisseur_societe }}</div>
              </td>
              <td>
                <span v-if="br.commande_numero" class="origin-tag">
                  {{ br.commande_numero }}
                </span>
                <span v-else class="text-muted text-xs">—</span>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="br.statut === 'valide' ? 'status-success' : 'status-warning'">
                  {{ br.statut?.toUpperCase() || 'BROUILLON' }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/bons-reception/${br.id}`" class="action-btn view" title="Ouvrir">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button @click="handleDelete(br.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="items.length === 0 && !loading">
              <td colspan="6" class="empty-row">
                <div class="empty-content">
                  <p>Aucun bon de réception trouvé.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="pagination-container">
        <button v-for="p in totalPages" :key="p" 
                class="page-link" :class="{ active: p === page }" 
                @click="changePage(p)">
          {{ p }}
        </button>
      </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer la Réception"
      message="Attention : la suppression de ce bon annulera les entrées en stock des articles réceptionnés."
      confirmText="Annuler la réception"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const items = ref([])
const search = ref('')
const page = ref(1)
const totalPages = ref(1)
const loading = ref(true)

const showConfirm = ref(false)
const itemToDelete = ref(null)

function formatDate(d) { 
  return d ? new Date(d).toLocaleDateString('fr-FR') : '—' 
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await api.get('/bons-reception', { 
      params: { search: search.value, page: page.value, per_page: 20 } 
    })
    items.value = data.data || []
    totalPages.value = data.last_page || 1
  } catch (e) { 
    console.error('Erreur BR:', e) 
  } finally {
    loading.value = false
  }
}

function changePage(p) {
  page.value = p
  fetchData()
}

async function handleDelete(id) {
  itemToDelete.value = id
  showConfirm.value = true
}

async function confirmDelete() {
  if (!itemToDelete.value) return
  showConfirm.value = false
  loading.value = true
  try {
    const { data } = await api.delete(`/bons-reception/${itemToDelete.value}`)
    items.value = items.value.filter(br => br.id !== itemToDelete.value)
    toast.success(data.message || 'Réception supprimée et stock mis à jour.')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de la suppression.')
  } finally {
    loading.value = false
    itemToDelete.value = null
  }
}

onMounted(fetchData)
</script>

<style scoped>
/* ─── Design Tokens Achats (Ambre) ─── */
.br-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #F59E0B;
  --c-accent-bg: #FFFBEB;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
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
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); transition: transform .2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); }

/* ─── Hero ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.supply-theme { background: linear-gradient(135deg, #F59E0B, #D97706); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #D97706; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #D97706; border-radius: 50%; }

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

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700; color: #D97706; background: #FFFBEB; padding: 4px 8px; border-radius: 6px; }
.supplier-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.origin-tag { font-size: .65rem; font-weight: 800; color: #3B82F6; background: #EFF6FF; padding: 3px 8px; border-radius: 4px; }

.status-pill { padding: 4px 12px; border-radius: 100px; font-size: .68rem; font-weight: 800; }
.status-success { background: #DCFCE7; color: #166534; }
.status-warning { background: #FEF3C7; color: #92400E; }

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); transform: translateY(-1px); }
.action-btn.delete:hover { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

/* ─── Pagination ─── */
.pagination-container { display: flex; justify-content: center; padding: 1.5rem; gap: 8px; border-top: 1px solid var(--c-border); }
.page-link { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; font-size: .85rem; font-weight: 600; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.page-link.active { background: var(--c-accent); color: #fff; border-color: var(--c-accent); box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2); }

.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>