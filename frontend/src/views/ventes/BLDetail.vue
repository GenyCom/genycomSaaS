<template>
  <div class="bl-detail-view">
    <Transition name="fade">
      <div v-if="loading || transforming || saving" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">{{ transforming ? 'Génération de la facture...' : (saving ? 'Enregistrement...' : 'Chargement...') }}</p>
      </div>
    </Transition>

    <ConfirmModal 
      :show="showConfirmModal"
      title="Générer la Facture"
      message="Voulez-vous générer la Facture à partir de ce Bon de Livraison ? Cette opération créera un nouveau document financier."
      confirmText="Générer la Facture"
      @confirm="executeTransform"
      @cancel="showConfirmModal = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/bons-livraison" class="back-btn" title="Retour aux livraisons">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouveau Bon de Livraison' : (bl.numero || 'Chargement...') }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button v-if="!isNew" class="btn-secondary-custom" @click="imprimer">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          <span>Imprimer BL</span>
        </button>
        
        <button v-if="isNew" class="btn-save bl-theme-btn" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer</span>
        </button>

        <button v-if="!isNew && !bl.facture_id" class="btn-save bl-theme-btn" @click="transformToFacture" :disabled="transforming">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <span>Générer la Facture</span>
        </button>
        <router-link v-else-if="!isNew" :to="`/factures/${bl.facture_id}`" class="hero-status-badge success hover-link">
           FACTURE : {{ bl.facture?.numero || 'VOIR' }}
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar bl-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Bon de Livraison</div>
        <h1 class="hero-name">{{ isNew ? 'Créer une livraison directe' : (bl.numero || 'Bon de Livraison') }}</h1>
        <p class="hero-sub" v-if="bl.client || form.client_id">Destinataire : <strong>{{ bl.client?.societe || selectedClientName || '...' }}</strong></p>
      </div>
      <div 
        v-if="!isNew && bl.etat" 
        class="hero-status-badge-dynamic" 
        :style="{ backgroundColor: bl.etat.color + '20', color: bl.etat.color, borderColor: bl.etat.color + '40' }"
      >
        {{ bl.etat.libelle }}
      </div>
      <div v-else-if="!isNew" class="hero-status-badge success">LIVRÉ</div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Articles Livrés</p>
          <p class="kpi-value">{{ totalArticlesLivres }} <span>Unités</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item bl-accent">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Date Livraison</p>
          <p class="kpi-value">{{ isNew ? formatDate(form.date_livraison) : formatDate(bl.date_livraison) }}</p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item bl-accent">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Montant TTC</p>
          <p class="kpi-value">{{ formatMoney(totalTTC) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <section v-if="isNew" class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon bl-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Client & Expédition</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Client *</label>
                <select v-model="form.client_id">
                  <option value="" disabled>Choisir un client...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.societe || c.display_name }}</option>
                </select>
              </div>
              <div class="form-group-custom">
                <label>Date Livraison</label>
                <input v-model="form.date_livraison" type="date" />
              </div>
            </div>
            <div class="form-group-custom mt-2">
              <label>Dépôt d'Expédition (Entrepôt) *</label>
              <select v-model="form.entrepot_id" class="accent-select">
                <option v-for="e in warehouses" :key="e.id" :value="e.id">
                  {{ e.nom }} {{ e.is_default ? '(Par défaut)' : '' }}
                </option>
              </select>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header table-header-actions">
            <div class="card-header-icon bl-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
            <h3>Détail des articles livrés</h3>
            <button v-if="isNew" class="btn-add-line" @click="addLine">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Ajouter
            </button>
          </div>
          <div class="card-body p-0">
            <table class="saas-table">
              <thead>
                <tr>
                  <th style="width: 40%">Désignation de l'article</th>
                  <th v-if="!isNew" style="width: 15%" class="text-center">Qté Comm.</th>
                  <th style="width: 15%" class="text-center">Qté Livrée</th>
                  <th style="width: 15%" class="text-right">P.U HT</th>
                  <th style="width: 15%" class="text-right">Total HT</th>
                  <th v-if="isNew" style="width: 5%"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(l, idx) in (isNew ? form.lignes : bl.lignes)" :key="idx">
                  <td>
                    <template v-if="isNew">
                      <select v-model="l.produit_id" @change="onProduitSelect(l)" class="select-inline-table">
                        <option value="">-- Texte libre --</option>
                        <option v-for="p in produits" :key="p.id" :value="p.id">[{{ p.reference }}] {{ p.designation }}</option>
                      </select>
                      <textarea v-model="l.designation" class="input-inline-sub" placeholder="Description..."></textarea>
                    </template>
                    <template v-else>
                      <div class="article-name">{{ l.designation }}</div>
                      <div class="article-sub" v-if="l.produit">Réf : {{ l.produit.reference }}</div>
                    </template>
                  </td>
                  <td v-if="!isNew" class="text-center text-muted font-medium">{{ l.quantite_prevue }}</td>
                  <td class="text-center">
                    <input v-if="isNew" v-model="l.quantite_livree" type="number" step="0.01" class="input-inline-table text-center" />
                    <span v-else class="status-pill status-success-light font-black">{{ l.quantite_livree }}</span>
                  </td>
                  <td>
                    <input v-if="isNew" v-model="l.prix_unitaire" type="number" step="0.01" class="input-inline-table text-right mono" />
                    <span v-else class="text-right mono" style="display:block;">{{ formatMoney(l.prix_unitaire) }}</span>
                  </td>
                  <td class="text-right mono font-bold">{{ formatMoney((l.quantite_livree || 0) * (l.prix_unitaire || 0)) }}</td>
                  <td v-if="isNew" class="text-center">
                    <button @click="removeLine(idx)" class="btn-row-delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="info-card mt-6">
          <div class="card-body signature-area">
             <p class="signature-label">Cachet & Signature Client</p>
             <div class="signature-placeholder"></div>
          </div>
        </section>
      </div>

      <div class="col-side">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <h3>Informations</h3>
          </div>
          <div class="card-body p-0">
            <div class="info-item">
              <span class="info-label">Date Livraison</span>
              <span class="info-value">{{ isNew ? formatDate(form.date_livraison) : formatDate(bl.date_livraison) }}</span>
            </div>
            <div class="info-item" v-if="!isNew && bl.entrepot">
              <span class="info-label">Entrepôt de sortie</span>
              <span class="info-value accent">{{ bl.entrepot.nom }}</span>
            </div>
            <div v-if="!isNew && bl.bon_commande" class="info-item">
              <span class="info-label">Commande N°</span>
              <span class="info-value mono accent">{{ bl.bon_commande.numero }}</span>
            </div>
            <div v-if="!isNew && bl.devis" class="info-item">
              <span class="info-label">Référence Devis</span>
              <span class="info-value mono">{{ bl.devis.numero }}</span>
            </div>
          </div>
        </section>

        <section class="info-card side-card totals-premium-card-bl">
          <div class="card-header-bl">
             <h3>Résumé financier</h3>
          </div>
          <div class="total-inner-bl">
            <div class="total-row-bl">
              <span class="total-label-bl">Total Net HT</span>
              <span class="total-value-bl">{{ formatMoney(totalHT) }} DH</span>
            </div>
            <div class="total-row-bl">
              <span class="total-label-bl">TVA Totale (20%)</span>
              <span class="total-value-bl">{{ formatMoney(totalTVA) }} DH</span>
            </div>
			<div class="total-row-bl main-total-bl">
              <span class="label-main-bl">NET TTC</span>
              <span class="amount-bl">{{ formatMoney(totalTTC) }} DH</span>
            </div>
          </div>
        </section>

        <section class="info-card mt-4">
          <div class="card-header">
            <div class="card-header-icon notes"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
            <h3>Observations</h3>
          </div>
          <div class="card-body">
            <textarea v-if="isNew" v-model="form.observations" rows="5" class="textarea-custom" placeholder="Notes sur la livraison..."></textarea>
            <p v-else class="notes-text">{{ bl.observations || 'Aucune observation.' }}</p>
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
const bl = ref({})
const loading = ref(true)
const transforming = ref(false)
const saving = ref(false)
const showConfirmModal = ref(false)

