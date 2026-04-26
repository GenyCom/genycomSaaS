<template>
  <div class="commande-detail-view">
    <Transition name="fade">
      <div v-if="saving || loading || transforming" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">{{ transforming ? 'Génération du BR...' : (saving ? 'Enregistrement...' : 'Chargement...') }}</p>
      </div>
    </Transition>

    <ConfirmModal 
      :show="showConfirm"
      title="Générer le Bon de Réception"
      message="Voulez-vous générer le Bon de Réception pour cette commande ? Cette action créera un document de réception pour vos stocks."
      confirmText="Générer le BR"
      @confirm="executeTransform"
      @cancel="showConfirm = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/commandes" class="back-btn" title="Retour aux commandes">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouvelle Commande Fournisseur' : (form.numero || 'Chargement...') }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button v-if="!isNew" class="btn-secondary-custom" @click="imprimer">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          <span>Imprimer</span>
        </button>
        <button v-if="!isNew && form.statut !== 'clos'" class="btn-secondary-custom accent-text" @click="transformToBR" :disabled="saving || transforming">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></svg>
          <span>Réceptionner (BR)</span>
        </button>
        <button class="btn-save cmd-theme-btn" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar cmd-theme">
        <span>{{ isNew ? '+' : 'CF' }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Bon de Commande Fournisseur</div>
        <h1 class="hero-name">{{ isNew ? 'Passer une Nouvelle Commande' : 'Commande : ' + form.numero }}</h1>
        <p class="hero-sub" v-if="selectedFournisseurName">Fournisseur : <strong>{{ selectedFournisseurName }}</strong></p>
      </div>
      <div v-if="!isNew" class="hero-status-badge" :style="{ backgroundColor: form.etat?.couleur + '15', color: form.etat?.couleur }">
        {{ (form.etat?.libelle || 'Validé').toUpperCase() }}
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
      <div class="kpi-item cmd-accent">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Montant à Régler TTC</p>
          <p class="kpi-value">{{ formatMoney(form.total_ttc) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon cmd-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <h3>Fournisseur & Dates</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Fournisseur *</label>
                <select v-model="form.fournisseur_id" :class="{ 'input-error': errors.fournisseur_id }">
                  <option value="" disabled>Choisir un fournisseur...</option>
                  <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.societe }}</option>
                </select>
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
          </div>
        </section>

        <section class="info-card">
          <div class="card-header table-header-actions">
            <div class="flex-align-center">
              <div class="card-header-icon cmd-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.37 2.63a2.12 2.12 0 1 1 3 3L12 15l-4 1 1-4Z"/></svg></div>
              <h3>Détail des Articles</h3>
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
                    <div class="prod-price">{{ formatMoney(parseFloat(prod.prix_ht_achat) || 0) }} DH HT</div>
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
                    <th style="width: 40%">Article / Service</th>
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
                        <option v-for="tva in tauxTvaList" :key="tva.id" :value="parseFloat(tva.taux)">
                          {{ parseFloat(tva.taux) }}%
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
            <h3>Notes d'Achat</h3>
          </div>
          <div class="card-body">
            <textarea v-model="form.observations" rows="6" class="textarea-custom" placeholder="Remarques pour le fournisseur..."></textarea>
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
const showConfirm = ref(false)
const transforming = ref(false)

const form = ref({
  numero: '',
  date_commande: new Date().toISOString().substring(0, 10),
  date_livraison_prevue: '',
  fournisseur_id: '',
  projet_id: '',
  total_ht: 0,
  total_tva: 0,
  total_ttc: 0,
  observations: '',
  statut: 'brouillon',
  lignes: []
})

const fournisseurs = ref([])
const products = ref([])
const produits = ref([])
const projects = ref([])
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
  // Pour une commande fournisseur, on utilise le prix d'achat
  const pu = parseFloat(produit.prix_ht_achat) || 0
  
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

const selectedFournisseurName = computed(() => {
  const f = fournisseurs.value.find(f => f.id === form.value.fournisseur_id)
  return f ? f.societe : null
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
    prix_unitaire: 0, 
    prix_unitaire_display: '0,00',
    taux_tva: 20, montant_ht: 0, montant_tva: 0, montant_ttc: 0 
  })
}

function removeLine(idx) {
  form.value.lignes.splice(idx, 1)
  recalculate()
}

