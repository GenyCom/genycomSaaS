<template>
  <div class="client-form-view">
    <Transition name="fade">
      <div v-if="saving" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Enregistrement du client…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/clients" class="back-btn" title="Retour aux clients">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Clients</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ props.id ? 'Modifier le client' : 'Nouveau Client' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <div class="status-toggle-wrapper">
          <span class="status-label">Statut :</span>
          <button @click="form.is_active = !form.is_active" :class="form.is_active ? 'active' : 'inactive'">
            {{ form.is_active ? 'ACTIF' : 'INACTIF' }}
          </button>
        </div>
        <button class="btn-save" @click="saveClient" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          <span>{{ props.id ? 'Mettre à jour' : 'Créer le client' }}</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>{{ avatarInitials }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ form.is_personne_physique ? 'Particulier / Personne Physique' : 'Société / Personne Morale' }}
        </div>
        <h1 class="hero-name">{{ props.id ? (form.societe || form.nom) : 'Nouvelle Fiche Client' }}</h1>
        <p class="hero-sub">Configuration des paramètres d'identité, de fiscalité et de facturation.</p>
      </div>
    </div>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h3>Identité du Partenaire</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Nature Juridique</label>
              <div class="nature-selector">
                <button @click="form.is_personne_physique = false" :class="{ active: !form.is_personne_physique }">Entreprise / Société</button>
                <button @click="form.is_personne_physique = true" :class="{ active: form.is_personne_physique }">Indépendant / Physique</button>
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Code Client</label>
                <div class="input-with-action">
                  <input v-model="form.code_client" type="text" class="mono" placeholder="AUTO" />
                  <button class="action-trigger" @click="suggestCode" title="Générer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4.5 16.5c1.5 1.26 3 1.5 4.5 1.5s3-.24 4.5-1.5M19.5 7.5c-1.5-1.26-3-1.5-4.5-1.5s-3 .24-4.5 1.5M12 3v18"/></svg>
                  </button>
                </div>
              </div>
              <div class="form-group-custom">
                <label>Classification (Type)</label>
                <select v-model="form.type_client_id">
                  <option value="">Sélectionner...</option>
                  <option v-for="t in typesClient" :key="t.id" :value="t.id">{{ t.libelle }}</option>
                </select>
              </div>
            </div>

            <div v-if="!form.is_personne_physique" class="form-group-custom">
              <label>Raison Sociale *</label>
              <input v-model="form.societe" type="text" class="input-lg" :class="{ 'input-error': errors.societe }" placeholder="Nom de l'entreprise" />
              <span v-if="errors.societe" class="error-msg">{{ errors.societe }}</span>
            </div>

            <div v-else class="form-row-custom">
              <div class="form-group-custom" style="flex: 0 0 100px;">
                <label>Civilité</label>
                <select v-model="form.civilite">
                  <option value="M.">M.</option>
                  <option value="Mme">Mme</option>
                  <option value="Mlle">Mlle</option>
                </select>
              </div>
              <div class="form-group-custom">
                <label>Nom *</label>
                <input v-model="form.nom" type="text" :class="{ 'input-error': errors.nom }" />
              </div>
              <div class="form-group-custom">
                <label>Prénom</label>
                <input v-model="form.prenom" type="text" />
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <h3>Coordonnées & Localisation</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Email Principal</label>
                <input v-model="form.email" type="email" placeholder="contact@email.com" :class="{ 'input-error': errors.email }" />
              </div>
              <div class="form-group-custom">
                <label>Site Web</label>
                <input v-model="form.site_web" type="url" placeholder="https://..." />
              </div>
            </div>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Téléphone Fixe</label>
                <input v-model="form.telephone" type="tel" />
              </div>
              <div class="form-group-custom">
                <label>Téléphone Mobile</label>
                <input v-model="form.mobile" type="tel" />
              </div>
            </div>
            <div class="form-group-custom">
              <label>Adresse du Siège</label>
              <textarea v-model="form.adresse" rows="2"></textarea>
            </div>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Ville</label>
                <input v-model="form.ville" type="text" />
              </div>
              <div class="form-group-custom">
                <label>Code Postal</label>
                <input v-model="form.code_postal" type="text" />
              </div>
              <div class="form-group-custom">
                <label>Pays</label>
                <input v-model="form.pays" type="text" />
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="col-side">
        <section v-if="!form.is_personne_physique" class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h3>Fiscalité & Légal</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>ICE</label>
              <input v-model="form.ice" type="text" class="mono" />
            </div>
            <div class="form-group-custom">
              <label>Identifiant Fiscal (IF)</label>
              <input v-model="form.if_fiscal" type="text" class="mono" />
            </div>
            <div class="form-group-custom">
              <label>RC / Patente</label>
              <div class="form-row-custom">
                <input v-model="form.rc" type="text" placeholder="RC" />
                <input v-model="form.patente" type="text" placeholder="Patente" />
              </div>
            </div>
            <div class="field-separator"></div>
            <div class="checkbox-group">
              <input type="checkbox" v-model="form.exempt_tva" id="exempt" />
              <label for="exempt">Exonéré de TVA (Art. 92)</label>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>Conditions & Banque</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Délai de Paiement (Jours)</label>
              <input v-model="form.delai_paiement" type="number" />
            </div>
            <div class="form-group-custom">
              <label>Plafond Crédit (DH)</label>
              <input v-model="form.plafond_credit" type="number" class="money-input" />
            </div>
            <div class="form-group-custom">
              <label>Banque</label>
              <input v-model="form.banque" type="text" />
            </div>
            <div class="form-group-custom">
              <label>RIB (24 chiffres)</label>
              <input v-model="form.rib" type="text" class="mono" maxlength="24" />
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <h3>Observations</h3>
          </div>
          <div class="card-body">
            <textarea v-model="form.observations" rows="3" style="width: 100%; border: 1px solid var(--c-border-mid); border-radius: var(--radius-sm); padding: 10px;"></textarea>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const props = defineProps({ id: [String, Number] })
