<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/lib/api'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import { formatRelativeTime } from '@/lib/utils'
import type { Notification } from '@/types/models'
import type { ApiResponse } from '@/types/api'

const route = useRoute()
const teamSlug = computed(() => route.params.team as string)

const notifications = ref<Notification[]>([])
const isLoading = ref(true)

onMounted(async () => {
  try {
    const res = await api.get<ApiResponse<Notification[]>>(`/teams/${teamSlug.value}/notifications`)
    notifications.value = res.data
  } finally {
    isLoading.value = false
  }
})

async function markAllRead() {
  await api.post(`/teams/${teamSlug.value}/notifications/mark-read`)
  notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }))
}

function notificationTitle(n: Notification): string {
  return (n.data?.title as string) ?? n.type.split('\\').pop() ?? 'Notification'
}

function notificationBody(n: Notification): string {
  return (n.data?.body as string) ?? ''
}
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Notifications</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">{{ notifications.filter(n => !n.read_at).length }} unread</p>
        </div>
        <BaseButton v-if="notifications.some(n => !n.read_at)" variant="ghost" size="sm" @click="markAllRead">
          Mark all read
        </BaseButton>
      </div>

      <template v-if="isLoading">
        <div class="space-y-2">
          <BaseSkeleton v-for="i in 5" :key="i" class="h-16 rounded-xl" />
        </div>
      </template>

      <BaseCard v-else-if="notifications.length" class="divide-y divide-[var(--color-border)]">
        <div
          v-for="n in notifications"
          :key="n.id"
          :class="['flex items-start gap-3 p-4', !n.read_at ? 'bg-indigo-600/5' : '']"
        >
          <div :class="['w-2 h-2 rounded-full mt-2 flex-shrink-0', n.read_at ? 'bg-transparent' : 'bg-indigo-600']" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-[var(--color-text-primary)]">{{ notificationTitle(n) }}</p>
            <p v-if="notificationBody(n)" class="text-xs text-[var(--color-text-secondary)] mt-0.5">{{ notificationBody(n) }}</p>
          </div>
          <span class="text-xs text-[var(--color-text-placeholder)] flex-shrink-0">{{ formatRelativeTime(n.created_at) }}</span>
        </div>
      </BaseCard>

      <div v-else class="text-center py-20">
        <p class="text-4xl mb-4">🔔</p>
        <p class="text-sm text-[var(--color-text-secondary)]">You're all caught up!</p>
      </div>
    </div>
  </AppLayout>
</template>