const form = ref({
  client_id: '',
  date_livraison: new Date().toISOString().substring(0, 10),
  entrepot_id: null,
  observations: '',
  lignes: []
})

const clients = ref([])
const produits = ref([])
const warehouses = ref([])

const totalArticlesLivres = computed(() => {
  const items = isNew.value ? form.value.lignes : bl.value.lignes
  return items?.reduce((acc, curr) => acc + (parseFloat(curr.quantite_livree) || 0), 0) || 0
})

const totalHT = computed(() => {
  const items = isNew.value ? form.value.lignes : bl.value.lignes
  return items?.reduce((acc, curr) => acc + (parseFloat(curr.quantite_livree) || 0) * (parseFloat(curr.prix_unitaire) || 0), 0) || 0
})
const totalTVA = computed(() => totalHT.value * 0.20)
const totalTTC = computed(() => totalHT.value + totalTVA.value)

const selectedClientName = computed(() => {
  const c = clients.value.find(x => x.id === form.value.client_id)
  return c ? (c.societe || c.display_name) : ''
})

function formatMoney(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR')
}

function addLine() {
  form.value.lignes.push({ produit_id: '', designation: '', quantite_livree: 1, prix_unitaire: 0 })
}
function removeLine(idx) { form.value.lignes.splice(idx, 1) }

