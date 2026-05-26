<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectsStore } from '@/stores/projects'
import { useIssuesStore } from '@/stores/issues'
import { useTeamStore } from '@/stores/team'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import IssueCard from '@/components/features/IssueCard.vue'
import CreateIssueModal from '@/components/features/CreateIssueModal.vue'
import type { Issue, IssueStatus } from '@/types/models'

const route = useRoute()
const router = useRouter()
const projectsStore = useProjectsStore()
const issuesStore = useIssuesStore()
const teamStore = useTeamStore()
const ui = useUiStore()

const teamSlug = computed(() => route.params.team as string)
const projectId = computed(() => Number(route.params.project))
const showCreateIssue = ref(false)
const isLoading = ref(true)

const project = computed(() => projectsStore.currentProject)
const statuses = computed(() => project.value?.statuses ?? [])
const labels = computed(() => project.value?.labels ?? [])
const members = computed(() => teamStore.members.map(m => m.user))

const issuesByStatus = computed(() => {
  const map = new Map<number, Issue[]>()
  for (const status of statuses.value) map.set(status.id, [])
  for (const issue of issuesStore.issues) {
    if (map.has(issue.status_id)) map.get(issue.status_id)!.push(issue)
  }
  return map
})

function openIssue(issue: Issue) {
  router.push(`/${teamSlug.value}/projects/${projectId.value}/issues/${issue.id}`)
}

const view = ref<'board' | 'list'>('board')

const priorityFilter = ref('')
const assigneeFilter = ref<number | ''>('')

const filteredIssuesByStatus = computed(() => {
  const filtered = issuesStore.issues.filter(i => {
    if (priorityFilter.value && i.priority !== priorityFilter.value) return false
    if (assigneeFilter.value && i.assignee_id !== assigneeFilter.value) return false
    return true
  })
  const map = new Map<number, Issue[]>()
  for (const status of statuses.value) map.set(status.id, [])
  for (const issue of filtered) {
    if (map.has(issue.status_id)) map.get(issue.status_id)!.push(issue)
  }
  return map
})

