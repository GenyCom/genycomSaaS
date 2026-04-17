<template>
  <div class="print-layout">
    <!-- Floating actions (hidden on print) -->
    <div class="no-print print-actions">
      <button class="btn btn-primary" @click="doPrint">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Imprimer / Sauvegarder PDF
      </button>
      <button class="btn btn-secondary" @click="closeWindow">Fermer</button>
    </div>

    <!-- A4 Page Container -->
    <div class="page-a4">
      
      <!-- Document Header -->
      <header class="doc-header">
         <div class="company-logo">
           <h1>GenyCom</h1>
           <p class="company-details">Solutions Informatiques & Digitales<br/>123 Avenue Hasan II, Casablanca<br/>contact@genycom.ma | +212 5 22 00 00 00</p>
         </div>
         <div class="doc-meta">
           <h2 class="doc-title">{{ docTitle }}</h2>
           <div class="meta-row"><span class="meta-label">Numéro</span><span class="meta-value font-mono">{{ docNumero }}</span></div>
           <div class="meta-row"><span class="meta-label">Date</span><span class="meta-value">{{ docDate }}</span></div>
         </div>
      </header>

      <!-- Parties (From / To) -->
      <div class="parties-section">
         <div class="party-box from-box">
            <h4>Émetteur</h4>
            <strong>GenyCom SARL</strong>
            <p>123 Avenue Hasan II<br/>20000 Casablanca, Maroc<br/>ICE: 001234567000089</p>
         </div>
         <div class="party-box to-box">
            <h4>À l'attention de</h4>
            <strong>{{ clientName }}</strong>
            <p>{{ clientAddress }}</p>
         </div>
      </div>

      <!-- Items Table -->
      <div class="items-section">
         <table class="doc-table">
            <thead>
               <tr>
                  <th style="width: 45%">Description</th>
                  <th style="text-align:right">Qté</th>
                  <th style="text-align:right">P.U HT</th>
                  <th style="text-align:right">TVA</th>
                  <th style="text-align:right">Total HT</th>
               </tr>
            </thead>
            <tbody>
               <tr v-for="(item, idx) in lignes" :key="idx">
                 <td style="font-weight: 500;">{{ item.designation }}</td>
                 <td style="text-align:right">{{ item.quantite }}</td>
                 <td style="text-align:right">{{ formatMoney(item.prix_unitaire) }}</td>
                 <td style="text-align:right">{{ item.taux_tva }}%</td>
                 <td style="text-align:right; font-weight: 600;">{{ formatMoney(item.prix_unitaire * item.quantite) }}</td>
               </tr>
               <tr v-if="lignes.length === 0">
                 <td colspan="5" style="text-align:center; color:#888;">Aucune ligne facturable.</td>
               </tr>
            </tbody>
         </table>
      </div>

      <!-- Totals -->
      <div class="doc-totals">
         <div class="totals-box">
            <div class="total-row"><span class="total-label">Total HT</span><span>{{ formatMoney(totalHT) }}</span></div>
            <div class="total-row"><span class="total-label">TVA (20%)</span><span>{{ formatMoney(totalTVA) }}</span></div>
            <div class="total-row grand-total"><span class="total-label">Total TTC</span><span>{{ formatMoney(totalTTC) }}</span></div>
         </div>
      </div>

      <!-- Remarks & Footer -->
      <div class="doc-footer">
         <p class="remarks">
           <strong>Conditions de paiement : </strong> {{ type === 'devis' ? 'Ce devis est valable 30 jours à compter de sa date d\'émission.' : 'Paiement à réception de facture par virement bancaire.' }}<br/>
           <strong>Coordonnées bancaires : </strong> Banque Populaire - RIB 000 000 0000 0000 0000 0000 00<br/>
         </p>
         <hr style="margin-top: 2rem; margin-bottom: 0.5rem; border-color: #ddd;">
         <p class="legal-notice">
           GenyCom SARL au capital de 100.000 DH - RC : 12345 - PATENTE : 1234567 - IF : 1234567 - ICE : 001234567000089<br/>
           <i>Document généré informatiquement par le système de gestion GenyCom</i>
         </p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const type = route.params.type
const id = route.params.id

// Mock Data Loading
const docTitle = computed(() => {
  if (type === 'facture') return 'FACTURE'
  if (type === 'devis') return 'DEVIS'
  if (type === 'commande') return 'BON DE COMMANDE'
  if (type === 'avoir-client') return 'AVOIR CLIENT'
  if (type === 'avoir-fournisseur') return 'AVOIR FOURNISSEUR'
  return 'DOCUMENT'
})

const docNumero = ref('---')
const docDate = ref('---')
const clientName = ref('Client Inconnu')
const clientAddress = ref('Adresse non renseignée\nMaroc')
const lignes = ref([])

