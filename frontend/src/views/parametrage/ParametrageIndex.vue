<template>
  <div class="parametrage-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Enregistrement des paramètres...</p>
      </div>
    </Transition>

    <div v-if="toastState.show" class="toast-notification" :class="toastState.type">{{ toastState.message }}</div>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Administration</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Configuration Entreprise</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button v-if="activeTab === 'general'" class="btn-save settings-theme-btn" @click="saveEntreprise" :disabled="loading">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer les modifications</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar settings-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Configuration Système</div>
        <h1 class="hero-name">Paramétrage de l'Entreprise</h1>
        <p class="hero-sub">Personnalisez les réglages globaux, l'identité visuelle et les informations légales de votre structure.</p>
      </div>
    </div>

    <div class="tabs-container">
      <button class="tab-btn" :class="{ active: activeTab === 'general' }" @click="activeTab = 'general'">Informations Générales</button>
      <button class="tab-btn" :class="{ active: activeTab === 'tva' }" @click="activeTab = 'tva'">Taxes (TVA) & Devises</button>
      <button class="tab-btn" :class="{ active: activeTab === 'stock' }" @click="activeTab = 'stock'">Entrepôts & Stock</button>
      <button class="tab-btn" :class="{ active: activeTab === 'reglements' }" @click="activeTab = 'reglements'">Règlements</button>
      <button class="tab-btn" :class="{ active: activeTab === 'produits' }" @click="activeTab = 'produits'">Familles de Produits</button>
      <button class="tab-btn" :class="{ active: activeTab === 'etats' }" @click="activeTab = 'etats'">États des Documents</button>
    </div>

    <div v-if="activeTab === 'general'" class="content-grid-single">
      
      <section class="info-card mb-4">
        <div class="card-header">
          <div class="card-header-icon settings-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <h3>Identité de la Société</h3>
        </div>
        <div class="card-body edit-form">
          <div class="form-row-custom">
            <div class="form-group-custom" style="flex: 2;">
              <label>Raison Sociale / Nom du Commerce *</label>
              <input v-model="form.raison_sociale" type="text" required />
            </div>
            <div class="form-group-custom" style="flex: 1;">
              <label>Forme Juridique</label>
              <input v-model="form.forme_juridique" type="text" placeholder="Ex: SARL, Auto-entrepreneur..." />
            </div>
          </div>
          
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>Logo de l'entreprise</label>
              <div class="logo-upload-wrapper">
                <div class="logo-preview">
                  <img v-if="form.logo_path" :src="form.logo_path" alt="Logo" />
                  <span v-else>Aucun Logo</span>
                </div>
                <div class="logo-input-area">
                  <input type="file" @change="handleLogoUpload" accept="image/*" class="file-input-custom" />
                  <p class="help-text">Formats supportés : JPG, PNG, SVG. Poids maximum : 2 Mo.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="info-card mb-4">
        <div class="card-header">
          <div class="card-header-icon settings-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <h3>Coordonnées de Contact</h3>
        </div>
        <div class="card-body edit-form">
          <div class="form-row-custom">
            <div class="form-group-custom" style="flex: 2;">
              <label>Adresse Complète *</label>
              <input v-model="form.adresse" type="text" required placeholder="N°, Rue, Quartier..." />
            </div>
            <div class="form-group-custom" style="flex: 1;">
              <label>Ville *</label>
              <input v-model="form.ville" type="text" required />
            </div>
          </div>
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>Téléphone</label>
              <input v-model="form.telephone" type="text" placeholder="+212 6..." />
            </div>
            <div class="form-group-custom">
              <label>Email de Contact</label>
              <input type="email" v-model="form.email" placeholder="contact@..." />
            </div>
            <div class="form-group-custom">
              <label>Site Web</label>
              <input v-model="form.site_web" type="url" placeholder="https://..." />
            </div>
          </div>
        </div>
      </section>

      <section class="info-card">
        <div class="card-header">
          <div class="card-header-icon settings-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
          <h3>Immatriculations Légales</h3>
        </div>
        <div class="card-body edit-form">
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>I.C.E (Identifiant Commun de l'Entreprise)</label>
              <input v-model="form.ice" type="text" />
            </div>
            <div class="form-group-custom">
              <label>R.C (Registre de Commerce)</label>
              <input v-model="form.rc" type="text" />
            </div>
            <div class="form-group-custom">
              <label>I.F (Identifiant Fiscal)</label>
              <input v-model="form.if_fiscal" type="text" />
            </div>
          </div>
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>Patente</label>
              <input v-model="form.patente" type="text" />
            </div>
            <div class="form-group-custom">
              <label>Numéro d'affiliation CNSS</label>
              <input v-model="form.cnss" type="text" />
            </div>
            <div class="form-group-custom" style="visibility: hidden;">
              </div>
          </div>
        </div>
      </section>

    </div>

    <div v-if="activeTab === 'tva'" class="content-grid-single">
      <ParamTaxesDevises />
    </div>
    <div v-if="activeTab === 'stock'" class="content-grid-single">
      <ParamEntrepots />
    </div>
    <div v-if="activeTab === 'reglements'" class="content-grid-single">
      <ParamReglements />
    </div>
    <div v-if="activeTab === 'produits'" class="content-grid-single">
      <ParamFamilles />
    </div>
    <div v-if="activeTab === 'etats'" class="content-grid-single">
      <ParamEtats />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

import ParamTaxesDevises from './ParamTaxesDevises.vue'
import ParamEntrepots from './ParamEntrepots.vue'
import ParamReglements from './ParamReglements.vue'
import ParamFamilles from './ParamFamilles.vue'
import ParamEtats from './ParamEtats.vue'

const authStore = useAuthStore()

const activeTab = ref('general')
const loading = ref(false)

const toastState = reactive({ show: false, message: '', type: 'success' })
function showToast(m, t = 'success') {
  toastState.show = true; toastState.message = m; toastState.type = t
  setTimeout(() => { toastState.show = false }, 4000)
}

const form = ref({
  id: null,
  raison_sociale: '',
  forme_juridique: '',
  logo_path: '',
  adresse: '',
  ville: '',
  telephone: '',
  email: '',
  site_web: '',
  ice: '',
  rc: '',
  if_fiscal: '',
  patente: '',
  cnss: ''
})

onMounted(async () => {
  await loadEntreprise()
})

function handleLogoUpload(event) {
  const file = event.target.files[0]
  if (!file) return

  // Vérifier la taille (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    showToast('Le fichier est trop volumineux (maximum 2 Mo).', 'error')
    return
  }

  const reader = new FileReader()
  reader.onload = (e) => {
    form.value.logo_path = e.target.result
  }
  reader.readAsDataURL(file)
}

