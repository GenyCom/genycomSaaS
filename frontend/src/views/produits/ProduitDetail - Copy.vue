<template>
  <div class="animate-fade-in product-detail">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/produits" class="btn btn-secondary btn-sm" style="padding:0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <h2 style="font-size:1.2rem; font-weight:600;">
          {{ isNew ? 'Nouveau Produit / Service' : 'Fiche Produit : ' + (form.reference || '...') }}
        </h2>
      </div>
      <div class="flex gap-2">
        <div class="flex items-center mr-4">
          <label class="form-label mb-0 mr-2">Statut :</label>
          <button @click="form.is_actif = !form.is_actif" :class="form.is_actif ? 'badge-success' : 'badge-danger'" class="btn-badge">
            {{ form.is_actif ? 'ACTIF' : 'INACTIF' }}
          </button>
        </div>
        <button class="btn btn-primary" @click="save" :disabled="saving">
          <span v-if="saving" class="spinner-inline"></span>
          {{ saving ? 'Enregistrement...' : 'Enregistrer la fiche' }}
        </button>
      </div>
    </div>

    <!-- Toast notification -->
    <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>

    <div class="layout-grid">
      <!-- LEFT COLUMN: IMAGE & NATURE -->
      <div class="column-sidebar">
        <div class="card image-card">
          <h3 class="form-section-title">Visuel Produit</h3>
          <div class="image-preview-zone">
            <template v-if="form.image_path">
              <img :src="form.image_path" class="img-fluid rounded" alt="Produit" />
              <button class="btn-remove-image" @click="form.image_path = null">×</button>
            </template>
            <div v-else class="image-placeholder" @click="simulateUpload">
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <span>Ajouter une image</span>
            </div>
          </div>
          <p class="text-muted text-xs mt-2 text-center">Formats: JPG, PNG. Max 2Mo.</p>
        </div>

        <div class="card mt-3">
          <h3 class="form-section-title">Nature de l'article</h3>
          <div class="nature-toggle">
            <button @click="toggleNature(false)" :class="{ 'active': !form.is_service }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
              Produit / Bien
            </button>
            <button @click="toggleNature(true)" :class="{ 'active': form.is_service }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v10"/><path d="M18.4 4.6a9 9 0 1 1-12.8 0"/></svg>
              Service
            </button>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: DETAILS -->
      <div class="column-main">
        <div class="card">
          <!-- SECTION 1: IDENTIFICATION -->
          <h3 class="form-section-title">Identification & Classification</h3>
          <div class="form-grid">
            <div class="form-group full-width">
              <label class="form-label">Désignation de l'article *</label>
              <input v-model="form.designation" @input="generateReference" type="text" class="form-input" :class="{ 'input-error': errors.designation }" placeholder="Ex: Ordinateur Dell Latitude 5420" />
              <span v-if="errors.designation" class="error-text">{{ errors.designation }}</span>
            </div>

            <div class="form-group">
              <label class="form-label">Référence Interne *</label>
              <input v-model="form.reference" type="text" class="form-input ref-input" :class="{ 'input-error': errors.reference }" />
              <span v-if="errors.reference" class="error-text">{{ errors.reference }}</span>
            </div>

            <div class="form-group">
              <label class="form-label">Code Barre (EAN/UPC)</label>
              <input v-model="form.code_barre" type="text" class="form-input" placeholder="123456789..." />
            </div>

            <div class="form-group">
              <label class="form-label">Famille / Catégorie</label>
              <select v-model="form.famille_id" class="form-input">
                <option value="">-- Sans Famille --</option>
                <option v-for="f in familles" :key="f.id" :value="f.id">{{ f.libelle }}</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Marque / Fabricant</label>
              <input v-model="form.marque" type="text" class="form-input" placeholder="Ex: Dell, Apple, Logi..." />
            </div>
          </div>

          <!-- SECTION 2: TARIFICATION & MARGE -->
          <h3 class="form-section-title">Tarification & Rentabilité</h3>
          <div class="pricing-card">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Prix Achat HT</label>
                <div class="input-suffix">
                  <input v-model="form.prix_ht_achat" @input="recalcFromAchat" type="number" step="0.01" class="form-input" />
                  <span>DH</span>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Marge Bénéficiaire (%)</label>
                <div class="input-suffix">
                  <input v-model="form.marge_pourcentage" @input="recalcFromMarge" type="number" step="0.1" class="form-input" />
                  <span>%</span>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Prix Vente HT *</label>
                <div class="input-suffix">
                  <input v-model="form.prix_ht_vente" @input="recalcFromVente" type="number" step="0.01" class="form-input highlight" />
                  <span>DH</span>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Taux TVA (%)</label>
                <select v-model="form.taux_tva" @change="recalcTTC" class="form-input">
                  <option value="20">20% (Standard)</option>
                  <option value="14">14% (Transport/Services)</option>
                  <option value="10">10%</option>
                  <option value="7">7%</option>
                  <option value="0">0% (Exonéré)</option>
                </select>
              </div>

              <div class="form-group full-width pricing-summary">
                <div class="summary-item">
                  <span>Prix de Vente TTC :</span>
                  <strong>{{ formatMoney(form.prix_ttc_vente) }} DH</strong>
                </div>
                <div class="summary-item">
                  <span>Marge brute :</span>
                  <strong :class="margeProfit >= 0 ? 'text-success' : 'text-danger'">{{ formatMoney(margeProfit) }} DH</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 3: STOCK & LOGISTIQUE (Hidden if Service) -->
          <template v-if="!form.is_service">
            <h3 class="form-section-title">Stock & Logistique</h3>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Unité de mesure</label>
                <input v-model="form.unite" class="form-input" placeholder="Ex: Unité, kg, m, Boîte..." />
              </div>
              <div class="form-group">
                <label class="form-label">Emplacement Stock</label>
                <input v-model="form.emplacement_stock" class="form-input" placeholder="Ex: Rayon A, Étagère 3..." />
              </div>

              <div class="form-group">
                <label class="form-label">Stock Minimum (Alerte)</label>
                <input v-model="form.stock_min" type="number" class="form-input border-warning" />
              </div>
              <div class="form-group">
                <label class="form-label">Délai Approvisionnement (Jours)</label>
                <input v-model="form.delai_appro" type="number" class="form-input" />
              </div>

              <div class="form-group flex items-center gap-2 pt-4">
                <input type="checkbox" v-model="form.is_lot" id="lot" />
                <label for="lot" class="mb-0 cursor-pointer">Gestion par lots ?</label>
              </div>
              <div class="form-group flex items-center gap-2 pt-4">
                <input type="checkbox" v-model="form.is_perissable" id="periss" />
                <label for="periss" class="mb-0 cursor-pointer">Produit Périssable ?</label>
              </div>

              <div class="form-group half-left">
                <label class="form-label">Poids (kg)</label>
                <input v-model="form.poids" type="number" step="0.001" class="form-input" />
              </div>
              <div class="form-group half-right">
                <label class="form-label">Dimensions (Lxlxh)</label>
                <input v-model="form.dimensions" class="form-input" placeholder="Ex: 40x20x10" />
              </div>
            </div>
          </template>

          <!-- SECTION 4: GARANTIE & DESCRIPTION -->
          <h3 class="form-section-title">Support & Description</h3>
          <div class="form-grid">
            <div class="form-group half-left">
              <label class="form-label">Garantie client (Mois)</label>
              <input v-model="form.garantie_mois" type="number" class="form-input" placeholder="0 = Pas de garantie" />
            </div>
            <div class="form-group full-width">
              <label class="form-label">Description détaillée / Notes techniques</label>
              <textarea v-model="form.detail" class="form-input" rows="4" placeholder="Caractéristiques, points forts, contenu du pack..."></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'

