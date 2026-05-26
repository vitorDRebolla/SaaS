<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api } from '@/lib/api'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import { formatDate } from '@/lib/utils'

interface PersonalToken {
  id: number
  name: string
  last_used_at: string | null
  created_at: string
}

const ui = useUiStore()
const tokens = ref<PersonalToken[]>([])
const isLoading = ref(true)
const newTokenName = ref('')
const isCreating = ref(false)
const newTokenValue = ref('')

onMounted(async () => {
  try {
    const res = await api.get<{ data: PersonalToken[] }>('/auth/tokens')
    tokens.value = res.data
  } finally {
    isLoading.value = false
  }
})

async function createToken() {
  if (!newTokenName.value.trim()) return
  isCreating.value = true
  try {
    const res = await api.post<{ token: string; data: PersonalToken }>('/auth/tokens', { name: newTokenName.value })
    newTokenValue.value = res.token
    tokens.value.unshift(res.data)
    newTokenName.value = ''
    ui.toast('Token created — copy it now, it won\'t be shown again')
  } catch {
    ui.toast('Failed to create token', 'error')
  } finally {
    isCreating.value = false
  }
}

async function deleteToken(id: number) {
  if (!confirm('Revoke this token?')) return
  await api.delete(`/auth/tokens/${id}`)
  tokens.value = tokens.value.filter(t => t.id !== id)
  ui.toast('Token revoked')
}

function copyToken() {
  navigator.clipboard.writeText(newTokenValue.value)
  ui.toast('Copied to clipboard')
}
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-2xl">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">API Tokens</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">Personal access tokens for the Meridian API</p>
      </div>

      <!-- New token result -->
      <div v-if="newTokenValue" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl">
        <p class="text-sm font-semibold text-emerald-600 mb-2">Token created — copy it now</p>
        <div class="flex items-center gap-2">
          <code class="flex-1 text-xs bg-[var(--color-bg-elevated)] rounded-lg px-3 py-2 text-[var(--color-text-primary)] font-mono break-all">{{ newTokenValue }}</code>
          <BaseButton size="sm" variant="ghost" @click="copyToken">Copy</BaseButton>
        </div>
        <p class="text-xs text-emerald-600/70 mt-2">This token will not be shown again.</p>
      </div>

      <!-- Create form -->
      <BaseCard class="p-4 mb-6">
        <h3 class="text-sm font-semibold text-[var(--color-text-primary)] mb-3">Create new token</h3>
        <form @submit.prevent="createToken" class="flex gap-2">
          <BaseInput v-model="newTokenName" placeholder="Token name (e.g. CI/CD pipeline)" class="flex-1" />
          <BaseButton type="submit" :loading="isCreating" size="sm">Create</BaseButton>
        </form>
      </BaseCard>

      <!-- Tokens list -->
      <h3 class="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-3">Active tokens</h3>
      <BaseCard class="divide-y divide-[var(--color-border)]">
        <template v-if="isLoading">
          <div v-for="i in 3" :key="i" class="p-4 flex items-center gap-3">
            <BaseSkeleton class="h-4 flex-1 rounded" />
            <BaseSkeleton class="h-4 w-24 rounded" />
          </div>
        </template>
        <div v-else-if="tokens.length" v-for="token in tokens" :key="token.id" class="flex items-center gap-3 p-4">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-[var(--color-text-primary)]">{{ token.name }}</p>
            <p class="text-xs text-[var(--color-text-secondary)]">
              Created {{ formatDate(token.created_at) }}
              <span v-if="token.last_used_at"> · Last used {{ formatDate(token.last_used_at) }}</span>
              <span v-else> · Never used</span>
            </p>
          </div>
          <button @click="deleteToken(token.id)" class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded transition-colors">
            Revoke
          </button>
        </div>
        <div v-else class="p-8 text-center text-sm text-[var(--color-text-secondary)]">No tokens yet</div>
      </BaseCard>
    </div>
  </AppLayout>
</template>
