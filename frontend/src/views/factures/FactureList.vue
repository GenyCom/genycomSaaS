<template>
  <div class="facture-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement de la facturation…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Factures Clients</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/factures/new" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nouvelle Facture</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar billing-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Gestion Financière
        </div>
        <h1 class="hero-name">Factures Clients</h1>
        <p class="hero-sub">Vous gérez <strong>{{ factures.length }}</strong> document(s) de facturation au total.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par N° facture ou client..." />
      </div>
      <div class="filter-group">
        <select v-model="filterEtat" class="filter-select">
          <option value="">Tous les états</option>
          <option value="Brouillon">Brouillon</option>
          <option value="Ouverte">Ouverte</option>
          <option value="Partielle">Partielle</option>
          <option value="Payée">Payée</option>
          <option value="En retard">En retard</option>
        </select>
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Date</th>
              <th>Client</th>
              <th class="text-center">État</th>
              <th class="text-right">Total TTC</th>
              <th class="text-right">Reste à payer</th>
              <th>Échéance</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in filteredFactures" :key="f.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ f.numero }}</span>
              </td>
              <td>{{ f.date_facture }}</td>
              <td>
                <div class="client-name">{{ f.client }}</div>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="getEtatClass(f.etat)">
                  {{ f.etat }}
                </span>
              </td>
              <td class="text-right">
                <div class="amount-cell">
                  {{ formatMoney(f.total_ttc) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-right">
                <div class="amount-cell" :class="{'text-danger font-black': f.reste > 0}">
                  {{ formatMoney(f.reste) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td>
                <span :class="{'text-danger font-bold': isOverdue(f.date_echeance) && f.reste > 0}">
                  {{ f.date_echeance || '—' }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/factures/${f.id}`" class="action-btn view" title="Détails">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button @click="handleDelete(f.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredFactures.length === 0 && !loading">
              <td colspan="8" class="empty-row">
                <div class="empty-content">
                  <p>Aucune facture trouvée.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer la Facture"
      message="Voulez-vous vraiment supprimer cette facture client ? Cette action annulera la créance."
      confirmText="Supprimer la facture"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const factures = ref([])
const loading = ref(true)
const search = ref('')
const filterEtat = ref('')

const showConfirm = ref(false)
const itemToDelete = ref(null)

const filteredFactures = computed(() => {
  return factures.value.filter(f => {
    // 1. Recherche texte 100% sécurisée
    const q = search.value.toLowerCase()
    const num = f.numero ? f.numero.toLowerCase() : ''
    const cli = f.client ? f.client.toLowerCase() : ''
    const matchSearch = !q || num.includes(q) || cli.includes(q)
    
    // 2. Recherche par état
    let matchEtat = true
    if (filterEtat.value) {
      matchEtat = f.etat === filterEtat.value
    }
    
    return matchSearch && matchEtat
  })
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function getEtatClass(etat) {
  const map = {
    'Payée': 'status-success',
    'Partielle': 'status-warning',
    'Ouverte': 'status-info',
    'Brouillon': 'status-neutral',
    'En retard': 'status-danger'
  }
  return map[etat] || 'status-neutral'
}

function isOverdue(date) {
  if (!date) return false
  return new Date(date) < new Date()
}

async function handleDelete(id) {
  itemToDelete.value = id
  showConfirm.value = true
}

async function confirmDelete() {
  if (!itemToDelete.value) return
  showConfirm.value = false
  loading.value = true
  try {
    const { data } = await api.delete(`/factures/${itemToDelete.value}`)
    factures.value = factures.value.filter(f => f.id !== itemToDelete.value)
    toast.success(data.message || 'Facture supprimée.')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de la suppression.')
  } finally {
    loading.value = false
    itemToDelete.value = null
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/factures')
    const rawFactures = data.data || data || []
    
    factures.value = rawFactures.map(f => {
      // 1. On calcule le reste à payer dynamiquement
      const reste = parseFloat(f.montant_restant ?? (f.total_ttc - (f.montant_regle || 0)))
      
      // 2. On détermine le statut financier exact
      let statutCalcule = 'Ouverte'
      if (parseInt(f.est_reglee) === 1 || reste <= 0) {
        statutCalcule = 'Payée'
      } else if (parseFloat(f.montant_regle) > 0) {
        statutCalcule = 'Partielle'
      } else if (isOverdue(f.date_echeance)) {
        statutCalcule = 'En retard'
      } else if (!f.numero || f.numero === 'Brouillon') {
        statutCalcule = 'Brouillon'
      }

      return {
        ...f, 
        // Récupération sécurisée du nom du client
        client: f.client?.societe || f.client?.display_name || f.client_societe || 'Client Inconnu', 
        etat: f.etat?.libelle || statutCalcule,
        reste: reste
      }
    })
  } catch (error) {
    console.error('Erreur:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.facture-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #2563EB;
  --c-accent-bg: #EEF4FF;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-primary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: 8px;
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: transform .2s;
}
.btn-primary-custom:hover { transform: translateY(-1px); }

/* ─── Hero ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.billing-theme { background: linear-gradient(135deg, #4338CA, #1E1B4B); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #4338CA; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #4338CA; border-radius: 50%; }

/* ─── Filters ─── */
.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.search-wrapper {
  flex: 1; display: flex; align-items: center; gap: 12px; background: var(--c-bg);
  padding: 0 16px; border-radius: 8px;
}
.search-wrapper input { flex: 1; padding: 12px 0; border: none; background: transparent; outline: none; font-size: .9rem; }
.filter-select { padding: 10px 14px; border-radius: 8px; border: 1.5px solid #D5D9E2; font-size: .85rem; font-weight: 600; outline: none; }

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700; color: #4338CA; background: #EEF2FF; padding: 4px 8px; border-radius: 6px; }
.client-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.amount-cell { font-size: .95rem; font-weight: 800; color: var(--c-text); }
.currency { font-size: .65rem; opacity: .7; margin-left: 2px; }

.status-pill { padding: 4px 12px; border-radius: 100px; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
.status-success { background: #DCFCE7; color: #166534; }
.status-danger { background: #FEE2E2; color: #991B1B; }
.status-warning { background: #FEF3C7; color: #92400E; }
.status-info { background: #E0F2FE; color: #0369A1; }
.status-neutral { background: #F1F5F9; color: #475569; }

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); transform: translateY(-1px); }
.action-btn.delete:hover { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.text-danger { color: #DC2626; }
.font-black { font-weight: 900; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>