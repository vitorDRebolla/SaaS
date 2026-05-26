import type { ApiError } from '@/types/api'

const BASE_URL = '/api/v1'

function getToken(): string | null {
  return localStorage.getItem('meridian_token')
}

export function setToken(token: string): void {
  localStorage.setItem('meridian_token', token)
}

export function clearToken(): void {
  localStorage.removeItem('meridian_token')
}

async function request<T>(
  method: string,
  path: string,
  body?: unknown,
  isFormData = false,
): Promise<T> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
  }

  const token = getToken()
  if (token) headers['Authorization'] = `Bearer ${token}`
  if (!isFormData && body) headers['Content-Type'] = 'application/json'

  const response = await fetch(`${BASE_URL}${path}`, {
    method,
    headers,
    body: isFormData ? (body as FormData) : body ? JSON.stringify(body) : undefined,
  })

  if (response.status === 204) return undefined as T

  const json = await response.json()

  if (!response.ok) {
    const error = json as ApiError
    throw error
  }

  return json as T
}

export const api = {
  get: <T>(path: string) => request<T>('GET', path),
  post: <T>(path: string, body?: unknown) => request<T>('POST', path, body),
  patch: <T>(path: string, body?: unknown) => request<T>('PATCH', path, body),
  put: <T>(path: string, body?: unknown) => request<T>('PUT', path, body),
  delete: <T>(path: string) => request<T>('DELETE', path),
  upload: <T>(path: string, formData: FormData) => request<T>('POST', path, formData, true),
}
