<template>
  <div class="sa-dashboard-view">
    <!-- Hero Header -->
    <div class="hero-header sa-hero">
      <div class="hero-left">
        <div class="hero-avatar sa-theme">
          <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
        <div class="hero-meta">
          <div class="hero-type-badge">
            <span class="dot pulse"></span>
            Supervision Centrale &amp; Télémétrie
          </div>
          <h1 class="hero-name">Tableau de Bord SaaS</h1>
          <p class="hero-sub">
            Surveillance en temps réel des tenants, des sessions utilisateurs et télémétrie des erreurs HTTP 500.
          </p>
        </div>
      </div>
      <div class="hero-actions">
        <button class="btn-refresh" @click="fetchStats" :disabled="loading">
          <svg :class="{ spinning: loading }" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
          </svg>
          Actualiser
        </button>
        <span class="last-update" v-if="lastUpdated">Mis à jour à {{ lastUpdated }}</span>
      </div>
    </div>

    <!-- KPI Grid -->
    <div class="sa-kpis-grid mb-6">
      <!-- KPI 1 : Tenants Actifs (15 min) -->
      <div class="sa-kpi-card tenants-active">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Tenants Actifs (15m)</span>
          <div class="sa-kpi-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          </div>
        </div>
        <div class="sa-kpi-value">
          {{ metrics.active_tenants_15m }} <span class="sa-kpi-denom">/ {{ metrics.total_tenants }}</span>
        </div>
        <div class="sa-kpi-sub flex justify-between align-center">
          <span>Locataires avec activité récente</span>
          <span class="badge-pill green" v-if="metrics.active_tenants_15m > 0">{{ metrics.active_tenants_15m }} connecté(s)</span>
          <span class="badge-pill gray" v-else>Inactif</span>
        </div>
      </div>

      <!-- KPI 2 : Utilisateurs en ligne -->
      <div class="sa-kpi-card users-online">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Utilisateurs en Ligne (15m)</span>
          <div class="sa-kpi-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
        </div>
        <div class="sa-kpi-value">
          {{ metrics.active_users_15m }} <span class="sa-kpi-denom">/ {{ metrics.total_users }}</span>
        </div>
        <div class="sa-kpi-sub">
          {{ metrics.active_users_total }} compte(s) actif(s) au total
        </div>
      </div>

      <!-- KPI 3 : Total Instances SaaS -->
      <div class="sa-kpi-card total-instances">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Instances SaaS</span>
          <div class="sa-kpi-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
        </div>
        <div class="sa-kpi-value">{{ metrics.total_tenants }}</div>
        <div class="sa-kpi-sub">
          {{ metrics.tenants_actifs }} actifs · {{ metrics.tenants_suspendus }} suspendus · {{ metrics.tenants_demo }} démo
        </div>
      </div>

      <!-- KPI 4 : Télémétrie Erreurs 500 (24h) -->
      <div class="sa-kpi-card errors-telemetry" :class="{ 'has-errors': metrics.errors_24h_count > 0 }">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Erreurs 500 (24h)</span>
          <div class="sa-kpi-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
        </div>
        <div class="sa-kpi-value">{{ metrics.errors_24h_count }}</div>
        <div class="sa-kpi-sub">
          <span class="badge-pill green" v-if="metrics.errors_24h_count === 0">✔ Système Stable</span>
          <span class="badge-pill red pulse-slow" v-else>⚠️ {{ metrics.errors_24h_count }} exception(s) détectée(s)</span>
        </div>
      </div>
    </div>

    <!-- Navigation Onglets -->
    <div class="sa-tabs-nav">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'tenants' }"
        @click="activeTab = 'tenants'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/></svg>
        Instances SaaS &amp; Activité ({{ tenantsList.length }})
      </button>

      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'users' }"
        @click="activeTab = 'users'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Utilisateurs en Ligne &amp; Récents ({{ activeUsersList.length }})
      </button>

      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'telemetry' }"
        @click="activeTab = 'telemetry'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Surveillance Erreurs 500
        <span class="tab-badge-error" v-if="telemetry.total_24h > 0">{{ telemetry.total_24h }}</span>
      </button>
    </div>

    <!-- TAB 1 : INSTANCES SAAS & ACTIVITÉ -->
    <div v-if="activeTab === 'tenants'" class="tab-content">
      <section class="info-card">
        <div class="card-header justify-between">
          <div class="flex items-center gap-2">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/></svg>
            </div>
            <h3>Suivi Détaillé des Tenants</h3>
          </div>
          <router-link to="/superadmin/tenants" class="btn-sm-action">
            Gérer les Instances
          </router-link>
        </div>

        <div class="table-responsive">
          <table class="sa-table">
            <thead>
              <tr>
                <th>Instance SaaS</th>
                <th>Plan Abonnement</th>
                <th>Statut</th>
                <th>Utilisateurs Rattachés</th>
                <th>Connectés (15m)</th>
                <th>Créé Le</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in tenantsList" :key="t.id">
                <td>
                  <div class="flex items-center gap-3">
                    <div class="tenant-avatar">{{ t.nom ? t.nom.substring(0, 2).toUpperCase() : 'TS' }}</div>
                    <div>
                      <div class="tenant-title">{{ t.nom }}</div>
                      <div class="tenant-sub mono">ID: #{{ t.id }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge-plan">{{ t.plan || 'Business' }}</span>
                </td>
                <td>
                  <span class="badge" :class="t.statut">{{ t.statut }}</span>
                </td>
                <td class="font-semibold">{{ t.total_users_count }} util.</td>
                <td>
                  <span class="badge-online-count" :class="{ 'is-active': t.active_users_count > 0 }">
                    <span class="dot-status" :class="{ active: t.active_users_count > 0 }"></span>
                    {{ t.active_users_count }} connecté(s)
                  </span>
                </td>
                <td class="text-muted text-sm">{{ formatDate(t.created_at) }}</td>
                <td>
                  <router-link :to="`/superadmin/tenants/${t.id}`" class="btn-xs-link">
                    Inspecter
                  </router-link>
                </td>
              </tr>
              <tr v-if="tenantsList.length === 0">
                <td colspan="7" class="text-center py-6 text-muted">Aucun tenant configuré.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- TAB 2 : UTILISATEURS & SESSIONS -->
    <div v-if="activeTab === 'users'" class="tab-content">
      <section class="info-card">
        <div class="card-header justify-between">
          <div class="flex items-center gap-2">
            <div class="card-header-icon users-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <h3>Utilisateurs Connectés &amp; Activité Récente</h3>
          </div>
          <router-link to="/superadmin/users" class="btn-sm-action">
            Gérer les Utilisateurs
          </router-link>
        </div>

        <div class="table-responsive">
          <table class="sa-table">
            <thead>
              <tr>
                <th>État</th>
                <th>Utilisateur</th>
                <th>Tenants Rattachés</th>
                <th>Rôle / Privilège</th>
                <th>Adresse IP</th>
                <th>Dernière Connexion</th>
                <th>Dernière Activité (last_seen)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in activeUsersList" :key="u.id">
                <td>
                  <span class="status-indicator" :class="{ online: u.is_online, idle: !u.is_online }">
                    <span class="dot"></span>
                    {{ u.is_online ? 'En ligne' : 'Récent' }}
                  </span>
                </td>
                <td>
                  <div class="user-info">
                    <div class="user-name">{{ u.full_name }}</div>
                    <div class="user-email text-muted">{{ u.email }}</div>
                  </div>
                </td>
                <td>
                  <div class="tenant-pills">
                    <span v-for="tn in u.tenants" :key="tn.id" class="tenant-pill">
                      {{ tn.nom }}
                    </span>
                    <span v-if="!u.tenants || u.tenants.length === 0" class="text-muted text-xs">Aucun</span>
                  </div>
                </td>
                <td>
                  <span class="badge-role" :class="{ superadmin: u.is_superadmin }">
                    {{ u.is_superadmin ? 'SuperAdmin Boss' : 'Utilisateur Tenant' }}
                  </span>
                </td>
                <td class="mono text-xs">{{ u.ip || '—' }}</td>
                <td class="text-muted text-xs">{{ formatDate(u.last_login_at) }}</td>
                <td class="text-xs font-semibold" :class="{ 'text-green': u.is_online }">
                  {{ formatDate(u.last_seen_at) }}
                </td>
              </tr>
              <tr v-if="activeUsersList.length === 0">
                <td colspan="7" class="text-center py-6 text-muted">Aucun utilisateur connecté récemment.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- TAB 3 : TÉLÉMÉTRIE ERREURS 500 -->
    <div v-if="activeTab === 'telemetry'" class="tab-content">
      <div class="telemetry-grid">
        <!-- Error Breakdown by Tenant -->
        <section class="info-card side-panel mb-4">
          <div class="card-header">
            <div class="card-header-icon alert-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            </div>
            <h3>Erreurs 500 par Tenant (24h)</h3>
          </div>
          <div class="card-body">
            <div v-if="telemetry.by_tenant && telemetry.by_tenant.length > 0" class="tenant-error-list">
              <div v-for="errItem in telemetry.by_tenant" :key="errItem.tenant_id || 'central'" class="tenant-error-row">
                <span class="t-name">{{ errItem.tenant_name }}</span>
                <span class="badge-count-red">{{ errItem.error_count }} erreur(s)</span>
              </div>
            </div>
            <div v-else class="text-green text-sm p-4 text-center">
              ✔ Aucune erreur serveur détectée sur les dernières 24h.
            </div>
          </div>
        </section>

        <!-- Unhandled Exception Logs Feed -->
        <section class="info-card main-panel">
          <div class="card-header justify-between">
            <div class="flex items-center gap-2">
              <div class="card-header-icon alert-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <h3>Journal des Erreurs Non Gérées (HTTP 500)</h3>
            </div>
            <span class="text-xs text-muted">Dernières 20 entrées</span>
          </div>

          <div class="logs-feed">
            <div v-for="log in telemetry.recent_logs" :key="log.id" class="log-card">
              <div class="log-card-header">
                <div class="flex items-center gap-2">
                  <span class="badge-status-500">HTTP {{ log.status_code }}</span>
                  <span class="log-route mono">{{ log.method }} {{ log.url }}</span>
                </div>
                <span class="log-time">{{ formatDate(log.created_at) }}</span>
              </div>

              <div class="log-context">
                <span><strong>Tenant :</strong> {{ log.tenant_name }}</span>
                <span><strong>User :</strong> {{ log.user_name }}</span>
                <span><strong>IP :</strong> <code class="mono">{{ log.ip }}</code></span>
              </div>

              <div class="log-message">
                {{ log.message }}
              </div>

              <div class="log-trace-toggle" v-if="log.trace_short">
                <button class="btn-toggle-trace" @click="toggleTrace(log.id)">
                  {{ expandedTraces[log.id] ? 'Masquer la Stack Trace' : 'Afficher la Stack Trace' }}
                </button>
                <pre v-if="expandedTraces[log.id]" class="log-trace-block mono">{{ log.trace_short }}</pre>
              </div>
            </div>

            <div v-if="!telemetry.recent_logs || telemetry.recent_logs.length === 0" class="empty-telemetry">
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <h4>Aucune exception non gérée</h4>
              <p>Le système fonctionne correctement sans erreurs 500 enregistrées.</p>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../../services/api'

