<template>
  <div class="product-detail-view">
    <Transition name="fade">
      <div v-if="saving" class="loading-overlay">
        <div class="loader-ring">
          <div></div><div></div><div></div><div></div>
        </div>
        <p class="loading-label">Enregistrement de l'article…</p>
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
          <span class="breadcrumb-current">{{ isNew ? 'Nouvel Article' : form.reference }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <div class="status-toggle">
          <span class="status-label">Statut :</span>
          <button @click="form.is_actif = !form.is_actif" :class="form.is_actif ? 'active' : 'inactive'">
            {{ form.is_actif ? 'ACTIF' : 'INACTIF' }}
          </button>
        </div>
        <button class="btn-save" @click="save" :disabled="saving">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          <span>Enregistrer l'article</span>
        </button>
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
        <h1 class="hero-name">{{ form.designation || 'Nouvel Article' }}</h1>
        <p class="hero-sub" v-if="!isNew">Réf : <strong>{{ form.reference }}</strong> · {{ form.marque || 'Marque non définie' }}</p>
      </div>
    </div>

    <div class="kpi-strip">
      <div v-if="!form.is_service" class="kpi-item neutral">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Prix Achat HT</p>
          <p class="kpi-value">{{ formatMoney(form.prix_ht_achat) }} <span>DH</span></p>
        </div>
      </div>
      <div v-if="!form.is_service" class="kpi-divider"></div>
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
      <div v-if="!form.is_service" class="kpi-item success">
        <div class="kpi-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="kpi-body">
          <p class="kpi-label">Marge Brute</p>
          <p class="kpi-value">{{ formatMoney(margeProfit) }} <span>DH</span></p>
        </div>
      </div>
    </div>

    <Transition name="slide-fade">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>
    </Transition>

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
            <div class="image-preview-zone" @click="triggerUpload">
              <input type="file" ref="fileInput" @change="handleImageUpload" accept="image/*" style="display: none;" />
              <template v-if="form.image_path">
                <img :src="imageUrl" alt="Produit" />
                <button class="btn-remove-image" @click.stop="removeImage">×</button>
              </template>
              <div v-else class="image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span>Ajouter une image</span>
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <h3>Nature</h3>
          </div>
          <div class="card-body">
            <div class="nature-pills">
              <button @click="toggleNature(false)" :class="{ active: !form.is_service }">Produit</button>
              <button @click="toggleNature(true)" :class="{ active: form.is_service }">Service</button>
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
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>{{ form.is_service ? 'Libellé de la prestation *' : 'Désignation de l\'article *' }}</label>
              <input v-model="form.designation" @input="generateReference" type="text" class="input-lg" :class="{ 'input-error': errors.designation }" :placeholder="form.is_service ? '' : 'Ex: Ordinateur Dell Latitude 5420'" />
              <span v-if="errors.designation" class="error-msg">{{ errors.designation }}</span>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Référence Interne *</label>
                <input v-model="form.reference" type="text" class="mono" :class="{ 'input-error': errors.reference }" />
              </div>
              <div class="form-group-custom">
                <label>Code Barre (EAN/UPC)</label>
                <input v-model="form.code_barre" type="text" placeholder="Gencode..." />
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Famille / Catégorie</label>
                <select v-model="form.famille_id">
                  <option value="">-- Sans Famille --</option>
                  <option v-for="f in familles" :key="f.id" :value="f.id">{{ f.libelle }}</option>
                </select>
              </div>
              <div class="form-group-custom">
                <label>Marque / Fabricant</label>
                <input v-model="form.marque" type="text" placeholder="Ex: Dell, HP, Apple..." />
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon bank">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>Tarification & Rentabilité</h3>
          </div>
          <div class="card-body edit-form highlight-zone">
            <div v-if="!form.is_service" class="form-row-custom">
              <div class="form-group-custom">
                <label>Prix Achat HT (DH)</label>
                <input v-model="form.prix_ht_achat" @input="recalcFromAchat" type="number" step="0.01" />
              </div>
              <div class="form-group-custom">
                <label>Marge Bénéficiaire (%)</label>
                <input v-model="form.marge_pourcentage" @input="recalcFromMarge" type="number" step="0.1" />
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Prix Vente HT (DH) *</label>
                <input v-model="form.prix_ht_vente" @input="recalcFromVente" type="number" step="0.01" class="money-input" />
              </div>
              <div class="form-group-custom">
                <label>Taux TVA (%)</label>
                <select v-model="form.taux_tva" @change="recalcTTC">
                  <option value="20">20% (Standard)</option>
                  <option value="14">14%</option>
                  <option value="10">10%</option>
                  <option value="7">7%</option>
                  <option value="0">0%</option>
                </select>
              </div>
            </div>

            <div class="field-separator"></div>
            <div class="pricing-footer">
              <div class="footer-item">
                <span class="label">Total TTC à la vente</span>
                <span class="value">{{ formatMoney(form.prix_ttc_vente) }} DH</span>
              </div>
              <div v-if="!form.is_service" class="footer-item">
                <span class="label">Marge brute estimée</span>
                <span class="value" :class="margeProfit >= 0 ? 'text-success' : 'text-danger'">{{ formatMoney(margeProfit) }} DH</span>
              </div>
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
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Unité de mesure</label>
                <input v-model="form.unite" placeholder="Ex: Unité, kg, m..." />
              </div>
              <div class="form-group-custom">
                <label>Emplacement Stock</label>
                <input v-model="form.emplacement_stock" placeholder="Ex: Rayon A, Étagère 3..." />
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Stock de Sécurité (Min)</label>
                <input v-model="form.stock_min" type="number" />
              </div>
              <div class="form-group-custom">
                <label>Seuil d'Alerte (Notif.)</label>
                <input v-model="form.seuil_alerte" type="number" class="border-warn" />
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Délai Appro. (Jours)</label>
                <input v-model="form.delai_appro" type="number" />
              </div>
            </div>
          </div>
        </section>

        <section class="info-card">
          <div class="card-header">
            <div class="card-header-icon notes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3>Détails Techniques & Support</h3>
          </div>
          <div class="card-body edit-form">
            <div class="form-group-custom">
              <label>Garantie client (Mois)</label>
              <input v-model="form.garantie_mois" type="number" style="max-width: 200px" />
            </div>
            <div class="form-group-custom">
              <label>Description & Notes</label>
              <textarea v-model="form.detail" rows="4" placeholder="Contenu du pack, caractéristiques techniques..."></textarea>
            </div>
          </div>
        </section>
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
  seuil_alerte: 10,
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
const fileInput = ref(null)
const imageFile = ref(null)

const margeProfit = computed(() => {
  return (form.value.prix_ht_vente || 0) - (form.value.prix_ht_achat || 0)
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
    form.value.prix_ht_achat = 0
    form.value.marge_pourcentage = 0
  } else {
    form.value.unite = 'Unité'
    form.value.stock_min = 5
    form.value.marge_pourcentage = 20
  }
  recalcTTC()
}

function recalcFromAchat() {
  const achat = parseFloat(form.value.prix_ht_achat) || 0
  const margePct = parseFloat(form.value.marge_pourcentage) || 0
  form.value.prix_ht_vente = +(achat * (1 + margePct / 100)).toFixed(2)
  recalcTTC()
}

function recalcFromMarge() { recalcFromAchat() }

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

function triggerUpload() {
  fileInput.value.click()
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    showToast("L'image est trop volumineuse (max 5 Mo)", 'error')
    return
  }
  imageFile.value = file
  form.value.image_path = URL.createObjectURL(file)
}

