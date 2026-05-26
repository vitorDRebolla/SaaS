<script setup lang="ts">
defineProps<{
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'info'
  size?: 'sm' | 'md'
}>()
</script>

<template>
  <span :class="[
    'inline-flex items-center font-medium rounded-full',
    size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-0.5 text-xs',
    variant === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
    variant === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
    variant === 'danger'  ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
    variant === 'info'    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                            'bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)]',
  ]">
    <slot />
  </span>
</template>