const router = useRouter()
const saving = ref(false)

const form = ref({
  societe: '',
  is_personne_physique: false,
  civilite: 'M.',
  nom: '',
  prenom: '',
  type_client_id: '',
  commercial_id: '',
  code_client: '',
  ice: '',
  rc: '',
  if_fiscal: '',
  patente: '',
  email: '',
  site_web: '',
  telephone: '',
  mobile: '',
  fax: '',
  adresse: '',
  ville: '',
  code_postal: '',
  pays: 'Maroc',
  delai_paiement: 0,
  plafond_credit: 0,
  banque: '',
  rib: '',
  exempt_tva: false,
  observations: '',
  is_active: true
})

// VARIABLE POUR MEMORISER LA RAISON SOCIALE
const memoSociete = ref('')

const avatarInitials = computed(() => {
  const name = form.value.societe || form.value.nom || 'CL'
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
})

// WATCHER : BASCULE INTELLIGENTE ENTRE ENTREPRISE ET PHYSIQUE
watch(() => form.value.is_personne_physique, (isPhysique) => {
  if (isPhysique) {
    // Mémorisation de la société actuelle et traitement anti "null null"
    memoSociete.value = form.value.societe || ''
    const p = form.value.prenom || ''
    const n = form.value.nom || ''
    form.value.societe = `${p} ${n}`.trim()
  } else {
    // Restauration de l'ancienne Raison Sociale
    form.value.societe = memoSociete.value
  }
})

// WATCHER : METTRE A JOUR LA SOCIETE SI ON TAPE LE NOM / PRENOM
watch([() => form.value.nom, () => form.value.prenom], () => {
  if (form.value.is_personne_physique) {
    const p = form.value.prenom || ''
    const n = form.value.nom || ''
    form.value.societe = `${p} ${n}`.trim()
  }
})

