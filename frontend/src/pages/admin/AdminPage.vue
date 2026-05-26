<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api } from '@/lib/api'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import type { FeatureFlag } from '@/types/models'
import type { ApiResponse } from '@/types/api'

const ui = useUiStore()
const flags = ref<FeatureFlag[]>([])
const isLoading = ref(true)

onMounted(async () => {
  try {
    const res = await api.get<ApiResponse<FeatureFlag[]>>('/admin/feature-flags')
    flags.value = res.data
  } finally {
    isLoading.value = false
  }
})

async function toggleFlag(flag: FeatureFlag) {
  const res = await api.patch<ApiResponse<FeatureFlag>>(`/admin/feature-flags/${flag.id}`, {
    globally_enabled: !flag.globally_enabled,
  })
  const idx = flags.value.findIndex(f => f.id === flag.id)
  if (idx >= 0) flags.value[idx] = res.data
  ui.toast(`${flag.name} ${res.data.globally_enabled ? 'enabled' : 'disabled'}`)
}

async function updateRollout(flag: FeatureFlag, pct: number) {
  const res = await api.patch<ApiResponse<FeatureFlag>>(`/admin/feature-flags/${flag.id}`, {
    rollout_percentage: pct,
  })
  const idx = flags.value.findIndex(f => f.id === flag.id)
  if (idx >= 0) flags.value[idx] = res.data
}
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-4xl">
      <div class="mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-xl">🛡</div>
        <div>
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Admin</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">System-wide settings and feature flags</p>
        </div>
      </div>

      <!-- Feature flags -->
      <section class="mb-8">
        <h2 class="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-4">Feature Flags</h2>
        <BaseCard class="divide-y divide-[var(--color-border)]">
          <template v-if="isLoading">
            <div v-for="i in 4" :key="i" class="p-4 flex items-center gap-4">
              <BaseSkeleton class="h-4 flex-1 rounded" />
              <BaseSkeleton class="h-7 w-12 rounded-full" />
            </div>
          </template>
          <div v-else-if="flags.length" v-for="flag in flags" :key="flag.id" class="p-4">
            <div class="flex items-center gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                  <p class="text-sm font-medium text-[var(--color-text-primary)] font-mono">{{ flag.name }}</p>
                  <span :class="['text-[10px] px-1.5 py-0.5 rounded-full', flag.globally_enabled ? 'bg-emerald-500/10 text-emerald-600' : 'bg-zinc-500/10 text-zinc-500']">
                    {{ flag.globally_enabled ? 'On' : 'Off' }}
                  </span>
                </div>
                <p v-if="flag.description" class="text-xs text-[var(--color-text-secondary)]">{{ flag.description }}</p>
              </div>

              <!-- Rollout -->
              <div class="flex items-center gap-3 flex-shrink-0">
                <div v-if="!flag.globally_enabled" class="flex items-center gap-2">
                  <span class="text-xs text-[var(--color-text-secondary)]">Rollout</span>
                  <input
                    type="range"
                    min="0"
                    max="100"
                    :value="flag.rollout_percentage"
                    @change="updateRollout(flag, Number(($event.target as HTMLInputElement).value))"
                    class="w-24 accent-indigo-600"
                  />
                  <span class="text-xs text-[var(--color-text-secondary)] w-8">{{ flag.rollout_percentage }}%</span>
                </div>

                <!-- Toggle -->
                <button
                  @click="toggleFlag(flag)"
                  :class="[
                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                    flag.globally_enabled ? 'bg-indigo-600' : 'bg-[var(--color-border)]',
                  ]"
                >
                  <span
                    :class="[
                      'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                      flag.globally_enabled ? 'translate-x-6' : 'translate-x-1',
                    ]"
                  />
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center text-sm text-[var(--color-text-secondary)]">No feature flags configured</div>
        </BaseCard>
      </section>

      <!-- Quick links -->
      <section>
        <h2 class="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-4">System</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <BaseCard class="p-4 flex items-center gap-3 cursor-default">
            <span class="text-2xl">📊</span>
            <div>
              <p class="text-sm font-medium text-[var(--color-text-primary)]">Horizon</p>
              <p class="text-xs text-[var(--color-text-secondary)]">Queue dashboard</p>
            </div>
          </BaseCard>
          <BaseCard class="p-4 flex items-center gap-3 cursor-default">
            <span class="text-2xl">📮</span>
            <div>
              <p class="text-sm font-medium text-[var(--color-text-primary)]">Mailpit</p>
              <p class="text-xs text-[var(--color-text-secondary)]">Email preview</p>
            </div>
          </BaseCard>
          <BaseCard class="p-4 flex items-center gap-3 cursor-default">
            <span class="text-2xl">🗄</span>
            <div>
              <p class="text-sm font-medium text-[var(--color-text-primary)]">MinIO</p>
              <p class="text-xs text-[var(--color-text-secondary)]">File storage</p>
            </div>
          </BaseCard>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
