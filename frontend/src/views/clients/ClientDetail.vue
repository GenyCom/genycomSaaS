<template>
  <div class="client-detail-view">

    <!-- Loading Overlay -->
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement du client…</p>
      </div>
    </Transition>

    <!-- Top Bar -->
    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/clients" class="back-btn" title="Retour aux clients">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Clients</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span v-if="loading" class="breadcrumb-skeleton"></span>
          <span v-else class="breadcrumb-current">{{ client.societe || '—' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link :to="`/clients/${client.id}/edit`" class="btn-edit">
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
      <div class="hero-avatar">
        <span>{{ avatarInitials }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ client.type_client?.libelle || 'Standard' }}
        </div>
        <h1 class="hero-name">
          <span v-if="loading" class="skeleton-line wide"></span>
          <template v-else>{{ client.societe || 'Chargement…' }}</template>
        </h1>
        <p class="hero-sub">
          <span v-if="loading" class="skeleton-line narrow"></span>
          <template v-else>
            Réf. <strong>{{ client.code_client || '—' }}</strong>
            <span v-if="client.ville"> · {{ (client.ville || '').toUpperCase() }}, {{ client.pays }}</span>
          </template>
        </p>
      </div>
      <div class="hero-tva-badge" :class="client.exempt_tva ? 'exempt' : 'standard'">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <path v-if="client.exempt_tva" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <path v-else d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ client.exempt_tva ? 'Exonéré TVA — Art. 92' : 'TVA Standard 20%' }}
      </div>
      <div v-if="client.is_default" class="hero-tva-badge standard" style="background: var(--c-accent-bg); color: var(--c-accent); border-color: var(--c-accent-mid);">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Client Comptoir par Défaut
      </div>
    </div>

    <!-- KPI Strip -->
    <div class="kpi-strip">
      <div class="kpi-item danger">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Reste à Encaisser</p>
          <p class="kpi-value">{{ formatMoney(client.montant_rest_du) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Plafond Crédit</p>
          <p class="kpi-value">{{ formatMoney(client.plafond_credit) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Délai de Règlement</p>
          <p class="kpi-value">{{ client.delai_paiement || 0 }} <span>jours</span></p>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="detail-tabs">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'fiche' }" 
        @click="activeTab = 'fiche'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <span>Fiche Client</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'transactions' }" 
        @click="activeTab = 'transactions'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="165" y1="1" x2="165" y2="1"/><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span>Transactions Commerciales</span>
        <span class="tab-badge" v-if="totalTransactionsCount > 0">{{ totalTransactionsCount }}</span>
      </button>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid" v-if="activeTab === 'fiche'">

      <!-- Column Left -->
      <div class="col-left">

        <!-- Identity Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <h3>Identité & Fiscalité</h3>
          </div>
          <div class="card-body">
            <div class="field-row">
              <span class="field-label">Code Client</span>
              <span class="field-value mono accent">{{ client.code_client || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">Type</span>
              <span class="field-value">
                <span class="tag">{{ client.type_client?.libelle || 'STANDARD' }}</span>
              </span>
            </div>
            <div class="field-separator"></div>
            <div class="field-row">
              <span class="field-label">ICE</span>
              <span class="field-value mono">{{ client.ice || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">RC</span>
              <span class="field-value mono">{{ client.rc || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">IF (Id. Fiscal)</span>
              <span class="field-value mono">{{ client.if_fiscal || '—' }}</span>
            </div>
            <div class="field-row">
              <span class="field-label">Patente</span>
              <span class="field-value mono">{{ client.patente || '—' }}</span>
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
              <span class="field-label">Banque Principale</span>
              <span class="field-value bold">{{ client.banque || 'Non renseignée' }}</span>
            </div>
            <div class="field-col mt-4">
              <span class="field-label">RIB</span>
              <div class="rib-display">
                <span class="rib-code">{{ formatRIB(client.rib) }}</span>
                <button class="copy-btn" @click="copyRIB" :class="{ copied: ribCopied }" title="Copier le RIB">
                  <svg v-if="!ribCopied" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Conditions Commerciales Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon commercial" style="background: var(--c-accent-bg); color: var(--c-accent);">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M21 12H3m9-9v18"/></svg>
            </div>
            <h3>Conditions Commerciales</h3>
          </div>
          <div class="card-body">
            <div class="field-row">
              <span class="field-label">Taux de remise par défaut</span>
              <span class="field-value mono accent font-bold">{{ parseFloat(client.taux_remise || 0).toFixed(2) }}%</span>
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
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
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
                    <p class="contact-label">Responsable</p>
                    <p class="contact-value">{{ client.civilite }} {{ client.prenom }} {{ client.nom || '—' }}</p>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Email</p>
                    <a :href="`mailto:${client.email}`" class="contact-link">{{ client.email || 'Non renseigné' }}</a>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.528.296 1.045.506 1.547"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Téléphone / GSM</p>
                    <p class="contact-value">
                      {{ client.telephone || '—' }}
                      <span v-if="client.mobile" class="contact-secondary"> · {{ client.mobile }}</span>
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
                    <p class="contact-label">Adresse du Siège</p>
                    <p class="contact-value address">
                      {{ client.adresse || '—' }}<br v-if="client.adresse"/>
                      <span v-if="client.code_postal || client.ville">{{ client.code_postal }} {{ (client.ville || '').toUpperCase() }}</span><br v-if="client.pays"/>
                      <span v-if="client.pays" class="contact-secondary">{{ client.pays }}</span>
                    </p>
                  </div>
                </div>

                <div v-if="client.site_web" class="contact-item">
                  <div class="contact-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                  </div>
                  <div>
                    <p class="contact-label">Site Web</p>
                    <a :href="client.site_web" target="_blank" rel="noopener" class="contact-link">{{ client.site_web }}</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Notes Card -->
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3>Observations</h3>
          </div>
          <div class="card-body">
            <div class="notes-content" :class="{ empty: !client.observations }">
              <p v-if="client.observations">{{ client.observations }}</p>
              <div v-else class="notes-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Aucune note particulière pour ce client.</span>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>

    <!-- Transactions Tab View -->
    <div class="transactions-tab-container" v-else-if="activeTab === 'transactions'">
      
      <!-- Sub-filters and Search Bar -->
      <div class="transactions-header-bar">
        <div class="sub-filters">
          <button class="filter-pill" :class="{ active: subFilter === 'all' }" @click="subFilter = 'all'">
            Tout <span class="pill-count">{{ countsByType.all }}</span>
          </button>
          <button class="filter-pill" :class="{ active: subFilter === 'devis' }" @click="subFilter = 'devis'">
            Devis <span class="pill-count">{{ countsByType.devis }}</span>
          </button>
          <button class="filter-pill" :class="{ active: subFilter === 'bcc' }" @click="subFilter = 'bcc'">
            Commandes <span class="pill-count">{{ countsByType.bcc }}</span>
          </button>
          <button class="filter-pill" :class="{ active: subFilter === 'bl' }" @click="subFilter = 'bl'">
            Livraisons <span class="pill-count">{{ countsByType.bl }}</span>
          </button>
          <button class="filter-pill" :class="{ active: subFilter === 'facture' }" @click="subFilter = 'facture'">
            Factures <span class="pill-count">{{ countsByType.facture }}</span>
          </button>
          <button class="filter-pill" :class="{ active: subFilter === 'avoir' }" @click="subFilter = 'avoir'">
            Avoirs <span class="pill-count">{{ countsByType.avoir }}</span>
          </button>
        </div>

        <!-- Search & Export Actions -->
        <div style="display: flex; gap: 10px; align-items: center;">
          <div class="search-box-custom">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
              type="text" 
              v-model="searchTransactionQuery" 
              placeholder="Rechercher par N°..." 
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

      <!-- Transactions Grid / Table -->
      <div class="info-card mt-4" style="overflow-x: auto;">
        <table class="saas-table" style="min-width: 900px; width: 100%;">
          <thead>
            <tr>
              <th style="width: 15%">Type</th>
              <th style="width: 20%">Référence</th>
              <th style="width: 15%">Date</th>
              <th style="width: 15%" class="text-right">Total HT</th>
              <th style="width: 18%" class="text-right">Total TTC</th>
              <th style="width: 12%" class="text-center">Statut</th>
              <th style="width: 5%"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in filteredTransactions" :key="`${t.type}-${t.id}`" class="ligne-row">
              <td>
                <span class="type-tag" :class="t.type">
                  {{ t.typeLabel }}
                </span>
              </td>
              <td>
                <router-link :to="t.link" class="ref-link bold mono">{{ t.numero || 'N/A' }}</router-link>
              </td>
              <td class="date-cell">
                {{ formatDate(t.date) }}
              </td>
              <td class="text-right mono font-medium">
                {{ formatMoney(t.total_ht) }} DH
              </td>
              <td class="text-right mono font-bold accent-amount">
                {{ formatMoney(t.total_ttc) }} DH
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
                  <p class="empty-desc">Il n'y a aucun document commercial correspondant à ces critères pour ce client.</p>
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
import { ref, watch, onMounted, computed, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const props = defineProps({ id: [String, Number] })
const route = useRoute()
const client = ref({})
const loading = ref(true)
const ribCopied = ref(false)

const activeTab = ref('fiche') // 'fiche' or 'transactions'
const subFilter = ref('all') // 'all', 'devis', 'bcc', 'bl', 'facture', 'avoir'
const searchTransactionQuery = ref('')

const avatarInitials = computed(() => {
  const name = client.value?.societe || ''
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() || 'CL'
})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

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
    t.etat?.libelle || 'Brouillon'
  ])

  const csvContent = "\uFEFF" + [
    headers.join(';'),
    ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(';'))
  ].join('\n')

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  
  const prefix = client.value?.societe || 'client_transactions'
  const filename = `transactions_client_${prefix.toLowerCase().replace(/[^a-z0-9]/g, '_')}.csv`
  
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
    t.etat?.libelle || 'Brouillon'
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
  const prefix = client.value?.societe || 'client_transactions'
  const filename = `transactions_client_${prefix.toLowerCase().replace(/[^a-z0-9]/g, '_')}.xls`
  link.setAttribute("download", filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function formatRIB(rib) {
  if (!rib) return 'NON RENSEIGNÉ'
  const clean = rib.replace(/\s+/g, '')
  return clean.match(/.{1,4}/g)?.join(' ') || clean
}

async function copyRIB() {
  const rib = client.value?.rib
  if (!rib) return
  try {
    await navigator.clipboard.writeText(rib)
    ribCopied.value = true
    setTimeout(() => ribCopied.value = false, 2000)
  } catch { /* silent */ }
}

async function fetchClient(id) {
  if (!id) return
  loading.value = true
  try {
    const { data } = await api.get(`/clients/${id}`)
    client.value = data.data || data
  } catch (error) {
    console.error('Erreur API Client:', error)
  } finally {
    loading.value = false
  }
}

const allTransactions = computed(() => {
  const list = []
  
  // Devis
  if (client.value?.devis) {
    client.value.devis.forEach(d => {
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
  if (client.value?.bons_commande) {
    client.value.bons_commande.forEach(bcc => {
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
  if (client.value?.bons_livraison) {
    client.value.bons_livraison.forEach(blDoc => {
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
  if (client.value?.factures) {
    client.value.factures.forEach(f => {
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

  // Avoirs
  if (client.value?.avoirs) {
    client.value.avoirs.forEach(av => {
      list.push({
        id: av.id,
        type: 'avoir',
        typeLabel: 'Avoir',
        numero: av.numero,
        date: av.date_avoir || av.created_at,
        total_ht: av.total_ht,
        total_ttc: av.total_ttc,
        etat: av.etat,
        link: `/avoirs-clients/${av.id}`
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
    avoir: allTransactions.value.filter(t => t.type === 'avoir').length,
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
  if (t.type === 'avoir') {
    return {
      backgroundColor: '#fce7f3',
      color: '#db2777',
      borderColor: '#fbcfe8'
    }
  }
  return {
    backgroundColor: '#f3f4f6',
    color: '#6b7280',
    borderColor: '#e5e7eb'
  }
}

function getDefaultStatusLabel(t) {
  if (t.type === 'avoir') return 'Validé'
  return 'Brouillon'
}

watch(() => props.id, (newId) => fetchClient(newId), { immediate: true })
onMounted(() => { if (!props.id && route.params.id) fetchClient(route.params.id) })
</script>

<style scoped>
/* ─── Design Tokens ─────────────────────────────────────────────────────────── */
.client-detail-view {
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

.hero-tva-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 14px;
  border-radius: 100px;
  font-size: .75rem;
  font-weight: 700;
  margin-left: auto;
  flex-shrink: 0;
}
.hero-tva-badge.standard { background: var(--c-success-bg); color: var(--c-success); }
.hero-tva-badge.exempt   { background: var(--c-warn-bg);    color: var(--c-warn);    }

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
.kpi-item.danger .kpi-icon  { background: var(--c-danger-bg);  color: var(--c-danger); }
.kpi-item.accent  .kpi-icon  { background: var(--c-accent-bg);  color: var(--c-accent); }
.kpi-item.neutral .kpi-icon  { background: var(--c-neutral-bg); color: #6366f1; }

.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted); margin: 0 0 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; line-height: 1; }
.kpi-value span { font-size: .7rem; font-weight: 600; opacity: .65; text-transform: uppercase; margin-left: 3px; }
.kpi-item.danger .kpi-value  { color: var(--c-danger); }
.kpi-item.accent  .kpi-value  { color: var(--c-accent); }
.kpi-item.neutral .kpi-value  { color: #6366f1; }

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
.card-header-icon.bank    { background: #FFF7ED; color: #EA580C; }
.card-header-icon.contact { background: var(--c-success-bg); color: var(--c-success); }
.card-header-icon.notes   { background: #FFF1F2; color: #E11D48; }

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
  .client-detail-view { padding: 16px 14px 40px; }
  .kpi-strip { flex-direction: column; }
  .kpi-divider { width: auto; height: 1px; margin: 0 16px; }
  .hero-header { gap: 14px; padding: 16px 18px; }
  .hero-name { font-size: 1.2rem; }
  .hero-tva-badge { margin-left: 0; width: 100%; justify-content: center; }
  .contact-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .btn-edit span { display: none; }
  .hero-avatar { width: 46px; height: 46px; font-size: .95rem; }
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
.type-tag.avoir { background: #FCE7F3; color: #DB2777; }

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