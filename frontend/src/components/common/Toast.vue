<template>
  <div class="toast-container">
    <TransitionGroup name="toast">
      <div v-for="toast in toasts" :key="toast.id" :class="['toast-item', toast.type]">
        <div class="toast-icon">
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <svg v-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <svg v-if="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </div>
        <div class="toast-content">
          <div v-if="toast.title" class="toast-title">{{ toast.title }}</div>
          <div class="toast-message">{{ toast.message }}</div>
        </div>
        <button @click="remove(toast.id)" class="toast-close">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const toasts = ref([])
let toastId = 0

const add = (message, type = 'info', title = '', duration = 4000) => {
  const id = ++toastId
  toasts.value.push({ id, message, type, title })
  
  if (duration > 0) {
    setTimeout(() => remove(id), duration)
  }
}

const remove = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

// Exposer globalement via un événement personnalisé ou un plugin
const handleToastEvent = (e) => {
  const { message, type, title, duration } = e.detail
  add(message, type, title, duration)
}

onMounted(() => {
  window.addEventListener('app-toast', handleToastEvent)
})

onUnmounted(() => {
  window.removeEventListener('app-toast', handleToastEvent)
})

// Export pour usage interne si besoin
defineExpose({ add })
</script>

<style scoped>
.toast-container {
  position: fixed; top: 24px; right: 24px; z-index: 9999;
  display: flex; flex-direction: column; gap: 12px;
  pointer-events: none;
}

.toast-item {
  pointer-events: auto; width: 340px; display: flex; align-items: flex-start; gap: 14px;
  padding: 16px; border-radius: 12px; background: #fff;
  box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
  border: 1px solid #E5E7EB; overflow: hidden; position: relative;
}

/* Types */
.toast-item.success { border-left: 4px solid #10B981; }
.toast-item.success .toast-icon { color: #10B981; background: #ECFDF5; }

.toast-item.error { border-left: 4px solid #EF4444; }
.toast-item.error .toast-icon { color: #EF4444; background: #FEF2F2; }

.toast-item.info { border-left: 4px solid #3B82F6; }
.toast-item.info .toast-icon { color: #3B82F6; background: #EFF6FF; }

.toast-icon {
  flex-shrink: 0; width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

.toast-content { flex: 1; min-width: 0; }
.toast-title { font-size: 0.9rem; font-weight: 700; color: #111827; margin-bottom: 2px; }
.toast-message { font-size: 0.85rem; color: #4B5563; line-height: 1.4; }

.toast-close {
  flex-shrink: 0; background: transparent; border: none; color: #9CA3AF;
  cursor: pointer; padding: 2px; border-radius: 4px; transition: all .2s;
}
.toast-close:hover { background: #F3F4F6; color: #111827; }

/* Animations */
.toast-enter-active { animation: toast-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-leave-active { animation: toast-out 0.3s ease forwards; }

@keyframes toast-in {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
@keyframes toast-out {
  from { transform: scale(1); opacity: 1; }
  to { transform: scale(0.9); opacity: 0; }
}
</style>
