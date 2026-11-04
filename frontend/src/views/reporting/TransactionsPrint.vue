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

    <div v-else-if="reportData" class="page-a4">
      <header class="doc-header">
        <div class="company-logo">
          <img v-if="entreprise.logo_path" :src="entreprise.logo_path" alt="Logo" class="print-logo" />
          <h1 v-else>{{ entreprise.raison_sociale || 'GenyCom' }}</h1>
          <p class="company-details">
            <strong>{{ entreprise.raison_sociale }}</strong><br/>
            {{ entreprise.adresse }}<br/>
            {{ entreprise.ville }}
            <template v-if="entreprise.telephone"><br/>Tél: {{ entreprise.telephone }}</template>
            <template v-if="entreprise.ice"><br/>ICE: {{ entreprise.ice }}</template>
          </p>
        </div>
        <div class="doc-meta">
          <h2 class="doc-title">{{ isSupplier ? 'RAPPORT DES ACHATS FOURNISSEURS' : 'RAPPORT DES VENTES CLIENTS' }}</h2>
          <div class="doc-subtitle">Transactions et Détail des Règlements</div>
          <div class="meta-row"><span class="meta-label">Période :</span><span class="meta-value">Du {{ formatDate(start) }} au {{ formatDate(end) }}</span></div>
          <div class="meta-row" v-if="filterName"><span class="meta-label">{{ isSupplier ? 'Fournisseur :' : 'Client :' }}</span><span class="meta-value">{{ filterName }}</span></div>
          <div class="meta-row"><span class="meta-label">Édité le :</span><span class="meta-value">{{ new Date().toLocaleDateString('fr-FR') }}</span></div>
        </div>
      </header>

      <!-- Synthèse Globale -->
      <section class="summary-section">
        <h3>Synthèse de la période ({{ reportData.totals.count }} transaction{{ reportData.totals.count > 1 ? 's' : '' }})</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="label">Total HT</span>
            <span class="value">{{ formatMoney(reportData.totals.total_ht) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Total TTC</span>
            <span class="value">{{ formatMoney(reportData.totals.total_ttc) }}</span>
          </div>
          <div class="summary-item highlight-success">
            <span class="label">Total Réglé</span>
            <span class="value success">{{ formatMoney(reportData.totals.total_regle) }}</span>
          </div>
          <div class="summary-item highlight-danger">
            <span class="label">Reste à Payer</span>
            <span class="value danger">{{ formatMoney(reportData.totals.total_reste_a_payer) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Taux de Règlement</span>
            <span class="value" :class="reportData.totals.taux_reglement >= 80 ? 'success' : (reportData.totals.taux_reglement >= 50 ? 'warning' : 'danger')">
              {{ reportData.totals.taux_reglement }}%
            </span>
          </div>
        </div>
      </section>

      <!-- Tableau Détaillé des Transactions -->
      <section class="details-section mt-4">
        <h3>Liste des Transactions et Règlements</h3>
        <table class="print-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>N° Pièce</th>
              <th>{{ isSupplier ? 'Fournisseur' : 'Client' }}</th>
              <th class="text-right">Total HT</th>
              <th class="text-right">Total TTC</th>
              <th class="text-right">Réglé</th>
              <th class="text-right">Reste</th>
              <th class="text-center">Statut</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(t, idx) in reportData.transactions" :key="idx">
              <!-- Ligne principale de la transaction -->
              <tr class="transaction-row">
                <td class="whitespace-nowrap">{{ formatDate(t.date_facture) }}</td>
                <td class="font-bold">{{ t.numero }}</td>
                <td>
                  <strong>{{ t.societe }}</strong>
                  <div class="tiers-sub" v-if="t.ice">ICE: {{ t.ice }}</div>
                </td>
                <td class="text-right">{{ formatMoney(t.total_ht) }}</td>
                <td class="text-right font-bold">{{ formatMoney(t.total_ttc) }}</td>
                <td class="text-right font-bold success">{{ formatMoney(t.montant_regle) }}</td>
                <td class="text-right font-bold" :class="t.reste_a_payer > 0.01 ? 'danger' : 'success'">
                  {{ formatMoney(t.reste_a_payer) }}
                </td>
                <td class="text-center">
                  <span class="badge" :class="t.statut_reglement">
                    {{ t.statut_reglement === 'paye' ? 'Réglée' : (t.statut_reglement === 'partiel' ? 'Partielle' : 'En attente') }}
                  </span>
                </td>
              </tr>
              <!-- Sous-tableau des détails de règlement -->
              <tr class="sub-table-row">
                <td colspan="8" class="sub-table-container">
                  <div class="settlements-box" v-if="t.reglements && t.reglements.length > 0">
                    <div class="settlements-title">
                      <span>Détail des règlements ({{ t.reglements.length }}) :</span>
                    </div>
                    <table class="sub-print-table">
                      <thead>
                        <tr>
                          <th>Date Règlement</th>
                          <th>Mode</th>
                          <th>Réf / N° Chèque</th>
                          <th>Banque</th>
                          <th>Échéance</th>
                          <th>Observations</th>
                          <th class="text-right">Montant</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="r in t.reglements" :key="r.id">
                          <td>{{ formatDate(r.date_reglement) }}</td>
                          <td><strong>{{ r.mode || 'Espèces' }}</strong></td>
                          <td>{{ r.numero_cheque || r.reference || '—' }}</td>
                          <td>{{ r.banque || '—' }}</td>
                          <td>{{ r.date_echeance_cheque ? formatDate(r.date_echeance_cheque) : '—' }}</td>
                          <td class="text-muted">{{ r.observations || '—' }}</td>
                          <td class="text-right font-bold success">+ {{ formatMoney(r.montant) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="settlements-empty" v-else>
                    <span class="text-muted">Aucun règlement enregistré sur cette pièce. Reste intégralement dû : <strong>{{ formatMoney(t.reste_a_payer) }}</strong></span>
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="!reportData.transactions || reportData.transactions.length === 0">
              <td colspan="8" class="text-center text-muted" style="padding: 24px;">Aucune transaction enregistrée sur cette période.</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="3" class="font-bold">TOTAL GÉNÉRAL</td>
              <td class="text-right font-bold">{{ formatMoney(reportData.totals.total_ht) }}</td>
              <td class="text-right font-bold">{{ formatMoney(reportData.totals.total_ttc) }}</td>
              <td class="text-right font-bold success">{{ formatMoney(reportData.totals.total_regle) }}</td>
              <td class="text-right font-bold danger">{{ formatMoney(reportData.totals.total_reste_a_payer) }}</td>
              <td class="text-center font-bold">{{ reportData.totals.taux_reglement }}%</td>
            </tr>
          </tfoot>
        </table>
      </section>

      <footer class="print-footer">
        <div class="footer-signatures">
          <div class="signature-block">
            <p>Direction Générale</p>
            <div class="signature-line"></div>
          </div>
          <div class="signature-block">
            <p>Responsable Financier & Comptable</p>
            <div class="signature-line"></div>
          </div>
        </div>
        <p class="footer-brand">Généré par GenyCom SaaS - Logiciel de gestion commerciale & financière</p>
      </footer>
    </div>

    <div v-else class="error-state">
      <p>Aucune donnée trouvée.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const type = route.query.type || 'fournisseur' // 'fournisseur' or 'client'
const isSupplier = computed(() => type === 'fournisseur')

const start = route.query.start || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]
const end = route.query.end || new Date().toISOString().split('T')[0]
const supplierId = route.query.fournisseur_id || null
const clientId = route.query.client_id || null

const loading = ref(true)
const reportData = ref(null)
const entreprise = ref({})

const filterName = computed(() => {
  if (!reportData.value || !reportData.value.transactions || !reportData.value.transactions.length) return null
  if (supplierId && isSupplier.value) {
    return reportData.value.transactions[0]?.societe || null
  }
  if (clientId && !isSupplier.value) {
    return reportData.value.transactions[0]?.societe || null
  }
  return null
})

function formatMoney(val) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(val || 0)
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR')
}

function doPrint() {
  window.print()
}

function closeWindow() {
  window.close()
}

onMounted(async () => {
  try {
    const endpoint = isSupplier.value ? '/reporting/supplier-transactions' : '/reporting/client-transactions'
    const params = {
      start,
      end,
      ...(isSupplier.value ? { fournisseur_id: supplierId } : { client_id: clientId })
    }

    const [resEnt, resReport] = await Promise.all([
      api.get('/parametrage/entreprise').catch(() => ({ data: {} })),
      api.get(endpoint, { params })
    ])

    entreprise.value = resEnt.data || {}
    reportData.value = resReport.data || null
  } catch (e) {
    console.error('Erreur chargement rapport:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
body { background-color: #f3f4f6; margin: 0; padding: 0; }
.print-layout { display: flex; flex-direction: column; align-items: center; padding: 20px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
.no-print { margin-bottom: 20px; display: flex; gap: 10px; }
.btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #e5e7eb; color: #374151; }

.page-a4 { width: 210mm; min-height: 297mm; background: white; padding: 15mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; }

.doc-header { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 16px; margin-bottom: 20px; }
.print-logo { max-height: 55px; }
.company-details { font-size: 0.78rem; color: #64748b; margin-top: 8px; line-height: 1.35; }
.doc-meta { text-align: right; }
.doc-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0 0 4px 0; }
.doc-subtitle { font-size: 0.85rem; font-weight: 600; color: #3b82f6; margin-bottom: 8px; }
.meta-row { display: flex; gap: 8px; font-size: 0.8rem; justify-content: flex-end; margin-bottom: 2px; }
.meta-label { color: #64748b; }
.meta-value { font-weight: 700; color: #1e293b; }

h3 { font-size: 0.92rem; border-left: 4px solid #3b82f6; padding-left: 8px; margin-bottom: 12px; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; }

.summary-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1px; background: #e2e8f0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
.summary-item { background: white; padding: 12px 10px; display: flex; flex-direction: column; gap: 4px; }
.summary-item.highlight-success { background: #f0fdf4; }
.summary-item.highlight-danger { background: #fef2f2; }
.summary-item .label { font-size: 0.65rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
.summary-item .value { font-size: 1rem; font-weight: 800; }
.summary-item .success { color: #059669; }
.summary-item .warning { color: #d97706; }
.summary-item .danger { color: #dc2626; }

.print-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 0.78rem; }
.print-table th { background: #f8fafc; padding: 8px 10px; text-align: left; font-size: 0.68rem; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; }
.print-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }

.transaction-row { background: #ffffff; }
.transaction-row:nth-child(4n+1) { background: #fafafa; }
.sub-table-row { background: #f8fafc; }
.sub-table-container { padding: 4px 10px 10px 24px !important; border-bottom: 2px solid #e2e8f0 !important; }

.settlements-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; }
.settlements-title { font-size: 0.7rem; font-weight: 700; color: #3b82f6; margin-bottom: 6px; text-transform: uppercase; }
.sub-print-table { width: 100%; border-collapse: collapse; font-size: 0.72rem; }
.sub-print-table th { background: #f1f5f9; padding: 4px 8px; text-align: left; font-size: 0.62rem; color: #64748b; font-weight: 700; text-transform: uppercase; }
.sub-print-table td { padding: 4px 8px; border-bottom: 1px dashed #e2e8f0; }

.settlements-empty { padding: 4px 0; font-size: 0.72rem; }

.tiers-sub { font-size: 0.68rem; color: #64748b; margin-top: 2px; }
.badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
.badge.paye { background: #dcfce7; color: #15803d; }
.badge.partiel { background: #fef3c7; color: #b45309; }
.badge.impaye { background: #fee2e2; color: #b91c1c; }

.total-row { background: #f1f5f9; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a; }
.total-row td { padding: 10px; font-size: 0.82rem; }

.text-right { text-align: right !important; }
.text-center { text-align: center !important; }
.text-muted { color: #64748b; }
.font-bold { font-weight: 700; }
.success { color: #059669; }
.danger { color: #dc2626; }
.whitespace-nowrap { white-space: nowrap; }

.footer-signatures { display: flex; justify-content: space-between; margin-top: 40px; margin-bottom: 20px; page-break-inside: avoid; }
.signature-block { width: 40%; text-align: center; }
.signature-block p { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 50px; }
.signature-line { border-bottom: 1px solid #94a3b8; width: 100%; }

.print-footer { margin-top: 30px; text-align: center; font-size: 0.68rem; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; page-break-inside: avoid; }

.loading-state, .error-state { padding: 40px; text-align: center; }
.spinner { width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 12px; }
@keyframes spin { to { transform: rotate(360deg); } }

@media print {
  body { background: white; }
  .no-print { display: none; }
  .page-a4 { box-shadow: none; width: 100%; min-height: auto; padding: 0; }
  .print-layout { padding: 0; }
  .transaction-row { page-break-inside: avoid; }
  .sub-table-row { page-break-inside: avoid; }
}
</style>