const props = defineProps({
  id: [String, Number],
  isNew: { type: Boolean, default: false }
})
const router = useRouter()
const route = useRoute()
const isNew = computed(() => props.isNew || route.params.id === 'new' || !route.params.id)
const saving = ref(false)

const form = ref({
  reference: '',
  designation: '',
  marque: '',
  famille_id: '',
  fournisseur_id: '',
  is_service: false,
  code_barre: '',
  prix_ht_achat: 0,
  marge_pourcentage: 20,
  prix_ht_vente: 0,
  taux_tva: '20',
  prix_ttc_vente: 0,
  unite: 'Unité',
  stock_min: 5,
  emplacement_stock: '',
  delai_appro: 0,
  poids: 0,
  dimensions: '',
  is_perissable: false,
  is_lot: false,
  garantie_mois: 0,
  detail: '',
  image_path: null,
  is_actif: true
})

const familles = ref([])
const errors = reactive({})
const toast = reactive({ show: false, message: '', type: 'success' })

const margeProfit = computed(() => {
  return (form.value.prix_ht_vente || 0) - (form.value.prix_ht_achat || 0)
})

function formatMoney(val) {
  return parseFloat(val || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function showToast(message, type = 'success') {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 4000)
}

function toggleNature(isService) {
  form.value.is_service = isService
  if (isService) {
    form.value.unite = 'Heure'
    form.value.stock_min = 0
  } else {
    form.value.unite = 'Unité'
    form.value.stock_min = 5
  }
}

// Pricing Logic
function recalcFromAchat() {
  const achat = parseFloat(form.value.prix_ht_achat) || 0
  const margePct = parseFloat(form.value.marge_pourcentage) || 0
  form.value.prix_ht_vente = +(achat * (1 + margePct / 100)).toFixed(2)
  recalcTTC()
}

function recalcFromMarge() {
  recalcFromAchat()
}

function recalcFromVente() {
  const achat = parseFloat(form.value.prix_ht_achat) || 0
  const vente = parseFloat(form.value.prix_ht_vente) || 0
  if (achat > 0) {
    form.value.marge_pourcentage = +(((vente - achat) / achat) * 100).toFixed(2)
  }
  recalcTTC()
}

function recalcTTC() {
  const venteHT = parseFloat(form.value.prix_ht_vente) || 0
  const tva = parseFloat(form.value.taux_tva) || 0
  form.value.prix_ttc_vente = +(venteHT * (1 + tva / 100)).toFixed(2)
}

// Ref Auto-gen
let refTimeout = null
function generateReference() {
  if (!isNew.value) return
  const designation = form.value.designation.trim()
  if (designation.length < 1) return

  clearTimeout(refTimeout)
  refTimeout = setTimeout(async () => {
    try {
      const { data } = await api.get('/produits-next-ref', { params: { designation } })
      if (data.reference) form.value.reference = data.reference
    } catch {
       const prefix = designation.substring(0, 3).toUpperCase()
       form.value.reference = `${prefix}_${Math.floor(Math.random()*1000)}`
    }
  }, 400)
}

function simulateUpload() {
  // Demo simulation d'image
  form.value.image_path = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=400'
  showToast('Image téléchargée (Simulation)')
}

function validate() {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.value.designation?.trim()) errors.designation = 'La désignation est obligatoire'
  if (!form.value.reference?.trim()) errors.reference = 'La référence est obligatoire'
  return Object.keys(errors).length === 0
}

