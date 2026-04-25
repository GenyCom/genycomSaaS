<template>
  <div class="dette-detail-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p>Chargement...</p>
      </div>
    </Transition>

    <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/dettes" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Finances</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ dette.numero || 'Chargement...' }}</span>
        </div>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar dette-theme">
        <span>DT</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Dette Fournisseur</div>
        <h1 class="hero-name">{{ dette.numero }}</h1>
        <p class="hero-sub" v-if="dette.fournisseur_societe">Fournisseur : <strong>{{ dette.fournisseur_societe }}</strong></p>
      </div>
      <div class="hero-status-badge" :class="statutClass">{{ statutLabel }}</div>
    </div>

    <!-- KPI Strip -->
    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Montant Total TTC</p>
          <p class="kpi-value">{{ formatMoney(dette.montant_total) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item success-item">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Montant Réglé</p>
          <p class="kpi-value" style="color: #059669;">{{ formatMoney(dette.montant_regle) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item danger-item">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        <div class="kpi-body">
          <p class="kpi-label">Reste à Payer</p>
          <p class="kpi-value" style="color: #DC2626;">{{ formatMoney(dette.montant_restant) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <!-- Info Card -->
        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon dette-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <h3>Informations</h3>
          </div>
          <div class="card-body">
            <div class="info-grid">
              <div class="info-item"><span class="label">Date Dette</span><span class="value">{{ formatDate(dette.date_dette) }}</span></div>
              <div class="info-item"><span class="label">Échéance</span><span class="value">{{ formatDate(dette.date_echeance) }}</span></div>
              <div class="info-item"><span class="label">Montant HT</span><span class="value mono">{{ formatMoney(dette.montant_ht) }} DH</span></div>
              <div class="info-item"><span class="label">TVA</span><span class="value mono">{{ formatMoney(dette.montant_tva) }} DH</span></div>
              <div class="info-item"><span class="label">BR Source</span><span class="value" style="color: #059669; font-weight: 700;">{{ dette.br_numero || '—' }}</span></div>
              <div class="info-item"><span class="label">Fournisseur</span><span class="value">{{ dette.fournisseur_societe }}</span></div>
            </div>
          </div>
        </section>

        <!-- Historique Règlements -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon dette-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
            <h3>Historique des Règlements</h3>
          </div>
          <div class="card-body p-0">
            <table class="saas-table">
              <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Observations</th></tr></thead>
              <tbody>
                <tr v-for="r in dette.reglements" :key="r.id">
                  <td>{{ formatDate(r.date_reglement) }}</td>
                  <td class="font-bold mono" style="color: #059669;">{{ formatMoney(r.montant) }} DH</td>
                  <td>{{ r.mode_libelle || '—' }}</td>
                  <td class="text-muted">{{ r.observations || '—' }}</td>
                </tr>
                <tr v-if="!dette.reglements?.length"><td colspan="4" class="text-center text-muted">Aucun règlement.</td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <div class="col-side">
        <!-- Formulaire Règlement -->
        <section v-if="parseFloat(dette.montant_restant) > 0" class="info-card">
          <div class="card-header">
            <div class="card-header-icon" style="background: #FEF2F2; color: #DC2626;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
            <h3>Enregistrer un Règlement</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Montant *</label>
              <input v-model="reglement.montant" type="number" step="0.01" :max="dette.montant_restant" />
            </div>
            <div class="form-group-custom">
              <label>Date</label>
              <input v-model="reglement.date_reglement" type="date" />
            </div>
            <div class="form-group-custom">
              <label>Mode de Règlement</label>
              <select v-model="reglement.mode_reglement_id">
                <option value="">—</option>
                <option v-for="m in modes" :key="m.id" :value="m.id">{{ m.libelle }}</option>
              </select>
            </div>
            <div class="form-group-custom">
              <label>Observations</label>
              <textarea v-model="reglement.observations" rows="2" style="border: 1.5px solid #D5D9E2; border-radius: 8px; padding: 10px; font-size: .85rem;"></textarea>
            </div>
            <button class="btn-save dette-save-btn" @click="enregistrerReglement" :disabled="saving" style="width: 100%; justify-content: center;">
              {{ saving ? 'Patientez...' : 'Valider le Règlement' }}
            </button>
          </div>
        </section>

        <section v-else class="info-card">
          <div class="card-body" style="padding: 30px; text-align: center;">
            <div style="font-size: 2.5rem; margin-bottom: 8px;">✅</div>
            <p style="font-weight: 700; color: #059669; font-size: 1.1rem;">Dette Soldée</p>
            <p class="text-muted" style="font-size: .85rem;">Aucun solde restant.</p>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const loading = ref(true)
const saving = ref(false)

const dette = ref({})
const modes = ref([])
const reglement = ref({
  montant: '',
  date_reglement: new Date().toISOString().substring(0, 10),
  mode_reglement_id: '',
  observations: ''
})

const statutLabel = computed(() => {
  if (parseFloat(dette.value.montant_restant) <= 0) return 'SOLDÉE'
  if (parseFloat(dette.value.montant_regle) > 0) return 'PARTIELLE'
  return 'EN ATTENTE'
})
const statutClass = computed(() => {
  if (parseFloat(dette.value.montant_restant) <= 0) return 'soldee'
  if (parseFloat(dette.value.montant_regle) > 0) return 'partielle'
  return 'en-attente'
})

function formatMoney(v) { return parseFloat(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function formatDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }

const toast = reactive({ show: false, message: '', type: 'success' })
function showToast(m, t = 'success') {
  toast.show = true; toast.message = m; toast.type = t
  setTimeout(() => { toast.show = false }, 4000)
}

async function enregistrerReglement() {
  if (!reglement.value.montant || parseFloat(reglement.value.montant) <= 0) {
    showToast('Montant invalide', 'error'); return
  }
  saving.value = true
  try {
    await api.post(`/dettes/${route.params.id}/reglement`, reglement.value)
    showToast('Règlement enregistré !')
    // Recharger
    const { data } = await api.get(`/dettes/${route.params.id}`)
    dette.value = data.data || data
    reglement.value.montant = ''
    reglement.value.observations = ''
  } catch (e) {
    showToast('Erreur: ' + (e.response?.data?.message || e.message), 'error')
  } finally { saving.value = false }
}

onMounted(async () => {
  loading.value = true
  try {
    const [dRes, mRes] = await Promise.all([
      api.get(`/dettes/${route.params.id}`),
      api.get('/parametrage/referentiels/modes-reglement').catch(() => ({ data: { data: [] } }))
    ])
    dette.value = dRes.data.data || dRes.data
    modes.value = mRes.data.data || mRes.data || []
  } catch (e) {
    console.error(e)
    showToast('Erreur de chargement', 'error')
  } finally { loading.value = false }
})
</script>

<style scoped>
.dette-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #DC2626;
  --c-accent-bg: #FEF2F2;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.dette-theme { background: linear-gradient(135deg, #DC2626, #EF4444); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
.hero-meta { flex: 1; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }
.hero-status-badge { padding: 6px 14px; border-radius: 100px; font-size: .7rem; font-weight: 800; }
.hero-status-badge.soldee { background: #ECFDF5; color: #059669; }
.hero-status-badge.partielle { background: #FEF9C3; color: #A16207; }
.hero-status-badge.en-attente { background: #FEF2F2; color: #DC2626; }

.kpi-strip { display: flex; background: #fff; border: 1px solid var(--c-border); border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
.kpi-item { flex: 1; padding: 18px 22px; display: flex; align-items: center; gap: 14px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.kpi-item.neutral .kpi-icon { background: #F1F5F9; color: #475569; }
.kpi-item.success-item .kpi-icon { background: #ECFDF5; color: #059669; }
.kpi-item.danger-item .kpi-icon { background: #FEF2F2; color: #DC2626; }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; }
.dette-icon { background: #FEF2F2; color: #DC2626; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.info-item { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #F1F5F9; }
.info-item .label { font-size: .75rem; color: var(--c-muted); font-weight: 600; }
.info-item .value { font-size: .85rem; font-weight: 700; }

.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; }
.form-group-custom label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
input, select, textarea { padding: 10px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .9rem; background: #fff; }
.btn-save { background: #DC2626; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(220,38,38,.2); }

.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th { background: #F9FAFB; padding: 12px 16px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 10px 16px; border-bottom: 1px solid #F1F5F9; }

.mono { font-family: 'JetBrains Mono', monospace; font-size: .85rem; }
.font-bold { font-weight: 700; }
.text-muted { color: #6B7280; }
.text-center { text-align: center; }
.mb-4 { margin-bottom: 16px; }
.p-0 { padding: 0; }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: #DC2626; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.toast-notification { position: fixed; top: 1rem; right: 1rem; padding: .85rem 1.5rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.toast-notification.success { background: #10b981; color: #fff; }
.toast-notification.error { background: #ef4444; color: #fff; }
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
