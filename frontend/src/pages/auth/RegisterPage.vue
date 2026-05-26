<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import type { ApiError } from '@/types/api'

const router = useRouter()
const auth = useAuthStore()
const ui = useUiStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errors = ref<Record<string, string[]>>({})

async function submit() {
  errors.value = {}
  try {
    await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
    router.push('/onboarding')
  } catch (e) {
    const err = e as ApiError
    errors.value = err.errors ?? {}
    ui.toast(err.message, 'error')
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[var(--color-bg-base)] p-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xl mx-auto mb-4">M</div>
        <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Create your account</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Start shipping with your team</p>
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-4">
        <BaseInput v-model="name" label="Full name" placeholder="Alex Johnson" required :error="errors.name?.[0]" />
        <BaseInput v-model="email" label="Work email" type="email" placeholder="you@company.com" required :error="errors.email?.[0]" />
        <BaseInput v-model="password" label="Password" type="password" placeholder="Min. 8 characters" required :error="errors.password?.[0]" />
        <BaseInput v-model="passwordConfirmation" label="Confirm password" type="password" placeholder="••••••••" required />

        <BaseButton type="submit" variant="primary" size="lg" :loading="auth.isLoading" class="w-full mt-2">
          Create account
        </BaseButton>
      </form>

      <p class="text-center text-sm text-[var(--color-text-secondary)] mt-6">
        Already have an account?
        <RouterLink to="/login" class="text-indigo-600 hover:underline font-medium">Sign in</RouterLink>
      </p>
    </div>
  </div>
</template>
