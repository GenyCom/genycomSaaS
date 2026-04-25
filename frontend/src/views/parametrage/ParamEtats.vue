<template>
  <div class="param-etats-view">
    


    <section class="info-card">
      <div class="card-header table-header-actions" style="flex-wrap: wrap;">
        <div class="flex-align-center">
          <div class="card-header-icon settings-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          </div>
          <div>
            <h3>États des Documents</h3>
            <p class="header-sub">Gérez les statuts personnalisés et leurs couleurs</p>
          </div>
        </div>
        <button class="btn-add-mini" @click="openModal()">+ Nouvel État</button>
      </div>

      <div class="filters-bar">
        <button 
          v-for="type in documentTypes" 
          :key="type.value"
          class="tab-small"
          :class="{ active: filterType === type.value }"
          @click="filterType = type.value"
        >
          {{ type.label }}
        </button>
      </div>

      <div class="card-body bg-light">
        <div v-if="loading" class="loading-state">
          <div class="loader-ring"><div></div><div></div><div></div><div></div></div>
          <p>Chargement des états...</p>
        </div>

        <div v-else class="grid-container">
          <div v-for="etat in filteredEtats" :key="etat.id" class="etat-card">
            <div class="etat-card-header">
              <div class="badge-preview" :style="{ backgroundColor: etat.couleur + '15', color: etat.couleur, borderColor: etat.couleur + '40' }">
                {{ etat.libelle }}
              </div>
              <div class="actions-group">
                <button class="action-btn edit" @click="openModal(etat)" title="Modifier">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button v-if="!etat.is_system" class="action-btn delete" @click="deleteEtat(etat.id)" title="Supprimer">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </div>
            </div>
            <div class="etat-card-body">
              <div class="etat-meta">Code: <strong>{{ etat.code }}</strong> <span class="dot-sep">•</span> Ordre: {{ etat.ordre }}</div>
              <p class="etat-desc">{{ etat.detail || 'Aucune description' }}</p>
            </div>
          </div>

          <div v-if="filteredEtats.length === 0" class="empty-row-card">
            Aucun état configuré pour ce type de document.
          </div>
        </div>
      </div>
    </section>

    <Transition name="fade">
      <div v-if="showModal" class="modal-overlay">
        <div class="modal-content info-card">
          <div class="card-header modal-header-custom">
            <h3>{{ editingId ? 'Modifier l\'État' : 'Nouvel État' }}</h3>
            <button class="btn-close-svg" @click="showModal = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          
          <div class="card-body edit-form">
            <div class="form-row-custom">
              <div class="form-group-custom" style="flex: 100%;">
                <label>Type de Document</label>
                <select v-model="form.type_document" class="input-custom">
                  <option v-for="type in documentTypes" :key="type.value" :value="type.value">
                    {{ type.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom" style="flex: 2;">
                <label>Libellé *</label>
                <input v-model="form.libelle" type="text" class="input-custom" placeholder="Ex: Payée, Brouillon..." required />
              </div>
              <div class="form-group-custom" style="flex: 1;">
                <label>Code *</label>
                <input v-model="form.code" type="text" class="input-custom" placeholder="Ex: PAY" required />
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom">
                <label>Couleur</label>
                <div class="color-picker-wrapper">
                  <input type="color" v-model="form.couleur" class="color-box" />
                  <input v-model="form.couleur" type="text" class="input-custom" placeholder="#000000" />
                </div>
              </div>
              <div class="form-group-custom">
                <label>Ordre d'affichage</label>
                <input type="number" v-model="form.ordre" class="input-custom" />
              </div>
            </div>

            <div class="form-row-custom">
              <div class="form-group-custom" style="flex: 100%;">
                <label>Description / Détail</label>
                <textarea v-model="form.detail" class="input-custom" rows="2" placeholder="Informations complémentaires..."></textarea>
              </div>
            </div>

            <div class="preview-section">
              <label>Prévisualisation du badge :</label>
              <div class="preview-box">
                <div class="badge-preview large" :style="{ backgroundColor: form.couleur + '15', color: form.couleur, borderColor: form.couleur + '40' }">
                  {{ form.libelle || 'Libellé' }}
                </div>
              </div>
            </div>

            <div class="form-actions mt-4 justify-end">
              <button class="btn-cancel-mini" @click="showModal = false">Annuler</button>
              <button class="btn-save-mini" @click="saveEtat" :disabled="submitting">
                {{ submitting ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer l'état"
      message="Supprimer cet état ? Cela pourrait affecter l'affichage des documents existants et rendre certains statuts illisibles."
      confirmText="Supprimer définitivement"
      @confirm="executeDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const etats = ref([])
const loading = ref(false)
const submitting = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const filterType = ref('facture')

const showConfirm = ref(false)
const itemToDelete = ref(null)



const documentTypes = [
  { value: 'devis', label: 'Devis' },
  { value: 'facture', label: 'Factures Client' },
  { value: 'commande', label: 'Commandes Fournisseur' },
  { value: 'br', label: 'Bons de Réception' },
  { value: 'bl', label: 'Bons de Livraison' },
  { value: 'dette', label: 'Factures Achat' },
  { value: 'depense', label: 'Dépenses' },
  { value: 'contrat', label: 'Contrats' },
  { value: 'projet', label: 'Projets' },
]

const form = ref({
  type_document: 'facture',
  code: '',
  libelle: '',
  ordre: 0,
  couleur: '#4F46E5',
  detail: ''
})

const filteredEtats = computed(() => {
  return etats.value
    // On force la mise en minuscules et on enlève les espaces vides (trim) pour comparer
    .filter(e => e.type_document && e.type_document.trim().toLowerCase() === filterType.value.toLowerCase())
    .sort((a, b) => a.ordre - b.ordre)
})

onMounted(loadEtats)

async function loadEtats() {
  loading.value = true
  try {
    const { data } = await api.get('/parametrage/referentiels/etats')
    etats.value = data || []
  } catch (err) {
    console.error('Erreur chargement états:', err)
    toast.error("Erreur lors du chargement des états.")
  } finally {
    loading.value = false
  }
}

function openModal(etat = null) {
  if (etat) {
    editingId.value = etat.id
    form.value = { ...etat }
  } else {
    editingId.value = null
    form.value = {
      type_document: filterType.value,
      code: '',
      libelle: '',
      ordre: filteredEtats.value.length + 1,
      couleur: '#4F46E5',
      detail: ''
    }
  }
  showModal.value = true
}

async function saveEtat() {
  if (!form.value.libelle || !form.value.code) {
    toast.error('Le libellé et le code sont obligatoires.')
    return
  }

  submitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/parametrage/referentiels/etats/${editingId.value}`, form.value)
      toast.success('État mis à jour avec succès.')
    } else {
      await api.post('/parametrage/referentiels/etats', form.value)
      toast.success('Nouvel état ajouté.')
    }
    showModal.value = false
    loadEtats()
  } catch (err) {
    console.error('Erreur sauvegarde état:', err)
    toast.error('Erreur lors de la sauvegarde.')
  } finally {
    submitting.value = false
  }
}

function deleteEtat(id) {
  itemToDelete.value = id
  showConfirm.value = true
}

async function executeDelete() {
  if (!itemToDelete.value) return
  showConfirm.value = false
  try {
    await api.delete(`/parametrage/referentiels/etats/${itemToDelete.value}`)
    toast.success('État supprimé avec succès.')
    loadEtats()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de la suppression')
  } finally {
    itemToDelete.value = null
  }
}
</script>

<style scoped>
/* ─── Variables et Animations ─── */
.param-etats-view {
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
.bg-light { background: #F8FAFC; }

.card-header h3 { font-size: .85rem; font-weight: 700; color: var(--c-text); margin: 0; }
.header-sub { font-size: 0.7rem; color: var(--c-muted); margin: 2px 0 0 0; }
.card-header-icon { width: 34px; height: 34px; border-radius: 8px; background: var(--c-accent-bg); color: var(--c-accent); display: flex; align-items: center; justify-content: center; }

/* ─── Filtres Tabs ─── */
.filters-bar { display: flex; gap: 8px; flex-wrap: wrap; padding: 12px 20px; background: #fff; border-bottom: 1px solid var(--c-border); }
.tab-small { padding: 6px 12px; font-size: 0.75rem; font-weight: 600; border-radius: 8px; border: 1px solid transparent; background: #F1F5F9; color: var(--c-muted); cursor: pointer; transition: all 0.2s; }
.tab-small:hover { background: #E2E8F0; color: var(--c-text); }
.tab-small.active { background: var(--c-accent-bg); color: var(--c-accent); border-color: #C7D2FE; }

/* ─── Grille des États ─── */
.grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; padding: 20px; }
.etat-card { background: #fff; border: 1px solid var(--c-border); border-radius: 12px; padding: 16px; transition: all 0.2s ease; display: flex; flex-direction: column; }
.etat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-2px); border-color: #CBD5E1; }
.etat-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.etat-card-body { border-top: 1px dashed var(--c-border); padding-top: 12px; }
.etat-meta { font-size: 0.7rem; color: var(--c-muted); margin-bottom: 6px; }
.etat-desc { font-size: 0.75rem; color: var(--c-text); margin: 0; line-height: 1.4; }
.dot-sep { margin: 0 4px; opacity: 0.5; }

/* ─── Badges ─── */
.badge-preview { padding: 4px 12px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; border: 1px solid transparent; display: inline-block; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.5px; }
.badge-preview.large { font-size: 0.85rem; padding: 8px 20px; border-radius: 8px; }

/* ─── Formulaires & Modals ─── */
.modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-content { width: 100%; max-width: 550px; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.modal-header-custom { display: flex; justify-content: space-between; align-items: center; }
.btn-close-svg { background: none; border: none; color: var(--c-muted); cursor: pointer; padding: 4px; border-radius: 6px; transition: background 0.2s; display: flex; }
.btn-close-svg:hover { background: #F1F5F9; color: var(--c-text); }

.edit-form { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.form-row-custom { display: flex; gap: 16px; flex-wrap: wrap; }
.form-group-custom { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.form-group-custom label { font-size: .72rem; font-weight: 700; color: var(--c-muted); text-transform: uppercase; }
.input-custom { padding: 10px 12px; border: 1.5px solid #D5D9E2; border-radius: 8px; font-size: .85rem; outline: none; transition: border-color 0.2s; background: #fff; font-family: inherit; width: 100%; box-sizing: border-box; }
.input-custom:focus { border-color: var(--c-accent); box-shadow: 0 0 0 3px var(--c-accent-bg); }

.color-picker-wrapper { display: flex; gap: 10px; }
.color-box { width: 42px; height: 42px; padding: 2px; border: 1.5px solid #D5D9E2; border-radius: 8px; background: #fff; cursor: pointer; }

.preview-section { margin-top: 8px; padding-top: 16px; border-top: 1px dashed var(--c-border); }
.preview-box { display: flex; justify-content: center; padding: 20px; background: #F8FAFC; border-radius: 8px; margin-top: 8px; border: 1px solid var(--c-border); }

/* ─── Boutons ─── */
.btn-add-mini { background: #fff; color: var(--c-accent); border: 1px solid var(--c-border); padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: .75rem; cursor: pointer; transition: all 0.2s; }
.btn-add-mini:hover { background: var(--c-accent-bg); border-color: var(--c-accent); }
.btn-save-mini { background: var(--c-accent); color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: .8rem; cursor: pointer; }
.btn-save-mini:hover { background: #4338CA; }
.btn-cancel-mini { background: #fff; color: var(--c-muted); border: 1px solid #D5D9E2; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: .8rem; cursor: pointer; }
.btn-cancel-mini:hover { background: #F1F5F9; color: var(--c-text); }
.justify-end { justify-content: flex-end; }

/* ─── Actions Table ─── */
.actions-group { display: flex; gap: 6px; justify-content: flex-end; }
.action-btn { width: 26px; height: 26px; border-radius: 6px; border: 1.5px solid var(--c-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--c-muted); transition: all .2s; }
.action-btn:hover.edit { color: var(--c-accent); border-color: var(--c-accent); background: var(--c-accent-bg); }
.action-btn:hover.delete { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }

/* ─── Utils ─── */
.empty-row-card { grid-column: 1 / -1; text-align: center; color: var(--c-muted); padding: 40px; font-size: 0.85rem; font-style: italic; background: #fff; border-radius: 12px; border: 1px dashed var(--c-border); }
.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: var(--c-muted); font-size: 0.85rem; font-weight: 600; }
.loader-ring { width: 30px; height: 30px; position: relative; margin-bottom: 10px; }
.loader-ring div { position: absolute; width: 24px; height: 24px; border: 3px solid transparent; border-top-color: var(--c-accent); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Toast ─── */
.toast-notification { position: fixed; top: 1.5rem; right: 1.5rem; padding: 1rem 1.5rem; border-radius: 10px; z-index: 9999; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-weight: 600; font-size: 0.9rem; }
.toast-notification.success { background: #10B981; color: #fff; }
.toast-notification.error { background: #EF4444; color: #fff; }
</style>