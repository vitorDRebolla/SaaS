<script setup lang="ts">
import { computed } from 'vue'
import type { Issue } from '@/types/models'
import BaseAvatar from '@/components/ui/BaseAvatar.vue'
import PriorityBadge from '@/components/ui/PriorityBadge.vue'
import { formatDate } from '@/lib/utils'

const props = defineProps<{ issue: Issue; teamSlug: string }>()
const emit = defineEmits<{ click: [issue: Issue] }>()

const dueDateClass = computed(() => {
  if (!props.issue.due_date) return ''
  const d = new Date(props.issue.due_date)
  const now = new Date()
  if (d < now) return 'text-red-500'
  const soon = new Date()
  soon.setDate(soon.getDate() + 3)
  if (d < soon) return 'text-amber-500'
  return 'text-[var(--color-text-secondary)]'
})
</script>

<template>
  <div
    @click="emit('click', issue)"
    class="group bg-[var(--color-bg-surface)] border border-[var(--color-border)] rounded-xl p-3.5 cursor-pointer hover:border-indigo-500/50 hover:shadow-sm transition-all"
  >
    <div class="flex items-start gap-2 mb-2.5">
      <span class="mt-0.5 w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: issue.status?.color ?? '#6b7280' }" />
      <p class="text-sm text-[var(--color-text-primary)] flex-1 leading-snug line-clamp-2">{{ issue.title }}</p>
    </div>

    <div class="flex items-center gap-2">
      <span class="text-[11px] text-[var(--color-text-placeholder)] font-mono">{{ issue.identifier }}</span>
      <PriorityBadge :priority="issue.priority" class="ml-auto" />
      <span v-if="issue.due_date" :class="['text-[11px]', dueDateClass]">{{ formatDate(issue.due_date) }}</span>
      <BaseAvatar v-if="issue.assignee" :user="issue.assignee" size="xs" />
    </div>

    <div v-if="issue.labels?.length" class="flex gap-1 mt-2 flex-wrap">
      <span
        v-for="label in issue.labels"
        :key="label.id"
        class="text-[10px] px-1.5 py-0.5 rounded-full font-medium"
        :style="{ backgroundColor: label.color + '22', color: label.color }"
      >{{ label.name }}</span>
    </div>
  </div>
</template>
