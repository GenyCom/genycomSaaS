<template>
  <Transition name="modal-fade">
    <div v-if="show" class="modal-overlay" @click.self="$emit('cancel')">
      <div class="modal-content">
        <div class="modal-header">
          <div class="icon-box warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
        </div>
        <div class="modal-body">
          <h3>{{ title }}</h3>
          <p>{{ message }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="$emit('cancel')">Annuler</button>
          <button class="btn-confirm" :class="confirmClass" @click="$emit('confirm')">
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: Boolean,
  title: { type: String, default: 'Confirmation' },
  message: { type: String, default: 'Êtes-vous sûr de vouloir effectuer cette action ?' },
  confirmText: { type: String, default: 'Confirmer' },
  confirmClass: { type: String, default: 'danger' }
})
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999; padding: 20px;
}

.modal-content {
  background: #ffffff;
  width: 100%; max-width: 400px;
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: modal-slide-up 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-header {
  padding: 32px 32px 16px;
  display: flex; justify-content: center;
}

.icon-box {
  width: 64px; height: 64px;
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
}

.icon-box.warning {
  background: #FFF7ED; color: #F97316;
}

.modal-body {
  padding: 0 32px 32px;
  text-align: center;
}

.modal-body h3 {
  font-size: 1.25rem; font-weight: 800;
  color: #1E293B; margin: 0 0 8px;
}

.modal-body p {
  font-size: 0.95rem; color: #64748B;
  line-height: 1.6; margin: 0;
}

.modal-footer {
  padding: 24px 32px 32px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
  background: #F8FAFC;
}

button {
  padding: 12px; border-radius: 12px;
  font-size: 0.9rem; font-weight: 700;
  cursor: pointer; transition: all 0.2s;
  border: none;
}

.btn-cancel {
  background: #fff; color: #64748B;
  border: 1.5px solid #E2E8F0;
}

.btn-cancel:hover {
  background: #F1F5F9; border-color: #CBD5E1;
}

.btn-confirm.danger {
  background: #EF4444; color: #fff;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

.btn-confirm.danger:hover {
  background: #DC2626; transform: translateY(-1px);
  box-shadow: 0 6px 15px rgba(239, 68, 68, 0.35);
}

/* Animations */
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}

@keyframes modal-slide-up {
  from { transform: translateY(20px) scale(0.95); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}
</style>
