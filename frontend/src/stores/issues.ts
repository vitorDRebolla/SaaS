import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/lib/api'
import type { Issue } from '@/types/models'
import type { ApiResponse } from '@/types/api'

interface IssueFilters {
  status_id?: number
  assignee_id?: number
  priority?: string
  search?: string
  sort?: string
}

export const useIssuesStore = defineStore('issues', () => {
  const issues = ref<Issue[]>([])
  const currentIssue = ref<Issue | null>(null)
  const isLoading = ref(false)
  const filters = ref<IssueFilters>({})

  async function fetchIssues(teamSlug: string, projectId: number, f: IssueFilters = {}) {
    isLoading.value = true
    try {
      const params = new URLSearchParams()
      Object.entries(f).forEach(([k, v]) => v !== undefined && params.set(k, String(v)))
      const query = params.toString() ? `?${params}` : ''
      const res = await api.get<ApiResponse<Issue[]>>(`/teams/${teamSlug}/projects/${projectId}/issues${query}`)
      issues.value = res.data
    } finally {
      isLoading.value = false
    }
  }

  async function fetchIssue(teamSlug: string, projectId: number, issueId: number) {
    const res = await api.get<ApiResponse<Issue>>(`/teams/${teamSlug}/projects/${projectId}/issues/${issueId}`)
    currentIssue.value = res.data
    return res.data
  }

  async function createIssue(teamSlug: string, projectId: number, data: Partial<Issue> & { status_id: number; title: string }) {
    const res = await api.post<ApiResponse<Issue>>(`/teams/${teamSlug}/projects/${projectId}/issues`, data)
    issues.value.push(res.data)
    return res.data
  }

  async function updateIssue(teamSlug: string, projectId: number, issueId: number, data: Partial<Issue>) {
    const res = await api.patch<ApiResponse<Issue>>(`/teams/${teamSlug}/projects/${projectId}/issues/${issueId}`, data)
    const idx = issues.value.findIndex((i) => i.id === issueId)
    if (idx >= 0) issues.value[idx] = res.data
    if (currentIssue.value?.id === issueId) currentIssue.value = res.data
    return res.data
  }

  async function deleteIssue(teamSlug: string, projectId: number, issueId: number) {
    await api.delete(`/teams/${teamSlug}/projects/${projectId}/issues/${issueId}`)
    issues.value = issues.value.filter((i) => i.id !== issueId)
  }

  function updateIssueLocally(issue: Issue) {
    const idx = issues.value.findIndex((i) => i.id === issue.id)
    if (idx >= 0) issues.value[idx] = issue
    if (currentIssue.value?.id === issue.id) currentIssue.value = issue
  }

  return {
    issues, currentIssue, isLoading, filters,
    fetchIssues, fetchIssue, createIssue, updateIssue, deleteIssue, updateIssueLocally,
  }
})
