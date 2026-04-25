<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <router-link to="/contrats" class="btn btn-secondary btn-sm">← Retour</router-link>
        <h2 style="font-size:1.1rem; font-weight:600;">{{ isNew ? 'Nouvel Abonnement / Contrat' : 'Édition du Contrat ' + (form.numero || '') }}</h2>
      </div>
      <div class="flex gap-2">
        <button v-if="!isNew" class="btn btn-secondary" @click="generateInvoice" :disabled="generating">
          {{ generating ? 'Génération...' : 'Générer la facture maintenant' }}
        </button>
        <button class="btn btn-primary" @click="save" :disabled="loading">
          {{ loading ? 'Enregistrement...' : 'Enregistrer le Contrat' }}
        </button>
      </div>
    </div>

    <!-- En-tête du contrat -->
    <div class="card mb-3">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
          <h3 class="text-sm text-muted mb-2 text-uppercase">Informations Client</h3>
          <div class="form-group mb-3">
            <label class="form-label">Client *</label>
            <select v-model="form.client_id" class="form-select" required>
              <option value="" disabled>Sélectionner un client...</option>
              <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.societe || `${c.nom} ${c.prenom}` }}</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Titre de l'abonnement / contrat *</label>
            <input v-model="form.titre" type="text" class="form-input" placeholder="Ex: Maintenance Annuelle Pack Gold" required />
          </div>
        </div>
        <div>
           <h3 class="text-sm text-muted mb-2 text-uppercase">Paramètres de Récurrence</h3>
           <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">Date de début *</label>
                <input v-model="form.date_debut" type="date" class="form-input" required />
              </div>
              <div class="form-group">
                <label class="form-label">Fréquence de facturation *</label>
                <select v-model="form.frequence" class="form-select">
                  <option value="MENSUEL">Chaque Mois (Mensuel)</option>
                  <option value="TRIMESTRIEL">Chaque Trimestre</option>
                  <option value="SEMESTRIEL">Chaque Semestre</option>
                  <option value="ANNUEL">Chaque Année (Annuel)</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Statut</label>
                <select v-model="form.statut" class="form-select">
                  <option value="ACTIF">Actif</option>
                  <option value="SUSPENDU">Suspendu</option>
                  <option value="RESILIE">Résilié</option>
                </select>
              </div>
           </div>
        </div>
      </div>
    </div>

    <!-- Lignes du contrat -->
    <div class="card mb-3">
       <div class="flex justify-between items-center mb-2">
         <h3 class="text-sm text-muted text-uppercase">Prestations & Articles récurrents</h3>
         <button class="btn btn-secondary btn-sm" @click="addLine">+ Ajouter une prestation</button>
       </div>
       <table class="data-table mb-3">
         <thead>
           <tr>
             <th style="width: 40%">Désignation</th>
             <th style="width: 15%; text-align:right">Qte par facture</th>
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
                 <option value="">Saisie libre</option>
                 <option v-for="p in produits" :key="p.id" :value="p.id">{{ p.nom || p.designation }}</option>
               </select>
               <input v-model="ligne.designation" class="form-input" placeholder="Désignation" style="flex:1;" />
             </td>
             <td><input v-model="ligne.quantite" type="number" step="0.01" class="form-input" style="text-align:right" /></td>
             <td><input v-model="ligne.prix_unitaire" type="number" step="0.01" class="form-input" style="text-align:right" /></td>
             <td><input v-model="ligne.taux_tva" type="number" class="form-input" style="text-align:right" /></td>
             <td style="text-align:right; font-weight:600; padding:0 1rem;">{{ formatMoney(ligne.quantite * ligne.prix_unitaire) }}</td>
             <td>
                <button class="text-danger" style="background:none; border:none; cursor:pointer;" @click="removeLine(idx)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
             </td>
           </tr>
           <tr v-if="form.lignes.length === 0">
             <td colspan="6" class="text-center text-muted">Aucune prestation. Cliquez sur + Ajouter une prestation.</td>
           </tr>
         </tbody>
       </table>

       <!-- Totaux -->
       <div style="display:flex; justify-content:flex-end;">
         <div style="width: 300px; background: var(--bg-primary); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div class="flex justify-between mb-1"><span class="text-muted">Total HT / échéance</span> <span>{{ formatMoney(totalHT) }}</span></div>
            <div class="flex justify-between mb-2"><span class="text-muted">TVA</span> <span>{{ formatMoney(totalTVA) }}</span></div>
            <div class="flex justify-between" style="border-top:1px solid var(--border-color); padding-top:0.5rem; font-size:1.1rem; font-weight:700; color:var(--accent);">
               <span>Total TTC</span> <span>{{ formatMoney(totalTTC) }}</span>
            </div>
         </div>
       </div>
    </div>

    <ConfirmModal 
      :show="showConfirm"
      title="Générer la facture"
      message="Voulez-vous générer immédiatement la facture pour ce contrat ? Cela avancera également la date de la prochaine échéance."
      confirmText="Générer maintenant"
      @confirm="executeGenerateInvoice"
      @cancel="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'
