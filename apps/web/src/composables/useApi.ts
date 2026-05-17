import { ofetch, type FetchOptions } from 'ofetch'

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly body?: { message?: string; errors?: Record<string, string[]>; code?: string },
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

function resolveApiBase(): string {
  // SSR in Docker: localhost is the web container, not Laravel.
  if (import.meta.env.SSR) {
    return (
      process.env.API_INTERNAL_URL ??
      import.meta.env.VITE_API_INTERNAL_URL ??
      'http://api/api/v1'
    )
  }

  return (
    import.meta.env.VITE_API_URL ??
    import.meta.env.VITE_API_BASE_URL ??
    '/api/v1'
  )
}

const apiBase = resolveApiBase()

export function apiOrigin(): string {
  if (apiBase.startsWith('http://') || apiBase.startsWith('https://')) {
    return apiBase.replace(/\/api\/v1\/?$/, '')
  }

  // Relative base → Vite dev proxy (same origin as the SPA).
  return ''
}

export async function ensureCsrfCookie(): Promise<void> {
  await ofetch('/sanctum/csrf-cookie', {
    baseURL: apiOrigin(),
    credentials: 'include',
  })
}

function readXsrfToken(): string | null {
  if (typeof document === 'undefined') {
    return null
  }

  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  if (!match?.[1]) {
    return null
  }

  try {
    return decodeURIComponent(match[1])
  } catch {
    return match[1]
  }
}

function methodNeedsCsrf(method?: string): boolean {
  const verb = (method ?? 'GET').toUpperCase()
  return !['GET', 'HEAD', 'OPTIONS'].includes(verb)
}

export async function api<T>(path: string, options: FetchOptions<'json'> = {}): Promise<T> {
  try {
    if (methodNeedsCsrf(options.method)) {
      await ensureCsrfCookie()
    }

    const xsrfToken = readXsrfToken()

    return await ofetch<T>(path, {
      baseURL: apiBase,
      credentials: 'include',
      ...options,
      headers: {
        Accept: 'application/json',
        ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
        ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
        ...options.headers,
      },
    })
  } catch (error: unknown) {
    const fetchError = error as {
      response?: { status?: number; _data?: ApiError['body'] }
      status?: number
      data?: ApiError['body']
      message?: string
    }
    const status = fetchError.response?.status ?? fetchError.status ?? 500
    const body = fetchError.response?._data ?? fetchError.data
    const message = body?.message ?? fetchError.message ?? 'Ошибка запроса'
    throw new ApiError(message, status, body)
  }
}

export function useApi() {
  return { api, apiBase, apiOrigin, ensureCsrfCookie, ApiError }
}
