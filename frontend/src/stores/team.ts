import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/lib/api'
import type { Team, TeamMember, TeamInvitation } from '@/types/models'
import type { ApiResponse } from '@/types/api'

export const useTeamStore = defineStore('team', () => {
  const teams = ref<Team[]>([])
  const currentTeam = ref<Team | null>(null)
  const members = ref<TeamMember[]>([])
  const invitations = ref<TeamInvitation[]>([])
  const isLoading = ref(false)

  const currentRole = computed(() => currentTeam.value?.member_role ?? null)
  const canManage = computed(() => ['owner', 'admin'].includes(currentRole.value ?? ''))
  const canEdit = computed(() => ['owner', 'admin', 'member'].includes(currentRole.value ?? ''))

  async function fetchTeams() {
    const res = await api.get<ApiResponse<Team[]>>('/auth/teams')
    teams.value = res.data
  }

  async function fetchTeam(slug: string) {
    const res = await api.get<ApiResponse<Team>>(`/teams/${slug}`)
    currentTeam.value = res.data
    return res.data
  }

  async function createTeam(name: string) {
    const res = await api.post<ApiResponse<Team>>('/teams', { name })
    teams.value.push(res.data)
    return res.data
  }

  async function updateTeam(slug: string, data: Partial<Team>) {
    const res = await api.patch<ApiResponse<Team>>(`/teams/${slug}`, data)
    currentTeam.value = res.data
    const idx = teams.value.findIndex((t) => t.slug === slug)
    if (idx >= 0) teams.value[idx] = res.data
    return res.data
  }

  async function fetchMembers(teamSlug: string) {
    const res = await api.get<ApiResponse<TeamMember[]>>(`/teams/${teamSlug}/members`)
    members.value = res.data
  }

  async function inviteMember(teamSlug: string, email: string, role: string) {
    const res = await api.post<ApiResponse<TeamInvitation>>(`/teams/${teamSlug}/invitations`, { email, role })
    return res.data
  }

  async function updateMember(teamSlug: string, memberId: number, role: string) {
    const res = await api.patch<ApiResponse<TeamMember>>(`/teams/${teamSlug}/members/${memberId}`, { role })
    const idx = members.value.findIndex((m) => m.id === memberId)
    if (idx >= 0) members.value[idx] = res.data
    return res.data
  }

  async function removeMember(teamSlug: string, memberId: number) {
    await api.delete(`/teams/${teamSlug}/members/${memberId}`)
    members.value = members.value.filter((m) => m.id !== memberId)
  }

  function setCurrentTeam(team: Team) {
    currentTeam.value = team
  }

  return {
    teams, currentTeam, members, invitations, isLoading,
    currentRole, canManage, canEdit,
    fetchTeams, fetchTeam, createTeam, updateTeam, setCurrentTeam,
    fetchMembers, inviteMember, updateMember, removeMember,
  }
})
