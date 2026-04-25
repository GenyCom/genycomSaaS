<template>
  <div class="facture-achat-detail-view">
    <Transition name="fade">
      <div v-if="loading || saving" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p>{{ saving ? 'Validation en cours...' : 'Chargement...' }}</p>
      </div>
    </Transition>

    <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/factures-achats" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ facture.numero || 'Facture' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-secondary-custom" @click="imprimer">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          <span>Imprimer</span>
        </button>

        <button v-if="facture.statut === 'brouillon'" class="btn-save fa-theme-btn" @click="save" :disabled="saving">
           <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
           <span>Valider la facture</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar fa-theme"><span>FA</span></div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Facture Fournisseur</div>
        <h1 class="hero-name">{{ facture.numero }}</h1>
        <p class="hero-sub" v-if="facture.fournisseur">Émetteur : <strong>{{ facture.fournisseur.societe }}</strong></p>
      </div>
      <div class="hero-status-badge" :class="statutClass">{{ (facture.statut || 'Brouillon').toUpperCase() }}</div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Montant Total TTC</p>
          <p class="kpi-value">{{ formatMoney(facture.montant_ttc) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item" :class="facture.reste_a_payer <= 0 ? 'success-item' : 'danger-item'">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Reste à Payer</p>
          <p class="kpi-value" :style="{ color: facture.reste_a_payer <= 0 ? '#059669' : '#DC2626' }">
            {{ formatMoney(facture.reste_a_payer) }} <span>DH</span>
          </p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon fa-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <h3>Informations Générales</h3>
          </div>
          <div class="card-body p-0">
            <div class="info-grid-simple">
              <div class="info-group">
                <span class="label">Date Émission</span>
                <span class="value">{{ formatDate(facture.date_facture) }}</span>
              </div>
              <div class="info-group">
                <span class="label">Date Échéance</span>
                <span class="value font-bold" :class="{'text-danger': isEnRetard}">{{ formatDate(facture.date_echeance) }}</span>
              </div>
              <div class="info-group">
                <span class="label">Réception(s) liée(s)</span>
                <div class="value">
                  <span v-for="br in facture.reception_notes" :key="br.id" class="source-tag">{{ br.numero }}</span>
                  <span v-if="!facture.reception_notes?.length">—</span>
                </div>
              </div>
              <div class="info-group">
                <span class="label">Établissement</span>
                <span class="value">{{ facture.fournisseur?.societe }}</span>
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon fa-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
            <h3>Détails des articles</h3>
          </div>
          <div class="card-body p-0">
            <table class="saas-table lines-table">
              <thead>
                <tr>
                  <th style="width: 50%">Désignation</th>
                  <th class="text-center">Qté</th>
                  <th class="text-right">P.U HT</th>
                  <th class="text-center">TVA</th>
                  <th class="text-right">Total HT</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="l in facture.lignes" :key="l.id">
                  <td><div class="line-name">{{ l.designation }}</div></td>
                  <td class="text-center font-semibold">{{ l.quantite }}</td>
                  <td class="text-right mono">{{ formatMoney(l.prix_unitaire) }}</td>
                  <td class="text-center font-bold">{{ l.taux_tva }}%</td>
                  <td class="text-right font-bold mono">{{ formatMoney(l.montant_ht) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <div class="col-side">
        <section class="info-card side-card mb-4">
          <div class="card-header">
            <div class="card-header-icon notes-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <h3>Observations</h3>
          </div>
          <div class="card-body p-4">
            <div class="observation-display-box">
               <p :class="facture.observations ? 'text-dark' : 'text-muted italic'">
                 {{ facture.observations || 'Aucune observation enregistrée.' }}
               </p>
            </div>
          </div>
        </section>

        <section class="premium-total-card totals-clean-white">
          <div class="total-inner">
            <div class="total-flex-row">
              <span class="total-label">Sous-total HT</span>
              <span class="total-value mono">{{ formatMoney(facture.montant_ht) }} <small>DH</small></span>
            </div>
            <div class="total-flex-row">
              <span class="total-label">Montant TVA</span>
              <span class="total-value mono">{{ formatMoney(facture.montant_tva) }} <small>DH</small></span>
            </div>
            <div class="total-separator-line"></div>
            <div class="total-flex-row">
              <span class="total-label" style="color: var(--c-text);">TOTAL TTC</span>
              <span class="total-value mono">{{ formatMoney(facture.montant_ttc) }} <small>DH</small></span>
            </div>
            <div class="total-flex-row" style="color: #059669; margin-top: 4px;">
              <span class="total-label" style="color: inherit;">Déjà Payé</span>
              <span class="total-value mono" style="color: inherit;">- {{ formatMoney(facture.montant_paye) }} <small>DH</small></span>
            </div>

            <div class="reste-a-payer-block" :class="{'is-paid': facture.reste_a_payer <= 0}">
              <div class="rap-label">{{ facture.reste_a_payer <= 0 ? 'FACTURE SOLDÉE' : 'RESTE À PAYER' }}</div>
              <div class="rap-amount mono">
                {{ formatMoney(facture.reste_a_payer) }} <small>DH</small>
              </div>
            </div>

          </div>
          <div class="total-footer-bar">Facture d'Achat Réglementaire</div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const saving = ref(false)
const facture = ref({})

const isEnRetard = computed(() => {
  if (!facture.value.date_echeance) return false
  return new Date(facture.value.date_echeance) < new Date() && parseFloat(facture.value.reste_a_payer) > 0
})

const statutClass = computed(() => {
  const s = facture.value.statut || 'brouillon'
  if (s === 'paye') return 'soldee'
  if (s === 'valide') return 'valide'
  return 'brouillon'
})

// Remplacement des virgules par des espaces pour les milliers (ex: 1 000 233,00)
function formatMoney(v) { 
  return parseFloat(v || 0)
    .toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    .replace(/\s/g, ' '); 
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const toast = reactive({ show: false, message: '', type: 'success' })
function showToast(m, t = 'success') {
  toast.show = true; toast.message = m; toast.type = t
  setTimeout(() => { toast.show = false }, 4000)
}

async function save() {
  if (facture.value.statut !== 'brouillon') return
  saving.value = true
  try {
    await api.put(`/factures-achats/${route.params.id}`, { statut: 'valide' })
    showToast('Facture validée !')
    loadData()
  } catch (e) {
    showToast('Erreur validation', 'error')
  } finally { saving.value = false }
}

// Fonction pour l'impression
function imprimer() {
  window.open(`/print/factures-achats/${route.params.id}`, '_blank')
}

async function loadData() {
  loading.value = true
  try {
    const { data } = await api.get(`/factures-achats/${route.params.id}`)
    facture.value = data
  } catch (e) {
    showToast('Erreur chargement', 'error')
  } finally { loading.value = false }
}

onMounted(loadData)
</script>

<style scoped>
/* ─── Design Base ─── */
.facture-achat-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #059669;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

/* ─── Structure & Hero ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; }
.topbar-actions { display: flex; gap: 10px; }
.btn-secondary-custom { background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; align-items: center; transition: all 0.2s; }
.btn-secondary-custom:hover { background: #F8FAFC; color: var(--c-text); border-color: #CBD5E1; }

.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.fa-theme { background: linear-gradient(135deg, #059669, #10B981); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; }
.hero-status-badge.soldee { background: #ECFDF5; color: #059669; }
.hero-status-badge.valide { background: #FEF9C3; color: #A16207; }

/* ─── KPI Strip ─── */
.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-value { font-size: 1.2rem; font-weight: 800; margin: 0; }
.danger-item .kpi-icon { background: #FEF2F2; color: #DC2626; }
.success-item .kpi-icon { background: #ECFDF5; color: #059669; }

/* ─── Content Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; }

/* ─── Info Groups ─── */
.info-grid-simple { display: flex; flex-direction: column; }
.info-group { padding: 14px 20px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; }
.info-group:last-child { border-bottom: none; }
.info-group .label { font-size: .72rem; color: var(--c-muted); font-weight: 600; text-transform: uppercase; }
.info-group .value { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.source-tag { font-size: .65rem; font-weight: 800; color: #3B82F6; background: #EBF5FF; padding: 3px 8px; border-radius: 4px; margin-left: 4px; }

.observation-display-box { min-height: 100px; background: #F9FAFB; border: 1px dashed #E2E8F0; border-radius: 12px; padding: 16px; }

/* ─── BLOC TOTAUX & RESTE À PAYER ─── */
.premium-total-card.totals-clean-white {
  background: #FFFFFF;
  border: 1px solid var(--c-border);
  border-radius: 16px;
  color: var(--c-text);
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.total-inner { padding: 24px; }
.total-flex-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.total-label { font-size: 0.75rem; color: var(--c-muted); font-weight: 600; text-transform: uppercase; }
.total-value { font-weight: 700; font-size: 1rem; color: var(--c-text); }
.total-value small { opacity: 0.5; font-size: 0.7rem; font-weight: 600; }
.total-separator-line { height: 1px; background: #F1F5F9; margin: 16px 0; }

.reste-a-payer-block {
  margin-top: 20px;
  background: #FEF2F2;
  border: 1.5px solid #FECACA;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  transition: all 0.3s ease;
}
.reste-a-payer-block.is-paid {
  background: #ECFDF5;
  border-color: #A7F3D0;
}
.rap-label {
  font-size: 0.72rem;
  font-weight: 800;
  color: #DC2626;
  letter-spacing: 0.05em;
  margin-bottom: 4px;
}
.is-paid .rap-label { color: #059669; }
.rap-amount {
  font-size: 2rem;
  font-weight: 900;
  color: #B91C1C;
  letter-spacing: -1px;
  line-height: 1;
}
.is-paid .rap-amount { color: #047857; }
.rap-amount small { font-size: 0.9rem; font-weight: 700; opacity: 0.8; }

.total-footer-bar { background: #F9FAFB; padding: 10px 24px; font-size: 0.65rem; text-align: right; color: var(--c-muted); font-weight: 700; text-transform: uppercase; border-top: 1px solid #F1F5F9; }

/* ─── Table & Utils ─── */
.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th { background: #F9FAFB; padding: 12px 16px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 12px 16px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
.mono { font-family: 'JetBrains Mono', monospace; }

.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, .2); }
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.toast-notification { position: fixed; top: 1rem; right: 1rem; padding: .85rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
</style>