<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <router-link to="/avoirs-fournisseurs" class="btn btn-secondary btn-sm">← Retour</router-link>
        <h2 style="font-size:1.1rem; font-weight:600;">{{ isNew ? 'Nouvel Avoir Fournisseur' : 'Détail de l\'Avoir ' + form.numero }}</h2>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-primary" @click="save">Enregistrer</button>
      </div>
    </div>

    <!-- En-tête -->
    <div class="card mb-3">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
          <h3 class="text-sm text-muted mb-2 text-uppercase">Fournisseur & Motif</h3>
          <div class="form-group">
            <label class="form-label">Fournisseur *</label>
            <select v-model="form.fournisseur_id" class="form-select">
              <option value="" disabled>Sélectionner le fournisseur...</option>
              <option value="1">DistriTech Maroc</option>
              <option value="2">ImportGlobal Equipements</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Motif de l'Avoir</label>
            <input v-model="form.motif" type="text" class="form-input" placeholder="Ex: Matériel sous garantie" />
          </div>
        </div>
        <div>
           <h3 class="text-sm text-muted mb-2 text-uppercase">Général</h3>
           <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">N° Avoir</label>
                <input v-model="form.numero" type="text" class="form-input font-mono" disabled />
              </div>
              <div class="form-group">
                <label class="form-label">Date de l'Avoir</label>
                <input v-model="form.date_avoir" type="date" class="form-input" />
              </div>
              <div class="form-group" style="grid-column: span 2">
                <label class="form-label">Réf. Commande / Dette d'origine</label>
                <select class="form-select">
                  <option value="">Aucune</option>
                  <option value="1">CMD-202604-001</option>
                  <option value="2">CMD-202604-009</option>
                </select>
              </div>
           </div>
        </div>
      </div>
    </div>

    <!-- Lignes de l'avoir -->
    <div class="card mb-3">
       <div class="flex justify-between items-center mb-2">
         <h3 class="text-sm text-muted text-uppercase">Articles concernés par le retour</h3>
         <button class="btn btn-secondary btn-sm" @click="addLine">+ Ajouter une ligne</button>
       </div>
       <table class="data-table mb-3">
         <thead>
           <tr>
             <th style="width: 40%">Désignation</th>
             <th style="width: 15%; text-align:right">Qte Retournée</th>
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
             <td colspan="5" class="text-center text-muted">Aucune ligne. Ajoutez un article.</td>
           </tr>
         </tbody>
       </table>

       <!-- Totaux -->
       <div style="display:flex; justify-content:flex-end;">
         <div style="width: 300px; background: var(--bg-primary); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div class="flex justify-between" style="font-size:1.1rem; font-weight:700; color:var(--accent);">
               <span>Total HT Retour</span> <span>{{ formatMoney(totalHT) }}</span>
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
  date_avoir: new Date().toISOString().substring(0, 10),
  fournisseur_id: '',
  motif: '',
  lignes: []
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

if (isNew.value) {
  form.value.lignes.push({ produit_id: '', designation: '', quantite: 1, prix_unitaire: 0 })
} else {
  form.value.numero = 'AVF-2026-003'
  form.value.fournisseur_id = 1
  form.value.motif = 'Écran cassé lors du transport'
  form.value.lignes = [
    { produit_id: 2, designation: 'Écran LED 27"', quantite: 1, prix_unitaire: 1000 }
  ]
}

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

const totalHT = computed(() => form.value.lignes.reduce((sum, l) => sum + (l.quantite * l.prix_unitaire), 0))

function formatMoney(val) { return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH' }

function save() {
  alert('Avoir Fournisseur enregistré avec succès (Mode Démo)')
  router.push('/avoirs-fournisseurs')
}
</script>
