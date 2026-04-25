<template>
  <div class="param-devises-tva-view">
    


    <div class="settings-grid">
      <section class="info-card">
        <div class="card-header table-header-actions">
          <div class="flex-align-center">
            <div class="card-header-icon settings-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
            </div>
            <div>
              <h3>Taux de TVA</h3>
              <p class="header-sub">Gérez les taxes applicables</p>
            </div>
          </div>
          <button class="btn-add-mini" @click="openTvaForm()">+ Ajouter</button>
        </div>

        <div class="card-body p-0">
          <div v-if="tvaForm.show" class="inline-form-box">
            <h4>{{ tvaForm.isEdit ? 'Modifier le taux' : 'Nouveau taux' }}</h4>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Libellé</label>
                <input v-model="tvaForm.data.libelle" type="text" placeholder="Ex: TVA Normale" />
              </div>
              <div class="form-group-custom">
                <label>Taux (%)</label>
                <input type="number" step="0.01" v-model="tvaForm.data.taux" placeholder="Ex: 20" />
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-save-mini" @click="saveTva" :disabled="loading">Sauvegarder</button>
              <button class="btn-cancel-mini" @click="tvaForm.show = false">Annuler</button>
            </div>
          </div>

          <table class="saas-table">
            <thead>
              <tr>
                <th>Libellé</th>
                <th>Taux (%)</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tva in tvas" :key="tva.id">
                <td class="font-medium">{{ tva.libelle || 'TVA Personnalisée' }}</td>
                <td class="font-bold mono text-accent">{{ Number(tva.taux).toFixed(2) }} %</td>
                <td>
                  <div class="actions-group">
                    <button class="action-btn edit" @click="editTva(tva)" title="Modifier">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="action-btn delete" @click="deleteItem('taux-tva', tva.id)" title="Supprimer">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="tvas.length === 0">
                <td colspan="3" class="empty-row">Aucun taux de TVA configuré.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="info-card">
        <div class="card-header table-header-actions">
          <div class="flex-align-center">
            <div class="card-header-icon settings-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="8"/><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <h3>Devises (Monnaies)</h3>
              <p class="header-sub">Gérez vos devises de facturation</p>
            </div>
          </div>
          <button class="btn-add-mini" @click="openDeviseForm()">+ Ajouter</button>
        </div>

        <div class="card-body p-0">
          <div v-if="deviseForm.show" class="inline-form-box">
            <h4>{{ deviseForm.isEdit ? 'Modifier la devise' : 'Nouvelle devise' }}</h4>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Nom de la devise</label>
                <input v-model="deviseForm.data.nom" type="text" placeholder="Ex: Euro" />
              </div>
              <div class="form-group-custom">
                <label>Symbole ou Code</label>
                <input v-model="deviseForm.data.symbole" type="text" placeholder="Ex: € ou EUR" />
              </div>
            </div>
            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Taux de change (par rapport à la principale)</label>
                <input type="number" step="0.0001" v-model="deviseForm.data.taux_change" placeholder="Ex: 10.85" />
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-save-mini" @click="saveDevise" :disabled="loading">Sauvegarder</button>
              <button class="btn-cancel-mini" @click="deviseForm.show = false">Annuler</button>
            </div>
          </div>

          <table class="saas-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th class="text-center">Symbole</th>
                <th class="text-right">Taux</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="devise in devises" :key="devise.id">
                <td class="font-medium">{{ devise.nom }} <span v-if="devise.is_principale" class="badge-principal">Principale</span></td>
                <td class="text-center font-bold">{{ devise.symbole || devise.code_iso }}</td>
                <td class="text-right mono">{{ Number(devise.taux_change || 1).toFixed(4) }}</td>
                <td>
                  <div class="actions-group">
                    <button class="action-btn edit" @click="editDevise(devise)" title="Modifier">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button v-if="!devise.is_principale" class="action-btn delete" @click="deleteItem('devises', devise.id)" title="Supprimer">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="devises.length === 0">
                <td colspan="4" class="empty-row">Aucune devise configurée.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Confirmer la suppression"
      message="Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible."
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
const tvas = ref([])
const devises = ref([])

const tvaForm = ref({ show: false, isEdit: false, data: {} })
const deviseForm = ref({ show: false, isEdit: false, data: {} })

const showConfirm = ref(false)
const deleteParams = reactive({ endpoint: '', id: null })



const loadData = async () => {
  try {
    const resTva = await api.get('/parametrage/referentiels/taux-tva')
    tvas.value = resTva.data || []
    
    const resDevise = await api.get('/parametrage/referentiels/devises')
    devises.value = resDevise.data || []
  } catch (e) {
    console.error(e)
    toast.error('Erreur lors du chargement des données.')
  }
}

// ==========================================
// ACTIONS TVA
// ==========================================
const openTvaForm = () => {
  tvaForm.value = { show: true, isEdit: false, data: { taux: '', libelle: '' } }
}
const editTva = (item) => {
  tvaForm.value = { show: true, isEdit: true, data: { ...item } }
}
const saveTva = async () => {
  if(!tvaForm.value.data.taux || !tvaForm.value.data.libelle) return toast.error("Remplissez tous les champs obligatoires.")
  loading.value = true
  try {
    if (tvaForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/taux-tva/${tvaForm.value.data.id}`, tvaForm.value.data)
      toast.success('Taux de TVA mis à jour avec succès.')
    } else {
      await api.post('/parametrage/referentiels/taux-tva', tvaForm.value.data)
      toast.success('Nouveau taux de TVA ajouté.')
    }
    tvaForm.value.show = false
    loadData()
  } catch (e) {
    toast.error("Erreur lors de l'enregistrement de la TVA.")
  } finally {
    loading.value = false
  }
}

// ==========================================
// ACTIONS DEVISES
// ==========================================
const openDeviseForm = () => {
  deviseForm.value = { show: true, isEdit: false, data: { nom: '', symbole: '', taux_change: 1 } }
}
const editDevise = (item) => {
  deviseForm.value = { show: true, isEdit: true, data: { ...item } }
}
const saveDevise = async () => {
  if(!deviseForm.value.data.nom) return toast.error("Le nom de la devise est obligatoire.")
  loading.value = true
  try {
    if (deviseForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/devises/${deviseForm.value.data.id}`, deviseForm.value.data)
      toast.success('Devise mise à jour avec succès.')
    } else {
      await api.post('/parametrage/referentiels/devises', deviseForm.value.data)
      toast.success('Nouvelle devise ajoutée.')
    }
    deviseForm.value.show = false
    loadData()
  } catch (e) {
    toast.error("Erreur lors de l'enregistrement de la devise.")
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
    toast.error("Impossible de supprimer. Cet élément est probablement utilisé dans un document.")
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
/* ─── Variables et Grille ─── */
.param-devises-tva-view {
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
.font-bold { font-weight: 700; }
.mono { font-family: 'JetBrains Mono', monospace; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-accent { color: var(--c-accent); }

.badge-principal { font-size: 0.6rem; background: var(--c-accent-bg); color: var(--c-accent); padding: 3px 6px; border-radius: 4px; margin-left: 6px; font-weight: 800; text-transform: uppercase; }

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