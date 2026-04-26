<template>
  <div class="facture-detail-view">
    <Transition name="fade">
      <div v-if="saving || loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">{{ saving ? 'Enregistrement en cours...' : 'Chargement...' }}</p>
      </div>
    </Transition>

    <ConfirmModal
      :show="showConfirmBL"
      title="Générer un Bon de Livraison"
      message="Cette action va générer un BL et décrémenter le stock pour tous les articles de cette facture. Continuer ?"
      confirmText="Générer le BL"
      @confirm="executeGenerateBL"
      @cancel="showConfirmBL = false"
    ></ConfirmModal>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/factures" class="back-btn" title="Retour aux factures">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouvelle Facture' : form.numero }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button v-if="!isNew" class="btn-secondary-custom" @click="imprimer">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          <span>Imprimer PDF</span>
        </button>
        <button v-if="!isNew && !form.has_bl" class="btn-secondary-custom accent-text" @click="showConfirmBL = true" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          <span>Générer BL</span>
        </button>
        <button class="btn-save billing-theme-btn" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar billing-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Facture de Vente</div>
        <h1 class="hero-name">{{ isNew ? 'Émettre une nouvelle facture' : 'Facture N° ' + form.numero }}</h1>
        <p class="hero-sub" v-if="selectedClientName">Client : <strong>{{ selectedClientName }}</strong></p>
      </div>
      <div v-if="!isNew" class="hero-status-badge info">
        {{ (form.etat?.libelle || 'Validée').toUpperCase() }}
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Total Hors Taxe</p>
          <p class="kpi-value">{{ formatMoney(form.total_ht) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Total TVA</p>
          <p class="kpi-value">{{ formatMoney(form.total_tva) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item billing-accent">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Net à Payer TTC</p>
          <p class="kpi-value">{{ formatMoney(form.total_ttc) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Destinataire & Analytique</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Client *</label>
                <select v-model="form.client_id" class="input-custom" :class="{ 'input-error': errors.client_id }">
                  <option value="" disabled>Sélectionner un client...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.societe || c.display_name }}</option>
                </select>
                <span v-if="errors.client_id" class="error-text">{{ errors.client_id }}</span>
              </div>
              <div class="form-group-custom">
                <label>Projet associé (Optionnel)</label>
                <select v-model="form.projet_id" class="input-custom">
                  <option value="">Aucun projet</option>
                  <option v-for="p in projects" :key="p.id" :value="p.id">[{{ p.code_projet }}] {{ p.nom_projet }}</option>
                </select>
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header table-header-actions">
            <div class="flex-align-center">
              <div class="card-header-icon billing-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.37 2.63a2.12 2.12 0 1 1 3 3L12 15l-4 1 1-4Z"/></svg></div>
              <h3>Détail des lignes de facturation</h3>
            </div>
            <button class="btn-add-line" @click="addLine">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Ajouter une ligne
            </button>
          </div>
          <div class="card-body p-0">
            <div class="product-search-bar-container">
              <div class="search-input-wrapper">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  @input="onSearchInput"
                  @keydown.enter.prevent="selectFirstProduct"
                  placeholder="Scanner un code-barres ou rechercher (référence, désignation)..." 
                  class="search-input"
                />
                
                <ul v-if="searchResults.length > 0" class="search-dropdown">
                  <li 
                    v-for="prod in searchResults" 
                    :key="prod.id" 
                    @click="ajouterProduitAuDocument(prod)"
                    class="search-item"
                  >
                    <div class="prod-info">
                      <span class="prod-ref" v-if="prod.reference || prod.code_barre">[{{ prod.reference || prod.code_barre }}]</span>
                      <span class="prod-name">{{ prod.designation }}</span>
                    </div>
                    <div class="prod-price">{{ formatMoney(prod.prix_ht_vente || prod.prix_vente_ht || 0) }} DH HT</div>
                  </li>
                </ul>

                <div v-if="searchQuery.length >= 2 && searchResults.length === 0" class="search-dropdown empty-result">
                  Aucun article trouvé pour "{{ searchQuery }}"
                </div>
              </div>
            </div>

            <div class="table-container-custom">
              <table class="saas-table">
                <thead>
                  <tr>
                    <th style="width: 40%">Article / Prestation</th>
                    <th style="width: 13%" class="text-center">Qté</th>
                    <th style="width: 15%" class="text-right">P.U HT</th>
                    <th style="width: 12%" class="text-center">TVA</th>
                    <th style="width: 17%" class="text-right">Total HT</th>
                    <th style="width: 5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(ligne, idx) in form.lignes" :key="idx" class="ligne-row">
                    <td class="td-article">
                      <select v-model="ligne.produit_id" @change="onProduitSelect(ligne)" class="select-inline-table">
                        <option value="">-- Texte libre / Service --</option>
                        <option v-for="p in produits" :key="p.id" :value="p.id">[{{ p.reference }}] {{ p.designation }}</option>
                      </select>
                      <textarea v-model="ligne.designation" class="input-inline-sub" placeholder="Description personnalisée..."></textarea>
                    </td>
                    <td class="td-center">
                      <input v-model="ligne.quantite" type="number" step="0.01" @input="recalculate" class="input-inline-table text-center" />
                    </td>
                    <td class="td-center">
                      <input 
                        v-model.lazy="ligne.prix_unitaire_display" 
                        @focus="onFocusPrice($event, ligne)"
                        @blur="onBlurPrice(ligne)"
                        type="text" 
                        class="input-inline-table text-right mono" 
                      />
                    </td>
                    <td class="td-center">
                      <select v-model="ligne.taux_tva" @change="recalculate" class="input-inline-table text-center tva-select">
                        <option v-for="t in tauxTvaList" :key="t.id" :value="parseInt(t.taux)">
                          {{ parseInt(t.taux) }}%
                        </option>
                      </select>
                    </td>
                    <td class="td-total text-right font-bold mono">
                      <span class="montant-ht">{{ formatMoney(ligne.montant_ht) }}</span>
                      <span class="montant-unit">DH</span>
                    </td>
                    <td class="td-action">
                      <button @click="removeLine(idx)" class="btn-row-delete" title="Supprimer la ligne">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="form.lignes.length === 0">
                    <td colspan="6" class="text-center p-6 text-muted italic text-sm" style="padding: 40px;">
                      Utilisez la barre de recherche ci-dessus pour scanner ou ajouter un article.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>

      <div class="col-side">
        
        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <h3>Général</h3>
          </div>
          <div class="card-body p-0">
            <div class="info-item">
              <span class="info-label">N° Facture</span>
              <span class="info-value mono accent">{{ form.numero }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Date d'émission</span>
              <input v-model="form.date_facture" type="date" class="date-input-transparent" :class="{ 'input-error': errors.date_facture }" />
            </div>
            <div class="info-item">
              <span class="info-label">Échéance de paiement</span>
              <input v-model="form.date_echeance" type="date" class="date-input-transparent" />
            </div>
          </div>
        </section>
        
        <section v-if="!isNew && form.reglements?.length > 0" class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon" style="background: #EFF6FF; color: #3B82F6;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <h3>Historique des Règlements</h3>
          </div>
          <div class="card-body p-0">
            <table class="saas-table" style="border: none;">
              <tbody>
                <tr v-for="r in form.reglements" :key="r.id">
                  <td>
                    <div style="font-weight: 700; font-size: .8rem;">{{ formatDate(r.date_reglement) }}</div>
                    <div style="font-size: .7rem; color: #6B7280;">{{ r.mode_reglement?.libelle || 'Non spécifié' }}</div>
                  </td>
                  <td class="text-right font-bold mono" style="color: #059669;">
                    + {{ formatMoney(r.montant) }} DH
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section v-if="!isNew && (form.total_ttc - (form.montant_regle || 0)) > 0" class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon" style="background: #F0FDF4; color: #059669;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>Encaisser un Paiement</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Montant Encaissé (DH) *</label>
              <input v-model="reglement.montant" type="number" step="0.01" :max="form.total_ttc - (form.montant_regle || 0)" class="input-custom mono" style="font-size: 1.1rem; font-weight: bold; color: #059669;" />
              <span style="font-size: 0.7rem; color: #6B7280; margin-top: 4px;">Reste à payer : {{ formatMoney(form.total_ttc - (form.montant_regle || 0)) }} DH</span>
            </div>
            <div class="form-group-custom">
              <label>Date</label>
              <input v-model="reglement.date_reglement" type="date" class="input-custom" />
            </div>
            <div class="form-group-custom">
              <label>Mode de Règlement</label>
              <select v-model="reglement.mode_reglement_id" class="input-custom">
                <option value="">— Sélectionner —</option>
                <option v-for="m in modesReglement" :key="m.id" :value="m.id">{{ m.libelle }}</option>
              </select>
            </div>
            <div class="form-group-custom">
              <label>Réf / Observations</label>
              <textarea v-model="reglement.observations" rows="2" class="input-custom" placeholder="N° de chèque, référence virement..."></textarea>
            </div>
            <button class="btn-save" @click="enregistrerReglement" :disabled="saving" style="width: 100%; justify-content: center; margin-top: 8px;">
              {{ saving ? 'Traitement...' : 'Valider l\'Encaissement' }}
            </button>
          </div>
        </section>

        <section v-else-if="!isNew" class="info-card mb-4">
          <div class="card-body" style="padding: 30px; text-align: center; background: #ECFDF5;">
            <div style="font-size: 2.5rem; margin-bottom: 8px;">✅</div>
            <p style="font-weight: 800; color: #059669; font-size: 1.1rem; text-transform: uppercase;">Facture Soldée</p>
            <p class="text-muted" style="font-size: .85rem;">Le client a réglé la totalité.</p>
          </div>
        </section>

        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon notes"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
            <h3>Observations</h3>
          </div>
          <div class="card-body">
            <textarea v-model="form.observations" rows="4" class="textarea-custom" placeholder="Notes visibles sur la facture..."></textarea>
          </div>
        </section>

        <section class="info-card side-card totals-premium-card">
          <div class="card-header totals-header-light">
             <div class="card-header-icon billing-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
             <h3>Résumé financier</h3>
          </div>
          <div class="total-inner">
            <div class="total-row">
              <span class="total-label">Total Net HT</span>
              <span class="total-value black-text">{{ formatMoney(form.total_ht) }} <small>DH</small></span>
            </div>
            <div class="total-row">
              <span class="total-label">TVA Totale</span>
              <span class="total-value black-text">{{ formatMoney(form.total_tva) }} <small>DH</small></span>
            </div>
            <div class="total-row main-total-light">
              <span class="label-main">NET À PAYER (TTC)</span>
              <div class="amount-group">
                <span class="amount mono black-text">{{ formatMoney(form.total_ttc) }}</span>
                <span class="currency-dark">DH</span>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const router = useRouter()
const route = useRoute()
const isNew = computed(() => route.params.id === 'new')
const loading = ref(false)
const saving = ref(false)
const showConfirmBL = ref(false)
const errors = reactive({})

const form = ref({
  numero: 'Brouillon',
  date_facture: new Date().toISOString().substring(0, 10),
  date_echeance: '',
  client_id: '',
  projet_id: '',
  total_ht: 0,
  total_tva: 0,
  total_ttc: 0,
  montant_regle: 0,
  reglements: [],
  observations: '',
  lignes: []
})

const modesReglement = ref([])
const reglement = ref({
  montant: '',
  date_reglement: new Date().toISOString().substring(0, 10),
  mode_reglement_id: '',
  observations: ''
})

const clients = ref([])
const products = ref([])
const produits = ref([])
const projects = ref([])
const tauxTvaList = ref([]) 


// --- RECHERCHE INTELLIGENTE (DOUCHETTE & AUTOCOMPLETE) ---
const searchQuery = ref('')
const searchResults = ref([])

function onSearchInput() {
  const q = searchQuery.value.toLowerCase().trim()
  if (q.length < 2) {
    searchResults.value = []
    return
  }
  searchResults.value = produits.value.filter(p => 
    (p.designation && p.designation.toLowerCase().includes(q)) || 
    (p.reference && p.reference.toLowerCase().includes(q)) || 
    (p.code_barre && p.code_barre.toLowerCase().includes(q))
  ).slice(0, 10) 
}

function selectFirstProduct() {
  if (searchResults.value.length > 0) {
    ajouterProduitAuDocument(searchResults.value[0])
  } else if (searchQuery.value.length > 0) {
    showToast("Article introuvable pour cette recherche/scan.", "error")
    searchQuery.value = ''
  }
}

function ajouterProduitAuDocument(produit) {
  // C'est une facture de vente, on prend le prix de vente
  const pu = parseFloat(produit.prix_ht_vente || produit.prix_vente_ht) || 0
  
  form.value.lignes.push({
    produit_id: produit.id,
    designation: produit.designation,
    quantite: 1,
    unite: produit.unite || 'Unité',
    prix_unitaire: pu,
    prix_unitaire_display: formatNumberInput(pu),
    taux_tva: parseFloat(produit.taux_tva) || 20,
    montant_ht: 0,
    montant_tva: 0,
    montant_ttc: 0
  })
  recalculate()
  searchQuery.value = ''
  searchResults.value = []
}
// ---------------------------------------------------------

const selectedClientName = computed(() => {
  const c = clients.value.find(client => client.id === form.value.client_id)
  return c ? (c.societe || c.display_name) : null
})

// === LOGIQUE DE FORMATTAGE DE L'INPUT ===
function formatNumberInput(val) {
  if (val === undefined || val === null || isNaN(val)) return '0,00';
  let parts = parseFloat(val).toFixed(2).split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
  return parts.join(','); 
}

function onFocusPrice(event, ligne) {
  ligne.prix_unitaire_display = ligne.prix_unitaire.toString();
  setTimeout(() => event.target.select(), 10);
}

function onBlurPrice(ligne) {
  let rawVal = String(ligne.prix_unitaire_display).replace(/[\s\u202f\xA0]/g, '').replace(/,/g, '.');
  ligne.prix_unitaire = parseFloat(rawVal) || 0;
  ligne.prix_unitaire_display = formatNumberInput(ligne.prix_unitaire);
  recalculate();
}

function formatMoney(val) { 
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) 
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR')
}

function addLine() {
  form.value.lignes.push({ 
    produit_id: '', 
    designation: '', 
    quantite: 1, 
    prix_unitaire: 0, 
    prix_unitaire_display: '0,00',
    taux_tva: 20,
    montant_ht: 0,
    montant_tva: 0,
    montant_ttc: 0
  })
}

function removeLine(idx) {
  form.value.lignes.splice(idx, 1)
  recalculate()
}

function onProduitSelect(ligne) {
  if (ligne.produit_id) {
    const prod = products.value.find(p => p.id === ligne.produit_id)
    if (prod) {
      ligne.designation = prod.designation
      ligne.prix_unitaire = parseFloat(prod.prix_ht_vente || prod.prix_vente) || 0
      ligne.prix_unitaire_display = formatNumberInput(ligne.prix_unitaire)
      ligne.taux_tva = parseInt(prod.taux_tva) || 20
    }
  }
  recalculate()
}

function recalculate() {
  let tht = 0, ttva = 0
  form.value.lignes.forEach(l => {
    const qty = parseFloat(l.quantite) || 0
    const pu = parseFloat(l.prix_unitaire) || 0
    const tvaPct = parseFloat(l.taux_tva) || 0
    
    const netHT = qty * pu
    const tvaAmt = netHT * (tvaPct / 100)
    
    l.montant_ht = +netHT.toFixed(2)
    l.montant_tva = +tvaAmt.toFixed(2)
    l.montant_ttc = +(netHT + tvaAmt).toFixed(2)
    
    tht += netHT; 
    ttva += tvaAmt
  })
  form.value.total_ht = +tht.toFixed(2)
  form.value.total_tva = +ttva.toFixed(2)
  form.value.total_ttc = +(tht + ttva).toFixed(2)
}

function validateForm() {
  Object.keys(errors).forEach(key => delete errors[key])
  let isValid = true

  if (!form.value.client_id) {
    errors.client_id = "Le client est requis"
    isValid = false
  }
  if (!form.value.date_facture) {
    errors.date_facture = "La date est requise"
    isValid = false
  }

  if (!form.value.lignes || form.value.lignes.length === 0) {
    toast.error('La facture doit contenir au moins une ligne.')
    return false
  }

  form.value.lignes.forEach((l, idx) => {
    if (!l.designation || l.designation.trim() === '') {
       toast.error(`Désignation manquante à la ligne ${idx + 1}`)
       isValid = false
    }
  })

  return isValid
}

async function save() {
  if (!validateForm()) return
  saving.value = true
  try {
    if (isNew.value) {
      const { data } = await api.post('/factures', form.value)
      toast.success('Facture créée avec succès')
      setTimeout(() => router.push(`/factures/${data.id}`), 1000)
    } else {
      await api.put(`/factures/${route.params.id}`, form.value)
      toast.success('Facture mise à jour')
      setTimeout(() => router.push('/factures'), 1000)
    }
  } catch (err) {
    if (err.response?.status === 422) {
      const serverErrors = err.response.data.errors
      Object.keys(serverErrors).forEach(key => {
        errors[key] = Array.isArray(serverErrors[key]) ? serverErrors[key][0] : serverErrors[key]
      })
      toast.error('Erreur de validation.')
    } else {
      toast.error('Une erreur est survenue.')
    }
  } finally {
    saving.value = false
  }
}

function imprimer() { window.open(`/print/facture/${route.params.id}`, '_blank') }

async function enregistrerReglement() {
  if (!reglement.value.montant || parseFloat(reglement.value.montant) <= 0) {
    toast.error('Veuillez saisir un montant valide.'); 
    return;
  }
  
  saving.value = true;
  try {
    await api.post(`/factures/${route.params.id}/reglement`, reglement.value);
    toast.success('Encaissement enregistré avec succès !');
    
    reglement.value.montant = '';
    reglement.value.observations = '';
    
    await loadFactureData();
    
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de l\'encaissement.');
  } finally {
    saving.value = false;
  }
}

async function loadFactureData() {
  if (isNew.value) return;
  const { data } = await api.get(`/factures/${route.params.id}`)
  const rawData = data.data || data
  if (rawData.date_facture) rawData.date_facture = rawData.date_facture.substring(0, 10)
  if (rawData.date_echeance) rawData.date_echeance = rawData.date_echeance.substring(0, 10)
  
  if (rawData.lignes) {
    rawData.lignes.forEach(l => {
      l.taux_tva = parseInt(l.taux_tva) || 0
      l.quantite = parseFloat(l.quantite) || 1;
      l.prix_unitaire = parseFloat(l.prix_unitaire) || 0;
      l.prix_unitaire_display = formatNumberInput(l.prix_unitaire);
    });
  }
  
  form.value = { ...form.value, ...rawData }
  recalculate()
}

onMounted(async () => {
  loading.value = true
  try {
    const [cRes, pRes, prRes, mRes, tvaRes] = await Promise.all([
      api.get('/clients', { params: { per_page: 500 } }),
      api.get('/produits', { params: { per_page: 500 } }),
      api.get('/projets', { params: { per_page: 500 } }),
      api.get('/parametrage/referentiels/modes-reglement').catch(() => ({ data: { data: [] } })),
      api.get('/parametrage/referentiels/taux-tva').catch(() => ({ data: { data: [] } }))
    ])
    
    clients.value = cRes.data.data || cRes.data || []
    products.value = pRes.data.data || pRes.data || []
    produits.value = products.value.filter(p => p.is_actif !== false)
    projects.value = prRes.data.data || prRes.data || []
    modesReglement.value = mRes.data.data || mRes.data || []
    
    tauxTvaList.value = tvaRes.data.data || tvaRes.data || []
    
    if (!isNew.value) {
      await loadFactureData();
    }
  } catch (e) {
    console.error(e)
    toast.error('Erreur lors du chargement des données')
  } finally {
    loading.value = false
  }
})

async function executeGenerateBL() {
  showConfirmBL.value = false
  saving.value = true
  try {
    const { data } = await api.post(`/workflow/facture-to-bl/${route.params.id}`)
    toast.success(`BL ${data.numero} généré — stock mis à jour !`)
    setTimeout(() => router.push(`/bons-livraison/${data.id}`), 1200)
  } catch (err) {
    if (err.response?.status === 422) {
      toast.error(err.response.data.message || 'Un BL existe déjà pour cette facture.')
    } else {
      toast.error('Erreur lors de la génération du BL.')
    }
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
/* ─── Design Tokens ─── */
.facture-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #4338CA;
  --c-accent-bg: #EEF2FF;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Totals Card Premium ─── */
.totals-premium-card { background: #fff; border: 1.5px solid #4338CA; border-radius: 16px; color: var(--c-text); overflow: hidden; box-shadow: 0 10px 25px rgba(67, 56, 202, 0.05); }
.total-inner { padding: 24px; }
.total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.total-label { font-size: 0.75rem; color: #6B7280; font-weight: 500; }
.total-value { font-size: 1rem; font-weight: 700; color: #1A1D23; }
.black-text { color: #000 !important; }

.main-total-light { flex-direction: column; align-items: flex-end; gap: 4px; margin-bottom: 0; margin-top: 12px; border-top: 1px solid #EEF2FF; padding-top: 12px; }
.label-main { font-size: 0.7rem; font-weight: 800; color: #4338CA; letter-spacing: 0.1em; }
.amount-group { display: flex; align-items: baseline; gap: 6px; }
.amount { font-size: 1.8rem; font-weight: 900; letter-spacing: -1px; color: #000; }
.currency-dark { font-size: 0.8rem; font-weight: 700; color: #94A3B8; }

/* ─── Top Bar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; transition: all 0.2s; }
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; gap: 10px; }
.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(67, 56, 202, 0.2); }
.billing-theme-btn { background: #4338CA; }
.btn-secondary-custom { background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; }
.accent-text { color: var(--c-accent); border-color: var(--c-accent-bg); }

/* ─── Hero Header ─── */
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.billing-theme { background: linear-gradient(135deg, #4338CA, #1E1B4B); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
.hero-meta { flex: 1; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #4338CA; margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #4338CA; border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
.hero-status-badge.info { background: #E0F2FE; color: #0369A1; }

/* ─── KPI Strip ─── */
.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.kpi-item.billing-accent .kpi-icon { background: #EEF2FF; color: #4338CA; }
.kpi-item.neutral .kpi-icon { background: #F1F5F9; color: #475569; }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

/* ─── Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #EEF2FF; color: #4338CA; display: flex; align-items: center; justify-content: center; }
.table-header-actions { justify-content: space-between; padding-right: 12px; }
.flex-align-center { display: flex; align-items: center; gap: 10px; }
.info-item { padding: 16px 20px; border-bottom: 1px solid #F1F5F9; display: flex; flex-direction: column; gap: 6px; }
.info-item:last-child { border-bottom: none; }
.info-label { font-size: .68rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .05em; }
.info-value { font-size: 1rem; font-weight: 700; color: var(--c-text); }
.date-input-transparent { border: none; font-family: inherit; font-size: 1rem; font-weight: 700; color: var(--c-text); outline: none; background: transparent; padding: 0; cursor: pointer; width: 100%; }

/* ─── Forms ─── */
.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 18px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.form-group-custom label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-row-custom { display: flex; gap: 16px; }
.input-custom { padding: 10px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .9rem; background: #fff; width: 100%; outline: none; }
.input-custom:focus { border-color: var(--c-accent); }
.textarea-custom { width: 100%; border: none; font-size: .85rem; line-height: 1.5; outline: none; resize: vertical; padding: 16px; }

/* ─── BARRE DE RECHERCHE (AUTOCOMPLETE / DOUCHETTE) ─── */
.product-search-bar-container { padding: 16px 20px; border-bottom: 1px dashed var(--c-border); background: #FCFDFE; }
.search-input-wrapper { position: relative; max-width: 600px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; }
.search-input { width: 100%; padding: 12px 14px 12px 42px; font-size: .9rem; border: 1.5px solid var(--c-accent); border-radius: 10px; box-shadow: 0 0 0 3px var(--c-accent-bg); transition: all 0.2s; background: #fff; outline: none; }
.search-input:focus { border-color: #312E81; box-shadow: 0 0 0 4px #E0E7FF; }

.search-dropdown { position: absolute; top: 100%; left: 0; right: 0; margin-top: 6px; margin-bottom: 0; background: #fff; border: 1px solid #E2E8F0; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 50; max-height: 280px; overflow-y: auto; list-style: none; padding: 0; }
.search-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #F1F5F9; cursor: pointer; transition: background 0.2s; }
.search-item:last-child { border-bottom: none; }
.search-item:hover { background: #F8FAFC; }
.prod-info { display: flex; align-items: center; gap: 10px; }
.prod-ref { font-family: 'JetBrains Mono', monospace; font-size: .75rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 3px 6px; border-radius: 4px; }
.prod-name { font-weight: 600; color: var(--c-text); font-size: .85rem; }
.prod-price { font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #10B981; font-size: .85rem; }
.empty-result { padding: 16px; text-align: center; color: var(--c-muted); font-size: .85rem; font-style: italic; }

/* ─── Table ─── */
.saas-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.saas-table th { background: #F9FAFB; padding: 13px 18px; font-size: .63rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 2px solid var(--c-border); letter-spacing: .04em; }
.saas-table th.text-center { text-align: center; }
.saas-table th.text-right { text-align: right; }

.ligne-row { transition: background .15s; }
.ligne-row:hover { background: #FAFBFD; }
.ligne-row:last-child td { border-bottom: none; }

.saas-table td { padding: 14px 18px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
.td-article { vertical-align: top; padding-top: 16px; }
.td-center { vertical-align: middle; padding: 14px 6px; }
.td-total { vertical-align: middle; padding: 14px 18px; }
.td-action { vertical-align: middle; padding: 14px 8px; text-align: center; }

.montant-ht { font-size: .95rem; font-weight: 800; color: var(--c-text); }
.montant-unit { font-size: .68rem; font-weight: 600; color: var(--c-muted); margin-left: 3px; opacity: .7; }

.select-inline-table { width: 100%; border: 1.5px solid #E2E8F0; border-radius: 7px; font-weight: 700; color: var(--c-accent); padding: 9px 10px; background: #fff; margin-bottom: 8px; font-size: .85rem; }
.input-inline-sub { width: 100%; border: 1.5px solid #E2E8F0; border-radius: 7px; font-size: .84rem; color: var(--c-text); padding: 9px 10px; min-height: 56px; font-family: inherit; resize: vertical; display: block; }
.input-inline-table { width: 100%; border: 1.5px solid #D5D9E2; border-radius: 8px; padding: 10px 10px; background: #fff; font-size: .88rem; }
.input-inline-table:focus { border-color: var(--c-accent); outline: none; }
.tva-select { font-weight: 600; font-size: .85rem; text-align-last: center; }

.btn-row-delete { background: none; border: none; color: #94A3B8; cursor: pointer; padding: 8px; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
.btn-row-delete:hover { background: #FEE2E2; color: #DC2626; }
.btn-add-line { background: var(--c-accent-bg); color: var(--c-accent); border: none; padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: .75rem; cursor: pointer; display: flex; align-items: center; gap: 4px; }

/* ─── Side Info ─── */
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.toast-notification { position: fixed; top: 1rem; right: 1rem; padding: 0.85rem 1.5rem; border-radius: 8px; z-index: 9999; animation: slideIn 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.toast-notification.success { background: #10b981; color: #fff; }
.toast-notification.error { background: #ef4444; color: #fff; }
.toast-notification.warning { background: #F59E0B; color: #fff; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

/* ─── Utilities ─── */
.mono { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.font-black { font-weight: 900; }
.input-error { border-color: #EF4444 !important; background-color: #FEF2F2 !important; }
.error-text { color: #EF4444; font-size: 0.65rem; font-weight: 600; margin-top: 2px; }
</style>
