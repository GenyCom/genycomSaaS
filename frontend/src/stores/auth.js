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
        this.error = err.response?.data?.errors?.email?.[0] 
                     || err.response?.data?.message 
                     || "Identifiants incorrects ou serveur inaccessible"
        throw err
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

    setUser(newUser) {
      this.user = newUser
      localStorage.setItem('genycom_user', JSON.stringify(newUser))
    },

    setEntrepriseInfo(entrepriseData) {
      if (this.user) {
        this.user.entreprise = entrepriseData
        localStorage.setItem('genycom_user', JSON.stringify(this.user))
      }
    }
  },
})
