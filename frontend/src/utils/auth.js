import { ref } from 'vue'

const STORAGE_KEY = 'auth_session'

const decodeJwtPayload = (token) => {
  try {
    const [, payload] = token.split('.')
    if (!payload) return null

    const normalized = payload.replace(/-/g, '+').replace(/_/g, '/')
    const padded = normalized.padEnd(normalized.length + ((4 - (normalized.length % 4)) % 4), '=')
    return JSON.parse(window.atob(padded))
  } catch {
    return null
  }
}

const normalizeSession = (session) => {
  if (!session?.token || !session?.user) {
    return null
  }

  const payload = decodeJwtPayload(session.token)
  if (!payload?.exp || Date.now() >= payload.exp * 1000) {
    return null
  }

  return {
    token: session.token,
    user: session.user,
  }
}

export const readAuthSession = () => {
  if (typeof window === 'undefined') {
    return null
  }

  try {
    const rawSession = window.localStorage.getItem(STORAGE_KEY)
    if (!rawSession) {
      return null
    }

    const session = normalizeSession(JSON.parse(rawSession))
    if (!session) {
      window.localStorage.removeItem(STORAGE_KEY)
      window.localStorage.removeItem('user')
    }

    return session
  } catch {
    window.localStorage.removeItem(STORAGE_KEY)
    window.localStorage.removeItem('user')
    return null
  }
}

export const authSession = ref(readAuthSession())

export const setAuthSession = ({ token, user }) => {
  const session = normalizeSession({ token, user })

  if (!session) {
    clearAuthSession()
    return
  }

  authSession.value = session
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(session))
  window.localStorage.setItem('user', JSON.stringify(user))
}

export const updateAuthUser = (user) => {
  const currentSession = readAuthSession()

  if (!currentSession?.token) {
    clearAuthSession()
    return
  }

  setAuthSession({
    token: currentSession.token,
    user: {
      ...currentSession.user,
      ...user,
    },
  })
}

export const clearAuthSession = () => {
  authSession.value = null

  if (typeof window !== 'undefined') {
    window.localStorage.removeItem(STORAGE_KEY)
    window.localStorage.removeItem('user')
  }
}

export const getCurrentUser = () => readAuthSession()?.user ?? null
export const getAuthToken = () => readAuthSession()?.token ?? null

if (typeof window !== 'undefined') {
  window.addEventListener('storage', () => {
    authSession.value = readAuthSession()
  })
}
