import type { PropsWithChildren } from 'react'

type AppShellProps = PropsWithChildren<{
  eyebrow: string
  title: string
  description: string
}>

export function AppShell({ children, eyebrow, title, description }: AppShellProps) {
  return (
    <main className="app-shell">
      <section className="content-card" aria-labelledby="page-title">
        <div className="brand" aria-label="Gestão de Usuários">
          <span className="brand-mark" aria-hidden="true">GU</span>
          <span>Gestão de Usuários</span>
        </div>
        <div className="page-heading">
          <p className="eyebrow">{eyebrow}</p>
          <h1 id="page-title">{title}</h1>
          <p>{description}</p>
        </div>
        {children}
      </section>
    </main>
  )
}
