<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/lib/api'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import type { Webhook } from '@/types/models'
import type { ApiResponse } from '@/types/api'

const route = useRoute()
const ui = useUiStore()
const teamSlug = computed(() => route.params.team as string)

const webhooks = ref<Webhook[]>([])
const isLoading = ref(true)
const showCreate = ref(false)
const isCreating = ref(false)

const EVENTS = ['issue.created', 'issue.updated', 'issue.completed', 'comment.created', 'member.joined']

const form = ref({
  name: '',
  url: '',
  subscribed_events: [] as string[],
})

onMounted(async () => {
  try {
    const res = await api.get<ApiResponse<Webhook[]>>(`/teams/${teamSlug.value}/webhooks`)
    webhooks.value = res.data
  } finally {
    isLoading.value = false
  }
})

async function createWebhook() {
  isCreating.value = true
  try {
    const res = await api.post<ApiResponse<Webhook>>(`/teams/${teamSlug.value}/webhooks`, form.value)
    webhooks.value.unshift(res.data)
    ui.toast('Webhook created')
    showCreate.value = false
    form.value = { name: '', url: '', subscribed_events: [] }
  } catch {
    ui.toast('Failed to create webhook', 'error')
  } finally {
    isCreating.value = false
  }
}

async function toggleWebhook(webhook: Webhook) {
  const res = await api.patch<ApiResponse<Webhook>>(`/teams/${teamSlug.value}/webhooks/${webhook.id}`, { active: !webhook.active })
  const idx = webhooks.value.findIndex(w => w.id === webhook.id)
  if (idx >= 0) webhooks.value[idx] = res.data
}

async function deleteWebhook(id: number) {
  if (!confirm('Delete this webhook?')) return
  await api.delete(`/teams/${teamSlug.value}/webhooks/${id}`)
  webhooks.value = webhooks.value.filter(w => w.id !== id)
  ui.toast('Webhook deleted')
}
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-3xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Webhooks</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">Notify external services when events occur</p>
        </div>
        <BaseButton size="sm" @click="showCreate = true">Add webhook</BaseButton>
      </div>

      <template v-if="isLoading">
        <div class="space-y-3">
          <BaseSkeleton v-for="i in 3" :key="i" class="h-20 rounded-xl" />
        </div>
      </template>

      <div v-else-if="webhooks.length" class="space-y-3">
        <BaseCard v-for="wh in webhooks" :key="wh.id" class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-medium text-[var(--color-text-primary)]">{{ wh.name }}</span>
                <span :class="['text-xs px-1.5 py-0.5 rounded-full', wh.active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-zinc-500/10 text-zinc-500']">
                  {{ wh.active ? 'Active' : 'Disabled' }}
                </span>
                <span v-if="wh.last_response_code" :class="['text-xs', wh.last_response_code >= 200 && wh.last_response_code < 300 ? 'text-emerald-500' : 'text-red-500']">
                  {{ wh.last_response_code }}
                </span>
              </div>
              <p class="text-xs text-[var(--color-text-secondary)] truncate font-mono">{{ wh.url }}</p>
              <div class="flex gap-1 mt-2 flex-wrap">
                <span v-for="ev in wh.subscribed_events" :key="ev" class="text-[10px] px-1.5 py-0.5 bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)] rounded-full">{{ ev }}</span>
              </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <button @click="toggleWebhook(wh)" class="text-xs text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] px-2 py-1 rounded transition-colors">
                {{ wh.active ? 'Disable' : 'Enable' }}
              </button>
              <button @click="deleteWebhook(wh.id)" class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded transition-colors">Delete</button>
            </div>
          </div>
        </BaseCard>
      </div>

      <div v-else class="text-center py-20">
        <p class="text-4xl mb-4">🔗</p>
        <p class="text-sm text-[var(--color-text-secondary)] mb-4">No webhooks configured</p>
        <BaseButton size="sm" @click="showCreate = true">Add first webhook</BaseButton>
      </div>
    </div>

    <BaseModal :show="showCreate" title="Add webhook" @close="showCreate = false">
      <form @submit.prevent="createWebhook" class="space-y-4">
        <BaseInput v-model="form.name" label="Name" placeholder="e.g. Slack notifications" required autofocus />
        <BaseInput v-model="form.url" type="url" label="URL" placeholder="https://hooks.example.com/..." required />
        <div>
          <label class="block text-xs font-medium text-[var(--color-text-secondary)] mb-2">Events</label>
          <div class="space-y-1.5">
            <label v-for="ev in EVENTS" :key="ev" class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                :value="ev"
                v-model="form.subscribed_events"
                class="rounded border-[var(--color-border)] text-indigo-600 focus:ring-indigo-500"
              />
              <span class="text-sm text-[var(--color-text-primary)] font-mono">{{ ev }}</span>
            </label>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="ghost" @click="showCreate = false">Cancel</BaseButton>
          <BaseButton type="submit" :loading="isCreating">Create</BaseButton>
        </div>
      </form>
    </BaseModal>
  </AppLayout>
</template>
