<template>
  <div class="bcc-detail-view">
    <Transition name="fade">
      <div v-if="saving || loading || transforming" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">{{ transforming ? 'Génération du BL...' : (saving ? 'Enregistrement...' : 'Chargement...') }}</p>
      </div>
    </Transition>

    <ConfirmModal 
      :show="showConfirmModal"
      title="Générer le BL"
      message="Voulez-vous générer le Bon de Livraison pour cette commande ? Cette action créera un nouveau document d'expédition."
      confirmText="Générer le BL"
      @confirm="executeTransform"
      @cancel="showConfirmModal = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/bons-commande-client" class="back-btn" title="Retour aux commandes">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouveau Bon de Commande' : (form.numero || 'Chargement...') }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button v-if="!isNew" class="btn-secondary-custom" @click="imprimer">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          <span>Imprimer</span>
        </button>
        <button v-if="!isNew && !form.est_livre" class="btn-secondary-custom accent-text" @click="transformToBL" :disabled="saving || transforming">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          <span>Expédier (BL)</span>
        </button>
        <button class="btn-save bcc-theme-btn" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar bcc-theme">
        <span>{{ isNew ? '+' : 'BC' }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Bon de Commande Client</div>
        <h1 class="hero-name">{{ isNew ? 'Établir un Nouveau Bon de Commande' : 'Commande : ' + form.numero }}</h1>
        <p class="hero-sub" v-if="selectedClientName">Client : <strong>{{ selectedClientName }}</strong></p>
      </div>
      <div v-if="!isNew" class="hero-status-badge" :style="form.etat ? { backgroundColor: form.etat.couleur + '15', color: form.etat.couleur } : {}">
        {{ form.etat ? (form.etat.libelle).toUpperCase() : (form.statut || 'Brouillon').toUpperCase() }}
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Total HT</p>
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
      <div class="kpi-item bcc-accent">
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
            <div class="card-header-icon bcc-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <h3>Identification & Dates</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Client *</label>
                <select v-model="form.client_id" :class="{ 'input-error': errors.client_id }">
                  <option value="" disabled>Choisir un client...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.societe || c.display_name }}</option>
                </select>
                <span v-if="errors.client_id" class="error-text">{{ errors.client_id }}</span>
              </div>
              <div class="form-group-custom">
                <label>Projet Lié</label>
                <select v-model="form.projet_id">
                  <option value="">Aucun projet</option>
                  <option v-for="p in projects" :key="p.id" :value="p.id">[{{ p.code_projet }}] {{ p.nom_projet }}</option>
                </select>
              </div>
            </div>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Date Commande</label>
                <input v-model="form.date_commande" type="date" />
              </div>
              <div class="form-group-custom">
                <label>Livraison Prévue</label>
                <input v-model="form.date_livraison_prevue" type="date" />
              </div>
            </div>
            <div class="form-group-custom mt-2">
              <label>Dépôt d'Expédition (Entrepôt) *</label>
              <select v-model="form.entrepot_id" class="accent-select">
                <option v-for="e in warehouses" :key="e.id" :value="e.id">
                  {{ e.nom }} {{ e.is_default ? '(Par défaut)' : '' }}
                </option>
              </select>
              <p class="field-hint">L'entrepôt d'où les articles seront décomptés lors de la livraison.</p>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header table-header-actions">
            <div class="flex-align-center">
              <div class="card-header-icon bcc-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.37 2.63a2.12 2.12 0 1 1 3 3L12 15l-4 1 1-4Z"/></svg></div>
              <h3>Détail des Prestations</h3>
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
                    <th style="width: 38%">Article / Service</th>
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
                        <option value="">-- Sélection manuelle --</option>
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
                    <td colspan="6" class="text-center" style="padding: 40px; color: var(--c-muted); font-style: italic; font-size: 0.85rem;">
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
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
            <h3>Observations & Notes</h3>
          </div>
          <div class="card-body">
            <textarea v-model="form.observations" rows="6" class="textarea-custom" :class="{ 'input-error': errors.observations }" placeholder="Notes visibles sur le bon de commande..."></textarea>
            <span v-if="errors.observations" class="error-text">{{ errors.observations }}</span>
          </div>
        </section>

        <section v-if="form.devis" class="info-card mt-4">
          <div class="card-header">
            <div class="card-header-icon bcc-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <h3>Devis d'origine</h3>
          </div>
          <div class="card-body">
            <div class="info-item-p">
              <span class="label">Numéro :</span>
              <span class="value mono accent">{{ form.devis.numero }}</span>
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
const saving = ref(false)
const loading = ref(true)
const transforming = ref(false)
const showConfirmModal = ref(false)

