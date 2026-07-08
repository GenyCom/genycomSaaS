<template>
  <div class="param-familles-view">
    


    <section class="info-card">
      <div class="card-header table-header-actions">
        <div class="flex-align-center">
          <div class="card-header-icon settings-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div>
            <h3>Familles de Produits</h3>
            <p class="header-sub">Structurez votre catalogue par catégories et sous-catégories</p>
          </div>
        </div>
        <button class="btn-add-mini" @click="openForm()">+ Nouvelle Famille</button>
      </div>

      <div class="card-body p-0">
        <div v-if="familleForm.show" class="inline-form-box">
          <h4>{{ familleForm.isEdit ? 'Modifier la famille' : 'Nouvelle famille' }}</h4>
          <div class="form-row-custom">
            <div class="form-group-custom" style="flex: 2;">
              <label>Libellé</label>
              <input v-model="familleForm.data.libelle" type="text" placeholder="Ex: Prestations Informatiques" />
            </div>
            <div class="form-group-custom" style="flex: 1;">
              <label>Code court</label>
              <input v-model="familleForm.data.code" type="text" placeholder="Ex: INFO" />
            </div>
          </div>
          <div class="form-row-custom">
            <div class="form-group-custom">
              <label>Catégorie Parente (Optionnelle)</label>
              <select v-model="familleForm.data.parent_id" class="select-custom">
                <option :value="null">--- Aucune (Catégorie Maître) ---</option>
                <option v-for="f in familles.filter(f => f.id !== familleForm.data.id && !f.parent_id)" :key="f.id" :value="f.id">
                  {{ f.libelle }} ({{ f.code }})
                </option>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-save-mini" @click="saveFamille" :disabled="loading">Sauvegarder</button>
            <button class="btn-cancel-mini" @click="familleForm.show = false">Annuler</button>
          </div>
        </div>

        <table class="saas-table">
          <thead>
            <tr>
              <th style="width: 20%">Code</th>
              <th style="width: 45%">Libellé</th>
              <th style="width: 20%">Type</th>
              <th class="text-right" style="width: 15%">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="parent in hierarchicalFamilles" :key="parent.id">
              <!-- Row for Parent -->
              <tr class="parent-row" :class="{ 'has-children': parent.children.length > 0 }">
                <td class="font-mono text-accent font-bold">
                  <div class="flex-align-center">
                    <button 
                      v-if="parent.children.length > 0"
                      class="btn-toggle-expand"
                      @click="toggleParent(parent.id)"
                      type="button"
                    >
                      <svg :class="{ 'rotated': collapsedParents[parent.id] }" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <span v-else class="toggle-placeholder"></span>
                    {{ parent.code }}
                  </div>
                </td>
                <td class="font-medium parent-libelle">
                  <span class="parent-icon">📁</span>
                  {{ parent.libelle }}
                  <span class="count-badge" v-if="parent.children.length > 0">
                    {{ parent.children.length }} sous-famille(s)
                  </span>
                </td>
                <td>
                  <span class="badge-master">Famille Maître</span>
                </td>
                <td>
                  <div class="actions-group">
                    <button class="action-btn edit" @click="editFamille(parent)" title="Modifier">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="action-btn delete" @click="deleteItem(parent.id)" title="Supprimer">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              
              <!-- Rows for Children -->
              <template v-if="!collapsedParents[parent.id]">
                <tr v-for="child in parent.children" :key="child.id" class="child-row">
                  <td class="font-mono text-muted pl-code">
                    {{ child.code }}
                  </td>
                  <td class="font-medium pl-libelle">
                    <span class="tree-line">└─</span>
                    <span class="child-icon">📁</span>
                    {{ child.libelle }}
                  </td>
                  <td>
                    <span class="badge-sub-fam">Sous-famille</span>
                  </td>
                  <td>
                    <div class="actions-group">
                      <button class="action-btn edit" @click="editFamille(child)" title="Modifier">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </button>
                      <button class="action-btn delete" @click="deleteItem(child.id)" title="Supprimer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </template>
            <tr v-if="familles.length === 0">
              <td colspan="4" class="empty-row">Aucune famille de produits configurée.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer la famille"
      message="Confirmer la suppression de cette famille de produits ? Cette action est irréversible."
      confirmText="Supprimer définitivement"
      @confirm="executeDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const loading = ref(false)
