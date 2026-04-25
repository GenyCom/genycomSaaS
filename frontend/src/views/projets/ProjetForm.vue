<template>
  <div class="project-form-view">
    <Transition name="fade">
      <div v-if="saving" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Enregistrement en cours…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/projets" class="back-btn" title="Retour aux projets">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Projets</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Nouveau Projet' : form.nom_projet }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-cancel" @click="router.push('/projets')">Annuler</button>
        <button class="btn-save" @click="saveProjet" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          <span>Enregistrer le projet</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>{{ isNew ? '+' : 'PJ' }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ isNew ? 'Initialisation' : 'Édition de la fiche' }}
        </div>
        <h1 class="hero-name">{{ isNew ? 'Créer un nouveau projet' : 'Modifier le projet' }}</h1>
        <p class="hero-sub" v-if="!isNew">Référence : <strong>{{ form.code_projet }}</strong></p>
      </div>
    </div>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">
        {{ toast.message }}
      </div>
    </Transition>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <h3>01. Identification & Client</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Nom du Projet / Mission *</label>
              <input v-model="form.nom_projet" @input="generateCode" type="text" class="input-lg" :class="{ 'input-error': errors.nom_projet }" placeholder="Ex: Refonte Site E-commerce" />
              <span v-if="errors.nom_projet" class="error-msg">{{ errors.nom_projet }}</span>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Code / Référence *</label>
                <input v-model="form.code_projet" type="text" class="mono" :class="{ 'input-error': errors.code_projet }" />
              </div>
              <div class="form-group-custom">
                <label>Client Associé</label>
                <select v-model="form.client_id">
                  <option value="">Sélectionner un client...</option>
                  <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.societe || client.display_name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Type de Projet</label>
                <select v-model="form.type_projet">
                  <option value="Forfait">Forfait (Global)</option>
                  <option value="Régie">Régie (Temps passé)</option>
                  <option value="Maintenance">Maintenance / Support</option>
                  <option value="Installation">Installation / Déploiement</option>
                  <option value="Conseil">Conseil / Audit</option>
                </select>
              </div>
              <div class="form-group-custom">
                <label>Priorité</label>
                <div class="priority-pills">
                  <button v-for="p in ['basse','normale','haute','urgente']" :key="p" @click="form.priorite = p" :class="['pill', p, { active: form.priorite === p }]">
                    {{ p }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3>02. Description & Détails</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Objectifs & Livrables</label>
              <textarea v-model="form.description" rows="5" placeholder="Détaillez les objectifs stratégiques..."></textarea>
            </div>
          </div>
        </section>
      </div>

      <div class="col-side">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3>03. Planification</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Date de Début</label>
              <input v-model="form.date_debut" type="date" />
            </div>
            <div class="form-group-custom">
              <label>Deadline (Fin prévue)</label>
              <input v-model="form.date_fin_prevue" type="date" class="border-warn" />
            </div>
            
            <div class="field-separator"></div>

            <div class="form-group-custom">
              <div class="flex-label">
                <label>Avancement</label>
                <span class="val-badge">{{ form.avancement_pcent }}%</span>
              </div>
              <input type="range" v-model="form.avancement_pcent" min="0" max="100" class="custom-slider" />
            </div>

            <div class="form-group-custom">
              <label>Statut</label>
              <select v-model="form.statut" class="statut-select" :class="form.statut">
                <option value="brouillon">Brouillon</option>
                <option value="en_cours">En Cours</option>
                <option value="en_pause">Suspendu</option>
                <option value="termine">Terminé</option>
                <option value="annule">Annulé</option>
              </select>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>04. Finances (DH)</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Budget Prévu (HT)</label>
              <input v-model="form.budget_prevu" type="number" class="money-input" placeholder="0.00" />
            </div>
            <div class="form-group-custom">
              <label>Budget Consommé</label>
              <input v-model="form.budget_consomme" type="number" class="money-input-muted" placeholder="0.00" />
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const route = useRoute()
const isNew = computed(() => !route.params.id)
const saving = ref(false)
const clients = ref([])

const form = ref({
  nom_projet: '',
  code_projet: '',
  client_id: '',
  type_projet: 'Forfait',
  date_debut: '',
  date_fin_prevue: '',
  budget_prevu: 0,
  budget_consomme: 0,
  statut: 'en_cours',
  avancement_pcent: 0,
  priorite: 'normale',
  description: ''
})

const errors = reactive({})
const toast = reactive({ show: false, message: '', type: 'success' })

function showToast(message, type = 'success') {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 4000)
}

