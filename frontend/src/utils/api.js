import { getAuthToken } from './auth'
// Tự động chọn URL theo môi trường:
// - npm run dev  → .env.development → http://localhost/DoAnEnglish2026/backend/api
// - npm run build → .env.production  → https://tttn375.cnttstu.online/backend/api
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
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