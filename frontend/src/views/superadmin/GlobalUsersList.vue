<template>
  <div class="animate-fade-in">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 style="font-size:1.1rem; font-weight:600;">Utilisateurs Globaux</h2>
        <p class="text-sm text-muted">Aperçu Central des comptes (y compris les SuperAdmins)</p>
      </div>
      <router-link to="/superadmin/users/create" class="btn btn-primary" style="background: var(--warning); box-shadow: none;">+ Nouvel Utilisateur</router-link>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom Complet</th>
            <th>Email</th>
            <th>Type</th>
            <th>SaaS rattachés</th>
            <th>Création</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="font-weight:600;">Geny Com</td>
            <td>genycomc@gmail.com</td>
            <td><span class="badge badge-danger" style="background:var(--warning-bg); color:var(--warning);">SuperAdmin Central</span></td>
            <td class="text-muted">Accès global</td>
            <td class="text-muted">Aujourd'hui</td>
            <td><router-link to="/superadmin/users/1/edit" class="btn btn-secondary btn-sm">Editer</router-link></td>
          </tr>
          <tr>
            <td style="font-weight:600;">GenyCom Admin</td>
            <td>admin@genycom.ma</td>
            <td><span class="badge badge-info">Locataire (SaaS)</span></td>
            <td>GenyCom Demo SARL</td>
            <td class="text-muted">Aujourd'hui</td>
            <td><router-link to="/superadmin/users/2/edit" class="btn btn-secondary btn-sm">Editer</router-link></td>
          </tr>
          <tr>
            <td style="font-weight:600;">Employé Test</td>
            <td>emp@genycom.ma</td>
            <td><span class="badge badge-default">Utilisateur</span></td>
            <td>GenyCom Demo SARL</td>
            <td class="text-muted">Aujourd'hui</td>
            <td><router-link to="/superadmin/users/3/edit" class="btn btn-secondary btn-sm">Editer</router-link></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
</script>