function validate() {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.value.nom_projet?.trim()) errors.nom_projet = 'Nom obligatoire'
  if (!form.value.code_projet?.trim()) errors.code_projet = 'Code obligatoire'
  return Object.keys(errors).length === 0
}

let refTimeout = null
function generateCode() {
  if (!isNew.value || form.value.code_projet) return
  if (!form.value.nom_projet?.trim()) return
  clearTimeout(refTimeout)
  refTimeout = setTimeout(() => {
     if (!form.value.code_projet) {
        const prefix = form.value.nom_projet.trim().substring(0, 3).toUpperCase()
        form.value.code_projet = `PRJ-${prefix}-${Math.floor(Math.random()*1000)}`
     }
  }, 400)
}

async function saveProjet() {
  if (!validate()) {
    showToast('Veuillez remplir les champs obligatoires', 'error')
    return
  }
  
  saving.value = true
  try {
    const payload = { 
      ...form.value,
      client_id: form.value.client_id ? parseInt(form.value.client_id) : null,
      budget_prevu: parseFloat(form.value.budget_prevu) || 0,
      budget_consomme: parseFloat(form.value.budget_consomme) || 0,
      avancement_pcent: parseInt(form.value.avancement_pcent) || 0
    }

    if (isNew.value) {
       await api.post('/projets', payload)
       showToast('Projet créé avec succès !')
    } else {
       await api.put(`/projets/${route.params.id}`, payload)
       showToast('Projet mis à jour !')
    }
    setTimeout(() => router.push('/projets'), 1000)
  } catch (error) {
    showToast(error.response?.data?.message || 'Erreur technique', 'error')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/clients', { params: { per_page: 500 }})
    clients.value = data.data || data || []
  } catch (e) { console.error(e) }

  if (!isNew.value) {
    try {
      const { data } = await api.get(`/projets/${route.params.id}`)
      form.value = { ...form.value, ...(data.data || data) }
    } catch (e) { showToast('Projet introuvable', 'error') }
  }
})
</script>

<style scoped>
/* ─── Design Tokens (Shared with ClientDetail) ─────────────────────────────── */
.project-form-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-subtle:     #F1F3F6;
  --c-accent:     #2563EB;
  --c-accent-bg:  #EEF4FF;
  --c-danger:     #DC2626;
  --c-success:    #16A34A;
  --c-warn:       #D97706;
  --radius-lg:    16px;
  --radius-md:    12px;
  --radius-sm:     8px;
  --shadow-sm:    0 1px 3px rgba(0,0,0,.06);

  font-family: 'Inter', system-ui, sans-serif;
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: var(--c-text);
}

/* ─── Topbar ────────────────────────────────────────────────────────────────── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn {
  display: flex; align-items: center; justify-content: center; width: 34px; height: 34px;
  border-radius: 50%; border: 1.5px solid var(--c-border-mid); background: #fff;
  color: var(--c-muted); transition: all .2s;
}
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 600; }

.topbar-actions { display: flex; gap: 12px; }
.btn-cancel { background: none; border: none; font-size: .85rem; font-weight: 600; color: var(--c-muted); cursor: pointer; }
.btn-save {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; border: none; cursor: pointer;
  box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: transform .2s;
}
.btn-save:hover { transform: translateY(-1px); background: #1d4ed8; }

/* ─── Hero ───────────────────────────────────────────────────────────────────── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: var(--radius-lg); border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: var(--shadow-sm);
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #2563EB, #4F46E5);
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

/* ─── Grid ───────────────────────────────────────────────────────────────────── */
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
.col-main, .col-side { display: flex; flex-direction: column; gap: 24px; }

