<template>
  <div class="dashboard-view">
    
    <div class="dashboard-top-nav">
      <div class="time-filters">
        <button v-for="f in ['Ce mois', 'Trimestre', 'Année']" :key="f" 
                :class="['filter-pill', { active: currentFilter === f }]"
                @click="currentFilter = f">
          {{ f }}
        </button>
      </div>
      <div class="header-right-actions">
        <span class="last-sync">Dernière mise à jour : {{ lastSync }}</span>
        <button class="btn-refresh-clean" @click="refreshData" :class="{ spinning: loading }" title="Actualiser les données">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </button>
      </div>
    </div>

    <!-- On vérifie que authStore.user existe, car l'entreprise peut être directement sur l'utilisateur ou chargée plus tard -->
    <div class="hero-header dashboard-hero">
      <div class="hero-avatar company-logo">
        <!-- Affichage du logo s'il existe -->
        <img v-if="authStore.entreprise?.logo_path" :src="authStore.entreprise.logo_path" alt="Logo Entreprise" />
        <!-- Sinon, on affiche la première lettre de la raison sociale, et si ça n'existe pas, une lettre par défaut "S" -->
        <span v-else>{{ authStore.entreprise?.raison_sociale?.charAt(0) || 'S' }}</span>
      </div>
      <div class="hero-meta">
        <!-- On affiche la raison sociale, ou un texte de chargement/défaut -->
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ authStore.entreprise?.raison_sociale || 'Mon Entreprise' }}
        </div>
        <h1 class="hero-name">
          Bonjour, {{ authStore.user?.prenom || authStore.user?.name || 'Admin' }}
          <span v-if="!connectionError" style="color:#059669; font-size:1rem;">(Connecté)</span>
          <span v-else style="color:#DC2626; font-size:1rem;">(Problème de connexion)</span>
          👋
        </h1>
        <p class="hero-sub">Résumé de performance pour la période sélectionnée.</p>
      </div>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card accent">
        <div class="kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg></div>
        <div class="kpi-content">
          <p class="kpi-label">Chiffre d'Affaires</p>
          <div class="value-row">
            <p class="kpi-value">{{ formatMoney(kpis.ca_mois) }}</p>
            <span :class="['trend-badge', (kpis.ca_trend || 0) >= 0 ? 'pos' : 'neg']">
              {{ (kpis.ca_trend || 0) > 0 ? '+' : '' }}{{ kpis.ca_trend || 0 }}%
            </span>
          </div>
        </div>
      </div>

      <div class="kpi-card" style="background: #FEF2F2; border-color: #FEE2E2;">
        <div class="kpi-icon" style="background: #FEE2E2; color: #E11D48;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
        </div>
        <div class="kpi-content">
          <p class="kpi-label" style="color: #9F1239;">Dépenses</p>
          <p class="kpi-value" style="color: #BE123C;">{{ formatMoney(kpis.depenses_mois) }}</p>
          <p class="kpi-sub-text" style="color: #9F1239;">Charges de fonctionnement</p>
        </div>
      </div>

      <div class="kpi-card" style="background: #F0FDF4; border-color: #A7F3D0;">
        <div class="kpi-icon" style="background: #D1FAE5; color: #059669;">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-content">
          <p class="kpi-label" style="color: #065F46;">CA Encaissé</p>
          <p class="kpi-value" style="color: #064E3B;">{{ formatMoney(kpis.ca_encaisse) }}</p>
          <p class="kpi-sub-text" style="color: #047857;">Trésorerie générée</p>
        </div>
      </div>

      <!-- Bénéfice Net (après CA Encaissé) -->
      <div class="kpi-card profit-card" :class="(kpis.benefice_net !== undefined ? kpis.benefice_net : ((kpis.ca_mois || 0) - (kpis.depenses_mois || 0))) >= 0 ? 'profit-pos' : 'profit-neg'">
        <div class="kpi-icon profit-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
            <polyline points="17 6 23 6 23 12"></polyline>
          </svg>
        </div>
        <div class="kpi-content">
          <p class="kpi-label profit-label">Bénéfice Net</p>
          <div class="value-row">
            <p class="kpi-value profit-value">{{ formatMoney(kpis.benefice_net !== undefined ? kpis.benefice_net : ((kpis.ca_mois || 0) - (kpis.depenses_mois || 0))) }}</p>
            <span v-if="kpis.benefice_marge_pct !== undefined" :class="['trend-badge', (kpis.benefice_net || 0) >= 0 ? 'pos' : 'neg']">
              {{ kpis.benefice_marge_pct }}%
            </span>
          </div>
          <p class="kpi-sub-text profit-sub">Rentabilité de la période</p>
        </div>
      </div>
    </div>

    <!-- Niveau 2 : Impayés Clients & Impayés Fournisseurs (Ligne 2) -->
    <div class="kpi-secondary-row">
      <router-link to="/factures" class="kpi-strip-card card-impayes-client" title="Voir les factures clients">
        <div class="strip-icon-wrapper warning">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3v12"/>
            <path d="m8 11 4 4 4-4"/>
            <path d="M4 21h16"/>
          </svg>
        </div>
        <div class="strip-main">
          <div class="strip-header">
            <span class="strip-tag tag-warning">Créances Clients</span>
            <span class="strip-count">{{ kpis.factures_impayees?.count || 0 }} facture(s) en attente</span>
          </div>
          <div class="strip-body">
            <span class="strip-title">Impayés Clients</span>
            <span class="strip-amount val-danger">{{ formatMoney(kpis.factures_impayees?.montant ?? kpis.encours_clients) }}</span>
          </div>
        </div>
        <div class="strip-arrow">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </router-link>

      <router-link to="/factures-achats" class="kpi-strip-card card-impayes-fournisseur" title="Voir les factures d'achats">
        <div class="strip-icon-wrapper info">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 21V9"/>
            <path d="m16 13-4-4-4 4"/>
            <path d="M4 3h16"/>
          </svg>
        </div>
        <div class="strip-main">
          <div class="strip-header">
            <span class="strip-tag tag-purple">Dettes Fournisseurs</span>
            <span class="strip-count">{{ kpis.impayes_fournisseurs?.count || 0 }} facture(s) d'achat à payer</span>
          </div>
          <div class="strip-body">
            <span class="strip-title">Impayés Fournisseurs</span>
            <span class="strip-amount val-purple">{{ formatMoney(kpis.impayes_fournisseurs?.montant ?? kpis.encours_fournisseurs) }}</span>
          </div>
        </div>
        <div class="strip-arrow">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </router-link>
    </div>

    <div class="dashboard-grid-main">
      <section class="info-card">
        <div class="card-header">
          <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="18" y="3" width="4" height="18"/><rect x="10" y="8" width="4" height="13"/><rect x="2" y="13" width="4" height="8"/></svg></div>
          <h3>Performance des Revenus</h3>
        </div>
        <div class="card-body chart-body">
          <div class="premium-chart">
            <div class="chart-grid">
              <div class="grid-line"></div>
              <div class="grid-line"></div>
              <div class="grid-line"></div>
              <div class="grid-line"></div>
            </div>
            <div class="bar-chart-container">
              <div v-for="(item, idx) in caMensuel" :key="idx" class="chart-column">
                <div class="bar-wrapper">
                  <span class="bar-value" :class="{ 'zero-value': item.ca === 0 }">
                    {{ formatBarValue(item.ca) }}
                  </span>
                  <div class="bar" :class="{ 'empty-bar': item.ca === 0 }" :style="{ height: getBarHeight(item.ca) }"></div>
                </div>
                <span class="bar-label">{{ item.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="info-card">
        <div class="card-header">
          <div class="card-header-icon bank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg></div>
          <h3>Répartition du Stock</h3>
        </div>
        <div class="card-body donut-body">
          <div class="donut-container">
            <svg viewBox="0 0 36 36" class="circular-chart">
              <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
              <path class="circle orange" :stroke-dasharray="`${stockStats.percent_critical || 0}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
              <path class="circle green" :stroke-dasharray="`${stockStats.percent_sufficient || 0}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
            <div class="donut-center">
              <span class="total-label">Articles</span>
              <span class="total-value">{{ stockStats.total || 0 }}</span>
            </div>
          </div>
          <div class="donut-legend">
            <div class="legend-item"><span class="dot green"></span> Suffisant ({{ stockStats.percent_sufficient || 0 }}%)</div>
            <div class="legend-item"><span class="dot orange"></span> Critique ({{ stockStats.percent_critical || 0 }}%)</div>
            <div class="legend-item"><span class="dot red"></span> Rupture ({{ stockStats.percent_rupture || 0 }}%)</div>
          </div>
        </div>
      </section>
    </div>

    <div class="dashboard-grid-secondary">
      <section class="info-card">
        <div class="card-header">
          <div class="card-header-icon contact"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg></div>
          <h3>Top Ventes (Volume)</h3>
        </div>
        <div class="card-body">
          <div v-if="topVentes.length === 0" class="empty-mini">Aucune vente enregistrée</div>
          <div v-for="(p, idx) in topVentes.slice(0, 4)" :key="idx" class="performance-item">
            <div class="perf-info">
              <p class="perf-title">{{ p.designation }}</p>
              <p class="perf-sub">{{ p.reference }} · <strong>{{ p.qte_vendue }}</strong> vendus</p>
            </div>
            <span class="perf-amount success">{{ formatMoney(p.ca_ttc) }}</span>
          </div>
        </div>
      </section>

      <section class="info-card">
        <div class="card-header">
          <div class="card-header-icon accent"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
          <h3>Meilleurs Clients (Revenus)</h3>
        </div>
        <div class="card-body">
          <div v-if="topClients.length === 0" class="empty-mini">Aucun client actif</div>
          <div v-for="(c, idx) in topClients.slice(0, 4)" :key="idx" class="performance-item">
            <div class="perf-info">
              <p class="perf-title">{{ c.societe }}</p>
              <p class="perf-sub">{{ c.nb_factures }} facture(s) émise(s)</p>
            </div>
            <span class="perf-amount accent">{{ formatMoney(c.ca_total) }}</span>
          </div>
        </div>
      </section>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'

const authStore = useAuthStore()
const kpis = ref({})
const caMensuel = ref([])
const topVentes = ref([])
const topClients = ref([])
const currentFilter = ref('Ce mois')
const lastSync = ref(new Date().toLocaleTimeString())
const loading = ref(false)
const connectionError = ref(false)

const stockStats = ref({ sufficient: 0, critical: 0, rupture: 0, total: 0 })

function formatMoney(val) {
  if (val === undefined || val === null) return '0,00 DH';
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

function formatCompact(val) {
  const num = parseFloat(val) || 0
  if (num >= 1000) return (num / 1000).toFixed(0) + 'K'
  return num.toFixed(0)
}

function formatBarValue(val) {
  const num = parseFloat(val) || 0
  if (num === 0) return '0 DH'
  return num.toLocaleString('fr-FR', {
    minimumFractionDigits: num % 1 === 0 ? 0 : 2,
    maximumFractionDigits: 2
  }) + ' DH'
}

const maxCA = computed(() => Math.max(...caMensuel.value.map(i => parseFloat(i.ca) || 0), 1))
function getBarHeight(val) {
  if (!val || val === 0) return '4px'; // Petite barre grise si aucun CA
  const max = Math.max(...caMensuel.value.map(i => parseFloat(i.ca) || 0), 1);
  const pct = (parseFloat(val) / max) * 75; // Limité à 75% pour laisser l'espace au libellé de montant au-dessus
  return `${Math.max(pct, 2)}%`; 
}

async function refreshData() {
  loading.value = true
  connectionError.value = false
  try {
    const config = { params: { periode: currentFilter.value } }
    
    const [kpiRes, caRes, tvRes, tcRes, ssRes] = await Promise.all([
      api.get('/dashboard/kpis', config),
      api.get('/dashboard/ca-mensuel', config), 
      api.get('/dashboard/top-ventes', config),
      api.get('/dashboard/top-clients', config),
      api.get('/dashboard/stock-stats')
    ])
    
    kpis.value = kpiRes.data?.data || kpiRes.data || {}
    topVentes.value = tvRes.data?.data || tvRes.data || []
    topClients.value = tcRes.data?.data || tcRes.data || []
    stockStats.value = ssRes.data || { sufficient: 0, critical: 0, rupture: 0, total: 0 }
    lastSync.value = new Date().toLocaleTimeString()

    // --- NOUVELLE LOGIQUE DU GRAPHIQUE (Les 6 derniers mois forcés) ---
    const backendCA = caRes.data?.data || caRes.data || [];
    const processedCA = [];
    const today = new Date();
    
    for (let i = 5; i >= 0; i--) {
      const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
      
      // CORRECTION : Formatage manuel YYYY-MM pour éviter le bug du fuseau horaire (UTC)
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const moisFormat = `${yyyy}-${mm}`; 
      
      // Nom du mois en français (ex: "janv", "févr")
      let moisLabel = d.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', ''); 

      const found = backendCA.find(item => item.mois === moisFormat);
      processedCA.push({
        label: moisLabel,
        ca: found ? parseFloat(found.ca) : 0
      });
    }
    caMensuel.value = processedCA;
    
  } catch (e) {
    console.error("❌ Erreur de chargement du Dashboard :", e)
    connectionError.value = true
  } finally {
    loading.value = false
  }
}

watch(currentFilter, () => {
  refreshData()
})

onMounted(refreshData)
</script>

<style scoped>
/* --- Design Tokens & Base --- */
.dashboard-view {
  padding: 8px 24px 40px; 
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #2563EB;
  --c-success: #16A34A; --c-danger: #DC2626; --c-warning: #EA580C;
  background: var(--c-bg); min-height: 100vh;
}

/* --- Top Navigation --- */
.dashboard-top-nav {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 8px; 
}
.filter-pill { 
  padding: 5px 14px; border: 1.5px solid var(--c-border); border-radius: 20px; 
  background: #fff; font-size: .78rem; font-weight: 600; cursor: pointer; color: var(--c-muted);
  margin-right: 6px; transition: all .2s;
}
.filter-pill.active { background: var(--c-accent); color: #fff; border-color: var(--c-accent); }

.header-right-actions { display: flex; align-items: center; gap: 10px; }
.last-sync { font-size: .72rem; color: var(--c-muted); }

/* Actualiser sans bordure */
.btn-refresh-clean {
  background: transparent !important;
  border: none !important;
  outline: none !important;
  padding: 6px;
  cursor: pointer;
  color: var(--c-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, color 0.2s;
}
.btn-refresh-clean:hover { color: var(--c-accent); transform: scale(1.1); }

/* --- Hero Section --- */
.dashboard-hero {
  display: flex; align-items: center; gap: 16px; background: #fff;
  padding: 12px 20px; margin-bottom: 12px;
  border-radius: 12px; border: 1px solid var(--c-border);
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.company-logo { 
  width: 56px; 
  height: 56px; 
  border-radius: 10px; 
  background: var(--c-bg); 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  font-size: 1.3rem; 
  font-weight: 800; 
  overflow: hidden; /* Essentiel : empêche l'image de déborder des bords arrondis */
}

.company-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Remplira parfaitement le carré. Si le logo est coupé, change pour 'contain' */
}

.hero-meta { flex: 1; }
.hero-type-badge { font-size: .62rem; font-weight: 800; text-transform: uppercase; color: var(--c-accent); margin-bottom: 2px; display: flex; align-items: center; gap: 4px; }
.hero-type-badge .dot { width: 5px; height: 5px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--c-text); line-height: 1.1; }
.hero-sub { font-size: .78rem; color: var(--c-muted); margin: 2px 0 0; }

/* --- KPIs Grid --- */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 20px; }
.kpi-card { background: #fff; padding: 16px; border-radius: 12px; border: 1px solid var(--c-border); display: flex; gap: 12px; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
.kpi-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-card.accent .kpi-icon { background: #EEF4FF; color: var(--c-accent); }
.kpi-card.success .kpi-icon { background: #F0FDF4; color: var(--c-success); }
.kpi-card.warning .kpi-icon { background: #FFF7ED; color: var(--c-warning); }
.kpi-card.info .kpi-icon { background: #F1F5F9; color: #475569; }

.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--c-text); }
.value-row { display: flex; align-items: baseline; gap: 6px; }
.trend-badge { padding: 1px 5px; border-radius: 4px; font-size: .6rem; font-weight: 800; }
.trend-badge.pos { background: #DCFCE7; color: #166534; }
.trend-badge.neg { background: #FEE2E2; color: #991B1B; }
.kpi-sub-text { font-size: .68rem; color: var(--c-muted); margin: 2px 0 0; }

/* --- Bénéfice Net Card --- */
.kpi-card.profit-pos {
  background: #F0FDF4;
  border-color: #A7F3D0;
}
.kpi-card.profit-pos .profit-icon {
  background: #D1FAE5;
  color: #059669;
}
.kpi-card.profit-pos .profit-label {
  color: #065F46;
}
.kpi-card.profit-pos .profit-value {
  color: #064E3B;
}
.kpi-card.profit-pos .profit-sub {
  color: #047857;
}

.kpi-card.profit-neg {
  background: #FEF2F2;
  border-color: #FECACA;
}
.kpi-card.profit-neg .profit-icon {
  background: #FEE2E2;
  color: #DC2626;
}
.kpi-card.profit-neg .profit-label {
  color: #991B1B;
}
.kpi-card.profit-neg .profit-value {
  color: #7F1D1D;
}
.kpi-card.profit-neg .profit-sub {
  color: #991B1B;
}

/* --- Niveau 2 : Ligne des Impayés (Clients & Fournisseurs) --- */
.kpi-secondary-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

@media (max-width: 768px) {
  .kpi-secondary-row {
    grid-template-columns: 1fr;
  }
}

.kpi-strip-card {
  background: #ffffff;
  border: 1px solid var(--c-border);
  border-radius: 12px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.kpi-strip-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.kpi-strip-card.card-impayes-client {
  border-left: 4px solid #F59E0B;
}

.kpi-strip-card.card-impayes-client:hover {
  border-color: #F59E0B;
  background: #FFFDF8;
}

.kpi-strip-card.card-impayes-fournisseur {
  border-left: 4px solid #8B5CF6;
}

.kpi-strip-card.card-impayes-fournisseur:hover {
  border-color: #8B5CF6;
  background: #FAF9FF;
}

.strip-icon-wrapper {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.strip-icon-wrapper.warning {
  background: #FEF3C7;
  color: #D97706;
}

.strip-icon-wrapper.info {
  background: #EDE9FE;
  color: #7C3AED;
}

.strip-main {
  flex: 1;
  min-width: 0;
}

.strip-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 3px;
}

.strip-tag {
  font-size: 0.64rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  padding: 2px 7px;
  border-radius: 4px;
}

.tag-warning {
  background: #FFFBEB;
  color: #B45309;
  border: 1px solid #FDE68A;
}

.tag-purple {
  background: #F5F3FF;
  color: #6D28D9;
  border: 1px solid #DDD6FE;
}

.strip-count {
  font-size: 0.72rem;
  color: var(--c-muted);
  font-weight: 500;
}

.strip-body {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 8px;
}

.strip-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--c-text);
}

.strip-amount {
  font-size: 1.15rem;
  font-weight: 800;
  letter-spacing: -0.01em;
}

.val-danger {
  color: #DC2626;
}

.val-purple {
  color: #7C3AED;
}

.strip-arrow {
  color: #9CA3AF;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease, color 0.2s ease;
  flex-shrink: 0;
}

.kpi-strip-card:hover .strip-arrow {
  transform: translateX(3px);
  color: var(--c-text);
}

/* --- Main Layout Grids --- */
.dashboard-grid-main { display: grid; grid-template-columns: 1.8fr 1fr; gap: 16px; margin-bottom: 16px; }
.dashboard-grid-secondary { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-header { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-bottom: 1px solid var(--c-border); background: #F9FAFB; }
.card-header h3 { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 24px; height: 24px; border-radius: 5px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--c-border); color: var(--c-accent); }
.card-body { padding: 12px 16px; }

/* Donut Stock */
.donut-body { display: flex; align-items: center; justify-content: space-around; padding: 10px 0; }
.donut-container { position: relative; width: 110px; height: 110px; }
.circular-chart { display: block; max-width: 100%; max-height: 100%; }
.circle-bg { fill: none; stroke: #FEE2E2; stroke-width: 3.8; }
.circle { fill: none; stroke-width: 3.8; stroke-linecap: round; transition: stroke-dasharray 0.3s; }
.circle.green { stroke: #22C55E; } .circle.orange { stroke: #F97316; }
.donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
.total-label { display: block; font-size: .5rem; color: var(--c-muted); text-transform: uppercase; font-weight: 700; }
.total-value { font-size: 1rem; font-weight: 800; }
.donut-legend { display: flex; flex-direction: column; gap: 4px; }
.legend-item { font-size: .65rem; font-weight: 600; display: flex; align-items: center; gap: 5px; }
.dot { width: 6px; height: 6px; border-radius: 50%; }
.dot.green { background: #22C55E; } .dot.orange { background: #F97316; } .dot.red { background: #EF4444; }

/* Premium Bar Chart */
.chart-body { padding: 24px 24px 16px; }
.premium-chart { position: relative; height: 195px; }

.chart-grid { position: absolute; inset: 0 0 25px 0; display: flex; flex-direction: column; justify-content: space-between; z-index: 1; pointer-events: none; }
.grid-line { width: 100%; border-top: 1px dashed #E2E8F0; }

.bar-chart-container { position: relative; z-index: 2; display: flex; align-items: flex-end; justify-content: space-between; height: 100%; padding-bottom: 25px; }
.chart-column { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; height: 100%; justify-content: flex-end; }

.bar-wrapper { width: 100%; max-width: 44px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; position: relative; cursor: pointer; }

.bar-value {
  font-size: 0.67rem;
  font-weight: 700;
  color: #1E293B;
  white-space: nowrap;
  margin-bottom: 5px;
  letter-spacing: -0.01em;
  transition: transform 0.2s ease, color 0.2s ease;
  pointer-events: none;
}
.bar-value.zero-value {
  color: #94A3B8;
  font-weight: 500;
  font-size: 0.62rem;
}
.bar-wrapper:hover .bar-value {
  color: #2563EB;
  transform: translateY(-2px);
}

.bar { width: 100%; background: #3b82f6; border-radius: 6px 6px 0 0; transition: height 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2); }
.empty-bar { background: #F1F5F9; box-shadow: none; border-radius: 4px; }
.bar-wrapper:hover .bar { opacity: 0.85; transform: scaleY(1.02); transform-origin: bottom; }

.bar-label { font-size: 0.65rem; font-weight: 600; color: #64748B; position: absolute; bottom: 0; text-transform: capitalize; }

/* Items list (Top Ventes / Meilleurs Clients) */
.performance-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F1F5F9; }
.performance-item:last-child { border-bottom: none; }
.perf-title { font-size: .85rem; font-weight: 700; margin: 0; color: var(--c-text); }
.perf-sub { font-size: .72rem; color: var(--c-muted); margin: 2px 0 0; }
.perf-amount { font-size: .85rem; font-weight: 800; }
.perf-amount.success { color: var(--c-success); }
.perf-amount.accent { color: var(--c-accent); }
.empty-mini { text-align: center; color: var(--c-muted); font-style: italic; font-size: .8rem; padding: 10px; }

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.spinning { animation: spin 1s linear infinite; }
</style>