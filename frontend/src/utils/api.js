import { getAuthToken } from './auth'

// Tự động chọn URL theo môi trường:
// - npm run dev   → .env.development → http://localhost/DoAnEnglish2026/backend/api
// - npm run build → .env.production  → https://tttn375.cnttstu.online/backend/api
// Fallback hardcode phòng khi env file bị thiếu lúc build
export const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ||
  'https://tttn375.cnttstu.online/backend/api'

// Ghép URL an toàn: không có double-slash dù base có trailing '/' hay path có leading '/'
export const buildApiUrl = (path) => {
  const base = API_BASE_URL.replace(/\/+$/, '')        // bỏ trailing slash của base
  const cleanPath = String(path || '').replace(/^\/+/, '') // bỏ leading slash của path
  return `${base}/${cleanPath}`
}

const isPublicApiPath = (path) => {
  const normalizedPath = String(path || '').replace(/^\/+/, '').toLowerCase()
  return normalizedPath.startsWith('public/')
}

export const getFileUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  
  // Extract base site url from API_BASE_URL
  const siteUrl = API_BASE_URL.replace(/\/backend\/api\/?$/, '');
  
  return siteUrl + (path.startsWith('/') ? path : '/' + path);
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