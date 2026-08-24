import { Link } from 'react-router-dom'

import { AppShell } from '../../components/AppShell'

export function UsersPage() {
  return (
    <AppShell
      eyebrow="Área de usuários"
      title="Gestão centralizada"
      description="A listagem e as ações administrativas serão disponibilizadas após a autenticação e a implementação do CRUD."
    >
      <div className="empty-state">
        <h2>Conteúdo em preparação</h2>
        <p>Esta rota já está disponível para receber a proteção de acesso e a lista de usuários nas próximas fases.</p>
        <Link className="secondary-link" to="/login">Voltar para o login</Link>
      </div>
    </AppShell>
  )
}
