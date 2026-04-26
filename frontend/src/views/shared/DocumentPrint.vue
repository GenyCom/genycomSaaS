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
       <p>Chargement du document en cours...</p>
    </div>

    <div v-else-if="error" class="error-state">
       <div class="error-card">
          <h3>Erreur de chargement</h3>
          <p>{{ errorMessage }}</p>
          <button class="btn btn-secondary" @click="closeWindow">Fermer</button>
       </div>
    </div>

    <div v-else class="page-a4">
      
      <header class="doc-header">
         <div class="company-logo">
           <img v-if="entreprises.logo_path" :src="entreprises.logo_path" alt="Logo" class="print-logo" />
           <h1 v-else>{{ entreprises.raison_sociale || 'GenyCom' }}</h1>
           <p class="company-details">
             <strong>{{ entreprises.raison_sociale || 'GenyCom SaaS' }}</strong><br/>
             {{ entreprises.forme_juridique || '' }}<br/>
             {{ entreprises.adresse || 'Adresse Entreprise' }}<br/>
             {{ entreprises.code_postal || '' }} {{ entreprises.ville || '' }}<br/>
             {{ entreprises.email || '' }} | {{ entreprises.telephone || '' }}
           </p>
         </div>
         <div class="doc-meta">
           <h2 class="doc-title">{{ docTitle }}</h2>
           <div class="meta-row"><span class="meta-label">Numéro</span><span class="meta-value font-mono">{{ docNumero }}</span></div>
           <div class="meta-row"><span class="meta-label">Date</span><span class="meta-value">{{ docDate }}</span></div>
           <div class="meta-row" v-if="docStatut"><span class="meta-label">État</span><span class="meta-value" :style="{color: docStatut.couleur || '#2563eb'}">{{ docStatut.libelle }}</span></div>
         </div>
      </header>

      <div class="parties-section">
         <div class="party-box from-box">
            <h4>Émetteur</h4>
            <strong>{{ entreprises.raison_sociale || 'GenyCom' }}</strong>
            <p>
              {{ entreprises.adresse || '---' }}<br/>
              {{ entreprises.code_postal || '' }} {{ entreprises.ville || '' }}<br/>
              ICE: {{ entreprises.ice || '---' }}
            </p>
         </div>
         <div class="party-box to-box">
            <h4>À l'attention de</h4>
            <strong>{{ clientName || 'Client Inconnu' }}</strong>
            <p v-if="clientAddress">{{ clientAddress }}</p>
            <p v-else>Adresse non renseignée</p>
         </div>
      </div>

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
                 <td style="text-align:right">{{ item.quantite }} {{ item.unite || '' }}</td>
                 <td style="text-align:right">{{ formatMoney(item.prix_unitaire) }}</td>
                 <td style="text-align:right">{{ parseFloat(item.taux_tva || 20).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}%</td>
                 <td style="text-align:right; font-weight: 600;">{{ formatMoney(item.montant_ht || (item.prix_unitaire * item.quantite)) }}</td>
               </tr>
               <tr v-if="lignes.length === 0">
                 <td colspan="5" style="text-align:center; padding: 2rem; color:#888;">Aucun article sur ce document.</td>
               </tr>
            </tbody>
         </table>
      </div>

      <div class="doc-totals">
         <div class="totals-box">
            <div class="total-row"><span class="total-label">Total HT</span><span>{{ formatMoney(totalHT) }} DH</span></div>
            <div class="total-row"><span class="total-label">Total TVA</span><span>{{ formatMoney(totalTVA) }} DH</span></div>
            <div class="total-row grand-total"><span class="total-label">Total TTC</span><span>{{ formatMoney(totalTTC) }} DH</span></div>
            
            <template v-if="isFacture">
              <div class="total-row" style="margin-top: 0.5rem; color: #059669;">
                <span class="total-label">Montant Payé</span>
                <span>{{ formatMoney(montantPaye) }} DH</span>
              </div>
              <div class="total-row" style="font-size: 0.9rem; font-weight: 800; color: #dc2626; border-top: 1px dashed #e5e7eb; padding-top: 0.5rem;">
                <span class="total-label">Reste à Payer</span>
                <span>{{ formatMoney(resteAPayer) }} DH</span>
              </div>
            </template>

         </div>
      </div>

      <div class="doc-footer">
         <p class="remarks" v-if="observations">
           <strong>Notes :</strong><br/>
           {{ observations }}
         </p>
         <p class="remarks">
           <strong>Conditions : </strong> {{ type === 'devis' ? 'Ce devis est valable 30 jours.' : 'Paiement selon conditions en vigueur.' }}<br/>
           <span v-if="entreprises.rib"><strong>Banque : </strong> {{ entreprises.banque }} | <strong>RIB : </strong> {{ entreprises.rib }}</span>
         </p>
         <hr style="margin-top: 2rem; margin-bottom: 0.5rem; border-color: #ddd;">
         <p class="legal-notice">
           {{ entreprises.raison_sociale }} - RC : {{ entreprises.rc || '---' }} - IF : {{ entreprises.if_fiscal || '---' }} - Patente : {{ entreprises.patente || '---' }} - ICE : {{ entreprises.ice || '---' }}<br/>
           <i>Ce document est une pièce commerciale officielle générée par le système GenyCom.</i>
         </p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const type = route.params.type
const id = route.params.id

const loading = ref(true)
const error = ref(false)
const errorMessage = ref('')
const entreprises = ref({})
const docData = ref({})
const docStatut = ref(null)
const observations = ref('')

// Détecter s'il s'agit d'une facture pour afficher le bloc "Reste à payer"
const isFacture = computed(() => {
  return ['facture', 'factures', 'facture-achat', 'factures-achats'].includes(type.toLowerCase())
})

const docTitle = computed(() => {
  const titles = {
    'devis': 'DEVIS',
    'facture': 'FACTURE',
    'factures-achats': 'FACTURE D\'ACHAT',
    'commande': 'BON DE COMMANDE',
    'avoir-client': 'AVOIR CLIENT',
    'avoir-fournisseur': 'AVOIR FOURNISSEUR',
    'bons-commande-client': 'BON DE COMMANDE CLIENT',
    'bcc': 'BON DE COMMANDE CLIENT',
    'bons-livraison': 'BON DE LIVRAISON',
    'bl': 'BON DE LIVRAISON',
    'bons-reception': 'BON DE RÉCEPTION',
    'br': 'BON DE RÉCEPTION'
  }
  return titles[type] || 'DOCUMENT COMMERCIAL'
})

const docNumero = ref('---')
const docDate = ref('---')
const clientName = ref('---')
const clientAddress = ref('')
const lignes = ref([])

onMounted(async () => {
  try {
    loading.value = true
    
    // 1. Charger les infos entreprise
    try {
      const resEnt = await api.get('/parametrage/entreprise')
      entreprises.value = resEnt.data || {}
    } catch (e) {
      console.warn("Infos entreprise non trouvées", e)
    }

    // 2. Déterminer l'endpoint (Ajout explicite de factures-achats)
    let endpoint = ''
    switch(type) {
      case 'devis': endpoint = `/devis/${id}`; break;
      case 'facture': 
      case 'factures': endpoint = `/factures/${id}`; break;
      case 'facture-achat': 
      case 'factures-achats': endpoint = `/factures-achats/${id}`; break;
      case 'commande': endpoint = `/commandes/${id}`; break;
      case 'bons-commande-client': 
      case 'bcc': endpoint = `/bons-commande-client/${id}`; break;
      case 'bons-livraison': 
      case 'bl': endpoint = `/bons-livraison/${id}`; break;
      case 'bons-reception': 
      case 'br': endpoint = `/bons-reception/${id}`; break;
      default: endpoint = `/${type}/${id}`;
    }

    const resDoc = await api.get(endpoint)
    const data = resDoc.data
    if (!data) throw new Error("Aucune donnée reçue de l'API")
    
    docData.value = data
    
    // Mapping des champs
    docNumero.value = data.numero || '---'
    const rawDate = data.date_devis || data.date_facture || data.date_commande || data.date_livraison || data.date_reception || data.created_at
    docDate.value = rawDate ? new Date(rawDate).toLocaleDateString('fr-FR') : '---'
    
    docStatut.value = data.etat || (data.etat_id ? { libelle: 'Statut ID ' + data.etat_id } : null)
    observations.value = data.observations || ''

    const tier = data.client || data.fournisseur
    if (tier) {
      clientName.value = tier.societe || (tier.nom ? `${tier.nom} ${tier.prenom || ''}` : 'Tier Inconnu')
      clientAddress.value = [tier.adresse, tier.code_postal, tier.ville, tier.pays].filter(Boolean).join(', ')
    }

    lignes.value = data.lignes || []
    
  } catch (err) {
    console.error("Erreur chargement impression:", err)
    error.value = true
    errorMessage.value = err.response?.data?.message || "Impossible de charger le document. Vérifiez votre connexion ou vos droits d'accès."
  } finally {
    loading.value = false
  }
})

// === CALCULS ===
const totalHT = computed(() => {
  if (docData.value.total_ht) return parseFloat(docData.value.total_ht)
  return lignes.value.reduce((s, l) => s + (parseFloat(l.montant_ht) || (l.quantite * l.prix_unitaire)), 0)
})
const totalTVA = computed(() => {
  if (docData.value.total_tva) return parseFloat(docData.value.total_tva)
  return lignes.value.reduce((s, l) => s + (parseFloat(l.montant_tva) || (l.quantite * l.prix_unitaire * ((l.taux_tva || 20)/100))), 0)
})
const totalTTC = computed(() => {
  if (docData.value.total_ttc) return parseFloat(docData.value.total_ttc)
  return totalHT.value + totalTVA.value
})

const montantPaye = computed(() => {
  return parseFloat(docData.value.montant_regle || 0)
})

const resteAPayer = computed(() => {
  // On utilise en priorité la vraie valeur "reste_a_payer" de l'API si elle existe (cas des factures d'achat)
  if (docData.value.reste_a_payer !== undefined && docData.value.reste_a_payer !== null) {
    return parseFloat(docData.value.reste_a_payer)
  }
  // Sinon on calcule pour les factures client : TTC - réglé
  const ttc = parseFloat(docData.value.total_ttc || 0)
  const regle = parseFloat(docData.value.montant_regle || 0)
  return Math.max(0, ttc - regle)
})

function formatMoney(val) {
  // Remplacement de la virgule par un espace pour les milliers comme vous l'aimez (ex: 1 000 233,52)
  return (parseFloat(val) || 0)
    .toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    .replace(/\s/g, ' '); 
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

.loading-state {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  gap: 1.5rem;
  color: #6b7280;
  font-family: sans-serif;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #fef2f2;
}

.error-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  text-align: center;
  max-width: 400px;
}

.error-card h3 { color: #991b1b; margin-top: 0; }
.error-card p { color: #4b5563; margin-bottom: 2rem; }

.print-logo {
  max-height: 60px;
  margin-bottom: 1rem;
}

.print-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 0;
  
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