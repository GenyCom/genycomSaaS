<template>
  <div class="project-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement des projets…</p>
      </div>
    </Transition>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le Projet"
      :message="`Êtes-vous sûr de vouloir supprimer le projet '${itemToDelete?.nom_projet}' ? Cette action est irréversible.`"
      confirmText="Supprimer le projet"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Gestion</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Liste des Projets</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/projets/create" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouveau Projet</span>
        </router-link>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="filters.search" type="text" placeholder="Rechercher un projet ou un code..." />
      </div>
      <div class="filter-group">
        <select v-model="filters.etat_id" class="filter-select">
          <option value="">Tous les statuts</option>
          <option v-for="s in etats" :key="s.id" :value="s.id">{{ s.libelle }}</option>
        </select>
      </div>
    </div>

    <div v-if="!loading && projectsFiltered.length === 0" class="empty-state-card">
      <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
        </svg>
      </div>
      <h3>Aucun projet trouvé</h3>
      <p>Commencez par initialiser un projet pour l'un de vos clients.</p>
      <router-link to="/projets/create" class="btn-primary-custom mt-4">Créer mon premier projet</router-link>
    </div>

    <div v-else class="projects-grid">
      <div v-for="p in projectsFiltered" :key="p.id" class="project-card">
        <div class="card-top">
          <div class="badges-row">
            <span :class="['priority-badge', p.priorite || 'normale']">{{ (p.priorite || 'Normale').toUpperCase() }}</span>
            <span 
              v-if="p.etat" 
              class="status-badge-dynamic" 
              :style="{ backgroundColor: p.etat.color + '20', color: p.etat.color, borderColor: p.etat.color + '40' }"
            >
              {{ p.etat.libelle }}
            </span>
            <span v-else class="status-badge">{{ formatStatus(p.statut) }}</span>
          </div>
          <h3 class="project-name" :title="p.nom_projet">{{ p.nom_projet }}</h3>
          <span class="project-code">{{ p.code_projet }}</span>
        </div>

        <div class="card-mid">
          <div class="client-info">
            <div class="client-avatar">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <span class="client-name">{{ p.client?.societe || 'Client Inconnu' }}</span>
          </div>

          <div class="progress-section">
            <div class="progress-meta">
              <span class="label">Avancement</span>
              <span class="value">{{ p.avancement_pcent || 0 }}%</span>
            </div>
            <div class="progress-track">
              <div class="progress-bar" :style="{ width: (p.avancement_pcent || 0) + '%' }"></div>
            </div>
          </div>
        </div>

        <div class="card-bottom">
          <div class="meta-item">
            <span class="meta-label">Échéance</span>
            <span class="meta-value">{{ p.date_fin_prevue ? formatDate(p.date_fin_prevue) : '--' }}</span>
          </div>
          <div class="actions-group">
            <router-link :to="'/projets/' + p.id + '/edit'" class="action-btn edit" title="Gérer le projet">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </router-link>
            <button @click="confirmDelete(p)" class="action-btn delete" title="Supprimer">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../services/api'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const loading = ref(true)
const projets = ref([])
const etats = ref([])
const filters = reactive({ search: '', etat_id: '', statut: '' })

const toast = reactive({ show: false, message: '', type: 'success' })
const showConfirm = ref(false)
const itemToDelete = ref(null)

function showToast(message, type = 'success') {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 4000)
}

async function fetchEtats() {
  try {
    // CORRECTION APPLIQUÉE ICI : /etats?type_document=projet
    const { data } = await api.get('/parametrage/referentiels/etats?type_document=projet')
    etats.value = data.data || data
  } catch (e) {
    console.error("Erreur lors de la récupération des états", e)
  }
}

const projectsFiltered = computed(() => {
  return projets.value.filter(p => {
    const matchSearch = !filters.search || 
      p.nom_projet?.toLowerCase().includes(filters.search.toLowerCase()) || 
      p.code_projet?.toLowerCase().includes(filters.search.toLowerCase())
    const matchStatut = !filters.etat_id || p.etat_id == filters.etat_id
    const matchLegacy = !filters.statut || p.statut === filters.statut
    return matchSearch && matchStatut && matchLegacy
  })
})

