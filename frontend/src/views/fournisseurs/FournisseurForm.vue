<template>
  <div class="supplier-form-view">
    <Transition name="fade">
      <div v-if="saving" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Enregistrement en cours…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/fournisseurs" class="back-btn" title="Retour">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-parent">Fournisseurs</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ props.id ? 'Édition' : 'Nouveau' }}</span>
        </div>
      </div>

      <div class="topbar-actions">
        <div class="status-toggle-container">
          <span class="status-label">Statut :</span>
          <button @click="form.is_active = !form.is_active" 
                  class="status-pill-toggle" 
                  :class="form.is_active ? 'active' : 'inactive'">
            <span class="dot"></span>
            {{ form.is_active ? 'PARTENAIRE ACTIF' : 'PARTENAIRE INACTIF' }}
          </button>
        </div>

        <button class="btn-save purchase-theme-btn" @click="saveFournisseur" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar supplier-theme">
        <span>{{ avatarInitials }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Fiche Partenaire Fournisseur</div>
        <h1 class="hero-name">{{ props.id ? (form.societe || form.nom) : 'Nouveau Partenaire' }}</h1>
        <p class="hero-sub">Coordonnées, conditions commerciales et paramètres de facturation.</p>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card mb-4">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Identité du Partenaire</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Nature Juridique</label>
              <div class="nature-selector-premium">
                <button @click="form.is_personne_physique = false" :class="{ active: !form.is_personne_physique }">Entreprise / Société</button>
                <button @click="form.is_personne_physique = true" :class="{ active: form.is_personne_physique }">Indépendant / Physique</button>
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom flex-1">
                <label>Code Interne</label>
                <div class="input-with-action">
                  <input v-model="form.code_fournisseur" type="text" class="mono font-bold" placeholder="AUTO-GÉNÉRÉ" />
                  <button class="action-trigger" @click="suggestCode" title="Générer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4.5 16.5c1.5 1.26 3 1.5 4.5 1.5s3-.24 4.5-1.5M19.5 7.5c-1.5-1.26-3-1.5-4.5-1.5s-3 .24-4.5 1.5M12 3v18"/></svg>
                  </button>
                </div>
              </div>
              <div class="form-group-custom flex-1">
                <label>Catégorie</label>
                <select v-model="form.type_fournisseur_id" class="form-select-custom">
                  <option v-for="t in typesFournisseur" :key="t.id" :value="t.id">{{ t.libelle }}</option>
                </select>
              </div>
            </div>

            <div v-if="!form.is_personne_physique" class="form-group-custom mt-2">
              <label>Raison Sociale *</label>
              <input v-model="form.societe" type="text" class="input-lg" placeholder="Nom de l'entreprise..." />
            </div>
            <div v-else class="form-row-custom mt-2">
              <div class="form-group-custom flex-1"><label>Nom *</label><input v-model="form.nom" type="text" /></div>
              <div class="form-group-custom flex-1"><label>Prénom</label><input v-model="form.prenom" type="text" /></div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72"/></svg></div>
            <h3>Contact & Localisation</h3>
          </div>
          <div class="card-body edit-form">
             <div class="form-row-custom">
                <div class="form-group-custom flex-1"><label>E-mail Professionnel</label><input v-model="form.email" type="email" placeholder="contact@fournisseur.ma" /></div>
                <div class="form-group-custom flex-1"><label>Téléphone / GSM</label><input v-model="form.mobile" type="tel" /></div>
             </div>
             <div class="form-group-custom mt-2">
                <label>Adresse du siège</label>
                <textarea v-model="form.adresse" rows="2" class="textarea-custom" placeholder="Rue, Quartier..."></textarea>
             </div>
             <div class="form-row-custom mt-2">
                <div class="form-group-custom flex-1"><label>Ville</label><input v-model="form.ville" type="text" /></div>
                <div class="form-group-custom flex-1"><label>Pays</label><input v-model="form.pays" type="text" /></div>
             </div>
          </div>
        </section>
      </div>

      <div class="col-side">
        <section class="info-card side-card">
          <div class="card-header">
            <div class="card-header-icon bank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
            <h3>Banque & Fiscalité</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom"><label>ICE (Identifiant Commun)</label><input v-model="form.ice" type="text" class="mono font-bold accent" /></div>
            <div class="form-group-custom"><label>Banque</label><input v-model="form.banque" type="text" placeholder="Ex: Attijariwafa..." /></div>
            <div class="form-group-custom"><label>RIB / Compte</label><input v-model="form.rib" type="text" class="mono" maxlength="24" placeholder="24 chiffres..." /></div>
            <div class="total-separator-line my-2"></div>
            <div class="form-group-custom">
              <label>Délai Livraison Moyen</label>
              <div class="input-with-unit">
                <input v-model="form.delai_livraison" type="number" />
                <span class="unit">Jours</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const props = defineProps({ id: [String, Number] })
const router = useRouter()
const saving = ref(false)

const form = ref({
  societe: '', is_personne_physique: false, nom: '', prenom: '',
  type_fournisseur_id: '', code_fournisseur: '', ice: '',
  email: '', mobile: '', adresse: '', ville: '', pays: 'Maroc',
  delai_livraison: 0, banque: '', rib: '', is_active: true
})

const avatarInitials = computed(() => {
  const name = form.value.societe || form.value.nom || 'FR'
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
})

const typesFournisseur = ref([])
const memoSociete = ref('')

function suggestCode() {
  const prefix = form.value.is_personne_physique ? 'F-IND-' : 'F-SO-'
  const random = Math.floor(Math.random() * 9000) + 1000
  form.value.code_fournisseur = prefix + random
}

// 1. Gère la bascule intelligente (Mémoire de la Raison Sociale)
watch(() => form.value.is_personne_physique, (isPhysique) => {
  if (isPhysique) {
    memoSociete.value = form.value.societe || ''
    const p = form.value.prenom || ''
    const n = form.value.nom || ''
    form.value.societe = `${p} ${n}`.trim()
  } else {
    form.value.societe = memoSociete.value
  }
})

// 2. Gère la saisie en direct si on est en "Personne Physique"
watch([() => form.value.nom, () => form.value.prenom], () => {
  if (form.value.is_personne_physique) {
    const p = form.value.prenom || ''
    const n = form.value.nom || ''
    form.value.societe = `${p} ${n}`.trim()
  }
})

watch(() => form.value.societe, (val) => {
  if (val && !form.value.code_fournisseur && !props.id) { suggestCode() }
})

async function saveFournisseur() {
  saving.value = true
  try {
    if (props.id) await api.put(`/fournisseurs/${props.id}`, form.value)
    else await api.post('/fournisseurs', form.value)
    router.push('/fournisseurs')
  } catch (error) { console.error(error) } finally { saving.value = false }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/parametrage/referentiels/types-fournisseur')
    typesFournisseur.value = data || []
  } catch (e) { console.error(e) }
  
  if (props.id) {
    const { data } = await api.get(`/fournisseurs/${props.id}`)
    form.value = { ...form.value, ...(data.data || data) }
    memoSociete.value = form.value.societe // On sauvegarde en mémoire dès l'ouverture
  }
})
</script>

