<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link to="/stock" class="btn btn-secondary btn-sm" style="padding: 0.4rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </router-link>
        <div>
          <h2 style="font-size:1.2rem; font-weight:600;">Historique du Stock</h2>
          <p class="text-sm text-muted">Produit associé : Ligne de stock #{{ route.params.id }}</p>
        </div>
      </div>
      <div>
        <button class="btn btn-secondary">Exporter CSV</button>
      </div>
    </div>

    <!-- Mouvements Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Réf. Document</th>
            <th>Libellé / Description</th>
            <th style="text-align:right">Qté Mouvement</th>
            <th>Acteur</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(mvt, idx) in mouvements" :key="idx">
             <td>{{ mvt.date_mouvement }}</td>
             <td>
               <span class="badge" :class="getTypeBadge(mvt.type)">{{ mvt.type }}</span>
             </td>
             <td class="font-mono text-muted text-sm">{{ mvt.ref_document || '—' }}</td>
             <td style="font-weight:500;">{{ mvt.libelle }}</td>
             <td style="text-align:right; font-weight:600;" :style="{ color: mvt.quantite > 0 ? 'var(--success)' : (mvt.quantite < 0 ? 'var(--danger)' : '') }">
               {{ mvt.quantite > 0 ? '+' : '' }}{{ mvt.quantite }}
             </td>
             <td class="text-muted text-sm">{{ mvt.auteur }}</td>
          </tr>
          <tr v-if="mouvements.length === 0">
             <td colspan="6" class="text-center text-muted">Aucun mouvement trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Mock data representing full history of inputs/outputs including Avoirs
const mouvements = ref([
  { date_mouvement: '2026-04-18 10:30', type: 'Sortie (Vente)', ref_document: 'FAC-202604-0012', libelle: 'Livraison Client TechS', quantite: -5, auteur: 'Admin' },
  { date_mouvement: '2026-04-15 14:00', type: 'Entrée (Avoir Client)', ref_document: 'AVR-2026-0001', libelle: 'Retour marchandise défectueuse', quantite: 2, auteur: 'Admin' },
  { date_mouvement: '2026-04-10 09:15', type: 'Sortie (Avoir FRS)', ref_document: 'AVF-2026-003', libelle: 'Renvoi fournisseur sous garantie', quantite: -2, auteur: 'Admin' },
  { date_mouvement: '2026-04-05 16:45', type: 'Entrée (Achat)', ref_document: 'REC-202604-001', libelle: 'Réception commande DistriTech', quantite: 50, auteur: 'Superviseur' },
  { date_mouvement: '2026-03-01 08:00', type: 'Inventaire Initial', ref_document: 'INV-202603-001', libelle: 'Ouverture de stock', quantite: 100, auteur: 'Superviseur' }
])

function getTypeBadge(type) {
  if (type.includes('Entrée')) return 'badge-success'
  if (type.includes('Sortie')) return 'badge-danger'
  return 'badge-info'
}
</script>