function onProduitSelect(ligne) {
  const p = produits.value.find(prod => prod.id === ligne.produit_id)
  if (p) {
    ligne.designation = p.designation
    ligne.prix_unitaire = parseFloat(p.prix_ht_achat) || 0
    ligne.prix_unitaire_display = formatNumberInput(ligne.prix_unitaire)
    ligne.taux_tva = p.taux_tva || 20
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

async function save() {
  if (!form.value.fournisseur_id) {
    toast.error('Veuillez sélectionner un fournisseur')
    return
  }
  if (form.value.lignes.length === 0) {
    toast.error('Ajoutez au moins une ligne')
    return
  }

  saving.value = true
  try {
    if (isNew.value) {
      await api.post('/commandes', form.value)
      toast.success('Commande créée avec succès !')
      setTimeout(() => router.push('/commandes'), 1000)
    } else {
      await api.put(`/commandes/${route.params.id}`, form.value)
      toast.success('Commande mise à jour !')
      setTimeout(() => router.push('/commandes'), 1000)
    }
  } catch (err) {
    console.error(err)
    toast.error('Erreur lors de l’enregistrement.')
  } finally {
    saving.value = false
  }
}

async function imprimer() { window.open(`/print/commande/${route.params.id}`, '_blank') }

async function transformToBR() {
  showConfirm.value = true
}

async function executeTransform() {
  showConfirm.value = false
  transforming.value = true
  try {
    const { data } = await api.post(`/workflow/commande-fournisseur-to-br/${route.params.id}`)
    toast.success('Bon de Réception généré avec succès !')
    setTimeout(() => {
      router.push(`/bons-reception/${data.id}`)
    }, 1000)
  } catch (err) {
    console.error(err)
    toast.error(err.response?.data?.message || 'Erreur lors de la transformation')
  } finally {
    transforming.value = false
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const [fRes, pRes, prRes, tvaRes] = await Promise.all([
      api.get('/fournisseurs', { params: { per_page: 500 } }),
      api.get('/produits', { params: { per_page: 500 } }),
      api.get('/projets', { params: { per_page: 500 } }),
      api.get('/parametrage/referentiels/taux-tva')
    ])
    fournisseurs.value = fRes.data.data || fRes.data || []
    
    const rawProducts = pRes.data.data || pRes.data || []
    produits.value = rawProducts.filter(p => p.is_actif !== false)
    
    projects.value = prRes.data.data || prRes.data || []
    tauxTvaList.value = tvaRes.data.data || tvaRes.data || []
	
    if (!isNew.value) {
      const { data } = await api.get(`/commandes/${route.params.id}`)
      const rawData = data.data || data
      if (rawData.date_commande) rawData.date_commande = rawData.date_commande.substring(0, 10)
      if (rawData.date_livraison_prevue) rawData.date_livraison_prevue = rawData.date_livraison_prevue.substring(0, 10)
	  
      if (rawData.lignes) {
        rawData.lignes.forEach(ligne => {
          ligne.taux_tva = parseFloat(ligne.taux_tva) || 0;
          ligne.quantite = parseFloat(ligne.quantite) || 1;
          ligne.prix_unitaire = parseFloat(ligne.prix_unitaire) || 0;
          ligne.prix_unitaire_display = formatNumberInput(ligne.prix_unitaire); 
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
.commande-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #059669;
  --c-accent-bg: #ECFDF5;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Structure ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; gap: 10px; }
.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); }
.btn-secondary-custom { background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; }
.accent-text { color: var(--c-accent); border-color: var(--c-accent-bg); }

/* ─── Hero ─── */
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.cmd-theme { background: linear-gradient(135deg, #059669, #10B981); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
.hero-meta { flex: 1; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; }

/* ─── KPI Strip ─── */
.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.kpi-item.cmd-accent .kpi-icon { background: #ECFDF5; color: var(--c-accent); }
.kpi-item.neutral .kpi-icon { background: #F1F5F9; color: #475569; }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

/* ─── Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #ECFDF5; color: var(--c-accent); display: flex; align-items: center; justify-content: center; }
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
.search-input:focus { border-color: #047857; box-shadow: 0 0 0 4px #D1FAE5; }

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

.toast-notification { position: fixed; top: 1rem; right: 1rem; padding: 0.85rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.toast-notification.success { background: #10b981; color: #fff; }
.toast-notification.error { background: #ef4444; color: #fff; }

/* ─── Utilities ─── */
.mono { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
</style>