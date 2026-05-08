<template>
  <Transition name="modal-fade">
    <div v-if="show" class="modal-overlay" @click.self="$emit('cancel')">
      <div class="modal-container stock-warning">
        <div class="modal-header-premium">
          <div class="warning-icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div class="header-text">
            <h2>Disponibilité du Stock</h2>
            <p>Certains articles ne sont pas disponibles en quantité suffisante.</p>
          </div>
        </div>

        <div class="modal-body-premium">
          <div class="stock-items-list">
            <div v-for="item in items" :key="item.produit_id" class="stock-item-card">
              <div class="item-info">
                <span class="item-name">{{ item.designation }}</span>
                <span class="item-status">Stock actuel : <strong :class="{ 'text-danger': item.disponible <= 0 }">{{ item.disponible }}</strong></span>
              </div>
              <div class="item-stats">
                <div class="stat-box">
                  <span class="stat-label">Requis</span>
                  <span class="stat-value">{{ item.requis }}</span>
                </div>
                <div class="stat-box missing">
                  <span class="stat-label">Manquant</span>
                  <span class="stat-value">{{ item.manquant }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="warning-footer-note">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>L'expédition forcée entraînera un stock négatif dans l'entrepôt sélectionné.</span>
          </div>
        </div>

        <div class="modal-footer-premium">
          <button class="btn-cancel" @click="$emit('cancel')">Modifier la commande</button>
          <button class="btn-confirm-force" @click="$emit('confirm')">
            <span>Forcer l'expédition</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: Boolean,
  items: Array
})
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px;
}

.modal-container {
  background: #fff; border-radius: 24px; width: 100%; max-width: 520px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;
  animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-header-premium {
  padding: 32px 32px 24px; display: flex; align-items: center; gap: 20px;
  background: linear-gradient(to bottom right, #FFFBEB, #FEF3C7);
}

.warning-icon-wrapper {
  width: 64px; height: 64px; border-radius: 18px;
  background: #F59E0B; color: #fff;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.4);
}

.header-text h2 { margin: 0; font-size: 1.5rem; font-weight: 800; color: #92400E; }
.header-text p { margin: 4px 0 0; font-size: 0.95rem; color: #B45309; opacity: 0.8; }

.modal-body-premium { padding: 24px 32px; }

.stock-items-list {
  display: flex; flex-direction: column; gap: 12px;
  max-height: 300px; overflow-y: auto; padding-right: 8px; margin-bottom: 20px;
}

.stock-item-card {
  padding: 16px; border-radius: 16px; background: #F8FAFC;
  border: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;
}

.item-info { display: flex; flex-direction: column; gap: 4px; }
.item-name { font-weight: 700; color: #1E293B; font-size: 1rem; }
.item-status { font-size: 0.85rem; color: #64748B; }
.text-danger { color: #EF4444; }

.item-stats { display: flex; gap: 12px; }
.stat-box {
  display: flex; flex-direction: column; align-items: center;
  padding: 8px 12px; border-radius: 12px; background: #fff; border: 1px solid #E2E8F0;
  min-width: 70px;
}
.stat-box.missing { background: #FEF2F2; border-color: #FEE2E2; }
.stat-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #64748B; margin-bottom: 2px; }
.stat-value { font-size: 1.1rem; font-weight: 800; color: #1E293B; }
.stat-box.missing .stat-value { color: #EF4444; }

.warning-footer-note {
  display: flex; align-items: center; gap: 10px;
  padding: 14px; background: #F1F5F9; border-radius: 12px;
  font-size: 0.85rem; color: #475569; font-weight: 500;
}

.modal-footer-premium {
  padding: 24px 32px 32px; display: flex; gap: 12px;
}

.btn-cancel {
  flex: 1; padding: 14px; border-radius: 14px; border: 1px solid #E2E8F0;
  background: #fff; color: #475569; font-weight: 700; cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover { background: #F8FAFC; border-color: #CBD5E1; }

.btn-confirm-force {
  flex: 1.2; padding: 14px; border-radius: 14px; border: none;
  background: #1E293B; color: #fff; font-weight: 700; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.btn-confirm-force:hover { background: #0F172A; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); }

/* Animations */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

@keyframes slide-up {
  from { transform: translateY(30px) scale(0.95); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}

/* Scrollbar styling */
.stock-items-list::-webkit-scrollbar { width: 5px; }
.stock-items-list::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.stock-items-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.stock-items-list::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
