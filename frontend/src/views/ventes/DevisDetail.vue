<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <router-link to="/devis" class="btn btn-secondary btn-sm">← Retour</router-link>
        <h2 style="font-size:1.1rem; font-weight:600;">{{ isNew ? 'Nouveau Devis' : 'Détail du Devis ' + form.numero }}</h2>
      </div>
      <div class="flex gap-2">
        <button v-if="!isNew" class="btn btn-secondary" @click="imprimer">Imprimer PDF</button>
        <button class="btn btn-primary" @click="save">Enregistrer</button>
      </div>
    </div>

    <!-- En-tête du devis -->
    <div class="card mb-3">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
          <h3 class="text-sm text-muted mb-2 text-uppercase">Informations Client</h3>
          <div class="form-group">
            <label class="form-label">Client *</label>
            <select v-model="form.client_id" class="form-select">
              <option value="" disabled>Sélectionner un client...</option>
              <option value="1">TechnoPlus SARL</option>
              <option value="2">Digital Factory</option>
              <option value="3">MediaCom Group</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Projet associé (Optionnel)</label>
            <select class="form-select">
              <option value="">Aucun</option>
              <option value="1">PRJ-26-001 - Déploiement Infr...</option>
            </select>
          </div>
        </div>
        <div>
           <h3 class="text-sm text-muted mb-2 text-uppercase">Général</h3>
           <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">N° Devis</label>
                <input v-model="form.numero" type="text" class="form-input font-mono" disabled />
              </div>
              <div class="form-group">
                <label class="form-label">Date</label>
                <input v-model="form.date_devis" type="date" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Validité (jrs)</label>
                <input value="30" type="number" class="form-input" />
              </div>
           </div>
        </div>
      </div>
    </div>

    <!-- Lignes du devis -->
    <div class="card mb-3">
       <div class="flex justify-between items-center mb-2">
         <h3 class="text-sm text-muted text-uppercase">Lignes d'articles</h3>
         <button class="btn btn-secondary btn-sm" @click="addLine">+ Ajouter une ligne</button>
       </div>
       <table class="data-table mb-3">
         <thead>
           <tr>
             <th style="width: 40%">Désignation</th>
             <th style="width: 15%; text-align:right">Qte</th>
             <th style="width: 15%; text-align:right">P.U HT</th>
             <th style="width: 10%; text-align:right">TVA %</th>
             <th style="width: 15%; text-align:right">Total HT</th>
             <th></th>
           </tr>
         </thead>
         <tbody>
           <tr v-for="(ligne, idx) in form.lignes" :key="idx">
             <td style="display: flex; gap: 0.5rem; align-items: center;">
               <select v-model="ligne.produit_id" @change="onProduitSelect(ligne)" class="form-input" style="width: 140px; padding: 0.2rem;">
                 <option value="">Texte libre</option>
                 <option v-for="p in produits" :key="p.id" :value="p.id">{{ p.nom }}</option>
               </select>
               <input v-model="ligne.designation" class="form-input" placeholder="Désignation" style="flex:1;" />
             </td>
             <td><input v-model="ligne.quantite" type="number" class="form-input" style="text-align:right" /></td>
             <td><input v-model="ligne.prix_unitaire" type="number" class="form-input" style="text-align:right" /></td>
             <td><input v-model="ligne.taux_tva" type="number" class="form-input" style="text-align:right" /></td>
             <td style="text-align:right; font-weight:600; padding:0 1rem;">{{ formatMoney(ligne.quantite * ligne.prix_unitaire) }}</td>
             <td>
                <button class="text-danger" style="background:none; border:none; cursor:pointer;" @click="removeLine(idx)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
             </td>
           </tr>
           <tr v-if="form.lignes.length === 0">
             <td colspan="6" class="text-center text-muted">Aucune ligne. Ajoutez un article.</td>
           </tr>
         </tbody>
       </table>

       <!-- Totaux -->
       <div style="display:flex; justify-content:flex-end;">
         <div style="width: 300px; background: var(--bg-primary); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div class="flex justify-between mb-1"><span class="text-muted">Total HT</span> <span>{{ formatMoney(totalHT) }}</span></div>
            <div class="flex justify-between mb-2"><span class="text-muted">TVA</span> <span>{{ formatMoney(totalTVA) }}</span></div>
            <div class="flex justify-between" style="border-top:1px solid var(--border-color); padding-top:0.5rem; font-size:1.1rem; font-weight:700; color:var(--accent);">
               <span>Total TTC</span> <span>{{ formatMoney(totalTTC) }}</span>
            </div>
         </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const route = useRoute()
const isNew = computed(() => route.params.id === 'new')

const form = ref({
  numero: 'Brouillon',
  date_devis: new Date().toISOString().substring(0, 10),
  client_id: '',
  lignes: []
})

const produits = ref([
  { id: 1, nom: 'Prestation de service', prix_vente: 1000, tva: 20 },
  { id: 2, nom: 'Écran LED 27"', prix_vente: 1500, tva: 20 },
  { id: 3, nom: 'Installation sur site', prix_vente: 500, tva: 20 }
])

onMounted(async () => {
  try {
    // Attempt to load real products
    const { data } = await api.get('/produits')
    if (data.data) {
      produits.value = data.data.map(p => ({
        id: p.id,
        nom: p.designation || p.nom,
        prix_vente: parseFloat(p.prix_vente) || 0,
        tva: p.tva || 20
      }))
    }
  } catch {}
})

if (isNew.value) {
  form.value.lignes.push({ designation: 'Prestation de service', quantite: 1, prix_unitaire: 1000, taux_tva: 20 })
} else {
  form.value.numero = 'DEV-202604-0012'
  form.value.client_id = 1
  form.value.lignes = [
    { produit_id: 2, designation: 'Écran LED 27"', quantite: 2, prix_unitaire: 1500, taux_tva: 20 },
    { produit_id: 3, designation: 'Installation', quantite: 1, prix_unitaire: 500, taux_tva: 20 }
  ]
}

function addLine() {
  form.value.lignes.push({ produit_id: '', designation: '', quantite: 1, prix_unitaire: 0, taux_tva: 20 })
}

function onProduitSelect(ligne) {
  if (ligne.produit_id) {
    const prod = produits.value.find(p => p.id === ligne.produit_id)
    if (prod) {
      ligne.designation = prod.nom
      ligne.prix_unitaire = prod.prix_vente
      ligne.taux_tva = prod.tva
    }
  }
}

function removeLine(idx) {
  form.value.lignes.splice(idx, 1)
}

const totalHT = computed(() => form.value.lignes.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire), 0))
const totalTVA = computed(() => form.value.lignes.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire * (l.taux_tva/100)), 0))
const totalTTC = computed(() => totalHT.value + totalTVA.value)

function formatMoney(val) { return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH' }

function save() {
  alert('Devis enregistré avec succès (Mode Démo)')
  router.push('/devis')
}

function imprimer() {
  window.open(`/print/devis/${form.value.numero}`, '_blank')
}
</script>