const loading = ref(true)
const activeTab = ref('tenants')
const lastUpdated = ref('')
const expandedTraces = ref({})
let refreshTimer = null

const metrics = ref({
  active_tenants_15m: 0,
  total_tenants: 0,
  active_users_15m: 0,
  total_users: 0,
  total_superadmins: 0,
  active_users_total: 0,
  errors_24h_count: 0,
  tenants_actifs: 0,
  tenants_suspendus: 0,
  tenants_demo: 0,
})

const tenantsList = ref([])
const activeUsersList = ref([])
const telemetry = ref({
  total_24h: 0,
  by_tenant: [],
  recent_logs: [],
})

async function fetchStats() {
  loading.value = true
  try {
    const { data } = await api.get('/superadmin/dashboard-stats')
    if (data.realtime_metrics) {
      metrics.value = data.realtime_metrics
    }
    if (data.tenants) {
      tenantsList.value = data.tenants
    }
    if (data.active_users) {
      activeUsersList.value = data.active_users
    }
    if (data.telemetry_errors) {
      telemetry.value = data.telemetry_errors
    }
    const now = new Date()
    lastUpdated.value = now.toLocaleTimeString()
  } catch (err) {
    console.error('Erreur chargement stats superadmin:', err)
  } finally {
    loading.value = false
  }
}

