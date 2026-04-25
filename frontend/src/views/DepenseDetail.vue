<template>
  <div class="depense-detail-view animate-fade-in">
    
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">
          <router-link to="/depenses" class="breadcrumb-link">Dépenses</router-link>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ isNew ? 'Saisir une dépense' : 'Détails de la dépense' }}</span>
        </div>
      </div>
      <div class="topbar-actions">
        <router-link to="/depenses" class="btn-secondary-custom">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Retour
        </router-link>
        <button v-if="isNew || editMode" class="btn-primary-custom" @click="save" :disabled="loading">
          <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span v-if="loading">Sauvegarde...</span>
          <span v-else>Enregistrer</span>
        </button>
        <button v-else class="btn-primary-custom" @click="editMode = true">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Modifier
        </button>
      </div>
    </div>

    <div class="hero-header">
      <div class="hero-avatar expense-theme">
        <span v-if="isNew">+</span>
        <span v-else>#{{ form.id }}</span>
      </div>
      <div class="hero-meta">
        <div class="hero-type-badge">
          <span class="dot"></span>
          Frais Généraux
        </div>
        <h1 class="hero-name">{{ isNew ? 'Nouvelle Dépense' : form.libelle || 'Détails de la Dépense' }}</h1>
        <p class="hero-sub" v-if="!isNew">Enregistrée le {{ formatDateDisplay(form.date_depense) }}</p>
      </div>
    </div>

    <div class="form-card">
      <form @submit.prevent>
        
        <h3 class="section-title">Informations Générales</h3>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="saas-label">Date de la dépense <span class="required">*</span></label>
            <input v-model="form.date_depense" type="date" class="saas-input" :disabled="!editMode" required />
          </div>

          <div class="form-group">
            <label class="saas-label">Catégorie</label>
            <select v-model="form.categorie_id" class="saas-select" :disabled="!editMode">
              <option :value="null">Sélectionner une catégorie</option>
              <option value="1">Énergie (Électricité, Eau)</option>
              <option value="2">Fournitures de bureau</option>
              <option value="3">Loyer</option>
              <option value="4">Abonnements SaaS / Logiciels</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="saas-label">Libellé (Description brève) <span class="required">*</span></label>
          <input v-model="form.libelle" type="text" class="saas-input" placeholder="Ex: Achat fournitures bureau" :disabled="!editMode" required />
        </div>

        <div class="form-separator"></div>

        <h3 class="section-title">Montant & Règlement</h3>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="saas-label">Montant de la dépense <span class="required">*</span></label>
            <div class="input-with-currency highlight-input">
              <input v-model="form.montant" type="number" step="0.01" class="saas-input amount-ttc" :disabled="!editMode" required />
              <span class="currency-tag font-bold">DH</span>
            </div>
          </div>
          
          <div class="form-group">
            <label class="saas-label">État du paiement</label>
            <select v-model="form.etat_id" class="saas-select" :disabled="!editMode">
              <option value="1">En attente de paiement</option>
              <option value="2">Payé</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="saas-label">Note de règlement (Détails additionnels)</label>
          <textarea v-model="form.note_reglement" class="saas-input" rows="2" placeholder="Informations sur le virement, le chèque, etc..." :disabled="!editMode"></textarea>
        </div>

        <div class="form-separator"></div>

        <h3 class="section-title">Récurrence</h3>
        <div class="payment-status-box" :class="{ 'is-active': form.is_recurrente, 'disabled': !editMode }">
          <label class="custom-checkbox">
            <input type="checkbox" v-model="form.is_recurrente" :disabled="!editMode" />
            <span class="checkmark"></span>
            <div class="status-text">
              <span class="status-title">Dépense récurrente</span>
              <span class="status-desc">Cochez si cette charge se répète dans le temps.</span>
            </div>
          </label>
          
          <div v-if="form.is_recurrente" class="recurrence-options animate-fade-in mt-4 pl-10">
             <label class="saas-label">Fréquence :</label>
             <select v-model="form.frequence" class="saas-select w-full sm:w-1/2" :disabled="!editMode">
                <option value="mensuel">Mensuel</option>
                <option value="trimestriel">Trimestriel</option>
                <option value="annuel">Annuel</option>
             </select>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api.js'
import { toast } from '../services/toastService'

const router = useRouter()
const route = useRoute()

const isNew = computed(() => !route.params.id || route.params.id === 'new')
const editMode = ref(isNew.value)
const loading = ref(false)

const form = ref({
  id: '',
  date_depense: new Date().toISOString().substring(0, 10),
  categorie_id: null,
  libelle: '',
  montant: 0,
  etat_id: 1,
  note_reglement: '',
  is_recurrente: false,
  frequence: null
})

