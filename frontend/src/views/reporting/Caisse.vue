<template>
  <div class="caisse-view">
    <!-- Top Bar -->
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Analyses</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
          <span class="breadcrumb-current">Journal de Caisse</span>
        </div>
      </div>

      <!-- Period Selection Controls -->
      <div class="topbar-actions flex-wrap gap-2">
        <select v-model="selectedPeriod" class="select-period" @change="onPeriodChange">
          <option value="today">Aujourd'hui (Ce jour)</option>
          <option value="yesterday">Hier</option>
          <option value="last_7_days">7 derniers jours</option>
          <option value="this_month">Ce mois</option>
          <option value="last_month">Mois dernier</option>
          <option value="custom">Personnalisé...</option>
        </select>
        
        <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-1">
          <input type="date" v-model="customStart" class="date-input" @change="fetchData" />
          <span style="color: #64748B; font-weight: bold; font-size: 0.85rem;">à</span>
          <input type="date" v-model="customEnd" class="date-input" @change="fetchData" />
        </div>

        <button class="btn-refresh" @click="fetchData" :disabled="loading" title="Actualiser les données">
          <svg :class="{ 'spin-icon': loading }" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="caisse-loader-overlay">
      <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
      <p>Mise à jour de la caisse...</p>
    </div>

    <!-- KPI Summary Grid -->
    <div class="kpi-grid">
      <!-- Encaissements -->
      <div class="kpi-card-custom money-in">
        <div class="card-inner">
          <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
              <polyline points="17 18 23 18 23 12"/>
            </svg>
          </div>
          <div class="card-body-custom">
            <p class="kpi-title">Encaissements (Entrées)</p>
            <p class="kpi-value-custom text-emerald font-black">{{ formatMoney(cashFlow.encaissements) }} <small>DH</small></p>
            <p class="kpi-subtitle">Ventes payées et acomptes collectés</p>
          </div>
        </div>
      </div>

      <!-- Décaissements -->
      <div class="kpi-card-custom money-out">
        <div class="card-inner">
          <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
              <polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <div class="card-body-custom">
            <p class="kpi-title">Décaissements (Sorties)</p>
            <p class="kpi-value-custom text-rose font-black">{{ formatMoney(cashFlow.decaissements) }} <small>DH</small></p>
            <p class="kpi-subtitle">Paiements fournisseurs & charges directes</p>
          </div>
        </div>
      </div>

      <!-- Solde Caisse -->
      <div class="kpi-card-custom caisse-balance" :class="{ positive: cashFlow.solde_caisse >= 0, negative: cashFlow.solde_caisse < 0 }">
        <div class="card-inner">
          <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <rect x="2" y="4" width="20" height="16" rx="2"/>
              <line x1="12" y1="4" x2="12" y2="20"/>
            </svg>
          </div>
          <div class="card-body-custom">
            <p class="kpi-title">Solde Net de Caisse</p>
            <p class="kpi-value-custom font-black">{{ formatMoney(cashFlow.solde_caisse) }} <small>DH</small></p>
            <p class="kpi-subtitle">Fonds disponibles sur la période</p>
          </div>
        </div>
      </div>

      <!-- Volume Vendu -->
      <div class="kpi-card-custom sales-volume">
        <div class="card-inner">
          <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
          </div>
          <div class="card-body-custom">
            <p class="kpi-title">Volume de Ventes (CA TTC)</p>
            <p class="kpi-value-custom text-slate font-black">{{ formatMoney(profitability.chiffre_affaires_ttc) }} <small>DH</small></p>
            <p class="kpi-subtitle">Factures de vente émises (Soldées ou non)</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Section -->
    <div class="main-card mt-6">
      
      <!-- List Header and Filters -->
      <div class="section-header-bar">
        <div class="left-controls">
          <h2 class="section-title">Ledger des Mouvements de Caisse</h2>
          <div class="flux-pills">
            <button class="pill" :class="{ active: fluxFilter === 'all' }" @click="fluxFilter = 'all'">
              Tous les flux <span class="pill-badge">{{ counts.all }}</span>
            </button>
            <button class="pill entrée" :class="{ active: fluxFilter === 'Entrée' }" @click="fluxFilter = 'Entrée'">
              Entrées (Recettes) <span class="pill-badge bg-emerald">{{ counts.entrées }}</span>
            </button>
            <button class="pill sortie" :class="{ active: fluxFilter === 'Sortie' }" @click="fluxFilter = 'Sortie'">
              Sorties (Dépenses) <span class="pill-badge bg-rose">{{ counts.sorties }}</span>
            </button>
          </div>
        </div>

        <div class="right-controls">
          <div class="search-box">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Filtrer par tiers, réf. ou mode..." 
              class="search-input"
            />
          </div>
        </div>
      </div>

      <!-- Movements Ledger Table -->
      <div class="table-container">
        <table class="saas-table" style="min-width: 950px; width: 100%;">
          <thead>
            <tr>
              <th style="width: 12%">Date</th>
              <th style="width: 12%">Sens</th>
              <th style="width: 25%">Tiers / Description</th>
              <th style="width: 20%">Document Source</th>
              <th style="width: 13%" class="text-center">Mode</th>
              <th style="width: 18%" class="text-right">Montant</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(m, idx) in filteredMovements" :key="idx" class="ligne-row">
              <td class="date-cell">{{ formatDate(m.date) }}</td>
              <td>
                <span class="flux-badge" :class="m.flux.toLowerCase()">
                  {{ m.flux === 'Entrée' ? 'Recette' : 'Dépense' }}
                </span>
              </td>
              <td>
                <div class="tiers-name">{{ m.tiers || 'Divers' }}</div>
                <div v-if="m.observations" class="observations-sub">{{ m.observations }}</div>
              </td>
              <td>
                <span class="ref-doc-tag">{{ m.ref_doc }}</span>
                <div v-if="m.reference && m.reference !== '—'" class="ref-sub">N° Réf: {{ m.reference }}</div>
              </td>
              <td class="text-center">
                <span class="mode-tag">{{ m.mode }}</span>
              </td>
              <td class="text-right mono font-bold price-cell" :class="m.flux.toLowerCase()">
                {{ m.flux === 'Entrée' ? '+' : '-' }} {{ formatMoney(m.montant) }} DH
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredMovements.length === 0">
              <td colspan="6">
                <div class="caisse-empty-state">
                  <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <line x1="12" y1="4" x2="12" y2="20"/>
                  </svg>
                  <h3>Aucun mouvement de caisse</h3>
                  <p>Il n'y a aucun encaissement ou décaissement enregistré pour la période sélectionnée.</p>
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
import { ref, onMounted, computed } from 'vue'
import api from '../../services/api'

