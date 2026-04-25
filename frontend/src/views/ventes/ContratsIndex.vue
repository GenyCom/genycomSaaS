<template>
  <div class="page-container">
    <div class="card mb-4 animate-fade-in-up">
      <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="card-title m-0">Abonnements & Contrats</h2>
        <div class="header-actions">
          <button @click="loadData" class="btn btn-secondary mr-2" title="Rafraîchir">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 1 0 2.13-5.87L2 9"/></svg>
          </button>
          <router-link to="/contrats/create" class="btn btn-primary">
            + Ajouter un contrat
          </router-link>
        </div>
      </div>
      <div v-if="loading" class="text-center p-8">Chargement...</div>
      <div v-else-if="error" class="text-center p-8 text-danger">{{ error }}</div>
      <div v-else-if="!contrats.length" class="text-center p-8 text-muted">
        Aucun contrat récurrent enregistré.
      </div>
      <div v-else class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Numéro / Titre</th>
              <th>Client</th>
              <th>Date Début</th>
              <th>Fréquence</th>
              <th>Prochaine Échéance</th>
              <th class="text-right">Total TTC</th>
              <th>Statut</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in contrats" :key="c.id" class="table-row-hover">
              <td>
                <div class="font-medium">{{ c.numero || ('CONT-' + c.id) }}</div>
                <div class="text-xs text-muted">{{ c.titre }}</div>
              </td>
              <td>{{ c.client?.societe || c.client?.nom + ' ' + c.client?.prenom }}</td>
              <td>{{ formatDate(c.date_debut) }}</td>
              <td>
                <span class="badge" :class="getFrequenceClass(c.frequence)">{{ c.frequence }}</span>
              </td>
              <td>
                <div :class="{'text-danger font-medium': isOverdue(c.prochaine_echeance) && c.statut === 'ACTIF'}">
                  {{ formatDate(c.prochaine_echeance) }}
                </div>
              </td>
              <td class="text-right font-medium">
                {{ formatMontant(c.total_ttc) }}
              </td>
              <td>
                <span class="badge" :class="getStatutClass(c.statut)">{{ c.statut }}</span>
              </td>
              <td class="text-center">
                <router-link :to="`/contrats/${c.id}/edit`" class="btn-icon text-primary mr-2" title="Modifier">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </router-link>
                <button @click="deleteContrat(c.id)" class="btn-icon text-danger" title="Supprimer">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer le contrat"
      message="Voulez-vous vraiment supprimer ce contrat ? Cette action est irréversible et stoppera toutes les facturations automatiques associées."
      confirmText="Supprimer définitivement"
      @confirm="executeDelete"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const contrats = ref([])
const loading = ref(false)
const error = ref('')
const showConfirm = ref(false)
const contractToDelete = ref(null)

const loadData = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/contrats')
    contrats.value = res.data
  } catch (err) {
    error.value = "Erreur lors du chargement des contrats"
    console.error(err)
  } finally {
    loading.value = false
  }
}

const deleteContrat = (id) => {
  contractToDelete.value = id
  showConfirm.value = true
}

const executeDelete = async () => {
  if (!contractToDelete.value) return
  showConfirm.value = false
  try {
    await api.delete(`/contrats/${contractToDelete.value}`)
    toast.success('Contrat supprimé.')
    loadData()
  } catch (err) {
    toast.error("Erreur lors de la suppression.")
  } finally {
    contractToDelete.value = null
  }
}

const formatDate = (dateStr) => {
  if(!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR')
}

const formatMontant = (mnt) => {
  return Number(mnt).toLocaleString('fr-FR', { style: 'currency', currency: 'MAD' })
}

const isOverdue = (dateStr) => {
  if(!dateStr) return false
  const d = new Date(dateStr)
  d.setHours(0,0,0,0)
  const today = new Date()
  today.setHours(0,0,0,0)
  return d <= today
}

const getStatutClass = (statut) => {
  switch (statut) {
    case 'ACTIF': return 'success'
    case 'SUSPENDU': return 'warning'
    case 'RESILIE': return 'danger'
    default: return 'secondary'
  }
}

const getFrequenceClass = (freq) => {
  switch (freq) {
    case 'MENSUEL': return 'info'
    case 'TRIMESTRIEL': return 'primary'
    case 'SEMESTRIEL': return 'warning'
    case 'ANNUEL': return 'success'
    default: return 'secondary'
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.badge.info { background: rgba(59, 130, 246, 0.1); color: var(--info); }
.badge.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
</style>