watch(() => form.value.societe, (val) => {
  if (val && !form.value.code_client && !props.id) {
    suggestCode()
  }
})

const typesClient = ref([])
const errors = reactive({})
const toast = reactive({ show: false, message: '', type: 'success' })

function showToast(message, type = 'success') {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 4000)
}

function suggestCode() {
  const prefix = form.value.is_personne_physique ? 'C-IND-' : 'C-SO-'
  const random = Math.floor(Math.random() * 9000) + 1000
  form.value.code_client = prefix + random
}

function validate() {
  Object.keys(errors).forEach(k => delete errors[k])
  if (form.value.is_personne_physique) {
    if (!form.value.nom?.trim()) errors.nom = 'Nom obligatoire'
  } else {
    if (!form.value.societe?.trim()) errors.societe = 'Raison sociale obligatoire'
  }
  return Object.keys(errors).length === 0
}

async function saveClient() {
  if (!validate()) {
    showToast('Corrigez les erreurs avant d\'enregistrer', 'error')
    return
  }
  saving.value = true
  try {
    const payload = { ...form.value }
    if (props.id) {
       await api.put(`/clients/${props.id}`, payload)
       showToast('Client mis à jour !')
    } else {
       await api.post('/clients', payload)
       showToast('Client créé !')
    }
    setTimeout(() => router.push('/clients'), 1000)
  } catch (error) {
    if (error.response?.status === 422 && error.response.data.errors) {
      Object.assign(errors, error.response.data.errors);
      showToast('Certaines données sont invalides. Veuillez vérifier le formulaire.', 'error');
      Object.keys(errors).forEach(k => {
        if (Array.isArray(errors[k])) errors[k] = errors[k][0];
      });
    } else {
      showToast(error.response?.data?.message || 'Erreur technique lors de la sauvegarde', 'error');
    }
  } finally {
    saving.value = false
  }
}

async function fetchClient(id) {
  try {
    const { data } = await api.get(`/clients/${id}`)
    form.value = { ...form.value, ...(data.data || data) }
    // Mémoriser la société au chargement pour qu'elle puisse être restaurée
    memoSociete.value = form.value.societe || ''
  } catch (e) { showToast('Impossible de charger le client', 'error') }
}

onMounted(async () => {
  try {
     const resTypes = await api.get('/parametrage/referentiels/types-client')
     typesClient.value = resTypes.data || []
  } catch (err) { console.error(err) }

  if (props.id) await fetchClient(props.id)
})
</script>

