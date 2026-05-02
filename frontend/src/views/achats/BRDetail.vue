<template>
  <div class="br-detail-view">
    <Transition name="fade">
      <div v-if="saving || loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p>{{ saving ? 'Enregistrement...' : 'Chargement...' }}</p>
      </div>
    </Transition>

    <ConfirmModal 
      :show="showConfirm"
      title="Générer la facture d'achat"
      message="Voulez-vous générer la facture d'achat pour ce bon de réception ? Cette opération créera un nouveau document financier fournisseur."
      confirmText="Générer la facture"
      @confirm="executeTransform"
      @cancel="showConfirm = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/bons-reception" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouveau Bon de Réception' : (brData.numero || 'Chargement...') }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button v-if="!isNew && !hasFacture" class="btn-secondary-custom accent-text" @click="transformToFactureAchat" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          <span>Facturer (Achat)</span>
        </button>
        <button v-if="isNew" class="btn-save br-theme-btn" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar br-theme">
        <span>{{ isNew ? '+' : 'BR' }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Bon de Réception</div>
        <h1 class="hero-name">{{ isNew ? 'Établir un Bon de Réception' : 'Réception : ' + brData.numero }}</h1>
        <p class="hero-sub" v-if="brData.fournisseur_societe">Fournisseur : <strong>{{ brData.fournisseur_societe }}</strong></p>
      </div>
      <div v-if="!isNew" class="hero-status-badge" :class="brData.statut">
        {{ (brData.statut || 'brouillon').toUpperCase() }}
      </div>
    </div>

    <!-- KPI Strip -->
    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Nb Articles</p>
          <p class="kpi-value">{{ form.lignes.length }}</p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Total HT Réceptionné</p>
          <p class="kpi-value">{{ formatMoney(totalHT) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item br-accent">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Commande Source</p>
          <p class="kpi-value" style="font-size: 1rem;">{{ brData.commande_numero || '—' }}</p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <!-- Identification -->
        <section v-if="isNew" class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon br-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
            <h3>Fournisseur & Dates</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Fournisseur *</label>
                <select v-model="form.fournisseur_id">
                  <option value="" disabled>Choisir un fournisseur...</option>
                  <option v-for="f in fournisseurs" :key="f.id" :value="f.id">{{ f.societe }}</option>
                </select>
              </div>
              <div class="form-group-custom">
                <label>Date Réception</label>
                <input v-model="form.date_reception" type="date" />
              </div>
            </div>
            <div class="form-group-custom mt-2">
              <label>Dépôt de Réception (Entrepôt) *</label>
              <select v-model="form.entrepot_id" class="accent-select">
                <option v-for="e in warehouses" :key="e.id" :value="e.id">
                  {{ e.nom }} {{ e.is_default ? '(Par défaut)' : '' }}
                </option>
              </select>
            </div>
          </div>
        </section>

        <!-- Lignes -->
        <section class="info-card">
          <div class="card-header table-header-actions">
            <div class="card-header-icon br-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.37 2.63a2.12 2.12 0 1 1 3 3L12 15l-4 1 1-4Z"/></svg></div>
            <h3>Articles Réceptionnés</h3>
            <button v-if="isNew" class="btn-add-line" @click="addLine">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Ajouter
            </button>
          </div>
          <div class="card-body p-0">
            <div class="table-container-custom">
              <table class="saas-table">
                <thead>
                  <tr>
                    <th style="width: 35%">Article</th>
                    <th style="width: 15%" class="text-center">Qté Commandée</th>
                    <th style="width: 15%" class="text-center">Qté Reçue</th>
                    <th style="width: 15%" class="text-right">P.U HT</th>
                    <th style="width: 15%" class="text-right">Total HT</th>
                    <th v-if="isNew" style="width: 5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(ligne, idx) in form.lignes" :key="idx">
                    <td>
                      <template v-if="isNew">
                        <select v-model="ligne.produit_id" @change="onProduitSelect(ligne)" class="select-inline-table">
                          <option value="">-- Texte libre --</option>
                          <option v-for="p in produits" :key="p.id" :value="p.id">[{{ p.reference }}] {{ p.designation }}</option>
                        </select>
                        <textarea v-model="ligne.designation" class="input-inline-sub" placeholder="Description..."></textarea>
                      </template>
                      <template v-else>
                        <div class="font-bold" style="color: var(--c-accent);">{{ ligne.produit_reference ? `[${ligne.produit_reference}]` : '' }} {{ ligne.designation }}</div>
                      </template>
                    </td>
                    <td class="text-center mono">{{ ligne.quantite_commandee }}</td>
                    <td>
                      <input v-if="isNew" v-model="ligne.quantite_recue" type="number" step="0.01" class="input-inline-table text-center" />
                      <span v-else class="text-center mono font-bold" style="display:block;">{{ ligne.quantite_recue }}</span>
                    </td>
                    <td>
                      <input v-if="isNew" v-model="ligne.prix_unitaire" type="number" step="0.01" class="input-inline-table text-right mono" />
                      <span v-else class="text-right mono" style="display:block;">{{ formatMoney(ligne.prix_unitaire) }}</span>
                    </td>
                    <td class="text-right font-bold mono">{{ formatMoney((ligne.quantite_recue || 0) * (ligne.prix_unitaire || 0)) }}</td>
                    <td v-if="isNew" class="text-center">
                      <button @click="removeLine(idx)" class="btn-row-delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></button>
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
            <h3>Observations</h3>
          </div>
          <div class="card-body">
            <textarea v-model="form.observations" rows="5" class="textarea-custom" placeholder="Notes sur la réception..."></textarea>
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
const hasFacture = computed(() => !!brData.value.facture_achat_id)

const brData = ref({})
const form = ref({
  fournisseur_id: '',
  date_reception: new Date().toISOString().substring(0, 10),
  observations: '',
  entrepot_id: null,
  lignes: []
})

const fournisseurs = ref([])
const produits = ref([])
const warehouses = ref([])

const totalHT = computed(() => {
  return form.value.lignes.reduce((sum, l) => sum + (parseFloat(l.quantite_recue) || 0) * (parseFloat(l.prix_unitaire) || 0), 0)
})

function formatMoney(v) { return parseFloat(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }

function addLine() {
  form.value.lignes.push({ produit_id: '', designation: '', quantite_commandee: 0, quantite_recue: 1, prix_unitaire: 0 })
}
function removeLine(idx) { form.value.lignes.splice(idx, 1) }

function onProduitSelect(ligne) {
  const p = produits.value.find(x => x.id === ligne.produit_id)
  if (p) {
    ligne.designation = p.designation
    ligne.prix_unitaire = p.prix_ht_achat || 0
  }
}



async function save() {
  if (!form.value.fournisseur_id) { toast.error('Sélectionnez un fournisseur'); return }
  if (form.value.lignes.length === 0) { toast.error('Ajoutez au moins une ligne'); return }
  
  const payload = {
    ...form.value,
    lignes: form.value.lignes.map(l => ({
      ...l,
      produit_id: l.produit_id || null
    }))
  }

  saving.value = true
  try {
    const { data } = await api.post('/bons-reception', payload)
    toast.success('Bon de réception créé !')
    setTimeout(() => router.push('/bons-reception'), 1000)
  } catch (e) {
    toast.error('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { saving.value = false }
}

async function transformToFactureAchat() {
  showConfirm.value = true
}

async function executeTransform() {
  showConfirm.value = false
  saving.value = true
  try {
    const { data } = await api.post(`/workflow/br-to-facture-achat/${route.params.id}`)
    toast.success('Facture d\'achat générée : ' + data.numero)
    setTimeout(() => router.push(`/factures-achats/${data.id}`), 1000)
  } catch (e) {
    toast.error('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { saving.value = false }
}

onMounted(async () => {
  loading.value = true
  try {
    const [fRes, pRes, wRes] = await Promise.all([
      api.get('/fournisseurs', { params: { per_page: 500 } }),
      api.get('/produits', { params: { per_page: 500 } }),
      api.get('/stock/entrepots')
    ])
    fournisseurs.value = fRes.data.data || fRes.data || []
    produits.value = (pRes.data.data || pRes.data || []).filter(p => p.is_actif !== false)
    warehouses.value = wRes.data || []

    // Set default warehouse
    if (!form.value.entrepot_id) {
      const def = warehouses.value.find(w => w.is_default)
      if (def) form.value.entrepot_id = def.id
    }

    if (!isNew.value) {
      const { data } = await api.get(`/bons-reception/${route.params.id}`)
      const raw = data.data || data
      brData.value = raw
      form.value.lignes = raw.lignes || []
      form.value.observations = raw.observations || ''
    } else {
      addLine()
    }
  } catch (e) {
    console.error(e)
    toast.error('Erreur de chargement')
  } finally { loading.value = false }
})
</script>

<style scoped>
.br-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #059669;
  --c-accent-bg: #ECFDF5;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }
.topbar-actions { display: flex; gap: 10px; }
.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(5,150,105,.2); }
.btn-secondary-custom { background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; }
.accent-text { color: var(--c-accent); border-color: var(--c-accent-bg); }

.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.br-theme { background: linear-gradient(135deg, #059669, #10B981); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
.hero-meta { flex: 1; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; }
.hero-status-badge.valide { background: #ECFDF5; color: #059669; }
.hero-status-badge.brouillon { background: #FEF9C3; color: #A16207; }

.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.kpi-item.br-accent .kpi-icon { background: #ECFDF5; color: var(--c-accent); }
.kpi-item.neutral .kpi-icon { background: #F1F5F9; color: #475569; }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

.content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #ECFDF5; color: var(--c-accent); display: flex; align-items: center; justify-content: center; }
.table-header-actions { justify-content: space-between; padding-right: 12px; }
.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 18px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.form-group-custom label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-row-custom { display: flex; gap: 16px; }
input, select, textarea { padding: 10px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .9rem; background: #fff; }
.textarea-custom { width: 100%; border: none; font-size: .85rem; line-height: 1.5; outline: none; resize: vertical; }

.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th { background: #F9FAFB; padding: 12px 16px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 8px 16px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
.select-inline-table { width: 100%; border: 1px solid #E2E8F0; border-radius: 6px; font-weight: 700; color: var(--c-accent); padding: 8px 10px; background: #fff; margin-bottom: 6px; }
.input-inline-sub { width: 100%; border: 1px solid #E2E8F0; border-radius: 6px; font-size: .85rem; color: var(--c-text); padding: 10px; min-height: 50px; font-family: inherit; resize: vertical; display: block; }
.input-inline-table { width: 100%; border: 1.5px solid #D5D9E2; border-radius: 8px; padding: 10px; background: #fff; }
.btn-row-delete { background: none; border: none; color: #94A3B8; cursor: pointer; padding: 8px; border-radius: 6px; }
.btn-row-delete:hover { background: #FEE2E2; color: #DC2626; }
.btn-add-line { background: var(--c-accent-bg); color: var(--c-accent); border: none; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: .75rem; cursor: pointer; display: flex; align-items: center; gap: 4px; }

.mono { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.mb-4 { margin-bottom: 16px; }
.p-0 { padding: 0; }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.toast-notification { position: fixed; top: 1rem; right: 1rem; padding: .85rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.toast-notification.success { background: #10b981; color: #fff; }
.toast-notification.error { background: #ef4444; color: #fff; }
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
