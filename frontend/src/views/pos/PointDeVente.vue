<template>
  <div class="pos-screen" :class="{ 'is-rectifying-mode': isRectifyingSale }">
    <!-- ═ ═ ═ RECTIFICATION BANNER ═ ═ ═ -->
    <div v-if="isRectifyingSale" class="pos-rectify-top-banner">
      <div class="pos-rectify-banner-content">
        <span class="pos-rectify-badge">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          MODE RECTIFICATION
        </span>
        <span class="pos-rectify-text">
          Modification de la Vente <strong>#{{ rectifyingSaleNumero }}</strong>
        </span>
        <span class="pos-rectify-diff-pill" :class="rectifyDiffClass">
          {{ rectifyDiffText }}
        </span>
      </div>
      <button class="pos-rectify-abort-btn" @click="abortRectification">
        ✕ Annuler la rectification
      </button>
    </div>
    <!-- ═══ LEFT PANEL — CART ═══ -->
    <section class="pos-cart">
      <div class="pos-cart-header">
        <div class="pos-brand">
          <div class="pos-brand-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          </div>
          <div>
            <div class="pos-brand-title">Point de Vente</div>
            <div class="pos-brand-sub">{{ auth.tenant?.nom || 'GenyCom' }}</div>
          </div>
        </div>
        <button class="pos-exit-btn" @click="handleExitPos" title="Retour à GenyCom">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </div>

      <!-- Barcode / Search -->
      <div class="pos-search-bar">
        <div class="pos-search-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <input
          ref="barcodeInput"
          v-model="searchQuery"
          @keydown.enter.prevent="handleBarcodeOrSearch"
          type="text"
          placeholder="Scanner ou rechercher un produit..."
          class="pos-search-input"
          id="pos-barcode-input"
          autofocus
        />
        <button v-if="searchQuery" @click="searchQuery = ''; $refs.barcodeInput?.focus()" class="pos-search-clear">✕</button>
      </div>

      <!-- Cart items -->
      <div class="pos-cart-items" id="pos-cart-list">
        <div v-if="cart.length === 0" class="pos-cart-empty">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
          <p>Panier vide</p>
          <span>Scannez un code-barres ou sélectionnez un produit</span>
        </div>
        <TransitionGroup name="cart-item" tag="div">
          <div
            v-for="(item, index) in cart"
            :key="item.produit_id + '-' + index"
            class="pos-cart-row"
            :class="{ 'pos-cart-row-flash': item._flash }"
          >
            <div class="pos-cart-row-info">
              <div class="pos-cart-row-name">{{ item.designation }}</div>
              <div class="pos-cart-row-meta">
                {{ formatPrice(item.prix_unitaire) }} × {{ item.quantite }}
              </div>
            </div>
            <div class="pos-cart-row-actions">
              <div class="pos-qty-controls">
                <button @click="decrementQty(index)" class="pos-qty-btn" :disabled="item.quantite <= 1">−</button>
                <input
                  type="number"
                  :value="item.quantite"
                  @change="setQty(index, $event.target.value)"
                  class="pos-qty-input"
                  min="1"
                  step="1"
                />
                <button @click="incrementQty(index)" class="pos-qty-btn">+</button>
              </div>
              <div class="pos-cart-row-total">{{ formatPrice(item.prix_unitaire * item.quantite) }}</div>
              <button @click="removeFromCart(index)" class="pos-remove-btn" title="Supprimer">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
          </div>
        </TransitionGroup>
      </div>

      <!-- Cart totals -->
      <div class="pos-cart-footer">
        <div class="pos-cart-summary">
          <div class="pos-summary-row">
            <span>Articles</span>
            <span>{{ totalItems }}</span>
          </div>
          <div class="pos-summary-row">
            <span>Sous-total HT</span>
            <span>{{ formatPrice(totalHT) }}</span>
          </div>
          <div class="pos-summary-row" v-if="totalTVA > 0">
            <span>TVA</span>
            <span>{{ formatPrice(totalTVA) }}</span>
          </div>
        </div>
        <div class="pos-total-bar">
          <span class="pos-total-label">TOTAL</span>
          <span class="pos-total-amount">{{ formatPrice(totalTTC) }}</span>
        </div>
        <button
          class="pos-btn-checkout-cta"
          :disabled="cart.length === 0"
          @click="openPayment"
          title="Passer à l'encaissement (F12)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          <span>Encaisser &bull; {{ formatPrice(totalTTC) }}</span>
          <span class="pos-shortcut-badge">F12</span>
        </button>
      </div>
    </section>

    <!-- ════ RIGHT PANEL — PRODUCTS + NUMPAD + PAYMENT ════ -->
    <section class="pos-right">
      <!-- Tab switcher -->
      <div class="pos-right-tabs">
        <button
          :class="['pos-tab', { active: rightTab === 'products' }]"
          @click="rightTab = 'products'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
          Produits
        </button>
        <button
          :class="['pos-tab', { active: rightTab === 'payment' }]"
          @click="openPayment"
          :disabled="cart.length === 0"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          Paiement
        </button>
        <button
          :class="['pos-tab', { active: rightTab === 'history' }]"
          @click="openHistory"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Historique
        </button>
        <button
          :class="['pos-tab', { active: rightTab === 'cloture' }]"
          @click="openCloture"
          id="pos-tab-cloture"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
          Clôture Z
        </button>
      </div>

      <!-- ── Products Tab ── -->
      <div v-show="rightTab === 'products'" class="pos-products-panel">
        <!-- Category filter -->
        <div class="pos-categories" v-if="familles.length > 0">
          <button
            :class="['pos-cat-btn', { active: selectedFamille === null }]"
            @click="selectedFamille = null"
          >Tous</button>
          <button
            v-for="f in familles"
            :key="f.id"
            :class="['pos-cat-btn', { active: selectedFamille === f.id }]"
            @click="selectedFamille = f.id"
          >{{ f.libelle }}</button>
        </div>

        <!-- Product grid -->
        <div class="pos-products-grid" id="pos-product-grid">
          <div v-if="loadingProducts" class="pos-products-loading">
            <div class="pos-spinner"></div>
            <span>Chargement…</span>
          </div>
          <button
            v-for="p in filteredProducts"
            :key="p.id"
            class="pos-product-card"
            @click="addToCart(p)"
            :title="p.designation"
          >
            <!-- Card top: Reference + Stock -->
            <div class="pos-product-card-top">
              <div class="pos-product-ref">{{ p.reference }}</div>
              <div v-if="!p.is_service && p.stock_actuel !== null" class="pos-product-stock" :class="{ low: p.stock_actuel <= 5 }">
                {{ Math.floor(p.stock_actuel) }}
              </div>
            </div>

            <!-- Product Image / Visual Thumbnail -->
            <div class="pos-product-img-box">
              <img
                v-if="p.image_path"
                :src="getProductImageUrl(p.image_path)"
                :alt="p.designation"
                class="pos-product-img"
                loading="lazy"
                @error="(e) => e.target.style.display = 'none'"
              />
              <div v-else class="pos-product-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
              </div>
            </div>

            <div class="pos-product-name">{{ p.designation }}</div>
            <div class="pos-product-price">{{ formatPrice(p.prix_ttc_vente) }}</div>
          </button>
          <div v-if="!loadingProducts && filteredProducts.length === 0" class="pos-no-products">
            <span>Aucun produit trouvé</span>
          </div>
        </div>
      </div>

      <!-- ── Payment Tab ── -->
      <div v-show="rightTab === 'payment'" class="pos-payment-panel">
        <!-- Top bar with Back button -->
        <div class="pos-payment-header">
          <button class="pos-back-link" @click="rightTab = 'products'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Retour aux articles</span>
          </button>
          <div class="pos-payment-badge-items">
            {{ totalItems }} article{{ totalItems > 1 ? 's' : '' }}
          </div>
        </div>

        <!-- Hero Total to pay -->
        <div class="pos-payment-total-display">
          <div class="pos-payment-total-label">Net à Payer (TTC)</div>
          <div class="pos-payment-total-value">{{ formatPrice(totalTTC) }}</div>
        </div>

        <!-- Payment mode selection (Espèces, Carte, Chèque, Virement) -->
        <div class="pos-payment-modes-grid">
          <button
            :class="['pos-pay-mode-card', 'especes', { active: paymentMode === 'especes' }]"
            @click="selectPaymentMode('especes')"
          >
            <div class="pos-mode-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><path d="M1 8h2"/><path d="M21 8h2"/><path d="M1 16h2"/><path d="M21 16h2"/></svg>
            </div>
            <div class="pos-mode-text">
              <span class="pos-mode-title">Espèces</span>
              <span class="pos-mode-sub">Cash & rendu</span>
            </div>
          </button>

          <button
            :class="['pos-pay-mode-card', 'carte', { active: paymentMode === 'carte' }]"
            @click="selectPaymentMode('carte')"
          >
            <div class="pos-mode-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="pos-mode-text">
              <span class="pos-mode-title">Carte TPE</span>
              <span class="pos-mode-sub">Terminal bancaire</span>
            </div>
          </button>

          <button
            :class="['pos-pay-mode-card', 'cheque', { active: paymentMode === 'cheque' }]"
            @click="selectPaymentMode('cheque')"
          >
            <div class="pos-mode-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div class="pos-mode-text">
              <span class="pos-mode-title">Chèque</span>
              <span class="pos-mode-sub">Règlement chèque</span>
            </div>
          </button>

          <button
            :class="['pos-pay-mode-card', 'virement', { active: paymentMode === 'virement' }]"
            @click="selectPaymentMode('virement')"
          >
            <div class="pos-mode-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </div>
            <div class="pos-mode-text">
              <span class="pos-mode-title">Virement</span>
              <span class="pos-mode-sub">Banque / Mobile</span>
            </div>
          </button>
        </div>

        <!-- Cash Details & Numpad Section -->
        <div v-if="paymentMode === 'especes'" class="pos-cash-container">
          <!-- Montant reçu input field with live feedback -->
          <div class="pos-recu-wrapper">
            <div class="pos-recu-header">
              <label class="pos-recu-label" for="pos-recu-input">Montant reçu du client</label>
              <button
                type="button"
                class="pos-btn-exact-pill"
                @click="setMontantExact"
                title="Définir sur le montant exact à payer"
              >
                ✓ Montant exact ({{ formatPrice(totalTTC) }})
              </button>
            </div>

            <div class="pos-recu-input-box">
              <input
                id="pos-recu-input"
                ref="recuInputRef"
                v-model="montantRecu"
                type="number"
                step="any"
                min="0"
                class="pos-recu-input-field"
                placeholder="Ex: 200"
                @keydown.enter="handleEnterKey"
              />
              <span class="pos-recu-devise">{{ deviseSymbole || 'DH' }}</span>
              <button
                v-if="montantRecu"
                type="button"
                class="pos-recu-clear"
                @click="montantRecu = ''; recuInputRef?.focus()"
                title="Effacer"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Quick cash amounts presets -->
          <div class="pos-quick-cash-row">
            <button
              v-for="amt in quickCashAmounts"
              :key="amt"
              type="button"
              class="pos-quick-pill"
              :class="{ selected: parseFloat(montantRecu) === amt }"
              @click="montantRecu = String(amt)"
            >
              {{ amt }} {{ deviseSymbole }}
            </button>
          </div>

          <!-- Real-time Change or Insufficient alert -->
          <div v-if="monnaie > 0" class="pos-change-banner positive">
            <div class="pos-change-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="pos-change-info">
              <span class="pos-change-label">Monnaie à rendre au client :</span>
              <span class="pos-change-amount">{{ formatPrice(monnaie) }}</span>
            </div>
          </div>

          <div v-else-if="resteAPayer > 0" class="pos-change-banner warning">
            <div class="pos-change-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="pos-change-info">
              <span class="pos-change-label">Montant perçu insuffisant :</span>
              <span class="pos-change-amount">Manque {{ formatPrice(resteAPayer) }}</span>
            </div>
            <button type="button" class="pos-quick-complete-btn" @click="setMontantExact">
              Ajuster à {{ formatPrice(totalTTC) }}
            </button>
          </div>

          <div v-else class="pos-change-banner neutral">
            <div class="pos-change-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <span class="pos-change-label">Compte exact &bull; Aucune monnaie à rendre</span>
          </div>

          <!-- Touch Numpad -->
          <div class="pos-numpad">
            <button v-for="k in ['7','8','9','4','5','6','1','2','3','.','0','⌫']" :key="k" type="button" class="pos-numpad-key" @click="handleNumpad(k)">
              {{ k }}
            </button>
          </div>
        </div>

        <!-- Non-cash notes & references -->
        <div v-else class="pos-non-cash-info">
          <div class="pos-non-cash-card">
            <div class="pos-non-cash-icon">
              <svg v-if="paymentMode === 'carte'" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <svg v-else-if="paymentMode === 'cheque'" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            </div>
            <div class="pos-non-cash-details">
              <h4>{{ paymentMode === 'carte' ? 'Paiement par Terminal Carte (TPE)' : (paymentMode === 'cheque' ? 'Paiement par Chèque' : 'Paiement par Virement') }}</h4>
              <p>Montant à régler : <strong>{{ formatPrice(totalTTC) }}</strong></p>
            </div>
          </div>

          <div class="pos-field-group">
            <label class="pos-field-label">Référence / Note de règlement (optionnel)</label>
            <input
              v-model="observationPaiement"
              type="text"
              class="pos-field-input"
              :placeholder="paymentMode === 'cheque' ? 'N° chèque, banque...' : (paymentMode === 'carte' ? 'N° d\'autorisation TPE...' : 'Réf. virement bancaire...')"
              @keydown.enter="handleEnterKey"
            />
          </div>
        </div>

        <!-- Rectification info box in payment tab -->
        <div v-if="isRectifyingSale" class="pos-rectify-summary-box">
          <div class="pos-rectify-box-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            <span>Rectification de la Vente #{{ rectifyingSaleNumero }}</span>
          </div>
          <div class="pos-rectify-rows">
            <div class="pos-rectify-row">
              <span>Total d'origine</span>
              <strong>{{ formatPrice(originalSaleTotal) }}</strong>
            </div>
            <div class="pos-rectify-row">
              <span>Nouveau Total rectifié</span>
              <strong class="accent-text">{{ formatPrice(totalTTC) }}</strong>
            </div>
            <div class="pos-rectify-row highlight">
              <span>Régularisation</span>
              <strong :class="rectifyDiffClass">{{ rectifyDiffText }}</strong>
            </div>
          </div>
          <div class="pos-field-group" style="margin-top: 10px;">
            <label class="pos-field-label">Motif de la rectification *</label>
            <input
              v-model="motifRectification"
              type="text"
              class="pos-field-input"
              placeholder="Ex: Correction erreur de saisie..."
            />
          </div>
        </div>

        <!-- Validate button (Always clear, active and friendly) -->
        <div class="pos-checkout-action-box">
          <button
            class="pos-validate-btn"
            :class="{ 'rectify-btn': isRectifyingSale }"
            @click="handleCheckout"
            :disabled="!canCheckout || isProcessing || (isRectifyingSale && !motifRectification)"
            id="pos-validate-btn"
          >
            <div v-if="isProcessing" class="pos-spinner-sm"></div>
            <svg v-else-if="isRectifyingSale" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <div class="pos-validate-btn-content">
              <span>{{ isProcessing ? 'Validation en cours...' : (isRectifyingSale ? 'Valider la rectification' : 'Valider le paiement') }}</span>
              <span v-if="!isProcessing" class="pos-validate-sub">{{ formatPrice(totalTTC) }} &bull; Touche Entrée ↵</span>
            </div>
          </button>
        </div>
      </div>

      <!-- ── History Tab ── -->
      <div v-show="rightTab === 'history'" class="pos-history-panel">
        <!-- Top bar: Title + Refresh + Search -->
        <div class="pos-history-header">
          <div class="pos-history-title-box">
            <h3>Historique des ventes</h3>
            <span class="pos-history-count">{{ filteredSalesHistory.length }} vente{{ filteredSalesHistory.length > 1 ? 's' : '' }}</span>
          </div>

          <div class="pos-history-actions">
            <div class="pos-history-search">
              <input
                v-model="historySearch"
                type="text"
                placeholder="Rechercher ticket ou montant..."
                class="pos-history-search-input"
              />
              <button v-if="historySearch" @click="historySearch = ''" class="pos-history-search-clear">✕</button>
            </div>

            <button class="pos-history-refresh-btn" @click="loadSalesHistory" :disabled="loadingHistory" title="Actualiser">
              <svg :class="{ 'pos-spinning': loadingHistory }" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.19"/></svg>
            </button>
          </div>
        </div>

        <!-- Period & Status Filter Bar -->
        <div class="pos-history-filter-strip">
          <div class="pos-period-pills">
            <button
              v-for="p in historyPeriods"
              :key="p.key"
              type="button"
              class="pos-period-pill"
              :class="{ active: historyPeriod === p.key }"
              @click="setHistoryPeriod(p.key)"
            >
              {{ p.label }}
            </button>
          </div>

          <!-- Status Filter Pills (Toutes / Validées / Annulées) -->
          <div class="pos-status-pills">
            <button
              type="button"
              class="pos-status-pill"
              :class="{ active: historyStatusFilter === 'all' }"
              @click="historyStatusFilter = 'all'"
            >
              Toutes
            </button>
            <button
              type="button"
              class="pos-status-pill success"
              :class="{ active: historyStatusFilter === 'valid' }"
              @click="historyStatusFilter = 'valid'"
            >
              Validées
            </button>
            <button
              type="button"
              class="pos-status-pill danger"
              :class="{ active: historyStatusFilter === 'cancelled' }"
              @click="historyStatusFilter = 'cancelled'"
            >
              Annulées ({{ historyCancelledCount }})
            </button>
          </div>

          <!-- Date picker personnalisé -->
          <div v-if="historyPeriod === 'custom'" class="pos-history-custom-dates">
            <div class="pos-date-field">
              <span class="pos-date-lbl">Du</span>
              <input
                type="date"
                v-model="historyCustomStart"
                class="pos-history-date-input"
                @change="loadSalesHistory"
              />
            </div>
            <span class="pos-date-sep">au</span>
            <div class="pos-date-field">
              <span class="pos-date-lbl">Au</span>
              <input
                type="date"
                v-model="historyCustomEnd"
                class="pos-history-date-input"
                @change="loadSalesHistory"
              />
            </div>
          </div>
        </div>

        <!-- Quick Summary Bar (Total Encaissé, Valides, Annulées) -->
        <div class="pos-history-summary-bar">
          <div class="pos-history-summary-card">
            <span class="pos-hist-lbl">Total Encaissé (Nets non annulés)</span>
            <span class="pos-hist-val accent">{{ formatPrice(historyPeriodTotal) }}</span>
          </div>
          <div class="pos-history-summary-card">
            <span class="pos-hist-lbl">Ventes Valides</span>
            <span class="pos-hist-val">{{ historyValidCount }}</span>
          </div>
          <div v-if="historyCancelledCount > 0" class="pos-history-summary-card danger-card">
            <span class="pos-hist-lbl">Ventes Annulées</span>
            <span class="pos-hist-val danger-text">{{ historyCancelledCount }} ({{ formatPrice(historyCancelledTotal) }})</span>
          </div>
        </div>

        <!-- Sales list -->
        <div class="pos-history-list">
          <div v-if="loadingHistory" class="pos-history-loading">
            <div class="pos-spinner"></div>
            <span>Chargement des ventes…</span>
          </div>

          <div v-else-if="filteredSalesHistory.length === 0" class="pos-history-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p>Aucune vente comptoir trouvée</p>
          </div>

          <div
            v-else
            v-for="s in filteredSalesHistory"
            :key="s.id"
            class="pos-history-item"
            :class="{ 'is-cancelled-item': s.est_annulee }"
          >
            <div class="pos-hist-main">
              <div class="pos-hist-top-line">
                <span class="pos-hist-numero" :class="{ 'strikethrough-text': s.est_annulee }">{{ s.numero }}</span>
                <span v-if="s.est_annulee" class="pos-hist-badge cancelled-badge">ANNULÉE</span>
                <span v-else class="pos-hist-badge" :class="s.mode_paiement?.toLowerCase()">{{ s.mode_paiement }}</span>
              </div>
              <div class="pos-hist-meta-line">
                <span>{{ s.datetime || s.date }}</span>
                <span class="pos-hist-dot">•</span>
                <span>{{ s.nb_articles }} article{{ s.nb_articles > 1 ? 's' : '' }}</span>
                <span v-if="s.caissier" class="pos-hist-dot">•</span>
                <span v-if="s.caissier">👤 {{ s.caissier }}</span>
              </div>
            </div>

            <div class="pos-hist-right">
              <div class="pos-hist-total" :class="{ 'strikethrough-text': s.est_annulee }">{{ formatPrice(s.total_ttc) }}</div>
              
              <div class="pos-hist-actions-row">
                <button
                  class="pos-hist-action-btn secondary"
                  @click="openSaleDetailModal(s)"
                  title="Voir les détails de cette vente"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                  <span>Détail</span>
                </button>

                <button
                  class="pos-hist-action-btn print"
                  @click="printHistorySale(s.id)"
                  title="Imprimer le ticket"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                  <span>Ticket</span>
                </button>

                <button
                  v-if="!s.est_annulee"
                  class="pos-hist-action-btn warning"
                  @click="startRectifySale(s)"
                  title="Rectifier / Modifier les articles ou paiements"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Rectifier</span>
                </button>

                <button
                  v-if="!s.est_annulee"
                  class="pos-hist-action-btn danger"
                  @click="openCancelModal(s)"
                  title="Annuler cette vente"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                  <span>Annuler</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Clôture Z Tab ── -->
      <div v-show="rightTab === 'cloture'" class="pos-cloture-panel">
        <!-- Top bar: Title + subtabs (Nouvelle Clôture / Historique des Z) -->
        <div class="pos-cloture-header">
          <div class="pos-cloture-title-box">
            <div class="pos-cloture-icon-badge">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <div>
              <h3>Clôture de Caisse (Rapport Z)</h3>
              <p class="pos-cloture-sub">Contrôle du tiroir et récapitulatif comptable de fin de journée</p>
            </div>
          </div>

          <div class="pos-cloture-nav-pills">
            <button
              :class="['pos-cloture-nav-pill', { active: clotureSubTab === 'new' }]"
              @click="clotureSubTab = 'new'; loadCurrentClotureSession()"
            >
              Nouvelle Clôture
            </button>
            <button
              :class="['pos-cloture-nav-pill', { active: clotureSubTab === 'history' }]"
              @click="clotureSubTab = 'history'; loadClotureHistory()"
            >
              Historique des Z
            </button>
          </div>
        </div>

        <!-- ── Subtab 1: New Clôture ── -->
        <div v-show="clotureSubTab === 'new'" class="pos-cloture-body">
          <div v-if="loadingClotureSession" class="pos-history-loading">
            <div class="pos-spinner"></div>
            <span>Calcul de la session en cours…</span>
          </div>

          <div v-else class="pos-cloture-content">
            <!-- Session Meta Strip -->
            <div class="pos-cloture-meta-banner">
              <div class="pos-cloture-meta-item">
                <span class="pos-meta-lbl">Date</span>
                <span class="pos-meta-val">{{ clotureSession.date }}</span>
              </div>
              <div class="pos-cloture-meta-item">
                <span class="pos-meta-lbl">Caissier</span>
                <span class="pos-meta-val">{{ clotureSession.caissier }}</span>
              </div>
              <div class="pos-cloture-meta-item">
                <span class="pos-meta-lbl">Ouverture</span>
                <span class="pos-meta-val">{{ clotureSession.opened_at }}</span>
              </div>
              <div class="pos-cloture-meta-item">
                <span class="pos-meta-lbl">Fermeture</span>
                <span class="pos-meta-val">{{ clotureSession.closed_at }}</span>
              </div>
            </div>

            <!-- 1. Récapitulatif des Ventes -->
            <div class="pos-cloture-section-card">
              <div class="pos-sec-header">
                <span class="pos-sec-num">1</span>
                <h4>RÉCAPITULATIF DES VENTES</h4>
              </div>
              <div class="pos-cloture-kpi-row">
                <div class="pos-cloture-kpi-main">
                  <span class="pos-kpi-lbl">Total Ventes</span>
                  <span class="pos-kpi-val">{{ formatPrice(clotureSession.total_ventes) }}</span>
                </div>
                <div class="pos-cloture-kpi-sub">
                  <span class="pos-kpi-lbl">Nombre de transactions</span>
                  <span class="pos-kpi-val-sm">{{ clotureSession.nb_ventes }} ticket{{ clotureSession.nb_ventes > 1 ? 's' : '' }}</span>
                </div>
              </div>
            </div>

            <!-- 2. Détail par Paiement -->
            <div class="pos-cloture-section-card">
              <div class="pos-sec-header">
                <span class="pos-sec-num">2</span>
                <h4>DÉTAIL PAR PAIEMENT</h4>
              </div>
              <div class="pos-pay-modes-list">
                <div class="pos-pay-mode-row">
                  <div class="pos-pay-mode-label">
                    <span class="pos-dot-mode especes"></span>
                    <span>Espèces</span>
                  </div>
                  <span class="pos-pay-mode-amount">{{ formatPrice(clotureSession.total_especes) }}</span>
                </div>
                <div class="pos-pay-mode-row">
                  <div class="pos-pay-mode-label">
                    <span class="pos-dot-mode carte"></span>
                    <span>Carte Bancaire (TPE)</span>
                  </div>
                  <span class="pos-pay-mode-amount">{{ formatPrice(clotureSession.total_carte) }}</span>
                </div>
                <div v-if="clotureSession.total_cheque > 0" class="pos-pay-mode-row">
                  <div class="pos-pay-mode-label">
                    <span class="pos-dot-mode cheque"></span>
                    <span>Chèque</span>
                  </div>
                  <span class="pos-pay-mode-amount">{{ formatPrice(clotureSession.total_cheque) }}</span>
                </div>
                <div v-if="clotureSession.total_virement > 0" class="pos-pay-mode-row">
                  <div class="pos-pay-mode-label">
                    <span class="pos-dot-mode virement"></span>
                    <span>Virement</span>
                  </div>
                  <span class="pos-pay-mode-amount">{{ formatPrice(clotureSession.total_virement) }}</span>
                </div>
              </div>
            </div>

            <!-- 3. Contrôle Tiroir-Caisse -->
            <div class="pos-cloture-section-card highlight">
              <div class="pos-sec-header">
                <span class="pos-sec-num">3</span>
                <h4>CONTRÔLE TIROIR-CAISSE</h4>
              </div>

              <div class="pos-tiroir-grid">
                <!-- Fond initial -->
                <div class="pos-tiroir-field">
                  <label>Fond initial (DH)</label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    v-model.number="clotureFondInitial"
                    @input="recalculateCloture"
                    class="pos-cloture-input"
                    placeholder="0.00"
                  />
                  <span class="pos-input-hint">Fond de caisse au début du service</span>
                </div>

                <!-- Espèces ajoutées -->
                <div class="pos-tiroir-field readonly">
                  <label>Espèces ajoutées (Ventes)</label>
                  <div class="pos-readonly-box">{{ formatPrice(clotureSession.total_especes) }}</div>
                  <span class="pos-input-hint">Total encaissé en espèces</span>
                </div>

                <!-- Total Attendu -->
                <div class="pos-tiroir-field readonly">
                  <label>Total Attendu dans le tiroir</label>
                  <div class="pos-readonly-box font-bold accent">{{ formatPrice(clotureTotalAttendu) }}</div>
                  <span class="pos-input-hint">Fond initial + Espèces</span>
                </div>

                <!-- Total Déclaré -->
                <div class="pos-tiroir-field focus-field">
                  <label>Total Déclaré (Compté réel) *</label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    v-model.number="clotureTotalDeclare"
                    @input="recalculateCloture"
                    class="pos-cloture-input declare-input"
                    placeholder="Saisir montant compté..."
                    id="pos-cloture-declare-input"
                  />
                  <span class="pos-input-hint">Montant physique actuellement dans le tiroir</span>
                </div>
              </div>

              <!-- Écart Display Banner -->
              <div class="pos-ecart-banner" :class="clotureEcartStatus">
                <div class="pos-ecart-left">
                  <span class="pos-ecart-lbl">ÉCART DE CAISSE</span>
                  <span class="pos-ecart-val">
                    {{ (clotureEcart > 0 ? '+' : '') + formatPrice(clotureEcart) }}
                  </span>
                </div>
                <div class="pos-ecart-badge">
                  <span v-if="clotureEcartStatus === 'conforme'">✓ Conforme (Aucun écart)</span>
                  <span v-else-if="clotureEcartStatus === 'manquant'">⚠ Manquant (Déficit de caisse)</span>
                  <span v-else>+ Excédent (Surplus de caisse)</span>
                </div>
              </div>

              <!-- Observations -->
              <div class="pos-cloture-obs">
                <label>Observations / Justificatif écart (optionnel)</label>
                <textarea
                  v-model="clotureObservations"
                  rows="2"
                  class="pos-cloture-textarea"
                  placeholder="Notes de passation, explication d'un écart..."
                ></textarea>
              </div>

              <!-- Action button -->
              <button
                class="pos-btn-submit-cloture"
                @click="submitCloture"
                :disabled="isSubmittingCloture || clotureTotalDeclare === null || clotureTotalDeclare === ''"
              >
                <div v-if="isSubmittingCloture" class="pos-spinner-sm"></div>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Valider la Clôture &amp; Imprimer le Rapport Z</span>
              </button>
            </div>
          </div>
        </div>

        <!-- ── Subtab 2: History of Z Reports ── -->
        <div v-show="clotureSubTab === 'history'" class="pos-cloture-body">
          <div v-if="loadingClotureHistory" class="pos-history-loading">
            <div class="pos-spinner"></div>
            <span>Chargement des rapports Z…</span>
          </div>

          <div v-else-if="clotureHistoryList.length === 0" class="pos-history-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p>Aucun rapport Z archivé</p>
          </div>

          <div v-else class="pos-z-history-list">
            <div
              v-for="z in clotureHistoryList"
              :key="z.id"
              class="pos-z-card"
            >
              <div class="pos-z-card-main">
                <div class="pos-z-top">
                  <span class="pos-z-num">{{ z.numero }}</span>
                  <span class="pos-z-badge" :class="z.statut_ecart">
                    {{ z.statut_ecart === 'conforme' ? 'Conforme' : (z.statut_ecart === 'manquant' ? 'Manquant ' + z.ecart + ' DH' : 'Excédent +' + z.ecart + ' DH') }}
                  </span>
                </div>
                <div class="pos-z-meta">
                  <span>📅 {{ z.date }} ({{ z.ouverture }} → {{ z.fermeture }})</span>
                  <span class="pos-hist-dot">•</span>
                  <span>👤 {{ z.caissier }}</span>
                  <span class="pos-hist-dot">•</span>
                  <span>{{ z.nb_ventes }} vente{{ z.nb_ventes > 1 ? 's' : '' }}</span>
                </div>
                <div class="pos-z-amounts">
                  <span>Ventes : <strong>{{ formatPrice(z.total_ventes) }}</strong></span>
                  <span class="pos-hist-dot">•</span>
                  <span>Attendu : <strong>{{ formatPrice(z.total_attendu) }}</strong></span>
                  <span class="pos-hist-dot">•</span>
                  <span>Déclaré : <strong>{{ formatPrice(z.total_declare) }}</strong></span>
                </div>
              </div>

              <div class="pos-z-actions">
                <button class="pos-btn-view-z" @click="openZReportPreview(z)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                  <span>Voir Ticket Z</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ SUCCESS MODAL ═══ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showSuccess" class="pos-modal-overlay" @click.self="resetAfterSale">
          <div class="pos-success-modal">
            <div class="pos-success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h2 class="pos-success-title">Vente enregistrée !</h2>
            <div class="pos-success-details">
              <div class="pos-success-row">
                <span>Facture N°</span>
                <strong>{{ lastSale?.numero }}</strong>
              </div>
              <div class="pos-success-row">
                <span>Total</span>
                <strong>{{ formatPrice(lastSale?.total_ttc) }}</strong>
              </div>
              <div v-if="lastSale?.monnaie > 0" class="pos-success-row change">
                <span>Monnaie</span>
                <strong>{{ formatPrice(lastSale.monnaie) }}</strong>
              </div>
            </div>
            <div class="pos-success-actions">
              <button class="pos-btn-print" @click="printTicket">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimer le ticket
              </button>
              <button class="pos-btn-new" @click="resetAfterSale" id="pos-new-sale">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvelle vente
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═ ═ ═  Z-REPORT RECEIPT MODAL ═ ═ ═  -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showZReportModal" class="pos-modal-overlay" @click.self="showZReportModal = false">
          <div class="pos-z-modal">
            <div class="pos-z-modal-header">
              <div class="pos-z-modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                <span>Rapport de Clôture Z</span>
              </div>
              <button class="pos-modal-close" @click="showZReportModal = false">✕</button>
            </div>

            <!-- Thermal receipt paper simulator -->
            <div class="pos-receipt-paper-wrapper">
              <div class="pos-receipt-paper">
                <pre class="pos-receipt-content">{{ currentZReportText }}</pre>
              </div>
            </div>

            <div class="pos-z-modal-footer">
              <button class="pos-z-modal-btn secondary" @click="copyZReportText">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span>Copier le texte</span>
              </button>
              <button class="pos-z-modal-btn primary" @click="printCurrentZReport">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Imprimer Ticket Z</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
    <!-- ═ ═ ═  SALE DETAIL MODAL ═ ═ ═  -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDetailModal" class="pos-modal-overlay" @click.self="showDetailModal = false">
          <div class="pos-detail-modal">
            <!-- Header -->
            <div class="pos-detail-header">
              <div class="pos-detail-title">
                <div class="pos-detail-title-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="pos-detail-header-text">
                  <div class="pos-detail-main-heading">Détail de la Vente</div>
                  <div class="pos-detail-sub-heading">#{{ selectedSaleDetail?.numero || '...' }}</div>
                </div>
                <span v-if="selectedSaleDetail?.est_annulee" class="pos-detail-status-pill danger">
                  <span class="status-dot"></span>ANNULÉE
                </span>
                <span v-else class="pos-detail-status-pill success">
                  <span class="status-dot"></span>PAYÉE
                </span>
              </div>
              <button class="pos-modal-close" @click="showDetailModal = false" title="Fermer">✕</button>
            </div>

            <!-- Body -->
            <div class="pos-detail-body">
              <div v-if="loadingSaleDetail" class="pos-history-loading">
                <div class="pos-spinner"></div>
                <span>Chargement des détails...</span>
              </div>
              <div v-else-if="selectedSaleDetail" class="pos-detail-content">
                <!-- Meta Info Cards Grid -->
                <div class="pos-detail-meta-grid">
                  <div class="pos-detail-meta-item">
                    <div class="pos-detail-meta-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="pos-detail-meta-info">
                      <span class="pos-detail-meta-label">Date & Heure</span>
                      <span class="pos-detail-meta-value">{{ formatSaleDateTime(selectedSaleDetail) }}</span>
                    </div>
                  </div>

                  <div class="pos-detail-meta-item">
                    <div class="pos-detail-meta-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="pos-detail-meta-info">
                      <span class="pos-detail-meta-label">Caissier</span>
                      <span class="pos-detail-meta-value">{{ selectedSaleDetail.caissier || 'Caissier' }}</span>
                    </div>
                  </div>

                  <div class="pos-detail-meta-item">
                    <div class="pos-detail-meta-icon accent">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                    <div class="pos-detail-meta-info">
                      <span class="pos-detail-meta-label">Mode de Paiement</span>
                      <span class="pos-detail-meta-value accent">{{ selectedSaleDetail.mode_paiement }}</span>
                    </div>
                  </div>

                  <div class="pos-detail-meta-item">
                    <div class="pos-detail-meta-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-3-3.87"/><path d="M9 21v-2a4 4 0 0 1 3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div class="pos-detail-meta-info">
                      <span class="pos-detail-meta-label">Client</span>
                      <span class="pos-detail-meta-value">{{ selectedSaleDetail.client?.societe || selectedSaleDetail.client?.nom || 'Client Comptoir' }}</span>
                    </div>
                  </div>
                </div>

                <!-- Articles Section -->
                <div class="pos-detail-section">
                  <div class="pos-detail-section-head">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span>Articles Vendus ({{ selectedSaleDetail.lignes?.length || 0 }})</span>
                  </div>
                  <div class="pos-detail-table-wrap">
                    <table class="pos-detail-table">
                      <thead>
                        <tr>
                          <th class="col-product">Désignation</th>
                          <th class="col-price">P.U TTC</th>
                          <th class="col-qty">Qté</th>
                          <th class="col-tva">TVA</th>
                          <th class="col-total">Total TTC</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(l, idx) in selectedSaleDetail.lignes" :key="l.id || idx" :class="{ 'row-alt': idx % 2 === 1 }">
                          <td class="col-product">
                            <div class="pos-detail-prod-name">{{ l.designation }}</div>
                            <div v-if="l.reference" class="pos-detail-prod-ref">Réf: {{ l.reference }}</div>
                          </td>
                          <td class="col-price">{{ formatPrice(l.prix_unitaire) }}</td>
                          <td class="col-qty">
                            <span class="pos-detail-qty-badge">{{ l.quantite }}</span>
                          </td>
                          <td class="col-tva">{{ l.taux_tva }}%</td>
                          <td class="col-total">{{ formatPrice(l.total_ttc) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Totals Grid -->
                <div class="pos-detail-totals">
                  <div class="pos-detail-totals-grid">
                    <div class="pos-detail-total-line">
                      <span class="pos-detail-total-label">Total HT</span>
                      <span class="pos-detail-total-amount">{{ formatPrice(selectedSaleDetail.total_ht) }}</span>
                    </div>
                    <div class="pos-detail-total-line">
                      <span class="pos-detail-total-label">Total TVA</span>
                      <span class="pos-detail-total-amount">{{ formatPrice(selectedSaleDetail.total_tva) }}</span>
                    </div>
                    <div class="pos-detail-total-line grand">
                      <span class="pos-detail-total-label">TOTAL TTC</span>
                      <span class="pos-detail-total-amount">{{ formatPrice(selectedSaleDetail.total_ttc) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Observations -->
                <div v-if="selectedSaleDetail.observations" class="pos-detail-observations">
                  <div class="pos-detail-obs-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Historique / Observations</span>
                  </div>
                  <div class="pos-detail-obs-content">{{ selectedSaleDetail.observations }}</div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="pos-detail-footer">
              <button class="pos-detail-action close" @click="showDetailModal = false">
                Fermer
              </button>

              <div class="pos-detail-footer-actions">
                <button v-if="selectedSaleDetail" class="pos-detail-action print" @click="printHistorySale(selectedSaleDetail.id)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                  <span>Imprimer Ticket</span>
                </button>
                <button
                  v-if="selectedSaleDetail && !selectedSaleDetail.est_annulee"
                  class="pos-detail-action rectify"
                  @click="startRectifySale(selectedSaleDetail)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Rectifier la vente</span>
                </button>
                <button
                  v-if="selectedSaleDetail && !selectedSaleDetail.est_annulee"
                  class="pos-detail-action cancel"
                  @click="openCancelModal(selectedSaleDetail)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                  <span>Annuler la vente</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═ ═ ═  CANCELLATION MODAL ═ ═ ═  -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showCancelModal" class="pos-modal-overlay" @click.self="showCancelModal = false">
          <div class="pos-cancel-modal">
            <div class="pos-cancel-header">
              <div class="pos-cancel-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Annuler la Vente #{{ saleToCancel?.numero }}</span>
              </div>
              <button class="pos-modal-close" @click="showCancelModal = false">✕</button>
            </div>

            <div class="pos-cancel-body">
              <div class="pos-cancel-alert">
                <strong>Attention :</strong> L'annulation de cette vente va restaurer immédiatement les quantités en stock et annuler le paiement enregistré.
              </div>

              <div class="pos-cancel-summary">
                <span>Total de la vente : <strong>{{ formatPrice(saleToCancel?.total_ttc) }}</strong></span>
                <span>Mode de Paiement : <strong>{{ saleToCancel?.mode_paiement }}</strong></span>
              </div>

              <!-- Quick Reasons chips -->
              <div class="pos-cancel-field">
                <label>Motif de l'annulation *</label>
                <div class="pos-reason-chips">
                  <button
                    v-for="reason in ['Erreur de saisie / Caisse', 'Retour produit client', 'Produit défectueux / Abîmé', 'Erreur de paiement', 'Autre']"
                    :key="reason"
                    type="button"
                    class="pos-reason-chip"
                    :class="{ active: motifCancel === reason }"
                    @click="motifCancel = reason"
                  >
                    {{ reason }}
                  </button>
                </div>
                <textarea
                  v-model="motifCancel"
                  rows="3"
                  class="pos-cancel-textarea"
                  placeholder="Expliquez la raison de l'annulation..."
                ></textarea>
              </div>
            </div>

            <div class="pos-cancel-footer">
              <button class="pos-cancel-btn secondary" @click="showCancelModal = false" :disabled="isCancelling">
                Conserver la vente
              </button>
              <button class="pos-cancel-btn danger" @click="confirmCancelSale" :disabled="isCancelling || !motifCancel">
                <div v-if="isCancelling" class="pos-spinner-sm"></div>
                <span>{{ isCancelling ? 'Annulation...' : 'Confirmer l\'annulation' }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirmation Modal on Exit if Cart is Not Empty -->
    <ConfirmModal
      :show="showExitConfirm"
      title="Quitter le Point de Vente ?"
      message="Votre panier contient des articles non validés. Si vous quittez maintenant, la vente en cours sera perdue. Voulez-vous vraiment quitter le Point de Vente ?"
      confirmText="Oui, quitter"
      confirmClass="danger"
      @confirm="confirmExit"
      @cancel="showExitConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const router = useRouter()
const auth = useAuthStore()

// ─── State ───
const searchQuery = ref('')
const barcodeInput = ref(null)
const rightTab = ref('products')
const cart = ref([])
const products = ref([])
const familles = ref([])
const selectedFamille = ref(null)
const loadingProducts = ref(false)
const paymentMode = ref('especes')
const montantRecu = ref('')
const recuInputRef = ref(null)
const observationPaiement = ref('')
const isProcessing = ref(false)
const showSuccess = ref(false)
const lastSale = ref(null)
const deviseSymbole = ref('')
const showExitConfirm = ref(false)
let isConfirmedExit = false

// Historique des ventes comptoir
const salesHistory = ref([])
const loadingHistory = ref(false)
const historySearch = ref('')
const historyPeriod = ref('today')
const historyCustomStart = ref('')
const historyCustomEnd = ref('')
const historyStatusFilter = ref('all')

// Sale Detail Modal
const showDetailModal = ref(false)
const selectedSaleDetail = ref(null)
const loadingSaleDetail = ref(false)

// Sale Cancel Modal
const showCancelModal = ref(false)
const saleToCancel = ref(null)
const motifCancel = ref('')
const isCancelling = ref(false)

// Sale Rectification Mode
const isRectifyingSale = ref(false)
const rectifyingSaleId = ref(null)
const rectifyingSaleNumero = ref('')
const originalSaleTotal = ref(0)
const motifRectification = ref('')

const historyPeriods = [
  { key: 'today', label: "Aujourd'hui" },
  { key: 'week', label: 'Semaine (7j)' },
  { key: 'month', label: 'Mois (30j)' },
  { key: '3months', label: '3 Mois' },
  { key: 'all', label: 'Tout' },
  { key: 'custom', label: 'Personnalisé' },
]

const currentPeriodLabel = computed(() => {
  switch (historyPeriod.value) {
    case 'today': return "Aujourd'hui"
    case 'week': return '7 derniers jours'
    case 'month': return '30 derniers jours'
    case '3months': return '3 derniers mois'
    case 'all': return 'Tout'
    case 'custom': return 'Période'
    default: return "Aujourd'hui"
  }
})

// ─── Clôture de Caisse (Rapport Z) ───
const clotureSubTab = ref('new')
const loadingClotureSession = ref(false)
const loadingClotureHistory = ref(false)
const isSubmittingCloture = ref(false)
const clotureSession = ref({
  date: '',
  caissier: '',
  opened_at: '08:00',
  closed_at: '',
  fond_initial: 0,
  nb_ventes: 0,
  total_ventes: 0,
  total_especes: 0,
  total_carte: 0,
  total_cheque: 0,
  total_virement: 0,
  total_attendu: 0,
  detail_paiements: [],
})
const clotureFondInitial = ref(0)
const clotureTotalDeclare = ref('')
const clotureObservations = ref('')
const clotureHistoryList = ref([])
const showZReportModal = ref(false)
const currentZReportText = ref('')

const clotureTotalAttendu = computed(() => {
  const fond = parseFloat(clotureFondInitial.value) || 0
  const esp = parseFloat(clotureSession.value.total_especes) || 0
  return Math.round((fond + esp) * 100) / 100
})

const clotureEcart = computed(() => {
  const declare = parseFloat(clotureTotalDeclare.value)
  if (isNaN(declare)) return 0
  return Math.round((declare - clotureTotalAttendu.value) * 100) / 100
})

const clotureEcartStatus = computed(() => {
  const declare = parseFloat(clotureTotalDeclare.value)
  if (isNaN(declare)) return 'conforme'
  const diff = clotureEcart.value
  if (Math.abs(diff) < 0.009) return 'conforme'
  return diff < 0 ? 'manquant' : 'excedent'
})

// ─── Computed ───
const totalItems = computed(() => cart.value.reduce((s, i) => s + i.quantite, 0))

const totalHT = computed(() => {
  return cart.value.reduce((s, i) => {
    const prixHT = i.prix_unitaire / (1 + (i.taux_tva / 100))
    return s + prixHT * i.quantite
  }, 0)
})

const totalTVA = computed(() => totalTTC.value - totalHT.value)

const totalTTC = computed(() => {
  return cart.value.reduce((s, i) => s + i.prix_unitaire * i.quantite, 0)
})

const monnaie = computed(() => {
  if (paymentMode.value !== 'especes') return 0
  const recu = parseFloat(montantRecu.value)
  if (isNaN(recu)) return 0
  return Math.max(0, Math.round((recu - totalTTC.value) * 100) / 100)
})

const resteAPayer = computed(() => {
  if (paymentMode.value !== 'especes') return 0
  const str = String(montantRecu.value ?? '').trim()
  if (!str) return 0
  const recu = parseFloat(str)
  if (isNaN(recu)) return totalTTC.value
  return Math.max(0, Math.round((totalTTC.value - recu) * 100) / 100)
})

const canCheckout = computed(() => {
  if (cart.value.length === 0) return false
  if (paymentMode.value === 'especes') {
    // Si vide ou non renseigné, le montant exact est pris en compte par défaut
    const str = String(montantRecu.value ?? '').trim()
    if (!str) return true
    const recu = parseFloat(str)
    return !isNaN(recu) && recu >= (totalTTC.value - 0.009)
  }
  return true // carte, cheque, virement = toujours valide
})

const quickCashAmounts = computed(() => {
  const total = totalTTC.value
  if (total <= 0) return [20, 50, 100, 200]
  const amounts = new Set()
  // Billets et paliers usuels
  const roundups = [10, 20, 50, 100, 200, 500, 1000]
  for (const r of roundups) {
    const rounded = Math.ceil(total / r) * r
    if (rounded >= total && amounts.size < 5) {
      amounts.add(rounded)
    }
  }
  if (!amounts.has(Math.ceil(total))) amounts.add(Math.ceil(total))
  return [...amounts].sort((a, b) => a - b).slice(0, 5)
})

const filteredSalesHistory = computed(() => {
  let list = salesHistory.value

  if (historyStatusFilter.value === 'valid') {
    list = list.filter(s => !s.est_annulee)
  } else if (historyStatusFilter.value === 'cancelled') {
    list = list.filter(s => s.est_annulee)
  }

  if (historySearch.value) {
    const q = historySearch.value.toLowerCase().trim()
    list = list.filter(s =>
      s.numero?.toLowerCase().includes(q) ||
      s.mode_paiement?.toLowerCase().includes(q) ||
      s.caissier?.toLowerCase().includes(q) ||
      String(s.total_ttc).includes(q)
    )
  }
  return list
})

const historyPeriodTotal = computed(() => {
  return salesHistory.value
    .filter(s => !s.est_annulee)
    .reduce((sum, s) => sum + (parseFloat(s.total_ttc) || 0), 0)
})

const historyValidCount = computed(() => {
  return salesHistory.value.filter(s => !s.est_annulee).length
})

const historyCancelledCount = computed(() => {
  return salesHistory.value.filter(s => s.est_annulee).length
})

const historyCancelledTotal = computed(() => {
  return salesHistory.value
    .filter(s => s.est_annulee)
    .reduce((sum, s) => sum + (parseFloat(s.total_ttc) || 0), 0)
})

const historyTodayTotal = historyPeriodTotal

const rectifyDiff = computed(() => {
  if (!isRectifyingSale.value) return 0
  return Math.round((totalTTC.value - originalSaleTotal.value) * 100) / 100
})

const rectifyDiffClass = computed(() => {
  const diff = rectifyDiff.value
  if (Math.abs(diff) < 0.01) return 'neutral'
  return diff > 0 ? 'positive' : 'negative'
})

const rectifyDiffText = computed(() => {
  const diff = rectifyDiff.value
  if (Math.abs(diff) < 0.01) return 'Montant identique (0,00 ' + (deviseSymbole.value || 'DH') + ')'
  if (diff > 0) return 'Supplément à encaisser: +' + formatPrice(diff)
  return 'Remboursement client: ' + formatPrice(Math.abs(diff))
})

const filteredProducts = computed(() => {
  let list = products.value
  if (selectedFamille.value) {
    list = list.filter(p => p.famille_id === selectedFamille.value)
  }
  if (searchQuery.value && searchQuery.value.length >= 2) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(p =>
      p.designation.toLowerCase().includes(q) ||
      p.reference?.toLowerCase().includes(q) ||
      p.code_barre?.toLowerCase().includes(q)
    )
  }
  return list
})

// ─── Methods ───
async function loadConfig() {
  try {
    const { data } = await api.get('/pos/config')
    deviseSymbole.value = data.devise_symbole || data.devise_code || ''
  } catch {
    deviseSymbole.value = ''
  }
}

function formatPrice(n) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'decimal',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(n || 0) + (deviseSymbole.value ? ' ' + deviseSymbole.value : '')
}

function formatSaleDateTime(sale) {
  if (!sale) return ''
  let dateStr = sale.datetime || sale.date_facture || sale.created_at || ''
  if (!dateStr) return ''

  if (dateStr.includes('T')) {
    const d = new Date(dateStr)
    if (!isNaN(d)) {
      const day = String(d.getDate()).padStart(2, '0')
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const year = d.getFullYear()
      const hours = String(d.getHours()).padStart(2, '0')
      const mins = String(d.getMinutes()).padStart(2, '0')
      return `${day}/${month}/${year} à ${hours}:${mins}`
    }
  }

  const timeStr = sale.heure ? String(sale.heure).trim() : ''
  if (timeStr && !dateStr.includes(timeStr)) {
    return `${dateStr} à ${timeStr}`
  }
  return dateStr
}

function getProductImageUrl(path) {
  if (!path) return null
  if (path.startsWith('blob:') || path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  return path.startsWith('/') ? path : '/' + path
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const { data } = await api.get('/pos/products')
    products.value = data
    // Extract unique familles
    const familleMap = new Map()
    data.forEach(p => {
      if (p.famille && !familleMap.has(p.famille.id)) {
        familleMap.set(p.famille.id, p.famille)
      }
    })
    familles.value = [...familleMap.values()].sort((a, b) => a.libelle.localeCompare(b.libelle))
  } catch (e) {
    toast.error('Impossible de charger les produits.')
  } finally {
    loadingProducts.value = false
  }
}

async function handleBarcodeOrSearch(e) {
  if (e && e.preventDefault) e.preventDefault()
  const query = searchQuery.value.trim()
  if (!query) return

  const queryLower = query.toLowerCase()

  // 1. Try local exact match on code_barre, reference, or ID
  let match = products.value.find(p =>
    (p.code_barre && p.code_barre.trim().toLowerCase() === queryLower) ||
    (p.reference && p.reference.trim().toLowerCase() === queryLower) ||
    (String(p.id) === query)
  )

  // 2. If not found locally, query backend API
  if (!match) {
    try {
      const { data } = await api.get(`/pos/product-by-barcode/${encodeURIComponent(query)}`)
      if (data && data.id) {
        match = data
        if (!products.value.some(p => p.id === data.id)) {
          products.value.push(data)
        }
      }
    } catch {
      // API call returned 404 or failed
    }
  }

  // 3. Fallback: if filtered list has exactly 1 matching item, use that!
  if (!match && filteredProducts.value.length === 1) {
    match = filteredProducts.value[0]
  }

  // 4. If a product was matched, add it to cart and clear search input
  if (match) {
    addToCart(match)
    searchQuery.value = ''
    nextTick(() => {
      barcodeInput.value?.focus()
    })
  } else {
    // If no match found at all, warn user and clear search query to prevent barcode concatenation
    toast.warning(`Aucun produit trouvé pour "${query}"`)
    searchQuery.value = ''
    nextTick(() => {
      barcodeInput.value?.focus()
    })
  }
}

function addToCart(product) {
  const existing = cart.value.find(i => i.produit_id === product.id)
  if (existing) {
    existing.quantite++
    existing._flash = true
    setTimeout(() => { existing._flash = false }, 400)
  } else {
    cart.value.push({
      produit_id: product.id,
      designation: product.designation,
      prix_unitaire: parseFloat(product.prix_ttc_vente) || 0,
      taux_tva: parseFloat(product.taux_tva) || 0,
      quantite: 1,
      reference: product.reference,
      _flash: true,
    })
    setTimeout(() => {
      const item = cart.value.find(i => i.produit_id === product.id)
      if (item) item._flash = false
    }, 400)
  }
  // Scroll to bottom of cart
  nextTick(() => {
    const cartList = document.getElementById('pos-cart-list')
    if (cartList) cartList.scrollTop = cartList.scrollHeight
  })
}

function incrementQty(index) {
  cart.value[index].quantite++
}

function decrementQty(index) {
  if (cart.value[index].quantite > 1) {
    cart.value[index].quantite--
  }
}

function setQty(index, value) {
  const qty = parseInt(value)
  if (qty > 0) {
    cart.value[index].quantite = qty
  }
}

function removeFromCart(index) {
  cart.value.splice(index, 1)
}

function openPayment() {
  if (cart.value.length === 0) return
  rightTab.value = 'payment'
  if (!montantRecu.value || parseFloat(montantRecu.value) === 0) {
    montantRecu.value = totalTTC.value > 0 ? String(totalTTC.value) : ''
  }
  nextTick(() => {
    recuInputRef.value?.focus()
    recuInputRef.value?.select()
  })
}

function selectPaymentMode(mode) {
  paymentMode.value = mode
  if (mode === 'especes') {
    if (!montantRecu.value || parseFloat(montantRecu.value) === 0) {
      montantRecu.value = totalTTC.value > 0 ? String(totalTTC.value) : ''
    }
    nextTick(() => {
      recuInputRef.value?.focus()
      recuInputRef.value?.select()
    })
  }
}

function setMontantExact() {
  montantRecu.value = String(totalTTC.value)
  recuInputRef.value?.focus()
}

function handleEnterKey() {
  if (canCheckout.value && !isProcessing.value) {
    handleCheckout()
  }
}

function handleNumpad(key) {
  const current = String(montantRecu.value ?? '')
  if (key === '⌫') {
    montantRecu.value = current.slice(0, -1)
  } else if (key === '.' && current.includes('.')) {
    return
  } else {
    montantRecu.value = current + key
  }
}

async function handleCheckout() {
  if (!canCheckout.value || isProcessing.value) return
  isProcessing.value = true

  try {
    const effectiveRecu = paymentMode.value === 'especes'
      ? (parseFloat(montantRecu.value) || totalTTC.value)
      : totalTTC.value

    if (isRectifyingSale.value && rectifyingSaleId.value) {
      const payload = {
        lignes: cart.value.map(item => ({
          produit_id: item.produit_id,
          produit_fini_id: item.produit_fini_id || null,
          is_produit_fini: item.is_produit_fini || false,
          designation: item.designation,
          quantite: item.quantite,
          prix_unitaire: item.prix_unitaire,
          taux_tva: item.taux_tva,
        })),
        mode_paiement: paymentMode.value,
        montant_recu: effectiveRecu,
        motif_rectification: motifRectification.value || 'Rectification effectuée en caisse',
      }

      const { data } = await api.put(`/pos/sales/${rectifyingSaleId.value}/rectify`, payload)
      lastSale.value = {
        numero: data.numero,
        total_ttc: data.total_ttc,
        monnaie: data.monnaie,
        facture_id: data.facture?.id || rectifyingSaleId.value,
        is_rectified: true,
      }
      toast.success(data.message || 'Vente rectifiée avec succès !')
      abortRectificationStateOnly()
      showSuccess.value = true
      await loadSalesHistory()
      await loadProducts()
      return
    }

    const payload = {
      client_id: null,
      lignes: cart.value.map(item => ({
        produit_id: item.produit_id,
        produit_fini_id: item.produit_fini_id || null,
        is_produit_fini: item.is_produit_fini || false,
        designation: item.designation,
        quantite: item.quantite,
        prix_unitaire: item.prix_unitaire,
        taux_tva: item.taux_tva,
      })),
      mode_paiement: paymentMode.value,
      montant_recu: effectiveRecu,
      observations: observationPaiement.value ? observationPaiement.value.trim() : null,
    }

    const { data } = await api.post('/pos/checkout', payload)
    lastSale.value = {
      numero: data.numero,
      total_ttc: data.total_ttc,
      monnaie: data.monnaie,
      facture_id: data.facture?.id,
      is_rectified: false,
    }
    showSuccess.value = true
    await loadSalesHistory()
    await loadProducts()
  } catch (e) {
    const msg = e.response?.data?.message || 'Erreur lors de l\'enregistrement de la vente'
    toast.error(msg)
  } finally {
    isProcessing.value = false
  }
}

// ─── Modal Actions & Rectification ───
async function openSaleDetailModal(sale) {
  loadingSaleDetail.value = true
  showDetailModal.value = true
  selectedSaleDetail.value = null
  try {
    const { data } = await api.get(`/pos/sales/${sale.id}`)
    selectedSaleDetail.value = data
  } catch {
    toast.error('Impossible de charger les détails de cette vente.')
    showDetailModal.value = false
  } finally {
    loadingSaleDetail.value = false
  }
}

function openCancelModal(sale) {
  saleToCancel.value = sale
  motifCancel.value = 'Erreur de saisie / Caisse'
  showCancelModal.value = true
}

async function confirmCancelSale() {
  if (!saleToCancel.value) return
  isCancelling.value = true
  try {
    const { data } = await api.post(`/pos/sales/${saleToCancel.value.id}/cancel`, {
      motif: motifCancel.value || 'Annulation demandée au Point de Vente'
    })
    toast.success(data.message || 'Vente annulée avec succès. Les stocks ont été réintégrés.')
    showCancelModal.value = false
    showDetailModal.value = false
    saleToCancel.value = null
    await loadSalesHistory()
    await loadProducts()
  } catch (e) {
    const msg = e.response?.data?.message || 'Erreur lors de l\'annulation de la vente'
    toast.error(msg)
  } finally {
    isCancelling.value = false
  }
}

async function startRectifySale(sale) {
  try {
    toast.info(`Chargement de la vente #${sale.numero}...`)
    const { data } = await api.get(`/pos/sales/${sale.id}`)

    if (data.est_annulee) {
      toast.error('Impossible de rectifier une vente déjà annulée.')
      return
    }

    rectifyingSaleId.value = data.id
    rectifyingSaleNumero.value = data.numero
    originalSaleTotal.value = data.total_ttc
    isRectifyingSale.value = true
    motifRectification.value = 'Modification / Rectification des articles en caisse'

    cart.value = data.lignes.map(l => ({
      produit_id: l.produit_id,
      produit_fini_id: l.produit_fini_id || null,
      is_produit_fini: l.is_produit_fini || false,
      designation: l.designation,
      prix_unitaire: l.prix_unitaire,
      taux_tva: l.taux_tva,
      quantite: l.quantite,
      reference: l.reference || '',
      _flash: false
    }))

    paymentMode.value = data.mode_paiement_code || 'especes'
    montantRecu.value = String(data.total_ttc)

    showDetailModal.value = false
    rightTab.value = 'products'
    toast.success(`Mode Rectification actif pour la vente #${data.numero}. Modifiez le panier puis validez le paiement.`)
  } catch {
    toast.error('Erreur lors de la préparation de la rectification.')
  }
}

function abortRectification() {
  isRectifyingSale.value = false
  rectifyingSaleId.value = null
  rectifyingSaleNumero.value = ''
  originalSaleTotal.value = 0
  motifRectification.value = ''
  cart.value = []
  montantRecu.value = ''
  toast.info('Mode rectification annulé.')
}

function abortRectificationStateOnly() {
  isRectifyingSale.value = false
  rectifyingSaleId.value = null
  rectifyingSaleNumero.value = ''
  originalSaleTotal.value = 0
  motifRectification.value = ''
}

function printTicket() {
  if (lastSale.value?.facture_id) {
    window.open(`/print/ticket/${lastSale.value.facture_id}`, '_blank')
  }
}

async function openHistory() {
  rightTab.value = 'history'
  await loadSalesHistory()
}

function setHistoryPeriod(periodKey) {
  historyPeriod.value = periodKey
  if (periodKey === 'custom') {
    if (!historyCustomStart.value || !historyCustomEnd.value) {
      const today = new Date()
      historyCustomEnd.value = today.toISOString().split('T')[0]
      const past = new Date()
      past.setDate(past.getDate() - 7)
      historyCustomStart.value = past.toISOString().split('T')[0]
    }
  }
  loadSalesHistory()
}

async function loadSalesHistory() {
  loadingHistory.value = true
  try {
    const params = { period: historyPeriod.value }
    if (historyPeriod.value === 'custom') {
      if (historyCustomStart.value && historyCustomEnd.value) {
        params.start_date = historyCustomStart.value
        params.end_date = historyCustomEnd.value
      }
    }
    const { data } = await api.get('/pos/history', { params })
    salesHistory.value = Array.isArray(data) ? data : []
  } catch {
    toast.error('Impossible de charger l\'historique des ventes.')
  } finally {
    loadingHistory.value = false
  }
}

function printHistorySale(saleId) {
  if (saleId) {
    window.open(`/print/ticket/${saleId}`, '_blank')
  }
}

// ─── Clôture de Caisse (Rapport Z) Methods ───
async function openCloture() {
  rightTab.value = 'cloture'
  clotureSubTab.value = 'new'
  await loadCurrentClotureSession()
}

async function loadCurrentClotureSession() {
  loadingClotureSession.value = true
  try {
    const { data } = await api.get('/pos/cloture/current', {
      params: { fond_initial: clotureFondInitial.value }
    })
    clotureSession.value = data
    if (clotureFondInitial.value === 0 && data.fond_initial > 0) {
      clotureFondInitial.value = data.fond_initial
    }
  } catch (e) {
    toast.error('Erreur lors du calcul de la session de caisse.')
  } finally {
    loadingClotureSession.value = false
  }
}

function recalculateCloture() {
  // Handled reactively via computed properties
}

async function submitCloture() {
  const declare = parseFloat(clotureTotalDeclare.value)
  if (isNaN(declare)) {
    toast.error('Veuillez saisir le montant total déclaré compté dans le tiroir.')
    return
  }

  isSubmittingCloture.value = true
  try {
    const payload = {
      fond_initial: parseFloat(clotureFondInitial.value) || 0,
      total_declare: declare,
      observations: clotureObservations.value ? clotureObservations.value.trim() : null,
    }
    const { data } = await api.post('/pos/cloture', payload)
    toast.success(data.message || 'Caisse clôturée avec succès !')
    currentZReportText.value = data.rapport_texte || ''
    showZReportModal.value = true
    clotureTotalDeclare.value = ''
    clotureObservations.value = ''
    await loadCurrentClotureSession()
    await loadClotureHistory()
  } catch (e) {
    const msg = e.response?.data?.message || 'Erreur lors de la clôture de caisse'
    toast.error(msg)
  } finally {
    isSubmittingCloture.value = false
  }
}

async function loadClotureHistory() {
  loadingClotureHistory.value = true
  try {
    const { data } = await api.get('/pos/cloture/history')
    clotureHistoryList.value = Array.isArray(data) ? data : []
  } catch {
    toast.error('Impossible de charger l\'historique des clôtures Z.')
  } finally {
    loadingClotureHistory.value = false
  }
}

function openZReportPreview(z) {
  currentZReportText.value = z.rapport_texte || ''
  showZReportModal.value = true
}

function printCurrentZReport() {
  if (!currentZReportText.value) return
  printZReport(currentZReportText.value)
}

function printZReport(text) {
  const win = window.open('', '_blank', 'width=400,height=650')
  if (!win) {
    toast.error('Veuillez autoriser les fenêtres pop-up pour imprimer.')
    return
  }
  win.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <meta charset="utf-8">
        <title>Rapport de Clôture Z</title>
        <style>
          @page { size: 80mm auto; margin: 4mm; }
          body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
            margin: 0;
            padding: 12px;
            color: #000;
            background: #fff;
          }
          pre {
            font-family: inherit;
            font-size: inherit;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
          }
        </style>
      </head>
      <body>
        <pre>${text}</pre>
        <script>
          window.onload = function() {
            setTimeout(function() {
              window.print();
            }, 300);
          }
        <\/script>
      </body>
    </html>
  `)
  win.document.close()
}

async function copyZReportText() {
  if (!currentZReportText.value) return
  try {
    await navigator.clipboard.writeText(currentZReportText.value)
    toast.success('Rapport Z copié dans le presse-papiers !')
  } catch {
    toast.info('Copie manuelle requise.')
  }
}

function resetAfterSale() {
  cart.value = []
  montantRecu.value = ''
  observationPaiement.value = ''
  paymentMode.value = 'especes'
  rightTab.value = 'products'
  showSuccess.value = false
  lastSale.value = null
  nextTick(() => {
    barcodeInput.value?.focus()
  })
}

function handleExitPos() {
  if (cart.value.length > 0) {
    showExitConfirm.value = true
  } else {
    exitPos()
  }
}

function confirmExit() {
  showExitConfirm.value = false
  isConfirmedExit = true
  cart.value = []
  exitPos()
}

function exitPos() {
  router.push('/dashboard')
}

onBeforeRouteLeave((to, from, next) => {
  if (cart.value.length > 0 && !isConfirmedExit) {
    showExitConfirm.value = true
    next(false)
  } else {
    next()
  }
})

// Global keyboard shortcut: F2 = focus barcode, F5 = new sale, F12 = payment, Enter = checkout
function handleGlobalKeydown(e) {
  if (showSuccess.value && (e.key === 'Enter' || e.key === ' ')) {
    e.preventDefault()
    resetAfterSale()
    return
  }
  if (e.key === 'F2') {
    e.preventDefault()
    barcodeInput.value?.focus()
  } else if (e.key === 'F5') {
    e.preventDefault()
    resetAfterSale()
  } else if (e.key === 'F12') {
    e.preventDefault()
    if (cart.value.length > 0) openPayment()
  } else if (e.key === 'Enter') {
    if (rightTab.value === 'payment' && canCheckout.value && !isProcessing.value) {
      e.preventDefault()
      handleCheckout()
    }
  } else if (e.key === 'Escape') {
    if (showSuccess.value) {
      resetAfterSale()
    } else if (rightTab.value === 'payment') {
      rightTab.value = 'products'
    }
  }
}

// ─── Lifecycle ───
onMounted(() => {
  loadConfig()
  loadProducts()
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════════════
   POS — Point de Vente — Professional Cashier Interface
   Utilise les CSS variables du Design System (light/dark)
   ═══════════════════════════════════════════════════════════════ */

.pos-screen {
  position: relative;
  box-sizing: border-box;
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  font-family: 'Inter', -apple-system, sans-serif;
  background: var(--bg-primary);
  color: var(--text-primary);
  transition: padding-top 0.2s ease;
}

/* ─── LEFT PANEL — CART ─────────────────────────────────────── */
.pos-cart {
  width: 420px;
  min-width: 380px;
  display: flex;
  flex-direction: column;
  background: var(--bg-card);
  border-right: 1px solid var(--border-color);
  box-shadow: var(--shadow-md);
}

.pos-cart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-card);
}

.pos-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pos-brand-icon {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, var(--accent), #8b5cf6);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}

.pos-brand-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.02em;
}

.pos-brand-sub {
  font-size: 0.7rem;
  color: var(--text-muted);
  font-weight: 500;
}

.pos-exit-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.pos-exit-btn:hover {
  background: var(--danger-bg);
  border-color: var(--danger);
  color: var(--danger);
}

/* ─── Search ─── */
.pos-search-bar {
  display: flex;
  align-items: center;
  padding: 10px 14px;
  border-bottom: 1px solid var(--border-color);
  position: relative;
}

.pos-search-icon {
  position: absolute;
  left: 24px;
  color: var(--text-muted);
  display: flex;
}

.pos-search-input {
  flex: 1;
  padding: 10px 14px 10px 36px;
  background: var(--bg-input);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  transition: all 0.2s;
}
.pos-search-input::placeholder {
  color: var(--text-muted);
}
.pos-search-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-subtle);
}

.pos-search-clear {
  position: absolute;
  right: 22px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: var(--subtle);
  color: var(--text-muted);
  font-size: 0.7rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ─── Cart Items ─── */
.pos-cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 6px 0;
  scrollbar-width: thin;
  scrollbar-color: var(--border-color) transparent;
}

.pos-cart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--text-muted);
  gap: 8px;
}
.pos-cart-empty p {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin: 0;
}
.pos-cart-empty span {
  font-size: 0.78rem;
  color: var(--text-muted);
}

.pos-cart-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  border-bottom: 1px solid var(--subtle);
  transition: background 0.15s;
}
.pos-cart-row:hover {
  background: var(--bg-card-hover);
}
.pos-cart-row-flash {
  animation: flash-row 0.4s ease;
}
@keyframes flash-row {
  0% { background: var(--accent-subtle); }
  100% { background: transparent; }
}

.pos-cart-row-info {
  flex: 1;
  min-width: 0;
}

.pos-cart-row-name {
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--text-primary);
}

.pos-cart-row-meta {
  font-size: 0.72rem;
  color: var(--text-muted);
  margin-top: 1px;
}

.pos-cart-row-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.pos-qty-controls {
  display: flex;
  align-items: center;
  background: var(--bg-input);
  border-radius: 6px;
  border: 1px solid var(--border-color);
}

.pos-qty-btn {
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.pos-qty-btn:hover:not(:disabled) {
  background: var(--subtle);
  color: var(--text-primary);
}
.pos-qty-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.pos-qty-input {
  width: 36px;
  text-align: center;
  border: none;
  border-left: 1px solid var(--border-color);
  border-right: 1px solid var(--border-color);
  background: transparent;
  color: var(--text-primary);
  font-size: 0.82rem;
  font-weight: 600;
  font-family: inherit;
  outline: none;
  -moz-appearance: textfield;
}
.pos-qty-input::-webkit-outer-spin-button,
.pos-qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}

.pos-cart-row-total {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-primary);
  min-width: 70px;
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.pos-remove-btn {
  width: 26px;
  height: 26px;
  border: none;
  border-radius: 6px;
  background: var(--danger-bg);
  color: var(--danger);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.pos-remove-btn:hover {
  background: var(--danger);
  color: white;
}

/* Cart animations */
.cart-item-enter-active {
  transition: all 0.25s ease-out;
}
.cart-item-leave-active {
  transition: all 0.2s ease-in;
}
.cart-item-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}
.cart-item-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

/* ─── Cart Footer ─── */
.pos-cart-footer {
  border-top: 1px solid var(--border-color);
  background: var(--bg-card);
}

.pos-cart-summary {
  padding: 10px 18px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pos-summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.pos-total-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 18px;
  background: var(--accent-subtle);
  border-top: 1px solid var(--accent);
}

.pos-total-label {
  font-size: 0.9rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: var(--text-secondary);
}

.pos-total-amount {
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--accent);
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.02em;
}

/* ─── RIGHT PANEL ───────────────────────────────────────────── */
.pos-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--bg-primary);
  overflow: hidden;
}

.pos-right-tabs {
  display: flex;
  gap: 2px;
  padding: 10px 16px 0;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-card);
}

.pos-tab {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
  border-radius: 6px 6px 0 0;
}
.pos-tab:hover:not(:disabled) {
  color: var(--text-secondary);
  background: var(--subtle);
}
.pos-tab.active {
  color: var(--accent);
  border-bottom-color: var(--accent);
  background: var(--accent-subtle);
}
.pos-tab:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* ─── Products Panel ─── */
.pos-products-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.pos-categories {
  display: flex;
  gap: 6px;
  padding: 12px 16px;
  overflow-x: auto;
  flex-shrink: 0;
  scrollbar-width: none;
}
.pos-categories::-webkit-scrollbar { display: none; }

.pos-cat-btn {
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-muted);
  font-size: 0.78rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s;
}
.pos-cat-btn:hover {
  background: var(--bg-card-hover);
  color: var(--text-primary);
}
.pos-cat-btn.active {
  background: var(--accent);
  border-color: var(--accent);
  color: white;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
}

.pos-products-grid {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 10px;
  padding: 12px 16px;
  overflow-y: auto;
  align-content: start;
  scrollbar-width: thin;
  scrollbar-color: var(--border-color) transparent;
}

.pos-products-loading {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 60px;
  color: var(--text-muted);
}

.pos-spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--border-color);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
.pos-spinner-sm {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.pos-product-card {
  display: flex;
  flex-direction: column;
  padding: 10px 10px 12px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: left;
  font-family: inherit;
  color: var(--text-primary);
  min-height: 165px;
  box-shadow: var(--shadow-sm);
  position: relative;
}
.pos-product-card:hover {
  background: var(--bg-card);
  border-color: var(--accent);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
}
.pos-product-card:active {
  transform: scale(0.97);
}

.pos-product-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
  width: 100%;
}

.pos-product-ref {
  font-size: 0.65rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 90px;
}

.pos-product-stock {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 10px;
  background: var(--success-bg);
  color: var(--success);
}
.pos-product-stock.low {
  background: var(--warning-bg);
  color: var(--warning);
}

.pos-product-img-box {
  width: 100%;
  height: 80px;
  border-radius: 8px;
  background: var(--subtle);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.pos-product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.25s ease;
}
.pos-product-card:hover .pos-product-img {
  transform: scale(1.08);
}

.pos-product-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: var(--text-muted);
  opacity: 0.5;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.02), rgba(0, 0, 0, 0.02));
}

.pos-product-name {
  font-size: 0.82rem;
  font-weight: 600;
  line-height: 1.3;
  margin-bottom: 6px;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.pos-product-price {
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--accent);
  font-variant-numeric: tabular-nums;
  margin-top: auto;
}

.pos-no-products {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px;
  color: var(--text-muted);
  font-size: 0.9rem;
}

/* ─── Cart Checkout CTA Button ─── */
.pos-btn-checkout-cta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: calc(100% - 32px);
  margin: 12px 16px 16px;
  padding: 14px 18px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-size: 0.96rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.pos-btn-checkout-cta:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}
.pos-btn-checkout-cta:active:not(:disabled) {
  transform: scale(0.98);
}
.pos-btn-checkout-cta:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}
.pos-shortcut-badge {
  font-size: 0.72rem;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
  letter-spacing: 0.04em;
}

/* ─── Payment Panel (Modern & User-Friendly) ─── */
.pos-payment-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 18px 24px;
  gap: 14px;
  overflow-y: auto;
  scrollbar-width: thin;
}

.pos-payment-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.pos-back-link {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 8px;
  transition: all 0.15s;
}
.pos-back-link:hover {
  background: var(--subtle);
  color: var(--text-primary);
}

.pos-payment-badge-items {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--accent);
  background: var(--accent-subtle);
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid var(--accent);
}

.pos-payment-total-display {
  text-align: center;
  padding: 16px 20px;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(99, 102, 241, 0.12));
  border-radius: 14px;
  border: 1.5px solid var(--accent);
  box-shadow: 0 4px 18px rgba(59, 130, 246, 0.08);
}

.pos-payment-total-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.pos-payment-total-value {
  font-size: 2.3rem;
  font-weight: 800;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.03em;
  margin-top: 2px;
}

/* 4 Payment Modes */
.pos-payment-modes-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.pos-pay-mode-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 12px 8px;
  border-radius: 12px;
  border: 2px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: center;
}
.pos-pay-mode-card:hover {
  border-color: var(--accent);
  color: var(--text-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}
.pos-pay-mode-card.active {
  border-color: var(--accent);
  background: var(--accent-subtle);
  color: var(--accent);
  box-shadow: 0 0 16px rgba(59, 130, 246, 0.15);
  font-weight: 700;
}
.pos-pay-mode-card.especes.active {
  border-color: var(--success);
  background: var(--success-bg);
  color: var(--success);
  box-shadow: 0 0 16px rgba(16, 185, 129, 0.15);
}

.pos-mode-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}
.pos-mode-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.pos-mode-title {
  font-size: 0.85rem;
  font-weight: 700;
}
.pos-mode-sub {
  font-size: 0.65rem;
  opacity: 0.75;
}

/* Cash Section */
.pos-cash-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.pos-recu-wrapper {
  background: var(--bg-card);
  border-radius: 12px;
  border: 1px solid var(--border-color);
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-recu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pos-recu-label {
  font-size: 0.76rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-muted);
  letter-spacing: 0.05em;
}
.pos-btn-exact-pill {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 8px;
  border: 1px solid var(--accent);
  background: var(--accent-subtle);
  color: var(--accent);
  cursor: pointer;
  transition: all 0.15s;
}
.pos-btn-exact-pill:hover {
  background: var(--accent);
  color: white;
}

.pos-recu-input-box {
  display: flex;
  align-items: center;
  background: var(--bg-input);
  border: 1.5px solid var(--border-color);
  border-radius: 10px;
  padding: 4px 12px;
  transition: border-color 0.2s;
}
.pos-recu-input-box:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.pos-recu-input-field {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
  outline: none;
  padding: 4px 0;
  min-width: 0;
}
.pos-recu-devise {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-muted);
  margin-left: 8px;
}
.pos-recu-clear {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 1rem;
  padding: 4px 8px;
  cursor: pointer;
  border-radius: 6px;
}
.pos-recu-clear:hover {
  background: var(--subtle);
  color: var(--text-primary);
}

/* Quick pills */
.pos-quick-cash-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.pos-quick-pill {
  flex: 1;
  min-width: 70px;
  padding: 8px 10px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 0.84rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  text-align: center;
}
.pos-quick-pill:hover {
  border-color: var(--accent);
  background: var(--accent-subtle);
  color: var(--accent);
}
.pos-quick-pill.selected {
  border-color: var(--success);
  background: var(--success-bg);
  color: var(--success);
  font-weight: 800;
}

/* Change banners */
.pos-change-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  transition: all 0.2s;
}
.pos-change-banner.positive {
  background: var(--success-bg);
  border: 1.5px solid var(--success);
  color: var(--success);
}
.pos-change-banner.warning {
  background: var(--warning-bg);
  border: 1.5px solid var(--warning);
  color: var(--warning);
}
.pos-change-banner.neutral {
  background: var(--subtle);
  border: 1px solid var(--border-color);
  color: var(--text-muted);
}
.pos-change-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pos-change-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}
.pos-change-label {
  font-size: 0.8rem;
  font-weight: 700;
}
.pos-change-amount {
  font-size: 1.35rem;
  font-weight: 800;
  font-variant-numeric: tabular-nums;
  margin-top: 1px;
}
.pos-quick-complete-btn {
  background: var(--warning);
  color: #1e1b4b;
  border: none;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: opacity 0.15s;
  white-space: nowrap;
}
.pos-quick-complete-btn:hover {
  opacity: 0.9;
}

/* Numpad */
.pos-numpad {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.pos-numpad-key {
  padding: 12px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 1.15rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.12s;
  user-select: none;
  box-shadow: var(--shadow-sm);
  display: flex;
  align-items: center;
  justify-content: center;
}
.pos-numpad-key:hover {
  background: var(--bg-card-hover);
  border-color: var(--accent);
}
.pos-numpad-key:active {
  background: var(--accent-subtle);
  transform: scale(0.96);
}

/* Non cash info */
.pos-non-cash-info {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.pos-non-cash-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: var(--accent-subtle);
  border: 1.5px solid var(--accent);
  padding: 18px;
  border-radius: 14px;
}
.pos-non-cash-icon {
  color: var(--accent);
}
.pos-non-cash-details h4 {
  margin: 0 0 4px 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-primary);
}
.pos-non-cash-details p {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
.pos-field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.pos-field-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text-muted);
}
.pos-field-input {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  background: var(--bg-input);
  color: var(--text-primary);
  font-family: inherit;
  font-size: 0.88rem;
  outline: none;
  transition: border-color 0.15s;
}
.pos-field-input:focus {
  border-color: var(--accent);
}

/* Checkout action box & Validate button */
.pos-checkout-action-box {
  margin-top: auto;
  padding-top: 10px;
}

.pos-validate-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  width: 100%;
  padding: 16px 20px;
  border-radius: 14px;
  border: none;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-size: 1.05rem;
  font-weight: 800;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 6px 22px rgba(16, 185, 129, 0.35);
}
.pos-validate-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 8px 26px rgba(16, 185, 129, 0.45);
}
.pos-validate-btn:active:not(:disabled) {
  transform: scale(0.98);
}
.pos-validate-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.pos-validate-btn-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}
.pos-validate-sub {
  font-size: 0.74rem;
  font-weight: 600;
  opacity: 0.9;
}

/* ═══ SUCCESS MODAL ═══ */
.pos-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.pos-success-modal {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 20px;
  padding: 40px;
  text-align: center;
  max-width: 400px;
  width: 90%;
  box-shadow: var(--shadow-lg);
}

.pos-success-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--success-bg);
  border: 2px solid var(--success);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  color: var(--success);
  animation: success-pop 0.5s ease;
}

@keyframes success-pop {
  0% { transform: scale(0); opacity: 0; }
  60% { transform: scale(1.15); }
  100% { transform: scale(1); opacity: 1; }
}

.pos-success-title {
  font-size: 1.3rem;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0 0 16px;
}

.pos-success-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 24px;
}

.pos-success-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 14px;
  background: var(--subtle);
  border-radius: 8px;
  font-size: 0.9rem;
}
.pos-success-row span {
  color: var(--text-muted);
}
.pos-success-row strong {
  color: var(--text-primary);
  font-weight: 700;
}
.pos-success-row.change {
  background: var(--success-bg);
  border: 1px solid var(--success);
}
.pos-success-row.change strong {
  color: var(--success);
}

.pos-success-actions {
  display: flex;
  gap: 10px;
}

.pos-btn-print {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-secondary);
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s;
}
.pos-btn-print:hover {
  background: var(--bg-card-hover);
}

.pos-btn-new {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, var(--accent), #6366f1);
  color: white;
  font-size: 0.85rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}
.pos-btn-new:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
}

/* Modal transitions */
.modal-enter-active { transition: all 0.3s ease; }
.modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from { opacity: 0; }
.modal-leave-to { opacity: 0; }
.modal-enter-from .pos-success-modal { transform: scale(0.9); }
.modal-leave-to .pos-success-modal { transform: scale(0.9); }

/* ─── History Panel (Clean & Ultra-Practical) ─── */
.pos-history-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 18px 24px;
  gap: 14px;
  overflow-y: auto;
  scrollbar-width: thin;
}

.pos-history-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.pos-history-title-box {
  display: flex;
  align-items: center;
  gap: 10px;
}
.pos-history-title-box h3 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
}
.pos-history-count {
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--accent);
  background: var(--accent-subtle);
  padding: 3px 9px;
  border-radius: 12px;
  border: 1px solid var(--accent);
}

.pos-history-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pos-history-search {
  position: relative;
  display: flex;
  align-items: center;
}
.pos-history-search-input {
  padding: 6px 28px 6px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-primary);
  font-size: 0.8rem;
  font-family: inherit;
  width: 190px;
  outline: none;
  transition: all 0.15s;
}
.pos-history-search-input:focus {
  border-color: var(--accent);
  width: 220px;
}
.pos-history-search-clear {
  position: absolute;
  right: 6px;
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 0.8rem;
}

.pos-history-refresh-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}
.pos-history-refresh-btn:hover:not(:disabled) {
  background: var(--subtle);
  color: var(--text-primary);
  border-color: var(--accent);
}
.pos-history-refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.pos-spinning {
  animation: spin 0.8s linear infinite;
}

/* History Period Filter Bar */
.pos-history-filter-strip {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-period-pills {
  display: flex;
  align-items: center;
  gap: 4px;
  background: var(--bg-card);
  padding: 4px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  overflow-x: auto;
}

.pos-period-pill {
  flex: 1;
  text-align: center;
  white-space: nowrap;
  padding: 6px 8px;
  font-size: 0.74rem;
  font-weight: 600;
  border-radius: 7px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s ease;
  user-select: none;
}

.pos-period-pill:hover {
  color: var(--text-primary);
  background: var(--subtle);
}

.pos-period-pill.active {
  background: var(--accent);
  color: #fff;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
}

.pos-history-custom-dates {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: var(--bg-card);
  border-radius: 8px;
  border: 1px dashed var(--border-color);
}

.pos-date-field {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 6px;
}

.pos-date-lbl {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-muted);
}

.pos-history-date-input {
  flex: 1;
  padding: 5px 8px;
  font-size: 0.78rem;
  font-family: inherit;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--subtle);
  color: var(--text-primary);
  outline: none;
}

.pos-history-date-input:focus {
  border-color: var(--accent);
}

.pos-date-sep {
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--text-muted);
}

/* Summary Bar */
.pos-history-summary-bar {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.pos-history-summary-card {
  padding: 12px 16px;
  background: var(--bg-card);
  border-radius: 10px;
  border: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.pos-hist-lbl {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--text-muted);
  letter-spacing: 0.04em;
}
.pos-hist-val {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
}
.pos-hist-val.accent {
  color: var(--accent);
}

/* History List */
.pos-history-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-history-loading,
.pos-history-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 20px;
  color: var(--text-muted);
  gap: 10px;
  font-size: 0.85rem;
}

.pos-history-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background: var(--bg-card);
  border-radius: 12px;
  border: 1px solid var(--border-color);
  transition: all 0.15s;
}
.pos-history-item:hover {
  border-color: var(--accent);
  box-shadow: var(--shadow-sm);
  transform: translateX(2px);
}

.pos-hist-main {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.pos-hist-top-line {
  display: flex;
  align-items: center;
  gap: 8px;
}
.pos-hist-numero {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--text-primary);
  font-family: monospace, monospace;
}
.pos-hist-badge {
  font-size: 0.68rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  background: var(--subtle);
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
}
.pos-hist-badge.espèces,
.pos-hist-badge.especes {
  background: var(--success-bg);
  color: var(--success);
  border-color: var(--success);
}
.pos-hist-badge.carte {
  background: var(--accent-subtle);
  color: var(--accent);
  border-color: var(--accent);
}

.pos-hist-meta-line {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.74rem;
  color: var(--text-muted);
}
.pos-hist-dot {
  opacity: 0.5;
}

.pos-hist-right {
  display: flex;
  align-items: center;
  gap: 14px;
}
.pos-hist-total {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
}

.pos-hist-print-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-primary);
  font-size: 0.76rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.pos-hist-print-btn:hover {
  background: var(--accent);
  border-color: var(--accent);
  color: white;
}

/* ══════════════════════════════════════════════════════════
   CLÔTURE DE CAISSE (RAPPORT Z)
   ══════════════════════════════════════════════════════════ */
.pos-cloture-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow-y: auto;
  gap: 16px;
  padding: 4px;
}

.pos-cloture-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-color);
  flex-wrap: wrap;
}

.pos-cloture-title-box {
  display: flex;
  align-items: center;
  gap: 12px;
}

.pos-cloture-icon-badge {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--accent-subtle, rgba(16, 185, 129, 0.12));
  color: var(--accent, #10b981);
  display: flex;
  align-items: center;
  justify-content: center;
}

.pos-cloture-title-box h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--text-primary);
}

.pos-cloture-sub {
  margin: 2px 0 0;
  font-size: 0.78rem;
  color: var(--text-muted);
}

.pos-cloture-nav-pills {
  display: flex;
  gap: 6px;
  background: var(--bg-card);
  padding: 4px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.pos-cloture-nav-pill {
  padding: 6px 14px;
  font-size: 0.78rem;
  font-weight: 700;
  border-radius: 7px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}

.pos-cloture-nav-pill:hover {
  color: var(--text-primary);
}

.pos-cloture-nav-pill.active {
  background: var(--accent);
  color: #fff;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}

.pos-cloture-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Meta banner */
.pos-cloture-meta-banner {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  background: var(--bg-card);
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.pos-cloture-meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.pos-meta-lbl {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-muted);
  letter-spacing: 0.04em;
}

.pos-meta-val {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--text-primary);
}

/* Sections */
.pos-cloture-section-card {
  background: var(--bg-card);
  border-radius: 12px;
  border: 1px solid var(--border-color);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.pos-cloture-section-card.highlight {
  border-color: var(--accent);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.08);
}

.pos-sec-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 8px;
  border-bottom: 1px dashed var(--border-color);
}

.pos-sec-num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pos-sec-header h4 {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 800;
  letter-spacing: 0.03em;
  color: var(--text-primary);
}

.pos-cloture-kpi-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 8px;
}

.pos-cloture-kpi-main {
  display: flex;
  flex-direction: column;
}

.pos-kpi-lbl {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
}

.pos-kpi-val {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--accent);
  font-variant-numeric: tabular-nums;
}

.pos-kpi-val-sm {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
}

/* Pay modes breakdown */
.pos-pay-modes-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-pay-mode-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 8px;
  background: var(--subtle);
}

.pos-pay-mode-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--text-primary);
}

.pos-dot-mode {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.pos-dot-mode.especes { background: #10b981; }
.pos-dot-mode.carte   { background: #3b82f6; }
.pos-dot-mode.cheque  { background: #f59e0b; }
.pos-dot-mode.virement{ background: #8b5cf6; }

.pos-pay-mode-amount {
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
}

/* Tiroir Grid */
.pos-tiroir-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.pos-tiroir-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pos-tiroir-field label {
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--text-secondary);
}

.pos-cloture-input {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 700;
  font-family: inherit;
  outline: none;
  transition: all 0.15s;
}

.pos-cloture-input:focus {
  border-color: var(--accent);
  background: var(--bg-card);
}

.pos-cloture-input.declare-input {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.05);
  font-size: 1.15rem;
  color: var(--text-primary);
}
.pos-cloture-input.declare-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
}

.pos-readonly-box {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
}
.pos-readonly-box.accent {
  color: var(--accent);
  font-size: 1.1rem;
}

.pos-input-hint {
  font-size: 0.68rem;
  color: var(--text-muted);
}

/* Ecart Banner */
.pos-ecart-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-radius: 10px;
  border: 1px solid;
  margin-top: 4px;
}

.pos-ecart-banner.conforme {
  background: rgba(16, 185, 129, 0.08);
  border-color: rgba(16, 185, 129, 0.3);
  color: #10b981;
}

.pos-ecart-banner.manquant {
  background: rgba(239, 68, 68, 0.08);
  border-color: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

.pos-ecart-banner.excedent {
  background: rgba(59, 130, 246, 0.08);
  border-color: rgba(59, 130, 246, 0.3);
  color: #3b82f6;
}

.pos-ecart-left {
  display: flex;
  flex-direction: column;
}

.pos-ecart-lbl {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  opacity: 0.85;
}

.pos-ecart-val {
  font-size: 1.4rem;
  font-weight: 900;
  font-variant-numeric: tabular-nums;
}

.pos-ecart-badge {
  font-size: 0.82rem;
  font-weight: 800;
  padding: 6px 12px;
  border-radius: 20px;
  background: currentColor;
}
.pos-ecart-badge span {
  color: white !important;
}

/* Observations */
.pos-cloture-obs {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.pos-cloture-obs label {
  font-size: 0.74rem;
  font-weight: 600;
  color: var(--text-muted);
}
.pos-cloture-textarea {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-primary);
  font-family: inherit;
  font-size: 0.82rem;
  outline: none;
  resize: vertical;
}

/* Submit Clôture Button */
.pos-btn-submit-cloture {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff;
  font-size: 0.95rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
  transition: all 0.15s;
}
.pos-btn-submit-cloture:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}
.pos-btn-submit-cloture:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  box-shadow: none;
}

/* Z History List */
.pos-z-history-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.pos-z-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-radius: 12px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  transition: all 0.15s;
}
.pos-z-card:hover {
  border-color: var(--accent);
  box-shadow: var(--shadow-sm);
}

.pos-z-card-main {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pos-z-top {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pos-z-num {
  font-family: var(--font-mono, monospace);
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--text-primary);
}

.pos-z-badge {
  font-size: 0.72rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 12px;
}
.pos-z-badge.conforme {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}
.pos-z-badge.manquant {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}
.pos-z-badge.excedent {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.pos-z-meta, .pos-z-amounts {
  font-size: 0.78rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 6px;
}

.pos-btn-view-z {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-primary);
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.pos-btn-view-z:hover {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
}

/* ══════════════════════════════════════════════════════════
   AUTHENTIC THERMAL RECEIPT MODAL (RAPPORT Z)
   ══════════════════════════════════════════════════════════ */
.pos-z-modal {
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  width: 440px;
  max-width: 95vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.pos-z-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-card);
}

.pos-z-modal-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--text-primary);
}

.pos-receipt-paper-wrapper {
  padding: 20px;
  background: #f1f5f9;
  overflow-y: auto;
  display: flex;
  justify-content: center;
}

.pos-receipt-paper {
  background: #ffffff;
  color: #111827;
  padding: 20px 16px;
  border-radius: 4px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
  width: 100%;
  max-width: 360px;
  border-top: 3px dashed #cbd5e1;
  border-bottom: 3px dashed #cbd5e1;
}

.pos-receipt-content {
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.85rem;
  font-weight: 600;
  line-height: 1.4;
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
  color: #000;
}

.pos-z-modal-footer {
  display: flex;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid var(--border-color);
  background: var(--bg-card);
}

.pos-z-modal-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 11px 16px;
  border-radius: 9px;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.15s;
}

.pos-z-modal-btn.secondary {
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-primary);
}
.pos-z-modal-btn.secondary:hover {
  background: var(--border-color);
}

.pos-z-modal-btn.primary {
  border: none;
  background: var(--accent);
  color: #fff;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.pos-z-modal-btn.primary:hover {
  filter: brightness(1.08);
}

/* ─── Rectification Mode Styles ─── */
/* ─── Rectification Mode Styles ─── */
.pos-screen.is-rectifying-mode {
  padding-top: 48px;
}

.pos-rectify-top-banner {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  background: linear-gradient(90deg, #92400e 0%, #b45309 50%, #d97706 100%);
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(180, 83, 9, 0.35);
  z-index: 100;
  box-sizing: border-box;
  animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
  from { transform: translateY(-100%); }
  to { transform: translateY(0); }
}

.pos-rectify-banner-content {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.88rem;
}

.pos-rectify-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  background: #fef3c7;
  color: #78350f;
  font-size: 0.75rem;
  font-weight: 900;
  border-radius: 20px;
  letter-spacing: 0.5px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.pos-rectify-text {
  font-weight: 500;
}

.pos-rectify-diff-pill {
  padding: 3px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.2px;
}
.pos-rectify-diff-pill.positive {
  background: #dcfce7;
  color: #15803d;
}
.pos-rectify-diff-pill.negative {
  background: #fee2e2;
  color: #b91c1c;
}
.pos-rectify-diff-pill.neutral {
  background: #f1f5f9;
  color: #334155;
}

.pos-rectify-abort-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  background: rgba(0, 0, 0, 0.25);
  color: #ffffff;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}
.pos-rectify-abort-btn:hover {
  background: rgba(0, 0, 0, 0.45);
  border-color: #ffffff;
  transform: translateY(-1px);
}
.pos-rectify-abort-btn:active {
  transform: translateY(0);
}

.pos-rectify-summary-box {
  margin: 14px 0;
  padding: 16px;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(217, 119, 6, 0.04) 100%);
  border: 1px solid rgba(245, 158, 11, 0.3);
  border-radius: 12px;
  color: #92400e;
}
.dark .pos-rectify-summary-box {
  background: linear-gradient(135deg, rgba(180, 83, 9, 0.2) 0%, rgba(120, 53, 15, 0.1) 100%);
  border-color: rgba(245, 158, 11, 0.35);
  color: #fef3c7;
}

.pos-rectify-box-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.88rem;
  font-weight: 800;
  margin-bottom: 12px;
  color: #d97706;
}
.dark .pos-rectify-box-header {
  color: #fbbf24;
}

.pos-rectify-rows {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 0.84rem;
  background: var(--bg-card);
  padding: 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.pos-rectify-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pos-rectify-row.highlight {
  padding-top: 8px;
  margin-top: 2px;
  border-top: 1px dashed var(--border-color);
  font-weight: 700;
}

.pos-validate-btn.rectify-btn {
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
  box-shadow: 0 4px 14px rgba(217, 119, 6, 0.4) !important;
}

/* ─── Status Filter Pills ─── */
.pos-status-pills {
  display: flex;
  gap: 6px;
  margin-left: auto;
}

.pos-status-pill {
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.76rem;
  font-weight: 700;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}
.pos-status-pill:hover {
  background: var(--border-color);
}
.pos-status-pill.active {
  background: var(--text-primary);
  color: var(--bg-card);
  border-color: var(--text-primary);
}
.pos-status-pill.success.active {
  background: #10b981;
  color: #ffffff;
  border-color: #10b981;
}
.pos-status-pill.danger.active {
  background: #ef4444;
  color: #ffffff;
  border-color: #ef4444;
}

.danger-card {
  border-color: rgba(239, 68, 68, 0.3) !important;
  background: rgba(239, 68, 68, 0.05) !important;
}
.danger-text {
  color: #ef4444 !important;
}

/* ─── History Item Cancelled & Actions ─── */
.pos-history-item.is-cancelled-item {
  opacity: 0.75;
  background: rgba(239, 68, 68, 0.03);
  border-color: rgba(239, 68, 68, 0.2);
}

.strikethrough-text {
  text-decoration: line-through;
  opacity: 0.7;
}

.pos-hist-badge.cancelled-badge {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.pos-hist-actions-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
}

.pos-hist-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.74rem;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
}
.pos-hist-action-btn.secondary {
  background: var(--subtle);
  border-color: var(--border-color);
  color: var(--text-primary);
}
.pos-hist-action-btn.secondary:hover {
  background: var(--border-color);
}
.pos-hist-action-btn.print {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border-color: rgba(59, 130, 246, 0.2);
}
.pos-hist-action-btn.print:hover {
  background: #2563eb;
  color: #ffffff;
}
.pos-hist-action-btn.warning {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.25);
}
.pos-hist-action-btn.warning:hover {
  background: #d97706;
  color: #ffffff;
}
.pos-hist-action-btn.danger {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.25);
}
.pos-hist-action-btn.danger:hover {
  background: #dc2626;
  color: #ffffff;
}

/* ─── SALE DETAIL MODAL ─── */
.pos-detail-modal {
  background: var(--bg-card);
  border-radius: 20px;
  box-shadow: 0 25px 70px rgba(0, 0, 0, 0.25);
  width: 680px;
  max-width: 95vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--border-color);
  backdrop-filter: blur(12px);
}

.pos-detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid var(--border-color);
  background: var(--subtle);
}

.pos-detail-title {
  display: flex;
  align-items: center;
  gap: 12px;
}
.pos-detail-title-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: color-mix(in srgb, var(--accent) 12%, var(--bg-card));
  color: var(--accent);
  border: 1px solid color-mix(in srgb, var(--accent) 25%, var(--border-color));
}
.pos-detail-header-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.pos-detail-main-heading {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1.2;
}
.pos-detail-sub-heading {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-muted);
  font-family: monospace;
}

.pos-detail-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 900;
  letter-spacing: 0.05em;
  margin-left: 6px;
}
.pos-detail-status-pill .status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}
.pos-detail-status-pill.success {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}
.pos-detail-status-pill.success .status-dot {
  background: #16a34a;
  box-shadow: 0 0 6px #16a34a;
}
.pos-detail-status-pill.danger {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}
.pos-detail-status-pill.danger .status-dot {
  background: #dc2626;
}

.pos-detail-body {
  padding: 22px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.pos-detail-content {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* ── Meta Cards Grid ── */
.pos-detail-meta-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.pos-detail-meta-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: var(--subtle);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}
.pos-detail-meta-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: var(--bg-card);
  color: var(--text-muted);
  border: 1px solid var(--border-color);
  flex-shrink: 0;
}
.pos-detail-meta-icon.accent {
  background: color-mix(in srgb, var(--accent) 10%, var(--bg-card));
  color: var(--accent);
  border-color: color-mix(in srgb, var(--accent) 20%, var(--border-color));
}

.pos-detail-meta-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}
.pos-detail-meta-label {
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.pos-detail-meta-value {
  font-size: 0.86rem;
  font-weight: 700;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pos-detail-meta-value.accent {
  color: var(--accent);
}

/* ── Section Header (Articles) ── */
.pos-detail-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pos-detail-section-head {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 0.76rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0 2px;
}

/* ── Articles Table ── */
.pos-detail-table-wrap {
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
}
.pos-detail-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.83rem;
  table-layout: fixed;
}
.pos-detail-table th {
  padding: 10px 14px;
  font-weight: 700;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  background: var(--subtle);
  border-bottom: 1px solid var(--border-color);
}
.pos-detail-table td {
  padding: 11px 14px;
  color: var(--text-primary);
  vertical-align: middle;
}
.pos-detail-table tbody tr {
  border-bottom: 1px solid var(--border-color);
  transition: background 0.1s;
}
.pos-detail-table tbody tr:last-child {
  border-bottom: none;
}
.pos-detail-table tbody tr:hover {
  background: color-mix(in srgb, var(--accent) 4%, transparent);
}
.pos-detail-table tbody tr.row-alt {
  background: color-mix(in srgb, var(--subtle) 50%, transparent);
}
.pos-detail-table tbody tr.row-alt:hover {
  background: color-mix(in srgb, var(--accent) 5%, var(--subtle));
}

/* Column widths */
.pos-detail-table .col-product { width: auto; text-align: left; }
.pos-detail-table .col-price { width: 110px; text-align: right; }
.pos-detail-table .col-qty { width: 60px; text-align: center; }
.pos-detail-table .col-tva { width: 65px; text-align: right; }
.pos-detail-table .col-total { width: 120px; text-align: right; font-weight: 700; }

.pos-detail-prod-name {
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.3;
}
.pos-detail-prod-ref {
  font-size: 0.72rem;
  color: var(--text-muted);
  font-weight: 500;
  margin-top: 1px;
}
.pos-detail-qty-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 26px;
  height: 24px;
  padding: 0 6px;
  border-radius: 6px;
  background: color-mix(in srgb, var(--accent) 10%, var(--subtle));
  color: var(--accent);
  font-weight: 800;
  font-size: 0.82rem;
}

/* ── Totals ── */
.pos-detail-totals {
  display: flex;
  justify-content: flex-end;
}
.pos-detail-totals-grid {
  display: flex;
  flex-direction: column;
  gap: 7px;
  min-width: 260px;
  padding: 14px 18px;
  background: var(--subtle);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}
.pos-detail-total-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pos-detail-total-label {
  font-size: 0.82rem;
  color: var(--text-muted);
  font-weight: 600;
}
.pos-detail-total-amount {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-primary);
  font-variant-numeric: tabular-nums;
}
.pos-detail-total-line.grand {
  padding-top: 8px;
  margin-top: 4px;
  border-top: 2px solid var(--border-color);
}
.pos-detail-total-line.grand .pos-detail-total-label {
  font-weight: 900;
  font-size: 0.9rem;
  color: var(--accent);
}
.pos-detail-total-line.grand .pos-detail-total-amount {
  font-weight: 900;
  font-size: 1.05rem;
  color: var(--accent);
}

/* ── Observations ── */
.pos-detail-observations {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 12px 14px;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.06), rgba(245, 158, 11, 0.03));
  border: 1px solid rgba(245, 158, 11, 0.2);
  border-radius: 12px;
}
.pos-detail-obs-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #d97706;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.pos-detail-obs-content {
  font-size: 0.82rem;
  color: var(--text-primary);
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

/* ── Footer ── */
.pos-detail-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid var(--border-color);
  background: var(--subtle);
}
.pos-detail-footer-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.pos-detail-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}
.pos-detail-action:active {
  transform: scale(0.97);
}

.pos-detail-action.close {
  background: var(--bg-card);
  color: var(--text-muted);
  border-color: var(--border-color);
}
.pos-detail-action.close:hover {
  background: var(--border-color);
  color: var(--text-primary);
}

.pos-detail-action.print {
  background: rgba(59, 130, 246, 0.08);
  color: #2563eb;
  border-color: rgba(59, 130, 246, 0.18);
}
.pos-detail-action.print:hover {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

.pos-detail-action.rectify {
  background: rgba(245, 158, 11, 0.08);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.18);
}
.pos-detail-action.rectify:hover {
  background: #d97706;
  color: white;
  border-color: #d97706;
  box-shadow: 0 2px 8px rgba(217, 119, 6, 0.25);
}

.pos-detail-action.cancel {
  background: rgba(239, 68, 68, 0.08);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.18);
}
.pos-detail-action.cancel:hover {
  background: #dc2626;
  color: white;
  border-color: #dc2626;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
}

/* ─── CANCELLATION MODAL ─── */
.pos-cancel-modal {
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  width: 480px;
  max-width: 95vw;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.pos-cancel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color);
  background: #fef2f2;
}
.dark .pos-cancel-header {
  background: rgba(239, 68, 68, 0.15);
}

.pos-cancel-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.05rem;
  font-weight: 800;
  color: #dc2626;
}

.pos-cancel-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.pos-cancel-alert {
  padding: 12px 14px;
  background: #fee2e2;
  color: #991b1b;
  border-radius: 10px;
  font-size: 0.82rem;
  line-height: 1.4;
  border: 1px solid #fca5a5;
}
.dark .pos-cancel-alert {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.4);
}

.pos-cancel-summary {
  display: flex;
  justify-content: space-between;
  font-size: 0.86rem;
  padding: 10px 14px;
  background: var(--subtle);
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.pos-cancel-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pos-cancel-field label {
  font-size: 0.8rem;
  font-weight: 800;
  color: var(--text-primary);
}

.pos-reason-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.pos-reason-chip {
  padding: 5px 11px;
  border-radius: 16px;
  font-size: 0.76rem;
  font-weight: 700;
  border: 1px solid var(--border-color);
  background: var(--subtle);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.15s;
}
.pos-reason-chip:hover {
  border-color: #ef4444;
}
.pos-reason-chip.active {
  background: #dc2626;
  color: #ffffff;
  border-color: #dc2626;
}

.pos-cancel-textarea {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-card);
  color: var(--text-primary);
  font-family: inherit;
  font-size: 0.84rem;
  outline: none;
  resize: vertical;
}

.pos-cancel-footer {
  display: flex;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid var(--border-color);
  justify-content: flex-end;
}

.pos-cancel-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 9px;
  font-size: 0.86rem;
  font-weight: 800;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}
.pos-cancel-btn.secondary {
  background: var(--subtle);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}
.pos-cancel-btn.danger {
  background: #dc2626;
  color: white;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}
.pos-cancel-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ─── Responsive ─── */
@media (max-width: 900px) {
  .pos-screen {
    flex-direction: column;
  }
  .pos-cart {
    width: 100%;
    min-width: unset;
    height: 50vh;
    border-right: none;
    border-bottom: 1px solid var(--border-color);
  }
  .pos-right {
    height: 50vh;
  }
  .pos-products-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>
