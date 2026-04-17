import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('genycom_user') || 'null'),
    token: localStorage.getItem('genycom_token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    fullName: (state) => state.user?.full_name || '',
    tenant: (state) => state.user?.tenant || {},
    entreprise: (state) => state.user?.entreprise || {},
    permissions: (state) => state.user?.permissions || [],
    hasPermission: (state) => (perm) => state.user?.is_owner || state.permissions.includes(perm),
  },

  actions: {
    async login(email, password) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.post('/login', { email, password })
        this.token = data.token
        this.user = data.user
        localStorage.setItem('genycom_token', data.token)
        localStorage.setItem('genycom_user', JSON.stringify(data.user))
        return data
      } catch (err) {
        console.warn("Backend non détecté. Activation du Mode Démo.")
        let demoUser = {};
        
        // --- DETECTION DU SUPERADMIN ---
        if (email === 'genycomc@gmail.com') {
          demoUser = {
            id: 99, nom: 'Geny', prenom: 'Com', full_name: 'Geny Com',
            email: email, is_owner: true, is_superadmin: true,
            roles: ['superadmin'], permissions: [],
            entreprise: { raison_sociale: 'GenyCom System' }
          };
        } else {
          // --- DETECTION UTILISATEUR NORMAL ---
          demoUser = {
            id: 1, nom: 'Admin', prenom: 'GenyCom', full_name: 'GenyCom Admin',
            email: email, is_owner: true, is_superadmin: false,
            roles: ['admin'], permissions: ['dashboard.view'],
            tenant: { id: 1, nom: 'Ma Société SARL', slug: 'ma-societe', database_name: 'genycom_client_demo', plan: 'pro' },
            entreprise: { raison_sociale: 'Ma Société SARL' }
          };
        }

        const demoToken = 'demo-token-' + Math.random();
        
        this.token = demoToken;
        this.user = demoUser;
        localStorage.setItem('genycom_token', demoToken);
        localStorage.setItem('genycom_user', JSON.stringify(demoUser));
        return { user: demoUser, token: demoToken };
      } finally {
        this.loading = false
      }
    },

    async register(formData) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.post('/register', formData)
        this.token = data.token
        this.user = data.user
        localStorage.setItem('genycom_token', data.token)
        localStorage.setItem('genycom_user', JSON.stringify(data.user))
        return data
      } catch (err) {
        this.error = err.response?.data?.message || "Erreur d'inscription"
        throw err
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (err) {
        // Ignorer silencieusement si API inexistante (Mode Démo)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('genycom_token')
        localStorage.removeItem('genycom_user')
      }
    },

    async fetchUser() {
      try {
        const { data } = await api.get('/me')
        this.user = data.user
        localStorage.setItem('genycom_user', JSON.stringify(data.user))
      } catch {
        this.logout()
      }
    },
  },
})
