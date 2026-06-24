<template>
  <div class="param-categories-view">
    <section class="info-card">
      <div class="card-header table-header-actions">
        <div class="flex-align-center">
          <div class="card-header-icon settings-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div>
            <h3>Catégories de Dépenses</h3>
            <p class="header-sub">Configurez les rubriques de charges pour vos analyses et rapports</p>
          </div>
        </div>
        <button class="btn-add-mini" @click="openForm()">+ Nouvelle Catégorie</button>
      </div>

      <div class="card-body p-0">
        <div v-if="categoryForm.show" class="inline-form-box">
          <h4>{{ categoryForm.isEdit ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h4>
          <div class="form-row-custom">
            <div class="form-group-custom" style="flex: 2;">
              <label>Libellé *</label>
              <input v-model="categoryForm.data.libelle" type="text" placeholder="Ex: Loyer, Électricité, Restauration..." required />
            </div>
            <div class="form-group-custom" style="flex: 1;">
              <label>Description / Détail</label>
              <input v-model="categoryForm.data.detail" type="text" placeholder="Ex: Charges fixes de bureau" />
            </div>
          </div>
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>Catégorie Parente (Optionnelle)</label>
              <select v-model="categoryForm.data.parent_id" class="select-custom">
                <option :value="null">--- Aucune (Catégorie Maître) ---</option>
                <option v-for="c in categories.filter(c => c.id !== categoryForm.data.id && !c.parent_id)" :key="c.id" :value="c.id">
                  {{ c.libelle }}
                </option>
              </select>
              <p class="help-text">Utile pour regrouper sous des catégories principales (ex: Loyer sous Charges Fixes).</p>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-save-mini" @click="saveCategory" :disabled="loading">Sauvegarder</button>
            <button class="btn-cancel-mini" @click="categoryForm.show = false">Annuler</button>
          </div>
        </div>

        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 45%">Libellé</th>
              <th style="width: 40%">Catégorie Parente / Description</th>
              <th class="text-right" style="width: 15%">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in categories" :key="item.id" class="table-row">
              <td class="font-medium">
                <span v-if="item.parent_id" class="sub-indicator">↳</span>
                {{ item.libelle }}
              </td>
              <td>
                <span class="badge-parent" v-if="item.parent">{{ item.parent.libelle }}</span>
                <span class="text-muted text-xs italic" v-else-if="item.detail">{{ item.detail }}</span>
                <span class="text-muted italic text-xs" v-else>Catégorie Maître</span>
              </td>
              <td>
                <div class="actions-group">
                  <button class="action-btn edit" @click="editCategory(item)" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="action-btn delete" @click="deleteItem(item.id)" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="categories.length === 0">
              <td colspan="3" class="empty-row">Aucune catégorie de dépenses configurée.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer la catégorie"
      message="Confirmer la suppression de cette catégorie de dépenses ? Cette action est irréversible et affectera la classification des dépenses existantes."
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
const categories = ref([])
const categoryForm = ref({ show: false, isEdit: false, data: {} })

const showConfirm = ref(false)
const itemToDelete = ref(null)

const loadData = async () => {
  try {
    const res = await api.get('/parametrage/referentiels/categories-depense')
    // Trier pour afficher les sous-catégories sous leurs parents respectifs
    const raw = res.data || []
    const masters = raw.filter(c => !c.parent_id).sort((a,b) => a.libelle.localeCompare(b.libelle))
    const sorted = []
    masters.forEach(m => {
      sorted.push(m)
      const children = raw.filter(c => c.parent_id === m.id).sort((a,b) => a.libelle.localeCompare(b.libelle))
      children.forEach(ch => sorted.push(ch))
    })
    categories.value = sorted
  } catch (e) {
    console.error(e)
    toast.error("Erreur lors du chargement des catégories de dépenses.")
  }
}

const openForm = () => {
  categoryForm.value = { show: true, isEdit: false, data: { libelle: '', detail: '', parent_id: null } }
}

const editCategory = (item) => {
  categoryForm.value = { show: true, isEdit: true, data: { ...item } }
}

const saveCategory = async () => {
  if (!categoryForm.value.data.libelle) {
    return toast.error("Le libellé est obligatoire.")
  }
  loading.value = true
  try {
    const payload = {
      libelle: categoryForm.value.data.libelle,
      detail: categoryForm.value.data.detail || null,
      parent_id: categoryForm.value.data.parent_id || null
    }

    if (categoryForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/categories-depense/${categoryForm.value.data.id}`, payload)
      toast.success("Catégorie mise à jour avec succès.")
    } else {
      await api.post('/parametrage/referentiels/categories-depense', payload)
      toast.success("Nouvelle catégorie ajoutée.")
    }
    categoryForm.value.show = false
    loadData()
  } catch(e) {
    toast.error("Erreur lors de l'enregistrement de la catégorie.")
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
    await api.delete(`/parametrage/referentiels/categories-depense/${itemToDelete.value}`)
    toast.success("Catégorie supprimée avec succès.")
    loadData()
  } catch (e) {
    toast.error("Impossible. Cette catégorie est probablement reliée à des dépenses existantes ou contient des sous-catégories.")
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
.param-categories-view {
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
.inline-form-box input, .select-custom { padding: 10px 12px; border: 1px solid #D5D9E2; border-radius: 8px; font-size: .85rem; outline: none; transition: border-color 0.2s; background: #fff; font-family: inherit; width: 100%; box-sizing: border-box; }
.inline-form-box input:focus, .select-custom:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px var(--c-accent-bg); }
.form-actions { display: flex; gap: 10px; margin-top: 20px; }
.help-text { margin: 0; font-size: 0.7rem; color: var(--c-muted); }

/* ─── Tableaux ─── */
.saas-table { width: 100%; border-collapse: collapse; }
.saas-table th { background: #fff; padding: 12px 20px; font-size: .68rem; font-weight: 700; text-transform: uppercase; color: var(--c-muted); text-align: left; border-bottom: 1px solid var(--c-border); }
.saas-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; font-size: 0.85rem; }
.empty-row { text-align: center; color: var(--c-muted); padding: 32px !important; font-size: 0.85rem; font-style: italic; }

.sub-indicator { color: var(--c-accent); margin-right: 6px; font-weight: bold; }

/* ─── Utilities ─── */
.font-medium { font-weight: 600; color: var(--c-text); }
.text-right { text-align: right; }
.text-muted { color: var(--c-muted); }
.text-xs { font-size: 0.75rem; }
.italic { font-style: italic; }

.badge-parent { 
  background: #F1F5F9; 
  color: #475569; 
  padding: 4px 10px; 
  border-radius: 6px; 
  font-weight: 700; 
  font-size: 0.7rem; 
  border: 1px solid #E2E8F0; 
  white-space: nowrap; 
  display: inline-block; 
}

/* ─── Actions Table ─── */
.actions-group { display: flex; gap: 6px; justify-content: flex-end; }
.action-btn { width: 28px; height: 28px; border-radius: 6px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover.edit { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn:hover.delete { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }
</style>
