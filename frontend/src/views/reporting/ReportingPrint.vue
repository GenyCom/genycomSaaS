<template>
  <div class="print-layout">
    <div class="no-print print-actions">
      <button class="btn btn-primary" @click="doPrint" :disabled="loading">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Imprimer / Sauvegarder PDF
      </button>
      <button class="btn btn-secondary" @click="closeWindow">Fermer</button>
    </div>

    <div v-if="loading" class="loading-state">
       <div class="spinner"></div>
       <p>Génération du rapport...</p>
    </div>

    <div v-else class="page-a4">
      <header class="doc-header">
         <div class="company-logo">
           <img v-if="entreprise.logo_path" :src="entreprise.logo_path" alt="Logo" class="print-logo" />
           <h1 v-else>{{ entreprise.raison_sociale || 'GenyCom' }}</h1>
           <p class="company-details">
             <strong>{{ entreprise.raison_sociale }}</strong><br/>
             {{ entreprise.adresse }}<br/>
             {{ entreprise.ville }}
           </p>
         </div>
         <div class="doc-meta">
           <h2 class="doc-title">RAPPORT DE CAISSE & BÉNÉFICE</h2>
           <div class="meta-row"><span class="meta-label">Période</span><span class="meta-value">Du {{ formatDate(start) }} au {{ formatDate(end) }}</span></div>
           <div class="meta-row"><span class="meta-label">Édité le</span><span class="meta-value">{{ new Date().toLocaleDateString('fr-FR') }}</span></div>
         </div>
      </header>

      <section class="summary-section">
        <h3>Synthèse de Flux de Trésorerie</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="label">Total Encaissé</span>
            <span class="value success">{{ formatMoney(data.cash_flow.encaissements) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Total Décaissements</span>
            <span class="value danger">{{ formatMoney(data.cash_flow.decaissements) }}</span>
          </div>
          <div class="summary-item highlight">
            <span class="label">Solde Caisse</span>
            <span class="value" :class="data.cash_flow.solde_caisse >= 0 ? 'success' : 'danger'">
              {{ formatMoney(data.cash_flow.solde_caisse) }}
            </span>
          </div>
        </div>
      </section>

      <section class="summary-section mt-4">
        <h3>Rentabilité & Bénéfice Net</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="label">Chiffre d'Affaires TTC</span>
            <span class="value">{{ formatMoney(data.profitability.chiffre_affaires_ttc) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Chiffre d'Affaires HT</span>
            <span class="value">{{ formatMoney(data.profitability.chiffre_affaires_ht) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Coût des Ventes HT</span>
            <span class="value text-muted">- {{ formatMoney(data.profitability.cout_ventes_ht) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Charges Fixes HT</span>
            <span class="value text-muted">- {{ formatMoney(data.profitability.charges_fixes) }}</span>
          </div>
          <div class="summary-item highlight-alt">
            <span class="label">Bénéfice Net</span>
            <span class="value" :class="data.profitability.benefice_net >= 0 ? 'success' : 'danger'">
              {{ formatMoney(data.profitability.benefice_net) }}
            </span>
            <span class="marge-badge" v-if="data.profitability.marge_pct">Marge : {{ data.profitability.marge_pct }}%</span>
          </div>
        </div>
      </section>

      <section class="details-section mt-4">
        <h3>Détail Journalier</h3>
        <table class="print-table">
          <thead>
            <tr>
              <th>Date</th>
              <th class="text-right">CA HT</th>
              <th class="text-right">Coût Achats HT</th>
              <th class="text-right">Marge Brute HT</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="day in data.daily_summary" :key="day.date">
              <td>{{ formatDate(day.date) }}</td>
              <td class="text-right">{{ formatMoney(day.ca) }}</td>
              <td class="text-right text-muted">{{ formatMoney(day.cogs) }}</td>
              <td class="text-right font-bold" :class="(day.ca - day.cogs) >= 0 ? 'success' : 'danger'">
                {{ formatMoney(day.ca - day.cogs) }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <footer class="print-footer">
        <p>Généré par GenyCom SaaS - Logiciel de gestion commerçante</p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const start = route.query.start
const end = route.query.end

const loading = ref(true)
const data = ref(null)
const entreprise = ref({})

function formatMoney(val) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(val || 0)
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR')
}

function doPrint() { window.print() }
function closeWindow() { window.close() }

onMounted(async () => {
  try {
    const [resEnt, resData] = await Promise.all([
      api.get('/parametrage/entreprise'),
      api.get('/reporting/cash-flow', { params: { start, end } })
    ])
    entreprise.value = resEnt.data || {}
    data.value = resData.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
body { background-color: #f3f4f6; margin: 0; padding: 0; }
.print-layout { display: flex; flex-direction: column; align-items: center; padding: 20px; font-family: 'Inter', sans-serif; }
.no-print { margin-bottom: 20px; display: flex; gap: 10px; }
.btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; }
.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #e5e7eb; color: #374151; }

.page-a4 { width: 210mm; min-height: 297mm; background: white; padding: 20mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }

.doc-header { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
.print-logo { max-height: 60px; }
.company-details { font-size: 0.8rem; color: #64748b; margin-top: 10px; }
.doc-title { font-size: 1.2rem; font-weight: 800; color: #1e293b; margin: 0 0 10px 0; }
.meta-row { display: flex; gap: 10px; font-size: 0.9rem; justify-content: flex-end; }
.meta-label { color: #64748b; }
.meta-value { font-weight: 700; }

h3 { font-size: 1rem; border-left: 4px solid #3b82f6; padding-left: 10px; margin-bottom: 15px; color: #1e293b; }

.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: #e2e8f0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
.summary-item { background: white; padding: 15px; display: flex; flex-direction: column; gap: 5px; }
.summary-item.highlight { background: #eff6ff; }
.summary-item.highlight-alt { background: #f8fafc; }
.summary-item .label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
.summary-item .value { font-size: 1.1rem; font-weight: 800; }
.summary-item .success { color: #059669; }
.summary-item .danger { color: #dc2626; }
.marge-badge { font-size: 0.7rem; font-weight: 700; color: #3b82f6; }

.print-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.print-table th { background: #f8fafc; padding: 10px; text-align: left; font-size: 0.8rem; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
.print-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.text-right { text-align: right; }
.text-muted { color: #64748b; }
.font-bold { font-weight: 700; }
.success { color: #059669; }
.danger { color: #dc2626; }

.print-footer { margin-top: 50px; text-align: center; font-size: 0.7rem; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }

@media print {
  body { background: white; }
  .no-print { display: none; }
  .page-a4 { box-shadow: none; width: 100%; padding: 0; }
  .print-layout { padding: 0; }
}
</style>
