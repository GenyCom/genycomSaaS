<template>
  <div class="param-reglements-view">
    


    <div class="settings-grid">
      <section class="info-card">
        <div class="card-header table-header-actions">
          <div class="flex-align-center">
            <div class="card-header-icon settings-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div>
              <h3>Modes de Règlement</h3>
              <p class="header-sub">Moyens de paiement acceptés</p>
            </div>
          </div>
          <button class="btn-add-mini" @click="openModeForm()">+ Ajouter</button>
        </div>

        <div class="card-body p-0">
          <div v-if="modeForm.show" class="inline-form-box">
            <h4>{{ modeForm.isEdit ? 'Modifier le mode' : 'Nouveau mode' }}</h4>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Libellé</label>
                <input v-model="modeForm.data.libelle" type="text" placeholder="Ex: Virement Bancaire" />
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-save-mini" @click="saveMode" :disabled="loading">Sauvegarder</button>
              <button class="btn-cancel-mini" @click="modeForm.show = false">Annuler</button>
            </div>
          </div>

          <table class="saas-table">
            <thead>
              <tr>
                <th>Libellé</th>
                <th class="text-right" style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in modes" :key="item.id">
                <td class="font-medium">{{ item.libelle }}</td>
                <td>
                  <div class="actions-group">
                    <button class="action-btn edit" @click="editMode(item)" title="Modifier">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="action-btn delete" @click="deleteItem('modes-reglement', item.id)" title="Supprimer">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="modes.length === 0">
                <td colspan="2" class="empty-row">Aucun mode de règlement configuré.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="info-card">
        <div class="card-header table-header-actions">
          <div class="flex-align-center">
            <div class="card-header-icon settings-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <h3>Conditions de Règlement</h3>
              <p class="header-sub">Délais de paiement standards</p>
            </div>
          </div>
          <button class="btn-add-mini" @click="openCondForm()">+ Ajouter</button>
        </div>

        <div class="card-body p-0">
          <div v-if="condForm.show" class="inline-form-box">
            <h4>{{ condForm.isEdit ? 'Modifier la condition' : 'Nouvelle condition' }}</h4>
            <div class="form-row-custom">
              <div class="form-group-custom" style="flex: 2;">
                <label>Libellé</label>
                <input v-model="condForm.data.libelle" type="text" placeholder="Ex: À 45 jours fin de mois" />
              </div>
              <div class="form-group-custom" style="flex: 1;">
                <label>Délai (+ jours)</label>
                <input type="number" v-model="condForm.data.nombre_jours" placeholder="Ex: 45" />
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-save-mini" @click="saveCond" :disabled="loading">Sauvegarder</button>
              <button class="btn-cancel-mini" @click="condForm.show = false">Annuler</button>
            </div>
          </div>

          <table class="saas-table">
            <thead>
              <tr>
                <th>Libellé / Description</th>
                <th class="text-center">Valeur</th>
                <th class="text-right" style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in conditions" :key="item.id">
                <td class="font-medium">{{ item.libelle }}</td>
                <td class="text-center">
                  <span class="badge-days" v-if="item.nombre_jours > 0">+ {{ item.nombre_jours }} jours</span>
                  <span class="badge-immediat" v-else-if="item.nombre_jours === 0">Immédiat</span>
                  <span class="badge-other" v-else>Autre</span>
                </td>
                <td>
                  <div class="actions-group">
                    <button class="action-btn edit" @click="editCond(item)" title="Modifier">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="action-btn delete" @click="deleteItem('conditions-reglement', item.id)" title="Supprimer">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="conditions.length === 0">
                <td colspan="3" class="empty-row">Aucune condition de règlement configurée.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Confirmer la suppression"
      message="Voulez-vous vraiment supprimer cet élément ? Cette action est irréversible."
      confirmText="Supprimer définitivement"
      @confirm="executeDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const loading = ref(false)
const modes = ref([])
const conditions = ref([])

const modeForm = ref({ show: false, isEdit: false, data: {} })
const condForm = ref({ show: false, isEdit: false, data: {} })

const showConfirm = ref(false)
const deleteParams = reactive({ endpoint: '', id: null })



const loadData = async () => {
  try {
    const resModes = await api.get('/parametrage/referentiels/modes-reglement')
    modes.value = resModes.data || []
    
    const resConds = await api.get('/parametrage/referentiels/conditions-reglement')
    conditions.value = resConds.data || []
  } catch (e) {
    console.error(e)
    toast.error('Erreur lors du chargement des données.')
  }
}

// ==========================================
// ACTIONS MODES DE REGLEMENT
// ==========================================
const openModeForm = () => {
  modeForm.value = { show: true, isEdit: false, data: { libelle: '' } }
}
const editMode = (item) => {
  modeForm.value = { show: true, isEdit: true, data: { ...item } }
}
const saveMode = async () => {
  if(!modeForm.value.data.libelle) return toast.error("Le libellé est obligatoire.")
  loading.value = true
  try {
    if (modeForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/modes-reglement/${modeForm.value.data.id}`, modeForm.value.data)
      toast.success('Mode de règlement mis à jour.')
    } else {
      await api.post('/parametrage/referentiels/modes-reglement', modeForm.value.data)
      toast.success('Nouveau mode de règlement ajouté.')
    }
    modeForm.value.show = false
    loadData()
  } catch (e) {
    toast.error("Erreur lors de l'enregistrement du mode.")
  } finally {
    loading.value = false
  }
}

// ==========================================
// ACTIONS CONDITIONS DE REGLEMENT
// ==========================================
const openCondForm = () => {
  condForm.value = { show: true, isEdit: false, data: { libelle: '', nombre_jours: 0 } }
}
const editCond = (item) => {
  condForm.value = { show: true, isEdit: true, data: { ...item } }
}
const saveCond = async () => {
  if(!condForm.value.data.libelle) return toast.error("Le libellé est obligatoire.")
  loading.value = true
  try {
    if (condForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/conditions-reglement/${condForm.value.data.id}`, condForm.value.data)
      toast.success('Condition mise à jour.')
    } else {
      await api.post('/parametrage/referentiels/conditions-reglement', condForm.value.data)
      toast.success('Nouvelle condition ajoutée.')
    }
    condForm.value.show = false
    loadData()
  } catch (e) {
    toast.error("Erreur lors de l'enregistrement de la condition.")
  } finally {
    loading.value = false
  }
}

