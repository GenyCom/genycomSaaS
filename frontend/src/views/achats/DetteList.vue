<template>
  <div class="dette-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Analyse du passif fournisseur…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Dettes Fournisseur</span>
        </div>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar debt-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Engagements Financiers
        </div>
        <h1 class="hero-name">Gestion des Dettes</h1>
        <p class="hero-sub">Suivi des règlements et du reste à payer auprès de vos fournisseurs.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par N° dette, fournisseur..." @input="fetchData" />
      </div>
      <div class="filter-group">
        <select v-model="filtre" class="filter-select" @change="fetchData">
          <option value="">Tous les statuts</option>
          <option value="en_attente">En attente</option>
          <option value="partielle">Partielle</option>
          <option value="soldee">Soldées</option>
        </select>
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Date</th>
              <th>Fournisseur</th>
              <th>Source (BR)</th>
              <th class="text-right">Montant TTC</th>
              <th class="text-right">Reste à payer</th>
              <th class="text-center">Statut</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in items" :key="d.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ d.numero }}</span>
              </td>
              <td>{{ formatDate(d.date_dette) }}</td>
              <td class="supplier-cell">
                <div class="supplier-name">{{ d.fournisseur_societe }}</div>
              </td>
              <td>
                <span v-if="d.br_numero" class="source-tag">BR: {{ d.br_numero }}</span>
                <span v-else class="text-muted">—</span>
              </td>
              <td class="text-right mono font-medium">{{ formatMoney(d.montant_total) }}</td>
              <td class="text-right">
                <div class="amount-cell" :class="{'text-danger font-black': parseFloat(d.montant_restant) > 0}">
                  {{ formatMoney(d.montant_restant) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="getStatutClass(d)">
                  {{ getStatutLabel(d) }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/dettes/${d.id}`" class="action-btn view" title="Ouvrir">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                </div>
              </td>
            </tr>
            <tr v-if="items.length === 0 && !loading">
              <td colspan="8" class="empty-row">
                <div class="empty-content">
                  <p>Aucune dette fournisseur répertoriée.</p>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const items = ref([])
const search = ref('')
const filtre = ref('')
const page = ref(1)
const totalPages = ref(1)
const loading = ref(true)

function formatDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }
function formatMoney(v) { return parseFloat(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }

function getStatutLabel(d) {
  if (parseFloat(d.montant_restant) <= 0) return 'Soldée'
  if (parseFloat(d.montant_regle) > 0) return 'Partielle'
  return 'En attente'
}

function getStatutClass(d) {
  if (parseFloat(d.montant_restant) <= 0) return 'status-success'
  if (parseFloat(d.montant_regle) > 0) return 'status-warning'
  return 'status-danger'
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await api.get('/dettes', { 
      params: { search: search.value, statut: filtre.value, page: page.value, per_page: 20 } 
    })
    items.value = data.data || []
    totalPages.value = data.last_page || 1
  } catch (e) { 
    console.error('Erreur Dettes:', e) 
  } finally {
    loading.value = false
  }
}

function changePage(p) {
  page.value = p
  fetchData()
}

onMounted(fetchData)
</script>

<style scoped>
/* ─── Design Tokens Dettes (Rose/Crimson) ─── */
.dette-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #E11D48;
  --c-accent-bg: #FFF1F2;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

/* ─── Hero ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.debt-theme { background: linear-gradient(135deg, #E11D48, #9F1239); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #E11D48; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #E11D48; border-radius: 50%; }

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
.filter-select { padding: 10px 14px; border-radius: 8px; border: 1.5px solid #D5D9E2; font-size: .85rem; font-weight: 600; outline: none; cursor: pointer; }

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700; color: #E11D48; background: #FFF1F2; padding: 4px 8px; border-radius: 6px; }
.supplier-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.source-tag { font-size: .65rem; font-weight: 800; color: #3B82F6; background: #EFF6FF; padding: 3px 8px; border-radius: 4px; }

.amount-cell { font-size: .95rem; font-weight: 800; color: var(--c-text); }
.currency { font-size: .65rem; opacity: .7; margin-left: 2px; }

.status-pill { padding: 4px 12px; border-radius: 100px; font-size: .68rem; font-weight: 800; text-transform: uppercase; }
.status-success { background: #DCFCE7; color: #166534; }
.status-warning { background: #FEF3C7; color: #92400E; }
.status-danger { background: #FEE2E2; color: #991B1B; }

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); transform: translateY(-1px); }

/* ─── Pagination ─── */
.pagination-container { display: flex; justify-content: center; padding: 1.5rem; gap: 8px; border-top: 1px solid var(--c-border); }
.page-link { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; font-size: .85rem; font-weight: 600; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.page-link.active { background: var(--c-accent); color: #fff; border-color: var(--c-accent); box-shadow: 0 4px 10px rgba(225, 29, 72, 0.2); }

.text-danger { color: #DC2626 !important; }
.font-black { font-weight: 900 !important; }
.mono { font-family: 'JetBrains Mono', monospace; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

/* ─── Loader ─── */
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>