function toggleTrace(id) {
  expandedTraces.value[id] = !expandedTraces.value[id]
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  try {
    const d = new Date(dateStr)
    return d.toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
    })
  } catch {
    return dateStr
  }
}

onMounted(() => {
  fetchStats()
  // Auto refresh toutes les 30 secondes pour le temps réel
  refreshTimer = setInterval(() => {
    fetchStats()
  }, 30000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
})
</script>

<style scoped>
.sa-dashboard-view {
  --c-bg: #F8FAFC;
  --c-card-bg: #FFFFFF;
  --c-text: #0F172A;
  --c-muted: #64748B;
  --c-accent: #F59E0B;
  --c-border: #E2E8F0;
  padding: 24px 28px;
  background: var(--c-bg);
  min-height: 100vh;
}

.hero-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  padding: 22px 28px;
  border-radius: 16px;
  margin-bottom: 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
  border-left: 5px solid var(--c-accent);
}

.hero-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.hero-avatar.sa-theme {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero-name {
  font-size: 1.45rem;
  font-weight: 800;
  margin: 2px 0;
  color: var(--c-text);
}

.hero-type-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .68rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--c-accent);
}

.hero-type-badge .dot {
  width: 7px;
  height: 7px;
  background: var(--c-accent);
  border-radius: 50%;
}

