<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <router-link to="/commandes" class="btn btn-secondary btn-sm">← Retour</router-link>
        <h2 style="font-size:1.1rem; font-weight:600;">Commande d'Achat (Brouillon)</h2>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-primary" @click="save">Enregistrer</button>
      </div>
    </div>

    <!-- En-tête -->
    <div class="card mb-3">
      <h3 class="text-sm text-muted mb-2 text-uppercase">Fournisseur & Détails</h3>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div class="form-group">
          <label class="form-label">Fournisseur *</label>
          <select v-model="form.fournisseur_id" class="form-select">
            <option value="" disabled>Sélectionner le fournisseur...</option>
            <option value="1">DistriTech Maroc</option>
            <option value="2">ImportGlobal Equipements</option>
          </select>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Date Commande</label>
            <input v-model="form.date_commande" type="date" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Livraison Prévue</label>
            <input v-model="form.date_livraison" type="date" class="form-input" />
          </div>
        </div>
      </div>
    </div>

    <!-- Lignes -->
    <div class="card mb-3">
       <div class="flex justify-between items-center mb-2">
         <h3 class="text-sm text-muted text-uppercase">Articles commandés</h3>
         <button class="btn btn-secondary btn-sm" @click="addLine">+ Ajouter un article</button>
       </div>
       <table class="data-table mb-3">
         <thead>
           <tr>
             <th style="width: 40%">Produit</th>
             <th style="width: 15%; text-align:right">Qte</th>
             <th style="width: 15%; text-align:right">P.U HT d'achat</th>
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
               <input v-model="ligne.designation" class="form-input" placeholder="Référence ou Désignation" style="flex:1;" />
             </td>
             <td><input v-model="ligne.quantite" type="number" class="form-input" style="text-align:right" /></td>
             <td><input v-model="ligne.prix_unitaire" type="number" class="form-input" style="text-align:right" /></td>
             <td style="text-align:right; font-weight:600; padding:0 1rem;">{{ formatMoney(ligne.quantite * ligne.prix_unitaire) }}</td>
             <td>
                <button class="text-danger" style="background:none; border:none; cursor:pointer;" @click="removeLine(idx)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
             </td>
           </tr>
           <tr v-if="form.lignes.length === 0">
             <td colspan="5" class="text-center text-muted">Aucune ligne.</td>
           </tr>
         </tbody>
       </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const form = ref({
  fournisseur_id: '',
  date_commande: new Date().toISOString().substring(0, 10),
  date_livraison: '',
  lignes: [
    { produit_id: 2, designation: 'Ecrans LED 27"', quantite: 10, prix_unitaire: 1200 }
  ]
})

const produits = ref([
  { id: 1, nom: 'Prestation de service', prix_achat: 800 },
  { id: 2, nom: 'Écran LED 27"', prix_achat: 1000 },
  { id: 3, nom: 'Licence Windows 11 Pro', prix_achat: 800 }
])

onMounted(async () => {
  try {
    const { data } = await api.get('/produits')
    if (data.data) {
      produits.value = data.data.map(p => ({
        id: p.id,
        nom: p.designation || p.nom,
        prix_achat: parseFloat(p.prix_achat) || parseFloat(p.prix_vente) || 0
      }))
    }
  } catch {}
})

function addLine() {
  form.value.lignes.push({ produit_id: '', designation: '', quantite: 1, prix_unitaire: 0 })
}

function onProduitSelect(ligne) {
  if (ligne.produit_id) {
    const prod = produits.value.find(p => p.id === ligne.produit_id)
    if (prod) {
      ligne.designation = prod.nom
      ligne.prix_unitaire = prod.prix_achat
    }
  }
}

function removeLine(idx) {
  form.value.lignes.splice(idx, 1)
}

function formatMoney(val) { return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH' }

function save() {
  alert('Commande sauvegardée (Mode Démo)')
  router.push('/commandes')
}
</script>
