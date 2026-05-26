import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/lib/api'
import type { Project } from '@/types/models'
import type { ApiResponse } from '@/types/api'

export const useProjectsStore = defineStore('projects', () => {
  const projects = ref<Project[]>([])
  const currentProject = ref<Project | null>(null)
  const isLoading = ref(false)

  async function fetchProjects(teamSlug: string) {
    isLoading.value = true
    try {
      const res = await api.get<ApiResponse<Project[]>>(`/teams/${teamSlug}/projects`)
      projects.value = res.data
    } finally {
      isLoading.value = false
    }
  }

  async function fetchProject(teamSlug: string, projectId: number) {
    const res = await api.get<ApiResponse<Project>>(`/teams/${teamSlug}/projects/${projectId}`)
    currentProject.value = res.data
    return res.data
  }

  async function createProject(teamSlug: string, data: Pick<Project, 'name' | 'identifier'> & { description?: string; color?: string }) {
    const res = await api.post<ApiResponse<Project>>(`/teams/${teamSlug}/projects`, data)
    projects.value.unshift(res.data)
    return res.data
  }

  async function updateProject(teamSlug: string, projectId: number, data: Partial<Project>) {
    const res = await api.patch<ApiResponse<Project>>(`/teams/${teamSlug}/projects/${projectId}`, data)
    currentProject.value = res.data
    const idx = projects.value.findIndex((p) => p.id === projectId)
    if (idx >= 0) projects.value[idx] = res.data
    return res.data
  }

  return { projects, currentProject, isLoading, fetchProjects, fetchProject, createProject, updateProject }
})