const loading = ref(false)
const selectedPeriod = ref('today')
const customStart = ref('')
const customEnd = ref('')
const searchQuery = ref('')
const fluxFilter = ref('all')

const cashFlow = ref({ encaissements: 0, decaissements: 0, solde_caisse: 0 })
const profitability = ref({ chiffre_affaires_ttc: 0 })
const ledgerMovements = ref([])

function onPeriodChange() {
  setPeriodDates()
  fetchData()
}

function setPeriodDates() {
  const todayStr = new Date().toISOString().split('T')[0]
  
  if (selectedPeriod.value === 'today') {
    customStart.value = todayStr
    customEnd.value = todayStr
  } else if (selectedPeriod.value === 'yesterday') {
    const yesterday = new Date()
    yesterday.setDate(yesterday.getDate() - 1)
    const yestStr = yesterday.toISOString().split('T')[0]
    customStart.value = yestStr
    customEnd.value = yestStr
  } else if (selectedPeriod.value === 'last_7_days') {
    const start = new Date()
    start.setDate(start.getDate() - 6)
    customStart.value = start.toISOString().split('T')[0]
    customEnd.value = todayStr
  } else if (selectedPeriod.value === 'this_month') {
    const start = new Date()
    customStart.value = new Date(start.getFullYear(), start.getMonth(), 1).toISOString().split('T')[0]
    customEnd.value = todayStr
  } else if (selectedPeriod.value === 'last_month') {
    const start = new Date()
    const firstDay = new Date(start.getFullYear(), start.getMonth() - 1, 1)
    const lastDay = new Date(start.getFullYear(), start.getMonth(), 0)
    customStart.value = firstDay.toISOString().split('T')[0]
    customEnd.value = lastDay.toISOString().split('T')[0]
  }
}

async function fetchData() {
  loading.value = true
  try {
    const params = { start: customStart.value, end: customEnd.value }
    
    // Fetch summary stats
    const summaryRes = await api.get('/reporting/cash-flow', { params })
    const data = summaryRes.data || summaryRes
    cashFlow.value = data.cash_flow || { encaissements: 0, decaissements: 0, solde_caisse: 0 }
    profitability.value = data.profitability || { chiffre_affaires_ttc: 0 }
    
    // Fetch ledger detailed payments
    const paymentsRes = await api.get('/reporting/payments', { params })
    ledgerMovements.value = paymentsRes.data || paymentsRes
  } catch (error) {
    console.error('Erreur de chargement du module caisse:', error)
  } finally {
    loading.value = false
  }
}