const form = ref({
  numero: '',
  date_commande: new Date().toISOString().substring(0, 10),
  date_livraison_prevue: '',
  client_id: '',
  projet_id: '',
  total_ht: 0,
  total_tva: 0,
  total_ttc: 0,
  observations: '',
  entrepot_id: null,
  lignes: []
})

const clients = ref([])
const products = ref([])
const produits = ref([])
const projects = ref([])
const warehouses = ref([])
const tauxTvaList = ref([])
const errors = reactive({})

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
    toast.error("Article introuvable pour cette recherche/scan.")
    searchQuery.value = ''
  }
}

function ajouterProduitAuDocument(produit) {
  // Commande Client (Vente), donc on prend le prix de vente
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
  return parseFloat(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function addLine() {
  form.value.lignes.push({ 
    produit_id: '', designation: '', quantite: 1, unite: 'Unité', 
    prix_unitaire: 0, prix_unitaire_display: '0,00', 
    taux_tva: 20, montant_ht: 0, montant_tva: 0, montant_ttc: 0 
  })
}

function removeLine(idx) {
  form.value.lignes.splice(idx, 1)
  recalculate()
}

function onProduitSelect(ligne) {
  const p = products.value.find(prod => prod.id === ligne.produit_id)
  if (p) {
    ligne.designation = p.designation
    ligne.prix_unitaire = parseFloat(p.prix_ht_vente || p.prix_vente_ht) || 0
    ligne.prix_unitaire_display = formatNumberInput(ligne.prix_unitaire)
    ligne.taux_tva = parseFloat(p.taux_tva) || 20
    ligne.unite = p.unite || 'Unité'
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
    tht += netHT; ttva += tvaAmt
  })
  form.value.total_ht = +tht.toFixed(2)
  form.value.total_tva = +ttva.toFixed(2)
  form.value.total_ttc = +(tht + ttva).toFixed(2)
}

function validateForm() {
  Object.keys(errors).forEach(key => delete errors[key])
  let isValid = true

  if (!form.value.client_id) { errors.client_id = "Le client est requis"; isValid = false }
  
  if (!form.value.lignes || form.value.lignes.length === 0) {
    toast.error('La commande doit contenir au moins une ligne.')
    isValid = false
  }

  form.value.lignes.forEach((l, idx) => {
    const rowNum = idx + 1
    if (!l.designation || l.designation.trim() === '') {
      toast.error(`Désignation manquante à la ligne ${rowNum}`)
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
      await api.post('/bons-commande-client', form.value)
      toast.success('Bon de commande créé avec succès !')
      setTimeout(() => router.push('/bons-commande-client'), 1000)
    } else {
      await api.put(`/bons-commande-client/${route.params.id}`, form.value)
      toast.success('Bon de commande mis à jour !')
      setTimeout(() => router.push('/bons-commande-client'), 1000)
    }
  } catch (err) {
    if (err.response?.status === 422) {
      const serverErrors = err.response.data.errors
      Object.keys(serverErrors).forEach(key => {
        errors[key] = Array.isArray(serverErrors[key]) ? serverErrors[key][0] : serverErrors[key]
      })
      toast.error('Erreur de validation.')
    } else {
      console.error(err)
      toast.error('Erreur lors de l’enregistrement.')
    }
  } finally {
    saving.value = false
  }
}

async function transformToBL() {
  showConfirmModal.value = true
}

async function executeTransform() {
  showConfirmModal.value = false
  transforming.value = true
  try {
    const { data } = await api.post(`/workflow/bc-to-bl/${route.params.id}`, {
      entrepot_id: form.value.entrepot_id
    })
    toast.success('Bon de Livraison généré avec succès !')
    setTimeout(() => router.push(`/bons-livraison/${data.id}`), 1000)
  } catch (err) {
    toast.error('Erreur lors de la génération du BL.')
  } finally { transforming.value = false }
}

async function imprimer() { window.open(`/print/bcc/${route.params.id}`, '_blank') }

onMounted(async () => {
  loading.value = true
  try {
    const [cRes, pRes, prRes, tvaRes, wRes] = await Promise.all([
      api.get('/clients', { params: { per_page: 500 } }),
      api.get('/produits', { params: { per_page: 500 } }),
      api.get('/projets', { params: { per_page: 500 } }),
      api.get('/parametrage/referentiels/taux-tva').catch(() => ({ data: { data: [] } })),
      api.get('/parametrage/referentiels/entrepots')
    ])
    
    clients.value = cRes.data.data || cRes.data || []
    products.value = pRes.data.data || pRes.data || []
    produits.value = products.value.filter(p => p.is_actif !== false)
    projects.value = prRes.data.data || prRes.data || []
    tauxTvaList.value = tvaRes.data.data || tvaRes.data || []
    warehouses.value = wRes.data.data || wRes.data || []

    // Set default warehouse
    if (!form.value.entrepot_id) {
      const def = warehouses.value.find(w => w.is_default)
      if (def) form.value.entrepot_id = def.id
    }

    if (!isNew.value) {
      const { data } = await api.get(`/bons-commande-client/${route.params.id}`)
      const rawData = data.data || data
      if (rawData.date_commande) rawData.date_commande = rawData.date_commande.substring(0, 10)
      if (rawData.date_livraison_prevue) rawData.date_livraison_prevue = rawData.date_livraison_prevue.substring(0, 10)
      
      if (rawData.lignes) {
        rawData.lignes.forEach(l => {
          l.taux_tva = parseFloat(l.taux_tva) || 0;
          l.quantite = parseFloat(l.quantite) || 1;
          l.prix_unitaire = parseFloat(l.prix_unitaire) || 0;
          l.prix_unitaire_display = formatNumberInput(l.prix_unitaire); 
        });
      }
      
      form.value = { ...form.value, ...rawData }
      recalculate()
    }
  } catch (e) {
    console.error(e)
    toast.error('Erreur lors du chargement')
  } finally { loading.value = false }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.bcc-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #4F46E5;
  --c-accent-bg: #EEF2FF;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Structure ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; gap: 10px; }
.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-secondary-custom { background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; }
.accent-text { color: var(--c-accent); border-color: var(--c-accent-bg); }

/* ─── Hero ─── */
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.bcc-theme { background: linear-gradient(135deg, #4F46E5, #3730A3); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
.hero-meta { flex: 1; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; background: #F1F3F6; color: #6B7280; }

/* ─── KPI Strip ─── */
.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #F1F5F9; color: #475569; }
.kpi-item.bcc-accent .kpi-icon { background: #EEF2FF; color: var(--c-accent); }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

/* ─── Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }

@media (max-width: 1200px) {
  .content-grid { grid-template-columns: 1fr; }
  .col-side { order: 2; }
}

.table-container-custom { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #EEF2FF; color: var(--c-accent); display: flex; align-items: center; justify-content: center; }
.table-header-actions { justify-content: space-between; padding-right: 12px; }
.flex-align-center { display: flex; align-items: center; gap: 10px; }

/* ─── Forms ─── */
.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 18px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.form-group-custom label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-row-custom { display: flex; gap: 16px; }
input, select, textarea { padding: 10px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .9rem; background: #fff; }
.textarea-custom { width: 100%; border: none; font-size: .85rem; line-height: 1.5; outline: none; resize: vertical; }

/* ─── BARRE DE RECHERCHE (AUTOCOMPLETE / DOUCHETTE) ─── */
.product-search-bar-container { padding: 16px 20px; border-bottom: 1px dashed var(--c-border); background: #FCFDFE; }
.search-input-wrapper { position: relative; max-width: 600px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; }
.search-input { width: 100%; padding: 12px 14px 12px 42px; font-size: .9rem; border: 1.5px solid var(--c-accent); border-radius: 10px; box-shadow: 0 0 0 3px var(--c-accent-bg); transition: all 0.2s; background: #fff; outline: none; }
.search-input:focus { border-color: #3730A3; box-shadow: 0 0 0 4px #E0E7FF; }

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
.saas-table { width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 800px; }
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
.info-item-p { display: flex; justify-content: space-between; padding: 10px 20px; border-bottom: 1px solid #F1F5F9; }
.info-item-p .label { font-size: .75rem; color: var(--c-muted); font-weight: 600; }
.info-item-p .value { font-size: .85rem; font-weight: 700; }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Utilities ─── */
.mono { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.accent { color: var(--c-accent); }
.input-error { border-color: #EF4444 !important; background-color: #FEF2F2 !important; }
.error-text { color: #EF4444; font-size: 0.65rem; font-weight: 600; margin-top: 2px; }
</style>