function formatDateDisplay(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

onMounted(async () => {
  if (!isNew.value) {
    loading.value = true
    try {
      const { data } = await api.get(`/depenses/${route.params.id}`)
      form.value = {
        id: data.id,
        date_depense: data.date_depense ? data.date_depense.substring(0, 10) : '',
        categorie_id: data.categorie_id,
        libelle: data.libelle || '',
        montant: data.montant || 0,
        etat_id: data.etat_id || 1,
        note_reglement: data.note_reglement || '',
        is_recurrente: Boolean(data.is_recurrente),
        frequence: data.frequence || null
      }
    } catch (error) {
      console.error('Erreur:', error)
    } finally {
      loading.value = false
    }
  }
})

async function save() {
  if (!form.value.libelle || !form.value.montant) {
    toast.error('Veuillez remplir les champs obligatoires.')
    return
  }

  loading.value = true
  try {
    if (isNew.value) {
      await api.post('/depenses', form.value)
    } else {
      await api.put(`/depenses/${form.value.id}`, form.value)
    }
    toast.success('Dépense enregistrée avec succès.')
    router.push('/depenses')
  } catch (error) {
    console.error(error)
    toast.error('Erreur lors de l\'enregistrement.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.depense-detail-view {
  --c-bg: #F7F8FA; --c-surface: #FFFFFF; --c-border: #E8EAEE;
  --c-text: #1A1D23; --c-muted: #6B7280; --c-accent: #2563EB;
  --c-danger: #E11D48; --c-success: #10B981;
  padding: 12px 28px 48px; background: var(--c-bg); min-height: 100vh; font-family: 'Inter', sans-serif;
}

.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: .85rem; }
.breadcrumb-link { color: var(--c-muted); font-weight: 500; text-decoration: none; transition: color 0.2s; }
.breadcrumb-link:hover { color: var(--c-accent); }
.breadcrumb-current { color: var(--c-text); font-weight: 700; }
.topbar-actions { display: flex; gap: 12px; }

.btn-primary-custom, .btn-secondary-custom {
  display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
  border-radius: 8px; font-size: .85rem; font-weight: 600; text-decoration: none; cursor: pointer;
  transition: all .2s; outline: none; border: 1.5px solid transparent;
}
.btn-primary-custom { background: var(--c-accent); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
.btn-primary-custom:hover:not(:disabled) { background: #1D4ED8; transform: translateY(-1px); }
.btn-primary-custom:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-secondary-custom { background: #fff; color: var(--c-text); border-color: var(--c-border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.btn-secondary-custom:hover { background: #F9FAFB; border-color: #D1D5DB; }

.hero-header {
  display: flex; align-items: center; gap: 20px; background: #fff;
  padding: 16px 24px; border-radius: 12px; border: 1px solid var(--c-border);
  margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); max-width: 800px; margin-left: auto; margin-right: auto;
}
.expense-theme { background: linear-gradient(135deg, #E11D48, #BE123C); color: #fff; }
.hero-avatar { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;}
.hero-name { font-size: 1.2rem; font-weight: 800; margin: 0; color: var(--c-text); }
.hero-type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase; color: var(--c-danger); margin-bottom: 4px; display: flex; align-items: center; gap: 5px; }
.hero-type-badge .dot { width: 6px; height: 6px; background: var(--c-danger); border-radius: 50%; }
.hero-sub { color: var(--c-muted); font-size: 0.85rem; margin-top: 2px;}

.form-card {
  background: #fff; border: 1px solid var(--c-border); border-radius: 16px; 
  padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,.03);
  max-width: 800px; margin: 0 auto;
}
.section-title { font-size: 1rem; font-weight: 700; color: var(--c-text); margin-bottom: 16px; margin-top: 0; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 20px; }
@media (max-width: 768px) { .form-grid-2 { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 20px; display: flex; flex-direction: column; }
.form-separator { height: 1px; background: var(--c-border); margin: 32px 0 24px; }

.saas-label { font-size: .85rem; font-weight: 600; color: var(--c-text); margin-bottom: 8px; }
.required { color: var(--c-danger); }

.saas-input, .saas-select {
  padding: 12px 16px; border-radius: 8px; border: 1.5px solid var(--c-border);
  background: var(--c-surface); color: var(--c-text); font-size: .95rem; font-family: inherit;
  transition: all 0.2s; width: 100%; box-sizing: border-box; outline: none;
}
textarea.saas-input { resize: vertical; min-height: 80px; }
.saas-input:focus, .saas-select:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.saas-input:disabled, .saas-select:disabled { background: #F3F4F6; color: var(--c-muted); cursor: not-allowed; border-color: #E5E7EB; }

.input-with-currency { position: relative; display: flex; align-items: center; }
.input-with-currency .saas-input { padding-right: 48px; text-align: left; }
.currency-tag { position: absolute; right: 16px; color: var(--c-muted); font-size: .85rem; pointer-events: none; }
.highlight-input .saas-input { border-color: #CBD5E1; background: #F8FAFC; }
.amount-ttc { font-weight: 800; color: var(--c-danger); font-size: 1.1rem; }

.payment-status-box {
  margin-top: 12px; padding: 16px 20px; border-radius: 12px;
  border: 1.5px solid var(--c-border); background: #F9FAFB; transition: all 0.3s;
}
.payment-status-box.is-active { background: #EEF2FF; border-color: #C7D2FE; }
.payment-status-box.disabled { opacity: 0.7; pointer-events: none; }

.custom-checkbox { display: flex; align-items: flex-start; gap: 16px; cursor: pointer; position: relative; }
.custom-checkbox input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
.checkmark {
  height: 22px; width: 22px; background-color: #fff; border: 2px solid #D1D5DB; border-radius: 6px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; margin-top: 2px;
}
.custom-checkbox input:checked ~ .checkmark { background-color: var(--c-accent); border-color: var(--c-accent); }
.checkmark:after { content: ""; display: none; width: 6px; height: 10px; border: solid white; border-width: 0 2.5px 2.5px 0; transform: rotate(45deg); margin-bottom: 2px;}
.custom-checkbox input:checked ~ .checkmark:after { display: block; }

.status-text { display: flex; flex-direction: column; }
.status-title { font-weight: 700; font-size: .95rem; color: var(--c-text); }
.status-desc { font-size: .8rem; color: var(--c-muted); margin-top: 2px; }
</style>