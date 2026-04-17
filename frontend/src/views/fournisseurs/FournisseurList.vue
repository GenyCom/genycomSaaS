<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Liste des Fournisseurs</h2>
        <p class="text-sm text-muted">{{ fournisseurs.length }} fournisseur(s) actif(s)</p>
      </div>
      <router-link to="/fournisseurs/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau Fournisseur
      </router-link>
    </div>

    <!-- Search -->
    <div class="card" style="padding: 1rem; margin-bottom: 1rem;">
      <input v-model="search" class="form-input" placeholder="Rechercher société, nom, contact..." style="max-width: 400px;" />
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Société / Nom</th>
            <th>Ville</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Type</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="frn in filteredFournisseurs" :key="frn.id">
            <td><span class="font-mono text-sm">{{ frn.code_fournisseur || '—' }}</span></td>
            <td style="font-weight:500; color:var(--text-primary);">{{ frn.societe }}</td>
            <td>{{ frn.ville || '—' }}</td>
            <td>{{ frn.telephone || frn.mobile || '—' }}</td>
            <td>{{ frn.email || '—' }}</td>
            <td><span class="badge badge-default">{{ frn.type_fournisseur?.libelle || 'Standard' }}</span></td>
            <td>
              <router-link :to="`/fournisseurs/${frn.id}`" class="btn btn-secondary btn-sm">Détails</router-link>
            </td>
          </tr>
          <tr v-if="filteredFournisseurs.length === 0">
            <td colspan="7" class="text-center text-muted" style="padding:2rem;">Aucun fournisseur trouvé</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const fournisseurs = ref([])
const search = ref('')

const filteredFournisseurs = computed(() => {
  if (!search.value) return fournisseurs.value
  const s = search.value.toLowerCase()
  return fournisseurs.value.filter(f =>
    (f.societe || '').toLowerCase().includes(s) ||
    (f.code_fournisseur || '').toLowerCase().includes(s) ||
    (f.email || '').toLowerCase().includes(s)
  )
})

onMounted(async () => {
  try {
    const { data } = await api.get('/fournisseurs')
    fournisseurs.value = data.data || data || []
  } catch {
    fournisseurs.value = [
      { id: 1, code_fournisseur: 'FRN-001', societe: 'DistriTech Maroc', ville: 'Casablanca', telephone: '0522-998877', email: 'contact@distritech.ma', type_fournisseur: { libelle: 'Grossiste IT' } },
      { id: 2, code_fournisseur: 'FRN-002', societe: 'ImportGlobal Equipements', ville: 'Tanger', telephone: '0539-112233', email: 'sales@importglobal.com', type_fournisseur: { libelle: 'Importateur' } },
    ]
  }
})
</script>
