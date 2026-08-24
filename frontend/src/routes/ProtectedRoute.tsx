import type { PropsWithChildren } from 'react'
import { Navigate, useLocation } from 'react-router-dom'

import { useAuth } from '../contexts/AuthContext'

export function ProtectedRoute({ children }: PropsWithChildren) {
  const { session, status } = useAuth()
  const location = useLocation()

  if (status === 'restoring') {
    return <main className="route-status" role="status">Restaurando sessão…</main>
  }

  if (!session) {
    return <Navigate replace to="/login" state={{ from: location }} />
  }

  return <>{children}</>
}
