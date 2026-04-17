<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/superadmin/users" class="btn btn-secondary btn-sm" style="padding: 0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <h2 style="font-size:1.2rem; font-weight:600;">
          {{ isEdit ? 'Editer un utilisateur global' : 'Nouvel utilisateur global' }}
        </h2>
      </div>
      <div>
        <button class="btn btn-primary" @click="saveUser">
          {{ isEdit ? 'Appliquer les modifications' : 'Créer l\'utilisateur' }}
        </button>
      </div>
    </div>

    <div class="card max-w-3xl">
      <h3 class="mb-3 text-warning">Informations du compte Central</h3>
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
        
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input v-model="form.nom" class="form-input" placeholder="Dupont" />
        </div>
        
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input v-model="form.prenom" class="form-input" placeholder="Jean" />
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label class="form-label">Adresse Email (Authentification Unique)</label>
          <input v-model="form.email" type="email" class="form-input" placeholder="jean.dupont@email.com" />
          <p class="text-sm text-muted mt-1">Cet e-mail lui permettra de se connecter sur l'ensemble de l'écosystème selon les droits qui lui seront accordés.</p>
        </div>

        <div class="form-group">
          <label class="form-label">Mot de passe {{ isEdit ? '(Optionnel)' : '' }}</label>
          <input v-model="form.password" type="password" class="form-input" placeholder="••••••••" />
        </div>

        <div class="form-group">
          <label class="form-label">Privilège SuperAdmin</label>
          <select v-model="form.is_superadmin" class="form-input">
            <option :value="false">OUI</option> <!-- Faking a visual boolean choice for the UI demo -->
            <option :value="false">NON - Simple Accès aux Bases Client</option>
            <option :value="true">OUI - Plein Accès au Portail SuperAdmin</option>
          </select>
        </div>
        
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const isEdit = ref(false)

const form = ref({
  nom: '',
  prenom: '',
  email: '',
  password: '',
  is_superadmin: false
})

onMounted(() => {
  if (route.params.id) {
    isEdit.value = true
    // Simulation:
    form.value.nom = 'GenyCom'
    form.value.prenom = 'Admin'
    form.value.email = 'admin@genycom.ma'
    form.value.is_superadmin = false
  }
})

function saveUser() {
  alert('Requête POST/PUT envoyée à "/api/superadmin/users".')
  router.push('/superadmin/users')
}
</script>
