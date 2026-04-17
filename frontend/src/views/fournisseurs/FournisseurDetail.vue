<template>
  <div class="animate-fade-in">
    <div class="flex items-center gap-2 mb-3">
      <router-link to="/fournisseurs" class="btn btn-secondary btn-sm">← Retour</router-link>
      <h2 style="font-size:1.1rem; font-weight:600;">Détail Fournisseur : {{ fournisseur.societe }}</h2>
    </div>

    <!-- KPI Fournisseur -->
    <div class="kpi-grid mb-3">
      <div class="kpi-card">
        <div class="kpi-label">Volume d'Achats (HT)</div>
        <div class="kpi-value">385 200,00 DH</div>
        <div class="kpi-sub">Depuis le début de l'année</div>
        <div class="kpi-icon info"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
      </div>
      <div class="kpi-card danger">
        <div class="kpi-label">Dettes d'Exploitation</div>
        <div class="kpi-value text-danger">15 400,00 DH</div>
        <div class="kpi-sub">Factures Non Réglées</div>
        <div class="kpi-icon danger"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
      </div>
      <div class="kpi-card warning">
        <div class="kpi-label">Commandes en Cours</div>
        <div class="kpi-value">2</div>
        <div class="kpi-sub">Attente de réception</div>
        <div class="kpi-icon warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
      </div>
    </div>

    <!-- Informations & Contact -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
      <div class="card">
         <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Profil Achat</h3>
         <div style="display:grid; grid-template-columns: 120px 1fr; gap:0.5rem; line-height:1.6;">
            <div class="text-muted">Réf interne</div><div class="font-mono">{{ fournisseur.code_fournisseur }}</div>
            <div class="text-muted">Catégorie</div><div><span class="badge badge-default">Grossiste IT</span></div>
            <div class="text-muted">ICE</div><div>{{ fournisseur.ice || 'Non renseigné' }}</div>
            <div class="text-muted">Paiement usuel</div><div>Virement Bancaire (45 Jours)</div>
         </div>
      </div>
      <div class="card">
         <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Coordonnées</h3>
         <div style="display:grid; grid-template-columns: 80px 1fr; gap:0.5rem; line-height:1.6;">
            <div class="text-muted">Contact</div><div>Mme. Sarah Alaoui</div>
            <div class="text-muted">Téléphone</div><div>{{ fournisseur.telephone }}</div>
            <div class="text-muted">Email</div><div><a href="#" style="color:var(--info);">{{ fournisseur.email }}</a></div>
            <div class="text-muted">Siège</div><div>Technopark, Casablanca</div>
         </div>
         <div class="mt-3 flex gap-2">
           <button class="btn btn-primary btn-sm">Editer la fiche</button>
           <button class="btn btn-secondary btn-sm" style="border-color:var(--danger); color:var(--danger)">Bloquer (Contentieux)</button>
         </div>
       </div>
    </div>

    <!-- Historique -->
    <div class="card">
       <div class="flex items-center justify-between mb-2">
           <h3 class="text-warning" style="font-size:1rem; font-weight:600;">Commandes & Approvisionnements</h3>
           <button class="btn btn-primary btn-sm">Passer une commande</button>
       </div>
       <table class="data-table">
          <thead>
            <tr>
              <th>Réf. B.C</th>
              <th>Date de Demande</th>
              <th style="text-align:right">Montant Exigé</th>
              <th>État de Livraison</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="font-mono">BC-2026-042</td>
              <td>12 Avr 2026</td>
              <td style="text-align:right">15 400,00 DH</td>
              <td><span class="badge badge-warning">En transit</span></td>
            </tr>
            <tr>
              <td class="font-mono">BC-2026-011</td>
              <td>03 Fév 2026</td>
              <td style="text-align:right">110 000,00 DH</td>
              <td><span class="badge badge-success">Réceptionné en totalité</span></td>
            </tr>
          </tbody>
        </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({ id: [String, Number] })

const fournisseur = ref({
  code_fournisseur: 'FRN-001',
  societe: 'DistriTech Maroc',
  telephone: '0522-998877',
  email: 'contact@distritech.ma',
  ice: '003344556677889'
})

onMounted(() => {
  // api.get(`/fournisseurs/${props.id}`)
})
</script>
