<template>
  <div class="pos-layout">
    <router-view />
    <Toast />
  </div>
</template>

<script setup>
import Toast from '../components/common/Toast.vue'
</script>

<style scoped>
.pos-layout {
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  background: var(--bg-primary);
  color: var(--text-primary);
}
</style>
