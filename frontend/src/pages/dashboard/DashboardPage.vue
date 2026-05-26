<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import { useProjectsStore } from '@/stores/projects'
import { useIssuesStore } from '@/stores/issues'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import PriorityBadge from '@/components/ui/PriorityBadge.vue'
import { formatRelativeTime } from '@/lib/utils'
import type { Issue } from '@/types/models'

const route = useRoute()
const teamStore = useTeamStore()
const projectsStore = useProjectsStore()
const issuesStore = useIssuesStore()
const authStore = useAuthStore()

const teamSlug = computed(() => route.params.team as string)
const isLoading = ref(true)
const recentIssues = ref<Issue[]>([])

onMounted(async () => {
  try {
    await Promise.all([
      teamStore.fetchTeam(teamSlug.value),
      projectsStore.fetchProjects(teamSlug.value),
    ])
    const firstProject = projectsStore.projects[0]
    if (firstProject) {
      await issuesStore.fetchIssues(teamSlug.value, firstProject.id, { sort: '-updated_at' })
      recentIssues.value = issuesStore.issues.slice(0, 8)
    }
  } finally {
    isLoading.value = false
  }
})

const stats = computed(() => {
  const issues = issuesStore.issues
  return [
    { label: 'Total Issues', value: issues.length, color: 'text-indigo-500' },
    { label: 'In Progress', value: issues.filter(i => i.status?.type === 'started').length, color: 'text-amber-500' },
    { label: 'Completed', value: issues.filter(i => i.status?.type === 'completed').length, color: 'text-emerald-500' },
    { label: 'Projects', value: projectsStore.projects.length, color: 'text-violet-500' },
  ]
})
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-7xl">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">
          Good morning, {{ authStore.user?.name?.split(' ')[0] }} 👋
        </h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">
          {{ teamStore.currentTeam?.name }} · {{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }) }}
        </p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <template v-if="isLoading">
          <BaseSkeleton v-for="i in 4" :key="i" class="h-24 rounded-xl" />
        </template>
        <BaseCard v-else v-for="stat in stats" :key="stat.label" class="p-4">
          <p class="text-sm text-[var(--color-text-secondary)]">{{ stat.label }}</p>
          <p :class="['text-3xl font-bold mt-1', stat.color]">{{ stat.value }}</p>
        </BaseCard>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Issues -->
        <div class="lg:col-span-2">
          <h2 class="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-3">Recent Activity</h2>
          <BaseCard class="divide-y divide-[var(--color-border)]">
            <template v-if="isLoading">
              <div v-for="i in 5" :key="i" class="p-3.5 flex gap-3">
                <BaseSkeleton class="w-4 h-4 rounded-full mt-0.5" />
                <BaseSkeleton class="h-4 flex-1 rounded" />
              </div>
            </template>
            <template v-else-if="recentIssues.length === 0">
              <div class="p-8 text-center text-sm text-[var(--color-text-secondary)]">No issues yet</div>
            </template>
            <RouterLink
              v-else
              v-for="issue in recentIssues"
              :key="issue.id"
              :to="`/${teamSlug}/projects/${issue.project_id}/issues/${issue.id}`"
              class="flex items-start gap-3 p-3.5 hover:bg-[var(--color-bg-elevated)] transition-colors"
            >
              <span
                class="mt-0.5 w-2.5 h-2.5 rounded-full flex-shrink-0"
                :style="{ backgroundColor: issue.status?.color ?? '#6b7280' }"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm text-[var(--color-text-primary)] truncate">{{ issue.title }}</p>
                <p class="text-xs text-[var(--color-text-secondary)] mt-0.5">
                  {{ issue.identifier }} · {{ formatRelativeTime(issue.updated_at) }}
                </p>
              </div>
              <PriorityBadge :priority="issue.priority" />
            </RouterLink>
          </BaseCard>
        </div>

        <!-- Projects sidebar -->
        <div>
          <h2 class="text-sm font-semibold text-[var(--color-text-secondary)] uppercase tracking-wider mb-3">Projects</h2>
          <div class="space-y-2">
            <template v-if="isLoading">
              <BaseSkeleton v-for="i in 3" :key="i" class="h-16 rounded-xl" />
            </template>
            <RouterLink
              v-else
              v-for="project in projectsStore.projects.slice(0, 6)"
              :key="project.id"
              :to="`/${teamSlug}/projects/${project.id}`"
              class="flex items-center gap-3 p-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-surface)] hover:bg-[var(--color-bg-elevated)] transition-colors"
            >
              <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-white text-xs font-bold" :style="{ backgroundColor: project.color }">
                {{ project.identifier }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[var(--color-text-primary)] truncate">{{ project.name }}</p>
                <p class="text-xs text-[var(--color-text-secondary)]">{{ project.issues_count ?? 0 }} issues</p>
              </div>
            </RouterLink>
            <RouterLink
              v-if="!isLoading && projectsStore.projects.length === 0"
              :to="`/${teamSlug}/projects`"
              class="flex items-center justify-center p-4 rounded-xl border border-dashed border-[var(--color-border)] text-sm text-[var(--color-text-secondary)] hover:border-indigo-500 hover:text-indigo-500 transition-colors"
            >
              + Create first project
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
