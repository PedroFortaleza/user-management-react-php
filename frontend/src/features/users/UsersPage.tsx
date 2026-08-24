import { AppShell } from '../../components/AppShell'
import { useAuth } from '../../contexts/AuthContext'

export function UsersPage() {
  const { logout, session } = useAuth()

  return (
    <AppShell
      eyebrow="Área de usuários"
      title="Gestão centralizada"
      description={`Sessão ativa para ${session?.user.name}. A listagem e as ações administrativas serão disponibilizadas na próxima fase.`}
    >
      <div className="empty-state">
        <h2>Conteúdo em preparação</h2>
        <p>Esta rota já está disponível para receber a proteção de acesso e a lista de usuários nas próximas fases.</p>
        <button className="secondary-button" type="button" onClick={() => void logout()}>Sair da conta</button>
      </div>
    </AppShell>
  )
}
