<template>
  <div class="animate-fade-in">
    <div class="flex items-center gap-2 mb-3">
      <router-link to="/superadmin/tenants" class="btn btn-secondary btn-sm">← Retour</router-link>
      <h2 style="font-size:1.1rem; font-weight:600;">Détails de l'Instance SaaS</h2>
    </div>

    <!-- Chargement -->
    <div v-if="loading" class="card" style="text-align:center; padding:2rem;">
      <p class="text-muted">Chargement des détails de l'instance...</p>
    </div>

    <template v-else-if="tenant">
      <div class="kpi-grid mb-3">
        <div class="kpi-card accent">
          <div class="kpi-label">Stockage Base de données</div>
          <div class="kpi-value">{{ dbSize }} MB</div>
          <div class="kpi-sub">Quota : 10 GB</div>
          <div class="kpi-icon accent"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div>
        </div>
        <div class="kpi-card success">
          <div class="kpi-label">Utilisateurs Licenciés</div>
          <div class="kpi-value">{{ users.length }}</div>
          <div class="kpi-sub">Max : Illimité (Plan Pro)</div>
          <div class="kpi-icon success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        </div>
        <div class="kpi-card warning">
          <div class="kpi-label">Statut du Serveur</div>
          <div class="kpi-value" :class="tenant.statut === 'suspendu' ? 'text-danger' : 'text-success'">
            {{ tenant.statut === 'suspendu' ? 'Suspendu' : 'En Ligne' }}
          </div>
          <div class="kpi-sub">{{ tenant.statut === 'suspendu' ? 'Hors ligne pour les clients' : 'Disponible' }}</div>
          <div class="kpi-icon warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="card">
          <h3 class="form-section-title">Configuration Infra</h3>
          <ul style="list-style:none; line-height: 2;">
             <li><span class="text-muted">ID & Raison Sociale :</span> {{ tenant.nom }}</li>
             <li><span class="text-muted">Domaine Isolé :</span> 
               <span v-if="tenant.domain">
                 <a :href="'http://' + tenant.domain" target="_blank" style="color:var(--info);">{{ tenant.domain }}</a>
               </span>
               <span v-else class="text-muted">Non configuré</span>
             </li>
             <li><span class="text-muted">Nom Base MySQL :</span> <code class="font-mono" style="background:var(--bg-input); padding:0.2rem 0.4rem; border-radius:4px;">{{ tenant.database_name }}</code></li>
             <li><span class="text-muted">Date provisionnement :</span> {{ formatDate(tenant.created_at) }}</li>
             <li><span class="text-muted">Statut :</span> 
               <span class="badge" :class="tenant.statut === 'actif' ? 'badge-success' : tenant.statut === 'suspendu' ? 'badge-danger' : 'badge-warning'">
                 {{ tenant.statut }}
               </span>
             </li>
          </ul>
          <div class="mt-3 flex gap-2">
             <button class="btn" 
                     :class="tenant.statut === 'suspendu' ? 'btn-success' : 'btn-secondary'" 
                     @click="toggleStatus" 
                     :disabled="actionLoading">
               <span v-if="actionLoading">⏳ En cours...</span>
               <span v-else-if="tenant.statut === 'suspendu'">Réactiver l'instance</span>
               <span v-else>Suspendre l'instance</span>
             </button>
             <button class="btn btn-secondary text-danger" @click="destroyTenant" :disabled="actionLoading">
               Détruire
             </button>
          </div>
        </div>

        <div class="card">
          <h3 class="form-section-title">Utilisateurs rattachés</h3>
          <table class="data-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th style="text-align:right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="users.length === 0">
                <td colspan="4" style="text-align:center;" class="text-muted">Aucun utilisateur rattaché.</td>
              </tr>
              <tr v-for="u in users" :key="u.id">
                <td>{{ u.prenom }} {{ u.nom }}</td>
                <td>{{ u.email }}</td>
                <td>
                  <span v-if="u.is_owner" class="badge badge-success">Gérant (Owner)</span>
                  <span v-else class="badge badge-default">{{ u.role_name }}</span>
                </td>
                <td style="text-align:right;">
                  <button class="btn btn-primary btn-sm" style="background:#4F46E5; color:#fff;" @click="impersonateUser(u)" title="Se connecter directement en tant que cet utilisateur">
                    🔑 Connexion
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <div v-else class="card" style="text-align:center; padding:2rem;">
      <p class="text-danger">Impossible de charger les données du tenant.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()


const route = useRoute()
const router = useRouter()

const loading = ref(true)
const actionLoading = ref(false)
const tenant = ref(null)
const users = ref([])
const dbSize = ref(0)

onMounted(async () => {
  await fetchTenantDetails()
})

async function fetchTenantDetails() {
  loading.value = true
  try {
    const { data } = await api.get(`/superadmin/tenants/${route.params.id}`)
    tenant.value = data.tenant
    users.value = data.users || []
    dbSize.value = data.db_size_mb || 0
  } catch (err) {
    console.error('Erreur chargement détails tenant:', err)
  } finally {
    loading.value = false
  }
}

async function toggleStatus() {
  if (!tenant.value) return
  actionLoading.value = true
  const newStatus = tenant.value.statut === 'suspendu' ? 'actif' : 'suspendu'
  
  try {
    const { data } = await api.put(`/superadmin/tenants/${tenant.value.id}`, {
      statut: newStatus
    })
    tenant.value = data.tenant
    toast.success(`Le tenant est désormais : ${newStatus === 'suspendu' ? 'suspendu' : 'actif'}.`)
  } catch (err) {
    console.error('Erreur changement statut tenant:', err)
  } finally {
    actionLoading.value = false
  }
}

async function destroyTenant() {
  if (!tenant.value) return
  if (!confirm(`Voulez-vous vraiment supprimer définitivement le tenant "${tenant.value.nom}" ? Cette action est irréversible.`)) {
    return
  }
  
  actionLoading.value = true
  try {
    await api.delete(`/superadmin/tenants/${tenant.value.id}`)
    toast.success('Le tenant a été supprimé avec succès.')
    router.push('/superadmin/tenants')
  } catch (err) {
    console.error('Erreur suppression tenant:', err)
  } finally {
    actionLoading.value = false
  }
}

async function impersonateUser(user) {
  try {
    await authStore.impersonate(user.id)
    toast.success(`Prise de contrôle réussie pour ${user.prenom} ${user.nom}`)
    router.push('/dashboard')
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de la prise de contrôle.')
  }
}

function formatDate(d) {

  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