function formatStatus(val) {
  // Option fallback si aucun état dynamique
  const legacyStatus = [
    { val: 'en_cours', label: 'En cours' },
    { val: 'termine', label: 'Terminé' },
    { val: 'annule', label: 'Annulé' }
  ]
  return legacyStatus.find(o => o.val === val)?.label || val || 'En cours'
}

function formatDate(val) {
  if (!val) return '--'
  return new Date(val).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function fetchProjets() {
  loading.value = true
  try {
    const { data } = await api.get('/projets')
    projets.value = data.data || data || []
  } catch (error) {
    console.error('Error fetching projects:', error)
  } finally {
    loading.value = false
  }
}

function confirmDelete(p) {
  itemToDelete.value = p
  showConfirm.value = true
}

async function executeDelete() {
  const p = itemToDelete.value
  if (!p) return

  showConfirm.value = false
  loading.value = true
  try {
    await api.delete(`/projets/${p.id}`)
    showToast('Projet supprimé avec succès !', 'success')
    fetchProjets()
  } catch (e) {
    showToast(e.response?.data?.message || 'Erreur lors de la suppression.', 'error')
    loading.value = false
  } finally {
    itemToDelete.value = null
  }
}

onMounted(() => {
  fetchProjets()
  fetchEtats()
})
</script>

<style scoped>
/* ─── Design Tokens (Standardisés) ──────────────────────────────────────────── */
.project-list-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-accent:     #2563EB;
  --c-accent-bg:  #EEF4FF;
  --c-danger:     #DC2626;
  --c-success:    #16A34A;
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
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-primary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: transform .2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); background: #1d4ed8; }

/* ─── Filters Strip ──────────────────────────────────────────────────────────── */
.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid var(--c-border);
  margin-bottom: 28px; box-shadow: var(--shadow-sm);
}
.search-wrapper {
  flex: 1; position: relative; display: flex; align-items: center;
  background: var(--c-subtle); border-radius: var(--radius-sm); padding: 0 12px;
}
.search-wrapper svg { color: var(--c-muted); }
.search-wrapper input {
  width: 100%; padding: 10px 10px; border: none; background: transparent;
  font-size: .88rem; color: var(--c-text); outline: none;
}
.filter-select {
  padding: 10px 14px; border-radius: var(--radius-sm); border: 1.5px solid var(--c-border-mid);
  font-size: .85rem; font-weight: 600; color: var(--c-text); outline: none;
}

/* ─── Projects Grid ─────────────────────────────────────────────────────────── */
.projects-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;
}

/* ─── Project Card ───────────────────────────────────────────────────────────── */
.project-card {
  background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-lg);
  padding: 20px; display: flex; flex-direction: column; gap: 18px;
  transition: all .25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: var(--shadow-sm);
}
.project-card:hover {
  transform: translateY(-4px); border-color: var(--c-accent);
  box-shadow: 0 12px 24px rgba(0,0,0,0.06);
}

