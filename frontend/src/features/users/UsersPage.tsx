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
  const [isFormOpen, setIsFormOpen] = useState(false)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState('')
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [query, setQuery] = useState('')
  const isAdmin = session?.user.role === 'admin'
  const isEditingOwnAccount = editing?.id === session?.user.id

  const loadUsers = useCallback(async () => {
    setLoading(true)
    try {
      setUsers((await userService.list()).users)
    } catch (error) {
      setMessage(error instanceof ApiError ? error.message : 'Não foi possível carregar usuários.')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { void loadUsers() }, [loadUsers])

  function startCreate() {
    setEditing(null)
    setForm(emptyForm)
    setErrors({})
    setMessage('')
    setIsFormOpen(true)
  }

  function startEdit(user: AuthUser) {
    setEditing(user)
    setForm({ name: user.name, email: user.email, password: '', role: user.role })
    setErrors({})
    setMessage('')
    setIsFormOpen(true)
  }

  function closeForm() {
    setEditing(null)
    setForm(emptyForm)
    setErrors({})
    setIsFormOpen(false)
  }

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setErrors({})
    setMessage('')

    const nextErrors: Record<string, string[]> = {}
    if (!form.name.trim()) nextErrors.name = ['O nome é obrigatório.']
    if (!/^\S+@\S+\.\S+$/.test(form.email)) nextErrors.email = ['Informe um e-mail válido.']
    if ((!editing && !form.password) || (form.password && form.password.length < 8)) nextErrors.password = ['A senha deve ter ao menos 8 caracteres.']
    if (Object.keys(nextErrors).length) {
      setErrors(nextErrors)
      return
    }

    setSaving(true)
    try {
      const action = editing ? 'atualizado' : 'criado'
      if (editing) await userService.update(editing.id, form)
      else await userService.create(form)
      closeForm()
      setMessage(`Usuário ${action} com sucesso.`)
      await loadUsers()
    } catch (error) {
      if (error instanceof ApiError) {
        setMessage(error.message)
        setErrors(error.errors)
      } else {
        setMessage('Não foi possível salvar o usuário.')
      }
    } finally {
      setSaving(false)
    }
  }

  async function remove(user: AuthUser) {
    if (!window.confirm(`Excluir o usuário ${user.name}? Esta ação não pode ser desfeita.`)) return
    try {
      await userService.remove(user.id)
      setMessage('Usuário removido com sucesso.')
      await loadUsers()
    } catch (error) {
      setMessage(error instanceof ApiError ? error.message : 'Não foi possível remover o usuário.')
    }
  }

  const fieldError = (field: string) => errors[field]?.[0]
  const filteredUsers = users.filter((user) => `${user.name} ${user.email} ${user.role}`.toLowerCase().includes(query.toLowerCase()))

  return (
    <AppShell eyebrow="Diretório" title="Usuários">
      <div className="users-page">
        <section className="users-toolbar" aria-label="Ações e busca de usuários">
          <label className="users-search" htmlFor="user-filter">
            <span className="visually-hidden">Buscar usuários</span>
            <input id="user-filter" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Buscar usuários" />
          </label>
          <div className="users-toolbar-actions">
            {isAdmin && <button type="button" onClick={startCreate}>Novo usuário</button>}
            <button className="secondary-button" type="button" onClick={() => void logout()}>Sair</button>
          </div>
        </section>

        {isFormOpen && isAdmin && (
          <section className="user-editor" aria-labelledby="user-form-title">
            <div className="editor-heading">
              <h2 id="user-form-title">{editing ? 'Editar usuário' : 'Novo usuário'}</h2>
              <button className="secondary-button" type="button" onClick={closeForm}>Cancelar</button>
            </div>
            <form className="user-form" onSubmit={submit} noValidate>
              <label>Nome<input value={form.name} onChange={(event) => setForm({ ...form, name: event.target.value })} />{fieldError('name') && <small>{fieldError('name')}</small>}</label>
              <label>E-mail<input type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} />{fieldError('email') && <small>{fieldError('email')}</small>}</label>
              <label>Senha{editing && ' (opcional)'}<input type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} />{fieldError('password') && <small>{fieldError('password')}</small>}</label>
              <fieldset className="role-picker" disabled={isEditingOwnAccount}><legend>Perfil</legend><div className="role-options"><label className={form.role === 'user' ? 'is-selected' : ''}><input type="radio" name="role" value="user" checked={form.role === 'user'} onChange={() => setForm({ ...form, role: 'user' })} />Usuário</label><label className={form.role === 'admin' ? 'is-selected' : ''}><input type="radio" name="role" value="admin" checked={form.role === 'admin'} onChange={() => setForm({ ...form, role: 'admin' })} />Administrador</label></div>{isEditingOwnAccount && <span className="field-note">O perfil da sua conta não pode ser alterado.</span>}{fieldError('role') && <small>{fieldError('role')}</small>}</fieldset>
              <div className="form-actions"><button disabled={saving}>{saving ? 'Salvando…' : editing ? 'Salvar' : 'Criar usuário'}</button></div>
            </form>
          </section>
        )}

        {message && <p className="status-note" role="status">{message}</p>}

        <section className="users-list-section" aria-labelledby="users-list-title">
          <div className="users-list-heading">
            <h2 id="users-list-title">{loading ? 'Carregando' : `${filteredUsers.length} usuário${filteredUsers.length === 1 ? '' : 's'}`}</h2>
          </div>
          {loading ? <p className="directory-feedback" role="status">Carregando usuários…</p> : filteredUsers.length === 0 ? <div className="empty-state"><p>Nenhum usuário encontrado.</p></div> : <div className="user-list">{filteredUsers.map((user) => <article className="user-item" key={user.id}>
            <div className="user-identity"><span className="user-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6"><circle cx="12" cy="8" r="3" /><path d="M5.5 19c.8-3.1 3-4.7 6.5-4.7s5.7 1.6 6.5 4.7" /></svg></span><div className="user-details"><strong>{user.name}</strong><span>{user.email}</span></div></div>
            {isAdmin && <div className="user-actions"><button className="secondary-button" type="button" onClick={() => startEdit(user)}>Editar</button><button className="danger-button" type="button" disabled={user.id === session?.user.id} title={user.id === session?.user.id ? 'Você não pode excluir a própria conta.' : undefined} onClick={() => void remove(user)}>Excluir</button></div>}
          </article>)}</div>}
        </section>
      </div>
    </AppShell>
  )
}