onMounted(() => {
  // Simulate fetching data depending on the type and logic
  docDate.value = new Date().toLocaleDateString('fr-FR')
  
  if (type === 'devis' || type === 'facture' || type === 'avoir-client') {
    docNumero.value = type === 'devis' ? 'DEV-2026-04-102' : (type === 'facture' ? 'FAC-2026-04-001' : 'AVR-2026-001')
    clientName.value = 'TechnoPlus SARL'
    clientAddress.value = '45 Boulevard d\'Anfa\n20000 Casablanca'
    lignes.value = [
      { designation: 'Audit Informatique Annuel', quantite: 1, prix_unitaire: 5000, taux_tva: 20 },
      { designation: 'Maintenance Serveur', quantite: 3, prix_unitaire: 1200, taux_tva: 20 }
    ]
  } else {
    docNumero.value = 'CMD-2026-001'
    clientName.value = 'DistriTech Maroc'
    clientAddress.value = 'Route El Jadida\n20000 Casablanca'
    lignes.value = [
      { designation: 'Écran LED 27"', quantite: 10, prix_unitaire: 1200, taux_tva: 20 }
    ]
  }
})

const totalHT = computed(() => lignes.value.reduce((s, l) => s + (l.quantite * l.prix_unitaire), 0))
const totalTVA = computed(() => lignes.value.reduce((s, l) => s + (l.quantite * l.prix_unitaire * (l.taux_tva/100)), 0))
const totalTTC = computed(() => totalHT.value + totalTVA.value)

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

function doPrint() {
  window.print()
}

function closeWindow() {
  window.close()
}
</script>

<style>
/* CSS Reset minimal for printing */
body { margin: 0; padding: 0; background-color: #f7f9fc; }

.print-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 0;
  font-family: 'Inter', system-ui, sans-serif;
  color: #111827;
}

.print-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.page-a4 {
  width: 210mm;
  min-height: 297mm;
  background: white;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  padding: 20mm;
  box-sizing: border-box;
  position: relative;
}

/* Header */
.doc-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 3rem;
  border-bottom: 2px solid #f3f4f6;
  padding-bottom: 2rem;
}

.company-logo h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #2563eb; /* Primary blue */
  margin: 0 0 0.5rem 0;
  letter-spacing: -1px;
}
.company-details {
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.5;
  margin: 0;
}

.doc-meta {
  text-align: right;
}
.doc-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  letter-spacing: 2px;
  margin: 0 0 1rem 0;
}
.meta-row {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
}
.meta-label { color: #6b7280; }
.meta-value { font-weight: 600; color: #111827; }

/* Parties */
.parties-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 3rem;
}
.party-box {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 8px;
}
.party-box h4 {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #6b7280;
  margin: 0 0 0.5rem 0;
  letter-spacing: 1px;
}
.party-box strong {
  display: block;
  font-size: 1.1rem;
  color: #111827;
  margin-bottom: 0.5rem;
}
.party-box p {
  font-size: 0.95rem;
  color: #4b5563;
  line-height: 1.6;
  margin: 0;
  white-space: pre-line;
}

/* Table */
.items-section {
  margin-bottom: 3rem;
}
.doc-table {
  width: 100%;
  border-collapse: collapse;
}
.doc-table th {
  background: #f3f4f6;
  color: #4b5563;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.75rem 1rem;
  border-bottom: 2px solid #e5e7eb;
}
.doc-table td {
  padding: 1rem;
  font-size: 0.95rem;
  border-bottom: 1px solid #f3f4f6;
  color: #1f2937;
}

/* Totals */
.doc-totals {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 4rem;
}
.totals-box {
  width: 300px;
}
.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.95rem;
  color: #4b5563;
}
.total-row.grand-total {
  font-size: 1.25rem;
  font-weight: 800;
  color: #2563eb;
  border-top: 2px solid #e5e7eb;
  padding-top: 1rem;
  margin-top: 0.5rem;
}

/* Footer */
.doc-footer {
  margin-top: auto;
}
.remarks {
  font-size: 0.85rem;
  color: #4b5563;
  line-height: 1.6;
}
.legal-notice {
  text-align: center;
  font-size: 0.7rem;
  color: #9ca3af;
  line-height: 1.5;
}

/* Print CSS */
@media print {
  @page {
    size: A4;
    margin: 0;
  }
  body, html {
    margin: 0;
    padding: 0;
    background: white !important;
  }
  .print-layout {
    padding: 0;
  }
  .no-print {
    display: none !important;
  }
  .page-a4 {
    box-shadow: none;
    width: 100%;
    margin: 0;
    page-break-after: avoid;
  }
  
  /* Retain Webkit background colors on Chrome PDF export */
  * {
    -webkit-print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
}
</style>
