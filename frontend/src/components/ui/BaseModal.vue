<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  show?: boolean
  open?: boolean
  title?: string
  size?: 'sm' | 'md' | 'lg' | 'xl'
}>()

const emit = defineEmits<{ close: [] }>()

const isOpen = computed(() => props.show ?? props.open ?? false)

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && isOpen.value) emit('close')
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')" />
        <div :class="[
          'relative bg-[var(--color-bg-surface)] border border-[var(--color-border)] rounded-xl shadow-2xl w-full max-h-[90vh] overflow-y-auto',
          size === 'sm' ? 'max-w-sm' : size === 'lg' ? 'max-w-2xl' : size === 'xl' ? 'max-w-4xl' : 'max-w-lg'
        ]">
          <div v-if="title" class="flex items-center justify-between px-6 py-4 border-b border-[var(--color-border)]">
            <h2 class="font-semibold text-[var(--color-text-primary)]">{{ title }}</h2>
            <button @click="$emit('close')" class="p-1 rounded hover:bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)]">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <div class="p-6"><slot /></div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.15s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