const counts = computed(() => {
  return {
    all: ledgerMovements.value.length,
    entrées: ledgerMovements.value.filter(m => m.flux === 'Entrée').length,
    sorties: ledgerMovements.value.filter(m => m.flux === 'Sortie').length
  }
})

const filteredMovements = computed(() => {
  let result = ledgerMovements.value

  // Sense filter
  if (fluxFilter.value !== 'all') {
    result = result.filter(m => m.flux === fluxFilter.value)
  }

  // Search filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(m => 
      (m.tiers && m.tiers.toLowerCase().includes(q)) ||
      (m.ref_doc && m.ref_doc.toLowerCase().includes(q)) ||
      (m.mode && m.mode.toLowerCase().includes(q)) ||
      (m.reference && m.reference.toLowerCase().includes(q)) ||
      (m.observations && m.observations.toLowerCase().includes(q))
    )
  }

  return result
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dStr) {
  if (!dStr) return '—'
  const date = new Date(dStr)
  if (isNaN(date.getTime())) return dStr
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(() => {
  setPeriodDates()
  fetchData()
})
</script>

<style scoped>
.caisse-view {
  --c-bg: #F8FAFC;
  --c-surface: #FFFFFF;
  --c-border: #E2E8F0;
  --c-border-mid: #CBD5E1;
  --c-text: #0F172A;
  --c-muted: #64748B;
  
  --c-accent: #2563EB;
  --c-accent-bg: #EFF6FF;
  
  --c-emerald: #10B981;
  --c-emerald-bg: #ECFDF5;
  --c-rose: #F43F5E;
  --c-rose-bg: #FFF1F2;
  
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--c-text);
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px;
}

/* ─── Topbar ─── */
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  background: var(--c-surface);
  padding: 16px 20px;
  border-radius: 16px;
  border: 1px solid var(--c-border);
  box-shadow: 0 1px 3px rgba(0,0,0,.02);
  margin-bottom: 24px;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: .9rem;
}
.breadcrumb-parent {
  color: var(--c-muted);
  font-weight: 500;
}
.breadcrumb-current {
  color: var(--c-text);
  font-weight: 700;
}
.select-period {
  padding: 8px 14px;
  border: 1.5px solid var(--c-border);
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--c-text);
  outline: none;
  background: var(--c-surface);
  cursor: pointer;
  min-width: 170px;
}
.date-input {
  padding: 7px 12px;
  border: 1.5px solid var(--c-border);
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
  color: var(--c-text);
}
.btn-refresh {
  background: var(--c-accent-bg);
  color: var(--c-accent);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}
.btn-refresh:hover {
  background: #DBEAFE;
}
.spin-icon {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ─── Loader Overlay ─── */
.caisse-loader-overlay {
  position: fixed;
  inset: 0;
  background: rgba(248, 250, 252, 0.7);
  z-index: 100;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  backdrop-filter: blur(4px);
}
.loader-ring {
  width: 40px;
  height: 40px;
  position: relative;
}
.loader-ring div {
  position: absolute;
  width: 32px;
  height: 32px;
  border: 3px solid transparent;
  border-top-color: var(--c-accent);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* ─── KPI Cards ─── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
}
.kpi-card-custom {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
}
.card-inner {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}
.icon-wrap {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kpi-card-custom.money-in .icon-wrap {
  background: var(--c-emerald-bg);
  color: var(--c-emerald);
}
.kpi-card-custom.money-out .icon-wrap {
  background: var(--c-rose-bg);
  color: var(--c-rose);
}
.kpi-card-custom.caisse-balance.positive {
  background: radial-gradient(100% 100% at 0% 0%, #EFF6FF 0%, #FFFFFF 100%);
  border-color: #BFDBFE;
}
.kpi-card-custom.caisse-balance.positive .icon-wrap {
  background: #DBEAFE;
  color: var(--c-accent);
}
.kpi-card-custom.caisse-balance.negative {
  background: radial-gradient(100% 100% at 0% 0%, #FEF2F2 0%, #FFFFFF 100%);
  border-color: #FECACA;
}
.kpi-card-custom.caisse-balance.negative .icon-wrap {
  background: #FEE2E2;
  color: var(--c-rose);
}
.kpi-card-custom.sales-volume .icon-wrap {
  background: #F1F5F9;
  color: #475569;
}
.card-body-custom {
  flex: 1;
}
.kpi-title {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--c-muted);
  margin: 0 0 4px;
  letter-spacing: 0.05em;
}
.kpi-value-custom {
  font-size: 1.4rem;
  color: var(--c-text);
  margin: 0 0 4px;
}
.kpi-value-custom small {
  font-size: 0.8rem;
  font-weight: 700;
}
.kpi-subtitle {
  font-size: 0.7rem;
  color: var(--c-muted);
  margin: 0;
}
.text-emerald { color: var(--c-emerald); }
.text-rose { color: var(--c-rose); }
.text-slate { color: #334155; }
.font-black { font-weight: 900; }

/* ─── Main Content Section ─── */
.main-card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
}
.section-header-bar {
  padding: 20px;
  border-bottom: 1px solid var(--c-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}
.section-title {
  font-size: 0.95rem;
  font-weight: 800;
  margin: 0 0 12px;
  color: var(--c-text);
}
.flux-pills {
  display: flex;
  gap: 6px;
}
.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid var(--c-border);
  background: var(--c-surface);
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--c-muted);
  cursor: pointer;
  transition: all 0.2s;
  outline: none;
}
.pill:hover {
  background: #F8FAFC;
}
.pill.active {
  background: var(--c-text);
  color: #fff;
  border-color: var(--c-text);
}
.pill-badge {
  font-size: 0.65rem;
  background: #F1F5F9;
  color: var(--c-muted);
  padding: 1px 6px;
  border-radius: 10px;
}
.pill.active .pill-badge {
  background: rgba(255,255,255,0.2);
  color: #fff;
}
.pill.active.entrée {
  background: var(--c-emerald);
  border-color: var(--c-emerald);
}
.pill.active.sortie {
  background: var(--c-rose);
  border-color: var(--c-rose);
}

/* Search Box */
.search-box {
  position: relative;
  max-width: 320px;
}
.search-input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  font-size: 0.8rem;
  border: 1.5px solid var(--c-border-mid);
  border-radius: 8px;
  background: var(--c-surface);
  outline: none;
  transition: all 0.2s;
}
.search-input:focus {
  border-color: var(--c-accent);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--c-muted);
}

