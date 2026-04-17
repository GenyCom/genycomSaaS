<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Avoirs Fournisseurs</h2>
        <p class="text-sm text-muted">Retours marchandises & avoirs reçus</p>
      </div>
      <router-link to="/avoirs-fournisseurs/new" class="btn btn-primary">Nouvel Avoir Fournisseur</router-link>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>N° Avoir</th>
            <th>Date</th>
            <th>Fournisseur</th>
            <th>Réf. Commande</th>
            <th style="text-align:right">Total HT</th>
            <th>État</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="avoirs.length === 0">
             <td colspan="7" class="text-center text-muted">Aucun avoir fournisseur enregistré.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const avoirs = ref([])
</script>
