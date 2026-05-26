<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useIssuesStore } from '@/stores/issues'
import { useProjectsStore } from '@/stores/projects'
import { useTeamStore } from '@/stores/team'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { api } from '@/lib/api'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseAvatar from '@/components/ui/BaseAvatar.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import PriorityBadge from '@/components/ui/PriorityBadge.vue'
import { formatDate, formatRelativeTime, PRIORITY_CONFIG } from '@/lib/utils'
import type { Comment, ActivityLog } from '@/types/models'
import type { ApiResponse } from '@/types/api'

const route = useRoute()
const router = useRouter()
const issuesStore = useIssuesStore()
const projectsStore = useProjectsStore()
const teamStore = useTeamStore()
const auth = useAuthStore()
const ui = useUiStore()

const teamSlug = computed(() => route.params.team as string)
const projectId = computed(() => Number(route.params.project))
const issueId = computed(() => Number(route.params.issue))

const issue = computed(() => issuesStore.currentIssue)
const project = computed(() => projectsStore.currentProject)
const statuses = computed(() => project.value?.statuses ?? [])
const members = computed(() => teamStore.members.map(m => m.user))

const isLoading = ref(true)
const comments = ref<Comment[]>([])
const activity = ref<ActivityLog[]>([])
const commentText = ref('')
const isPosting = ref(false)
const activeTab = ref<'comments' | 'activity'>('comments')

onMounted(async () => {
  try {
    await Promise.all([
      issuesStore.fetchIssue(teamSlug.value, projectId.value, issueId.value),
      projectsStore.fetchProject(teamSlug.value, projectId.value),
      teamStore.fetchMembers(teamSlug.value),
    ])
    await loadComments()
  } finally {
    isLoading.value = false
  }
})

async function loadComments() {
  const [commentsRes, activityRes] = await Promise.all([
    api.get<ApiResponse<Comment[]>>(`/teams/${teamSlug.value}/projects/${projectId.value}/issues/${issueId.value}/comments`),
    api.get<ApiResponse<ActivityLog[]>>(`/teams/${teamSlug.value}/projects/${projectId.value}/issues/${issueId.value}/activity`),
  ])
  comments.value = commentsRes.data
  activity.value = activityRes.data
}

async function postComment() {
  if (!commentText.value.trim()) return
  isPosting.value = true
  try {
    const res = await api.post<ApiResponse<Comment>>(
      `/teams/${teamSlug.value}/projects/${projectId.value}/issues/${issueId.value}/comments`,
      { content: commentText.value }
    )
    comments.value.push(res.data)
    commentText.value = ''
  } catch {
    ui.toast('Failed to post comment', 'error')
  } finally {
    isPosting.value = false
  }
}

async function updateField(field: string, value: unknown) {
  if (!issue.value) return
  await issuesStore.updateIssue(teamSlug.value, projectId.value, issue.value.id, { [field]: value })
  ui.toast('Updated')
}
</script>

