<template>
  <div class="bl-list-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
        <p class="loading-label">Chargement des expéditions…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Ventes</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Bons de Livraison</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/bons-livraison/new" class="btn-create bl-theme-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Nouveau BL</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar bl-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Logistique & Expéditions
        </div>
        <h1 class="hero-name">Bons de Livraison</h1>
        <p class="hero-sub"><strong>{{ bls.length }}</strong> bon(s) de livraison enregistré(s) dans le système.</p>
      </div>
    </div>

      <div class="search-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Rechercher par N° BL, client ou origine (BC/Devis)..." />
      </div>
      <div class="filter-group">
        <select v-model="filters.etat_id" class="filter-select">
          <option value="">Tous les états</option>
          <option v-for="s in etats" :key="s.id" :value="s.id">{{ s.libelle }}</option>
        </select>
      </div>

    <div class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Date Livraison</th>
              <th>Client</th>
              <th>Origine</th>
              <th class="text-center">État Livraison</th>
              <th class="text-center">Facturation</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in filteredBls" :key="b.id" class="table-row">
              <td>
                <span class="code-badge mono">{{ b.numero }}</span>
              </td>
              <td>{{ formatDate(b.date_livraison) }}</td>
              <td class="client-cell">
                <div class="client-name">{{ b.client?.societe || b.client?.display_name }}</div>
              </td>
              <td class="origin-cell">
                 <router-link v-if="b.facture" :to="`/factures/${b.facture.id}`" class="origin-tag facture">FAC: {{ b.facture.numero }}</router-link>
                 <router-link v-if="b.bon_commande" :to="`/bons-commande-client/${b.bon_commande.id}`" class="origin-tag">BC: {{ b.bon_commande.numero }}</router-link>
                 <router-link v-if="b.devis && !b.bon_commande" :to="`/devis/${b.devis.id}`" class="origin-tag secondary">DV: {{ b.devis.numero }}</router-link>
                 <span v-if="!b.facture && !b.bon_commande && !b.devis" class="text-muted">Saisie directe</span>
              </td>
               <td class="text-center">
                  <span class="status-pill saas-pill" :class="statutClass(b.statut)">
                    {{ statutLabel(b.statut) }}
                  </span>
               </td>
               <td class="text-center">
                  <span class="status-pill saas-pill" :class="b.facture_id ? 'status-success' : 'status-pending'">
                    {{ b.facture_id ? 'FACTURÉ' : 'À FACTURER' }}
                  </span>
               </td>
              <td class="text-right">
                <div class="actions-group">
                  <router-link :to="`/bons-livraison/${b.id}`" class="action-btn view" title="Détails">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </router-link>
                  <button @click="handleDelete(b.id)" class="action-btn delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredBls.length === 0 && !loading">
              <td colspan="6" class="empty-row">
                <div class="empty-content">
                  <p>Aucun bon de livraison trouvé.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le Bon de Livraison"
      message="Voulez-vous vraiment supprimer ce bon de livraison ? Cela annulera l'expédition."
      confirmText="Supprimer le BL"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const bls = ref([])
const etats = ref([])
const loading = ref(true)
const searchQuery = ref('')

const showConfirm = ref(false)
const itemToDelete = ref(null)
const filters = reactive({ etat_id: '' })

const filteredBls = computed(() => {
  let res = bls.value
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    res = res.filter(b => 
      b.numero?.toLowerCase().includes(q) || 
      (b.client?.societe || '').toLowerCase().includes(q) ||
      (b.bon_commande?.numero || '').toLowerCase().includes(q) ||
      (b.devis?.numero || '').toLowerCase().includes(q) ||
      (b.facture?.numero || '').toLowerCase().includes(q)
    )
  }

  if (filters.etat_id) {
    res = res.filter(b => b.etat_id == filters.etat_id)
  }

  return res
})

