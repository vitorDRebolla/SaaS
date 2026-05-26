import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('@/pages/LandingPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/pages/auth/RegisterPage.vue'),
      meta: { guest: true },
    },
    {
      path: '/onboarding',
      name: 'onboarding',
      component: () => import('@/pages/onboarding/OnboardingPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/:team',
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('@/pages/dashboard/DashboardPage.vue'),
        },
        {
          path: 'projects',
          name: 'projects',
          component: () => import('@/pages/projects/ProjectListPage.vue'),
        },
        {
          path: 'projects/:project',
          name: 'project-board',
          component: () => import('@/pages/projects/ProjectBoardPage.vue'),
        },
        {
          path: 'projects/:project/issues/:issue',
          name: 'issue-detail',
          component: () => import('@/pages/issues/IssueDetailPage.vue'),
        },
        {
          path: 'analytics',
          name: 'analytics',
          component: () => import('@/pages/analytics/AnalyticsPage.vue'),
        },
        {
          path: 'notifications',
          name: 'notifications',
          component: () => import('@/pages/notifications/NotificationsPage.vue'),
        },
        {
          path: 'settings/members',
          name: 'settings-members',
          component: () => import('@/pages/settings/MembersPage.vue'),
        },
        {
          path: 'settings/webhooks',
          name: 'settings-webhooks',
          component: () => import('@/pages/settings/WebhooksPage.vue'),
        },
        {
          path: 'settings/api-tokens',
          name: 'settings-api-tokens',
          component: () => import('@/pages/settings/ApiTokensPage.vue'),
        },
      ],
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('@/pages/admin/AdminPage.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

let authInitialized = false

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  const ui = useUiStore()

  if (!authInitialized) {
    await auth.fetchMe()
    authInitialized = true
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) return '/login'
  if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
  if (to.meta.guest && auth.isAuthenticated) {
    const teamStore = await import('@/stores/team').then((m) => m.useTeamStore())
    if (teamStore.teams.length === 0) await teamStore.fetchTeams()
    const firstTeam = teamStore.teams[0]
    if (firstTeam) return `/${firstTeam.slug}`
    return '/onboarding'
  }
})

export default router
