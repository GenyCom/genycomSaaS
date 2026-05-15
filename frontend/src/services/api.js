import axios from 'axios'
import { toast } from './toastService'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000, // 30 secondes
})

// Intercepteur: ajouter le token automatiquement
api.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('genycom_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  // Ajout dynamique du header Tenant pour le multi-SaaS
  try {
    const rawUser = sessionStorage.getItem('genycom_user');
    if (rawUser && rawUser !== 'undefined') {
      const userData = JSON.parse(rawUser) || {};
      const tenantId = userData.current_tenant_id || userData.tenant_id || userData.tenant?.id;
      if (tenantId) {
        config.headers['X-Tenant-ID'] = tenantId;
      }
    }
  } catch (e) {
    console.error('Error parsing tenant from storage', e);
  }

  return config
})

// Intercepteur: gérer les erreurs globales
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Erreur de connexion (Internet indisponible ou serveur injoignable)
    if (!error.response) {
      toast.info(
        'La connexion internet est lente ou indisponible. Veuillez vérifier votre réseau.', 
        'État du Réseau'
      )
      return Promise.reject(error)
    }

    // Session expirée
    if (error.response?.status === 401) {
      sessionStorage.removeItem('genycom_token')
      sessionStorage.removeItem('genycom_user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    } else {
      // Autres erreurs (500, 403, 404, etc.)
      const message = error.response?.data?.message || 'Une erreur est survenue lors de la communication avec le serveur.'
      toast.error(message, 'Erreur Serveur')
    }
    return Promise.reject(error)
  }
)

export default api
