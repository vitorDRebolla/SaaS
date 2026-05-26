<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTeamStore } from '@/stores/team'
import { useUiStore } from '@/stores/ui'
import BaseAvatar from '@/components/ui/BaseAvatar.vue'

const auth = useAuthStore()
const team = useTeamStore()
const ui = useUiStore()
const route = useRoute()

const teamSlug = computed(() => route.params.team as string)

const navItems = computed(() => [
  { to: `/${teamSlug.value}`, label: 'Dashboard', icon: '⊞', exact: true },
  { to: `/${teamSlug.value}/projects`, label: 'Projects', icon: '◫' },
  { to: `/${teamSlug.value}/analytics`, label: 'Analytics', icon: '◈' },
  { to: `/${teamSlug.value}/notifications`, label: 'Notifications', icon: '◻' },
])

const settingsItems = computed(() => [
  { to: `/${teamSlug.value}/settings/members`, label: 'Members' },
  { to: `/${teamSlug.value}/settings/webhooks`, label: 'Webhooks' },
  { to: `/${teamSlug.value}/settings/api-tokens`, label: 'API Tokens' },
])

function isActive(path: string, exact = false) {
  return exact ? route.path === path : route.path.startsWith(path)
}
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-[var(--color-bg-base)]">
    <!-- Sidebar -->
    <aside :class="[
      'flex flex-col border-r border-[var(--color-border)] bg-[var(--color-bg-surface)] transition-all duration-200 flex-shrink-0',
      ui.sidebarCollapsed ? 'w-14' : 'w-56'
    ]">
      <!-- Logo / Team -->
      <div class="flex items-center gap-3 px-4 h-14 border-b border-[var(--color-border)]">
        <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">M</div>
        <span v-if="!ui.sidebarCollapsed" class="font-semibold text-[var(--color-text-primary)] truncate">
          {{ team.currentTeam?.name ?? 'Meridian' }}
        </span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-3 space-y-0.5">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          :class="[
            'flex items-center gap-3 px-2.5 py-2 rounded-lg text-sm transition-colors',
            isActive(item.to, item.exact)
              ? 'bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 font-medium'
              : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-elevated)] hover:text-[var(--color-text-primary)]',
          ]"
        >
          <span class="text-base w-5 text-center flex-shrink-0">{{ item.icon }}</span>
          <span v-if="!ui.sidebarCollapsed" class="truncate">{{ item.label }}</span>
        </RouterLink>

        <div v-if="!ui.sidebarCollapsed" class="pt-4 pb-1 px-2.5">
          <span class="text-[10px] font-semibold uppercase tracking-widest text-[var(--color-text-placeholder)]">Settings</span>
        </div>
        <RouterLink
          v-for="item in settingsItems"
          :key="item.to"
          :to="item.to"
          :class="[
            'flex items-center gap-3 px-2.5 py-1.5 rounded-lg text-sm transition-colors',
            route.path.startsWith(item.to)
              ? 'bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 font-medium'
              : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-elevated)] hover:text-[var(--color-text-primary)]',
          ]"
        >
          <span v-if="ui.sidebarCollapsed" class="text-base w-5 text-center">·</span>
          <span v-if="!ui.sidebarCollapsed" class="truncate">{{ item.label }}</span>
        </RouterLink>
      </nav>

      <!-- User -->
      <div class="border-t border-[var(--color-border)] px-3 py-3">
        <div class="flex items-center gap-2.5">
          <BaseAvatar :user="auth.user" size="sm" />
          <div v-if="!ui.sidebarCollapsed" class="flex-1 min-w-0">
            <p class="text-xs font-medium text-[var(--color-text-primary)] truncate">{{ auth.user?.name }}</p>
            <p class="text-[10px] text-[var(--color-text-secondary)] truncate">{{ team.currentRole }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top bar -->
      <header class="h-14 border-b border-[var(--color-border)] flex items-center gap-4 px-6 flex-shrink-0">
        <button @click="ui.toggleSidebar()" class="p-1.5 rounded-lg hover:bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)]">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
        </button>
        <div class="flex-1" />
        <button @click="ui.toggleDark()" class="p-1.5 rounded-lg hover:bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)] text-sm">
          {{ ui.isDark ? '☀' : '☾' }}
        </button>
        <RouterLink :to="`/${teamSlug}/settings/members`" class="p-1.5 rounded-lg hover:bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)]">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </RouterLink>
        <button @click="auth.logout().then(() => $router.push('/login'))" class="p-1.5 rounded-lg hover:bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)]" title="Logout">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </button>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
