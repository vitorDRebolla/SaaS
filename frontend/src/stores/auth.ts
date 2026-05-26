import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api, setToken, clearToken } from '@/lib/api'
import type { User } from '@/types/models'
import type { ApiResponse } from '@/types/api'

interface AuthResponse {
  user: User
  token?: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.is_admin ?? false)

  async function fetchMe() {
    try {
      const res = await api.get<{ user: User }>('/auth/me')
      user.value = res.user
    } catch {
      user.value = null
    }
  }

  async function login(email: string, password: string) {
    isLoading.value = true
    try {
      const res = await api.post<AuthResponse>('/auth/login', { email, password })
      if (res.token) setToken(res.token)
      user.value = res.user
      return res
    } finally {
      isLoading.value = false
    }
  }

  async function register(name: string, email: string, password: string, passwordConfirmation: string) {
    isLoading.value = true
    try {
      const res = await api.post<AuthResponse>('/auth/register', {
        name, email, password, password_confirmation: passwordConfirmation,
      })
      if (res.token) setToken(res.token)
      user.value = res.user
      return res
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    await api.post('/auth/logout').catch(() => {})
    clearToken()
    user.value = null
  }

  async function updateProfile(data: Partial<Pick<User, 'name' | 'timezone' | 'preferences'>>) {
    const res = await api.patch<{ user: User }>('/auth/me', data)
    user.value = res.user
  }

  return { user, isLoading, isAuthenticated, isAdmin, login, register, logout, fetchMe, updateProfile }
})
