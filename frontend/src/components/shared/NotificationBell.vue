<template>
  <div class="notification-wrapper" v-click-outside="close">
    <button class="bell-btn" @click="toggle" :class="{ 'has-unread': unreadCount > 0 }">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
    </button>

    <div v-if="isOpen" class="notification-dropdown animate-slide-up">
      <div class="dropdown-header">
        <h3>Notifications</h3>
        <button v-if="unreadCount > 0" @click="markAllAsRead" class="read-all-btn">Tout marquer comme lu</button>
      </div>

      <div class="dropdown-body">
        <div v-if="notifications.length === 0" class="empty-state">
          Aucune nouvelle notification
        </div>
        <div v-for="n in notifications" :key="n.id" class="notification-item" @click="handleNotifClick(n)">
          <div class="notif-icon" :class="n.data.type || n.type">
            <!-- Icone Facture / Paiement -->
            <svg v-if="n.data.type === 'facture_overdue' || n.type === 'facture_overdue'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/><path d="M18 13l-2 2 2 2"/><path d="M14 15h6"/>
            </svg>
            <!-- Icone Stock -->
            <svg v-else-if="n.data.type === 'stock_alert' || n.type === 'stock_alert'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <!-- Icone Devis / Relance -->
            <svg v-else-if="n.data.type === 'devis_relance' || n.type === 'devis_relance'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <!-- Icone Par défaut (Dépenses, etc) -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 8v4l3 3"/>
            </svg>
          </div>
          <div class="notif-content">
            <p class="notif-title">{{ n.data.title || 'Alerte Système' }}</p>
            <p class="notif-message">{{ n.data.message }}</p>
            <span class="notif-time">{{ formatTime(n.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const route = useRoute()
const notifications = ref([])
const unreadCount = ref(0)
const isOpen = ref(false)
let interval = null

// Rafraîchir les notifications à chaque changement de page
watch(route, () => {
  fetchNotifications()
})

async function fetchNotifications() {
  try {
    const { data } = await api.get('/notifications')
    notifications.value = data.data
    unreadCount.value = notifications.value.length
  } catch (error) {
    console.error('Erreur notifications:', error)
  }
}

async function handleNotifClick(notification) {
  // 1. Marquer comme lu
  markAsRead(notification)
  
  // 2. Naviguer vers le document
  const d = notification.data
  if (d.facture_id) router.push(`/factures/${d.facture_id}`)
  else if (d.devis_id) router.push(`/devis/${d.devis_id}`)
  else if (d.produit_id) router.push(`/stock`) // Ou vers la fiche produit si elle existe
  
  isOpen.value = false
}

async function markAsRead(notification) {
  try {
    await api.post(`/notifications/${notification.id}/read`)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
    unreadCount.value--
  } catch (error) {
    console.error(error)
  }
}

async function markAllAsRead() {
  try {
    await api.post('/notifications/read-all')
    notifications.value = []
    unreadCount.value = 0
    isOpen.value = false
  } catch (error) {
    console.error(error)
  }
}

function toggle() {
  isOpen.value = !isOpen.value
  if (isOpen.value) fetchNotifications()
}

function close() {
  isOpen.value = false
}

function formatTime(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

// Simple directive for click-outside
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  }
}

onMounted(() => {
  fetchNotifications()
  interval = setInterval(fetchNotifications, 60000 * 5) // Toutes les 5 minutes
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>

<style scoped>
.notification-wrapper { position: relative; }
.bell-btn {
  background: transparent; border: none; padding: 6px; cursor: pointer;
  color: var(--c-muted); position: relative; transition: color 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.bell-btn:hover { color: var(--c-accent); }
.bell-btn.has-unread { color: var(--c-text); }

.badge {
  position: absolute; top: 0; right: 0; background: #EF4444; color: #fff;
  font-size: 0.65rem; font-weight: 800; min-width: 16px; height: 16px;
  border-radius: 10px; display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}

.notification-dropdown {
  position: absolute; top: 100%; right: 0; width: 320px; background: #fff;
  border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  margin-top: 10px; z-index: 1000; overflow: hidden;
}

.dropdown-header {
  padding: 12px 16px; border-bottom: 1px solid #F1F5F9;
  display: flex; justify-content: space-between; align-items: center;
}
.dropdown-header h3 { font-size: 0.9rem; font-weight: 700; margin: 0; }
.read-all-btn { font-size: 0.75rem; color: var(--c-accent); background: none; border: none; cursor: pointer; font-weight: 600; }

.dropdown-body { max-height: 400px; overflow-y: auto; }
.empty-state { padding: 32px; text-align: center; color: #94A3B8; font-size: 0.85rem; }

.notification-item {
  padding: 12px 16px; display: flex; gap: 12px; cursor: pointer;
  transition: background 0.2s; border-bottom: 1px solid #F8FAFC;
}
.notification-item:hover { background: #F8FAFC; }

.notif-icon {
  width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.notif-icon.expense_reminder { background: #FEF2F2; color: #EF4444; }
.notif-icon.stock_alert { background: #FFF7ED; color: #EA580C; }
.notif-icon.facture_overdue { background: #FEF2F2; color: #DC2626; }
.notif-icon.devis_relance { background: #EEF2FF; color: #4F46E5; }

.notif-content { flex: 1; min-width: 0; }
.notif-title { font-size: 0.85rem; font-weight: 700; color: #0F172A; margin: 0 0 2px 0; line-height: 1.2; }
.notif-message { font-size: 0.8rem; color: #64748B; margin: 0; line-height: 1.4; }
.notif-time { font-size: 0.7rem; color: #94A3B8; margin-top: 4px; display: block; }

@keyframes slide-up {
  from { transform: translateY(10px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.2s ease-out; }
</style>