.badges-row { display: flex; justify-content: space-between; align-items: center; }
.priority-badge {
  padding: 3px 10px; border-radius: 6px; font-size: .65rem; font-weight: 800;
}
.priority-badge.urgente { background: #FEF2F2; color: #DC2626; }
.priority-badge.haute { background: #FFFBEB; color: #D97706; }
.priority-badge.normale { background: #EEF4FF; color: #2563EB; }
.priority-badge.basse { background: #F0FDF4; color: #16A34A; }

.status-badge {
  padding: 3px 10px; border-radius: 6px; font-size: .65rem; font-weight: 700;
  background: var(--c-subtle); color: var(--c-muted);
}
.status-badge-dynamic {
  padding: 3px 10px; border-radius: 6px; font-size: .65rem; font-weight: 700;
  border: 1px solid transparent;
}

.project-name { font-size: 1.1rem; font-weight: 800; margin: 8px 0 2px; color: var(--c-text); }
.project-code { font-family: 'JetBrains Mono', monospace; font-size: .75rem; color: var(--c-accent); font-weight: 700; }

.client-info { display: flex; align-items: center; gap: 8px; background: var(--c-subtle); padding: 8px 12px; border-radius: 10px; }
.client-avatar { width: 24px; height: 24px; border-radius: 6px; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-accent); }
.client-name { font-size: .8rem; font-weight: 700; color: var(--c-text); }

.progress-section { margin-top: 4px; }
.progress-meta { display: flex; justify-content: space-between; margin-bottom: 6px; }
.progress-meta .label { font-size: .7rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.progress-meta .value { font-size: .8rem; font-weight: 800; color: var(--c-accent); }
.progress-track { height: 6px; background: var(--c-subtle); border-radius: 10px; overflow: hidden; }
.progress-bar { height: 100%; background: var(--c-accent); border-radius: 10px; transition: width 1s ease; }

.card-bottom {
  display: flex; justify-content: space-between; align-items: center;
  padding-top: 14px; border-top: 1px solid var(--c-border);
}
.meta-label { display: block; font-size: .65rem; color: var(--c-muted); font-weight: 700; text-transform: uppercase; }
.meta-value { font-size: .8rem; font-weight: 600; color: var(--c-text); }

.actions-group { display: flex; gap: 8px; }
.action-btn {
  width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid var(--c-border);
  background: #fff; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .2s; color: var(--c-muted);
}
.action-btn.edit:hover { border-color: var(--c-accent); color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn.delete:hover { border-color: var(--c-danger); color: var(--c-danger); background: #FEF2F2; }

/* ─── Empty State ────────────────────────────────────────────────────────────── */
.empty-state-card {
  text-align: center; background: #fff; border: 2px dashed var(--c-border-mid);
  padding: 60px 20px; border-radius: var(--radius-lg); color: var(--c-muted);
}
.empty-icon { color: var(--c-border-mid); margin-bottom: 16px; }
.empty-state-card h3 { color: var(--c-text); font-weight: 800; font-size: 1.25rem; margin-bottom: 8px; }

/* ─── Loading ────────────────────────────────────────────────────────────────── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 100; background: rgba(247,248,250,0.8);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { display: inline-block; width: 40px; height: 40px; position: relative; }
.loader-ring div {
  position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent);
  border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
  .project-list-view { padding: 16px; }
  .filters-card { flex-direction: column; align-items: stretch; }
  .projects-grid { grid-template-columns: 1fr; }
}

/* ─── Modal & Toast ──────────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
  animation: fadeIn 0.2s ease;
}
.modal-box {
  background: #fff; width: 400px; max-width: 90%; border-radius: 12px; padding: 24px;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}
.modal-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.modal-header h3 { font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--c-text); }
.text-danger { color: var(--c-danger); }
.modal-box p { font-size: 0.9rem; color: var(--c-muted); margin-bottom: 24px; line-height: 1.5; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
.btn-cancel {
  padding: 8px 16px; border-radius: 8px; border: 1px solid var(--c-border-mid);
  background: #fff; color: var(--c-text); font-weight: 600; cursor: pointer;
}
.btn-confirm-delete {
  padding: 8px 16px; border-radius: 8px; border: none; background: var(--c-danger);
  color: #fff; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px rgba(220, 38, 38, 0.2);
}
.btn-cancel:hover { background: var(--c-subtle); }
.btn-confirm-delete:hover { background: #b91c1c; }

.toast-notification {
  position: fixed; top: 24px; right: 24px; padding: 14px 24px; border-radius: 12px;
  color: #fff; font-weight: 700; z-index: 1100; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.toast-notification.success { background: var(--c-success); }
.toast-notification.error { background: var(--c-danger); }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateX(20px); opacity: 0; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>