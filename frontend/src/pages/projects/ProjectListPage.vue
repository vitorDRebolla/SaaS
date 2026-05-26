<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import { useProjectsStore } from '@/stores/projects'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseTextarea from '@/components/ui/BaseTextarea.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'

const route = useRoute()
const router = useRouter()
const teamStore = useTeamStore()
const projectsStore = useProjectsStore()
const ui = useUiStore()

const teamSlug = computed(() => route.params.team as string)
const showCreate = ref(false)
const isCreating = ref(false)

const form = ref({ name: '', identifier: '', description: '', color: '#6366f1' })

const COLORS = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#f97316']

function suggestIdentifier(name: string) {
  return name.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 4) || ''
}

function onNameInput() {
  if (!form.value.identifier || form.value.identifier === suggestIdentifier(form.value.name.slice(0, -1))) {
    form.value.identifier = suggestIdentifier(form.value.name)
  }
}

async function createProject() {
  if (!form.value.name.trim() || !form.value.identifier.trim()) return
  isCreating.value = true
  try {
    const project = await projectsStore.createProject(teamSlug.value, {
      name: form.value.name,
      identifier: form.value.identifier.toUpperCase(),
      description: form.value.description,
      color: form.value.color,
    })
    ui.toast('Project created')
    showCreate.value = false
    form.value = { name: '', identifier: '', description: '', color: '#6366f1' }
    router.push(`/${teamSlug.value}/projects/${project.id}`)
  } catch {
    ui.toast('Failed to create project', 'error')
  } finally {
    isCreating.value = false
  }
}

onMounted(async () => {
  await projectsStore.fetchProjects(teamSlug.value)
})
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-5xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Projects</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">{{ projectsStore.projects.length }} projects in {{ teamStore.currentTeam?.name }}</p>
        </div>
        <BaseButton @click="showCreate = true" size="sm">New Project</BaseButton>
      </div>

      <!-- Loading -->
      <div v-if="projectsStore.isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <BaseSkeleton v-for="i in 6" :key="i" class="h-36 rounded-xl" />
      </div>

      <!-- Empty -->
      <EmptyState
        v-else-if="projectsStore.projects.length === 0"
        title="No projects yet"
        description="Create your first project to start tracking issues."
        action="Create Project"
        @action="showCreate = true"
      />

      <!-- Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <RouterLink
          v-for="project in projectsStore.projects"
          :key="project.id"
          :to="`/${teamSlug}/projects/${project.id}`"
          class="group block"
        >
          <BaseCard class="h-full p-5 hover:border-indigo-500/50 hover:shadow-sm transition-all">
            <div class="flex items-start gap-3 mb-4">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                :style="{ backgroundColor: project.color }"
              >
                {{ project.identifier }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-[var(--color-text-primary)] truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                  {{ project.name }}
                </h3>
                <p v-if="project.description" class="text-xs text-[var(--color-text-secondary)] mt-0.5 line-clamp-2">{{ project.description }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4 text-xs text-[var(--color-text-secondary)]">
              <span>{{ project.issues_count ?? 0 }} issues</span>
              <span :class="project.status === 'active' ? 'text-emerald-500' : 'text-zinc-500'" class="capitalize">{{ project.status }}</span>
            </div>
          </BaseCard>
        </RouterLink>

        <!-- Add card -->
        <button
          @click="showCreate = true"
          class="flex items-center justify-center h-36 rounded-xl border-2 border-dashed border-[var(--color-border)] text-sm text-[var(--color-text-secondary)] hover:border-indigo-500 hover:text-indigo-500 transition-colors"
        >
          + New Project
        </button>
      </div>
    </div>

    <!-- Create modal -->
    <BaseModal :show="showCreate" title="New Project" @close="showCreate = false">
      <form @submit.prevent="createProject" class="space-y-4">
        <BaseInput v-model="form.name" label="Project name" placeholder="e.g. Backend API" required autofocus @input="onNameInput" />
        <BaseInput v-model="form.identifier" label="Identifier" placeholder="e.g. API" maxlength="4" required>
          <template #hint>Short uppercase prefix for issues (e.g. API-1)</template>
        </BaseInput>
        <BaseTextarea v-model="form.description" label="Description" placeholder="What is this project about?" :rows="2" />
        <div>
          <label class="block text-xs font-medium text-[var(--color-text-secondary)] mb-2">Color</label>
          <div class="flex gap-2">
            <button
              v-for="c in COLORS"
              :key="c"
              type="button"
              @click="form.color = c"
              class="w-7 h-7 rounded-lg transition-transform hover:scale-110"
              :style="{ backgroundColor: c }"
              :class="form.color === c ? 'ring-2 ring-offset-2 ring-indigo-500' : ''"
            />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="ghost" @click="showCreate = false">Cancel</BaseButton>
          <BaseButton type="submit" :loading="isCreating">Create Project</BaseButton>
        </div>
      </form>
    </BaseModal>
  </AppLayout>
</template>
