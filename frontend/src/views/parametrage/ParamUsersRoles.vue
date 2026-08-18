<template>
  <div class="param-users-roles">
    <div v-if="toast.show" class="toast-notification" :class="toast.type">{{ toast.message }}</div>

    <!-- En-tête avec sous-onglets -->
    <div class="inner-tabs-container">
      <button class="inner-tab" :class="{ active: subTab === 'users' }" @click="subTab = 'users'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Sous-comptes Utilisateurs ({{ users.length }})</span>
      </button>
      <button class="inner-tab" :class="{ active: subTab === 'roles' }" @click="subTab = 'roles'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span>Rôles & Habilitations ({{ roles.length }})</span>
      </button>
    </div>

    <!-- ───────────── ONGLET 1: SOUS-COMPTES UTILISATEURS ───────────── -->
    <div v-if="subTab === 'users'" class="tab-content">
      <div class="section-toolbar">
        <div class="search-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="searchUser" type="text" placeholder="Rechercher par nom, prénom ou email..." />
        </div>
        <button class="btn-primary" @click="openAddUserModal">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Nouveau Sous-Compte</span>
        </button>
      </div>

      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Rôle Attribué</th>
              <th>Statut</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loadingUsers">
              <td colspan="6" class="text-center py-4">Chargement des utilisateurs...</td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td colspan="6" class="text-center py-4 text-muted">Aucun sous-compte trouvé.</td>
            </tr>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>
                <div class="user-identity">
                  <div class="avatar-circle" :class="{ 'owner-avatar': user.is_owner }">
                    {{ (user.prenom?.[0] || '') + (user.nom?.[0] || '') }}
                  </div>
                  <div>
                    <div class="user-name">
                      {{ user.prenom }} {{ user.nom }}
                      <span v-if="user.is_owner" class="badge-owner">Gérant Principal</span>
                    </div>
                  </div>
                </div>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ user.telephone || '—' }}</td>
              <td>
                <span class="role-badge" :class="getRoleBadgeClass(user.role_name)">
                  {{ user.role_name || 'Utilisateur' }}
                </span>
              </td>
              <td>
                <span class="status-pill" :class="user.is_active ? 'active' : 'inactive'">
                  {{ user.is_active ? 'Actif' : 'Désactivé' }}
                </span>
              </td>
              <td style="text-align: right;">
                <div class="action-buttons">
                  <button class="btn-icon" title="Modifier" @click="editUser(user)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="btn-icon" title="Changer le mot de passe" @click="openPasswordModal(user)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </button>
                  <button class="btn-icon danger" title="Supprimer" :disabled="user.is_owner" @click="confirmDeleteUser(user)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ───────────── ONGLET 2: RÔLES & HABILITATIONS ───────────── -->
    <div v-if="subTab === 'roles'" class="tab-content">
      <div class="section-toolbar">
        <p class="section-subtext">Définissez les rôles et attribuez des habilitations granulaires pour chaque module de votre SaaS.</p>
        <button class="btn-primary" @click="openAddRoleModal">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Nouveau Rôle</span>
        </button>
      </div>

      <div class="roles-grid">
        <div v-for="role in roles" :key="role.id" class="role-card">
          <div class="role-header">
            <div>
              <h4 class="role-title">
                {{ role.name }}
                <span v-if="role.is_system" class="system-badge">Système</span>
              </h4>
              <p class="role-desc">{{ role.description || 'Aucune description' }}</p>
            </div>
          </div>

          <div class="role-body">
            <div class="permissions-count">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <span>{{ role.permission_ids.length }} permission(s) active(s)</span>
            </div>
          </div>

          <div class="role-footer">
            <button class="btn-secondary-sm" @click="openEditRoleModal(role)">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              <span>Éditer les habilitations</span>
            </button>
            <button v-if="!role.is_system" class="btn-danger-sm" title="Supprimer" @click="confirmDeleteRole(role)">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ───────────── MODALE USER (CRÉATION / ÉDITION) ───────────── -->
    <div v-if="userModal.show" class="modal-overlay" @click.self="userModal.show = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ userModal.isEdit ? 'Modifier le sous-compte' : 'Nouveau sous-compte' }}</h3>
          <button class="close-btn" @click="userModal.show = false">&times;</button>
        </div>
        <form @submit.prevent="saveUser" class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>Nom *</label>
              <input v-model="userForm.nom" type="text" required placeholder="Nom de famille" />
            </div>
            <div class="form-group">
              <label>Prénom *</label>
              <input v-model="userForm.prenom" type="text" required placeholder="Prénom" />
            </div>
          </div>
          <div class="form-group">
            <label>Adresse Email *</label>
            <input v-model="userForm.email" type="email" required :disabled="userModal.isEdit" placeholder="email@exemple.com" />
          </div>
          <div v-if="!userModal.isEdit" class="form-group">
            <label>Mot de passe initial *</label>
            <div class="password-wrapper">
              <input v-model="userForm.password" :type="showUserPassword ? 'text' : 'password'" required minlength="6" placeholder="Minimum 6 caractères" />
              <button type="button" class="toggle-password" @click="showUserPassword = !showUserPassword" tabindex="-1" title="Afficher / Masquer">
                <svg v-if="!showUserPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone</label>
              <input v-model="userForm.telephone" type="text" placeholder="+212 6..." />
            </div>
            <div class="form-group">
              <label>Rôle Attribué *</label>
              <select v-model="userForm.role_id" required>
                <option value="" disabled>Sélectionnez un rôle</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
            </div>
          </div>
          <div class="form-group" v-if="userModal.isEdit">
            <label class="checkbox-label">
              <input type="checkbox" v-model="userForm.is_active" :disabled="userForm.is_owner" />
              <span>Compte Actif (Autoriser l'accès)</span>
            </label>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="userModal.show = false">Annuler</button>
            <button type="submit" class="btn-submit" :disabled="savingUser">
              {{ savingUser ? 'Enregistrement...' : 'Sauvegarder' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ───────────── MODALE PASSWORDR (RESET) ───────────── -->
    <div v-if="pwdModal.show" class="modal-overlay" @click.self="pwdModal.show = false">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h3>Réinitialiser le mot de passe</h3>
          <button class="close-btn" @click="pwdModal.show = false">&times;</button>
        </div>
        <form @submit.prevent="savePassword" class="modal-body">
          <p class="text-sm text-muted">Changement de mot de passe pour <strong>{{ pwdModal.userName }}</strong></p>
          <div class="form-group mt-3">
            <label>Nouveau mot de passe *</label>
            <div class="password-wrapper">
              <input v-model="pwdModal.password" :type="showPwdModalPassword ? 'text' : 'password'" required minlength="6" placeholder="Minimum 6 caractères" />
              <button type="button" class="toggle-password" @click="showPwdModalPassword = !showPwdModalPassword" tabindex="-1" title="Afficher / Masquer">
                <svg v-if="!showPwdModalPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <div class="modal-footer mt-4">
            <button type="button" class="btn-cancel" @click="pwdModal.show = false">Annuler</button>
            <button type="submit" class="btn-submit" :disabled="pwdModal.loading">
              {{ pwdModal.loading ? 'Mise à jour...' : 'Valider' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ───────────── MODALE RÔLE & PERMISSIONS ───────────── -->
    <div v-if="roleModal.show" class="modal-overlay" @click.self="roleModal.show = false">
      <div class="modal-card modal-lg">
        <div class="modal-header">
          <h3>{{ roleModal.isEdit ? 'Éditer le rôle & les habilitations' : 'Créer un rôle sur-mesure' }}</h3>
          <button class="close-btn" @click="roleModal.show = false">&times;</button>
        </div>
        <form @submit.prevent="saveRole" class="modal-body">
          <div class="form-row">
            <div class="form-group" style="flex: 2;">
              <label>Nom du Rôle *</label>
              <input v-model="roleForm.name" type="text" required :disabled="roleForm.is_system" placeholder="Ex: Commercial Senior, Magasinier..." />
            </div>
            <div class="form-group" style="flex: 3;">
              <label>Description</label>
              <input v-model="roleForm.description" type="text" placeholder="Description de l'usage du rôle..." />
            </div>
          </div>

          <div class="permissions-section">
            <div class="permissions-header">
              <h4>Habilitations & Droits d'accès</h4>
              <div class="perm-global-actions">
                <button type="button" class="btn-text" @click="selectAllPermissions">Tout cocher</button>
                <span class="sep">|</span>
                <button type="button" class="btn-text" @click="unselectAllPermissions">Tout décocher</button>
              </div>
            </div>

            <div class="modules-grid">
              <div v-for="(perms, moduleName) in groupedPermissions" :key="moduleName" class="module-box">
                <div class="module-box-header">
                  <h5>{{ getModuleLabel(moduleName) }}</h5>
                  <button type="button" class="btn-text-sm" @click="toggleModule(perms)">
                    {{ isModuleAllSelected(perms) ? 'Décocher' : 'Cocher tout' }}
                  </button>
                </div>
                <div class="module-perms-list">
                  <label v-for="p in perms" :key="p.id" class="perm-checkbox-item">
                    <input type="checkbox" :value="p.id" v-model="roleForm.permissions" />
                    <span>{{ p.display_name || p.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="roleModal.show = false">Annuler</button>
            <button type="submit" class="btn-submit" :disabled="savingRole">
              {{ savingRole ? 'Enregistrement...' : 'Sauvegarder le rôle' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const subTab = ref('users')

// Feedback toast
const toast = ref({ show: false, message: '', type: 'success' })
function showToast(m, t = 'success') {
  toast.value = { show: true, message: m, type: t }
  setTimeout(() => { toast.value.show = false }, 4000)
}

// Data Lists
const users = ref([])
const roles = ref([])
const groupedPermissions = ref({})
const allPermissions = ref([])
const loadingUsers = ref(false)
const searchUser = ref('')

// Modal States
const userModal = ref({ show: false, isEdit: false })
const userForm = ref({ id: null, nom: '', prenom: '', email: '', password: '', telephone: '', role_id: '', is_active: true, is_owner: false })
const savingUser = ref(false)
const showUserPassword = ref(false)

const pwdModal = ref({ show: false, userId: null, userName: '', password: '', loading: false })
const showPwdModalPassword = ref(false)

const roleModal = ref({ show: false, isEdit: false })

const roleForm = ref({ id: null, name: '', description: '', is_system: false, permissions: [] })
const savingRole = ref(false)

onMounted(async () => {
  await Promise.all([loadUsers(), loadRoles(), loadPermissions()])
})

// Computeds
const filteredUsers = computed(() => {
  if (!searchUser.value.trim()) return users.value
  const q = searchUser.value.toLowerCase()
  return users.value.filter(u => 
    (u.nom && u.nom.toLowerCase().includes(q)) ||
    (u.prenom && u.prenom.toLowerCase().includes(q)) ||
    (u.email && u.email.toLowerCase().includes(q))
  )
})

// Loaders
async function loadUsers() {
  loadingUsers.value = true
  try {
    const { data } = await api.get('/parametrage/users')
    users.value = data
  } catch (err) {
    showToast('Erreur chargement des sous-comptes.', 'error')
  } finally {
    loadingUsers.value = false
  }
}

async function loadRoles() {
  try {
    const { data } = await api.get('/parametrage/roles')
    roles.value = data
  } catch (err) {
    showToast('Erreur chargement des rôles.', 'error')
  }
}

async function loadPermissions() {
  try {
    const { data } = await api.get('/parametrage/permissions')
    groupedPermissions.value = data.grouped
    allPermissions.value = data.all
  } catch (err) {
    console.error('Erreur chargement des permissions:', err)
  }
}

// Helpers
function getRoleBadgeClass(roleName) {
  if (!roleName) return 'default'
  const name = roleName.toLowerCase()
  if (name.includes('admin')) return 'admin'
  if (name.includes('commercial')) return 'commercial'
  if (name.includes('comptable')) return 'comptable'
  return 'default'
}

function getModuleLabel(moduleName) {
  const labels = {
    dashboard: 'Tableau de Bord',
    clients: 'Gestion Clients',
    fournisseurs: 'Gestion Fournisseurs',
    produits: 'Catalogue Produits',
    ventes: 'Ventes (Devis / Factures / BL)',
    achats: 'Achats (Commandes / BR / Factures)',
    stock: 'Stock & Inventaires',
    finances: 'Finances & Règlements',
    projets: 'Gestion Projets',
    parametrage: 'Paramétrage & Utilisateurs',
  }
  return labels[moduleName] || moduleName.toUpperCase()
}

// Users Handlers
function openAddUserModal() {
  userForm.value = { id: null, nom: '', prenom: '', email: '', password: '', telephone: '', role_id: roles.value[0]?.id || '', is_active: true, is_owner: false }
  showUserPassword.value = false
  userModal.value = { show: true, isEdit: false }
}

function editUser(user) {
  userForm.value = { ...user }
  userModal.value = { show: true, isEdit: true }
}

async function saveUser() {
  savingUser.value = true
  try {
    if (userModal.value.isEdit) {
      await api.put(`/parametrage/users/${userForm.value.id}`, userForm.value)
      showToast('Sous-compte mis à jour avec succès !')
    } else {
      await api.post('/parametrage/users', userForm.value)
      showToast('Nouveau sous-compte créé avec succès !')
    }
    userModal.value.show = false
    await loadUsers()
  } catch (err) {
    showToast(err.response?.data?.errors?.email?.[0] || err.response?.data?.message || 'Erreur d\'enregistrement', 'error')
  } finally {
    savingUser.value = false
  }
}

function openPasswordModal(user) {
  showPwdModalPassword.value = false
  pwdModal.value = { show: true, userId: user.id, userName: `${user.prenom} ${user.nom}`, password: '', loading: false }
}

async function savePassword() {
  pwdModal.value.loading = true
  try {
    await api.put(`/parametrage/users/${pwdModal.value.userId}/password`, { password: pwdModal.value.password })
    showToast('Mot de passe réinitialisé avec succès !')
    pwdModal.value.show = false
  } catch (err) {
    showToast(err.response?.data?.message || 'Erreur lors du changement de mot de passe.', 'error')
  } finally {
    pwdModal.value.loading = false
  }
}

async function confirmDeleteUser(user) {
  if (confirm(`Voulez-vous vraiment détacher l'utilisateur ${user.prenom} ${user.nom} de votre entreprise ?`)) {
    try {
      await api.delete(`/parametrage/users/${user.id}`)
      showToast('Sous-compte détaché avec succès.')
      await loadUsers()
    } catch (err) {
      showToast(err.response?.data?.message || 'Erreur lors de la suppression.', 'error')
    }
  }
}

// Roles Handlers
function openAddRoleModal() {
  roleForm.value = { id: null, name: '', description: '', is_system: false, permissions: [] }
  roleModal.value = { show: true, isEdit: false }
}

function openEditRoleModal(role) {
  roleForm.value = {
    id: role.id,
    name: role.name,
    description: role.description,
    is_system: role.is_system,
    permissions: [...role.permission_ids]
  }
  roleModal.value = { show: true, isEdit: true }
}

function selectAllPermissions() {
  roleForm.value.permissions = allPermissions.value.map(p => p.id)
}

function unselectAllPermissions() {
  roleForm.value.permissions = []
}

function isModuleAllSelected(perms) {
  return perms.every(p => roleForm.value.permissions.includes(p.id))
}

function toggleModule(perms) {
  const ids = perms.map(p => p.id)
  if (isModuleAllSelected(perms)) {
    roleForm.value.permissions = roleForm.value.permissions.filter(id => !ids.includes(id))
  } else {
    roleForm.value.permissions = Array.from(new Set([...roleForm.value.permissions, ...ids]))
  }
}

async function saveRole() {
  savingRole.value = true
  try {
    if (roleModal.value.isEdit) {
      await api.put(`/parametrage/roles/${roleForm.value.id}`, roleForm.value)
      showToast('Rôle et habilitations mis à jour avec succès !')
    } else {
      await api.post('/parametrage/roles', roleForm.value)
      showToast('Nouveau rôle créé avec succès !')
    }
    roleModal.value.show = false
    await loadRoles()
  } catch (err) {
    showToast(err.response?.data?.message || 'Erreur lors de la sauvegarde du rôle.', 'error')
  } finally {
    savingRole.value = false
  }
}

async function confirmDeleteRole(role) {
  if (confirm(`Voulez-vous vraiment supprimer le rôle "${role.name}" ?`)) {
    try {
      await api.delete(`/parametrage/roles/${role.id}`)
      showToast('Rôle supprimé avec succès.')
      await loadRoles()
    } catch (err) {
      showToast(err.response?.data?.message || 'Impossible de supprimer ce rôle.', 'error')
    }
  }
}
</script>

<style scoped>
.param-users-roles {
  display: flex; flex-direction: column; gap: 20px;
}

/* Sub tabs */
.inner-tabs-container {
  display: flex; gap: 12px; border-bottom: 1px solid #E8EAEE; padding-bottom: 8px;
}
.inner-tab {
  display: flex; align-items: center; gap: 8px; background: transparent; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; color: #6B7280; cursor: pointer; transition: all 0.2s;
}
.inner-tab:hover { background: #F3F4F6; color: #1A1D23; }
.inner-tab.active { background: #EEF2FF; color: #4F46E5; }

/* Toolbar */
.section-toolbar {
  display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px;
}
.search-box {
  display: flex; align-items: center; gap: 8px; background: #fff; border: 1.5px solid #D5D9E2; border-radius: 8px; padding: 8px 14px; width: 320px;
}
.search-box input { border: none; outline: none; padding: 0; width: 100%; font-size: 0.85rem; }
.section-subtext { color: #6B7280; font-size: 0.85rem; margin: 0; }

.btn-primary {
  display: flex; align-items: center; gap: 8px; background: #4F46E5; color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: transform 0.15s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}
.btn-primary:hover { transform: translateY(-1px); }

/* Table Card */
.table-card { background: #fff; border: 1px solid #E8EAEE; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
.data-table th { background: #F9FAFB; padding: 12px 16px; font-size: 0.72rem; font-weight: 700; color: #6B7280; text-transform: uppercase; border-bottom: 1px solid #E8EAEE; }
.data-table td { padding: 14px 16px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }

/* User identity */
.user-identity { display: flex; align-items: center; gap: 12px; }
.avatar-circle { width: 36px; height: 36px; border-radius: 50%; background: #EEF2FF; color: #4F46E5; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; text-transform: uppercase; }
.avatar-circle.owner-avatar { background: #FEF3C7; color: #D97706; }
.user-name { font-weight: 700; color: #1A1D23; display: flex; align-items: center; gap: 6px; }
.badge-owner { font-size: 0.65rem; background: #FEF3C7; color: #B45309; padding: 2px 6px; border-radius: 4px; font-weight: 700; }

/* Roles Badges */
.role-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #F3F4F6; color: #4B5563; }
.role-badge.admin { background: #FEE2E2; color: #DC2626; }
.role-badge.commercial { background: #E0E7FF; color: #4338CA; }
.role-badge.comptable { background: #D1FAE5; color: #047857; }

/* Status pill */
.status-pill { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 0.72rem; font-weight: 700; }
.status-pill.active { background: #D1FAE5; color: #065F46; }
.status-pill.inactive { background: #F3F4F6; color: #9CA3AF; }

/* Action buttons */
.action-buttons { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
.btn-icon { width: 32px; height: 32px; border-radius: 6px; border: 1px solid #E8EAEE; background: #fff; color: #4B5563; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; }
.btn-icon:hover:not(:disabled) { background: #F9FAFB; border-color: #D5D9E2; color: #1A1D23; }
.btn-icon.danger:hover:not(:disabled) { background: #FEE2E2; border-color: #FCA5A5; color: #DC2626; }
.btn-icon:disabled { opacity: 0.4; cursor: not-allowed; }

/* Roles Grid */
.roles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.role-card { background: #fff; border: 1px solid #E8EAEE; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.role-title { margin: 0 0 4px; font-size: 1.05rem; font-weight: 700; color: #1A1D23; display: flex; align-items: center; justify-content: space-between; }
.system-badge { font-size: 0.65rem; background: #E5E7EB; color: #4B5563; padding: 2px 6px; border-radius: 4px; font-weight: 600; text-transform: uppercase; }
.role-desc { font-size: 0.8rem; color: #6B7280; margin: 0; }
.permissions-count { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; font-weight: 600; color: #4F46E5; }
.role-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; pt: 10px; border-top: 1px solid #F3F4F6; }
.btn-secondary-sm { display: flex; align-items: center; gap: 6px; background: #F3F4F6; color: #374151; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.78rem; cursor: pointer; }
.btn-secondary-sm:hover { background: #E5E7EB; }
.btn-danger-sm { background: #FEE2E2; color: #DC2626; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; }
.btn-danger-sm:hover { background: #FCA5A5; }

/* Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(6px);
  padding: 16px;
}

.modal-card {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 560px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  border: 1px solid #E8EAEE;
  animation: modalScale 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalScale {
  from { opacity: 0; transform: scale(0.95) translateY(6px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-card.modal-sm { max-width: 420px; }
.modal-card.modal-lg { max-width: 860px; }

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  background: linear-gradient(135deg, #FAFAFC, #F3F4F8);
  border-bottom: 1px solid #E8EAEE;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #1A1D23;
  letter-spacing: -0.01em;
}

.close-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #EFEFF4;
  border: 1px solid #E2E4E9;
  font-size: 1.25rem;
  color: #64748B;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  line-height: 1;
}

.close-btn:hover {
  background: #FEE2E2;
  color: #EF4444;
  border-color: #FCA5A5;
}

.modal-body {
  padding: 24px;
  max-height: 82vh;
  overflow-y: auto;
}

.form-row {
  display: flex;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
  flex: 1;
}

.form-group label {
  font-size: 0.72rem;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.password-wrapper {
  position: relative;
  width: 100%;
}

.password-wrapper input {
  padding-right: 44px !important;
}

.toggle-password {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #94A3B8;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.toggle-password:hover {
  color: #4F46E5;
  background: #EEF2FF;
}

.modal-body input[type="text"],
.modal-body input[type="email"],
.modal-body input[type="password"],
.modal-body select {

  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid #CBD5E1;
  border-radius: 9px;
  font-size: 0.9rem;
  background: #FFFFFF;
  color: #1E293B;
  font-family: inherit;
  transition: all 0.2s ease;
  outline: none;
  box-sizing: border-box;
}

.modal-body input:focus,
.modal-body select:focus {
  border-color: #4F46E5;
  box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.14);
}

.modal-body input:disabled,
.modal-body select:disabled {
  background: #F1F5F9;
  color: #94A3B8;
  cursor: not-allowed;
  border-color: #E2E8F0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  font-weight: 600;
  color: #1E293B;
  cursor: pointer;
  text-transform: none !important;
  user-select: none;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #4F46E5;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #F1F5F9;
}

.btn-cancel {
  background: #F1F5F9;
  color: #475569;
  border: 1px solid #E2E8F0;
  padding: 10px 20px;
  border-radius: 9px;
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: #E2E8F0;
  color: #0F172A;
}

.btn-submit {
  background: linear-gradient(135deg, #4F46E5, #6366F1);
  color: #ffffff;
  border: none;
  padding: 10px 24px;
  border-radius: 9px;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
  transition: all 0.2s ease;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}



/* Permissions Matrix */
.permissions-section { margin-top: 16px; border-top: 1px solid #E8EAEE; pt: 16px; }
.permissions-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.permissions-header h4 { margin: 0; font-size: 0.95rem; font-weight: 700; color: #1A1D23; }
.perm-global-actions { font-size: 0.78rem; display: flex; align-items: center; gap: 6px; }
.btn-text { background: none; border: none; color: #4F46E5; font-weight: 600; cursor: pointer; padding: 0; }
.btn-text-sm { background: none; border: none; color: #6366F1; font-weight: 600; font-size: 0.72rem; cursor: pointer; }
.sep { color: #D1D5DB; }

.modules-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 14px; }
.module-box { border: 1px solid #E8EAEE; border-radius: 10px; padding: 12px; background: #F9FAFB; }
.module-box-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid #E5E7EB; padding-bottom: 6px; }
.module-box-header h5 { margin: 0; font-size: 0.82rem; font-weight: 700; color: #374151; }
.module-perms-list { display: flex; flex-direction: column; gap: 6px; }
.perm-checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #4B5563; cursor: pointer; }

/* Toast */
.toast-notification { position: fixed; top: 1.5rem; right: 1.5rem; padding: 1rem 1.5rem; border-radius: 10px; z-index: 9999; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-weight: 600; font-size: 0.9rem; }
.toast-notification.success { background: #10B981; color: #fff; }
.toast-notification.error { background: #EF4444; color: #fff; }
</style>
