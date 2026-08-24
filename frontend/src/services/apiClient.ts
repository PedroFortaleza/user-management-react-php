type ApiErrorPayload = {
  message?: string
  errors?: Record<string, string[]>
}

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly errors: Record<string, string[]> = {},
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

type RequestOptions = Omit<RequestInit, 'body' | 'headers'> & {
  body?: unknown
  headers?: HeadersInit
}

class ApiClient {
  private token: string | null = null
  private onUnauthorized: () => void = () => undefined
  private readonly baseUrl = (import.meta.env.VITE_API_URL ?? 'http://localhost:8000').replace(/\/$/, '')

  setToken(token: string | null) {
    this.token = token
  }

  setUnauthorizedHandler(handler: () => void) {
    this.onUnauthorized = handler
  }

  get<T>(path: string, options?: RequestOptions) {
    return this.request<T>(path, { ...options, method: 'GET' })
  }

  post<T>(path: string, body?: unknown, options?: RequestOptions) {
    return this.request<T>(path, { ...options, method: 'POST', body })
  }

  private async request<T>(path: string, options: RequestOptions): Promise<T> {
    const headers = new Headers(options.headers)
    headers.set('Accept', 'application/json')

    if (this.token) {
      headers.set('Authorization', `Bearer ${this.token}`)
    }

    let body: string | undefined

    if (options.body !== undefined) {
      headers.set('Content-Type', 'application/json')
      body = JSON.stringify(options.body)
    }

    let response: Response

    try {
      response = await fetch(`${this.baseUrl}${path}`, { ...options, headers, body })
    } catch {
      throw new ApiError('Não foi possível conectar à API. Verifique se o backend está em execução.', 0)
    }

    const payload = (await response.json().catch(() => ({}))) as ApiErrorPayload

    if (!response.ok) {
      if (response.status === 401) {
        this.onUnauthorized()
      }

      throw new ApiError(payload.message ?? 'Não foi possível concluir a solicitação.', response.status, payload.errors ?? {})
    }

    return payload as T
  }
}

export const apiClient = new ApiClient()
