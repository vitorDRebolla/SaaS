<script setup lang="ts">
defineProps<{
  modelValue?: string | number
  label?: string
  placeholder?: string
  type?: string
  error?: string
  hint?: string
  required?: boolean
  disabled?: boolean
  autofocus?: boolean
  maxlength?: number | string
}>()

defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<template>
  <div class="flex flex-col gap-1.5">
    <label v-if="label" class="text-xs font-medium text-[var(--color-text-secondary)] uppercase tracking-wide">
      {{ label }}<span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <input
      :type="type ?? 'text'"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :autofocus="autofocus"
      :maxlength="maxlength"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      :class="[
        'w-full px-3 py-2 rounded-lg border text-sm bg-[var(--color-bg-base)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-placeholder)] transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:opacity-50',
        error ? 'border-red-500' : 'border-[var(--color-border)]'
      ]"
    />
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
    <slot name="hint">
      <p v-if="hint" class="text-xs text-[var(--color-text-secondary)]">{{ hint }}</p>
    </slot>
  </div>
</template>
