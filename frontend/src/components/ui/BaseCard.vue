<script setup lang="ts">
defineProps<{ padding?: boolean; hover?: boolean }>()
</script>

<template>
  <div :class="[
    'bg-[var(--color-bg-surface)] border border-[var(--color-border)] rounded-xl',
    padding !== false ? 'p-5' : '',
    hover ? 'hover:border-[var(--color-border-strong)] transition-colors cursor-pointer' : '',
  ]">
    <slot />
  </div>
</template>
