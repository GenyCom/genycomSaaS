<template>
  <div class="avoir-list-view animate-fade-in">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement des avoirs…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Avoirs Fournisseurs</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/avoirs-fournisseurs/new" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouvel Avoir Fournisseur</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar avoir-fr-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline><path d="M12 16v-5"></path><path d="M12 11L9.5 13.5"></path><path d="M12 11l2.5 2.5"></path></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Retours Marchandises & Crédits
        </div>
        <h1 class="hero-name">Avoirs Fournisseurs</h1>
        <p class="hero-sub">Total des notes de crédit reçues : <strong>{{ calculateTotal() }}</strong></p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Rechercher par n° d'avoir, fournisseur ou réf. commande..." />
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 15%">N° Avoir</th>
              <th style="width: 12%">Date</th>
              <th style="width: 25%">Fournisseur</th>
              <th style="width: 15%">Réf. Facture / Dette</th>
              <th style="width: 15%" class="text-right">Montant HT</th>
              <th style="width: 10%" class="text-center">État</th>
              <th style="width: 8%" class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in filteredAvoirs" :key="a.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ a.numero }}</span>
              </td>
              <td class="text-muted text-sm font-medium">{{ formatDate(a.date_avoir) }}</td>
              <td>
                <div class="fournisseur-name">{{ a.fournisseur?.societe || a.fournisseur?.nom || '—' }}</div>
              </td>
              <td>
                <span v-if="a.facture?.numero" class="ref-badge">{{ a.facture.numero }}</span>
                <span v-else class="text-muted">—</span>
              </td>
              <td class="text-right">
                <div class="amount-cell text-credit">
                  + {{ formatMoney(a.montant_ht) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-center">
                <span v-if="a.statut === 'valide'" class="status-pill-dynamic status-active">Validé</span>
                <span v-else class="status-pill-dynamic" style="background: #F3F4F6; color: #4B5563;">Brouillon</span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/avoirs-fournisseurs/${a.id}`" class="action-btn view" title="Détails">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button class="action-btn delete" title="Supprimer" @click="confirmDelete(a.id)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredAvoirs.length === 0 && !loading">
              <td colspan="7" class="empty-row">
                <div class="empty-content">
                  <p>Aucun avoir fournisseur ne correspond à votre recherche.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de Confirmation de Suppression -->
    <ConfirmModal 
      :show="showDeleteModal"
      title="Supprimer l'avoir"
      message="Voulez-vous vraiment supprimer cet avoir fournisseur ? Cette action est irréversible."
      confirmText="Supprimer"
      @confirm="executeDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api.js'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const loading = ref(false)
const searchQuery = ref('')
const avoirs = ref([])
const showDeleteModal = ref(false)
const idToDelete = ref(null)

const filteredAvoirs = computed(() => {
  if (!searchQuery.value) return avoirs.value
  const q = searchQuery.value.toLowerCase()
  return avoirs.value.filter(a => 
    a.numero?.toLowerCase().includes(q) || 
    a.fournisseur?.societe?.toLowerCase().includes(q) ||
    a.facture?.numero?.toLowerCase().includes(q)
  )
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateString) {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

function calculateTotal() {
  // CORRECTION : Calcul sur montant_ht
  const sum = avoirs.value.reduce((acc, a) => acc + (parseFloat(a.montant_ht) || 0), 0)
  return formatMoney(sum) + ' DH'
}

onMounted(async () => {
  loading.value = true
  try {
    // Connexion à ton contrôleur Laravel
    const { data } = await api.get('/avoirs-fournisseurs')
    // Gère la structure de pagination de Laravel
    avoirs.value = data.data || data || []
  } catch (error) {
    console.error('Erreur lors du chargement des avoirs:', error)
  } finally {
    loading.value = false
  }
})

function confirmDelete(id) {
  idToDelete.value = id
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!idToDelete.value) return
  showDeleteModal.value = false
  loading.value = true
  try {
    await api.delete(`/avoirs-fournisseurs/${idToDelete.value}`)
    toast.success('Avoir fournisseur supprimé avec succès.')
    // Rafraîchir la liste
    avoirs.value = avoirs.value.filter(a => a.id !== idToDelete.value)
  } catch (err) {
    console.error('Erreur suppression:', err)
    // Le toast d'erreur est géré globalement par api.js
  } finally {
    loading.value = false
    idToDelete.value = null
  }
}
</script>

<style scoped>
/* ─── Design Tokens (Avoirs Fournisseurs / Achats) ─── */
.avoir-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #D97706; /* Ambre / Orange */
  --c-accent-bg: #FEF3C7; --c-success: #10B981;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; gap: 12px; }
.btn-primary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: 8px;
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2); transition: transform .2s; cursor: pointer;
}
.btn-primary-custom:hover { transform: translateY(-1px); background: #B45309; }

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.avoir-fr-theme { background: linear-gradient(135deg, #F59E0B, #D97706); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-sub { color: var(--c-muted); margin-top: 2px;}

/* ─── Filters ─── */
.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.search-wrapper {
  flex: 1; display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: 8px; color: var(--c-muted);
}
.search-wrapper input { flex: 1; padding: 12px 0; border: none; background: transparent; outline: none; font-size: .9rem; color: var(--c-text); }

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; color: var(--c-text); font-size: 0.9rem;}
.table-row:hover { background: #F9FAFB; }

.fournisseur-name { font-size: .95rem; font-weight: 700; color: var(--c-text); }
.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .75rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px; width: fit-content; text-transform: uppercase; }
.ref-badge { font-size: .75rem; font-weight: 600; color: #64748B; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; width: fit-content; border: 1px solid #E2E8F0; }

.amount-cell { font-size: .95rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
.text-credit { color: var(--c-success); }
.currency { font-size: .65rem; opacity: .7; margin-left: 2px; }

.status-pill-dynamic { 
  display: inline-block; padding: 4px 12px; border-radius: 100px; 
  font-size: .72rem; font-weight: 800; text-transform: uppercase;
}
.status-active { background: #ECFDF5; color: #047857; } 

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn.delete:hover { color: #DC2626; border-color: #FECACA; background: #FEF2F2; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.text-sm { font-size: 0.85rem; }
.text-muted { color: var(--c-muted); }
.font-medium { font-weight: 500; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

/* ─── Loader ─── */
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>