async function save() {
  if (!validate()) {
    showToast('Corrigez les erreurs du formulaire', 'error')
    return
  }
  
  saving.value = true
  try {
    const payload = { ...form.value }
    payload.prix_ht_achat = parseFloat(payload.prix_ht_achat) || 0
    payload.prix_ht_vente = parseFloat(payload.prix_ht_vente) || 0
    payload.marge_pourcentage = parseFloat(payload.marge_pourcentage) || 0
    payload.taux_tva = parseFloat(payload.taux_tva) || 0
    payload.famille_id = parseInt(payload.famille_id) || null

    if (isNew.value) {
      await api.post('/produits', payload)
      showToast('Article créé avec succès !')
    } else {
      await api.put(`/produits/${route.params.id}`, payload)
      showToast('Article mis à jour avec succès !')
    }
    setTimeout(() => router.push('/produits'), 1000)
  } catch (error) {
    const msg = error.response?.data?.message || 'Erreur lors de la sauvegarde.'
    showToast(msg, 'error')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
     const { data: familleData } = await api.get('/parametrage/referentiels/familles-produit')
     familles.value = familleData || []
  } catch (e) { console.error(e) }

  if (!isNew.value) {
    try {
      const { data } = await api.get(`/produits/${route.params.id}`)
      const p = data.data || data
      form.value = { ...form.value, ...p }
      // Unify field names if needed from DB
      form.value.prix_ht_achat = p.prix_ht_achat || p.prix_achat_ht || 0
      form.value.prix_ht_vente = p.prix_ht_vente || p.prix_vente_ht || 0
      form.value.detail = p.detail || p.description || ''
    } catch (error) {
      showToast('Produit introuvable', 'error')
    }
  }
})
</script>