.hero-type-badge .dot.pulse {
  box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
  animation: pulse-ring 1.8s infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}

.hero-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
}

.btn-refresh {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #F1F5F9;
  color: #334155;
  border: 1px solid #CBD5E1;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-refresh:hover {
  background: #E2E8F0;
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

.last-update {
  font-size: 0.72rem;
  color: var(--c-muted);
}

/* KPIs GRID */
.sa-kpis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 20px;
}

.sa-kpi-card {
  background: #fff;
  padding: 22px;
  border-radius: 16px;
  border: 1px solid var(--c-border);
  box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
  position: relative;
  overflow: hidden;
}

.sa-kpi-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
}

.sa-kpi-card.tenants-active::after { background: #10B981; }
.sa-kpi-card.users-online::after { background: #6366F1; }
.sa-kpi-card.total-instances::after { background: #3B82F6; }
.sa-kpi-card.errors-telemetry::after { background: #10B981; }
.sa-kpi-card.errors-telemetry.has-errors::after { background: #EF4444; }

.sa-kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.sa-kpi-label {
  font-size: .73rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--c-muted);
}

.sa-kpi-icon {
  color: var(--c-muted);
  opacity: .6;
}

.sa-kpi-value {
  font-size: 2.1rem;
  font-weight: 900;
  line-height: 1.1;
  margin-bottom: 8px;
  color: var(--c-text);
}

.sa-kpi-denom {
  font-size: 1.1rem;
  color: var(--c-muted);
  font-weight: 600;
}

.sa-kpi-sub {
  font-size: .75rem;
  color: var(--c-muted);
  font-weight: 500;
}

.badge-pill {
  padding: 2px 8px;
  border-radius: 99px;
  font-size: 0.68rem;
  font-weight: 700;
}

.badge-pill.green { background: #DCFCE7; color: #15803D; }
.badge-pill.gray { background: #F1F5F9; color: #64748B; }
.badge-pill.red { background: #FEE2E2; color: #B91C1C; }

/* TABS NAV */
.sa-tabs-nav {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid var(--c-border);
  padding-bottom: 2px;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  padding: 10px 18px;
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--c-muted);
  cursor: pointer;
  border-radius: 8px 8px 0 0;
  transition: all 0.2s ease;
  position: relative;
}

.tab-btn:hover {
  color: var(--c-text);
  background: #F1F5F9;
}

.tab-btn.active {
  color: #2563EB;
  background: #EFF6FF;
  border-bottom: 3px solid #2563EB;
}

.tab-badge-error {
  background: #EF4444;
  color: #fff;
  font-size: 0.65rem;
  padding: 2px 6px;
  border-radius: 99px;
  font-weight: 800;
}

/* CARDS & TABLES */
.info-card {
  background: #fff;
  border: 1px solid var(--c-border);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  background: #F8FAFC;
  border-bottom: 1px solid var(--c-border);
}

.card-header h3 {
  font-size: .8rem;
  font-weight: 800;
  text-transform: uppercase;
  color: var(--c-muted);
  margin: 0;
}

.card-header-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-header-icon.bank { background: #FEF3C7; color: #D97706; }
.card-header-icon.users-icon { background: #E0E7FF; color: #4F46E5; }
.card-header-icon.alert-icon { background: #FEE2E2; color: #DC2626; }

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.sa-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.sa-table th {
  background: #F8FAFC;
  padding: 12px 18px;
  text-align: left;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  color: var(--c-muted);
  border-bottom: 1px solid var(--c-border);
}

.sa-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: middle;
}

.tenant-avatar {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: #E2E8F0;
  color: #334155;
  font-weight: 800;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tenant-title { font-weight: 700; color: var(--c-text); }
.tenant-sub { font-size: 0.7rem; color: var(--c-muted); }

.badge-plan {
  background: #F3E8FF;
  color: #7E22CE;
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.72rem;
}

.badge {
  padding: 3px 8px;
  border-radius: 6px;
  font-size: .68rem;
  font-weight: 800;
  text-transform: uppercase;
}

.badge.actif { background: #DCFCE7; color: #166534; }
.badge.suspendu { background: #FEE2E2; color: #991B1B; }

.badge-online-count {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.75rem;
  background: #F1F5F9;
  color: var(--c-muted);
}

.badge-online-count.is-active {
  background: #DCFCE7;
  color: #15803D;
  font-weight: 700;
}

.dot-status {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #94A3B8;
}

.dot-status.active {
  background: #22C55E;
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

.status-indicator.online { background: #DCFCE7; color: #15803D; }
.status-indicator.online .dot { width: 6px; height: 6px; border-radius: 50%; background: #22C55E; }
.status-indicator.idle { background: #F1F5F9; color: #64748B; }
.status-indicator.idle .dot { width: 6px; height: 6px; border-radius: 50%; background: #94A3B8; }

.user-name { font-weight: 700; color: var(--c-text); }
.user-email { font-size: 0.75rem; }

.tenant-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.tenant-pill {
  background: #EFF6FF;
  color: #1D4ED8;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}

.badge-role {
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  background: #F1F5F9;
  color: #475569;
}

.badge-role.superadmin {
  background: #FEF3C7;
  color: #B45309;
}

.btn-xs-link {
  font-size: 0.75rem;
  font-weight: 700;
  color: #2563EB;
  text-decoration: none;
}

.btn-sm-action {
  font-size: 0.78rem;
  font-weight: 700;
  color: #2563EB;
  background: #EFF6FF;
  padding: 6px 12px;
  border-radius: 8px;
  text-decoration: none;
}

/* TELEMETRY STYLES */
.telemetry-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 20px;
}

@media (max-width: 900px) {
  .telemetry-grid { grid-template-columns: 1fr; }
}

.tenant-error-list {
  display: flex;
  flex-direction: column;
}

.tenant-error-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  border-bottom: 1px solid #F1F5F9;
  font-size: 0.82rem;
}

.t-name { font-weight: 700; color: var(--c-text); }
.badge-count-red { background: #FEE2E2; color: #991B1B; font-weight: 800; font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; }

.logs-feed {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 20px;
}

.log-card {
  border: 1px solid #F1F5F9;
  background: #FAFAFA;
  border-radius: 10px;
  padding: 14px 16px;
}

.log-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.badge-status-500 {
  background: #EF4444;
  color: #fff;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 4px;
}

.log-route {
  font-size: 0.8rem;
  font-weight: 700;
  color: #1E293B;
}

.log-time {
  font-size: 0.72rem;
  color: var(--c-muted);
}

.log-context {
  display: flex;
  gap: 16px;
  font-size: 0.75rem;
  color: var(--c-muted);
  margin-bottom: 8px;
}

.log-message {
  font-size: 0.85rem;
  font-weight: 600;
  color: #991B1B;
  background: #FEF2F2;
  padding: 8px 12px;
  border-radius: 6px;
  border-left: 3px solid #EF4444;
  margin-bottom: 8px;
}

.btn-toggle-trace {
  background: none;
  border: none;
  color: #2563EB;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
}

.log-trace-block {
  background: #0F172A;
  color: #F8FAFC;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.72rem;
  overflow-x: auto;
  margin-top: 8px;
  white-space: pre-wrap;
  max-height: 250px;
}

.empty-telemetry {
  text-align: center;
  padding: 40px 20px;
  color: var(--c-muted);
}

.empty-telemetry h4 { margin: 10px 0 4px; color: var(--c-text); }

.mono { font-family: 'JetBrains Mono', monospace; }
.text-green { color: #166534; }
.text-muted { color: var(--c-muted); }
.text-xs { font-size: 0.72rem; }
.text-sm { font-size: 0.8rem; }
.font-semibold { font-weight: 600; }
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.mb-4 { margin-bottom: 16px; }
.mb-6 { margin-bottom: 24px; }
.py-6 { padding-top: 24px; padding-bottom: 24px; }
.text-center { text-align: center; }
</style>