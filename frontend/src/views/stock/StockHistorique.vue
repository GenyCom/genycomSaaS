<template>
  <div class="stock-history-view animate-fade-in">
    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/stock" class="back-btn" title="Retour au stock">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Logistique</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-parent">État du Stock</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">Historique</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-secondary-custom" @click="exportCSV">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          <span>Exporter CSV</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar stock-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Traçabilité des Flux
        </div>
        <h1 class="hero-name">Historique des Mouvements</h1>
        <p class="hero-sub" v-if="stock">
          Produit : <strong>{{ stock.produit?.designation }}</strong> 
          <span class="mx-2">|</span> 
          Dépôt : <strong>{{ stock.entrepot?.nom }}</strong>
        </p>
        <p class="hero-sub" v-else>Chargement de la ligne de stock...</p>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
      <p>Chargement des mouvements...</p>
    </div>

    <div v-else class="table-card">
      <div class="table-container-custom">
        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 15%">Date & Heure</th>
              <th style="width: 15%">Type de Flux</th>
              <th style="width: 15%">Réf. Document</th>
              <th style="width: 30%">Libellé / Description</th>
              <th style="width: 15%" class="text-right">Quantité</th>
              <th style="width: 10%">Acteur</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="mvt in mouvements" :key="mvt.id" class="table-row">
              <td>{{ formatDate(mvt.created_at) }}</td>
              <td>
                <span class="status-pill" :class="getTypeClass(mvt.type_mouvement)">
                  {{ formatType(mvt.type_mouvement).toUpperCase() }}
                </span>
              </td>
              <td>
                <span class="code-badge mono">{{ mvt.document_type }} #{{ mvt.document_id || '—' }}</span>
              </td>
              <td class="font-medium">{{ mvt.libelle || formatType(mvt.type_mouvement) }}</td>
              <td class="text-right">
                <div class="amount-cell" :class="isPositive(mvt.type_mouvement) ? 'text-success' : 'text-danger'">
                  <span class="font-black">{{ isPositive(mvt.type_mouvement) ? '+' : '-' }}{{ mvt.quantite }}</span>
                </div>
              </td>
              <td class="text-muted text-sm">
                {{ mvt.auteur ? `${mvt.auteur.nom} ${mvt.auteur.prenom || ''}` : 'Système' }}
              </td>
            </tr>
            <tr v-if="mouvements.length === 0">
              <td colspan="6" class="empty-row">
                <div class="empty-content">
                  <p>Aucun mouvement enregistré pour cet article.</p>
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
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const loading = ref(true)
const stock = ref(null)
const mouvements = ref([])

const fetchData = async () => {
  loading.value = true
  try {
    const stockId = route.params.id
    const { data } = await api.get(`/stock/${stockId}`)
    stock.value = data.stock
    mouvements.value = data.mouvements || []
  } catch (error) {
    console.error('Erreur chargement historique:', error)
  } finally {
    loading.value = false
  }
}

function formatDate(dateString) {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleString('fr-FR')
}

function formatType(type) {
  const types = {
    entree_achat: 'Achat',
    sortie_vente: 'Vente',
    ajustement_positif: 'Ajustement (+)',
    ajustement_negatif: 'Ajustement (-)',
    transfert_in: 'Transfert IN',
    transfert_out: 'Transfert OUT',
    entree_retour: 'Retour Vente',
    sortie_retour: 'Retour Achat'
  }
  return types[type] || type
}

function isPositive(type) {
  return ['entree_achat', 'entree_retour', 'ajustement_positif', 'transfert_in'].includes(type)
}

function getTypeClass(type) {
  if (isPositive(type)) return 'status-success'
  return 'status-danger'
}

function exportCSV() {
  console.log('Exportation de l\'historique...')
}

onMounted(fetchData)
</script>

<style scoped>
/* ─── Design Tokens (Teal / Stock) ─── */
.stock-history-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #0D9488;
  --c-accent-bg: #F0FDFA;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; 
}

.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn { width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #D5D9E2; background: #fff; display: flex; align-items: center; justify-content: center; color: var(--c-muted); text-decoration: none; transition: all 0.2s; }
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); }

.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-secondary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px;
  background: #fff; color: var(--c-muted); border: 1.5px solid #D5D9E2; border-radius: 8px;
  font-size: .85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-secondary-custom:hover { background: var(--c-bg); border-color: var(--c-muted); }

.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 16px; border: 1px solid var(--c-border);
  margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.stock-theme { background: linear-gradient(135deg, #0D9488, #0F766E); color: #fff; }
.hero-avatar { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: #0D9488; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: #0D9488; border-radius: 50%; }
.hero-sub { color: var(--c-muted); font-size: 0.9rem; margin-top: 4px; }

.table-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
.saas-table { width: 100%; border-collapse: collapse; text-align: left; }
.saas-table th { background: #F9FAFB; padding: 14px 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 16px 20px; border-bottom: 1px solid var(--c-border); vertical-align: middle; }
.table-row:hover { background: #F9FAFB; }

.code-badge { font-family: 'JetBrains Mono', monospace; font-size: .72rem; font-weight: 700; color: #0D9488; background: #F0FDFA; padding: 2px 6px; border-radius: 4px; }
.status-pill { padding: 4px 10px; border-radius: 100px; font-size: .65rem; font-weight: 800; }
.status-success { background: #DCFCE7; color: #166534; }
.status-danger { background: #FEE2E2; color: #991B1B; }

.amount-cell { font-family: 'JetBrains Mono', monospace; font-size: 1rem; }
.text-success { color: #059669; }
.text-danger { color: #DC2626; }
.font-black { font-weight: 900; }
.font-medium { font-weight: 500; }
.mono { font-family: 'JetBrains Mono', monospace; }

.empty-row { padding: 60px 0 !important; text-align: center; color: var(--c-muted); }

.loading-state { padding: 80px; text-align: center; color: var(--c-muted); }
.loader-ring { width: 40px; height: 40px; position: relative; margin: 0 auto 16px; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>