import { toast } from '../../services/toastService'
import ConfirmModal from '../../components/shared/ConfirmModal.vue'

const router = useRouter()
const route = useRoute()
const isNew = computed(() => !route.params.id)
const loading = ref(false)
const generating = ref(false)
const showConfirm = ref(false)

const form = ref({
  titre: '',
  date_debut: new Date().toISOString().substring(0, 10),
  frequence: 'MENSUEL',
  statut: 'ACTIF',
  client_id: '',
  lignes: []
})

const clients = ref([])
const produits = ref([])

onMounted(async () => {
  try {
    const [resClients, resProduits] = await Promise.all([
      api.get('/clients'),
      api.get('/produits')
    ])
    clients.value = resClients.data?.data || resClients.data || []
    produits.value = resProduits.data?.data || resProduits.data || []

    if (!isNew.value) {
      const resContrat = await api.get(`/contrats/${route.params.id}`)
      const c = resContrat.data
      form.value = {
        titre: c.titre,
        numero: c.numero,
        date_debut: c.date_debut?.substring(0, 10),
        frequence: c.frequence,
        statut: c.statut,
        client_id: c.client_id,
        lignes: c.lignes.map(l => ({
          produit_id: l.produit_id || '',
          designation: l.designation,
          quantite: l.quantite,
          prix_unitaire: l.prix_unitaire,
          taux_tva: l.taux_tva
        }))
      }
    } else {
      addLine()
    }
  } catch (err) {
    console.error("Erreur de chargement des références", err)
  }
})

function addLine() {
  form.value.lignes.push({ produit_id: '', designation: '', quantite: 1, prix_unitaire: 0, taux_tva: 20 })
}

function onProduitSelect(ligne) {
  if (ligne.produit_id) {
    const prod = produits.value.find(p => p.id === ligne.produit_id)
    if (prod) {
      ligne.designation = prod.nom || prod.designation
      ligne.prix_unitaire = prod.prix_vente
      ligne.taux_tva = prod.taux_tva !== undefined ? prod.taux_tva : 20
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

async function save() {
  if(!form.value.client_id || !form.value.titre || !form.value.date_debut) {
    toast.error("Veuillez remplir les champs obligatoires (Client, Titre, Date de début).")
    return
  }

  loading.value = true
  const payload = {
    ...form.value,
    total_ht: totalHT.value,
    total_tva: totalTVA.value,
    total_ttc: totalTTC.value,
    lignes: form.value.lignes.map(l => ({
      ...l,
      montant_ht: l.quantite * l.prix_unitaire,
      montant_tva: l.quantite * l.prix_unitaire * (l.taux_tva/100),
      montant_ttc: l.quantite * l.prix_unitaire * (1 + l.taux_tva/100)
    }))
  }

  try {
    if (isNew.value) {
      await api.post('/contrats', payload)
    } else {
      await api.put(`/contrats/${route.params.id}`, payload)
    }
    router.push('/contrats')
    toast.success('Contrat enregistré avec succès.')
  } catch (err) {
    toast.error("Une erreur est survenue lors de l'enregistrement.")
    console.error(err)
  } finally {
    loading.value = false
  }
}

async function generateInvoice() {
  showConfirm.value = true
}

async function executeGenerateInvoice() {
  showConfirm.value = false
  generating.value = true
  try {
    const res = await api.post(`/contrats/${route.params.id}/generer-facture`)
    toast.success(`Facture ${res.data.numero} générée avec succès !`)
    setTimeout(() => {
      router.go(0)
    }, 1500)
  } catch (err) {
    toast.error(err.response?.data?.message || "Erreur lors de la génération de la facture")
    console.error(err)
  } finally {
    generating.value = false
  }
}
</script>
