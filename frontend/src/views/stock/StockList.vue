<template>
  <div class="stock-list-view animate-fade-in">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Analyse de l'inventaire...</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Logistique</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">État du Stock</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-secondary-custom" title="Transférer entre dépôts">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
          <span>Mouvement Interne</span>
        </button>
        <button class="btn-primary-custom" title="Effectuer un inventaire physique">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Nouvel Inventaire</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar stock-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Inventaire Temps Réel
        </div>
        <h1 class="hero-name">État des Stocks</h1>
        <p class="hero-sub">Vous suivez actuellement <strong>{{ stock.length }}</strong> références réparties sur vos entrepôts.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Rechercher un produit par désignation ou référence..." />
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 35%">Désignation Produit</th>
              <th style="width: 15%">Entrepôt</th>
              <th style="width: 12%" class="text-right">Physique</th>
              <th style="width: 12%" class="text-right">Réservé</th>
              <th style="width: 12%" class="text-right">Disponible</th>
              <th style="width: 8%" class="text-center">État</th>
              <th style="width: 6%" class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filteredStock" :key="s.id" class="table-row">
              <td>
                <div class="product-info">
                  <span class="code-badge mono">{{ s.produit?.reference }}</span>
                  <div class="product-name">{{ s.produit?.designation }}</div>
                </div>
              </td>
              <td>
                <div class="warehouse-tag">{{ s.entrepot?.nom || 'Dépôt Inconnu' }}</div>
              </td>
              <td class="text-right">
                <span class="amount-cell">{{ s.quantite_physique }}</span>
              </td>
              <td class="text-right">
                <span class="amount-cell text-muted">{{ s.quantite_reservee }}</span>
              </td>
              <td class="text-right">
                <div class="amount-cell" :class="{'text-danger font-black': s.quantite_disponible <= 0, 'text-accent': s.quantite_disponible > 0}">
                  {{ s.quantite_disponible }}
                </div>
              </td>
              <td class="text-center">
                <span class="status-pill-dynamic" :class="getStatusClass(s)">
                  {{ getStatusLabel(s) }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                    <button class="action-btn" title="Ajuster le stock" @click="openAction(s, 'adjust')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="action-btn" title="Historique des mouvements" @click="openHistory(s)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </button>
                    <button class="action-btn" title="Transférer" @click="openAction(s, 'transfer')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredStock.length === 0 && !loading">
              <td colspan="7" class="empty-row">
                <div class="empty-content">
                  <p>Aucun produit ne correspond à votre recherche ou l'inventaire est vide.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <StockActionModal
      :is-open="isModalOpen"
      :mode="modalMode"
      :produit="selectedStock"
      :current-entrepot="selectedStock?.entrepot"
      :entrepots="entrepots"
      @close="isModalOpen = false"
      @success="fetchData"
    />

    <StockHistoryModal
      :is-open="isHistoryOpen"
      :stock-id="selectedStockId"
      @close="isHistoryOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import StockActionModal from './StockActionModal.vue'
import StockHistoryModal from './StockHistoryModal.vue'

const stock = ref([])
const entrepots = ref([])
const loading = ref(true)
const searchQuery = ref('')

// Modal state
const isModalOpen = ref(false)
const isHistoryOpen = ref(false)
const modalMode = ref('adjust')
const selectedStock = ref(null)
const selectedStockId = ref(null)

const filteredStock = computed(() => {
  if (!searchQuery.value) return stock.value
  const q = searchQuery.value.toLowerCase()
  return stock.value.filter(s => 
    s.produit?.designation?.toLowerCase().includes(q) || 
    s.produit?.reference?.toLowerCase().includes(q)
  )
})

const fetchData = async () => {
  loading.value = true
  try {
    const [stockRes, entrepotsRes] = await Promise.all([
      api.get('/stock'),
      api.get('/parametrage/referentiels/entrepots')
    ])
    stock.value = stockRes.data.data || stockRes.data || []
    entrepots.value = entrepotsRes.data || []
  } catch (error) {
    console.error('Erreur inventaire:', error)
  } finally {
    loading.value = false
  }
}

const openAction = (item, mode) => {
  selectedStock.value = item
  modalMode.value = mode
  isModalOpen.value = true
}

const openHistory = (item) => {
  selectedStockId.value = item.id
  isHistoryOpen.value = true
}

const getStatusClass = (s) => {
  const qty = s.quantite_disponible
  const threshold = s.produit?.seuil_alerte || 0
  if (qty <= 0) return 'status-danger'
  if (qty <= threshold) return 'status-warning'
  return 'status-success'
}

const getStatusLabel = (s) => {
  const qty = s.quantite_disponible
  const threshold = s.produit?.seuil_alerte || 0
  if (qty <= 0) return 'RUPTURE'
  if (qty <= threshold) return 'SOUS SEUIL'
  return 'DISPONIBLE'
}

onMounted(fetchData)
</script>

<style scoped>
/* ─── Design Tokens (Teal / Stock) ─── */
.stock-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #0D9488;
  --c-accent-bg: #F0FDFA;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; gap: 12px; }
.btn-primary-custom, .btn-secondary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  border-radius: 8px; font-size: .85rem; font-weight: 600; text-decoration: none; cursor: pointer;
  transition: all .2s; outline: none; border: 1.5px solid transparent;
}
.btn-primary-custom { background: var(--c-accent); color: #fff; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); }
.btn-primary-custom:hover { background: #0F766E; transform: translateY(-1px); }
.btn-secondary-custom { background: #fff; color: var(--c-text); border-color: var(--c-border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.btn-secondary-custom:hover { background: #F9FAFB; border-color: #D1D5DB; }

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.stock-theme { background: linear-gradient(135deg, #0D9488, #0F766E); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-sub { color: var(--c-muted); margin-top: 2px; }

/* ─── Filters ─── */
.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.search-wrapper {
  flex: 1; display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: 8px; color: var(--c-muted);
}
.search-wrapper input { flex: 1; padding: 12px 0; border: none; background: transparent; outline: none; font-size: .9rem; color: var(--c-text); }

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; color: var(--c-text); font-size: 0.9rem;}
.table-row:hover { background: #F9FAFB; }

.product-info { display: flex; flex-direction: column; gap: 4px; }
.product-name { font-size: .95rem; font-weight: 700; color: var(--c-text); }
.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .72rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px; width: fit-content; text-transform: uppercase; }
.warehouse-tag { font-size: .75rem; font-weight: 600; color: #64748B; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; width: fit-content; border: 1px solid #E2E8F0; }

.amount-cell { font-size: .95rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
.text-muted { color: var(--c-muted); }
.text-danger { color: #DC2626 !important; }
.text-accent { color: var(--c-accent); }
.font-black { font-weight: 900 !important; }

.status-pill-dynamic { 
  display: inline-block; padding: 4px 12px; border-radius: 100px; 
  font-size: .72rem; font-weight: 800; text-transform: uppercase;
}
.status-success { background: #DCFCE7; color: #166534; }
.status-warning { background: #FFEDD5; color: #9A3412; }
.status-danger { background: #FEE2E2; color: #991B1B; }

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
/* Correction de la classe action-btn au lieu de btn-icon */
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }

.text-right { text-align: right; }
.text-center { text-align: center; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

/* ─── Loader ─── */
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>