/* ─── Table ─── */
.table-container {
  overflow-x: auto;
}
.saas-table {
  border-collapse: collapse;
  table-layout: fixed;
}
.saas-table th {
  background: #F8FAFC;
  padding: 14px 16px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--c-muted);
  border-bottom: 2px solid var(--c-border);
  letter-spacing: 0.05em;
  text-align: left;
}
.saas-table td {
  padding: 16px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: middle;
  font-size: 0.82rem;
}
.ligne-row {
  background: #FCFDFE;
  transition: background .15s;
}
.ligne-row:nth-child(even) {
  background: #F8FAFC;
}
.ligne-row:hover {
  background: #EFF6FF !important;
}
.ligne-row:last-child td {
  border-bottom: none;
}

.date-cell {
  font-weight: 600;
  color: var(--c-muted);
}
.flux-badge {
  display: inline-block;
  padding: 3px 8px;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  border-radius: 6px;
  letter-spacing: 0.02em;
}
.flux-badge.entrée {
  background: var(--c-emerald-bg);
  color: var(--c-emerald);
}
.flux-badge.sortie {
  background: var(--c-rose-bg);
  color: var(--c-rose);
}

.tiers-name {
  font-weight: 700;
  color: var(--c-text);
}
.observations-sub {
  font-size: 0.72rem;
  color: var(--c-muted);
  margin-top: 2px;
}

.ref-doc-tag {
  display: inline-block;
  background: #F1F5F9;
  color: #475569;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
}
.ref-sub {
  font-size: 0.7rem;
  font-family: 'JetBrains Mono', monospace;
  color: var(--c-muted);
  margin-top: 2px;
}

.mode-tag {
  display: inline-block;
  background: #EFF6FF;
  color: var(--c-accent);
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
}

.price-cell.entrée {
  color: var(--c-emerald);
}
.price-cell.sortie {
  color: var(--c-rose);
}
.mono {
  font-family: 'JetBrains Mono', monospace;
}
.font-bold {
  font-weight: 700;
}
.text-center {
  text-align: center;
}
.text-right {
  text-align: right;
}

/* Empty State */
.caisse-empty-state {
  padding: 60px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: var(--c-muted);
}
.caisse-empty-state svg {
  color: var(--c-border-mid);
  margin-bottom: 16px;
}
.caisse-empty-state h3 {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--c-text);
  margin: 0 0 6px;
}
.caisse-empty-state p {
  font-size: 0.8rem;
  max-width: 320px;
  margin: 0;
  line-height: 1.5;
}

/* ─── Utilities ─── */
.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.mt-4 { margin-top: 16px; }
.mt-6 { margin-top: 24px; }
.overflow-hidden { overflow: hidden; }

@media (max-width: 768px) {
  .topbar {
    flex-direction: column;
    align-items: stretch;
  }
  .section-header-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .right-controls .search-box {
    max-width: 100%;
  }
}
</style>
