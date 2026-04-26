<template>
  <div class="supplier-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement des fournisseurs…</p>
      </div>
    </Transition>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

    <!-- Modal de confirmation élégant -->
    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le Fournisseur"
      message="Êtes-vous sûr de vouloir supprimer ce fournisseur ? Cette action est irréversible et pourrait impacter vos historiques d'achat."
      confirmText="Supprimer définitivement"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Gestion</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Fournisseurs</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/fournisseurs/create" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouveau Fournisseur</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>FR</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Chaîne d'approvisionnement
        </div>
        <h1 class="hero-name">Gestion Fournisseurs</h1>
        <p class="hero-sub">Vous collaborez avec <strong>{{ fournisseurs.length }}</strong> fournisseur(s) actif(s).</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher société, code, email..." />
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Société / Nom</th>
              <th>Localisation</th>
              <th>Contact</th>
              <th>Type</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="frn in filteredFournisseurs" :key="frn.id" class="table-row">
              <td>
                <span class="supplier-code-badge">{{ frn.code_fournisseur || '—' }}</span>
              </td>
              <td class="societe-cell">
                <div class="societe-name">{{ frn.societe }}</div>
                <div class="societe-sub">{{ frn.ice || 'Sans ICE' }}</div>
              </td>
              <td>
                <div class="location-info">
                  <span class="ville">{{ (frn.ville || '—').toUpperCase() }}</span>
                  <span class="pays">{{ frn.pays || 'Maroc' }}</span>
                </div>
              </td>
              <td>
                <div class="contact-stack">
                  <div class="phone">{{ frn.telephone || frn.mobile || '—' }}</div>
                  <div class="email-sub">{{ frn.email || '—' }}</div>
                </div>
              </td>
              <td>
                <span class="type-pill">
                  {{ frn.type_fournisseur?.libelle || 'Standard' }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/fournisseurs/${frn.id}`" class="action-btn view" title="Détails">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <router-link :to="`/fournisseurs/${frn.id}/edit`" class="action-btn edit" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </router-link>
                  <button @click="deleteFournisseur(frn.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredFournisseurs.length === 0">
              <td colspan="6" class="empty-row">
                <div class="empty-content">
                  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" color="var(--c-border-mid)"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                  <p>Aucun fournisseur ne correspond à votre recherche.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '../../services/api'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const fournisseurs = ref([])
const search = ref('')
const loading = ref(true)

const filteredFournisseurs = computed(() => {
  if (!search.value) return fournisseurs.value
  const s = search.value.toLowerCase()
  return fournisseurs.value.filter(f =>
    (f.societe || '').toLowerCase().includes(s) ||
    (f.code_fournisseur || '').toLowerCase().includes(s) ||
    (f.email || '').toLowerCase().includes(s)
  )
})

const toast = reactive({ show: false, message: '', type: 'success' })
const showConfirm = ref(false)
const itemToDelete = ref(null)

function showToast(message, type = 'success') {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 4000)
}

function deleteFournisseur(id) {
  itemToDelete.value = id
  showConfirm.value = true
}

async function confirmDelete() {
  const id = itemToDelete.value
  if (!id) return
  
  showConfirm.value = false
  loading.value = true
  try {
    await api.delete(`/fournisseurs/${id}`)
    fournisseurs.value = fournisseurs.value.filter(f => f.id !== id)
    showToast('Fournisseur supprimé avec succès !', 'success')
  } catch (error) {
    showToast(error.response?.data?.message || 'Erreur lors de la suppression.', 'error')
  } finally {
    loading.value = false
    itemToDelete.value = null
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/fournisseurs')
    fournisseurs.value = data.data || data || []
  } catch (error) {
    console.error('Erreur de chargement:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens (Standardisés) ──────────────────────────────────────────── */
.supplier-list-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-accent:     #2563EB;
  --c-accent-bg:  #EEF4FF;
  --c-danger:     #DC2626;
  --radius-lg:    16px;
  --radius-md:    12px;
  --radius-sm:     8px;
  --shadow-sm:    0 1px 3px rgba(0,0,0,.06);

  
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: var(--c-text);
}

/* ─── Topbar & Navigation ────────────────────────────────────────────────────── */
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

/* ─── Hero Header ────────────────────────────────────────────────────────────── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: var(--radius-lg); border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: var(--shadow-sm);
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #4F46E5, #7C3AED);
  display: flex; align-items: center; justify-content: center; color: #fff;
  font-weight: 800; font-size: 1.1rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: #7C3AED; margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: #7C3AED; border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 4px 0 0; }

/* ─── Filters ────────────────────────────────────────────────────────────────── */
.filters-card {
  background: #fff; padding: 12px 16px; border-radius: var(--radius-md);
  border: 1px solid var(--c-border); margin-bottom: 20px; box-shadow: var(--shadow-sm);
}
.search-wrapper {
  display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: var(--radius-sm); max-width: 480px;
}
.search-wrapper svg { color: var(--c-muted); }
.search-wrapper input {
  flex: 1; padding: 12px 0; border: none; background: transparent;
  font-size: .9rem; color: var(--c-text); outline: none;
}

/* ─── Table Styling ──────────────────────────────────────────────────────────── */
.table-card {
  background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm); overflow: hidden;
}
.table-container-custom { overflow-x: auto; }

.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th {
  background: var(--c-subtle); padding: 14px 20px; font-size: .72rem;
  font-weight: 700; text-transform: uppercase; color: var(--c-muted);
  border-bottom: 1px solid var(--c-border); letter-spacing: .03em;
}
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }

.table-row { transition: background .15s; }
.table-row:hover { background: #F9FAFB; }

.supplier-code-badge {
  font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700;
  color: #7C3AED; background: #F5F3FF; padding: 4px 8px; border-radius: 6px;
}

.societe-name { font-size: .9rem; font-weight: 700; color: var(--c-text); margin-bottom: 2px; }
.societe-sub { font-size: .75rem; color: var(--c-muted); }

.location-info { display: flex; flex-direction: column; }
.location-info .ville { font-size: .8rem; font-weight: 600; color: var(--c-text); }
.location-info .pays { font-size: .75rem; color: var(--c-muted); }

.contact-stack { display: flex; flex-direction: column; gap: 2px; }
.contact-stack .phone { font-size: .85rem; font-weight: 600; }
.contact-stack .email-sub { font-size: .75rem; color: var(--c-accent); }

.type-pill {
  display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: .7rem;
  font-weight: 800; background: var(--c-subtle); color: var(--c-muted);
}

.text-right { text-align: right; }

/* ─── Actions ────────────────────────────────────────────────────────────────── */
.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn {
  width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border);
  background: #fff; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .2s; color: var(--c-muted);
}
.action-btn:hover { background: var(--c-subtle); transform: translateY(-1px); }
.action-btn.view:hover { color: var(--c-accent); border-color: var(--c-accent); }
.action-btn.edit:hover { color: var(--c-success); border-color: #16A34A; }
.action-btn.delete:hover { color: var(--c-danger); border-color: var(--c-danger); background: #FEF2F2; }

/* ─── States ─────────────────────────────────────────────────────────────────── */
.empty-row { padding: 60px 0 !important; }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--c-muted); font-size: .9rem; }

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

@media (max-width: 768px) {
  .hero-header { flex-direction: column; text-align: center; }
  .saas-table th:nth-child(3), .saas-table td:nth-child(3),
  .saas-table th:nth-child(4), .saas-table td:nth-child(4) { display: none; }
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
.toast-notification.success { background: #16A34A; }
.toast-notification.error { background: var(--c-danger); }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateX(20px); opacity: 0; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>