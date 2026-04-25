<template>
  <div class="param-entrepots-view">
    


    <section class="info-card">
      <div class="card-header table-header-actions">
        <div class="flex-align-center">
          <div class="card-header-icon settings-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 21V9l9-7 9 7v12Z"/><path d="M9 21V12h6v9"/></svg>
          </div>
          <div>
            <h3>Entrepôts de Stockage</h3>
            <p class="header-sub">Gérez les lieux physiques où vous stockez vos marchandises</p>
          </div>
        </div>
        <button class="btn-add-mini" @click="openForm()">+ Nouvel Entrepôt</button>
      </div>

      <div class="card-body p-0">
        <div v-if="entrepotForm.show" class="inline-form-box">
          <h4>{{ entrepotForm.isEdit ? 'Modifier l\'entrepôt' : 'Nouvel entrepôt' }}</h4>
          <div class="form-row-custom">
            <div class="form-group-custom" style="flex: 2;">
              <label>Nom de l'entrepôt</label>
              <input v-model="entrepotForm.data.nom" type="text" placeholder="Ex: Magasin Principal" />
            </div>
            <div class="form-group-custom" style="flex: 1;">
              <label>Code court</label>
              <input v-model="entrepotForm.data.code" type="text" placeholder="Ex: MAG-01" />
            </div>
          </div>
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>Adresse (Optionnelle)</label>
              <input v-model="entrepotForm.data.adresse" type="text" placeholder="Adresse complète de localisation..." />
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-save-mini" @click="saveEntrepot" :disabled="loading">Sauvegarder</button>
            <button class="btn-cancel-mini" @click="entrepotForm.show = false">Annuler</button>
          </div>
        </div>

        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 15%">Code</th>
              <th style="width: 30%">Nom de l'Entrepôt</th>
              <th style="width: 35%">Adresse</th>
              <th class="text-center" style="width: 10%">Statut</th>
              <th class="text-right" style="width: 10%">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in entrepots" :key="item.id">
              <td class="font-mono text-accent font-bold">{{ item.code }}</td>
              <td class="font-medium">{{ item.nom }}</td>
              <td class="text-muted">{{ item.adresse || '---' }}</td>
              <td class="text-center">
                <span v-if="item.is_default" class="badge-principal">Par défaut</span>
              </td>
              <td>
                <div class="actions-group">
                  <button class="action-btn edit" @click="editEntrepot(item)" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="action-btn delete" @click="deleteItem(item.id)" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="entrepots.length === 0">
              <td colspan="5" class="empty-row">Aucun entrepôt configuré.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer l'entrepôt"
      message="Supprimer cet entrepôt ? Assurez-vous qu'il est vide de tout stock ou historique. Cette action est irréversible."
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
const entrepots = ref([])
const entrepotForm = ref({ show: false, isEdit: false, data: {} })

const showConfirm = ref(false)
const itemToDelete = ref(null)



const loadData = async () => {
  try {
    const res = await api.get('/parametrage/referentiels/entrepots')
    entrepots.value = res.data || []
  } catch (e) {
    console.error(e)
    toast.error("Erreur lors du chargement des entrepôts.")
  }
}

const openForm = () => {
  entrepotForm.value = { show: true, isEdit: false, data: { nom: '', code: '', adresse: '' } }
}

const editEntrepot = (item) => {
  entrepotForm.value = { show: true, isEdit: true, data: { ...item } }
}

const saveEntrepot = async () => {
  if (!entrepotForm.value.data.nom || !entrepotForm.value.data.code) {
    return toast.error("Veuillez remplir le nom et le code court.")
  }
  loading.value = true
  try {
    if (entrepotForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/entrepots/${entrepotForm.value.data.id}`, entrepotForm.value.data)
      toast.success("Entrepôt mis à jour avec succès.")
    } else {
      await api.post('/parametrage/referentiels/entrepots', entrepotForm.value.data)
      toast.success("Nouvel entrepôt ajouté.")
    }
    entrepotForm.value.show = false
    loadData()
  } catch(e) {
    toast.error("Erreur : Le code doit être unique ou les données sont invalides.")
  } finally {
    loading.value = false
  }
}

const deleteItem = (id) => {
  itemToDelete.value = id
  showConfirm.value = true
}

const executeDelete = async () => {
  if (!itemToDelete.value) return
  showConfirm.value = false
  try {
    await api.delete(`/parametrage/referentiels/entrepots/${itemToDelete.value}`)
    toast.success("Entrepôt supprimé avec succès.")
    loadData()
  } catch (e) {
    toast.error("Impossible. Cet entrepôt contient potentiellement des stocks ou des historiques.")
  } finally {
    itemToDelete.value = null
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
/* ─── Variables et Animations ─── */
.param-entrepots-view {
  --c-border: #E8EAEE;
  --c-text: #1A1D23; 
  --c-muted: #6B7280; 
  --c-accent: #4F46E5; 
  --c-accent-bg: #EEF2FF;
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

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
.text-muted { color: var(--c-muted); }

.badge-principal { 
  font-size: 0.65rem; 
  background: #ECFDF5; 
  color: #059669; 
  padding: 4px 8px; 
  border-radius: 4px; 
  font-weight: 800; 
  text-transform: uppercase; 
  border: 1px solid #A7F3D0; 
  white-space: nowrap; 
  display: inline-block; 
}

/* ─── Actions Table ─── */
.actions-group { display: flex; gap: 6px; justify-content: flex-end; }
.action-btn { width: 28px; height: 28px; border-radius: 6px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover.edit { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn:hover.delete { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

/* ─── Toast ─── */
.toast-notification { position: fixed; top: 1.5rem; right: 1.5rem; padding: 1rem 1.5rem; border-radius: 10px; z-index: 9999; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-weight: 600; font-size: 0.9rem; }
.toast-notification.success { background: #10B981; color: #fff; }
.toast-notification.error { background: #EF4444; color: #fff; }
</style>