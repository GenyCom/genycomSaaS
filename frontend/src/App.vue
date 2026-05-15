<template>
  <div class="app-container">
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
    <Toast />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import Toast from './components/common/Toast.vue'
import { toast } from './services/toastService'

const handleConnectivityChange = () => {
  if (navigator.onLine) {
    toast.success('Votre connexion internet est rétablie.', 'En ligne')
  } else {
    toast.info('Vous êtes actuellement hors ligne. Certaines fonctionnalités peuvent être limitées.', 'Hors ligne')
  }
}

onMounted(() => {
  window.addEventListener('online', handleConnectivityChange)
  window.addEventListener('offline', handleConnectivityChange)
})

onUnmounted(() => {
  window.removeEventListener('online', handleConnectivityChange)
  window.removeEventListener('offline', handleConnectivityChange)
})
</script>

<style>
.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