function statutLabel(statut) {
  const map = {
    'brouillon': 'Brouillon',
    'valide': 'Livré',
    'livre': 'Livré',
    'en_cours': 'En cours',
    'annule': 'Annulé',
    'partiel': 'Partiel',
  }
  return map[statut] || statut || 'Brouillon'
}

function statutClass(statut) {
  const map = {
    'brouillon': 'status-neutral',
    'valide': 'status-success',
    'livre': 'status-success',
    'en_cours': 'status-pending',
    'annule': 'status-danger',
    'partiel': 'status-warning',
  }
  return map[statut] || 'status-neutral'
}

async function fetchEtats() {
  try {
    // CORRECTION ICI : /etats au lieu de /etat-document
    const { data } = await api.get('/parametrage/referentiels/etats?type_document=bl')
    etats.value = data.data || data
  } catch (e) {
    console.error("Erreur lors de la récupération des états", e)
  }
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR')
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
    const { data } = await api.delete(`/bons-livraison/${itemToDelete.value}`)
    bls.value = bls.value.filter(b => b.id !== itemToDelete.value)
    toast.success(data.message || 'Bon de livraison supprimé.')
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
    fetchEtats()
    const { data } = await api.get('/bons-livraison')
    bls.value = data.data || data
  } catch (e) {
    console.error('Erreur lors du chargement des BL:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ─── Design Tokens ─── */
.bl-list-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #10B981;
  --c-accent-bg: #ECFDF5;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-actions { display: flex; gap: 10px; }
.btn-create { 
  display: flex; align-items: center; gap: 8px; padding: 8px 20px; 
  border-radius: 10px; font-weight: 700; font-size: .85rem; 
  text-decoration: none; transition: all .2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}
.bl-theme-btn { background: #10B981; color: #fff; }
.bl-theme-btn:hover { background: #059669; transform: translateY(-1px); }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

/* ─── Hero ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.bl-theme { background: linear-gradient(135deg, #10B981, #059669); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #10B981; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #10B981; border-radius: 50%; }

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

/* ─── Table ─── */
.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .78rem; font-weight: 700; color: #059669; background: #ECFDF5; padding: 4px 8px; border-radius: 6px; }
.client-name { font-size: .9rem; font-weight: 700; color: var(--c-text); }

.origin-cell { display: flex; flex-direction: column; gap: 4px; }
.origin-tag { font-size: .65rem; font-weight: 800; color: #4F46E5; background: #EEF2FF; padding: 2px 6px; border-radius: 4px; width: fit-content; text-decoration: none; transition: all .2s; }
.origin-tag:hover { filter: brightness(0.95); transform: translateY(-1px); }
.origin-tag.secondary { color: #2563EB; background: #EEF4FF; }
.origin-tag.facture { color: #7C3AED; background: #F5F3FF; }

.status-pill { padding: 4px 12px; border-radius: 100px; font-size: .68rem; font-weight: 800; text-transform: uppercase; border: 1px solid transparent; }
.saas-pill { background: #fefce8; color: #854d0e; border-color: #fef08a; }
.status-success { background: #DCFCE7; color: #166534; border-color: #bbf7d0; }
.status-pending { background: #fff7ed; color: #9a3412; border-color: #ffedd5; }
.status-neutral { background: #F1F5F9; color: #475569; border-color: #E2E8F0; }
.status-danger { background: #FEF2F2; color: #991B1B; border-color: #FECACA; }
.status-warning { background: #FFF7ED; color: #9A3412; border-color: #FED7AA; }

.status-badge-dynamic { padding: 4px 12px; border-radius: 100px; font-size: .68rem; font-weight: 800; border: 1px solid currentColor; }

.filter-select { padding: 8px 12px; border-radius: 8px; border: 1px solid var(--c-border); font-size: .85rem; outline: none; }

.actions-group { display: flex; gap: 8px; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); transform: translateY(-1px); }
.action-btn.delete:hover { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; display: flex; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>