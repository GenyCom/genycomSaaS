<template>
  <div class="avoir-detail-view animate-fade-in">
    
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <router-link to="/avoirs-clients" class="breadcrumb-link">Avoirs Clients</router-link>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouvel Avoir' : 'Détails de l\'avoir' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/avoirs-clients" class="btn-secondary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Retour
        </router-link>
        <button v-if="!isNew" class="btn-secondary-custom" title="Imprimer le document">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
          Imprimer PDF
        </button>
        <button v-if="!isNew && form.etat_id === 1" class="btn-success-custom" @click="valider">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Valider & Impacter Stock
        </button>
        <button class="btn-primary-custom" @click="save">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Enregistrer
        </button>
      </div>
    </div>

    <!-- Modal de Confirmation -->
    <ConfirmModal 
      :show="showConfirmModal"
      title="Valider l'avoir client"
      message="Voulez-vous vraiment valider cet avoir ? Cette action impactera le stock et ne pourra pas être annulée."
      confirmText="Valider l'Avoir"
      @confirm="executeValidation"
      @cancel="showConfirmModal = false"
    />

    <div class="hero-header">
      <div class="hero-avatar avoir-theme">
        <span v-if="isNew">+</span>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Retours & Réductions
        </div>
        <h1 class="hero-name">{{ isNew ? 'Saisie d\'un Avoir Client' : 'Avoir ' + form.numero }}</h1>
        <p class="hero-sub" v-if="!isNew">Document généré le {{ formatDateDisplay(form.date_avoir) }}</p>
      </div>
    </div>

    <div class="form-card mb-20">
      <h3 class="section-title">Informations Générales</h3>
      <div class="form-grid-2">
        <div class="form-group">
          <label class="saas-label">Client concerné <span class="required">*</span></label>
          <select v-model="form.client_id" class="saas-select" @change="onClientChange">
            <option value="" disabled>Sélectionner un client...</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.societe || c.nom }}</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="saas-label">Réf. Facture d'origine</label>
          <select class="saas-select" v-model="form.facture_id">
            <option :value="null">Indépendant (Aucune facture liée)</option>
            <option v-for="f in factures" :key="f.id" :value="f.id">{{ f.numero }} ({{ formatDateDisplay(f.date_facture) }})</option>
          </select>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-group">
          <label class="saas-label">Date de l'Avoir <span class="required">*</span></label>
          <input v-model="form.date_avoir" type="date" class="saas-input" />
        </div>
        <div class="form-group">
          <label class="saas-label">Numéro d'Avoir</label>
          <input v-model="form.numero" type="text" class="saas-input font-mono" disabled style="background-color: #F8FAFC;" />
        </div>
      </div>

      <div class="form-separator"></div>

      <div class="form-group">
        <label class="saas-label">Motif de l'Avoir <span class="required">*</span></label>
        <input v-model="form.motif" type="text" class="saas-input" placeholder="Ex: Retour marchandise défectueuse, Remise commerciale retard..." />
      </div>
    </div>

    <div class="form-card">
      <div class="section-header-flex">
        <h3 class="section-title" style="margin: 0;">Lignes de l'Avoir</h3>
        <button class="btn-secondary-custom btn-sm" @click="addLine">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Ajouter une ligne
        </button>
      </div>

      <div class="table-container-custom mt-16">
        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 35%">Désignation & Produit</th>
              <th style="width: 12%" class="text-right">Qté</th>
              <th style="width: 15%" class="text-right">P.U HT</th>
              <th style="width: 15%" class="text-right">TVA %</th>
              <th style="width: 15%" class="text-right">Total HT</th>
              <th style="width: 8%"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ligne, idx) in form.lignes" :key="idx" class="table-row-input">
              <td>
                <div class="flex-input-group">
                  <select v-model="ligne.produit_id" @change="onProduitSelect(ligne)" class="saas-input-table select-compact">
                    <option value="">Libre</option>
                    <option v-for="p in produits" :key="p.id" :value="p.id">{{ p.nom }}</option>
                  </select>
                  <input v-model="ligne.designation" class="saas-input-table" placeholder="Description de l'article..." />
                </div>
              </td>
              <td><input v-model="ligne.quantite" type="number" step="0.01" class="saas-input-table text-right font-mono" min="0.01" /></td>
              <td><input v-model="ligne.prix_unitaire" type="number" step="0.01" class="saas-input-table text-right font-mono" /></td>
              <td><input v-model="ligne.taux_tva" type="number" class="saas-input-table text-right font-mono" /></td>
              <td class="text-right amount-cell">{{ formatMoney(ligne.quantite * ligne.prix_unitaire) }}</td>
              <td class="text-center">
                <button class="action-btn delete mx-auto" title="Supprimer la ligne" @click="removeLine(idx)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </td>
            </tr>
            <tr v-if="form.lignes.length === 0">
              <td colspan="6" class="empty-row">
                <p>Aucune ligne. Ajoutez les articles concernés par l'avoir.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="totals-wrapper">
        <div class="totals-box">
           <div class="total-row">
             <span class="total-label">Total HT</span>
             <span class="total-value font-mono">{{ formatMoney(totalHT) }}</span>
           </div>
           <div class="total-row">
             <span class="total-label">TVA Globale</span>
             <span class="total-value font-mono">{{ formatMoney(totalTVA) }}</span>
           </div>
           <div class="total-row grand-total">
             <span class="total-label">Net à Déduire TTC</span>
             <span class="total-value font-mono text-accent">- {{ formatMoney(totalTTC) }}</span>
           </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const router = useRouter()
