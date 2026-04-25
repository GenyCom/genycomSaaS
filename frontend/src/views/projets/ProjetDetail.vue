<template>
  <div class="project-detail-view">
    
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement du projet…</p>
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
          <span v-if="loading" class="breadcrumb-skeleton"></span>
          <span v-else class="breadcrumb-current">{{ projet.nom_projet || '—' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-secondary-custom" @click="editMode = !editMode">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>{{ editMode ? 'Annuler' : 'Modifier' }}</span>
        </button>
        <button v-if="editMode" class="btn-save" @click="saveProjet">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>{{ projectInitials }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ projet.code_projet }}
        </div>
        <h1 class="hero-name">
          <span v-if="loading" class="skeleton-line wide"></span>
          <template v-else>{{ projet.nom_projet || 'Chargement…' }}</template>
        </h1>
        <p class="hero-sub">
          <span v-if="loading" class="skeleton-line narrow"></span>
          <template v-else>
            Client : <strong>{{ projet.client?.societe || 'Non défini' }}</strong>
          </template>
        </p>
      </div>
      <div 
        v-if="projet.etat" 
        class="hero-status-badge-dynamic" 
        :style="{ backgroundColor: projet.etat.color + '20', color: projet.etat.color, borderColor: projet.etat.color + '40' }"
      >
        {{ projet.etat.libelle }}
      </div>
      <div v-else class="hero-status-badge" :class="projet.statut">
        {{ formatStatut(projet.statut) }}
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Budget Prévu</p>
          <p class="kpi-value">{{ formatMoney(projet.budget_prevu) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Avancement</p>
          <p class="kpi-value">{{ projet.avancement_pcent || 0 }} <span>%</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item danger">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Consommé</p>
          <p class="kpi-value">{{ formatMoney(projet.budget_consomme) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      
      <div class="col-left">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3>Calendrier & Références</h3>
          </div>
          <div class="card-body">
            <div v-if="!editMode">
              <div class="field-row">
                <span class="field-label">Date de début</span>
                <span class="field-value">{{ projet.date_debut || '—' }}</span>
              </div>
              <div class="field-row">
                <span class="field-label">Fin prévue</span>
                <span class="field-value">{{ projet.date_fin_prevue || '—' }}</span>
              </div>
              <div class="field-separator"></div>
              <div class="field-row">
                <span class="field-label">Code interne</span>
                <span class="field-value mono accent">{{ projet.code_projet }}</span>
              </div>
            </div>

            <div v-else class="edit-form">
              <div class="form-group-custom">
                <label>Nom du Projet</label>
                <input v-model="form.nom_projet" type="text" />
              </div>
              <div class="form-row-custom">
                <div class="form-group-custom">
                  <label>Date Début</label>
                  <input v-model="form.date_debut" type="date" />
                </div>
                <div class="form-group-custom">
                  <label>Fin Prévue</label>
                  <input v-model="form.date_fin_prevue" type="date" />
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="col-right">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Client Associé</h3>
          </div>
          <div class="card-body">
            <div v-if="projet.client" class="client-mini-card">
              <p class="client-name">{{ projet.client.societe }}</p>
              <router-link :to="`/clients/${projet.client.id}`" class="client-link-btn">Voir la fiche client</router-link>
            </div>
            <p v-else class="notes-empty">Aucun client rattaché.</p>
          </div>
        </section>

        <section v-if="editMode" class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h3>Gestion Statut & Budget</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Statut du Projet</label>
              <select v-model="form.etat_id">
                <option v-for="s in etats" :key="s.id" :value="s.id">{{ s.libelle }}</option>
              </select>
            </div>
            <div class="form-group-custom">
              <label>Budget Prévu (DH)</label>
              <input v-model="form.budget_prevu" type="number" />
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'

const props = defineProps({ id: [String, Number] })
const route = useRoute()
const editMode = ref(false)
const loading = ref(true)

const projet = ref({
  id: null,
  code_projet: '',
  nom_projet: '',
  client: null,
  date_debut: '',
  date_fin_prevue: '',
  budget_prevu: 0,
  budget_consomme: 0,
  statut: 'en_cours',
  etat_id: null,
  etat: null,
  avancement_pcent: 0
})

const etats = ref([])

const form = ref({ ...projet.value })

const projectInitials = computed(() => {
  const name = projet.value?.nom_projet || ''
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() || 'PJ'
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function fetchEtats() {
  try {
    const { data } = await api.get('/parametrage/referentiels/etat-document?type=projet')
    etats.value = data.data || data
  } catch (e) {
    console.error("Erreur états", e)
  }
}

async function fetchProjet() {
  loading.value = true
  try {
    const id = props.id || route.params.id
    const { data } = await api.get(`/projets/${id}`)
    projet.value = data.data || data
    form.value = { ...projet.value }
  } catch (error) {
    console.error('Erreur chargement projet:', error)
  } finally {
    loading.value = false
  }
}

async function saveProjet() {
  try {
    await api.put(`/projets/${projet.value.id}`, form.value)
    projet.value = { ...form.value }
    editMode.value = false
    toast.success('Projet mis à jour avec succès.')
  } catch (e) {
    toast.error('Erreur lors de la sauvegarde.')
  }
}

onMounted(() => {
  fetchProjet()
  fetchEtats()
})
</script>

<style scoped>
/* ─── Design Tokens (Identiques à ClientDetail) ─────────────────────────────── */
.project-detail-view {
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
  --c-danger-bg:  #FEF2F2;
  --c-success:    #16A34A;
  --c-success-bg: #F0FDF4;
  --c-warn:       #D97706;
  --c-warn-bg:    #FFFBEB;
  --c-neutral-bg: #F0F4FF;

  --radius-sm:  8px;
  --radius-md:  12px;
  --radius-lg:  16px;
  --shadow-sm:  0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
  --shadow-md:  0 4px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);

  font-family: 'Inter', system-ui, sans-serif;
  color: var(--c-text);
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
}

/* ─── Loading & Skeletons (Identiques) ───────────────────────────────────────── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 100;
  background: rgba(247,248,250,0.85); backdrop-filter: blur(4px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { display: inline-block; position: relative; width: 48px; height: 48px; }
.loader-ring div {
  box-sizing: border-box; display: block; position: absolute; width: 38px; height: 38px; margin: 5px;
  border: 3px solid transparent; border-radius: 50%; animation: loader-spin 1.1s infinite; border-top-color: var(--c-accent);
}
@keyframes loader-spin { to { transform: rotate(360deg); } }

/* ─── Top Bar ────────────────────────────────────────────────────────────────── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn {
  display: flex; align-items: center; justify-content: center; width: 34px; height: 34px;
  border-radius: 50%; border: 1.5px solid var(--c-border-mid); background: var(--c-surface);
  color: var(--c-muted); transition: all .18s; box-shadow: var(--shadow-sm);
}
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); transform: translateX(-1px); }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 600; }

.topbar-actions { display: flex; gap: 10px; }
.btn-secondary-custom, .btn-save {
  display: inline-flex; align-items: center; gap: 7px; padding: 8px 18px; border-radius: var(--radius-sm);
  font-size: .82rem; font-weight: 600; cursor: pointer; transition: all .18s; border: none;
}
.btn-secondary-custom { background: var(--c-subtle); color: var(--c-muted); }
.btn-save { background: var(--c-accent); color: #fff; box-shadow: 0 1px 4px rgba(37,99,235,.3); }

/* ─── Hero ───────────────────────────────────────────────────────────────────── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: var(--c-surface);
  border: 1px solid var(--c-border); border-radius: var(--radius-lg);
  padding: 22px 28px; margin-bottom: 20px; box-shadow: var(--shadow-sm);
}
.hero-avatar {
  width: 56px; height: 56px; border-radius: 14px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.15rem; font-weight: 800;
}
.hero-meta { flex: 1; }
.hero-type-badge {
  display: inline-flex; align-items: center; gap: 5px; font-size: .68rem;
  font-weight: 700; text-transform: uppercase; color: #6366f1; margin-bottom: 5px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: #6366f1; border-radius: 50%; }
.hero-name { font-size: 1.45rem; font-weight: 800; margin: 0 0 4px; }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 0; }

.hero-status-badge {
  padding: 7px 14px; border-radius: 100px; font-size: .75rem; font-weight: 700;
}
.hero-status-badge.en_cours { background: var(--c-accent-bg); color: var(--c-accent); }
.hero-status-badge.termine  { background: var(--c-success-bg); color: var(--c-success); }
.hero-status-badge.brouillon { background: var(--c-subtle); color: var(--c-muted); }

.hero-status-badge-dynamic { padding: 8px 20px; border-radius: 100px; font-size: .75rem; font-weight: 800; border: 1px solid transparent; }

/* ─── KPI Strip ──────────────────────────────────────────────────────────────── */
.kpi-strip {
  display: flex; background: var(--c-surface); border: 1px solid var(--c-border);
  border-radius: var(--radius-lg); margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-sm);
}
.kpi-item { flex: 1; display: flex; align-items: center; gap: 14px; padding: 18px 22px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.kpi-item.accent  .kpi-icon { background: var(--c-accent-bg); color: var(--c-accent); }
.kpi-item.neutral .kpi-icon { background: var(--c-neutral-bg); color: #6366f1; }
.kpi-item.danger .kpi-icon  { background: var(--c-danger-bg); color: var(--c-danger); }

.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0 0 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .65; margin-left: 3px; }

/* ─── Content Grid & Cards ───────────────────────────────────────────────────── */
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }
.col-left, .col-right { display: flex; flex-direction: column; gap: 20px; }

.info-card {
  background: var(--c-surface); border: 1px solid var(--c-border);
  border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;
}
.card-header {
  display: flex; align-items: center; gap: 10px; padding: 16px 20px;
  border-bottom: 1px solid var(--c-border); background: var(--c-subtle);
}
.card-header h3 { font-size: .77rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon {
  width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center;
  justify-content: center; background: var(--c-accent-bg); color: var(--c-accent);
}
.card-header-icon.contact { background: var(--c-success-bg); color: var(--c-success); }
.card-header-icon.bank    { background: #FFF7ED; color: #EA580C; }

.card-body { padding: 18px 20px; }
.field-row { display: flex; justify-content: space-between; padding: 8px 0; }
.field-label { font-size: .8rem; color: var(--c-muted); font-weight: 500; }
.field-value { font-size: .85rem; font-weight: 600; }
.field-value.mono { font-family: 'JetBrains Mono', monospace; color: var(--c-accent); }
.field-separator { height: 1px; background: var(--c-border); margin: 10px 0; }

/* ─── Form Elements ──────────────────────────────────────────────────────────── */
.edit-form { display: flex; flex-direction: column; gap: 14px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; }
.form-group-custom label { font-size: .75rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-group-custom input, .form-group-custom select {
  padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--c-border-mid);
  font-size: .9rem; font-family: inherit; transition: border-color .2s;
}
.form-group-custom input:focus { border-color: var(--c-accent); outline: none; }
.form-row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* ─── Client Mini Card ───────────────────────────────────────────────────────── */
.client-mini-card { display: flex; flex-direction: column; gap: 10px; }
.client-name { font-weight: 700; font-size: 1rem; margin: 0; }
.client-link-btn {
  display: inline-block; padding: 6px 12px; background: var(--c-accent-bg);
  color: var(--c-accent); border-radius: 6px; font-size: .75rem; font-weight: 700;
  text-decoration: none; text-align: center;
}
.notes-empty { color: var(--c-muted); font-style: italic; font-size: .85rem; text-align: center; padding: 10px; }

@media (max-width: 900px) {
  .content-grid { grid-template-columns: 1fr; }
  .kpi-strip { flex-direction: column; }
  .kpi-divider { height: 1px; width: auto; margin: 0 16px; }
}
</style>