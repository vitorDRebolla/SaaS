<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import { useProjectsStore } from '@/stores/projects'
import { useUiStore } from '@/stores/ui'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { ApiError } from '@/types/api'

const router = useRouter()
const team = useTeamStore()
const projects = useProjectsStore()
const ui = useUiStore()

const step = ref(1)
const teamName = ref('')
const projectName = ref('')
const identifier = ref('')
const isLoading = ref(false)
let createdTeam: Awaited<ReturnType<typeof team.createTeam>> | null = null

function suggestIdentifier(name: string) {
  identifier.value = name.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 5)
}

async function createTeam() {
  isLoading.value = true
  try {
    createdTeam = await team.createTeam(teamName.value)
    step.value = 2
  } catch (e) {
    ui.toast((e as ApiError).message, 'error')
  } finally {
    isLoading.value = false
  }
}

async function createProject() {
  if (!createdTeam) return
  isLoading.value = true
  try {
    await projects.createProject(createdTeam.slug, { name: projectName.value, identifier: identifier.value })
    router.push(`/${createdTeam.slug}`)
    ui.toast('Workspace ready! Welcome to Meridian.', 'success')
  } catch (e) {
    ui.toast((e as ApiError).message, 'error')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[var(--color-bg-base)] p-4">
    <div class="w-full max-w-sm">
      <!-- Steps indicator -->
      <div class="flex items-center gap-2 mb-8 justify-center">
        <div v-for="i in 2" :key="i" :class="[
          'h-1.5 rounded-full transition-all',
          i <= step ? 'bg-indigo-600 w-8' : 'bg-[var(--color-border)] w-4'
        ]" />
      </div>

      <!-- Step 1: Team -->
      <template v-if="step === 1">
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Create your workspace</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-1">This will be your team's home in Meridian</p>
        </div>
        <form @submit.prevent="createTeam" class="flex flex-col gap-4">
          <BaseInput v-model="teamName" label="Workspace name" placeholder="Acme Engineering" required />
          <BaseButton type="submit" variant="primary" size="lg" :loading="isLoading" class="w-full mt-2">
            Continue →
          </BaseButton>
        </form>
      </template>

      <!-- Step 2: First project -->
      <template v-else>
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Create your first project</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-1">Projects are where your team's work lives</p>
        </div>
        <form @submit.prevent="createProject" class="flex flex-col gap-4">
          <BaseInput v-model="projectName" label="Project name" placeholder="Platform Core" required @input="suggestIdentifier(projectName)" />
          <BaseInput v-model="identifier" label="Identifier" placeholder="CORE" required hint="Used to prefix issues (e.g. CORE-42)" />
          <BaseButton type="submit" variant="primary" size="lg" :loading="isLoading" class="w-full mt-2">
            Launch workspace →
          </BaseButton>
        </form>
      </template>
    </div>
  </div>
</template>
