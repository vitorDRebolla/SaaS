<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useUiStore } from '@/stores/ui'
import ToastProvider from '@/components/ui/ToastProvider.vue'

const ui = useUiStore()
onMounted(() => ui.initTheme())
</script>

<template>
  <RouterView />
  <ToastProvider />
</template>
