import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'

import { AppShell } from '../../components/AppShell'
import { ApiError } from '../../services/apiClient'
import { useAuth } from '../../contexts/AuthContext'

export function LoginPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [message, setMessage] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)
  const redirectTo = (location.state as { from?: { pathname?: string } } | null)?.from?.pathname ?? '/users'

  useEffect(() => {
    const notice = sessionStorage.getItem('user-management.expired-session-notice')

    if (notice) {
      setMessage(notice)
      sessionStorage.removeItem('user-management.expired-session-notice')
    }
  }, [])

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage('')

    if (!email || !password) {
      setMessage('Informe seu e-mail e sua senha.')
      return
    }

    setIsSubmitting(true)

    try {
      await login({ email, password })
      navigate(redirectTo, { replace: true })
    } catch (error) {
      setMessage(error instanceof ApiError ? error.message : 'Não foi possível iniciar a sessão.')
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <AppShell
      eyebrow="Acesso ao sistema"
      title="Entre na sua conta"
      description="Use suas credenciais para acessar a gestão de usuários."
    >
      <form className="form-stack" onSubmit={handleSubmit} noValidate>
        <div className="field-group">
          <label htmlFor="email">E-mail</label>
          <input id="email" name="email" type="email" autoComplete="email" placeholder="voce@empresa.com" value={email} onChange={(event) => setEmail(event.target.value)} required />
        </div>

        <div className="field-group">
          <label htmlFor="password">Senha</label>
          <input id="password" name="password" type="password" autoComplete="current-password" placeholder="Sua senha" value={password} onChange={(event) => setPassword(event.target.value)} required />
        </div>

        <button type="submit" disabled={isSubmitting}>
          {isSubmitting ? 'Entrando…' : 'Entrar'}
        </button>
      </form>
      {message && <p className="status-note status-note--error" role="alert">{message}</p>}
    </AppShell>
  )
}
