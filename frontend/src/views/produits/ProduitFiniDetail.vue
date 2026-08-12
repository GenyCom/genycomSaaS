<template>
  <div class="product-fini-detail-view">
    <Transition name="fade">
      <div v-if="loading" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Chargement des détails…</p>
      </div>
    </Transition>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le Produit Composé"
      message="Êtes-vous sûr de vouloir supprimer ce produit composé du catalogue ? Cette action est irréversible."
      confirmText="Supprimer le composé"
      @confirm="confirmDelete"
      @cancel="showConfirm = false"
    />

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
          <span class="breadcrumb-current">{{ rawData.reference }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link :to="`/produits/fini/${id}/edit`" class="btn-edit-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>Modifier</span>
        </router-link>
        <button class="btn-delete-custom" @click="showConfirm = true">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
          </svg>
          <span>Supprimer</span>
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar">
        <span>PF</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Produit Fini / Composé
        </div>
        <h1 class="hero-name">{{ rawData.designation }}</h1>
        <p class="hero-sub">Réf : <strong>{{ rawData.reference }}</strong> · Catégorie : <strong>{{ rawData.famille?.libelle || 'Non définie' }}</strong></p>
      </div>
    </div>

    <div class="kpi-strip">
      <div class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Prix Total HT</p>
          <p class="kpi-value">{{ formatMoney(rawData.prix_ht) }} <span>DH</span></p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item accent">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Stock Virtuel Estimé</p>
          <p class="kpi-value highlighted-stock">
            {{ virtualStock !== null ? virtualStock : '-' }}
            <span v-if="virtualStock !== null">unités</span>
          </p>
        </div>
      </div>
      <div class="kpi-divider"></div>
      <div class="kpi-item success">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Prix Total TTC</p>
          <p class="kpi-value highlighted-kpi">{{ formatMoney(rawData.prix_ttc) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <div class="col-main">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon composite-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <h3>Composants de la Nomenclature</h3>
          </div>

          <div class="card-body p-0">
            <div class="table-container-custom">
              <table class="saas-table">
                <thead>
                  <tr>
                    <th style="width: 20%">Référence</th>
                    <th style="width: 40%">Composant</th>
                    <th style="width: 10%" class="text-center">Type</th>
                    <th style="width: 10%" class="text-right">Quantité</th>
                    <th style="width: 10%" class="text-right">P.U HT Vente</th>
                    <th style="width: 10%" class="text-right">Total HT</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="n in rawData.nomenclature" :key="n.id" class="ligne-row">
                    <td><span class="product-ref-badge">{{ n.produit?.reference }}</span></td>
                    <td class="font-semibold">{{ n.produit?.designation }}</td>
                    <td class="text-center">
                      <span class="type-pill" :class="n.produit?.is_service ? 'service' : 'goods'">
                        {{ n.produit?.is_service ? 'SERVICE' : 'PRODUIT' }}
                      </span>
                    </td>
                    <td class="text-right font-bold">{{ formatNumber(n.quantite) }}</td>
                    <td class="text-right mono font-semibold">{{ formatMoney(n.produit?.prix_ht_vente) }} DH</td>
                    <td class="text-right font-bold mono text-accent">{{ formatMoney(n.montant_ht) }} DH</td>
                  </tr>
                  <tr v-if="!rawData.nomenclature || rawData.nomenclature.length === 0">
                    <td colspan="6" class="text-center" style="padding: 40px; font-style: italic; color: #6B7280;">
                      Aucun composant défini pour ce produit composé.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="info-card" v-if="rawData.detail">
          <div class="card-header">
            <div class="card-header-icon notes"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
            <h3>Description / Observations</h3>
          </div>
          <div class="card-body">
            <p style="font-size: 0.9rem; line-height: 1.6; margin: 0; color: #374151; white-space: pre-line;">{{ rawData.detail }}</p>
          </div>
        </section>
      </div>

      <!-- Sidebar -->
      <div class="col-side">
        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <h3>Visuel du composé</h3>
          </div>
          <div class="card-body">
            <div class="image-preview-zone">
              <img v-if="rawData.image_path" :src="getImageUrl(rawData.image_path)" alt="Produit Composé" />
              <div v-else class="image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>Aucune image</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Technical breakdown -->
        <section class="info-card" v-if="rawData.nomenclature && rawData.nomenclature.length > 0">
          <div class="card-header">
            <div class="card-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
            <h3>Répartition des coûts HT</h3>
          </div>
          <div class="card-body breakdown-body">
            <div v-for="n in rawData.nomenclature" :key="n.id" class="breakdown-row">
              <div class="breakdown-info">
                <span class="breakdown-name">{{ n.produit?.designation }}</span>
                <span class="breakdown-pct">({{ formatPercent(n) }}%)</span>
              </div>
              <div class="breakdown-bar-bg">
                <div class="breakdown-bar-fill" :style="{ width: formatPercent(n) + '%', backgroundColor: n.produit?.is_service ? '#eab308' : '#0891b2' }"></div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const route = useRoute()
const router = useRouter()
const id = route.params.id
const loading = ref(true)
const showConfirm = ref(false)
const rawData = ref({})

// Computations
const virtualStock = computed(() => {
  if (!rawData.value.nomenclature || rawData.value.nomenclature.length === 0) return null;
  let minStock = null;
  let hasPhysical = false;
  rawData.value.nomenclature.forEach(n => {
    const prod = n.produit;
    if (prod && !prod.is_service) {
      hasPhysical = true;
      const qtyNeeded = parseFloat(n.quantite) || 1;
      const currentStock = parseFloat(prod.stock_actuel) || 0;
      const virtualVal = Math.floor(currentStock / qtyNeeded);
      if (minStock === null || virtualVal < minStock) {
        minStock = virtualVal;
      }
    }
  });
  return hasPhysical ? minStock : null;
})

function formatPercent(n) {
  const totalHt = parseFloat(rawData.value.prix_ht) || 0
  if (totalHt === 0) return 0
  const rowHt = parseFloat(n.montant_ht) || 0
  return Math.round((rowHt / totalHt) * 100)
}

function getImageUrl(imagePath) {
  if (!imagePath) return null
  if (imagePath.startsWith('http')) return imagePath
  return imagePath.startsWith('/') ? imagePath : '/' + imagePath
}

function formatMoney(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatNumber(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { maximumFractionDigits: 2 })
}

async function confirmDelete() {
  showConfirm.value = false
  loading.value = true
  try {
    await api.delete(`/produits-finis/${id}`)
    toast.success("Produit composé supprimé avec succès !")
    router.push('/produits')
  } catch (err) {
    console.error(err)
    toast.error("Impossible de supprimer ce produit composé.")
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get(`/produits-finis/${id}`)
    rawData.value = data.data || data
  } catch (err) {
    console.error(err)
    toast.error("Erreur de chargement du produit composé.")
    router.push('/produits')
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.product-fini-detail-view {
  background: #F7F8FA;
  min-height: 100vh;
  padding: 24px 28px 48px;
  color: #1A1D23;
  --c-accent: #0f766e;
  --c-accent-bg: #f0fdf4;
}

/* topbar */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.back-btn {
  display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px;
  border-radius: 8px; border: 1px solid #E8EAEE; background: #fff; color: #6B7280;
  margin-right: 12px; transition: all 0.2s;
}
.back-btn:hover { background: #F3F4F6; color: #111827; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-parent { color: #6B7280; font-weight: 500; }
.breadcrumb-current { color: #1A1D23; font-weight: 700; }

.btn-edit-link {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: 8px;
  font-size: .85rem; font-weight: 600; text-decoration: none; border: none;
  box-shadow: 0 4px 12px rgba(15,118,110,0.2); transition: transform .2s;
}
.btn-edit-link:hover { transform: translateY(-1px); background: #0d5c56; }

.btn-delete-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: #fee2e2; color: #b91c1c; border-radius: 8px;
  font-size: .85rem; font-weight: 600; border: none; cursor: pointer;
  margin-left: 10px; transition: transform .2s;
}
.btn-delete-custom:hover { transform: translateY(-1px); background: #fecaca; }

/* hero */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: 16px; border: 1px solid #E8EAEE;
  margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.hero-avatar {
  width: 52px; height: 52px; border-radius: 12px;
  background: linear-gradient(135deg, #0d9488, #0f766e);
  display: flex; align-items: center; justify-content: center; color: #fff;
  font-weight: 800; font-size: 1.1rem;
}
.hero-type-badge {
  display: flex; align-items: center; gap: 5px; font-size: .65rem; font-weight: 700;
  text-transform: uppercase; color: var(--c-accent); margin-bottom: 4px;
}
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-accent); border-radius: 50%; }
.hero-name { font-size: 1.4rem; font-weight: 800; margin: 0; }
.hero-sub { font-size: .82rem; color: #6B7280; margin: 4px 0 0; }

/* kpi strip */
.kpi-strip {
  display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; align-items: center;
  background: #fff; border-radius: 16px; border: 1px solid #E8EAEE;
  padding: 16px 32px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.kpi-item { display: flex; align-items: center; gap: 16px; }
.kpi-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}
.kpi-item.neutral .kpi-icon { background: #F3F4F6; color: #4B5563; }
.kpi-item.accent .kpi-icon { background: #ECFEFF; color: #0891B2; }
.kpi-item.success .kpi-icon { background: #F0FDF4; color: #16A34A; }
.kpi-body { display: flex; flex-direction: column; }
.kpi-label { font-size: .75rem; color: #6B7280; margin: 0; font-weight: 600; }
.kpi-value { font-size: 1.25rem; font-weight: 800; margin: 2px 0 0; display: flex; align-items: baseline; gap: 4px; }
.kpi-value span { font-size: .78rem; font-weight: 600; opacity: .7; }
.highlighted-kpi { color: #16A34A; }
.highlighted-stock { color: #0891B2; }
.kpi-divider { width: 1px; height: 36px; background: #E8EAEE; }

/* grid */
.content-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }
@media (max-width: 1024px) {
  .content-grid { grid-template-columns: 1fr; }
}

.info-card {
  background: #fff; border: 1px solid #E8EAEE; border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 24px; overflow: hidden;
}
.card-header {
  display: flex; align-items: center; gap: 10px; padding: 16px 24px;
  border-bottom: 1px solid #E8EAEE;
}
.card-header-icon {
  width: 28px; height: 28px; border-radius: 8px; background: var(--c-accent-bg);
  color: var(--c-accent); display: flex; align-items: center; justify-content: center;
}
.card-header-icon.composite-icon {
  background: #f0fdf4; color: #0d9488;
}
.card-header-icon.notes {
  background: #fdf2f8; color: #db2777;
}
.card-header h3 { font-size: 0.92rem; font-weight: 800; margin: 0; color: #1A1D23; }
.card-body { padding: 24px; }

/* table components */
.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th {
  background: #F8FAFC; padding: 12px 20px; font-size: 0.72rem; font-weight: 800;
  text-transform: uppercase; color: #4B5563; border-bottom: 2px solid #E8EAEE; text-align: left;
}
.saas-table td { padding: 12px 20px; border-bottom: 1px solid #E8EAEE; vertical-align: middle; }
.ligne-row:hover { background: #F9FAFB; }

.product-ref-badge {
  font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 700;
  color: #475569; background-color: #F1F5F9; border: 1px solid #E2E8F0;
  padding: 3px 6px; border-radius: 4px; display: inline-block;
}

.type-pill {
  display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: .65rem;
  font-weight: 800; text-transform: uppercase;
}
.type-pill.service { background: #fef9c3; color: #a16207; }
.type-pill.goods { background: #e0f2fe; color: #0369a1; }

.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.mono { font-family: 'JetBrains Mono', monospace; }
.text-accent { color: var(--c-accent); }

/* image previews */
.image-preview-zone {
  width: 100%; height: 180px; border-radius: 12px; border: 1px solid #E8EAEE;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  position: relative; overflow: hidden; background: #F9FAFB;
}
.image-preview-zone img { width: 100%; height: 100%; object-fit: cover; }
.image-placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; color: #6B7280; font-size: .8rem; font-weight: 600; }

/* breakdown */
.breakdown-body { display: flex; flex-direction: column; gap: 14px; }
.breakdown-row { display: flex; flex-direction: column; gap: 4px; }
.breakdown-info { display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; }
.breakdown-name { color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
.breakdown-pct { color: var(--c-accent); font-family: 'JetBrains Mono', monospace; font-weight: 700; }
.breakdown-bar-bg { width: 100%; height: 6px; background-color: #F3F4F6; border-radius: 3px; overflow: hidden; }
.breakdown-bar-fill { height: 100%; border-radius: 3px; }

/* loader overlay */
.loading-overlay {
  position: fixed; inset: 0; background: rgba(255,255,255,0.7);
  backdrop-filter: blur(4px); display: flex; flex-direction: column;
  align-items: center; justify-content: center; z-index: 1000;
}
.loader-ring {
  display: inline-block; position: relative; width: 64px; height: 64px;
}
.loader-ring div {
  box-sizing: border-box; display: block; position: absolute;
  width: 48px; height: 48px; margin: 8px; border: 4px solid var(--c-accent);
  border-radius: 50%; animation: loader-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: var(--c-accent) transparent transparent transparent;
}
.loader-ring div:nth-child(1) { animation-delay: -0.45s; }
.loader-ring div:nth-child(2) { animation-delay: -0.3s; }
.loader-ring div:nth-child(3) { animation-delay: -0.15s; }
@keyframes loader-ring {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.loading-label { margin-top: 16px; font-size: 0.88rem; font-weight: 700; color: var(--c-accent); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.flex-align-center { display: flex; align-items: center; }
.p-0 { padding: 0 !important; }
</style>