function removeImage() {
  form.value.image_path = null
  imageFile.value = null
  if (fileInput.value) fileInput.value.value = ''
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
    const payload = { 
      ...form.value,
      prix_ht_achat: parseFloat(form.value.prix_ht_achat) || 0,
      prix_ht_vente: parseFloat(form.value.prix_ht_vente) || 0,
      marge_pourcentage: parseFloat(form.value.marge_pourcentage) || 0,
      taux_tva: parseFloat(form.value.taux_tva) || 0,
      famille_id: parseInt(form.value.famille_id) || null
    }

    const formData = new FormData()
    Object.entries(payload).forEach(([k, v]) => {
      if (k === 'image_path' && imageFile.value) return // Don't send blob URL
      if (v !== null && v !== undefined) {
        formData.append(k, typeof v === 'boolean' ? (v ? 1 : 0) : v)
      }
    })

    if (imageFile.value) {
      formData.append('image_upload', imageFile.value)
    }

    if (isNew.value) {
      await api.post('/produits', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      showToast('Article créé avec succès !')
    } else {
      formData.append('_method', 'PUT')
      await api.post(`/produits/${route.params.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      showToast('Article mis à jour !')
    }
    setTimeout(() => router.push('/produits'), 1000)
  } catch (error) {
    showToast(error.response?.data?.message || 'Erreur technique', 'error')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
     const { data } = await api.get('/parametrage/referentiels/familles-produit')
     familles.value = data || []
  } catch (e) { console.error(e) }

  if (!isNew.value) {
    try {
      const { data } = await api.get(`/produits/${route.params.id}`)
      const p = data.data || data
      form.value = { 
        ...form.value, 
        ...p,
        taux_tva: p.taux_tva !== null && p.taux_tva !== undefined ? String(parseFloat(p.taux_tva)) : '20',
        prix_ht_achat: p.prix_ht_achat || p.prix_achat_ht || 0,
        prix_ht_vente: p.prix_ht_vente || p.prix_vente_ht || 0,
        detail: p.detail || p.description || ''
      }
    } catch (error) { showToast('Produit introuvable', 'error') }
  }
})
</script>

<style scoped>
/* ─── Design Tokens (Cyan Theme) ─── */
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

/* ─── Topbar ─── */
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.topbar-left { display: flex; align-items: center; gap: 12px; }
.back-btn {
  display: flex; align-items: center; justify-content: center; width: 34px; height: 34px;
  border-radius: 50%; border: 1.5px solid var(--c-border-mid); background: #fff;
}
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .82rem; }
.breadcrumb-parent { color: var(--c-muted); font-weight: 500; }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }

.topbar-actions { display: flex; align-items: center; gap: 20px; }
.status-toggle { display: flex; align-items: center; gap: 8px; }
.status-label { font-size: .75rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.status-toggle button {
  padding: 5px 12px; border-radius: 100px; font-size: .7rem; font-weight: 800; border: 1px solid transparent; cursor: pointer;
}
.status-toggle button.active { background: #dcfce7; color: #166534; border-color: #16653440; }
.status-toggle button.inactive { background: #fee2e2; color: #991b1b; border-color: #991b1b40; }
.btn-save {
  display: flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-accent); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; border: none; cursor: pointer;
  box-shadow: 0 4px 12px rgba(8,145,178,0.2);
}

.btn-edit-mode {
  display: flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: var(--c-success); color: #fff; border-radius: var(--radius-sm);
  font-size: .85rem; font-weight: 600; text-decoration: none;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}

/* ─── Hero Header ─── */
.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 20px 28px; border-radius: var(--radius-lg); border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: var(--shadow-sm);
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

/* ─── Form UI ─── */
.edit-form { display: flex; flex-direction: column; gap: 20px; }
.form-group-custom { display: flex; flex-direction: column; gap: 8px; }
.form-group-custom label { font-size: .75rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.form-row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

input, select, textarea {
  padding: 12px 14px; border: 1.5px solid var(--c-border-mid); border-radius: var(--radius-sm);
  font-size: .92rem; transition: all .2s; background: #fff;
}
input:focus { border-color: var(--c-accent); outline: none; box-shadow: 0 0 0 3px rgba(8,145,178,0.08); }
input.input-lg { font-size: 1.1rem; font-weight: 700; color: var(--c-accent); }
input.mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--c-accent); }
input.money-input { font-weight: 800; font-size: 1.1rem; border-color: var(--c-accent); color: var(--c-accent); }

/* ─── Widgets ─── */
.image-preview-zone {
  width: 100%; aspect-ratio: 1/1; border: 2px dashed var(--c-border-mid);
  border-radius: 12px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; overflow: hidden; position: relative;
}
.image-preview-zone img { width: 100%; height: 100%; object-fit: cover; }
.image-placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; color: var(--c-muted); font-size: .8rem; }
.btn-remove-image {
  position: absolute; top: 8px; right: 8px; width: 24px; height: 24px;
  background: var(--c-danger); color: #fff; border: none; border-radius: 50%; cursor: pointer;
}

.nature-pills { display: flex; flex-direction: column; gap: 8px; }
.nature-pills button {
  padding: 12px; border: 1px solid var(--c-border-mid); border-radius: 10px;
  font-weight: 700; font-size: .85rem; cursor: pointer; background: #fff; transition: all .2s;
}
.nature-pills button.active { background: var(--c-accent); color: #fff; border-color: var(--c-accent); }

.highlight-zone { background: #f8fafc; border-radius: 12px; padding: 20px; }
.pricing-footer { display: flex; justify-content: space-between; padding-top: 10px; }
.footer-item { display: flex; flex-direction: column; }
.footer-item .label { font-size: .75rem; color: var(--c-muted); font-weight: 600; }
.footer-item .value { font-size: 1.1rem; font-weight: 800; }

.field-separator { height: 1px; background: var(--c-border); margin: 10px 0; }
.error-msg { font-size: .75rem; color: var(--c-danger); font-weight: 700; margin-top: -4px; }
.text-success { color: var(--c-success); }
.text-danger { color: var(--c-danger); }
.border-warn { border-color: #ea580c !important; }

/* ─── Global ─── */
.loading-overlay {
  position: fixed; inset: 0; z-index: 1000; background: rgba(255,255,255,0.7); backdrop-filter: blur(4px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
}
.loader-ring { width: 40px; height: 40px; position: relative; }
.loader-ring div { position: absolute; width: 32px; height: 32px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.toast-notification {
  position: fixed; top: 24px; right: 24px; padding: 14px 24px; border-radius: 12px; color: #fff; font-weight: 700; z-index: 1100;
}
.toast-notification.success { background: var(--c-success); }
.toast-notification.error { background: var(--c-danger); }

@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>