<style scoped>
.supplier-form-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #4F46E5; /* Indigo */
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

/* ─── Topbar & Actions ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; align-items: center; gap: 16px; }

/* ─── STATUT TOGGLE (Premium Style) ─── */
.status-toggle-container { display: flex; align-items: center; gap: 10px; }
.status-label { font-size: 0.68rem; font-weight: 800; color: var(--c-muted); text-transform: uppercase; }
.status-pill-toggle {
  display: flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 100px;
  font-size: 0.72rem; font-weight: 800; border: 1.5px solid transparent; cursor: pointer; transition: all 0.2s;
}
.status-pill-toggle .dot { width: 6px; height: 6px; border-radius: 50%; }
.status-pill-toggle.active { background: #DCFCE7; color: #166534; border-color: #BBF7D0; }
.status-pill-toggle.active .dot { background: #166534; }
.status-pill-toggle.inactive { background: #FEE2E2; color: #991B1B; border-color: #FECACA; }
.status-pill-toggle.inactive .dot { background: #991B1B; }

.btn-save { background: var(--c-accent); color: #fff; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; gap: 8px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }

/* ─── Hero ─── */
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.supplier-theme { background: linear-gradient(135deg, #4F46E5, #3730A3); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 2px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }

/* ─── Grid & Cards ─── */
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.card-header h3 { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #EEF2FF; color: var(--c-accent); display: flex; align-items: center; justify-content: center; }

.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.form-group-custom { display: flex; flex-direction: column; gap: 5px; }
.form-group-custom label { font-size: .68rem; font-weight: 800; color: var(--c-muted); text-transform: uppercase; letter-spacing: 0.02em; }
.form-group-custom input, .form-group-custom select, .form-group-custom textarea { padding: 10px 12px; border: 1.5px solid #D5D9E2; border-radius: 10px; font-size: .9rem; outline: none; background: #FDFDFF; transition: border-color 0.2s; }
.form-group-custom input:focus { border-color: var(--c-accent); }
.input-lg { font-size: 1.1rem; font-weight: 800; border-color: var(--c-accent) !important; }

.form-row-custom { display: flex; gap: 16px; }
.nature-selector-premium { display: flex; background: #F3F4F6; padding: 4px; border-radius: 12px; border: 1.5px solid #E5E7EB; }
.nature-selector-premium button { flex: 1; padding: 8px; border: none; border-radius: 8px; font-weight: 700; font-size: .75rem; cursor: pointer; background: transparent; color: var(--c-muted); }
.nature-selector-premium button.active { background: #fff; color: var(--c-accent); box-shadow: 0 2px 6px rgba(0,0,0,0.06); }

.input-with-action { display: flex; gap: 8px; }
.action-trigger { width: 42px; border: 1.5px solid #D5D9E2; border-radius: 10px; background: #fff; cursor: pointer; color: var(--c-muted); display: flex; align-items: center; justify-content: center; }
.action-trigger:hover { border-color: var(--c-accent); color: var(--c-accent); }

.input-with-unit { position: relative; display: flex; align-items: center; }
.input-with-unit input { width: 100%; padding-right: 60px; }
.input-with-unit .unit { position: absolute; right: 14px; font-size: .65rem; font-weight: 800; color: var(--c-muted); text-transform: uppercase; }

.total-separator-line { height: 1px; background: #F1F5F9; }
.mono { font-family: 'JetBrains Mono', monospace; }
.accent { color: var(--c-accent); }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>