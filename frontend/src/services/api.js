import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Intercepteur: ajouter le token automatiquement
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('genycom_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Intercepteur: gérer les erreurs 401 (seulement si c'est un vrai 401, pas un réseau manquant)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && error.config?.url?.includes('/me')) {
      localStorage.removeItem('genycom_token')
      localStorage.removeItem('genycom_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
