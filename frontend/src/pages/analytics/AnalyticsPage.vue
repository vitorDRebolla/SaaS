<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import { useProjectsStore } from '@/stores/projects'
import { api } from '@/lib/api'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import type { AnalyticsSnapshot, ProjectAnalytics } from '@/types/models'
import type { ApiResponse } from '@/types/api'

const route = useRoute()
const teamStore = useTeamStore()
const projectsStore = useProjectsStore()

const teamSlug = computed(() => route.params.team as string)
const isLoading = ref(true)
const snapshot = ref<AnalyticsSnapshot | null>(null)
const projectAnalytics = ref<ProjectAnalytics | null>(null)
const selectedProjectId = ref<number | null>(null)

onMounted(async () => {
  try {
    await Promise.all([
      teamStore.fetchTeam(teamSlug.value),
      projectsStore.fetchProjects(teamSlug.value),
    ])
    const snapshotRes = await api.get<AnalyticsSnapshot>(`/teams/${teamSlug.value}/analytics`)
    snapshot.value = snapshotRes
    const firstProject = projectsStore.projects[0]
    if (firstProject) {
      selectedProjectId.value = firstProject.id
      await loadProjectAnalytics(firstProject.id)
    }
  } finally {
    isLoading.value = false
  }
})

async function loadProjectAnalytics(projectId: number) {
  try {
    const res = await api.get<ApiResponse<ProjectAnalytics>>(
      `/teams/${teamSlug.value}/projects/${projectId}/analytics`
    )
    projectAnalytics.value = res.data
  } catch {
    projectAnalytics.value = null
  }
}

async function selectProject(id: number) {
  selectedProjectId.value = id
  await loadProjectAnalytics(id)
}

function barWidth(count: number, max: number) {
  if (!max) return '0%'
  return `${Math.round((count / max) * 100)}%`
}

const maxVelocity = computed(() =>
  Math.max(...(snapshot.value?.velocity ?? []).map(v => v.count), 1)
)

const completionRate = computed(() => {
  if (!snapshot.value) return 0
  const { total_issues, completed_issues } = snapshot.value
  return total_issues ? Math.round((completed_issues / total_issues) * 100) : 0
})
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-6xl">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Analytics</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">Team performance & project insights</p>
      </div>

      <!-- Team stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <template v-if="isLoading">
          <BaseSkeleton v-for="i in 4" :key="i" class="h-24 rounded-xl" />
        </template>
        <template v-else-if="snapshot">
          <BaseCard class="p-4">
            <p class="text-xs text-[var(--color-text-secondary)]">Total Issues</p>
            <p class="text-3xl font-bold text-indigo-500 mt-1">{{ snapshot.total_issues }}</p>
          </BaseCard>
          <BaseCard class="p-4">
            <p class="text-xs text-[var(--color-text-secondary)]">Completed</p>
            <p class="text-3xl font-bold text-emerald-500 mt-1">{{ snapshot.completed_issues }}</p>
          </BaseCard>
          <BaseCard class="p-4">
            <p class="text-xs text-[var(--color-text-secondary)]">In Progress</p>
            <p class="text-3xl font-bold text-amber-500 mt-1">{{ snapshot.in_progress }}</p>
          </BaseCard>
          <BaseCard class="p-4">
            <p class="text-xs text-[var(--color-text-secondary)]">Completion Rate</p>
            <p class="text-3xl font-bold text-violet-500 mt-1">{{ completionRate }}%</p>
          </BaseCard>
        </template>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Velocity chart -->
        <BaseCard class="p-5">
          <h3 class="text-sm font-semibold text-[var(--color-text-primary)] mb-4">Weekly Velocity</h3>
          <template v-if="isLoading">
            <BaseSkeleton class="h-40 rounded-lg" />
          </template>
          <div v-else-if="snapshot?.velocity?.length" class="space-y-2">
            <div v-for="week in snapshot.velocity.slice(-8)" :key="week.week" class="flex items-center gap-3">
              <span class="text-xs text-[var(--color-text-secondary)] w-20 text-right flex-shrink-0">{{ week.week }}</span>
              <div class="flex-1 bg-[var(--color-bg-elevated)] rounded-full h-5 overflow-hidden">
                <div
                  class="h-full bg-indigo-600 rounded-full transition-all duration-500"
                  :style="{ width: barWidth(week.count, maxVelocity) }"
                />
              </div>
              <span class="text-xs font-mono text-[var(--color-text-primary)] w-6 text-right">{{ week.count }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-[var(--color-text-placeholder)] text-center py-8">No velocity data yet</p>
        </BaseCard>

        <!-- Project selector + breakdown -->
        <BaseCard class="p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-[var(--color-text-primary)]">Project Breakdown</h3>
            <select
              :value="selectedProjectId"
              @change="selectProject(Number(($event.target as HTMLSelectElement).value))"
              class="text-xs border border-[var(--color-border)] rounded-lg px-2 py-1 bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)] focus:outline-none focus:border-indigo-500"
            >
              <option v-for="p in projectsStore.projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <template v-if="isLoading || !projectAnalytics">
            <BaseSkeleton class="h-40 rounded-lg" />
          </template>
          <div v-else class="space-y-2">
            <div v-for="status in projectAnalytics.status_breakdown" :key="status.name" class="flex items-center gap-3">
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: status.color }" />
              <span class="text-xs text-[var(--color-text-secondary)] flex-1">{{ status.name }}</span>
              <div class="w-24 bg-[var(--color-bg-elevated)] rounded-full h-3 overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :style="{ width: barWidth(status.count, projectAnalytics!.total_issues), backgroundColor: status.color }"
                />
              </div>
              <span class="text-xs font-mono text-[var(--color-text-primary)] w-6 text-right">{{ status.count }}</span>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Member workload -->
      <BaseCard v-if="projectAnalytics?.member_workload?.length" class="p-5">
        <h3 class="text-sm font-semibold text-[var(--color-text-primary)] mb-4">Member Workload</h3>
        <div class="space-y-3">
          <div
            v-for="item in projectAnalytics.member_workload"
            :key="item.assignee_id"
            class="flex items-center gap-3"
          >
            <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
              {{ item.assignee.name.charAt(0) }}
            </div>
            <span class="text-sm text-[var(--color-text-primary)] w-32 truncate">{{ item.assignee.name }}</span>
            <div class="flex-1 bg-[var(--color-bg-elevated)] rounded-full h-4 overflow-hidden">
              <div
                class="h-full bg-indigo-600/70 rounded-full"
                :style="{ width: barWidth(item.count, Math.max(...projectAnalytics!.member_workload.map(i => i.count))) }"
              />
            </div>
            <span class="text-xs font-mono text-[var(--color-text-secondary)] w-8 text-right">{{ item.count }}</span>
          </div>
        </div>
      </BaseCard>
    </div>
  </AppLayout>
</template>
