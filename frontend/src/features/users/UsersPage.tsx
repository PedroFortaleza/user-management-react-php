import { useCallback, useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import { AppShell } from '../../components/AppShell'
import { useAuth } from '../../contexts/AuthContext'
import { ApiError } from '../../services/apiClient'
import { userService } from '../../services/userService'
import type { AuthUser, UserRole } from '../../types/auth'

type FormState = { name: string; email: string; password: string; role: UserRole }
const emptyForm: FormState = { name: '', email: '', password: '', role: 'user' }

export function UsersPage() {
  const { logout, session } = useAuth()
  const [users, setUsers] = useState<AuthUser[]>([])
  const [form, setForm] = useState<FormState>(emptyForm)
  const [editing, setEditing] = useState<AuthUser | null>(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState('')
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [query, setQuery] = useState('')
  const isAdmin = session?.user.role === 'admin'

  const loadUsers = useCallback(async () => {
    setLoading(true)
    try { setUsers((await userService.list()).users) } catch (error) { setMessage(error instanceof ApiError ? error.message : 'Não foi possível carregar usuários.') } finally { setLoading(false) }
  }, [])
  useEffect(() => { void loadUsers() }, [loadUsers])

  function startCreate() { setEditing(null); setForm(emptyForm); setErrors({}); setMessage('') }
  function startEdit(user: AuthUser) { setEditing(user); setForm({ name: user.name, email: user.email, password: '', role: user.role }); setErrors({}); setMessage('') }
  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setErrors({}); setMessage('')
    const nextErrors: Record<string, string[]> = {}
    if (!form.name.trim()) nextErrors.name = ['O nome é obrigatório.']
    if (!/^\S+@\S+\.\S+$/.test(form.email)) nextErrors.email = ['Informe um e-mail válido.']
    if ((!editing && !form.password) || (form.password && form.password.length < 8)) nextErrors.password = ['A senha deve ter ao menos 8 caracteres.']
    if (Object.keys(nextErrors).length) { setErrors(nextErrors); return }
    setSaving(true)
    try { const action = editing ? 'atualizado' : 'criado'; if (editing) await userService.update(editing.id, form); else await userService.create(form); setEditing(null); setForm(emptyForm); setMessage(`Usuário ${action} com sucesso.`); await loadUsers() } catch (error) { if (error instanceof ApiError) { setMessage(error.message); setErrors(error.errors) } else setMessage('Não foi possível salvar o usuário.') } finally { setSaving(false) }
  }
  async function remove(user: AuthUser) {
    if (!window.confirm(`Excluir o usuário ${user.name}? Esta ação não pode ser desfeita.`)) return
    try { await userService.remove(user.id); setMessage('Usuário removido com sucesso.'); await loadUsers() } catch (error) { setMessage(error instanceof ApiError ? error.message : 'Não foi possível remover o usuário.') }
  }
  const fieldError = (field: string) => errors[field]?.[0]
  const filteredUsers = users.filter((user) => `${user.name} ${user.email} ${user.role}`.toLowerCase().includes(query.toLowerCase()))

  return (
    <AppShell
      eyebrow="Área de usuários"
      title="Gestão centralizada"
      description={`Sessão ativa para ${session?.user.name}. Gerencie os usuários cadastrados abaixo.`}
    >
      <div className="toolbar">{isAdmin && <button type="button" onClick={startCreate}>Novo usuário</button>}<button className="secondary-button" type="button" onClick={() => void logout()}>Sair da conta</button></div>
      <label className="filter-field" htmlFor="user-filter">Buscar usuários<input id="user-filter" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Nome, e-mail ou perfil" /></label>
      {isAdmin && (editing || form.name || message === '') && <form className="form-stack user-form" onSubmit={submit} noValidate>
        <h2>{editing ? 'Editar usuário' : 'Cadastrar usuário'}</h2>
        <label>Nome<input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />{fieldError('name') && <small>{fieldError('name')}</small>}</label>
        <label>E-mail<input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />{fieldError('email') && <small>{fieldError('email')}</small>}</label>
        <label>Senha{editing && ' (deixe em branco para manter)'}<input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />{fieldError('password') && <small>{fieldError('password')}</small>}</label>
        <label>Perfil<select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value as UserRole })}><option value="user">Usuário</option><option value="admin">Administrador</option></select>{fieldError('role') && <small>{fieldError('role')}</small>}</label>
        <button disabled={saving}>{saving ? 'Salvando…' : editing ? 'Salvar alterações' : 'Cadastrar usuário'}</button>
      </form>}
      {message && <p className="status-note" role="status">{message}</p>}
      {loading ? <p role="status">Carregando usuários…</p> : filteredUsers.length === 0 ? <div className="empty-state"><p>Nenhum usuário encontrado.</p></div> : <div className="user-list">{filteredUsers.map((user) => <article className="user-row" key={user.id}><div><strong>{user.name}</strong><span>{user.email} · {user.role}</span></div>{isAdmin && <div><button className="secondary-button" type="button" onClick={() => startEdit(user)}>Editar</button><button className="danger-button" type="button" disabled={user.id === session?.user.id} title={user.id === session?.user.id ? 'Você não pode excluir a própria conta.' : undefined} onClick={() => void remove(user)}>Excluir</button></div>}</article>)}</div>}
    </AppShell>
  )
}
