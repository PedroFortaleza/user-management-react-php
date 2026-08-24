import { Navigate, Route, Routes } from 'react-router-dom'

import { LoginPage } from './features/auth/LoginPage'
import { UsersPage } from './features/users/UsersPage'
import { ProtectedRoute } from './routes/ProtectedRoute'
import { PublicOnlyRoute } from './routes/PublicOnlyRoute'

export function App() {
  return (
    <Routes>
      <Route path="/login" element={<PublicOnlyRoute><LoginPage /></PublicOnlyRoute>} />
      <Route path="/users" element={<ProtectedRoute><UsersPage /></ProtectedRoute>} />
      <Route path="*" element={<Navigate replace to="/login" />} />
    </Routes>
  )
}
