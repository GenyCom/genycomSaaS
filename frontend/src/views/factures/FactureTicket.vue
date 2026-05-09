<template>
  <div class="ticket-container" v-if="facture">
    <div class="no-print actions">
      <button @click="imprimer" class="btn">Imprimer</button>
      <button @click="fermer" class="btn btn-secondary">Fermer</button>
    </div>

    <div class="ticket">
      <header class="ticket-header">
        <h1 class="company-name">{{ entreprise.raison_sociale || 'GENYCOM' }}</h1>
        <p class="company-info">
          {{ entreprise.adresse }}<br/>
          {{ entreprise.ville }}<br/>
          {{ entreprise.telephone }}
        </p>
      </header>

      <div class="separator">--------------------------------</div>

      <div class="ticket-meta">
        <p><strong>TICKET N°:</strong> {{ facture.numero }}</p>
        <p><strong>DATE:</strong> {{ formatDate(facture.date_facture) }}</p>
        <p v-if="facture.client"><strong>CLIENT:</strong> {{ facture.client.societe || facture.client.display_name }}</p>
      </div>

      <div class="separator">--------------------------------</div>

      <table class="items-table">
        <thead>
          <tr>
            <th class="qty">Qté</th>
            <th class="desc">Désignation</th>
            <th class="price">Prix</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="l in facture.lignes" :key="l.id">
            <td class="qty">{{ l.quantite }}</td>
            <td class="desc">{{ l.designation }}</td>
            <td class="price">{{ formatMoney(l.montant_ttc) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="separator">--------------------------------</div>

      <div class="totals">
        <div class="total-row">
          <span>TOTAL HT:</span>
          <span>{{ formatMoney(facture.total_ht) }} DH</span>
        </div>
        <div class="total-row">
          <span>TVA:</span>
          <span>{{ formatMoney(facture.total_tva) }} DH</span>
        </div>
        <div class="total-row grand-total">
          <span>TOTAL TTC:</span>
          <span>{{ formatMoney(facture.total_ttc) }} DH</span>
        </div>
      </div>

      <div class="separator">--------------------------------</div>

      <footer class="ticket-footer">
        <p>Merci de votre visite !</p>
        <p>{{ new Date().toLocaleTimeString('fr-FR') }}</p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const facture = ref(null)
const entreprise = ref({})

function formatMoney(val) {
  return (parseFloat(val) || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR')
}

function imprimer() { window.print() }
function fermer() { window.close() }

onMounted(async () => {
  try {
    const [fRes, eRes] = await Promise.all([
      api.get(`/factures/${route.params.id}`),
      api.get('/parametrage/entreprise')
    ])
    facture.value = fRes.data.data || fRes.data
    entreprise.value = eRes.data || {}
    
    // Auto print if requested via query
    if (route.query.autoprint) {
      setTimeout(() => window.print(), 1000)
    }
  } catch (e) {
    console.error(e)
  }
})
</script>

<style scoped>
.ticket-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  background: #f0f2f5;
  min-height: 100vh;
}

.ticket {
  width: 80mm;
  background: white;
  padding: 10mm 5mm;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  font-family: 'Courier New', Courier, monospace;
  font-size: 12px;
  line-height: 1.4;
  color: black;
}

.ticket-header {
  text-align: center;
  margin-bottom: 10px;
}

.company-name {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 5px;
  text-transform: uppercase;
}

.company-info {
  font-size: 10px;
}

.separator {
  text-align: center;
  margin: 5px 0;
  font-weight: bold;
}

.ticket-meta p {
  margin: 2px 0;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin: 10px 0;
}

.items-table th {
  text-align: left;
  border-bottom: 1px dashed black;
  padding-bottom: 5px;
}

.items-table td {
  padding: 5px 0;
  vertical-align: top;
}

.qty { width: 15%; text-align: center; }
.desc { width: 55%; }
.price { width: 30%; text-align: right; }

.totals {
  margin-top: 10px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 3px;
}

.grand-total {
  font-weight: bold;
  font-size: 14px;
  margin-top: 5px;
  border-top: 1px double black;
  padding-top: 5px;
}

.ticket-footer {
  text-align: center;
  margin-top: 20px;
  font-size: 10px;
}

.no-print {
  margin-bottom: 20px;
  display: flex;
  gap: 10px;
}

.btn {
  padding: 8px 16px;
  background: #2563EB;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.btn-secondary {
  background: #94A3B8;
}

@media print {
  .no-print { display: none; }
  body, html { background: white !important; margin: 0; padding: 0; }
  .ticket-container { background: white !important; padding: 0; }
  .ticket { box-shadow: none; width: 100%; padding: 0; }
  @page { margin: 0; }
}
</style>
