<script setup lang="ts">
import { ref, computed } from 'vue'
import { useIssuesStore } from '@/stores/issues'
import { useUiStore } from '@/stores/ui'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseTextarea from '@/components/ui/BaseTextarea.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { IssueStatus, IssueLabel, User, Priority } from '@/types/models'

const props = defineProps<{
  show: boolean
  teamSlug: string
  projectId: number
  statuses: IssueStatus[]
  labels: IssueLabel[]
  members: User[]
}>()

const emit = defineEmits<{ close: []; created: [] }>()

const issuesStore = useIssuesStore()
const ui = useUiStore()

const form = ref({
  title: '',
  description: '',
  priority: 'medium' as Priority,
  status_id: null as number | null,
  assignee_id: null as string | number | null,
  due_date: '',
})

const defaultStatus = computed(() => props.statuses.find(s => s.type === 'backlog') ?? props.statuses[0])

const isSubmitting = ref(false)

async function submit() {
  if (!form.value.title.trim()) return
  isSubmitting.value = true
  try {
    await issuesStore.createIssue(props.teamSlug, props.projectId, {
      title: form.value.title,
      description: form.value.description,
      priority: form.value.priority,
      due_date: form.value.due_date || null,
      status_id: Number(form.value.status_id) || (defaultStatus.value?.id ?? 0),
      assignee_id: form.value.assignee_id ? Number(form.value.assignee_id) : null,
    })
    ui.toast('Issue created')
    emit('created')
    emit('close')
    form.value = { title: '', description: '', priority: 'medium', status_id: null, assignee_id: null as string | number | null, due_date: '' }
  } catch {
    ui.toast('Failed to create issue', 'error')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <BaseModal :show="show" title="New Issue" @close="emit('close')">
    <form @submit.prevent="submit" class="space-y-4">
      <BaseInput v-model="form.title" label="Title" placeholder="Issue title..." required autofocus />
      <BaseTextarea v-model="form.description" label="Description" placeholder="Add more details..." :rows="3" />

      <div class="grid grid-cols-2 gap-4">
        <BaseSelect v-model="form.status_id" label="Status">
          <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
        </BaseSelect>
        <BaseSelect v-model="form.priority" label="Priority">
          <option value="none">No priority</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </BaseSelect>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <BaseSelect v-model="form.assignee_id" label="Assignee">
          <option :value="null">Unassigned</option>
          <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
        </BaseSelect>
        <BaseInput v-model="form.due_date" label="Due date" type="date" />
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <BaseButton type="button" variant="ghost" @click="emit('close')">Cancel</BaseButton>
        <BaseButton type="submit" :loading="isSubmitting">Create Issue</BaseButton>
      </div>
    </form>
  </BaseModal>
</template>
