<template>
  <div class="animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Commandes Fournisseur</h2>
        <p class="text-sm text-muted">{{ commandes.length }} commande(s)</p>
      </div>
      <router-link to="/commandes/new" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle Commande
      </router-link>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>N° Cmd</th>
            <th>Date</th>
            <th>Fournisseur</th>
            <th>Livraison Prévue</th>
            <th style="text-align:right">Montant TTC</th>
            <th>État</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in commandes" :key="c.id">
             <td><span class="font-mono" style="font-weight:600; color:var(--accent);">{{ c.numero }}</span></td>
             <td>{{ c.date_commande }}</td>
             <td style="font-weight:500;">{{ c.fournisseur }}</td>
             <td>{{ c.date_livraison_prevue || '—' }}</td>
             <td style="text-align:right; font-weight:600;">{{ formatMoney(c.total_ttc) }}</td>
             <td>
               <span class="badge" :class="c.etat === 'Réceptionnée' ? 'badge-success' : 'badge-warning'">{{ c.etat }}</span>
             </td>
             <td>
               <router-link :to="'/commandes/' + c.id" class="btn btn-secondary btn-sm">Détails</router-link>
             </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const commandes = ref([])

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}

onMounted(async () => {
  try {
    const { data } = await api.get('/commandes')
    commandes.value = (data.data || data || []).map(c => ({ ...c, fournisseur: c.fournisseur?.societe, etat: c.etat?.libelle }))
  } catch {
    commandes.value = [
      { id: 1, numero: 'CMD-F-2026-003', date_commande: '2026-04-10', date_livraison_prevue: '2026-04-20', fournisseur: 'DistriTech Maroc', total_ttc: 120000, etat: 'En cours' },
      { id: 2, numero: 'CMD-F-2026-002', date_commande: '2026-03-25', date_livraison_prevue: '2026-04-05', fournisseur: 'ImportGlobal Equipements', total_ttc: 45500, etat: 'Réceptionnée' },
    ]
  }
})
</script>