function onProduitSelect(ligne) {
  const p = produits.value.find(x => x.id === ligne.produit_id)
  if (p) {
    ligne.designation = p.designation
    ligne.prix_unitaire = p.prix_ht_vente || p.prix_vente_ht || 0
  }
}

async function save() {
  if (!form.value.client_id) return toast.error('Sélectionnez un client')
  if (!form.value.entrepot_id) return toast.error('Sélectionnez un entrepôt')
  if (form.value.lignes.length === 0) return toast.error('Ajoutez au moins un article')

  saving.value = true
  try {
    const { data } = await api.post('/bons-livraison', form.value)
    toast.success('Bon de livraison créé avec succès !')
    setTimeout(() => router.push('/bons-livraison'), 1000)
  } catch (e) {
    toast.error('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { saving.value = false }
}

async function transformToFacture() {
  showConfirmModal.value = true
}

async function executeTransform() {
  showConfirmModal.value = false
  transforming.value = true
  try {
    const { data } = await api.post(`/workflow/bl-to-facture/${route.params.id}`)
    toast.success(data.message)
    setTimeout(() => {
      router.push(`/factures/${data.id}`)
    }, 1500)
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de la facturation.')
  } finally {
    transforming.value = false
  }
}

async function imprimer() {
  window.open(`/print/bl/${route.params.id}`, '_blank')
}

onMounted(async () => {
  loading.value = true
  try {
    const requests = [
      api.get('/clients', { params: { per_page: 500 } }),
      api.get('/produits', { params: { per_page: 500 } }),
      api.get('/parametrage/referentiels/entrepots')
    ]
    const [cRes, pRes, wRes] = await Promise.all(requests)
    
    clients.value = cRes.data.data || cRes.data || []
    produits.value = (pRes.data.data || pRes.data || []).filter(p => p.is_actif !== false)
    warehouses.value = wRes.data.data || wRes.data || []

    if (!form.value.entrepot_id) {
      const def = warehouses.value.find(w => w.is_default)
      if (def) form.value.entrepot_id = def.id
    }

    if (!isNew.value) {
      const { data } = await api.get(`/bons-livraison/${route.params.id}`)
      bl.value = data
    } else {
      addLine()
    }
  } catch (e) {
    toast.error('Erreur de chargement')
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.bl-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #10B981;
  --c-accent-bg: #ECFDF5;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Top Bar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; transition: all .2s; }
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; align-items: center; gap: 10px; }
.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
.bl-theme-btn { background: #10B981; }
.btn-secondary-custom { background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; }

/* ─── Hero Header ─── */
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.bl-theme { background: linear-gradient(135deg, #10B981, #059669); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #10B981; margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #10B981; border-radius: 50%; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
.hero-status-badge.success { background: #DCFCE7; color: #166534; }
.hero-status-badge-dynamic { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; text-transform: uppercase; border: 1px solid currentColor; }
.hover-link { text-decoration: none; transition: opacity 0.2s; }

/* ─── KPI Strip ─── */
.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.kpi-item.bl-accent .kpi-icon { background: #ECFDF5; color: #10B981; }
.kpi-item.neutral .kpi-icon { background: #F1F5F9; color: #475569; }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

/* ─── Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; }

/* ─── Forms ─── */
.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 18px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.form-group-custom label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-row-custom { display: flex; gap: 16px; }
input, select, textarea { padding: 10px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .9rem; background: #fff; width: 100%; outline: none; }
.accent-select { border-color: var(--c-accent); background: var(--c-accent-bg); font-weight: 700; color: var(--c-accent); }

/* ─── Table ─── */
.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th { background: #F9FAFB; padding: 12px 16px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 12px 16px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
.article-name { font-size: .88rem; font-weight: 700; color: var(--c-text); }
.article-sub { font-size: .72rem; color: var(--c-muted); margin-top: 2px; }
.select-inline-table { width: 100%; border: 1px solid #E2E8F0; border-radius: 6px; font-weight: 700; color: var(--c-accent); padding: 8px 10px; background: #fff; margin-bottom: 6px; }
.input-inline-sub { width: 100%; border: 1px solid #E2E8F0; border-radius: 6px; font-size: .85rem; color: var(--c-text); padding: 10px; min-height: 50px; font-family: inherit; resize: vertical; display: block; }
.input-inline-table { width: 100%; border: 1.5px solid #D5D9E2; border-radius: 8px; padding: 10px; background: #fff; }

/* ─── Informations Card ─── */
.info-item { padding: 16px 20px; border-bottom: 1px solid #F1F5F9; display: flex; flex-direction: column; gap: 6px; }
.info-item:last-child { border-bottom: none; }
.info-label { font-size: .68rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .05em; }
.info-value { font-size: 1rem; font-weight: 700; color: var(--c-text); }
.mono { font-family: 'JetBrains Mono', monospace; }
.accent { color: #10B981; }

/* ─── Signature ─── */
.signature-area { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px !important; background: #F9FAFB; border: 2px dashed #E2E8F0; border-radius: 12px; margin: 20px; }
.signature-label { font-size: .7rem; font-weight: 800; color: #94A3B8; text-transform: uppercase; margin-bottom: 24px; }
.signature-placeholder { width: 200px; height: 1px; background: #E2E8F0; }

/* ─── Notes ─── */
.notes-text { font-size: .85rem; line-height: 1.6; color: var(--c-text); font-style: italic; }

/* ─── Utilities ─── */
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: #10B981; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.status-pill { padding: 4px 12px; border-radius: 100px; font-size: .72rem; }
.status-success-light { background: #ECFDF5; color: #059669; }

/* ─── Financial Card for BL ─── */
.totals-premium-card-bl { background: #FFF; border: 1.5px solid #10B981; border-radius: 16px; margin-bottom: 12px; }
.card-header-bl { padding: 12px 18px; border-bottom: 1px solid #F1F5F9; }
.card-header-bl h3 { font-size: .7rem; font-weight: 800; text-transform: uppercase; color: #64748B; margin: 0; }
.total-inner-bl { padding: 18px; }
.total-row-bl { display: flex; justify-content: space-between; margin-bottom: 8px; }
.total-label-bl { font-size: .75rem; color: #64748B; }
.total-value-bl { font-size: .85rem; font-weight: 700; color: #1A1D23; }
.main-total-bl { margin-top: 12px; padding-top: 12px; border-top: 2px solid #ECFDF5; }
.label-main-bl { font-size: .8rem; font-weight: 800; color: #059669; }
.amount-bl { font-size: 1.25rem; font-weight: 900; color: #000; }

.btn-add-line { background: var(--c-accent-bg); color: var(--c-accent); border: none; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: .75rem; cursor: pointer; display: flex; align-items: center; gap: 4px; }
.btn-row-delete { background: none; border: none; color: #94A3B8; cursor: pointer; padding: 8px; border-radius: 6px; }
.btn-row-delete:hover { background: #FEE2E2; color: #DC2626; }
.mb-4 { margin-bottom: 16px; }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 16px; }
.mt-6 { margin-top: 24px; }
.p-0 { padding: 0; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: 700; }
.font-black { font-weight: 900; }
.mono { font-family: 'JetBrains Mono', monospace; }

.toast-notification { position: fixed; top: 1rem; right: 1rem; padding: 0.85rem 1.5rem; border-radius: 8px; z-index: 9999; animation: slideIn 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.toast-notification.success { background: #10b981; color: #fff; }
.toast-notification.error { background: #ef4444; color: #fff; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>