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
       <p>Génération de l'état de compte...</p>
    </div>

    <div v-else-if="tiersData" class="page-a4">
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
           <h2 class="doc-title">ÉTAT DE COMPTE</h2>
           <div class="doc-subtitle">{{ tiersType === 'client' ? 'CRÉANCES CLIENT' : 'DETTES FOURNISSEUR' }}</div>
           <div class="meta-row"><span class="meta-label">Édité le</span><span class="meta-value">{{ new Date().toLocaleDateString('fr-FR') }}</span></div>
         </div>
      </header>

      <!-- Informations du tiers -->
      <section class="tiers-info-section">
        <div class="tiers-info-grid">
          <div class="tiers-info-item">
            <span class="ti-label">{{ tiersType === 'client' ? 'Client' : 'Fournisseur' }}</span>
            <span class="ti-value main">{{ tiersData.societe }}</span>
          </div>
          <div class="tiers-info-item" v-if="tiersData.ice">
            <span class="ti-label">ICE</span>
            <span class="ti-value">{{ tiersData.ice }}</span>
          </div>
          <div class="tiers-info-item" v-if="tiersData.telephone">
            <span class="ti-label">Téléphone</span>
            <span class="ti-value">{{ tiersData.telephone }}</span>
          </div>
          <div class="tiers-info-item" v-if="tiersData.email">
            <span class="ti-label">Email</span>
            <span class="ti-value">{{ tiersData.email }}</span>
          </div>
        </div>
      </section>

      <!-- Synthèse -->
      <section class="summary-section">
        <h3>Synthèse</h3>
        <div class="summary-grid three">
          <div class="summary-item">
            <span class="label">Total Facturé TTC</span>
            <span class="value">{{ formatMoney(tiersData.total_ttc) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Total Réglé</span>
            <span class="value success">{{ formatMoney(tiersData.total_regle) }}</span>
          </div>
          <div class="summary-item highlight">
            <span class="label">Reste à Payer</span>
            <span class="value danger">{{ formatMoney(tiersData.reste_a_payer) }}</span>
          </div>
        </div>
      </section>

      <!-- Détail des factures -->
      <section class="details-section mt-4">
        <h3>Détail des Factures Impayées ({{ tiersData.nb_factures }} facture{{ tiersData.nb_factures > 1 ? 's' : '' }})</h3>
        <table class="print-table">
          <thead>
            <tr>
              <th>N° Facture</th>
              <th>Date</th>
              <th>Échéance</th>
              <th class="text-right">Total TTC</th>
              <th class="text-right">Réglé</th>
              <th class="text-right">Reste à Payer</th>
              <th class="text-center">Retard</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(f, idx) in tiersData.factures" :key="idx">
              <td class="font-bold">{{ f.numero }}</td>
              <td>{{ f.date_facture ? formatDate(f.date_facture) : '—' }}</td>
              <td :class="{ 'danger': f.jours_retard }">
                {{ f.date_echeance ? formatDate(f.date_echeance) : '—' }}
              </td>
              <td class="text-right">{{ formatMoney(f.total_ttc) }}</td>
              <td class="text-right success">{{ formatMoney(f.montant_regle) }}</td>
              <td class="text-right font-bold danger">{{ formatMoney(f.reste_a_payer) }}</td>
              <td class="text-center">
                <span v-if="f.jours_retard" class="retard-badge">{{ f.jours_retard }} j</span>
                <span v-else class="on-time-text">—</span>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="3" class="font-bold">TOTAL</td>
              <td class="text-right font-bold">{{ formatMoney(tiersData.total_ttc) }}</td>
              <td class="text-right font-bold success">{{ formatMoney(tiersData.total_regle) }}</td>
              <td class="text-right font-bold danger total-highlight">{{ formatMoney(tiersData.reste_a_payer) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </section>

      <!-- Montant en toutes lettres -->
      <section class="amount-words-section mt-4">
        <div class="amount-words-box">
          <span class="aw-label">Arrêté le présent état de compte à la somme de :</span>
          <span class="aw-value">{{ formatMoney(tiersData.reste_a_payer) }}</span>
        </div>
      </section>

      <footer class="print-footer">
        <div class="footer-signatures">
          <div class="signature-block">
            <p>Cachet et signature de l'entreprise</p>
            <div class="signature-line"></div>
          </div>
          <div class="signature-block">
            <p>Cachet et signature du {{ tiersType === 'client' ? 'client' : 'fournisseur' }}</p>
            <div class="signature-line"></div>
          </div>
        </div>
        <p class="footer-brand">Généré par GenyCom SaaS - Logiciel de gestion commerciale</p>
      </footer>
    </div>

    <div v-else class="error-state">
      <p>Aucune donnée trouvée pour ce tiers.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const tiersType = route.query.type // 'client' or 'fournisseur'
const tiersId = route.query.tiers_id

const loading = ref(true)
const tiersData = ref(null)
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
      api.get('/reporting/unpaid-statement', { params: { tiers_type: tiersType, tiers_id: tiersId } })
    ])
    entreprise.value = resEnt.data || {}

    const data = resData.data
    // Find the specific tiers from the results
    if (tiersType === 'client' && data.clients && data.clients.length > 0) {
      tiersData.value = data.clients[0]
    } else if (tiersType === 'fournisseur' && data.fournisseurs && data.fournisseurs.length > 0) {
      tiersData.value = data.fournisseurs[0]
    }
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
.btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
.btn-primary { background: #3b82f6; color: white; }
.btn-secondary { background: #e5e7eb; color: #374151; }

.loading-state { display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 60px; }
.spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.error-state { padding: 60px; text-align: center; color: #94a3b8; font-style: italic; }

.page-a4 { width: 210mm; min-height: 297mm; background: white; padding: 18mm 20mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }

/* Header */
.doc-header { display: flex; justify-content: space-between; border-bottom: 3px solid #3b82f6; padding-bottom: 20px; margin-bottom: 25px; }
.print-logo { max-height: 60px; }
.company-details { font-size: 0.8rem; color: #64748b; margin-top: 10px; line-height: 1.6; }
.doc-title { font-size: 1.3rem; font-weight: 800; color: #1e293b; margin: 0 0 4px 0; text-align: right; }
.doc-subtitle { font-size: 0.85rem; font-weight: 700; color: #3b82f6; text-align: right; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.meta-row { display: flex; gap: 10px; font-size: 0.85rem; justify-content: flex-end; }
.meta-label { color: #64748b; }
.meta-value { font-weight: 700; }

/* Tiers info */
.tiers-info-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; margin-bottom: 24px; }
.tiers-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 30px; }
.tiers-info-item { display: flex; flex-direction: column; gap: 2px; }
.ti-label { font-size: 0.65rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.ti-value { font-size: 0.9rem; font-weight: 600; color: #1e293b; }
.ti-value.main { font-size: 1.15rem; font-weight: 800; color: #1e293b; }

/* Sections */
h3 { font-size: 0.95rem; border-left: 4px solid #3b82f6; padding-left: 10px; margin-bottom: 15px; color: #1e293b; font-weight: 700; }

.summary-grid { display: grid; gap: 1px; background: #e2e8f0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
.summary-grid.three { grid-template-columns: 1fr 1fr 1fr; }
.summary-item { background: white; padding: 16px; display: flex; flex-direction: column; gap: 5px; }
.summary-item.highlight { background: #fff1f2; }
.summary-item .label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
.summary-item .value { font-size: 1.15rem; font-weight: 800; color: #1e293b; }
.success { color: #059669 !important; }
.danger { color: #dc2626 !important; }

/* Table */
.print-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.print-table th { background: #f1f5f9; padding: 10px 12px; text-align: left; font-size: 0.72rem; text-transform: uppercase; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; letter-spacing: 0.3px; }
.print-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; color: #334155; }
.print-table tfoot td { border-bottom: none; border-top: 2px solid #334155; padding-top: 12px; }
.text-right { text-align: right !important; }
.text-center { text-align: center !important; }
.text-muted { color: #64748b; }
.font-bold { font-weight: 700; }

.total-row { background: #f8fafc; }
.total-highlight { font-size: 1rem; }

.retard-badge { background: #fef2f2; color: #b91c1c; padding: 2px 8px; border-radius: 100px; font-size: 0.72rem; font-weight: 700; }
.on-time-text { color: #94a3b8; }

/* Amount in words */
.amount-words-section { margin-top: 24px; }
.amount-words-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px 20px; display: flex; flex-direction: column; gap: 6px; }
.aw-label { font-size: 0.8rem; font-weight: 600; color: #475569; }
.aw-value { font-size: 1.2rem; font-weight: 800; color: #1e40af; }

/* Footer */
.print-footer { margin-top: 50px; }
.footer-signatures { display: flex; justify-content: space-between; margin-bottom: 40px; }
.signature-block { text-align: center; width: 40%; }
.signature-block p { font-size: 0.8rem; color: #64748b; font-weight: 600; margin-bottom: 50px; }
.signature-line { border-bottom: 1px dashed #94a3b8; }
.footer-brand { text-align: center; font-size: 0.7rem; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }

.mt-4 { margin-top: 1.5rem; }

@media print {
  body { background: white; }
  .no-print { display: none !important; }
  .page-a4 { box-shadow: none; width: 100%; padding: 0; }
  .print-layout { padding: 0; }
  .tiers-info-section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .summary-item.highlight { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .retard-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .amount-words-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .print-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