const familles = ref([])
const familleForm = ref({ show: false, isEdit: false, data: {} })

const showConfirm = ref(false)
const itemToDelete = ref(null)

const collapsedParents = reactive({})

const toggleParent = (id) => {
  collapsedParents[id] = !collapsedParents[id]
}

const hierarchicalFamilles = computed(() => {
  const parents = familles.value.filter(f => !f.parent_id)
  return parents.map(parent => {
    const children = familles.value.filter(f => f.parent_id === parent.id)
    return {
      ...parent,
      children: children.sort((a, b) => a.libelle.localeCompare(b.libelle))
    }
  }).sort((a, b) => a.libelle.localeCompare(b.libelle))
})



const loadData = async () => {
  try {
    const res = await api.get('/parametrage/referentiels/familles-produit')
    familles.value = res.data || []
  } catch (e) {
    console.error(e)
    toast.error("Erreur lors du chargement des familles.")
  }
}

const openForm = () => {
  familleForm.value = { show: true, isEdit: false, data: { libelle: '', code: '', parent_id: null } }
}

const editFamille = (item) => {
  familleForm.value = { show: true, isEdit: true, data: { ...item } }
}

const saveFamille = async () => {
  if (!familleForm.value.data.libelle || !familleForm.value.data.code) {
    return toast.error("Veuillez remplir le libellé et le code.")
  }
  loading.value = true
  try {
    if (familleForm.value.isEdit) {
      await api.put(`/parametrage/referentiels/familles-produit/${familleForm.value.data.id}`, familleForm.value.data)
      toast.success("Famille mise à jour avec succès.")
    } else {
      await api.post('/parametrage/referentiels/familles-produit', familleForm.value.data)
      toast.success("Nouvelle famille ajoutée.")
    }
    familleForm.value.show = false
    loadData()
  } catch(e) {
    toast.error("Erreur lors de l'enregistrement. Le code doit être unique.")
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
    await api.delete(`/parametrage/referentiels/familles-produit/${itemToDelete.value}`)
    toast.success("Famille supprimée avec succès.")
    loadData()
  } catch (e) {
    toast.error("Impossible. Cette famille contient probablement des sous-familles ou des produits associés.")
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
.param-familles-view {
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
.text-xs { font-size: 0.75rem; }
.italic { font-style: italic; }

/* Badge Parents (Même correction display nowrap) */
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

/* ─── Tree Table Specific Styles ─── */
.parent-row {
  background-color: #FAFBFD;
}
.parent-row:hover {
  background-color: #F3F4F6;
}
.child-row {
  background-color: #FFFFFF;
}
.child-row:hover {
  background-color: #F9FAFB;
}
.parent-libelle {
  display: flex;
  align-items: center;
  gap: 8px;
}
.parent-icon {
  font-size: 1.1rem;
}
.child-icon {
  font-size: 0.95rem;
  margin-right: 4px;
}
.toggle-placeholder {
  display: inline-block;
  width: 20px;
}
.btn-toggle-expand {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--c-muted);
  border-radius: 4px;
  transition: all 0.2s;
  width: 20px;
  height: 20px;
}
.btn-toggle-expand:hover {
  background: var(--c-accent-bg);
  color: var(--c-accent);
}
.btn-toggle-expand svg {
  transition: transform 0.2s ease;
}
.btn-toggle-expand svg.rotated {
  transform: rotate(-90deg);
}
.count-badge {
  background: #E0E7FF;
  color: #4338CA;
  font-size: 0.68rem;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 9999px;
  margin-left: 8px;
}
.pl-code {
  padding-left: 2.2rem !important;
}
.pl-libelle {
  padding-left: 1.8rem !important;
  display: flex;
  align-items: center;
}
.tree-line {
  color: #CBD5E1;
  font-family: monospace;
  font-weight: bold;
  margin-right: 8px;
  user-select: none;
}
.badge-master {
  background: var(--c-accent-bg);
  color: var(--c-accent);
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.68rem;
  border: 1px solid #CFFAFE;
}
.badge-sub-fam {
  background: #F0FDF4;
  color: #16A34A;
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.68rem;
  border: 1px solid #DCFCE7;
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