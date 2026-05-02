<template>
  <div class="reporting-view">
    <div class="top-header">
      <div class="header-left">
        <h1 class="page-title">Reporting & Analyses</h1>
        <p class="page-subtitle">Suivi détaillé de votre performance commerciale et financière</p>
      </div>
      <div class="header-right">
        <div class="date-picker-group">
          <div class="input-field">
            <label>Du</label>
            <input type="date" v-model="dateRange.start" @change="fetchData" />
          </div>
          <div class="input-field">
            <label>Au</label>
            <input type="date" v-model="dateRange.end" @change="fetchData" />
          </div>
        </div>
        <button class="btn-refresh" @click="fetchData" :class="{ spinning: loading }">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </button>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="reporting-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id" 
        :class="['tab-btn', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
      >
        <component :is="tab.icon" class="tab-icon" />
        {{ tab.label }}
      </button>
    </div>

    <div class="reporting-content">
      <!-- Ventes Journal -->
      <div v-if="activeTab === 'sales'" class="tab-pane">
        <div class="summary-cards">
          <div class="mini-card">
            <span class="label">Total HT</span>
            <span class="value">{{ formatMoney(salesData.totals.ht) }}</span>
          </div>
          <div class="mini-card">
            <span class="label">Total TVA</span>
            <span class="value">{{ formatMoney(salesData.totals.tva) }}</span>
          </div>
          <div class="mini-card accent">
            <span class="label">Total TTC</span>
            <span class="value">{{ formatMoney(salesData.totals.ttc) }}</span>
          </div>
        </div>

        <div class="table-card mt-4">
          <div class="card-header">
            <div class="header-with-filter">
              <h3>Journal des Ventes</h3>
              <select v-model="filters.clientId" @change="fetchData" class="filter-select">
                <option :value="null">Tous les clients</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.societe }}</option>
              </select>
            </div>
            <button @click="exportCSV(salesData.journal, 'journal_ventes')" class="btn-export">Exporter CSV</button>
          </div>
          <div class="table-responsive">
            <table class="report-table">
              <thead>
                <tr>
                  <th>N° Facture</th>
                  <th>Date</th>
                  <th>Client</th>
                  <th class="text-right">HT</th>
                  <th class="text-right">TVA</th>
                  <th class="text-right">TTC</th>
                  <th class="text-center">Statut</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in salesData.journal" :key="item.id">
                  <td><span class="badge-code">{{ item.numero }}</span></td>
                  <td>{{ formatDate(item.date_facture) }}</td>
                  <td class="font-bold">{{ item.societe }}</td>
                  <td class="text-right">{{ formatMoney(item.total_ht) }}</td>
                  <td class="text-right">{{ formatMoney(item.total_tva) }}</td>
                  <td class="text-right font-bold">{{ formatMoney(item.total_ttc) }}</td>
                  <td class="text-center">
                    <span :class="['status-pill', item.est_reglee ? 'paid' : 'pending']">
                      {{ item.est_reglee ? 'Réglée' : 'En attente' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="salesData.journal.length === 0">
                  <td colspan="7" class="empty-state">Aucune donnée sur cette période</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Achats & Dépenses -->
      <div v-if="activeTab === 'purchases'" class="tab-pane">
        <div class="table-card">
          <div class="card-header">
            <div class="header-with-filter">
              <h3>Journal des Achats & Dépenses</h3>
              <select v-model="filters.supplierId" @change="fetchData" class="filter-select">
                <option :value="null">Tous les fournisseurs</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.societe }}</option>
              </select>
            </div>
            <button @click="exportCSV(purchaseData, 'journal_achats')" class="btn-export">Exporter CSV</button>
          </div>
          <div class="table-responsive">
            <table class="report-table">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Référence / Libellé</th>
                  <th>Date</th>
                  <th>Fournisseur / Bénéficiaire</th>
                  <th class="text-right">HT</th>
                  <th class="text-right">TVA</th>
                  <th class="text-right">TTC</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in purchaseData" :key="idx">
                  <td>
                    <span :class="['type-badge', item.type === 'achat' ? 'blue' : 'purple']">
                      {{ item.type === 'achat' ? 'ACHAT' : 'DÉPENSE' }}
                    </span>
                  </td>
                  <td>{{ item.numero || '—' }}</td>
                  <td>{{ formatDate(item.date_facture) }}</td>
                  <td class="font-bold">{{ item.societe }}</td>
                  <td class="text-right">{{ formatMoney(item.total_ht) }}</td>
                  <td class="text-right">{{ formatMoney(item.total_tva) }}</td>
                  <td class="text-right font-bold">{{ formatMoney(item.total_ttc) }}</td>
                </tr>
                <tr v-if="purchaseData.length === 0">
                  <td colspan="7" class="empty-state">Aucune donnée sur cette période</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Finance & TVA -->
      <div v-if="activeTab === 'finance'" class="tab-pane">
        <div class="finance-grid">
          <div class="table-card">
            <div class="card-header"><h3>Synthèse TVA</h3></div>
            <div class="vat-details">
              <div class="vat-row">
                <span>TVA Collectée (Ventes)</span>
                <span class="val pos">+ {{ formatMoney(financeData.vat.collected_vat) }}</span>
              </div>
              <div class="vat-row">
                <span>TVA Déductible (Achats)</span>
                <span class="val neg">- {{ formatMoney(financeData.vat.deductible_vat) }}</span>
              </div>
              <div class="vat-divider"></div>
              <div class="vat-row total">
                <span>TVA Nette à décaisser</span>
                <span class="val">{{ formatMoney(financeData.vat.net_vat) }}</span>
              </div>
            </div>
          </div>

          <div class="table-card">
            <div class="card-header"><h3>Rentabilité par Projet</h3></div>
            <div class="table-responsive">
              <table class="report-table mini">
                <thead>
                  <tr>
                    <th>Projet</th>
                    <th class="text-right">Marge HT</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(p, idx) in financeData.profitability" :key="idx">
                    <td>{{ p.nom_projet }}</td>
                    <td class="text-right font-bold text-success">
                      {{ formatMoney(p.revenue_ht) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Stock -->
      <div v-if="activeTab === 'stock'" class="tab-pane">
        <div class="summary-cards">
          <div class="mini-card">
            <span class="label">Valeur Totale (Achat)</span>
            <span class="value">{{ formatMoney(stockData.total_value_purchase) }}</span>
          </div>
          <div class="mini-card accent">
            <span class="label">Valeur Totale (Vente)</span>
            <span class="value">{{ formatMoney(stockData.total_value_sale) }}</span>
          </div>
          <div class="mini-card success">
            <span class="label">Marge Potentielle</span>
            <span class="value">{{ formatMoney(stockData.total_value_sale - stockData.total_value_purchase) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loader-overlay">
      <div class="spinner"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, markRaw } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'

// Icons (inline SVG components)
const IconSales = markRaw({
  template: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>`
})
const IconPurchase = markRaw({
  template: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`
})
const IconFinance = markRaw({
  template: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`
})
const IconStock = markRaw({
  template: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>`
})

const tabs = [
  { id: 'sales', label: 'Ventes', icon: IconSales },
  { id: 'purchases', label: 'Achats & Dépenses', icon: IconPurchase },
  { id: 'finance', label: 'Finance & Projets', icon: IconFinance },
  { id: 'stock', label: 'Stock', icon: IconStock }
]

const activeTab = ref('sales')
const loading = ref(false)
const dateRange = reactive({
  start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
})

const salesData = ref({ journal: [], by_client: [], totals: { ht: 0, tva: 0, ttc: 0 } })
const purchaseData = ref([])
const financeData = ref({ vat: { collected_vat: 0, deductible_vat: 0, net_vat: 0 }, profitability: [] })
const stockData = ref({ total_value_purchase: 0, total_value_sale: 0 })

const clients = ref([])
const suppliers = ref([])
const filters = reactive({
  clientId: null,
  supplierId: null
})

async function fetchFilterData() {
  try {
    const [cRes, sRes] = await Promise.all([
      api.get('/clients'),
      api.get('/fournisseurs')
    ])
    clients.value = cRes.data.data || cRes.data
    suppliers.value = sRes.data.data || sRes.data
  } catch (e) { console.error(e) }
}

async function fetchData() {
  loading.value = true
  const params = { 
    start: dateRange.start, 
    end: dateRange.end,
    client_id: filters.clientId,
    fournisseur_id: filters.supplierId
  }
  try {
    const [salesRes, purchaseRes, financeRes, stockRes] = await Promise.all([
      api.get('/reporting/sales', { params }),
      api.get('/reporting/purchases', { params }),
      api.get('/reporting/finance', { params }),
      api.get('/reporting/stock')
    ])

    salesData.value = salesRes.data
    // Calculate sales totals
    salesData.value.totals = salesData.value.journal.reduce((acc, curr) => {
      acc.ht += parseFloat(curr.total_ht)
      acc.tva += parseFloat(curr.total_tva)
      acc.ttc += parseFloat(curr.total_ttc)
      return acc
    }, { ht: 0, tva: 0, ttc: 0 })

    purchaseData.value = purchaseRes.data
    financeData.value = financeRes.data
    stockData.value = stockRes.data
  } catch (error) {
    toast.error("Erreur lors du chargement des rapports")
  } finally {
    loading.value = false
  }
}

function formatMoney(val) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(val || 0)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR')
}

function exportCSV(data, filename) {
  if (!data || !data.length) return
  const headers = Object.keys(data[0]).join(',')
  const rows = data.map(obj => Object.values(obj).join(','))
  const csvContent = "data:text/csv;charset=utf-8," + headers + "\n" + rows.join("\n")
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement("a")
  link.setAttribute("href", encodedUri)
  link.setAttribute("download", `${filename}_${dateRange.start}_to_${dateRange.end}.csv`)
  document.body.appendChild(link)
  link.click()
}

onMounted(() => {
  fetchFilterData()
  fetchData()
})
</script>

<style scoped>
.reporting-view { padding: 20px; background: #f8fafc; min-height: 100vh; }
.top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0; }
.page-subtitle { font-size: 0.875rem; color: #64748b; margin: 4px 0 0; }

.header-right { display: flex; align-items: flex-end; gap: 12px; }
.date-picker-group { display: flex; gap: 8px; background: white; padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0; }
.input-field { display: flex; flex-direction: column; gap: 2px; }
.input-field label { font-size: 0.65rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.input-field input { border: none; font-size: 0.85rem; color: #1e293b; font-weight: 600; outline: none; }

.btn-refresh { background: white; border: 1px solid #e2e8f0; padding: 10px; border-radius: 10px; cursor: pointer; color: #64748b; transition: all 0.2s; height: 45px; width: 45px; display: flex; align-items: center; justify-content: center; }
.btn-refresh:hover { color: #3b82f6; border-color: #3b82f6; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.reporting-tabs { display: flex; gap: 4px; background: #e2e8f0; padding: 4px; border-radius: 12px; margin-bottom: 24px; width: fit-content; }
.tab-btn { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: none; background: transparent; border-radius: 8px; font-size: 0.875rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
.tab-btn.active { background: white; color: #3b82f6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.tab-icon { width: 18px; height: 18px; }

.summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.mini-card { background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 4px; }
.mini-card .label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.mini-card .value { font-size: 1.25rem; font-weight: 800; color: #1e293b; }
.mini-card.accent { border-left: 4px solid #3b82f6; }
.mini-card.success { border-left: 4px solid #10b981; }

.table-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.card-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
.card-header h3 { font-size: 0.9rem; font-weight: 700; color: #334155; margin: 0; }
.btn-export { font-size: 0.75rem; font-weight: 600; color: #3b82f6; background: #eff6ff; border: 1px solid #dbeafe; padding: 6px 12px; border-radius: 6px; cursor: pointer; }

.table-responsive { overflow-x: auto; }
.report-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.report-table th { text-align: left; padding: 12px 20px; background: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; border-bottom: 1px solid #e2e8f0; }
.report-table td { padding: 12px 20px; border-bottom: 1px solid #f1f5f9; color: #334155; }
.report-table tr:last-child td { border-bottom: none; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.badge-code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: 700; color: #3b82f6; }
.status-pill { padding: 2px 8px; border-radius: 100px; font-size: 0.7rem; font-weight: 700; }
.status-pill.paid { background: #dcfce7; color: #166534; }
.status-pill.pending { background: #fef3c7; color: #92400e; }
.type-badge { padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 800; }
.type-badge.blue { background: #dbeafe; color: #1e40af; }
.type-badge.purple { background: #f3e8ff; color: #6b21a8; }
.empty-state { text-align: center; padding: 40px !important; color: #94a3b8; font-style: italic; }

.finance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px; }
.vat-details { padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.vat-row { display: flex; justify-content: space-between; font-weight: 600; color: #475569; }
.vat-row.total { font-size: 1.1rem; font-weight: 800; color: #1e293b; }
.vat-row .pos { color: #059669; }
.vat-row .neg { color: #dc2626; }
.vat-divider { height: 1px; background: #e2e8f0; margin: 4px 0; }
.text-success { color: #059669; }
.text-danger { color: #dc2626; }

.loader-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); display: flex; align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(2px); }
.spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
.mt-4 { margin-top: 1rem; }

.header-with-filter { display: flex; align-items: center; gap: 12px; flex: 1; }
.filter-select { 
  padding: 4px 8px; 
  border-radius: 6px; 
  border: 1px solid #e2e8f0; 
  font-size: 0.8rem; 
  color: #334155; 
  font-weight: 600; 
  outline: none;
  background: white;
  max-width: 250px;
}
.filter-select:focus { border-color: #3b82f6; }
</style>
