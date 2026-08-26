import { useTheme } from '../contexts/ThemeContext'

export function ThemeToggle() {
  const { theme, toggleTheme } = useTheme()
  const nextTheme = theme === 'light' ? 'escuro' : 'claro'
  return <button className="theme-toggle" type="button" onClick={toggleTheme} aria-label={`Ativar tema ${nextTheme}`}><span aria-hidden="true">{theme === 'light' ? '◐' : '◑'}</span><span>{nextTheme}</span></button>
}
