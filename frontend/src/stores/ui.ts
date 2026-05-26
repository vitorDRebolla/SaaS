import { defineStore } from 'pinia'
import { ref } from 'vue'

interface Toast {
  id: string
  type: 'success' | 'error' | 'info' | 'warning'
  message: string
}

export const useUiStore = defineStore('ui', () => {
  const sidebarCollapsed = ref(false)
  const isDark = ref(false)
  const toasts = ref<Toast[]>([])

  function toggleSidebar() { sidebarCollapsed.value = !sidebarCollapsed.value }

  function toggleDark() {
    isDark.value = !isDark.value
    if (isDark.value) document.documentElement.classList.add('dark')
    else document.documentElement.classList.remove('dark')
    localStorage.setItem('meridian_theme', isDark.value ? 'dark' : 'light')
  }

  function initTheme() {
    const saved = localStorage.getItem('meridian_theme')
    isDark.value = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)
    if (isDark.value) document.documentElement.classList.add('dark')
  }

  function toast(message: string, type: Toast['type'] = 'success') {
    const id = Math.random().toString(36).slice(2)
    toasts.value.push({ id, type, message })
    setTimeout(() => { toasts.value = toasts.value.filter((t) => t.id !== id) }, 3500)
  }

  function removeToast(id: string) { toasts.value = toasts.value.filter((t) => t.id !== id) }

  return { sidebarCollapsed, isDark, toasts, toggleSidebar, toggleDark, initTheme, toast, removeToast }
})
