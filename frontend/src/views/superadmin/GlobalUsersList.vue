<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Utilisateurs Globaux</h2>
        <p class="text-sm text-muted">Aperçu Central des comptes (y compris les SuperAdmins)</p>
      </div>
      <router-link to="/superadmin/users/create" class="btn btn-primary" style="background: var(--warning); box-shadow: none;">+ Nouvel Utilisateur</router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card" style="text-align:center; padding:3rem;">
      <p class="text-muted">Chargement des utilisateurs...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card" style="text-align:center; padding:2rem;">
      <p style="color: var(--danger);">{{ error }}</p>
      <button class="btn btn-secondary btn-sm mt-2" @click="fetchUsers">Réessayer</button>
    </div>

    <!-- Table -->
    <div v-else class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom Complet</th>
            <th>Email</th>
            <th>Type</th>
            <th>SaaS rattachés</th>
            <th>Création</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="users.length === 0">
            <td colspan="6" class="text-muted" style="text-align:center; padding:2rem;">Aucun utilisateur trouvé.</td>
          </tr>
          <tr v-for="user in users" :key="user.id">
            <td style="font-weight:600;">{{ user.prenom }} {{ user.nom }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span v-if="user.is_superadmin" class="badge badge-danger" style="background:var(--warning-bg); color:var(--warning);">SuperAdmin Central</span>
              <span v-else-if="user.tenants && user.tenants.length > 0" class="badge badge-info">Locataire (SaaS)</span>
              <span v-else class="badge badge-default">Utilisateur</span>
            </td>
            <td>
              <template v-if="user.is_superadmin">
                <span class="text-muted">Accès global</span>
              </template>
              <template v-else-if="user.tenants && user.tenants.length > 0">
                {{ user.tenants.map(t => t.nom).join(', ') }}
              </template>
              <template v-else>
                <span class="text-muted">—</span>
              </template>
            </td>
            <td class="text-muted">{{ formatDate(user.created_at) }}</td>
            <td>
              <div class="flex items-center gap-2">
                <router-link :to="`/superadmin/users/${user.id}/edit`" class="btn btn-secondary btn-sm">Editer</router-link>
                <button v-if="!user.is_superadmin" class="btn btn-sm" style="background:var(--danger-bg); color:var(--danger);" @click="deleteUser(user)">Suppr.</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Supprimer l'utilisateur"
      :message="`Voulez-vous vraiment supprimer l'utilisateur ${userToDelete?.prenom} ${userToDelete?.nom} ? Cette action est irréversible.`"
      confirmText="Supprimer l'utilisateur"
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

const users = ref([])
const loading = ref(false)
const error = ref(null)
const showConfirm = ref(false)
const userToDelete = ref(null)

onMounted(() => {
  fetchUsers()
})

async function fetchUsers() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/superadmin/users')
    // L'API retourne un objet paginé { data: [...], ... }
    users.value = data.data || data
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des utilisateurs.'
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  const now = new Date()
  const diffMs = now - d
  const diffDays = Math.floor(diffMs / 86400000)
  if (diffDays === 0) return "Aujourd'hui"
  if (diffDays === 1) return 'Hier'
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function deleteUser(user) {
  userToDelete.value = user
  showConfirm.value = true
}

async function executeDelete() {
  if (!userToDelete.value) return
  showConfirm.value = false
  try {
    await api.delete(`/superadmin/users/${userToDelete.value.id}`)
    users.value = users.value.filter(u => u.id !== userToDelete.value.id)
    toast.success('Utilisateur supprimé.')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de la suppression.')
  } finally {
    userToDelete.value = null
  }
}
</script>
