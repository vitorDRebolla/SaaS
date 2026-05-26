<script setup lang="ts">
defineProps<{
  modelValue?: string
  label?: string
  placeholder?: string
  rows?: number
  error?: string
}>()
defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label v-if="label" class="text-xs font-medium text-[var(--color-text-secondary)] uppercase tracking-wide">{{ label }}</label>
    <textarea
      :value="modelValue"
      :placeholder="placeholder"
      :rows="rows ?? 3"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
      :class="['w-full px-3 py-2 rounded-lg border text-sm bg-[var(--color-bg-base)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-placeholder)] resize-none transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent', error ? 'border-red-500' : 'border-[var(--color-border)]']"
    />
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
  </div>
</template>
