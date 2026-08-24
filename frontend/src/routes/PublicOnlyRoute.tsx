import type { PropsWithChildren } from 'react'
import { Navigate } from 'react-router-dom'

import { useAuth } from '../contexts/AuthContext'

export function PublicOnlyRoute({ children }: PropsWithChildren) {
  const { session, status } = useAuth()

  if (status === 'restoring') {
    return <main className="route-status" role="status">Restaurando sessão…</main>
  }

  return session ? <Navigate replace to="/users" /> : <>{children}</>
}
