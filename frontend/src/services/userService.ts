import { apiClient } from './apiClient'
import type { AuthUser, UserRole } from '../types/auth'

export type UserInput = { name: string; email: string; password: string; role: UserRole }
export const userService = {
  list: () => apiClient.get<{ users: AuthUser[] }>('/api/users'),
  create: (input: UserInput) => apiClient.post<AuthUser>('/api/users', input),
  update: (id: number, input: UserInput) => apiClient.put<AuthUser>(`/api/users/${id}`, input),
  remove: (id: number) => apiClient.delete<{ message: string }>(`/api/users/${id}`),
}
