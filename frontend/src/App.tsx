import { Navigate, Route, Routes } from 'react-router-dom'

import { LoginPage } from './features/auth/LoginPage'
import { UsersPage } from './features/users/UsersPage'

export function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/users" element={<UsersPage />} />
      <Route path="*" element={<Navigate replace to="/login" />} />
    </Routes>
  )
}
