<template>
  <div class="sa-dashboard-view">
    <div class="hero-header sa-hero">
      <div class="hero-avatar sa-theme"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
      <div class="hero-meta">
        <div class="hero-type-badge"><span class="dot"></span>Administration Centrale</div>
        <h1 class="hero-name">Tableau de Bord SaaS</h1>
        <p class="hero-sub">Supervision globale de l'écosystème <strong>GenyCom</strong>.</p>
      </div>
    </div>

    <div class="sa-kpis-grid mb-6">
      <div class="sa-kpi-card tenants">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Instances</span>
          <div class="sa-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div>
        </div>
        <div class="sa-kpi-value">{{ kpis.total_tenants }}</div>
        <div class="sa-kpi-sub">{{ kpis.tenants_actifs }} actifs · {{ kpis.tenants_suspendus }} suspendus</div>
      </div>

      <div class="sa-kpi-card users">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Utilisateurs</span>
          <div class="sa-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        </div>
        <div class="sa-kpi-value">{{ kpis.total_users }}</div>
        <div class="sa-kpi-sub">{{ kpis.active_users }} actifs globaux</div>
      </div>

      <div class="sa-kpi-card logins">
        <div class="sa-kpi-header">
          <span class="sa-kpi-label">Connexions (24h)</span>
          <div class="sa-kpi-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        </div>
        <div class="sa-kpi-value">{{ kpis.recent_logins_24h }}</div>
        <div class="sa-kpi-sub">Activité réseau récente</div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-left">
        <section class="info-card">
          <div class="card-header justify-between">
            <div class="flex items-center gap-2">
              <div class="card-header-icon bank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/></svg></div>
              <h3>Dernières Instances</h3>
            </div>
            <router-link to="/superadmin/tenants" class="text-link">Tout voir</router-link>
          </div>
          <div class="card-body p-0">
            <div v-for="t in recentTenants" :key="t.id" class="sa-activity-item">
              <div class="sa-item-info">
                <div class="sa-item-title">{{ t.nom }}</div>
                <div class="sa-item-meta mono">{{ t.database_name }} · {{ t.users_count }} users</div>
              </div>
              <div class="sa-item-status"><span class="badge" :class="t.statut">{{ t.statut }}</span></div>
            </div>
          </div>
        </section>
      </div>

      <div class="col-right">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-6M9 20v-10M15 20v-2M18 20V4M6 20v-4"/></svg></div>
            <h3>Actions Centrales</h3>
          </div>
          <div class="card-body sa-quick-actions">
            <router-link to="/superadmin/tenants" class="sa-action-btn orange">Provisionner un SaaS</router-link>
            <router-link to="/superadmin/users" class="sa-action-btn indigo">Gérer les utilisateurs</router-link>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const loading = ref(true)
const kpis = ref({ total_tenants: 0, tenants_actifs: 0, tenants_suspendus: 0, total_users: 0, active_users: 0, recent_logins_24h: 0 })
const recentTenants = ref([])

onMounted(async () => {
  try {
    const { data } = await api.get('/superadmin/dashboard')
    kpis.value = data.kpis; recentTenants.value = data.recent_tenants
  } catch (err) { console.error(err) } finally { loading.value = false }
})
</script>

<style scoped>
.sa-dashboard-view {
  --c-bg: #F7F8FA; --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #f59e0b;
  padding: 24px 28px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}
.sa-hero { border-left: 4px solid var(--c-accent); }
.sa-theme { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
.hero-avatar { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.hero-header { display: flex; align-items: center; gap: 20px; background: #fff; padding: 22px 28px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.hero-name { font-size: 1.45rem; font-weight: 800; margin: 0; }
.hero-type-badge { display: flex; align-items: center; gap: 5px; font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }

.sa-kpis-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
.sa-kpi-card { background: #fff; padding: 24px; border-radius: 16px; border: 1px solid #E8EAEE; box-shadow: 0 1px 3px rgba(0,0,0,.04); position: relative; overflow: hidden; }
.sa-kpi-card::after { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; }
.sa-kpi-card.tenants::after { background: #f59e0b; }
.sa-kpi-card.users::after { background: #6366f1; }
.sa-kpi-card.logins::after { background: #3b82f6; }

.sa-kpi-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.sa-kpi-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); }
.sa-kpi-icon { color: var(--c-muted); opacity: .5; }
.sa-kpi-value { font-size: 2.2rem; font-weight: 900; line-height: 1; margin-bottom: 8px; color: var(--c-text); }
.sa-kpi-sub { font-size: .75rem; color: var(--c-muted); font-weight: 500; }

.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
.info-card { background: #fff; border: 1px solid #E8EAEE; border-radius: 16px; overflow: hidden; }
.card-header { display: flex; align-items: center; gap: 10px; padding: 16px 20px; background: #F9FAFB; border-bottom: 1px solid #E8EAEE; }
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon { width: 28px; height: 28px; border-radius: 7px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; }
.card-header-icon.bank { background: #FEF3C7; color: #D97706; }

.sa-activity-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid #F1F3F6; }
.sa-activity-item:last-child { border: none; }
.sa-item-title { font-size: .88rem; font-weight: 700; color: var(--c-text); }
.sa-item-meta { font-size: .72rem; color: var(--c-muted); margin-top: 2px; }

.sa-quick-actions { display: flex; flex-direction: column; gap: 12px; padding: 20px; }
.sa-action-btn { padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: .82rem; text-align: center; transition: transform .2s; }
.sa-action-btn:hover { transform: translateY(-2px); }
.sa-action-btn.orange { background: #FFF7ED; color: #EA580C; border: 1px solid #FFEDD5; }
.sa-action-btn.indigo { background: #EEF2FF; color: #4F46E5; border: 1px solid #E0E7FF; }

.mono { font-family: 'JetBrains Mono', monospace; }
.text-link { font-size: .75rem; color: var(--c-accent); font-weight: 700; text-decoration: none; }
.badge { padding: 3px 8px; border-radius: 6px; font-size: .65rem; font-weight: 800; text-transform: uppercase; }
.badge.actif { background: #DCFCE7; color: #166534; }
.badge.suspendu { background: #FEE2E2; color: #991B1B; }
</style>