const route = useRoute()
const isNew = computed(() => route.params.id === 'new')
const loading = ref(false)
const showConfirmModal = ref(false)

const form = ref({
  numero: 'Brouillon',
  date_avoir: new Date().toISOString().substring(0, 10),
  client_id: '',
  facture_id: null,
  motif: '',
  lignes: []
})

const clients = ref([])
const produits = ref([])
const factures = ref([])

const fetchFactures = async (clientId) => {
  if (!clientId) {
    factures.value = []
    return
  }
  try {
    const { data } = await api.get('/factures', { params: { client_id: clientId, per_page: 100 } })
    factures.value = data.data || data
  } catch (err) {
    console.error('Erreur chargement factures:', err)
  }
}

async function onClientChange() {
  form.value.facture_id = null
  await fetchFactures(form.value.client_id)
}

onMounted(async () => {
  loading.value = true
  try {
    const [resCl, resPr] = await Promise.all([
      api.get('/clients'),
      api.get('/produits')
    ])
    clients.value = resCl.data.data || resCl.data
    produits.value = (resPr.data.data || resPr.data).map(p => ({
      id: p.id,
      nom: p.designation || p.nom,
      prix_vente: parseFloat(p.prix_vente) || 0,
      tva: p.tva || 20
    }))

    if (!isNew.value) {
      const { data } = await api.get(`/avoirs-clients/${route.params.id}`)
      form.value = {
        ...data,
        lignes: data.lignes.map(l => ({
          ...l,
          prix_unitaire: parseFloat(l.prix_unitaire),
          quantite: parseFloat(l.quantite),
          taux_tva: parseFloat(l.taux_tva) || 20
        }))
      }
      if (form.value.client_id) {
        await fetchFactures(form.value.client_id)
      }
    } else {
      addLine()
    }
  } catch (err) {
    console.error('Erreur chargement:', err)
  } finally {
    loading.value = false
  }
})

function formatDateDisplay(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

function addLine() {
  form.value.lignes.push({ produit_id: '', designation: '', quantite: 1, prix_unitaire: 0, taux_tva: 20 })
}

function onProduitSelect(ligne) {
  if (ligne.produit_id) {
    const prod = produits.value.find(p => p.id === ligne.produit_id)
    if (prod) {
      ligne.designation = prod.nom
      ligne.prix_unitaire = prod.prix_vente
      ligne.taux_tva = prod.tva
    }
  }
}

function removeLine(idx) {
  form.value.lignes.splice(idx, 1)
}

const totalHT = computed(() => form.value.lignes.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire), 0))
const totalTVA = computed(() => form.value.lignes.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire * (l.taux_tva/100)), 0))
const totalTTC = computed(() => totalHT.value + totalTVA.value)

function formatMoney(val) { return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }

async function save() {
  if (!form.value.client_id || !form.value.motif || form.value.lignes.length === 0) {
    toast.error('Veuillez remplir tous les champs obligatoires.', 'Champs manquants')
    return
  }

  loading.value = true
  try {
    if (isNew.value) {
      await api.post('/avoirs-clients', form.value)
    } else {
      await api.put(`/avoirs-clients/${route.params.id}`, form.value)
    }
    toast.success('Avoir enregistré avec succès !')
    router.push('/avoirs-clients')
  } catch (err) {
    console.error('Erreur sauvegarde:', err)
    toast.error('Erreur lors de l\'enregistrement.')
  } finally {
    loading.value = false
  }
}

async function valider() {
  showConfirmModal.value = true
}

