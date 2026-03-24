import { getAuthToken } from './auth'

export const API_BASE_URL = 'http://localhost/DoAnEnglish2026/backend/api'

export const buildApiUrl = (path) => `${API_BASE_URL}/${path.replace(/^\/+/, '')}`

const isPublicApiPath = (path) => {
  const normalizedPath = String(path || '').replace(/^\/+/, '').toLowerCase()
  return normalizedPath.startsWith('public/')
}

export const apiFetch = (path, options = {}) => {
  const headers = new Headers(options.headers || {})
  const token = getAuthToken()

  if (token && !isPublicApiPath(path) && !headers.has('Authorization')) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  if (options.body && !(options.body instanceof FormData) && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  return fetch(buildApiUrl(path), {
    ...options,
    headers,
  })
}