/* ─── Cards ──────────────────────────────────────────────────────────────────── */
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); }
.card-header {
  display: flex; align-items: center; gap: 10px; padding: 14px 20px;
  background: var(--c-subtle); border-bottom: 1px solid var(--c-border);
}
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon {
  width: 28px; height: 28px; border-radius: 7px; background: var(--c-accent-bg);
  color: var(--c-accent); display: flex; align-items: center; justify-content: center;
}
.card-header-icon.contact { background: #F0FDF4; color: #16A34A; }
.card-header-icon.notes { background: #FFF1F2; color: #E11D48; }
.card-header-icon.bank { background: #FFF7ED; color: #EA580C; }

.card-body { padding: 20px; }

/* ─── Form Elements ──────────────────────────────────────────────────────────── */
.edit-form { display: flex; flex-direction: column; gap: 20px; }
.form-group-custom { display: flex; flex-direction: column; gap: 8px; }
.form-group-custom label { font-size: .75rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .02em; }
.form-row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

input, select, textarea {
  padding: 12px 14px; border: 1.5px solid var(--c-border-mid); border-radius: var(--radius-sm);
  font-size: .92rem; font-family: inherit; transition: all .2s; background: #fff;
}
input:focus, select:focus, textarea:focus { border-color: var(--c-accent); outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
input.input-lg { font-size: 1.1rem; font-weight: 700; color: var(--c-accent); }
input.mono { font-family: 'JetBrains Mono', monospace; font-weight: 600; text-transform: uppercase; }

.error-msg { font-size: .75rem; color: var(--c-danger); font-weight: 600; margin-top: -4px; }
.input-error { border-color: var(--c-danger); }
.field-separator { height: 1px; background: var(--c-border); margin: 10px 0; }

/* ─── Specific Widgets ───────────────────────────────────────────────────────── */
.priority-pills { display: flex; gap: 6px; }
.pill {
  flex: 1; padding: 6px; border-radius: 6px; border: 1px solid var(--c-border-mid);
  font-size: .65rem; font-weight: 700; text-transform: uppercase; cursor: pointer; background: #fff;
}
.pill.active.basse   { border-color: var(--c-success); background: #F0FDF4; color: var(--c-success); }
.pill.active.normale { border-color: var(--c-accent); background: #EEF4FF; color: var(--c-accent); }
.pill.active.haute   { border-color: var(--c-warn); background: #FFFBEB; color: var(--c-warn); }
.pill.active.urgente { border-color: var(--c-danger); background: #FEF2F2; color: var(--c-danger); }

.flex-label { display: flex; justify-content: space-between; align-items: center; }
.val-badge { background: var(--c-accent); color: #fff; padding: 2px 8px; border-radius: 10px; font-size: .75rem; font-weight: 800; }

.custom-slider { width: 100%; height: 6px; background: var(--c-subtle); border-radius: 5px; accent-color: var(--c-accent); cursor: pointer; }

.statut-select { font-weight: 700; text-transform: uppercase; font-size: .8rem; }
.statut-select.en_cours { color: var(--c-accent); }
.statut-select.termine  { color: var(--c-success); }
.statut-select.en_pause { color: var(--c-warn); }

.money-input { font-weight: 800; color: var(--c-accent); font-size: 1.1rem; border-color: var(--c-accent); }
.money-input-muted { font-weight: 600; color: var(--c-muted); background: var(--c-bg); }

/* ─── Toast ──────────────────────────────────────────────────────────────────── */
.toast-notification {
  position: fixed; top: 24px; right: 24px; padding: 14px 24px; border-radius: 12px;
  color: #fff; font-weight: 600; font-size: .85rem; z-index: 1000; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.toast-notification.success { background: var(--c-success); }
.toast-notification.error { background: var(--c-danger); }

/* ─── Loading ────────────────────────────────────────────────────────────────── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 2000; background: rgba(255,255,255,0.7); backdrop-filter: blur(4px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { display: inline-block; width: 40px; height: 40px; position: relative; }
.loader-ring div {
  position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent);
  border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Transitions ────────────────────────────────────────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-enter-from { transform: translateX(20px); opacity: 0; }

@media (max-width: 1024px) {
  .content-grid { grid-template-columns: 1fr; }
}
</style>