async function executeValidation() {
  showConfirmModal.value = false
  loading.value = true
  try {
    await api.post(`/avoirs-clients/${route.params.id}/valider`)
    toast.success('Avoir validé et stock mis à jour !')
    router.push('/avoirs-clients')
  } catch (err) {
    console.error('Erreur validation:', err)
    toast.error('Erreur lors de la validation.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ─── Design Tokens ─── */
.avoir-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #6366F1;
  --c-accent-bg: #EEF2FF; --c-danger: #E11D48;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

/* ─── Topbar & Actions ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-link { color: var(--c-muted); font-weight: 500; text-decoration: none; transition: color 0.2s; }
.breadcrumb-link:hover { color: var(--c-accent); }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }
.topbar-actions { display: flex; gap: 12px; }

.btn-primary-custom, .btn-secondary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  border-radius: 8px; font-size: .85rem; font-weight: 600; text-decoration: none; cursor: pointer;
  transition: all .2s; outline: none; border: 1.5px solid transparent;
}
.btn-primary-custom { background: var(--c-accent); color: #fff; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2); }
.btn-primary-custom:hover:not(:disabled) { background: #4F46E5; transform: translateY(-1px); }
.btn-success-custom { 
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  border-radius: 8px; font-size: .85rem; font-weight: 600; text-decoration: none; cursor: pointer;
  transition: all .2s; outline: none; border: 1.5px solid transparent;
  background: #10B981; color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); 
}
.btn-success-custom:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
.btn-secondary-custom { background: #fff; color: var(--c-text); border-color: var(--c-border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.btn-secondary-custom:hover { background: #F9FAFB; border-color: #D1D5DB; }
.btn-sm { padding: 6px 12px; font-size: .8rem; }

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); max-width: 900px; margin-left: auto; margin-right: auto;
}
.avoir-theme { background: linear-gradient(135deg, #8B5CF6, #6D28D9); color: #fff; }
.hero-avatar { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;}
.hero-name { font-size: 1.2rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-sub { color: var(--c-muted); font-size: 0.85rem; margin-top: 2px;}

/* ─── Formulaire ─── */
.form-card {
  background: #fff; border: 1px solid var(--c-border); border-radius: 16px; 
  padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,.03);
  max-width: 900px; margin: 0 auto;
}
.mb-20 { margin-bottom: 20px; }
.mt-16 { margin-top: 16px; }

.section-title { font-size: 1.05rem; font-weight: 700; color: var(--c-text); margin-bottom: 20px; margin-top: 0; }
.section-header-flex { display: flex; justify-content: space-between; align-items: center; }

.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 20px; }
@media (max-width: 768px) { .form-grid-2 { grid-template-columns: 1fr; } }

.form-group { display: flex; flex-direction: column; }
.form-separator { height: 1px; background: var(--c-border); margin: 24px 0; }

.saas-label { font-size: .85rem; font-weight: 600; color: var(--c-text); margin-bottom: 8px; }
.required { color: var(--c-danger); }

.saas-input, .saas-select {
  padding: 12px 16px; border-radius: 8px; border: 1.5px solid var(--c-border);
  background: var(--c-surface); color: var(--c-text); font-size: .95rem; font-family: inherit;
  transition: all 0.2s; width: 100%; box-sizing: border-box; outline: none;
}
.saas-input:focus, .saas-select:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
.saas-input:disabled { color: var(--c-muted); cursor: not-allowed; }

/* ─── Table Lignes ─── */
.table-container-custom { border: 1px solid var(--c-border); border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 12px 16px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 8px 16px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }

/* Champs de saisie dans le tableau */
.flex-input-group { display: flex; gap: 8px; align-items: center; }
.saas-input-table {
  padding: 8px 12px; border-radius: 6px; border: 1px solid transparent; background: transparent;
  color: var(--c-text); font-size: .9rem; font-family: inherit; transition: all 0.2s; width: 100%; box-sizing: border-box; outline: none;
}
.saas-input-table:hover { border-color: var(--c-border); background: #F9FAFB; }
.saas-input-table:focus { border-color: var(--c-accent); background: #fff; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1); }
.select-compact { width: 130px; flex-shrink: 0; border: 1px solid var(--c-border); background: #fff; }

.table-row-input:hover { background: #F8FAFC; }
.font-mono { font-family: 'JetBrains Mono', monospace; }
.amount-cell { font-size: .95rem; font-weight: 700; color: var(--c-text); }
.text-accent { color: var(--c-accent) !important; }

/* ─── Actions & Totaux ─── */
.action-btn { width: 28px; height: 28px; border-radius: 6px; border: 1.5px solid transparent; background: transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { background: #fff; border-color: var(--c-border); }
.action-btn.delete:hover { color: #DC2626; border-color: #FECACA; background: #FEF2F2; }

.totals-wrapper { display: flex; justify-content: flex-end; }
.totals-box { width: 320px; background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid var(--c-border); display: flex; flex-direction: column; gap: 12px; }
.total-row { display: flex; justify-content: space-between; align-items: center; font-size: .95rem; color: var(--c-muted); }
.total-value { font-weight: 600; color: var(--c-text); }
.grand-total { border-top: 1px solid #E2E8F0; padding-top: 12px; margin-top: 4px; font-size: 1.1rem; font-weight: 800; color: var(--c-text); }
.grand-total .total-label { color: var(--c-text); }

.text-right { text-align: right; }
.text-center { text-align: center; }
.mx-auto { margin-left: auto; margin-right: auto; }
.empty-row { padding: 40px 0 !important; text-align: center; color: var(--c-muted); font-size: .85rem; }
</style>