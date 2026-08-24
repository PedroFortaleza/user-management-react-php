import type { FormEvent } from 'react'

import { AppShell } from '../../components/AppShell'

export function LoginPage() {
  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
  }

  return (
    <AppShell
      eyebrow="Acesso ao sistema"
      title="Entre na sua conta"
      description="Use suas credenciais para acessar a gestão de usuários. A autenticação será disponibilizada na próxima fase."
    >
      <form className="form-stack" onSubmit={handleSubmit} noValidate>
        <div className="field-group">
          <label htmlFor="email">E-mail</label>
          <input id="email" name="email" type="email" autoComplete="email" placeholder="voce@empresa.com" disabled />
        </div>

        <div className="field-group">
          <label htmlFor="password">Senha</label>
          <input id="password" name="password" type="password" autoComplete="current-password" placeholder="Sua senha" disabled />
        </div>

        <button type="submit" disabled>
          Entrar
        </button>
      </form>
      <p className="status-note" role="status">
        Interface inicial criada. O formulário será conectado à API na Fase 2.
      </p>
    </AppShell>
  )
}