async function loadEntreprise() {
  try {
    const { data } = await api.get('/parametrage/entreprise')
    if (data) {
      form.value = { ...data }
    }
  } catch (err) {
    console.error('Erreur chargement de l\'entreprise:', err)
  }
}

async function saveEntreprise() {
  loading.value = true
  try {
    const { data } = await api.put('/parametrage/entreprise', form.value)
    showToast(data.message || 'Configuration sauvegardée avec succès !')
    
    if (authStore.entreprise) {
      authStore.setEntrepriseInfo(data.entreprise)
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Erreur lors de la sauvegarde.', 'error')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ─── Design Tokens (Thème Indigo/Paramètres) ─── */
.parametrage-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; 
  --c-accent: #4F46E5; /* Indigo professionnel */
  --c-accent-bg: #EEF2FF;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; font-weight: 500; }
.breadcrumb-parent { color: var(--c-muted); }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; gap: 10px; }
.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); transition: transform 0.2s; }
.btn-save:hover:not(:disabled) { transform: translateY(-1px); }

/* ─── Hero Header ─── */
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.settings-theme { background: linear-gradient(135deg, #4F46E5, #6366F1); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.hero-meta { flex: 1; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 2px 0 0; }

/* ─── Tabs Modernes ─── */
.tabs-container { display: flex; gap: 8px; border-bottom: 2px solid var(--c-border); margin-bottom: 24px; overflow-x: auto; padding-bottom: 2px; }
.tab-btn { background: transparent; border: none; padding: 12px 18px; font-size: .85rem; font-weight: 600; color: var(--c-muted); cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.2s; white-space: nowrap; }
.tab-btn:hover { color: var(--c-text); }
.tab-btn.active { color: var(--c-accent); border-bottom-color: var(--c-accent); }

/* ─── Grille et Cartes ─── */
.content-grid-single { display: flex; flex-direction: column; gap: 20px; max-width: 1000px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: var(--c-accent-bg); color: var(--c-accent); display: flex; align-items: center; justify-content: center; }

/* ─── Formulaires ─── */
.edit-form { padding: 24px; display: flex; flex-direction: column; gap: 20px; }
.form-row-custom { display: flex; gap: 20px; flex-wrap: wrap; }
.form-group-custom { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 250px; }
.form-group-custom label { font-size: .72rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
input, select, textarea { padding: 12px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .9rem; background: #fff; font-family: inherit; transition: border-color 0.2s; outline: none; width: 100%; box-sizing: border-box; }
input:focus, select:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px var(--c-accent-bg); }

/* ─── Upload Logo ─── */
.logo-upload-wrapper { display: flex; gap: 20px; align-items: center; }
.logo-preview { width: 80px; height: 80px; border-radius: 12px; border: 1.5px dashed var(--c-border); display: flex; align-items: center; justify-content: center; background: #F9FAFB; overflow: hidden; font-size: 0.7rem; color: var(--c-muted); text-align: center; }
.logo-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
.logo-input-area { flex: 1; display: flex; flex-direction: column; gap: 6px; }
.file-input-custom { padding: 8px; font-size: 0.85rem; border: 1.5px dashed #D5D9E2; background: #FAFAFA; cursor: pointer; }
.help-text { margin: 0; font-size: 0.75rem; color: var(--c-muted); }

/* ─── Utilitaires & Toast ─── */
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; margin-bottom: 12px; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
.loading-label { font-weight: 600; color: var(--c-text); font-size: 0.9rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.toast-notification { position: fixed; top: 1.5rem; right: 1.5rem; padding: 1rem 1.5rem; border-radius: 10px; z-index: 9999; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-weight: 600; font-size: 0.9rem; }
.toast-notification.success { background: #10B981; color: #fff; }
.toast-notification.error { background: #EF4444; color: #fff; }

.mb-4 { margin-bottom: 1rem; }
</style>