onMounted(async () => {
  try {
    await Promise.all([
      projectsStore.fetchProject(teamSlug.value, projectId.value),
      issuesStore.fetchIssues(teamSlug.value, projectId.value),
      teamStore.fetchMembers(teamSlug.value),
    ])
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <AppLayout>
    <div class="flex flex-col h-full">
      <!-- Project header -->
      <div class="border-b border-[var(--color-border)] px-6 py-4 flex-shrink-0">
        <div class="flex items-center gap-3 mb-3">
          <div
            class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
            :style="{ backgroundColor: project?.color ?? '#6366f1' }"
          >
            {{ project?.identifier }}
          </div>
          <div>
            <h1 class="text-lg font-bold text-[var(--color-text-primary)]">{{ project?.name }}</h1>
          </div>
          <div class="ml-auto flex items-center gap-2">
            <!-- View toggle -->
            <div class="flex rounded-lg border border-[var(--color-border)] overflow-hidden">
              <button
                v-for="v in ['board', 'list'] as const"
                :key="v"
                @click="view = v"
                :class="[
                  'px-3 py-1.5 text-xs font-medium transition-colors capitalize',
                  view === v ? 'bg-indigo-600 text-white' : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-elevated)]',
                ]"
              >{{ v }}</button>
            </div>
            <BaseButton size="sm" @click="showCreateIssue = true">New Issue</BaseButton>
          </div>
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3">
          <select
            v-model="priorityFilter"
            class="text-xs border border-[var(--color-border)] rounded-lg px-2.5 py-1.5 bg-[var(--color-bg-surface)] text-[var(--color-text-secondary)] focus:outline-none focus:border-indigo-500"
          >
            <option value="">All priorities</option>
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
            <option value="none">No priority</option>
          </select>
          <select
            v-model="assigneeFilter"
            class="text-xs border border-[var(--color-border)] rounded-lg px-2.5 py-1.5 bg-[var(--color-bg-surface)] text-[var(--color-text-secondary)] focus:outline-none focus:border-indigo-500"
          >
            <option value="">All assignees</option>
            <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
          <span class="text-xs text-[var(--color-text-placeholder)]">{{ issuesStore.issues.length }} issues</span>
        </div>
      </div>

      <!-- Board view -->
      <div v-if="view === 'board'" class="flex-1 overflow-x-auto">
        <div class="flex gap-4 p-6 h-full" style="min-width: max-content">
          <template v-if="isLoading">
            <div v-for="i in 4" :key="i" class="w-72 flex-shrink-0">
              <BaseSkeleton class="h-8 rounded-lg mb-3" />
              <div class="space-y-2">
                <BaseSkeleton v-for="j in 3" :key="j" class="h-24 rounded-xl" />
              </div>
            </div>
          </template>

          <template v-else>
            <div
              v-for="status in statuses"
              :key="status.id"
              class="w-72 flex-shrink-0 flex flex-col"
            >
              <!-- Column header -->
              <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: status.color }" />
                <span class="text-sm font-medium text-[var(--color-text-primary)]">{{ status.name }}</span>
                <span class="text-xs text-[var(--color-text-placeholder)] ml-auto">
                  {{ filteredIssuesByStatus.get(status.id)?.length ?? 0 }}
                </span>
              </div>

              <!-- Issues -->
              <div class="flex-1 space-y-2 overflow-y-auto pb-4">
                <IssueCard
                  v-for="issue in filteredIssuesByStatus.get(status.id)"
                  :key="issue.id"
                  :issue="issue"
                  :teamSlug="teamSlug"
                  @click="openIssue"
                />
                <button
                  @click="showCreateIssue = true"
                  class="w-full text-left px-3 py-2 text-xs text-[var(--color-text-placeholder)] hover:text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-elevated)] rounded-lg transition-colors"
                >
                  + Add issue
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- List view -->
      <div v-else class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto p-6">
          <template v-if="isLoading">
            <div class="space-y-2">
              <BaseSkeleton v-for="i in 8" :key="i" class="h-14 rounded-xl" />
            </div>
          </template>
          <template v-else>
            <div
              v-for="status in statuses"
              :key="status.id"
              class="mb-6"
            >
              <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: status.color }" />
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">{{ status.name }}</span>
                <span class="text-xs text-[var(--color-text-placeholder)]">{{ filteredIssuesByStatus.get(status.id)?.length ?? 0 }}</span>
              </div>
              <div class="divide-y divide-[var(--color-border)] rounded-xl border border-[var(--color-border)] overflow-hidden bg-[var(--color-bg-surface)]">
                <RouterLink
                  v-for="issue in filteredIssuesByStatus.get(status.id)"
                  :key="issue.id"
                  :to="`/${teamSlug}/projects/${projectId}/issues/${issue.id}`"
                  class="flex items-center gap-3 px-4 py-3 hover:bg-[var(--color-bg-elevated)] transition-colors"
                >
                  <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: issue.status?.color }" />
                  <span class="text-xs font-mono text-[var(--color-text-placeholder)] w-16 flex-shrink-0">{{ issue.identifier }}</span>
                  <span class="text-sm text-[var(--color-text-primary)] flex-1 truncate">{{ issue.title }}</span>
                  <span v-if="issue.assignee" class="text-xs text-[var(--color-text-secondary)] hidden sm:block">{{ issue.assignee.name }}</span>
                </RouterLink>
                <div v-if="!filteredIssuesByStatus.get(status.id)?.length" class="px-4 py-3 text-xs text-[var(--color-text-placeholder)]">
                  No issues
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <CreateIssueModal
      :show="showCreateIssue"
      :teamSlug="teamSlug"
      :projectId="projectId"
      :statuses="statuses"
      :labels="labels"
      :members="members"
      @close="showCreateIssue = false"
      @created="issuesStore.fetchIssues(teamSlug, projectId)"
    />
  </AppLayout>
</template>
