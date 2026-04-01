import { getAuthToken } from './auth'

// 🚀 BÍ QUYẾT Ở ĐÂY: Tự động kiểm tra xem bạn đang chạy ở đâu
const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

// Nếu là localhost thì dùng link XAMPP/WAMP, ngược lại dùng link web thật
export const API_BASE_URL = isLocalhost 
  ? 'http://localhost/DoAnEnglish2026/backend/api' 
  : 'https://tttn375.cnttstu.online/backend/api';

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