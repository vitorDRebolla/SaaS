<script setup lang="ts">
defineProps<{ width?: string; height?: string; rounded?: string }>()
</script>

<template>
  <div
    :class="['animate-pulse bg-[var(--color-bg-elevated)]', rounded ?? 'rounded']"
    :style="{ width: width, height: height }"
  />
</template>
