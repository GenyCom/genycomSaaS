<template>
  <div class="fournisseur-detail-view">

    <!-- Loading Overlay -->
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement du fournisseur…</p>
      </div>
    </Transition>

    <!-- Top Bar -->
    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/fournisseurs" class="back-btn" title="Retour aux fournisseurs">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Fournisseurs</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span v-if="loading" class="breadcrumb-skeleton"></span>
          <span v-else class="breadcrumb-current">{{ fournisseur.societe || fournisseur.nom || '—' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link :to="`/fournisseurs/${fournisseur.id}/edit`" class="btn-edit">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>Modifier</span>
        </router-link>
      </div>
    </div>

    <!-- Hero Header -->
    <div class="hero-header">
      <div class="hero-avatar supplier">
        <span>{{ avatarInitials }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ fournisseur.type_fournisseur?.libelle || 'Standard' }}
        </div>
        <h1 class="hero-name">
          <span v-if="loading" class="skeleton-line wide"></span>
          <template v-else>{{ fournisseur.societe || fournisseur.nom || 'Chargement…' }}</template>
        </h1>
        <p class="hero-sub">
          <span v-if="loading" class="skeleton-line narrow"></span>
          <template v-else>
            Réf. <strong>{{ fournisseur.code_fournisseur || '—' }}</strong>
            <span v-if="fournisseur.ville"> · {{ (fournisseur.ville || '').toUpperCase() }}, {{ fournisseur.pays }}</span>
          </template>
        </p>
      </div>
      <div class="hero-rating-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Partenaire Actif
      </div>
    </div>

    <!-- KPI Strip -->
    <div class="kpi-strip">
      <div class="kpi-item liability">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Engagements (Dû)</p>
          <p class="kpi-value">{{ formatMoney(fournisseur.total_achats || 0) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9h12M6 9a6 6 0 016-6 6 6 0 016 6m0 0v3h2a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2v-7a2 2 0 012-2h2V9"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Volume Commandé</p>
          <p class="kpi-value">{{ formatMoney(fournisseur.total_commandes || 0) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Délai de Règlement</p>
          <p class="kpi-value">{{ fournisseur.delai_paiement || 0 }} <span>jours</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item warning">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Délai de Livraison</p>
          <p class="kpi-value">{{ fournisseur.delai_livraison || 0 }} <span>jours</span></p>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">

      <!-- Column Left -->
      <div class="col-left">

        <!-- Identity Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4h-4a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
            </div>
            <h3>Fiche Partenaire</h3>
          </div>
          <div class="card-body">
            <div class="field-row">
              <span class="field-label">Code Fournisseur</span>
              <span class="field-value mono accent">{{ fournisseur.code_fournisseur || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">Type</span>
              <span class="field-value">
                <span class="tag">{{ fournisseur.type_fournisseur?.libelle || 'STANDARD' }}</span>
              </span>
            </div>
            <div class="field-separator"></div>
            <div class="field-row">
              <span class="field-label">ICE</span>
              <span class="field-value mono">{{ fournisseur.ice || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">R.C.</span>
              <span class="field-value mono">{{ fournisseur.rc || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">I.F. (Fiscal)</span>
              <span class="field-value mono">{{ fournisseur.if_fiscal || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">Patente</span>
              <span class="field-value mono">{{ fournisseur.patente || '—' }}</span>
            </div>
          </div>
        </section>

        <!-- Banking Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h3>Données Bancaires</h3>
          </div>
          <div class="card-body">
            <div class="field-col">
              <span class="field-label">Banque</span>
              <span class="field-value bold">{{ fournisseur.banque || 'Non renseignée' }}</span>
            </div>
            <div class="field-col mt-4">
              <span class="field-label">RIB de Paiement</span>
              <div class="rib-display">
                <span class="rib-code">{{ formatRIB(fournisseur.rib) }}</span>
                <button class="copy-btn" @click="copyRIB" :class="{ copied: ribCopied }" title="Copier le RIB">
                  <svg v-if="!ribCopied" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
              </div>
            </div>
          </div>
        </section>

      </div>

      <!-- Column Right -->
      <div class="col-right">

        <!-- Contact Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M17 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Contact & Localisation</h3>
          </div>
          <div class="card-body">
            <div class="contact-grid">
              <!-- Left column -->
              <div class="contact-col">
                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Contact Commercial</p>
                    <p class="contact-value">{{ fournisseur.prenom }} {{ fournisseur.nom || '—' }}</p>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Email</p>
                    <a :href="`mailto:${fournisseur.email}`" class="contact-link">{{ fournisseur.email || 'Non renseigné' }}</a>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.528.296 1.045.506 1.547"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Téléphones</p>
                    <p class="contact-value">
                      {{ fournisseur.telephone || '—' }}
                      <span v-if="fournisseur.mobile" class="contact-secondary"> · {{ fournisseur.mobile }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Right column -->
              <div class="contact-col">
                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Siège Social</p>
                    <p class="contact-value address">
                      {{ fournisseur.adresse || '—' }}<br v-if="fournisseur.adresse"/>
                      <span v-if="fournisseur.code_postal || fournisseur.ville">{{ fournisseur.code_postal }} {{ (fournisseur.ville || '').toUpperCase() }}</span><br v-if="fournisseur.pays"/>
                      <span v-if="fournisseur.pays" class="contact-secondary">{{ fournisseur.pays }}</span>
                    </p>
                  </div>
                </div>

                <div v-if="fournisseur.site_web" class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Site Web</p>
                    <a :href="fournisseur.site_web" target="_blank" rel="noopener" class="contact-link">{{ fournisseur.site_web }}</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Conditions Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon conditions">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="13" x2="8" y2="13"/><line x1="12" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3>Conditions & Notes</h3>
          </div>
          <div class="card-body">
            <div class="notes-content" :class="{ empty: !fournisseur.observations }">
              <p v-if="fournisseur.observations">{{ fournisseur.observations }}</p>
              <div v-else class="notes-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Aucune observation enregistrée.</span>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const props = defineProps({ id: [String, Number] })
const route = useRoute()
const fournisseur = ref({})
const loading = ref(true)
const ribCopied = ref(false)

const avatarInitials = computed(() => {
  const name = fournisseur.value?.societe || fournisseur.value?.nom || ''
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() || 'FR'
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatRIB(rib) {
  if (!rib) return 'NON RENSEIGNÉ'
  const clean = rib.replace(/\s+/g, '')
  return clean.match(/.{1,4}/g)?.join(' ') || clean
}

async function copyRIB() {
  const rib = fournisseur.value?.rib
  if (!rib) return
  try {
    await navigator.clipboard.writeText(rib)
    ribCopied.value = true
    setTimeout(() => ribCopied.value = false, 2000)
  } catch { /* silent */ }
}

async function fetchFournisseur(id) {
  if (!id) return
  loading.value = true
  try {
    const { data } = await api.get(`/fournisseurs/${id}`)
    fournisseur.value = data.data || data
  } catch (error) {
    console.error('Erreur API Fournisseur:', error)
  } finally {
    loading.value = false
  }
}

watch(() => props.id, (newId) => fetchFournisseur(newId), { immediate: true })
onMounted(() => { if (!props.id && route.params.id) fetchFournisseur(route.params.id) })
</script>

<style scoped>
/* ─── Design Tokens ─────────────────────────────────────────────────────────── */
.fournisseur-detail-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-subtle:     #F1F3F6;

  --c-accent:     #2563EB;
  --c-accent-bg:  #EEF4FF;
  --c-liability:  #DC2626;
  --c-liability-bg: #FEF2F2;
  --c-success:    #16A34A;
  --c-success-bg: #F0FDF4;
  --c-warn:       #D97706;
  --c-warn-bg:    #FFFBEB;
  --c-neutral-bg: #F0F4FF;

  --radius-sm:  8px;
  --radius-md:  12px;
  --radius-lg:  16px;
  --shadow-sm:  0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
  --shadow-md:  0 4px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);

  --font-sans:  'Inter', 'Segoe UI', system-ui, sans-serif;
  --font-mono:  'JetBrains Mono', 'Fira Code', 'Courier New', monospace;

  font-family: var(--font-sans);
  color: var(--c-text);
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
  position: relative;
}

/* ─── Loading ────────────────────────────────────────────────────────────────── */
.loading-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(247,248,250,0.85);
  backdrop-filter: blur(4px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
}
.loader-ring {
  display: inline-block;
  position: relative;
  width: 48px;
  height: 48px;
}
.loader-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 38px;
  height: 38px;
  margin: 5px;
  border: 3px solid transparent;
  border-radius: 50%;
  animation: loader-spin 1.1s cubic-bezier(.5,.1,.5,.9) infinite;
  border-top-color: var(--c-accent);
}
.loader-ring div:nth-child(1) { animation-delay: -0.45s; }
.loader-ring div:nth-child(2) { animation-delay: -0.3s; }
.loader-ring div:nth-child(3) { animation-delay: -0.15s; }
@keyframes loader-spin { to { transform: rotate(360deg); } }
.loading-label { font-size: .8rem; color: var(--c-muted); font-weight: 500; letter-spacing: .02em; }
.fade-enter-active, .fade-leave-active { transition: opacity .25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ─── Skeleton ───────────────────────────────────────────────────────────────── */
@keyframes shimmer {
  0%   { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}
.skeleton-line, .breadcrumb-skeleton {
  display: inline-block;
  height: 1em;
  border-radius: 4px;
  background: linear-gradient(90deg, #e2e5ea 25%, #edf0f4 50%, #e2e5ea 75%);
  background-size: 800px 100%;
  animation: shimmer 1.4s infinite linear;
}
.skeleton-line.wide  { width: 220px; }
.skeleton-line.narrow { width: 140px; height: .7em; }
.breadcrumb-skeleton  { width: 100px; height: .85em; vertical-align: middle; }

/* ─── Top Bar ────────────────────────────────────────────────────────────────── */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
}
.topbar-left { display: flex; align-items: center; gap: 12px; }

.back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1.5px solid var(--c-border-mid);
  background: var(--c-surface);
  color: var(--c-muted);
  transition: all .18s;
  text-decoration: none;
  box-shadow: var(--shadow-sm);
}
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); transform: translateX(-1px); }

.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb svg { color: var(--c-border-mid); }
.breadcrumb-current { color: var(--c-text); font-weight: 600; }

.btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 18px;
  border-radius: var(--radius-sm);
  background: var(--c-accent);
  color: #fff;
  font-size: .82rem;
  font-weight: 600;
  text-decoration: none;
  box-shadow: 0 1px 4px rgba(37,99,235,.3);
  transition: all .18s;
  letter-spacing: .01em;
}
.btn-edit:hover { background: #1d4ed8; box-shadow: 0 4px 14px rgba(37,99,235,.35); transform: translateY(-1px); }

/* ─── Hero ───────────────────────────────────────────────────────────────────── */
.hero-header {
  display: flex;
  align-items: center;
  gap: 20px;
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  padding: 22px 28px;
  margin-bottom: 20px;
  box-shadow: var(--shadow-sm);
  flex-wrap: wrap;
}
.hero-avatar {
  flex-shrink: 0;
  width: 56px;
  height: 56px;
  border-radius: 14px;
  background: linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.15rem;
  font-weight: 800;
  letter-spacing: .03em;
  box-shadow: 0 2px 8px rgba(37,99,235,.25);
}
.hero-avatar.supplier { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 2px 8px rgba(124,58,237,.25); }
.hero-meta { flex: 1; min-width: 0; }
.hero-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: .68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--c-accent);
  margin-bottom: 5px;
}
.hero-type-badge .dot {
  width: 6px; height: 6px;
  background: var(--c-accent);
  border-radius: 50%;
}
.hero-name {
  font-size: 1.45rem;
  font-weight: 800;
  color: var(--c-text);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0 0 4px;
}
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 0; }
.hero-sub strong { color: var(--c-text); }

.hero-rating-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 14px;
  border-radius: 100px;
  font-size: .75rem;
  font-weight: 700;
  background: var(--c-success-bg);
  color: var(--c-success);
  margin-left: auto;
  flex-shrink: 0;
}

/* ─── KPI Strip ──────────────────────────────────────────────────────────────── */
.kpi-strip {
  display: flex;
  align-items: stretch;
  gap: 0;
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  margin-bottom: 24px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.kpi-item {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px 22px;
  transition: background .15s;
}
.kpi-item:hover { background: var(--c-subtle); }
.kpi-divider { width: 1px; background: var(--c-border); flex-shrink: 0; margin: 12px 0; }

.kpi-icon {
  width: 38px;
  height: 38px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.kpi-item.liability .kpi-icon { background: var(--c-liability-bg); color: var(--c-liability); }
.kpi-item.accent    .kpi-icon { background: var(--c-accent-bg);   color: var(--c-accent); }
.kpi-item.neutral   .kpi-icon { background: var(--c-neutral-bg);  color: #6366f1; }
.kpi-item.warning   .kpi-icon { background: var(--c-warn-bg);     color: var(--c-warn); }

.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted); margin: 0 0 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; line-height: 1; }
.kpi-value span { font-size: .7rem; font-weight: 600; opacity: .65; text-transform: uppercase; margin-left: 3px; }
.kpi-item.liability .kpi-value { color: var(--c-liability); }
.kpi-item.accent    .kpi-value { color: var(--c-accent); }
.kpi-item.neutral   .kpi-value { color: #6366f1; }
.kpi-item.warning   .kpi-value { color: var(--c-warn); }

/* ─── Content Grid ───────────────────────────────────────────────────────────── */
.content-grid {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 20px;
  align-items: start;
}
.col-left, .col-right { display: flex; flex-direction: column; gap: 20px; }

/* ─── Info Cards ─────────────────────────────────────────────────────────────── */
.info-card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: box-shadow .2s;
}
.info-card:hover { box-shadow: var(--shadow-md); }

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--c-border);
  background: var(--c-subtle);
}
.card-header h3 {
  font-size: .77rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .07em;
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
  background: var(--c-accent-bg);
  color: var(--c-accent);
  flex-shrink: 0;
}
.card-header-icon.bank       { background: #FFF7ED; color: #EA580C; }
.card-header-icon.contact    { background: var(--c-success-bg); color: var(--c-success); }
.card-header-icon.conditions { background: var(--c-warn-bg); color: var(--c-warn); }

.card-body { padding: 18px 20px; }

/* ─── Field Rows ─────────────────────────────────────────────────────────────── */
.field-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 7px 0;
  gap: 12px;
}
.field-col { display: flex; flex-direction: column; gap: 4px; }
.field-label {
  font-size: .76rem;
  color: var(--c-muted);
  font-weight: 500;
  flex-shrink: 0;
}
.field-value {
  font-size: .82rem;
  font-weight: 600;
  color: var(--c-text);
  text-align: right;
  word-break: break-all;
}
.field-value.mono  { font-family: var(--font-mono); font-size: .78rem; font-weight: 600; }
.field-value.accent { color: var(--c-accent); }
.field-value.bold  { font-weight: 700; font-size: .88rem; text-align: left; }
.field-separator { height: 1px; background: var(--c-border); margin: 8px 0; }
.mt-4 { margin-top: 14px; }

.tag {
  display: inline-block;
  padding: 2px 9px;
  border-radius: 4px;
  background: var(--c-accent-bg);
  color: var(--c-accent);
  font-size: .68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .06em;
}

/* ─── RIB Display ────────────────────────────────────────────────────────────── */
.rib-display {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--c-subtle);
  border: 1px dashed var(--c-border-mid);
  border-radius: var(--radius-sm);
  padding: 9px 12px;
  margin-top: 2px;
}
.rib-code {
  font-family: var(--font-mono);
  font-size: .76rem;
  font-weight: 600;
  color: var(--c-text);
  flex: 1;
  word-break: break-all;
  line-height: 1.5;
}
.copy-btn {
  flex-shrink: 0;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: 1px solid var(--c-border-mid);
  background: var(--c-surface);
  color: var(--c-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .15s;
}
.copy-btn:hover { border-color: var(--c-accent); color: var(--c-accent); }
.copy-btn.copied { border-color: var(--c-success); color: var(--c-success); background: var(--c-success-bg); }

/* ─── Contact Grid ───────────────────────────────────────────────────────────── */
.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 28px;
}
.contact-col { display: flex; flex-direction: column; gap: 18px; }
.contact-item { display: flex; align-items: flex-start; gap: 12px; }
.contact-item-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--c-subtle);
  border: 1px solid var(--c-border);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--c-muted);
  flex-shrink: 0;
  margin-top: 1px;
}
.contact-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted); margin: 0 0 3px; }
.contact-value { font-size: .84rem; font-weight: 600; color: var(--c-text); margin: 0; line-height: 1.5; }
.contact-value.address { font-weight: 500; }
.contact-secondary { color: var(--c-muted); font-weight: 400; }
.contact-link { font-size: .84rem; font-weight: 600; color: var(--c-accent); text-decoration: none; }
.contact-link:hover { text-decoration: underline; }

/* ─── Notes ──────────────────────────────────────────────────────────────────── */
.notes-content {
  min-height: 80px;
  font-size: .85rem;
  line-height: 1.7;
  color: var(--c-text);
}
.notes-content p { margin: 0; }
.notes-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 20px;
  color: var(--c-muted);
  font-size: .8rem;
  font-style: italic;
  text-align: center;
  min-height: 80px;
}

/* ─── Responsive ─────────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .content-grid { grid-template-columns: 1fr; }
}
@media (max-width: 760px) {
  .fournisseur-detail-view { padding: 16px 14px 40px; }
  .kpi-strip { flex-direction: column; }
  .kpi-divider { width: auto; height: 1px; margin: 0 16px; }
  .hero-header { gap: 14px; padding: 16px 18px; }
  .hero-name { font-size: 1.2rem; }
  .hero-rating-badge { margin-left: 0; width: 100%; justify-content: center; }
  .contact-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .btn-edit span { display: none; }
  .hero-avatar { width: 46px; height: 46px; font-size: .95rem; }
}
</style>