// ==========================================
// SUPPRESSION COMMUNE
// ==========================================
const deleteItem = (endpoint, id) => {
  deleteParams.endpoint = endpoint
  deleteParams.id = id
  showConfirm.value = true
}

const executeDelete = async () => {
  showConfirm.value = false
  try {
    await api.delete(`/parametrage/referentiels/${deleteParams.endpoint}/${deleteParams.id}`)
    toast.success('Élément supprimé avec succès.')
    loadData()
  } catch (e) {
    toast.error("Impossible de supprimer. Cet élément est peut-être déjà relié à une facture.")
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
/* ─── Variables et Animations ─── */
.param-reglements-view {
  --c-border: #E8EAEE;
  --c-text: #1A1D23; 
  --c-muted: #6B7280; 
  --c-accent: #4F46E5; 
  --c-accent-bg: #EEF2FF;
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

.settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }

/* ─── Cartes ─── */
.info-card { background: #fff; border: 1px solid var(--c-border); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.card-header { padding: 16px 20px; background: #F9FAFB; border-bottom: 1px solid var(--c-border); }
.table-header-actions { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.flex-align-center { display: flex; align-items: center; gap: 12px; }

.card-header h3 { font-size: .85rem; font-weight: 700; color: var(--c-text); margin: 0; }
.header-sub { font-size: 0.7rem; color: var(--c-muted); margin: 2px 0 0 0; }
.card-header-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--c-accent-bg); color: var(--c-accent); display: flex; align-items: center; justify-content: center; }

/* ─── Boutons ─── */
.btn-add-mini { background: #fff; color: var(--c-accent); border: 1px solid var(--c-border); padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: .75rem; cursor: pointer; transition: all 0.2s; }
.btn-add-mini:hover { background: var(--c-accent-bg); border-color: var(--c-accent); }

.btn-save-mini { background: var(--c-accent); color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: .8rem; cursor: pointer; }
.btn-save-mini:hover { background: #4338CA; }
.btn-cancel-mini { background: #fff; color: var(--c-muted); border: 1px solid #D5D9E2; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: .8rem; cursor: pointer; }
.btn-cancel-mini:hover { background: #F1F5F9; color: var(--c-text); }

/* ─── Formulaires Inline ─── */
.inline-form-box { margin: 16px; padding: 16px; background: #F8FAFC; border: 1.5px dashed #CBD5E1; border-radius: 12px; }
.inline-form-box h4 { margin: 0 0 16px 0; font-size: 0.8rem; font-weight: 700; color: var(--c-accent); text-transform: uppercase; }
.form-row-custom { display: flex; gap: 16px; margin-bottom: 16px; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.form-group-custom label { font-size: .72rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.inline-form-box input { padding: 10px 12px; border: 1px solid #D5D9E2; border-radius: 8px; font-size: .85rem; outline: none; transition: border-color 0.2s; }
.inline-form-box input:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px var(--c-accent-bg); }
.form-actions { display: flex; gap: 10px; margin-top: 20px; }

/* ─── Tableaux ─── */
.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th { background: #fff; padding: 12px 20px; font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; font-size: 0.85rem; }
.empty-row { text-align: center; color: var(--c-muted); padding: 32px !important; font-size: 0.85rem; font-style: italic; }

/* ─── Utilities ─── */
.font-medium { font-weight: 600; color: var(--c-text); }
.text-right { text-align: right; }
.text-center { text-align: center; }

/* Badges Spécifiques */
.badge-days { background: #EEF2FF; color: #4F46E5; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.7rem; border: 1px solid #C7D2FE; white-space: nowrap; display: inline-block; }
.badge-immediat { background: #ECFDF5; color: #059669; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.7rem; border: 1px solid #A7F3D0; white-space: nowrap; display: inline-block; }
.badge-other { background: #FFFBEB; color: #D97706; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.7rem; border: 1px solid #FDE68A; white-space: nowrap; display: inline-block; }

/* ─── Actions Table ─── */
.actions-group { display: flex; gap: 6px; justify-content: flex-end; }
.action-btn { width: 28px; height: 28px; border-radius: 6px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover.edit { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn:hover.delete { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

/* ─── Toast ─── */
.toast-notification { position: fixed; top: 1.5rem; right: 1.5rem; padding: 1rem 1.5rem; border-radius: 10px; z-index: 9999; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-weight: 600; font-size: 0.9rem; }
.toast-notification.success { background: #10B981; color: #fff; }
.toast-notification.error { background: #EF4444; color: #fff; }

/* Responsive Grid */
@media (max-width: 900px) {
  .settings-grid { grid-template-columns: 1fr; }
}
</style>