<style scoped>
.layout-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.5rem;
  align-items: start;
}

.image-preview-zone {
  width: 100%;
  aspect-ratio: 1/1;
  background: var(--bg-body);
  border: 2px dashed var(--border-color);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}
.image-preview-zone:hover {
  border-color: var(--accent);
  background: rgba(var(--accent-rgb), 0.05);
}
.image-placeholder {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.85rem;
}
.btn-remove-image {
  position: absolute;
  top: 5px;
  right: 5px;
  background: #ef4444;
  color: white;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
}

.nature-toggle {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.nature-toggle button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--bg-body);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  color: var(--text-muted);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}
.nature-toggle button.active {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
}

.pricing-card {
  background: rgba(var(--accent-rgb, 245, 158, 11), 0.05);
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid rgba(var(--accent-rgb, 245, 158, 11), 0.2);
}
.input-suffix {
  position: relative;
  display: flex;
  align-items: center;
}
.input-suffix input {
  padding-right: 3rem;
}
.input-suffix span {
  position: absolute;
  right: 1rem;
  color: var(--text-muted);
  font-size: 0.85rem;
  font-weight: 700;
}
.form-input.highlight {
  border-color: var(--accent);
  font-weight: 700;
  font-size: 1rem;
}

.pricing-summary {
  display: flex;
  justify-content: space-between;
  padding-top: 1rem;
  border-top: 1px dashed var(--border-color);
  margin-top: 0.5rem;
}
.summary-item {
  display: flex;
  flex-direction: column;
}
.summary-item span { font-size: 0.75rem; color: var(--text-muted); }
.summary-item strong { font-size: 1.1rem; }

.ref-input {
  font-family: 'JetBrains Mono', monospace;
  color: var(--accent);
  font-weight: 700;
  background: rgba(0,0,0,0.1);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}
.form-group.full-width { grid-column: span 2; }

.text-success { color: #10b981; }
.text-danger { color: #ef4444; }
.border-warning { border-color: #f59e0b !important; }

.btn-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
}
.badge-success { background: #10b98120; color: #10b981; border: 1px solid #10b98140; }
.badge-danger { background: #ef444420; color: #ef4444; border: 1px solid #ef444440; }

.toast-notification {
  position: fixed; top: 1rem; right: 1rem; padding: 0.85rem 1.5rem; border-radius: 8px;
  font-size: 0.875rem; font-weight: 500; z-index: 9999; animation: slideIn 0.3s ease;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.toast-notification.success { background: #10b981; color: #fff; }
.toast-notification.error { background: #ef4444; color: #fff; }

@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.spinner-inline {
  display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 0.4rem;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
