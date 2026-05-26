<script setup lang="ts">
defineProps<{
  title: string
  description?: string
  icon?: string
  action?: string
}>()

defineEmits<{ action: [] }>()
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div v-if="icon" class="text-5xl mb-4">{{ icon }}</div>
    <h3 class="font-semibold text-[var(--color-text-primary)] mb-1">{{ title }}</h3>
    <p v-if="description" class="text-sm text-[var(--color-text-secondary)] max-w-sm mt-1">{{ description }}</p>
    <div class="mt-5">
      <slot>
        <button
          v-if="action"
          @click="$emit('action')"
          class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
        >{{ action }}</button>
      </slot>
    </div>
  </div>
</template>
