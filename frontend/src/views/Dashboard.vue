<template>
  <div class="dashboard animate-fade-in">
    <!-- KPI Cards -->
    <div class="kpi-grid stagger">
      <div class="kpi-card accent">
        <div class="kpi-label">Chiffre d'Affaires (Mois)</div>
        <div class="kpi-value">{{ formatMoney(kpis.ca_mois) }}</div>
        <div class="kpi-sub">Année: {{ formatMoney(kpis.ca_annee) }}</div>
        <div class="kpi-icon accent">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
      </div>

      <div class="kpi-card warning">
        <div class="kpi-label">Factures Impayées</div>
        <div class="kpi-value">{{ formatMoney(kpis.factures_impayees?.montant) }}</div>
        <div class="kpi-sub">{{ kpis.factures_impayees?.count || 0 }} facture(s)</div>
        <div class="kpi-icon warning">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
      </div>

      <div class="kpi-card danger">
        <div class="kpi-label">Encours Fournisseurs</div>
        <div class="kpi-value">{{ formatMoney(kpis.encours_fournisseurs) }}</div>
        <div class="kpi-sub">Dettes ouvertes</div>
        <div class="kpi-icon danger">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
        </div>
      </div>

      <div class="kpi-card success">
        <div class="kpi-label">Devis En Cours</div>
        <div class="kpi-value">{{ kpis.devis_en_cours || 0 }}</div>
        <div class="kpi-sub">À convertir en facture</div>
        <div class="kpi-icon success">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
      </div>

      <div class="kpi-card info">
        <div class="kpi-label">Alertes Stock</div>
        <div class="kpi-value">{{ kpis.alertes_stock || 0 }}</div>
        <div class="kpi-sub">Produits en alerte</div>
        <div class="kpi-icon danger">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
      </div>

      <div class="kpi-card accent">
        <div class="kpi-label">Dépenses du Mois</div>
        <div class="kpi-value">{{ formatMoney(kpis.depenses_mois) }}</div>
        <div class="kpi-sub">Charges en cours</div>
        <div class="kpi-icon info">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-top: 1.5rem;">
      <!-- CA Mensuel Chart -->
      <div class="card animate-fade-in-up">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">CA Mensuel (12 derniers mois)</h3>
        <div style="height: 280px; display: flex; align-items: flex-end; gap: 6px; padding: 0.5rem 0;">
          <div v-for="(item, idx) in caMensuel" :key="idx" 
               style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
            <div class="text-sm" style="font-size:0.65rem; color:var(--text-muted);">{{ formatCompact(item.ca) }}</div>
            <div :style="{
              width: '100%', 
              height: getBarHeight(item.ca) + 'px',
              background: 'var(--gradient-primary)',
              borderRadius: '4px 4px 0 0',
              minHeight: '4px',
              transition: 'height 1s ease-out',
            }"></div>
            <div style="font-size:0.6rem; color:var(--text-muted); transform:rotate(-45deg); white-space:nowrap;">{{ item.mois?.slice(5) }}</div>
          </div>
        </div>
      </div>

      <!-- Compteurs -->
      <div class="card animate-fade-in-up" style="animation-delay: 0.15s;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Vue d'ensemble</h3>
        <div style="display:flex; flex-direction:column; gap: 0.75rem;">
          <div class="flex items-center justify-between" style="padding:0.6rem 0; border-bottom:1px solid var(--border-color);">
            <span class="text-sm" style="color:var(--text-secondary);">Clients</span>
            <span style="font-weight:700; font-size:1.1rem;">{{ kpis.nb_clients || 0 }}</span>
          </div>
          <div class="flex items-center justify-between" style="padding:0.6rem 0; border-bottom:1px solid var(--border-color);">
            <span class="text-sm" style="color:var(--text-secondary);">Fournisseurs</span>
            <span style="font-weight:700; font-size:1.1rem;">{{ kpis.nb_fournisseurs || 0 }}</span>
          </div>
          <div class="flex items-center justify-between" style="padding:0.6rem 0; border-bottom:1px solid var(--border-color);">
            <span class="text-sm" style="color:var(--text-secondary);">Produits</span>
            <span style="font-weight:700; font-size:1.1rem;">{{ kpis.nb_produits || 0 }}</span>
          </div>
          <div class="flex items-center justify-between" style="padding:0.6rem 0; border-bottom:1px solid var(--border-color);">
            <span class="text-sm" style="color:var(--text-secondary);">Commandes en cours</span>
            <span style="font-weight:700; font-size:1.1rem;">{{ kpis.commandes_en_cours || 0 }}</span>
          </div>
          <div class="flex items-center justify-between" style="padding:0.6rem 0;">
            <span class="text-sm" style="color:var(--text-secondary);">Encours clients</span>
            <span style="font-weight:700; font-size:1.1rem; color: var(--warning);">{{ formatMoney(kpis.encours_clients) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent & Alerts -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1.5rem;">
      <!-- Top Ventes -->
      <div class="card">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Top Ventes (Année)</h3>
        <div v-if="topVentes.length === 0" class="text-sm text-muted" style="padding:2rem; text-align:center;">Aucune donnée de vente</div>
        <div v-for="(p, idx) in topVentes.slice(0, 5)" :key="idx" class="flex items-center justify-between" style="padding:0.6rem 0; border-bottom:1px solid var(--border-color);">
          <div>
            <div style="font-size:0.85rem; font-weight:500;">{{ p.designation }}</div>
            <div class="text-sm text-muted">{{ p.reference }} — {{ p.qte_vendue }} vendus</div>
          </div>
          <div style="font-weight:600; color:var(--success);">{{ formatMoney(p.ca_ttc) }}</div>
        </div>
      </div>

      <!-- Top Clients -->
      <div class="card">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Top Clients (Année)</h3>
        <div v-if="topClients.length === 0" class="text-sm text-muted" style="padding:2rem; text-align:center;">Aucune donnée client</div>
        <div v-for="(c, idx) in topClients.slice(0, 5)" :key="idx" class="flex items-center justify-between" style="padding:0.6rem 0; border-bottom:1px solid var(--border-color);">
          <div>
            <div style="font-size:0.85rem; font-weight:500;">{{ c.societe }}</div>
            <div class="text-sm text-muted">{{ c.nb_factures }} facture(s)</div>
          </div>
          <div style="font-weight:600; color:var(--accent);">{{ formatMoney(c.ca_total) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'

const kpis = ref({})
const caMensuel = ref([])
const topVentes = ref([])
const topClients = ref([])

// Format money
function formatMoney(val) {
  const num = parseFloat(val) || 0
  return num.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DH'
}

function formatCompact(val) {
  const num = parseFloat(val) || 0
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(0) + 'K'
  return num.toFixed(0)
}

// Calculate bar heights
const maxCA = computed(() => Math.max(...caMensuel.value.map(i => parseFloat(i.ca) || 0), 1))
function getBarHeight(val) {
  return Math.max(4, (parseFloat(val) || 0) / maxCA.value * 220)
}

function loadDemoData() {
  kpis.value = {
    ca_mois: 125000, ca_annee: 1450000,
    factures_impayees: { count: 12, montant: 89500 },
    encours_clients: 89500, encours_fournisseurs: 45000,
    devis_en_cours: 8, commandes_en_cours: 5,
    alertes_stock: 3, depenses_mois: 32000,
    nb_clients: 47, nb_produits: 234, nb_fournisseurs: 18,
  }
  caMensuel.value = [
    { mois: '2025-05', ca: 95000 }, { mois: '2025-06', ca: 110000 },
    { mois: '2025-07', ca: 88000 }, { mois: '2025-08', ca: 72000 },
    { mois: '2025-09', ca: 105000 }, { mois: '2025-10', ca: 130000 },
    { mois: '2025-11', ca: 115000 }, { mois: '2025-12', ca: 145000 },
    { mois: '2026-01', ca: 98000 }, { mois: '2026-02', ca: 112000 },
    { mois: '2026-03', ca: 138000 }, { mois: '2026-04', ca: 125000 },
  ]
  topVentes.value = [
    { designation: 'Écran LED 27"', reference: 'PRD-001', qte_vendue: 156, ca_ttc: 234000 },
    { designation: 'Clavier Mécanique Pro', reference: 'PRD-012', qte_vendue: 89, ca_ttc: 89000 },
    { designation: 'Câble HDMI 2.1', reference: 'PRD-045', qte_vendue: 342, ca_ttc: 51300 },
    { designation: 'Souris Ergonomique', reference: 'PRD-023', qte_vendue: 67, ca_ttc: 40200 },
    { designation: 'Station d\'accueil USB-C', reference: 'PRD-008', qte_vendue: 45, ca_ttc: 36000 },
  ]
  topClients.value = [
    { societe: 'TechnoPlus SARL', nb_factures: 23, ca_total: 345000 },
    { societe: 'Digital Factory', nb_factures: 18, ca_total: 267000 },
    { societe: 'MediaCom Group', nb_factures: 15, ca_total: 198000 },
    { societe: 'Solutions Pro MA', nb_factures: 12, ca_total: 156000 },
    { societe: 'Atlas Import Export', nb_factures: 9, ca_total: 134500 },
  ]
}

onMounted(async () => {
  const [kpiRes, caRes, tvRes, tcRes] = await Promise.allSettled([
    api.get('/dashboard/kpis'),
    api.get('/dashboard/ca-mensuel'),
    api.get('/dashboard/top-ventes'),
    api.get('/dashboard/top-clients'),
  ])

  const anySuccess = [kpiRes, caRes, tvRes, tcRes].some(r => r.status === 'fulfilled')

  if (anySuccess) {
    if (kpiRes.status === 'fulfilled') kpis.value = kpiRes.value.data
    if (caRes.status === 'fulfilled') caMensuel.value = caRes.value.data
    if (tvRes.status === 'fulfilled') topVentes.value = tvRes.value.data
    if (tcRes.status === 'fulfilled') topClients.value = tcRes.value.data
  } else {
    // No backend available — load demo data
    loadDemoData()
  }
})
</script>
