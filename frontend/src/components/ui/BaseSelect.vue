<script setup lang="ts">
defineProps<{
  modelValue?: string | number | null
  label?: string
  placeholder?: string
  error?: string
}>()
defineEmits<{ 'update:modelValue': [value: string | number | null] }>()
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label v-if="label" class="text-xs font-medium text-[var(--color-text-secondary)] uppercase tracking-wide">{{ label }}</label>
    <select
      :value="modelValue ?? ''"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      class="w-full px-3 py-2 rounded-lg border border-[var(--color-border)] text-sm bg-[var(--color-bg-base)] text-[var(--color-text-primary)] transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <slot />
    </select>
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
  </div>
</template>
