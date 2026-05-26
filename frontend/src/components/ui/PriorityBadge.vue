<script setup lang="ts">
import { computed } from 'vue'
import { PRIORITY_CONFIG } from '@/lib/utils'
import type { Priority } from '@/types/models'

const props = defineProps<{ priority: Priority }>()
const config = computed(() => PRIORITY_CONFIG[props.priority])
</script>

<template>
  <span :class="['inline-flex items-center gap-1 text-xs font-medium', config.color]" :title="config.label">
    <span class="text-base leading-none">{{ config.icon }}</span>
    <slot>{{ config.label }}</slot>
  </span>
</template>
