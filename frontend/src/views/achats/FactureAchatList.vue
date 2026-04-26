<template>
  <div class="facture-achat-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement des factures d'achat…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Achats</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Factures d'Achat</span>
        </div>
      </div>
      <div class="topbar-actions">
        <!-- Optional: New manually created invoice button -->
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar fa-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Facturation Fournisseur
        </div>
        <h1 class="hero-name">Factures d'Achat</h1>
        <p class="hero-sub">Gestion des factures reçues de vos fournisseurs et suivi des paiements.</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par N° facture, fournisseur..." @input="fetchData" />
      </div>
      <div class="filter-group">
        <select v-model="filtre" class="filter-select" @change="fetchData">
          <option value="">Tous les statuts</option>
          <option value="brouillon">Brouillon</option>
          <option value="valide">Validées</option>
          <option value="paye">Payées</option>
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
              <th>Échéance</th>
              <th>Fournisseur</th>
              <th class="text-right">Montant TTC</th>
              <th class="text-right">Reste à payer</th>
              <th class="text-center">Statut</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in items" :key="f.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ f.numero }}</span>
              </td>
              <td>{{ formatDate(f.date_facture) }}</td>
              <td>{{ formatDate(f.date_echeance) }}</td>
              <td class="supplier-cell">
                <div class="supplier-name">{{ f.fournisseur?.societe || 'Fournisseur inconnu' }}</div>
              </td>
              <td class="text-right mono font-medium">{{ formatMoney(f.montant_ttc) }}</td>
              <td class="text-right">
                <div class="amount-cell" :class="{'text-danger font-black': f.statut !== 'paye' && parseFloat(f.reste_a_payer) > 0}">
                  {{ formatMoney(f.statut === 'paye' ? 0 : f.reste_a_payer) }}
                  <span class="currency">DH</span>
                </div>
              </td>
              <td class="text-center">
                <span class="status-pill" :class="getStatutClass(f)">
                  {{ (f.statut || 'brouillon').toUpperCase() }}
                </span>
              </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/factures-achats/${f.id}`" class="action-btn view" title="Ouvrir">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button @click="handleDelete(f.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="items.length === 0 && !loading">
              <td colspan="8" class="empty-row">
                <div class="empty-content">
                  <p>Aucune facture d'achat répertoriée.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="pagination-container">
        <button v-for="p in totalPages" :key="p" 
                class="page-link" :class="{ active: p === page }" 
                @click="changePage(p)">
          {{ p }}
        </button>
      </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer la Facture d'Achat"
      message="Êtes-vous sûr de vouloir supprimer cette facture fournisseur ?"
      confirmText="Supprimer la facture"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const items = ref([])
const search = ref('')
const filtre = ref('')
const page = ref(1)
const totalPages = ref(1)
const loading = ref(true)

const showConfirm = ref(false)
const itemToDelete = ref(null)

function formatDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '—' }
function formatMoney(v) { return parseFloat(v || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }

function getStatutClass(f) {
  const s = f.statut || 'brouillon'
  if (s === 'paye') return 'status-success'
  if (s === 'valide') return 'status-warning'
  return 'status-neutral'
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await api.get('/factures-achats', { 
      params: { search: search.value, statut: filtre.value, page: page.value, per_page: 20 } 
    })
    
    // On affiche les données brutes dans la console pour voir ce qui arrive
    console.log("✅ Réponse API Factures Achats :", data);
    
    items.value = data.data || (Array.isArray(data) ? data : [])
    totalPages.value = data.last_page || 1
    
  } catch (e) { 
    console.error('❌ Erreur API Factures Achat:', e);
    toast.error("Erreur de chargement ! " + (e.response?.data?.message || ''));
  } finally {
    loading.value = false
  }
}

function changePage(p) {
  page.value = p
  fetchData()
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
    const { data } = await api.delete(`/factures-achats/${itemToDelete.value}`)
    items.value = items.value.filter(f => f.id !== itemToDelete.value)
    toast.success(data.message || 'Facture d\'achat supprimée.')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Erreur lors de la suppression.')
  } finally {
    loading.value = false
    itemToDelete.value = null
  }
}

onMounted(fetchData)
</script>

<style scoped>
.facture-achat-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #059669; /* Emerald for Procurement */
  --c-accent-bg: #ECFDF5;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.fa-theme { background: linear-gradient(135deg, #059669, #10B981); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }

.filters-card {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 16px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.search-wrapper { flex: 1; display: flex; align-items: center; gap: 12px; background: var(--c-bg); padding: 0 16px; border-radius: 8px; }
.search-wrapper input { flex: 1; padding: 12px 0; border: none; background: transparent; outline: none; font-size: .9rem; }
.filter-select { padding: 10px 14px; border-radius: 8px; border: 1.5px solid #D5D9E2; font-size: .85rem; font-weight: 600; outline: none; cursor: pointer; }

.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700; color: var(--c-accent); background: var(--c-accent-bg); padding: 4px 8px; border-radius: 6px; }
.supplier-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }
.amount-cell { font-size: .95rem; font-weight: 800; color: var(--c-text); }
.currency { font-size: .65rem; opacity: .7; margin-left: 2px; }

.status-pill { padding: 4px 12px; border-radius: 100px; font-size: .68rem; font-weight: 800; text-transform: uppercase; }
.status-success { background: #DCFCE7; color: #166534; }
.status-warning { background: #FEF3C7; color: #92400E; }
.status-neutral { background: #F1F5F9; color: #475569; }

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); transform: translateY(-1px); }
.action-btn.delete:hover { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

.pagination-container { display: flex; justify-content: center; padding: 1.5rem; gap: 8px; border-top: 1px solid var(--c-border); }
.page-link { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; font-size: .85rem; font-weight: 600; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.page-link.active { background: var(--c-accent); color: #fff; border-color: var(--c-accent); box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2); }

.text-danger { color: #DC2626 !important; }
.font-black { font-weight: 900 !important; }
.mono { font-family: 'JetBrains Mono', monospace; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
