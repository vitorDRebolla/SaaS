<script setup lang="ts">
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
</script>

<template>
  <Teleport to="body">
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full">
      <TransitionGroup name="toast">
        <div
          v-for="toast in ui.toasts"
          :key="toast.id"
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg text-sm font-medium cursor-pointer',
            toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-700' :
            toast.type === 'error'   ? 'bg-red-600 text-white border-red-700' :
            toast.type === 'warning' ? 'bg-amber-600 text-white border-amber-700' :
                                       'bg-[var(--color-bg-elevated)] text-[var(--color-text-primary)] border-[var(--color-border)]',
          ]"
          @click="ui.removeToast(toast.id)"
        >
          <span class="text-base">{{ toast.type === 'success' ? '✓' : toast.type === 'error' ? '✕' : toast.type === 'warning' ? '⚠' : 'ℹ' }}</span>
          <span>{{ toast.message }}</span>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>
