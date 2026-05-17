import { computed, ref } from 'vue'
import { api, ApiError, ensureCsrfCookie } from '@/composables/useApi'
import type { AdminUser } from '@/types/admin'

const user = ref<AdminUser | null>(null)
const checked = ref(false)
const loading = ref(false)

export function useAuth() {
  const isAuthenticated = computed(() => user.value !== null)

  async function login(email: string, password: string): Promise<void> {
    loading.value = true
    try {
      await ensureCsrfCookie()
      const response = await api<{ user: AdminUser; message: string }>('admin/auth/login', {
        method: 'POST',
        body: { email, password },
      })
      user.value = response.user
      sessionStorage.setItem('admin_user', JSON.stringify(response.user))
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    try {
      await api('admin/auth/logout', { method: 'POST' })
    } catch {
      // ignore
    }
    user.value = null
    sessionStorage.removeItem('admin_user')
  }

  async function checkSession(): Promise<boolean> {
    const cached = sessionStorage.getItem('admin_user')
    if (cached) {
      try {
        user.value = JSON.parse(cached) as AdminUser
      } catch {
        sessionStorage.removeItem('admin_user')
      }
    }

    try {
      await ensureCsrfCookie()
      await api<{ data: unknown[] }>('admin/tours', { query: { per_page: 1 } })
      checked.value = true
      return true
    } catch (error) {
      if (error instanceof ApiError && error.status >= 500) {
        checked.value = true
        return false
      }
      user.value = null
      sessionStorage.removeItem('admin_user')
      checked.value = true
      return false
    }
  }

  function restoreFromCache(): void {
    const cached = sessionStorage.getItem('admin_user')
    if (cached && !user.value) {
      try {
        user.value = JSON.parse(cached) as AdminUser
      } catch {
        sessionStorage.removeItem('admin_user')
      }
    }
  }

  return {
    user,
    checked,
    loading,
    isAuthenticated,
    login,
    logout,
    checkSession,
    restoreFromCache,
  }
}