<template>
  <AppLayout>
    <div v-if="isLoading" class="p-6 max-w-5xl space-y-4">
      <BaseSkeleton class="h-8 w-2/3 rounded-lg" />
      <BaseSkeleton class="h-4 w-1/3 rounded" />
      <div class="grid grid-cols-3 gap-6 mt-6">
        <div class="col-span-2 space-y-4">
          <BaseSkeleton class="h-40 rounded-xl" />
        </div>
        <BaseSkeleton class="h-64 rounded-xl" />
      </div>
    </div>

    <div v-else-if="issue" class="flex flex-col lg:flex-row gap-0 h-full">
      <!-- Main content -->
      <div class="flex-1 min-w-0 overflow-y-auto p-6">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-[var(--color-text-secondary)] mb-4">
          <RouterLink :to="`/${teamSlug}/projects`" class="hover:text-[var(--color-text-primary)]">Projects</RouterLink>
          <span>/</span>
          <RouterLink :to="`/${teamSlug}/projects/${projectId}`" class="hover:text-[var(--color-text-primary)]">{{ project?.name }}</RouterLink>
          <span>/</span>
          <span class="font-mono text-[var(--color-text-placeholder)]">{{ issue.identifier }}</span>
        </nav>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-[var(--color-text-primary)] mb-1 leading-snug">{{ issue.title }}</h1>
        <div class="flex items-center gap-3 text-sm text-[var(--color-text-secondary)] mb-6">
          <BaseAvatar :user="issue.creator" size="xs" />
          <span>{{ issue.creator.name }}</span>
          <span>·</span>
          <span>{{ formatRelativeTime(issue.created_at) }}</span>
          <PriorityBadge :priority="issue.priority" />
        </div>

        <!-- Description -->
        <div class="prose prose-sm dark:prose-invert max-w-none mb-8">
          <p v-if="issue.description" class="text-[var(--color-text-secondary)] leading-relaxed whitespace-pre-wrap">{{ issue.description }}</p>
          <p v-else class="text-[var(--color-text-placeholder)] italic">No description provided.</p>
        </div>

        <!-- Tabs -->
        <div class="border-b border-[var(--color-border)] mb-6">
          <div class="flex gap-6">
            <button
              v-for="tab in ['comments', 'activity'] as const"
              :key="tab"
              @click="activeTab = tab"
              :class="[
                'pb-3 text-sm font-medium capitalize border-b-2 transition-colors -mb-px',
                activeTab === tab
                  ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]',
              ]"
            >
              {{ tab }}
              <span v-if="tab === 'comments'" class="ml-1 text-xs">{{ comments.length }}</span>
            </button>
          </div>
        </div>

        <!-- Comments -->
        <div v-if="activeTab === 'comments'" class="space-y-4">
          <div v-for="comment in comments" :key="comment.id" class="flex gap-3">
            <BaseAvatar :user="comment.user" size="sm" class="flex-shrink-0" />
            <div class="flex-1">
              <div class="flex items-baseline gap-2 mb-1">
                <span class="text-sm font-medium text-[var(--color-text-primary)]">{{ comment.user.name }}</span>
                <span class="text-xs text-[var(--color-text-secondary)]">{{ formatRelativeTime(comment.created_at) }}</span>
              </div>
              <div class="bg-[var(--color-bg-elevated)] rounded-xl px-4 py-3 text-sm text-[var(--color-text-primary)] whitespace-pre-wrap">{{ comment.content }}</div>
            </div>
          </div>

          <!-- New comment -->
          <div class="flex gap-3 mt-4">
            <BaseAvatar :user="auth.user" size="sm" class="flex-shrink-0" />
            <div class="flex-1">
              <textarea
                v-model="commentText"
                placeholder="Add a comment..."
                rows="3"
                class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-surface)] px-4 py-3 text-sm text-[var(--color-text-primary)] placeholder-[var(--color-text-placeholder)] focus:outline-none focus:border-indigo-500 resize-none"
              />
              <div class="flex justify-end mt-2">
                <BaseButton size="sm" :loading="isPosting" @click="postComment">Comment</BaseButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Activity -->
        <div v-else class="space-y-3">
          <div v-for="log in activity" :key="log.id" class="flex items-start gap-3">
            <BaseAvatar :user="log.causer" size="xs" class="flex-shrink-0 mt-0.5" />
            <div class="text-sm">
              <span class="font-medium text-[var(--color-text-primary)]">{{ log.causer?.name ?? 'System' }}</span>
              <span class="text-[var(--color-text-secondary)]"> {{ log.event }}</span>
              <span class="text-xs text-[var(--color-text-placeholder)] ml-2">{{ formatRelativeTime(log.created_at) }}</span>
            </div>
          </div>
          <div v-if="activity.length === 0" class="text-sm text-[var(--color-text-placeholder)] py-4">No activity yet</div>
        </div>
      </div>

      <!-- Sidebar -->
      <aside class="w-full lg:w-72 flex-shrink-0 border-t lg:border-t-0 lg:border-l border-[var(--color-border)] bg-[var(--color-bg-surface)] p-5 space-y-5 overflow-y-auto">
        <!-- Status -->
        <div>
          <label class="text-xs font-medium text-[var(--color-text-secondary)] block mb-1.5">Status</label>
          <select
            :value="issue.status_id"
            @change="updateField('status_id', Number(($event.target as HTMLSelectElement).value))"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-elevated)] px-3 py-2 text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-indigo-500"
          >
            <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <!-- Priority -->
        <div>
          <label class="text-xs font-medium text-[var(--color-text-secondary)] block mb-1.5">Priority</label>
          <select
            :value="issue.priority"
            @change="updateField('priority', ($event.target as HTMLSelectElement).value)"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-elevated)] px-3 py-2 text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-indigo-500"
          >
            <option v-for="(cfg, p) in PRIORITY_CONFIG" :key="p" :value="p">{{ cfg.label }}</option>
          </select>
        </div>

        <!-- Assignee -->
        <div>
          <label class="text-xs font-medium text-[var(--color-text-secondary)] block mb-1.5">Assignee</label>
          <select
            :value="issue.assignee_id ?? ''"
            @change="updateField('assignee_id', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-elevated)] px-3 py-2 text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-indigo-500"
          >
            <option value="">Unassigned</option>
            <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
          </select>
        </div>

        <!-- Due date -->
        <div>
          <label class="text-xs font-medium text-[var(--color-text-secondary)] block mb-1.5">Due date</label>
          <input
            type="date"
            :value="issue.due_date ? issue.due_date.slice(0, 10) : ''"
            @change="updateField('due_date', ($event.target as HTMLInputElement).value || null)"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-elevated)] px-3 py-2 text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-indigo-500"
          />
        </div>

        <!-- Labels -->
        <div v-if="issue.labels?.length">
          <label class="text-xs font-medium text-[var(--color-text-secondary)] block mb-1.5">Labels</label>
          <div class="flex gap-1.5 flex-wrap">
            <span
              v-for="label in issue.labels"
              :key="label.id"
              class="text-xs px-2 py-0.5 rounded-full font-medium"
              :style="{ backgroundColor: label.color + '22', color: label.color }"
            >{{ label.name }}</span>
          </div>
        </div>

        <!-- Metadata -->
        <div class="pt-3 border-t border-[var(--color-border)] space-y-2">
          <div class="flex justify-between text-xs">
            <span class="text-[var(--color-text-secondary)]">Created</span>
            <span class="text-[var(--color-text-primary)]">{{ formatDate(issue.created_at) }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-[var(--color-text-secondary)]">Updated</span>
            <span class="text-[var(--color-text-primary)]">{{ formatRelativeTime(issue.updated_at) }}</span>
          </div>
        </div>
      </aside>
    </div>
  </AppLayout>
</template>
