export type UserRole = 'admin' | 'user'

export type AuthUser = {
  id: number
  name: string
  email: string
  role: UserRole
  created_at: string
  updated_at: string
}

export type AuthSession = {
  token: string
  user: AuthUser
}
