import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import type { PropsWithChildren } from 'react'

import { ApiError, apiClient } from '../services/apiClient'
import type { AuthSession, AuthUser } from '../types/auth'

type LoginInput = { email: string; password: string }
type LoginResponse = AuthSession
type MeResponse = { user: AuthUser }
type AuthStatus = 'restoring' | 'authenticated' | 'unauthenticated'

type AuthContextValue = {
  session: AuthSession | null
  status: AuthStatus
  login: (input: LoginInput) => Promise<void>
  logout: () => Promise<void>
}

const storageKey = 'user-management.auth-session'
const AuthContext = createContext<AuthContextValue | null>(null)

function readStoredSession(): AuthSession | null {
  try {
    const value = localStorage.getItem(storageKey)
    return value ? (JSON.parse(value) as AuthSession) : null
  } catch {
    localStorage.removeItem(storageKey)
    return null
  }
}

export function AuthProvider({ children }: PropsWithChildren) {
  const [session, setSession] = useState<AuthSession | null>(readStoredSession)
  const [status, setStatus] = useState<AuthStatus>('restoring')

  const clearSession = useCallback(() => {
    apiClient.setToken(null)
    localStorage.removeItem(storageKey)
    setSession(null)
    setStatus('unauthenticated')
  }, [])

  const persistSession = useCallback((nextSession: AuthSession) => {
    apiClient.setToken(nextSession.token)
    localStorage.setItem(storageKey, JSON.stringify(nextSession))
    setSession(nextSession)
    setStatus('authenticated')
  }, [])

  useEffect(() => {
    apiClient.setUnauthorizedHandler(clearSession)
    const storedSession = readStoredSession()

    if (!storedSession) {
      clearSession()
      return
    }

    apiClient.setToken(storedSession.token)
    apiClient
      .get<MeResponse>('/api/me')
      .then(({ user }) => persistSession({ token: storedSession.token, user }))
      .catch((error: unknown) => {
        if (!(error instanceof ApiError) || error.status !== 401) {
          clearSession()
        }
      })
  }, [clearSession, persistSession])

  const login = useCallback(async (input: LoginInput) => {
    const nextSession = await apiClient.post<LoginResponse>('/api/login', input)
    persistSession(nextSession)
  }, [persistSession])

  const logout = useCallback(async () => {
    try {
      await apiClient.post('/api/logout')
    } finally {
      clearSession()
    }
  }, [clearSession])

  const value = useMemo(() => ({ session, status, login, logout }), [login, logout, session, status])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  const context = useContext(AuthContext)

  if (!context) {
    throw new Error('useAuth deve ser utilizado dentro de AuthProvider.')
  }

  return context
}
