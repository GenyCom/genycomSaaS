<template>
  <div class="animate-fade-in">
    <div class="flex items-center gap-2 mb-3">
      <router-link to="/superadmin/tenants" class="btn btn-secondary btn-sm">← Retour</router-link>
      <h2 style="font-size:1.1rem; font-weight:600;">Détails de l'Instance SaaS</h2>
    </div>

    <div class="kpi-grid mb-3">
      <div class="kpi-card accent">
        <div class="kpi-label">Stockage Base de données</div>
        <div class="kpi-value">14.2 MB</div>
        <div class="kpi-sub">Quota : 10 GB</div>
        <div class="kpi-icon accent"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div>
      </div>
      <div class="kpi-card success">
        <div class="kpi-label">Utilisateurs Licenciés</div>
        <div class="kpi-value">4</div>
        <div class="kpi-sub">Max : Illimité (Plan Pro)</div>
        <div class="kpi-icon success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
      </div>
      <div class="kpi-card warning">
        <div class="kpi-label">Statut du Serveur</div>
        <div class="kpi-value text-success">En Ligne</div>
        <div class="kpi-sub">Ping: 12ms</div>
        <div class="kpi-icon warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
      <div class="card">
        <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Configuration Infra</h3>
        <ul style="list-style:none; line-height: 2;">
           <li><span class="text-muted">ID & Raison Sociale :</span> GenyCom Demo SARL</li>
           <li><span class="text-muted">Domaine Isolé :</span> <a href="#" style="color:var(--info);">demo.genycom.ma</a></li>
           <li><span class="text-muted">Nom Base MySQL :</span> <code class="font-mono" style="background:var(--bg-input); padding:0.2rem 0.4rem; border-radius:4px;">genycom_client_f9a8b1c</code></li>
           <li><span class="text-muted">Date provisionnement :</span> Aujourd'hui</li>
        </ul>
        <div class="mt-3 flex gap-2">
           <button class="btn btn-secondary">Suspendre l'instance</button>
           <button class="btn btn-secondary text-danger" style="border-color:rgba(239, 68, 68, 0.3);">Détruire</button>
        </div>
      </div>

      <div class="card">
        <h3 class="mb-2 text-warning" style="font-size:1rem; font-weight:600;">Utilisateurs rattachés</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>GenyCom Admin</td>
              <td>admin@genycom.ma</td>
              <td><span class="badge badge-success">Gérant (Owner)</span></td>
            </tr>
            <tr>
              <td>Employé Test</td>
              <td>emp@genycom.ma</td>
              <td><span class="badge badge-default">Commercial</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
// on pourrait récupérer le tenant via route.params.id
</script>
