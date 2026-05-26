<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTeamStore } from '@/stores/team'
import { useUiStore } from '@/stores/ui'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { ApiError } from '@/types/api'

const router = useRouter()
const auth = useAuthStore()
const team = useTeamStore()
const ui = useUiStore()

const email = ref('')
const password = ref('')
const errors = ref<Record<string, string[]>>({})

async function submit() {
  errors.value = {}
  try {
    await auth.login(email.value, password.value)
    await team.fetchTeams()
    const firstTeam = team.teams[0]
    if (firstTeam) router.push(`/${firstTeam.slug}`)
    else router.push('/onboarding')
  } catch (e) {
    const err = e as ApiError
    errors.value = err.errors ?? { email: [err.message] }
    ui.toast(err.message, 'error')
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[var(--color-bg-base)] p-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xl mx-auto mb-4">M</div>
        <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Welcome back</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Sign in to your Meridian account</p>
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-4">
        <BaseInput v-model="email" label="Email" type="email" placeholder="you@company.com" required :error="errors.email?.[0]" />
        <BaseInput v-model="password" label="Password" type="password" placeholder="••••••••" required :error="errors.password?.[0]" />

        <BaseButton type="submit" variant="primary" size="lg" :loading="auth.isLoading" class="w-full mt-2">
          Sign in
        </BaseButton>
      </form>

      <p class="text-center text-sm text-[var(--color-text-secondary)] mt-6">
        Don't have an account?
        <RouterLink to="/register" class="text-indigo-600 hover:underline font-medium">Sign up</RouterLink>
      </p>

      <div class="mt-6 p-4 rounded-xl bg-[var(--color-bg-elevated)] border border-[var(--color-border)]">
        <p class="text-xs font-semibold text-[var(--color-text-secondary)] mb-2">Demo credentials</p>
        <button @click="email = 'demo@meridian.test'; password = 'password'" class="text-xs text-indigo-600 hover:underline">
          Use demo@meridian.test / password
        </button>
      </div>
    </div>
  </div>
</template>
