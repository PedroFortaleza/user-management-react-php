import type { PropsWithChildren } from 'react'
import { ThemeToggle } from './ThemeToggle'

type AppShellProps = PropsWithChildren<{
  eyebrow: string
  title: string
  description?: string
  variant?: 'auth' | 'workspace'
}>

export function AppShell({ children, eyebrow, title, description, variant = 'workspace' }: AppShellProps) {
  return (
    <main className="app-shell">
      <section className={`content-card content-card--${variant}`} aria-labelledby="page-title">
        <div className="content-main">
          <header className="app-header"><div className="brand" aria-label="Gestão de Usuários"><span>Gestão de usuários</span></div><ThemeToggle /></header>
          <div className="page-heading">
            <p className="eyebrow">{eyebrow}</p>
            <h1 id="page-title">{title}</h1>
            {description && <p>{description}</p>}
          </div>
          {children}
          <footer className="app-footer"><span>sistema local</span><span>•</span><span>v1.0</span></footer>
        </div>
        {variant === 'auth' && <aside className="auth-art" aria-hidden="true"><div className="art-shape art-shape--one" /><div className="art-shape art-shape--two" /><div className="art-copy"><span>USER MANAGEMENT</span><strong>Bem-vindo.</strong><small>Seu espaço para uma gestão simples.</small></div></aside>}
      </section>
    </main>
  )
}
