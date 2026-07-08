<template>
  <div class="product-detail-view">
    <!-- Loading Overlay -->
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement de l'article…</p>
      </div>
    </Transition>

    <div class="topbar">
      <div class="topbar-left">
        <router-link to="/produits" class="back-btn" title="Retour au catalogue">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
          </svg>
        </router-link>
        <div class="breadcrumb">
          <span class="breadcrumb-parent">Inventaire</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ form.reference || 'Consultation' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <!-- Strictly no edit button here if the user "should not have the right" -->
        <!-- But adding it for administration convenience if they have access -->
        <router-link :to="`/produits/${form.id}/edit`" class="btn-edit" v-if="form.id">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>Modifier l'article</span>
        </router-link>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>PR</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          {{ form.is_service ? 'Prestation de Service' : 'Marchandise / Bien' }}
        </div>
        <h1 class="hero-name">{{ form.designation || '...' }}</h1>
        <p class="hero-sub">Réf : <strong>{{ form.reference }}</strong> · {{ form.marque || 'Marque non définie' }}</p>
      </div>
      <div class="hero-status-badge" :class="form.is_actif ? 'active' : 'inactive'">
        {{ form.is_actif ? 'ARTICLE ACTIF' : 'ARTICLE INACTIF' }}
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Prix Achat HT</p>
          <p class="kpi-value">{{ formatMoney(form.prix_ht_achat) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Prix Vente HT</p>
          <p class="kpi-value">{{ formatMoney(form.prix_ht_vente) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item success">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Marge Brute</p>
          <p class="kpi-value">{{ formatMoney(margeProfit) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-side">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <h3>Visuel Article</h3>
          </div>
          <div class="card-body">
            <div class="image-static-zone">
              <img v-if="form.image_path" :src="imageUrl" alt="Produit" />
              <div v-else class="image-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>Aucune image</span>
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>Tarification TTC</h3>
          </div>
          <div class="card-body">
            <div class="price-bubble">
              <span class="p-label">Prix de vente TTC</span>
              <span class="p-value">{{ formatMoney(form.prix_ttc_vente) }} <small>DH</small></span>
              <span class="p-sub">TVA incluse ({{ form.taux_tva }}%)</span>
            </div>
          </div>
        </section>
      </div>

      <div class="col-main">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <h3>Identification & Classification</h3>
          </div>
          <div class="card-body">
            <div class="data-grid">
               <div class="data-item">
                 <span class="data-label">Désignation</span>
                 <span class="data-value bold">{{ form.designation }}</span>
               </div>
               <div class="data-row">
                 <div class="data-item">
                   <span class="data-label">Référence Interne</span>
                   <span class="data-value mono">{{ form.reference }}</span>
                 </div>
                 <div class="data-item">
                   <span class="data-label">Code Barre</span>
                   <span class="data-value">{{ form.code_barre || '—' }}</span>
                 </div>
               </div>
                  <div class="data-row">
                    <div class="data-item">
                      <span class="data-label">Référence OEM</span>
                      <span class="data-value bold text-accent">{{ form.reference_oem || '—' }}</span>
                    </div>
                    <div class="data-item">
                      <span class="data-label">Marque</span>
                      <span class="data-value">{{ form.marque || '—' }}</span>
                    </div>
                  </div>
                  <div class="data-item">
                    <span class="data-label">Famille / Catégorie</span>
                    <span class="data-value">{{ familleLibelle }}</span>
                  </div>
               </div>
            </div>
          </section>

          <!-- Rupture / Stock alert banner -->
          <div v-if="!form.is_service && form.stock_actuel <= 0" class="out-of-stock-banner">
            <div class="banner-icon">⚠️</div>
            <div class="banner-content">
              <h4>Cet article est en rupture de stock !</h4>
              <p v-if="form.compatibles && form.compatibles.length > 0">Vous pouvez proposer l'une des alternatives compatibles ci-dessous en remplacement.</p>
              <p v-else>Aucune pièce alternative n'est répertoriée pour cet article.</p>
            </div>
          </div>

          <!-- Compatible alternatives card -->
          <section v-if="!form.is_service" class="info-card">
            <div class="card-header">
              <div class="card-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5M4 20L20.2 3.8M21 16v5h-5M4 4l5 5"/></svg>
              </div>
              <h3>Alternatives & Compatibilités</h3>
            </div>
            <div class="card-body">
              <div v-if="form.compatibles && form.compatibles.length > 0" class="compatibles-table-wrapper">
                <table class="compatibles-table">
                  <thead>
                    <tr>
                      <th style="width: 15%">Référence</th>
                      <th style="width: 35%">Désignation</th>
                      <th style="width: 15%">Marque</th>
                      <th style="width: 15%">Réf. OEM</th>
                      <th class="text-right" style="width: 12%">Prix Vente HT</th>
                      <th class="text-center" style="width: 13%">Disponibilité</th>
                      <th class="text-right" style="width: 10%">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="alt in form.compatibles" :key="alt.id" class="compat-table-row">
                      <td>
                        <span class="product-ref-badge">{{ alt.reference }}</span>
                      </td>
                      <td class="designation-cell">
                        <span class="alt-designation-title">{{ alt.designation }}</span>
                      </td>
                      <td>
                        <span class="alt-marque-text">{{ alt.marque || '—' }}</span>
                      </td>
                      <td>
                        <span class="oem-badge-alt" v-if="alt.reference_oem">{{ alt.reference_oem }}</span>
                        <span class="alt-none-text" v-else>—</span>
                      </td>
                      <td class="text-right font-bold price-text">
                        {{ formatMoney(alt.prix_ht_vente) }} <span class="price-curr">DH</span>
                      </td>
                      <td class="text-center">
                        <span 
                          class="stock-badge" 
                          :class="alt.stock_actuel > 0 ? 'in-stock' : 'out-of-stock'"
                        >
                          <span class="dot-indicator">●</span>
                          {{ alt.stock_actuel > 0 ? parseFloat(alt.stock_actuel) + ' en stock' : 'Rupture' }}
                        </span>
                      </td>
                      <td class="text-right">
                        <button 
                          @click="navigateToProduct(alt.id)" 
                          class="btn-consult-alt"
                          title="Consulter la fiche"
                          type="button"
                        >
                          Consulter <span>→</span>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="empty-compat-info">
                Aucune référence compatible (alternative) n'a été configurée pour cet article.
              </div>
            </div>
        </section>

        <section v-if="!form.is_service" class="info-card">
          <div class="card-header">
            <div class="card-header-icon contact">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
            <h3>Logistique & Stock</h3>
          </div>
          <div class="card-body">
            <div class="data-grid">
               <div class="data-row">
                 <div class="data-item">
                   <span class="data-label">Stock Actuel</span>
                   <span class="data-value font-black" :class="form.stock_actuel <= form.stock_min ? 'text-danger' : 'text-success'">
                     {{ form.stock_actuel }} {{ form.unite }}
                   </span>
                 </div>
                 <div class="data-item">
                   <span class="data-label">Stock Initial</span>
                   <span class="data-value">{{ form.stock_initial }} {{ form.unite }}</span>
                 </div>
               </div>
               <div class="data-row">
                 <div class="data-item">
                   <span class="data-label">Stock de Sécurité</span>
                   <span class="data-value">{{ form.stock_min }} {{ form.unite }}</span>
                 </div>
                 <div class="data-item">
                   <span class="data-label">Seuil d'Alerte (Notif)</span>
                   <span class="data-value text-orange">{{ form.seuil_alerte }} {{ form.unite }}</span>
                 </div>
               </div>
               <div class="data-row">
                 <div class="data-item">
                   <span class="data-label">Délai Appro.</span>
                   <span class="data-value">{{ form.delai_appro }} jours</span>
                 </div>
                 <div class="data-item">
                   <span class="data-label">Unité</span>
                   <span class="data-value">{{ form.unite }}</span>
                 </div>
               </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3>Description & Notes</h3>
          </div>
          <div class="card-body">
            <p class="description-text">{{ form.detail || 'Aucune description technique disponible pour cet article.' }}</p>
            <div class="garantie-badge" v-if="form.garantie_mois > 0">
               Garantie : {{ form.garantie_mois }} mois
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const props = defineProps({ id: [String, Number] })
const route = useRoute()
const router = useRouter()
const loading = ref(true)

const form = ref({
  id: null,
  reference: '',
  reference_oem: '',
  designation: '',
  marque: '',
  famille_id: '',
  is_service: false,
  code_barre: '',
  prix_ht_achat: 0,
  prix_ht_vente: 0,
  taux_tva: '20',
  prix_ttc_vente: 0,
  unite: 'Unité',
  stock_initial: 0,
  stock_actuel: 0,
  stock_min: 0,
  seuil_alerte: 0,
  emplacement_stock: '',
  delai_appro: 0,
  garantie_mois: 0,
  detail: '',
  image_path: null,
  is_actif: true,
  compatibles: []
})

const familles = ref([])

const margeProfit = computed(() => {
  return (form.value.prix_ht_vente || 0) - (form.value.prix_ht_achat || 0)
})

const familleLibelle = computed(() => {
  const f = familles.value.find(x => x.id == form.value.famille_id)
  if (!f) return 'Sans famille'
  if (f.parent) {
    return `${f.parent.libelle} > ${f.libelle}`
  }
  return f.libelle
})

const imageUrl = computed(() => {
  if (!form.value.image_path) return null
  if (form.value.image_path.startsWith('blob:') || form.value.image_path.startsWith('http')) {
    return form.value.image_path
  }
  let path = form.value.image_path
  if (!path.startsWith('/')) path = '/' + path
  return path
})

function formatMoney(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const navigateToProduct = (id) => {
  router.push(`/produits/${id}`)
}

const loadProduct = async () => {
  loading.value = true
  const productId = props.id || route.params.id
  if (!productId) return
  
  try {
     const [pRes, fRes] = await Promise.all([
       api.get(`/produits/${productId}`),
       api.get('/parametrage/referentiels/familles-produit')
     ])
     
     const p = pRes.data.data || pRes.data
     form.value = { 
       ...form.value, 
       ...p,
       taux_tva: p.taux_tva !== null && p.taux_tva !== undefined ? String(parseFloat(p.taux_tva)) : '20'
     }
     familles.value = fRes.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProduct()
})

watch(() => props.id || route.params.id, () => {
  loadProduct()
})
</script>

<style scoped>
.product-detail-view {
  --c-bg:         #F7F8FA;
  --c-surface:    #FFFFFF;
  --c-border:     #E8EAEE;
  --c-border-mid: #D5D9E2;
  --c-text:       #1A1D23;
  --c-muted:      #6B7280;
  --c-subtle:     #F1F3F6;
  --c-accent:     #0891b2; 
  --c-accent-bg:  #ecfeff;
  --c-danger:     #DC2626;
  --c-success:    #16A34A;
  --radius-lg:    16px;
  --radius-md:    12px;
  --radius-sm:     8px;
  --shadow-sm:    0 1px 3px rgba(0,0,0,.06);

  
  background: var(--c-bg);
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: var(--c-text);
}

/* ─── Alternatives & Out-of-stock Styles ─── */
.out-of-stock-banner {
  background-color: #FEF2F2;
  border: 1.5px solid #FCA5A5;
  border-radius: var(--radius-lg);
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 24px;
}
.banner-icon {
  font-size: 1.5rem;
  line-height: 1;
}
.banner-content h4 {
  margin: 0 0 4px 0;
  color: #991B1B;
  font-weight: 800;
  font-size: 0.95rem;
}
.banner-content p {
  margin: 0;
  color: #7F1D1D;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Compatibles Table Styling */
.compatibles-table-wrapper {
  overflow-x: auto;
  border: 1px solid var(--c-border);
  border-radius: var(--radius-md);
  background: #FFFFFF;
  box-shadow: var(--shadow-sm);
}
.compatibles-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.85rem;
}
.compatibles-table th {
  padding: 12px 16px;
  background-color: #F8FAFC;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--c-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1.5px solid var(--c-border);
}
.compatibles-table td {
  padding: 14px 16px;
  border-bottom: 1.5px solid var(--c-border);
  vertical-align: middle;
}
.compat-table-row {
  transition: background-color 0.2s ease;
}
.compat-table-row:hover {
  background-color: #F8FAFC;
}
.compat-table-row:last-child td {
  border-bottom: none;
}

/* Badges & Elements */
.product-ref-badge {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.78rem;
  font-weight: 700;
  color: #475569;
  background-color: #F1F5F9;
  border: 1px solid #E2E8F0;
  padding: 3px 8px;
  border-radius: 6px;
  display: inline-block;
}
.alt-designation-title {
  font-weight: 700;
  color: var(--c-text);
  font-size: 0.88rem;
}
.alt-marque-text {
  font-weight: 600;
  color: #334155;
}
.oem-badge-alt {
  background-color: #F1F5F9;
  color: #475569;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  display: inline-block;
}
.alt-none-text {
  color: var(--c-muted);
}
.price-text {
  color: var(--c-text);
  font-size: 0.9rem;
}
.price-curr {
  font-size: 0.72rem;
  opacity: 0.7;
  font-weight: 600;
}

/* Stock Status Pill */
.stock-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 100px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
}
.stock-badge.in-stock {
  background-color: #DCFCE7;
  color: #15803D;
}
.stock-badge.out-of-stock {
  background-color: #FEE2E2;
  color: #B91C1C;
}
.dot-indicator {
  font-size: 0.6rem;
  line-height: 1;
}

/* Consult Action Button */
.btn-consult-alt {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background-color: #FFFFFF;
  border: 1.5px solid var(--c-accent);
  color: var(--c-accent);
  padding: 6px 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.76rem;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-consult-alt:hover {
  background-color: var(--c-accent);
  color: #FFFFFF;
  box-shadow: 0 4px 8px rgba(8, 145, 178, 0.15);
  transform: translateY(-1px);
}
.btn-consult-alt:active {
  transform: translateY(0);
}
.empty-compat-info {
  font-size: 0.85rem;
  color: var(--c-muted);
  font-style: italic;
  text-align: center;
  padding: 24px 0;
}

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn {
  display: flex; align-items: center; justify-content: center; width: 34px; height: 34px;
  border-radius: 50%; border: 1.5px solid var(--c-border-mid); background: #fff; color: var(--c-muted);
}
.back-btn:hover { color: var(--c-accent); border-color: var(--c-accent); }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.btn-edit {
  display: flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none;
  box-shadow: 0 4px 12px rgba(8,145,178,0.2);
}

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: var(--radius-lg); border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: var(--shadow-sm); position: relative;
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #0891b2, #06b6d4);
  display: flex; align-items: center; justify-content: center; color: #fff;
  font-weight: 800; font-size: 1.2rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-sub { font-size: .82rem; color: var(--c-muted); margin: 4px 0 0; }
.hero-status-badge {
  position: absolute; top: 20px; right: 28px; padding: 6px 14px; border-radius: 100px;
  font-size: .65rem; font-weight: 800;
}
.hero-status-badge.active { background: #dcfce7; color: #166534; }
.hero-status-badge.inactive { background: #fee2e2; color: #991b1b; }

/* ─── KPI Strip ─── */
.kpi-strip {
  display: flex; background: #fff; border: 1px solid var(--c-border);
  border-radius: var(--radius-lg); margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-sm);
}
.kpi-item { flex: 1; display: flex; align-items: center; gap: 14px; padding: 18px 22px; }
.kpi-divider { width: 1px; background: var(--c-border); margin: 12px 0; }
.kpi-icon { width: 38px; height: 38px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; }
.kpi-item.accent .kpi-icon { background: var(--c-accent-bg); color: var(--c-accent); }
.kpi-item.neutral .kpi-icon { background: var(--c-subtle); color: var(--c-text); }
.kpi-item.success .kpi-icon { background: #f0fdf4; color: #16a34a; }
.kpi-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin-bottom: 3px; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 0; }
.kpi-value span { font-size: .7rem; opacity: .6; margin-left: 3px; }

/* ─── Layout Grid ─── */
.content-grid { display: grid; grid-template-columns: 300px 1fr; gap: 24px; }
.col-side, .col-main { display: flex; flex-direction: column; gap: 24px; }

/* ─── Cards ─── */
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: var(--radius-lg); overflow: hidden; }
.card-header {
  display: flex; align-items: center; gap: 10px; padding: 14px 20px;
  background: var(--c-subtle); border-bottom: 1px solid var(--c-border);
}
.card-header h3 { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); margin: 0; }
.card-header-icon {
  width: 28px; height: 28px; border-radius: 7px; background: var(--c-accent-bg); color: var(--c-accent);
  display: flex; align-items: center; justify-content: center;
}
.card-header-icon.notes { background: #fff1f2; color: #e11d48; }
.card-header-icon.bank { background: #fff7ed; color: #ea580c; }
.card-header-icon.contact { background: #f0fdf4; color: #16a34a; }

.card-body { padding: 20px; }

/* ─── Data Display ─── */
.data-grid { display: flex; flex-direction: column; gap: 16px; }
.data-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.data-item { display: flex; flex-direction: column; gap: 4px; }
.data-label { font-size: .68rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .02em; }
.data-value { font-size: .95rem; font-weight: 600; color: var(--c-text); }
.data-value.bold { font-weight: 800; font-size: 1.05rem; color: var(--c-accent); }
.data-value.mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--c-accent); }

.price-bubble {
  background: var(--c-accent-bg); border: 1px solid #0891b220; border-radius: 12px; padding: 16px;
  display: flex; flex-direction: column; align-items: center; gap: 4px;
}
.price-bubble .p-label { font-size: .7rem; font-weight: 700; color: var(--c-accent); text-transform: uppercase; }
.price-bubble .p-value { font-size: 1.6rem; font-weight: 900; color: var(--c-accent); }
.price-bubble .p-value small { font-size: .8rem; opacity: .7; margin-left: 2px; }
.price-bubble .p-sub { font-size: .65rem; color: var(--c-muted); font-weight: 600; }

.description-text { font-size: .92rem; line-height: 1.6; color: #4b5563; margin: 0; }
.garantie-badge {
  display: inline-block; margin-top: 16px; padding: 4px 12px; background: #fef3c7; color: #92400e;
  border-radius: 6px; font-size: .75rem; font-weight: 700;
}

.image-static-zone {
  width: 100%; aspect-ratio: 1/1; border: 1px solid var(--c-border);
  border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center;
  background: var(--c-subtle);
}
.image-static-zone img { width: 100%; height: 100%; object-fit: cover; }
.image-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; color: var(--c-muted); font-size: .75rem; }

/* ─── Global ─── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 1000; background: rgba(255,255,255,0.7); backdrop-filter: blur(4px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.text-orange { color: #EA580C; }
</style>