<style scoped>
/* ─── Design Tokens ─────────────────────────────────────────────────────────── */
.client-form-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E5E7EB; /* Bordure douce */
  --c-border-mid: #D1D5DB;
  --c-text:       #111827;
  --c-muted:      #6B7280;
  --c-subtle:     #FFFFFF; /* Header blanc comme demandé */
  --c-accent:     #4F46E5; /* Indigo Premium */
  --c-accent-bg:  #EEF2FF; /* Fond de l'icône */
  --c-danger:     #DC2626;
  --c-success:    #16A34A;
  --radius-lg:    12px;
  --radius-md:    8px;
  --radius-sm:    6px;
  --shadow-sm:    0 1px 2px rgba(0,0,0,.05);

  
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: var(--c-text);
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn {
  display: flex; align-items: center; justify-content: center; width: 34px; height: 34px;
  border-radius: 50%; border: 1.5px solid var(--c-border-mid); background: #fff;
  color: var(--c-muted); transition: all .2s;
}
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; align-items: center; gap: 20px; }
.status-toggle-wrapper { display: flex; align-items: center; gap: 8px; }
.status-label { font-size: .75rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.status-toggle-wrapper button {
  padding: 5px 12px; border-radius: 100px; font-size: .7rem; font-weight: 800; border: none; cursor: pointer;
}
.status-toggle-wrapper button.active { background: #dcfce7; color: #166534; }
.status-toggle-wrapper button.inactive { background: #fee2e2; color: #991b1b; }

.btn-save {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; border: none; cursor: pointer;
  box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2); transition: transform .2s;
}

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: var(--radius-lg); border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: var(--shadow-sm);
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #4F46E5, #3730A3); /* Thème Indigo */
  display: flex; align-items: center; justify-content: center; color: #fff;
  font-weight: 800; font-size: 1.2rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 4px 0 0; }

/* ─── Grid ─── */
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
.col-main, .col-side { display: flex; flex-direction: column; gap: 24px; }

/* ─── Cards ─── */
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); }
.card-header {
  display: flex; align-items: center; gap: 12px; padding: 16px 20px;
  background: var(--c-subtle);
  border-bottom: 1px solid var(--c-border);
}
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; letter-spacing: 0.03em; }
.card-header-icon {
  width: 32px; height: 32px; border-radius: 8px; background: var(--c-accent-bg); color: var(--c-accent);
  display: flex; align-items: center; justify-content: center;
}
.card-header-icon.contact { background: #F0FDF4; color: #16A34A; }
.card-header-icon.bank { background: #FFF7ED; color: #EA580C; }
.card-header-icon.notes { background: #FFF1F2; color: #E11D48; }

.card-body { padding: 24px 20px; }

/* ─── Form UI ─── */
.edit-form { display: flex; flex-direction: column; gap: 20px; }
.form-group-custom { display: flex; flex-direction: column; gap: 8px; }
.form-group-custom label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: 0.02em; }
.form-row-custom { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 16px; }

input, select, textarea {
  padding: 10px 14px; border: 1px solid var(--c-border-mid); border-radius: var(--radius-md);
  font-size: .9rem; font-family: inherit; transition: all .2s; background: #fff;
}
input:focus, select:focus, textarea:focus { border-color: var(--c-accent); outline: none; box-shadow: 0 0 0 3px rgba(79,70,229,0.08); }
input.input-lg { font-size: 1.1rem; font-weight: 700; color: var(--c-accent); }
input.mono { font-family: 'JetBrains Mono', monospace; font-weight: 600; text-transform: uppercase; }
input.money-input { font-weight: 800; border-color: var(--c-accent); color: var(--c-accent); }

/* ─── NATURE SELECTOR (Premium Theme) ─── */
.nature-selector {
  display: flex; background: #F3F4F6; padding: 4px; border-radius: 10px; border: 1px solid #E5E7EB;
}
.nature-selector button {
  flex: 1; padding: 10px; border: none; border-radius: 8px; font-weight: 600; font-size: .85rem;
  cursor: pointer; background: transparent; color: #6B7280; transition: all .2s;
}
.nature-selector button.active { 
  background: #FFFFFF; color: var(--c-accent); 
  box-shadow: 0 1px 3px rgba(0,0,0,0.1); font-weight: 700; 
}

.input-with-action { display: flex; gap: 8px; }
.action-trigger {
  width: 42px; border: 1px solid var(--c-border-mid); border-radius: var(--radius-md); background: #F9FAFB; cursor: pointer; color: var(--c-muted); display: flex; align-items: center; justify-content: center;
}
.action-trigger:hover { border-color: var(--c-accent); color: var(--c-accent); background: #fff; }

.checkbox-group { display: flex; align-items: center; gap: 10px; }
.checkbox-group label { font-size: .85rem; font-weight: 600; cursor: pointer; }

.error-msg { font-size: .7rem; color: var(--c-danger); font-weight: 700; margin-top: -4px; }
.input-error { border-color: var(--c-danger); }
.field-separator { height: 1px; background: var(--c-border); margin: 8px 0; }

/* ─── Global ─── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 1000; background: rgba(255,255,255,0.7); backdrop-filter: blur(4px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.toast-notification {
  position: fixed; top: 24px; right: 24px; padding: 14px 24px; border-radius: 12px; color: #fff; font-weight: 700; z-index: 1100; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.toast-notification.success { background: var(--c-success); }
.toast-notification.error { background: var(--c-danger); }

@media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr; } }
</style>