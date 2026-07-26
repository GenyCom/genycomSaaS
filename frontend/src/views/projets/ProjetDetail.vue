<template>
  <div class="project-detail-view">
    
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement du projet…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/projets" class="back-btn" title="Retour aux projets">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Projets</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span v-if="loading" class="breadcrumb-skeleton"></span>
          <span v-else class="breadcrumb-current">{{ projet.nom_projet || '—' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn-secondary-custom" @click="editMode = !editMode">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>{{ editMode ? 'Annuler' : 'Modifier' }}</span>
        </button>
        <button v-if="editMode" class="btn-save" @click="saveProjet">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          <span>Enregistrer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>{{ projectInitials }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ projet.code_projet }}
        </div>
        <h1 class="hero-name">
          <span v-if="loading" class="skeleton-line wide"></span>
          <template v-else>{{ projet.nom_projet || 'Chargement…' }}</template>
        </h1>
        <p class="hero-sub">
          <span v-if="loading" class="skeleton-line narrow"></span>
          <template v-else>
            Client : <strong>{{ projet.client?.societe || 'Non défini' }}</strong>
          </template>
        </p>
      </div>
      <div 
        v-if="projet.etat" 
        class="hero-status-badge-dynamic" 
        :style="{ backgroundColor: projet.etat.color + '20', color: projet.etat.color, borderColor: projet.etat.color + '40' }"
      >
        {{ projet.etat.libelle }}
      </div>
      <div v-else class="hero-status-badge" :class="projet.statut">
        {{ formatStatut(projet.statut) }}
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Budget Prévu</p>
          <p class="kpi-value">{{ formatMoney(projet.budget_prevu) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Avancement</p>
          <p class="kpi-value">{{ projet.avancement_pcent || 0 }} <span>%</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item danger">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Consommé</p>
          <p class="kpi-value">{{ formatMoney(projet.budget_consomme) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <!-- Detail Tabs Navigation -->
    <div class="detail-tabs">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'fiche' }" 
        @click="activeTab = 'fiche'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
          <polyline points="10 9 9 9 8 9"/>
        </svg>
        <span>Fiche Projet</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'transactions' }" 
        @click="activeTab = 'transactions'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
          <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        <span>Transactions Commerciales</span>
        <span class="tab-badge" v-if="totalTransactionsCount > 0">{{ totalTransactionsCount }}</span>
      </button>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid" v-if="activeTab === 'fiche'">
      
      <div class="col-left">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3>Calendrier & Références</h3>
          </div>
          <div class="card-body">
            <div v-if="!editMode">
              <div class="field-row">
                <span class="field-label">Date de début</span>
                <span class="field-value">{{ projet.date_debut || '—' }}</span>
              </div>
              <div class="field-row">
                <span class="field-label">Fin prévue</span>
                <span class="field-value">{{ projet.date_fin_prevue || '—' }}</span>
              </div>
              <div class="field-separator"></div>
              <div class="field-row">
                <span class="field-label">Code interne</span>
                <span class="field-value mono accent">{{ projet.code_projet }}</span>
              </div>
            </div>

            <div v-else class="edit-form">
              <div class="form-group-custom">
                <label>Nom du Projet</label>
                <input v-model="form.nom_projet" type="text" />
              </div>
              <div class="form-row-custom">
                <div class="form-group-custom">
                  <label>Date Début</label>
                  <input v-model="form.date_debut" type="date" />
                </div>
                <div class="form-group-custom">
                  <label>Fin Prévue</label>
                  <input v-model="form.date_fin_prevue" type="date" />
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="col-right">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Client Associé</h3>
          </div>
          <div class="card-body">
            <div v-if="projet.client" class="client-mini-card">
              <p class="client-name">{{ projet.client.societe }}</p>
              <router-link :to="`/clients/${projet.client.id}`" class="client-link-btn">Voir la fiche client</router-link>
            </div>
            <p v-else class="notes-empty">Aucun client rattaché.</p>
          </div>
        </section>

        <section v-if="editMode" class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h3>Gestion Statut & Budget</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Statut du Projet</label>
              <select v-model="form.etat_id">
                <option v-for="s in etats" :key="s.id" :value="s.id">{{ s.libelle }}</option>
              </select>
            </div>
            <div class="form-group-custom">
              <label>Budget Prévu (DH)</label>
              <input v-model="form.budget_prevu" type="number" />
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Transactions Tab View -->
    <div class="transactions-tab-container" v-else-if="activeTab === 'transactions'">
      <div class="transactions-header-bar mb-4">
        <!-- Sub filters pills -->
        <div class="sub-filters">
          <button 
            class="filter-pill" 
            :class="{ active: subFilter === 'all' }"
            @click="subFilter = 'all'"
          >
            Tous <span class="pill-count">{{ countsByType.all }}</span>
          </button>
          <button 
            class="filter-pill" 
            :class="{ active: subFilter === 'devis' }"
            @click="subFilter = 'devis'"
          >
            Devis <span class="pill-count">{{ countsByType.devis }}</span>
          </button>
          <button 
            class="filter-pill" 
            :class="{ active: subFilter === 'bcc' }"
            @click="subFilter = 'bcc'"
          >
            Commandes <span class="pill-count">{{ countsByType.bcc }}</span>
          </button>
          <button 
            class="filter-pill" 
            :class="{ active: subFilter === 'bl' }"
            @click="subFilter = 'bl'"
          >
            Bons Livraison <span class="pill-count">{{ countsByType.bl }}</span>
          </button>
          <button 
            class="filter-pill" 
            :class="{ active: subFilter === 'facture' }"
            @click="subFilter = 'facture'"
          >
            Factures Vente <span class="pill-count">{{ countsByType.facture }}</span>
          </button>
        </div>

        <!-- Search & Export Actions -->
        <div style="display: flex; gap: 10px; align-items: center;">
          <div class="search-box-custom">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
              type="text" 
              v-model="searchTransactionQuery" 
              placeholder="Rechercher par référence..." 
              class="search-input-custom"
            />
          </div>
          <div class="export-dropdown-wrapper">
            <button @click.stop="toggleExportMenu" class="btn-export-trigger">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              <span>Exporter</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 2px;">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </button>
            <div v-show="showExportMenu" class="export-menu">
              <button @click="exportData('csv')" class="export-item">
                <span class="file-icon csv">CSV</span>
                <span>Format CSV (.csv)</span>
              </button>
              <button @click="exportData('xlsx')" class="export-item">
                <span class="file-icon xlsx">XLSX</span>
                <span>Format Excel (.xls)</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="table-card overflow-hidden">
        <table class="saas-table" style="min-width: 950px; width: 100%;">
          <thead>
            <tr>
              <th style="width: 15%">Type Doc</th>
              <th style="width: 15%">Référence</th>
              <th style="width: 15%">Date</th>
              <th style="width: 15%" class="text-right">Montant HT</th>
              <th style="width: 15%" class="text-right">Montant TTC</th>
              <th style="width: 15%" class="text-center">Statut</th>
              <th style="width: 10%" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="t in filteredTransactions" 
              :key="t.type + '-' + t.id" 
              class="ligne-row"
            >
              <td>
                <span class="type-tag" :class="t.type">
                  {{ t.typeLabel }}
                </span>
              </td>
              <td>
                <router-link :to="t.link" class="ref-link mono">
                  {{ t.numero || '—' }}
                </router-link>
              </td>
              <td class="text-muted font-medium">
                {{ formatDate(t.date) }}
              </td>
              <td class="text-right mono font-bold accent-amount">
                {{ t.total_ht !== null ? formatMoney(t.total_ht) + ' DH' : '—' }}
              </td>
              <td class="text-right mono font-bold accent-amount">
                {{ t.total_ttc !== null ? formatMoney(t.total_ttc) + ' DH' : '—' }}
              </td>
              <td class="text-center">
                <span 
                  class="status-badge" 
                  :style="getStatusBadgeStyle(t)"
                >
                  {{ t.etat?.libelle || getDefaultStatusLabel(t) }}
                </span>
              </td>
              <td class="text-center">
                <router-link :to="t.link" class="btn-consult" title="Consulter le document">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </router-link>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredTransactions.length === 0">
              <td colspan="7" class="text-center">
                <div class="empty-state-box">
                  <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                  </svg>
                  <p class="empty-title">Aucune transaction trouvée</p>
                  <p class="empty-desc">Il n'y a aucun document commercial correspondant à ces critères pour ce projet.</p>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'

const props = defineProps({ id: [String, Number] })
const route = useRoute()
const editMode = ref(false)
const loading = ref(true)

const projet = ref({
  id: null,
  code_projet: '',
  nom_projet: '',
  client: null,
  date_debut: '',
  date_fin_prevue: '',
  budget_prevu: 0,
  budget_consomme: 0,
  statut: 'en_cours',
  etat_id: null,
  etat: null,
  avancement_pcent: 0
})

const etats = ref([])

const form = ref({ ...projet.value })

const activeTab = ref('fiche') // 'fiche' or 'transactions'
const subFilter = ref('all') // 'all', 'devis', 'bcc', 'bl', 'facture'
const searchTransactionQuery = ref('')

function formatDate(dStr) {
  if (!dStr) return '—'
  const date = new Date(dStr)
  if (isNaN(date.getTime())) return dStr
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const showExportMenu = ref(false)

function toggleExportMenu() {
  showExportMenu.value = !showExportMenu.value
}

function closeExportMenu(e) {
  if (!e.target.closest('.export-dropdown-wrapper')) {
    showExportMenu.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', closeExportMenu)
})

onBeforeUnmount(() => {
  window.removeEventListener('click', closeExportMenu)
})

function exportData(format) {
  showExportMenu.value = false
  if (format === 'csv') {
    exportToCSV()
  } else {
    exportToExcel()
  }
}

function exportToCSV() {
  const headers = ['Type Document', 'Référence', 'Date', 'Montant HT (DH)', 'Montant TTC (DH)', 'Statut']
  const rows = filteredTransactions.value.map(t => [
    t.typeLabel,
    t.numero || '',
    formatDate(t.date),
    t.total_ht !== null ? (parseFloat(t.total_ht) || 0).toFixed(2) : '',
    t.total_ttc !== null ? (parseFloat(t.total_ttc) || 0).toFixed(2) : '',
    t.etat?.libelle || getDefaultStatusLabel(t)
  ])

  const csvContent = "\uFEFF" + [
    headers.join(';'),
    ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(';'))
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  
  const prefix = projet.value?.nom_projet || 'projet_transactions'
  const filename = `transactions_projet_${prefix.toLowerCase().replace(/[^a-z0-9]/g, '_')}.csv`
  
  link.setAttribute("download", filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function escapeXML(str) {
  if (str === null || str === undefined) return ''
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;')
}

function exportToExcel() {
  const headers = ['Type Document', 'Référence', 'Date', 'Montant HT (DH)', 'Montant TTC (DH)', 'Statut']
  const rows = filteredTransactions.value.map(t => [
    t.typeLabel,
    t.numero || '',
    formatDate(t.date),
    t.total_ht !== null ? (parseFloat(t.total_ht) || 0).toFixed(2) : '0.00',
    t.total_ttc !== null ? (parseFloat(t.total_ttc) || 0).toFixed(2) : '0.00',
    t.etat?.libelle || getDefaultStatusLabel(t)
  ])

  let xml = '\x3C?xml version="1.0" encoding="utf-8"?\x3E\n'
  xml += '\x3C?mso-application progid="Excel.Sheet"?\x3E\n'
  xml += '\x3CWorkbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"\n'
  xml += ' xmlns:o="urn:schemas-microsoft-com:office:office"\n'
  xml += ' xmlns:x="urn:schemas-microsoft-com:office:excel"\n'
  xml += ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"\n'
  xml += ' xmlns:html="http://www.w3.org/TR/REC-html40"\x3E\n'
  xml += ' \x3CWorksheet ss:Name="Transactions"\x3E\n'
  xml += '  \x3CTable\x3E\n'
  xml += '   \x3CRow\x3E\n'
  
  headers.forEach(h => {
    xml += '    \x3CCell\x3E\x3CData ss:Type="String"\x3E' + escapeXML(h) + '\x3C/Data\x3E\x3C/Cell\x3E\n'
  })
  xml += '   \x3C/Row\x3E\n'

  rows.forEach(row => {
    xml += '   \x3CRow\x3E\n'
    xml += '    \x3CCell\x3E\x3CData ss:Type="String"\x3E' + escapeXML(row[0]) + '\x3C/Data\x3E\x3C/Cell\x3E\n'
    xml += '    \x3CCell\x3E\x3CData ss:Type="String"\x3E' + escapeXML(row[1]) + '\x3C/Data\x3E\x3C/Cell\x3E\n'
    xml += '    \x3CCell\x3E\x3CData ss:Type="String"\x3E' + escapeXML(row[2]) + '\x3C/Data\x3E\x3C/Cell\x3E\n'
    xml += '    \x3CCell\x3E\x3CData ss:Type="Number"\x3E' + row[3] + '\x3C/Data\x3E\x3C/Cell\x3E\n'
    xml += '    \x3CCell\x3E\x3CData ss:Type="Number"\x3E' + row[4] + '\x3C/Data\x3E\x3C/Cell\x3E\n'
    xml += '    \x3CCell\x3E\x3CData ss:Type="String"\x3E' + escapeXML(row[5]) + '\x3C/Data\x3E\x3C/Cell\x3E\n'
    xml += '   \x3C/Row\x3E\n'
  })

  xml += '  \x3C/Table\x3E\n'
  xml += ' \x3C/Worksheet\x3E\n'
  xml += '\x3C/Workbook\x3E'

  const blob = new Blob([xml], { type: 'application/vnd.ms-excel;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  const prefix = projet.value?.nom_projet || 'projet_transactions'
  const filename = `transactions_projet_${prefix.toLowerCase().replace(/[^a-z0-9]/g, '_')}.xls`
  link.setAttribute("download", filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const allTransactions = computed(() => {
  const list = []

  // Devis
  if (projet.value?.devis) {
    projet.value.devis.forEach(d => {
      list.push({
        id: d.id,
        type: 'devis',
        typeLabel: 'Devis',
        numero: d.numero,
        date: d.date_devis || d.created_at,
        total_ht: d.total_ht,
        total_ttc: d.total_ttc,
        etat: d.etat,
        link: `/devis/${d.id}`
      })
    })
  }

  // BCC
  if (projet.value?.bons_commande) {
    projet.value.bons_commande.forEach(bcc => {
      list.push({
        id: bcc.id,
        type: 'bcc',
        typeLabel: 'Commande',
        numero: bcc.numero,
        date: bcc.date_commande || bcc.created_at,
        total_ht: bcc.total_ht,
        total_ttc: bcc.total_ttc,
        etat: bcc.etat,
        link: `/bons-commande-client/${bcc.id}`
      })
    })
  }

  // BL
  if (projet.value?.bons_livraison) {
    projet.value.bons_livraison.forEach(blDoc => {
      list.push({
        id: blDoc.id,
        type: 'bl',
        typeLabel: 'Livraison',
        numero: blDoc.numero,
        date: blDoc.date_livraison || blDoc.created_at,
        total_ht: blDoc.total_ht,
        total_ttc: blDoc.total_ttc,
        etat: blDoc.etat,
        link: `/bons-livraison/${blDoc.id}`
      })
    })
  }

  // Factures
  if (projet.value?.factures) {
    projet.value.factures.forEach(f => {
      list.push({
        id: f.id,
        type: 'facture',
        typeLabel: 'Facture',
        numero: f.numero,
        date: f.date_facture || f.created_at,
        total_ht: f.total_ht,
        total_ttc: f.total_ttc,
        etat: f.etat,
        link: `/factures/${f.id}`
      })
    })
  }

  return list.sort((a, b) => {
    const dateA = new Date(a.date)
    const dateB = new Date(b.date)
    return dateB - dateA
  })
})

const totalTransactionsCount = computed(() => allTransactions.value.length)

const countsByType = computed(() => {
  return {
    all: allTransactions.value.length,
    devis: allTransactions.value.filter(t => t.type === 'devis').length,
    bcc: allTransactions.value.filter(t => t.type === 'bcc').length,
    bl: allTransactions.value.filter(t => t.type === 'bl').length,
    facture: allTransactions.value.filter(t => t.type === 'facture').length,
  }
})

const filteredTransactions = computed(() => {
  let result = allTransactions.value

  if (subFilter.value !== 'all') {
    result = result.filter(t => t.type === subFilter.value)
  }

  if (searchTransactionQuery.value.trim()) {
    const q = searchTransactionQuery.value.toLowerCase()
    result = result.filter(t => 
      (t.numero && t.numero.toLowerCase().includes(q)) || 
      (t.typeLabel && t.typeLabel.toLowerCase().includes(q))
    )
  }

  return result
})

function getStatusBadgeStyle(t) {
  if (t.etat?.couleur) {
    return {
      backgroundColor: `${t.etat.couleur}15`,
      color: t.etat.couleur,
      borderColor: `${t.etat.couleur}30`
    }
  }
  return {
    backgroundColor: '#f3f4f6',
    color: '#6b7280',
    borderColor: '#e5e7eb'
  }
}

function getDefaultStatusLabel(t) {
  return 'Brouillon'
}

function formatStatut(status) {
  const map = {
    brouillon: 'Brouillon',
    en_cours: 'En Cours',
    en_pause: 'Suspendu',
    termine: 'Terminé',
    annule: 'Annulé'
  }
  return map[status] || status
}

const projectInitials = computed(() => {
  const name = projet.value?.nom_projet || ''
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() || 'PJ'
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function fetchEtats() {
  try {
    const { data } = await api.get('/parametrage/referentiels/etats?type_document=projet')
    etats.value = data.data || data
  } catch (e) {
    console.error("Erreur états", e)
  }
}

async function fetchProjet() {
  loading.value = true
  try {
    const id = props.id || route.params.id
    const { data } = await api.get(`/projets/${id}`)
    projet.value = data.data || data
    form.value = { ...projet.value }
  } catch (error) {
    console.error('Erreur chargement projet:', error)
  } finally {
    loading.value = false
  }
}

async function saveProjet() {
  try {
    await api.put(`/projets/${projet.value.id}`, form.value)
    projet.value = { ...form.value }
    editMode.value = false
    toast.success('Projet mis à jour avec succès.')
  } catch (e) {
    toast.error('Erreur lors de la sauvegarde.')
  }
}

onMounted(() => {
  fetchProjet()
  fetchEtats()
  if (route.query.tab === 'transactions') {
    activeTab.value = 'transactions'
  }
})
</script>

<style scoped>
/* ─── Design Tokens (Identiques à ClientDetail) ─────────────────────────────── */
.project-detail-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-subtle:     #F1F3F6;
  --c-accent:     #2563EB;
  --c-accent-bg:  #EEF4FF;
  --c-danger:     #DC2626;
  --c-danger-bg:  #FEF2F2;
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

  
  color: var(--c-text);
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
}

/* ─── Loading & Skeletons (Identiques) ───────────────────────────────────────── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 100;
  background: rgba(247,248,250,0.85); backdrop-filter: blur(4px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { display: inline-block; position: relative; width: 48px; height: 48px; }
.loader-ring div {
  box-sizing: border-box; display: block; position: absolute; width: 38px; height: 38px; margin: 5px;
  border: 3px solid transparent; border-radius: 50%; animation: loader-spin 1.1s infinite; border-top-color: var(--c-accent);
}
@keyframes loader-spin { to { transform: rotate(360deg); } }

/* ─── Top Bar ────────────────────────────────────────────────────────────────── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn {
  display: flex; align-items: center; justify-content: center; width: 34px; height: 34px;
  border-radius: 50%; border: 1.5px solid var(--c-border-mid); background: var(--c-surface);
  color: var(--c-muted); transition: all .18s; box-shadow: var(--shadow-sm);
}
.back-btn:hover { border-color: var(--c-accent); color: var(--c-accent); transform: translateX(-1px); }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 600; }

.topbar-actions { display: flex; gap: 10px; }
.btn-secondary-custom, .btn-save {
  display: inline-flex; align-items: center; gap: 7px; padding: 8px 18px; border-radius: var(--radius-sm);
  font-size: .82rem; font-weight: 600; cursor: pointer; transition: all .18s; border: none;
}
.btn-secondary-custom { background: var(--c-subtle); color: var(--c-muted); }
.btn-save { background: var(--c-accent); color: #fff; box-shadow: 0 1px 4px rgba(37,99,235,.3); }

/* ─── Hero ───────────────────────────────────────────────────────────────────── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: var(--c-surface);
  border: 1px solid var(--c-border); border-radius: var(--radius-lg);
  padding: 22px 28px; margin-bottom: 20px; box-shadow: var(--shadow-sm);
}
.hero-avatar {
  width: 56px; height: 56px; border-radius: 14px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.15rem; font-weight: 800;
}
.hero-meta { flex: 1; }
.hero-type-badge {
  display: inline-flex; align-items: center; gap: 5px; font-size: .68rem;
  font-weight: 700; text-transform: uppercase; color: #6366f1; margin-bottom: 5px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: #6366f1; border-radius: 50%; }
.hero-name { font-size: 1.45rem; font-weight: 800; margin: 0 0 4px; }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 0; }

.hero-status-badge {
  padding: 7px 14px; border-radius: 100px; font-size: .75rem; font-weight: 700;
}
.hero-status-badge.en_cours { background: var(--c-accent-bg); color: var(--c-accent); }
.hero-status-badge.termine  { background: var(--c-success-bg); color: var(--c-success); }
.hero-status-badge.brouillon { background: var(--c-subtle); color: var(--c-muted); }

.hero-status-badge-dynamic { padding: 8px 20px; border-radius: 100px; font-size: .75rem; font-weight: 800; border: 1px solid transparent; }

/* ─── KPI Strip ──────────────────────────────────────────────────────────────── */
.kpi-strip {
  display: flex; background: var(--c-surface); border: 1px solid var(--c-border);
  border-radius: var(--radius-lg); margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-sm);
}
.kpi-item { flex: 1; display: flex; align-items: center; gap: 14px; padding: 18px 22px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.kpi-item.accent  .kpi-icon { background: var(--c-accent-bg); color: var(--c-accent); }
.kpi-item.neutral .kpi-icon { background: var(--c-neutral-bg); color: #6366f1; }
.kpi-item.danger .kpi-icon  { background: var(--c-danger-bg); color: var(--c-danger); }

.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0 0 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .65; margin-left: 3px; }

/* ─── Content Grid & Cards ───────────────────────────────────────────────────── */
.content-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }
.col-left, .col-right { display: flex; flex-direction: column; gap: 20px; }

.info-card {
  background: var(--c-surface); border: 1px solid var(--c-border);
  border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;
}
.card-header {
  display: flex; align-items: center; gap: 10px; padding: 16px 20px;
  border-bottom: 1px solid var(--c-border); background: var(--c-subtle);
}
.card-header h3 { font-size: .77rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon {
  width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center;
  justify-content: center; background: var(--c-accent-bg); color: var(--c-accent);
}
.card-header-icon.contact { background: var(--c-success-bg); color: var(--c-success); }
.card-header-icon.bank    { background: #FFF7ED; color: #EA580C; }

.card-body { padding: 18px 20px; }
.field-row { display: flex; justify-content: space-between; padding: 8px 0; }
.field-label { font-size: .8rem; color: var(--c-muted); font-weight: 500; }
.field-value { font-size: .85rem; font-weight: 600; }
.field-value.mono { font-family: 'JetBrains Mono', monospace; color: var(--c-accent); }
.field-separator { height: 1px; background: var(--c-border); margin: 10px 0; }

/* ─── Form Elements ──────────────────────────────────────────────────────────── */
.edit-form { display: flex; flex-direction: column; gap: 14px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; }
.form-group-custom label { font-size: .75rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-group-custom input, .form-group-custom select {
  padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--c-border-mid);
  font-size: .9rem; font-family: inherit; transition: border-color .2s;
}
.form-group-custom input:focus { border-color: var(--c-accent); outline: none; }
.form-row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* ─── Client Mini Card ───────────────────────────────────────────────────────── */
.client-mini-card { display: flex; flex-direction: column; gap: 10px; }
.client-name { font-weight: 700; font-size: 1rem; margin: 0; }
.client-link-btn {
  display: inline-block; padding: 6px 12px; background: var(--c-accent-bg);
  color: var(--c-accent); border-radius: 6px; font-size: .75rem; font-weight: 700;
  text-decoration: none; text-align: center;
}
.notes-empty { color: var(--c-muted); font-style: italic; font-size: .85rem; text-align: center; padding: 10px; }

@media (max-width: 900px) {
  .content-grid { grid-template-columns: 1fr; }
  .kpi-strip { flex-direction: column; }
  .kpi-divider { height: 1px; width: auto; margin: 0 16px; }
}

/* ─── Detail Tabs Navigation ────────────────────────────────────────────────── */
.detail-tabs {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid var(--c-border);
  margin: 24px 0;
  padding-bottom: 2px;
}
.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  font-size: .88rem;
  font-weight: 700;
  color: var(--c-muted);
  border: none;
  background: transparent;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s ease;
  position: relative;
  outline: none;
}
.tab-btn:hover {
  color: var(--c-text);
}
.tab-btn.active {
  color: var(--c-accent);
  border-bottom-color: var(--c-accent);
}
.tab-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--c-accent-bg);
  color: var(--c-accent);
  font-size: 0.72rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 20px;
  margin-left: 4px;
}

/* ─── Transactions Tab Styles ────────────────────────────────────────────── */
.transactions-tab-container {
  display: flex;
  flex-direction: column;
}
.transactions-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.sub-filters {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.filter-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--c-muted);
  cursor: pointer;
  transition: all 0.2s ease;
  outline: none;
}
.filter-pill:hover {
  background: var(--c-subtle);
  color: var(--c-text);
}
.filter-pill.active {
  background: var(--c-accent);
  color: #fff;
  border-color: var(--c-accent);
}
.pill-count {
  font-size: 0.7rem;
  background: rgba(0, 0, 0, 0.08);
  color: inherit;
  padding: 1px 6px;
  border-radius: 10px;
  font-weight: 700;
}
.filter-pill.active .pill-count {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}

/* Search Box Custom */
.search-box-custom {
  position: relative;
  max-width: 320px;
  width: 100%;
}
.search-input-custom {
  width: 100%;
  padding: 8px 12px 8px 36px;
  font-size: .82rem;
  border: 1.5px solid var(--c-border-mid);
  border-radius: 8px;
  background: var(--c-surface);
  outline: none;
  transition: all 0.2s;
}
.search-input-custom:focus {
  border-color: var(--c-accent);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.search-box-custom .search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--c-muted);
  pointer-events: none;
}

/* Type Tags */
.type-tag {
  display: inline-block;
  padding: 3px 8px;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  border-radius: 6px;
  letter-spacing: 0.02em;
}
.type-tag.devis { background: #FEF3C7; color: #D97706; }
.type-tag.bcc { background: #E0F2FE; color: #0284C7; }
.type-tag.bl { background: #F3E8FF; color: #7C3AED; }
.type-tag.facture { background: #D1FAE5; color: #059669; }

/* Status Badges & Links */
.status-badge {
  display: inline-block;
  padding: 4px 10px;
  font-size: 0.72rem;
  font-weight: 700;
  border-radius: 100px;
  border: 1px solid transparent;
}
.ref-link {
  color: var(--c-accent);
  text-decoration: none;
  font-weight: 700;
  transition: opacity 0.15s;
}
.ref-link:hover {
  text-decoration: underline;
}
.accent-amount {
  color: var(--c-text);
}
.btn-consult {
  background: var(--c-subtle);
  color: var(--c-muted);
  border: none;
  padding: 6px;
  border-radius: 6px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
  text-decoration: none;
}
.btn-consult:hover {
  background: var(--c-accent-bg);
  color: var(--c-accent);
}

/* Empty State */
.empty-state-box {
  padding: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--c-muted);
}
.empty-state-icon {
  color: var(--c-border-mid);
  margin-bottom: 12px;
}
.empty-title {
  font-weight: 700;
  font-size: 0.92rem;
  color: var(--c-text);
  margin: 0 0 4px;
}
.empty-desc {
  font-size: 0.8rem;
  max-width: 320px;
  margin: 0;
  line-height: 1.4;
}

.saas-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.saas-table th { background: #F9FAFB; padding: 13px 10px; font-size: .63rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 2px solid var(--c-border); letter-spacing: .04em; }
.saas-table td { padding: 14px 10px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
.saas-table th.text-center, .saas-table td.text-center { text-align: center; }
.saas-table th.text-right, .saas-table td.text-right { text-align: right; }

.ligne-row { background: #FCFDFE; transition: background .15s; }
.ligne-row:nth-child(even) { background: #F5F8FF; }
.ligne-row:hover { background: #EEF4FF !important; }
.ligne-row:last-child td { border-bottom: none; }

/* ─── Export Dropdown Premium Styles ───────────────────────────────────────── */
.export-dropdown-wrapper {
  position: relative;
  display: inline-block;
}
.btn-export-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: var(--c-surface, #fff);
  border: 1.5px solid var(--c-border-mid, #D5D9E2);
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--c-text, #1A1D23);
  cursor: pointer;
  transition: all 0.2s ease;
  height: 36px;
  outline: none;
}
.btn-export-trigger:hover {
  border-color: var(--c-accent, #2563EB);
  color: var(--c-accent, #2563EB);
  background: var(--c-accent-bg, #EEF4FF);
}
.export-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 6px);
  background: var(--c-surface, #fff);
  border: 1px solid var(--c-border, #E8EAEE);
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  z-index: 100;
  width: 190px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.export-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: transparent;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--c-text, #1A1D23);
  cursor: pointer;
  text-align: left;
  transition: background 0.15s ease;
  width: 100%;
}
.export-item:hover {
  background: var(--c-subtle, #F1F3F6);
}
.file-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 5px;
  border-radius: 4px;
  color: #fff;
  min-width: 32px;
}
.file-icon.csv {
  background: #0284C7;
}
.file-icon.xlsx {
  background: #16A34A;
}
</style>