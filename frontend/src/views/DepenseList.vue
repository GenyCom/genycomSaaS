<template>
  <div class="depense-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement des dépenses…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Finances</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Dépenses & Charges</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/depenses/new" class="btn-primary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Saisir une Dépense</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar expense-theme">
        <span>DP</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Frais Généraux
        </div>
        <h1 class="hero-name">Gestion des Dépenses</h1>
        <p class="hero-sub">Total enregistré : <strong>{{ calculateTotal() }}</strong></p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="filters.search" type="text" placeholder="Rechercher par libellé..." />
      </div>
      <div class="filter-group">
        <select v-model="filters.statut" class="filter-select">
          <option value="">Tous les statuts</option>
          <option value="paye">Payé</option>
          <option value="en_attente">À payer</option>
        </select>
      </div>
    </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Catégorie & Détails</th>
              <th>Référence</th>
              <th class="text-right">Montant TTC</th>
              <th class="text-center">Statut</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in filteredDepenses" :key="d.id" class="table-row">
              <td>{{ formatDate(d.date_depense) }}</td>
              
              <td class="client-cell">
                <span class="category-badge">{{ d.categorie?.libelle || 'Général' }}</span>
                <div class="client-name" style="margin-top: 4px;">{{ d.libelle }}</div>
              </td>
              
              <td><span v-if="d.code" class="text-xs font-mono bg-slate-100 px-2 py-1 rounded">{{ d.code }}</span><span v-else>—</span></td>
              
              <td class="text-right">
                <div class="amount-cell text-expense">
                  {{ formatMoney(d.montant) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-center">
                <span v-if="d.etat_id === 2" class="status-pill-dynamic" style="background-color: #d1fae5; color: #047857;">
                  Payé
                </span>
                <span v-else class="status-pill-dynamic" style="background-color: #fef3c7; color: #b45309;">
                  À payer
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/depenses/${d.id}`" class="action-btn view" title="Détails">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button @click="handleDelete(d.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredDepenses.length === 0 && !loading">
              <td colspan="6" class="empty-row">
                <div class="empty-content">
                  <p>Aucune dépense ne correspond à vos critères.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer la Dépense"
      message="Êtes-vous sûr de vouloir supprimer cette charge ? Cette action est irréversible et modifiera vos statistiques financières."
      confirmText="Supprimer définitivement"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../services/api.js'
import { toast } from '../services/toastService'
import ConfirmModal from '../components/shared/ConfirmModal.vue'

const depenses = ref([])
const loading = ref(true)
const filters = reactive({ search: '', statut: '' })

const showConfirm = ref(false)
const itemToDelete = ref(null)

const filteredDepenses = computed(() => {
  return depenses.value.filter(d => {
    // Filtre sur le libellé (la description) ou le code
    const matchSearch = !filters.search || 
      d.libelle?.toLowerCase().includes(filters.search.toLowerCase()) ||
      d.code?.toLowerCase().includes(filters.search.toLowerCase())
    
    let matchStatut = true
    // Utilisation de etat_id pour le filtre de statut
    if (filters.statut === 'paye') matchStatut = d.etat_id === 2
    if (filters.statut === 'en_attente') matchStatut = d.etat_id !== 2
      
    return matchSearch && matchStatut
  })
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Nouvelle fonction pour nettoyer la date ISO renvoyée par Laravel
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
  // Calcul sur le champ 'montant' de la base de données
  const sum = depenses.value.reduce((acc, d) => acc + (parseFloat(d.montant) || 0), 0)
  return formatMoney(sum) + ' DH'
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
    await api.delete(`/depenses/${itemToDelete.value}`)
    depenses.value = depenses.value.filter(d => d.id !== itemToDelete.value)
    toast.success('Dépense supprimée.')
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
    const { data } = await api.get('/depenses')
    // Adapter selon la structure de ta pagination Laravel (data.data ou juste data)
    depenses.value = data.data || data || []
  } catch (error) {
    console.error('Erreur API:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens issus de DevisList ─── */
.depense-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #2563EB;
  --c-accent-bg: #EEF4FF; --c-danger: #E11D48;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
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
.expense-theme { background: linear-gradient(135deg, #E11D48, #BE123C); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-danger); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-danger); border-radius: 50%; }
.hero-sub { color: var(--c-muted); }

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
.filter-select { padding: 10px 14px; border-radius: 8px; border: 1.5px solid #D5D9E2; font-size: .85rem; font-weight: 600; outline: none; color: var(--c-text); background: #fff;}

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; color: var(--c-text); font-size: 0.9rem;}
.table-row:hover { background: #F9FAFB; }

.category-badge { font-size: .7rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px; text-transform: uppercase; }
.client-name { font-size: .95rem; font-weight: 700; color: var(--c-text); }
.amount-cell { font-size: .95rem; font-weight: 800; }
.text-expense { color: var(--c-danger); }
.currency { font-size: .65rem; opacity: .7; margin-left: 2px; }

.status-pill-dynamic { 
  display: inline-block;
  padding: 4px 12px; 
  border-radius: 100px; 
  font-size: .72rem; 
  font-weight: 800; 
  text-transform: uppercase;
}

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all 0.2s;}
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn.delete:hover { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>