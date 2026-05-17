import { useToast as usePrimeToast } from 'primevue/usetoast'
import { ApiError } from '@/composables/useApi'

export function useAppToast() {
  const toast = usePrimeToast()

  function showError(error: unknown, fallback = 'Произошла ошибка') {
    let detail = fallback
    if (error instanceof ApiError) {
      detail = error.message
      if (error.body?.errors) {
        const first = Object.values(error.body.errors)[0]?.[0]
        if (first) detail = first
      }
    } else if (error instanceof Error) {
      detail = error.message
    }
    toast.add({ severity: 'error', summary: 'Ошибка', detail, life: 5000 })
  }

  function showSuccess(detail: string) {
    toast.add({ severity: 'success', summary: 'Готово', detail, life: 3000 })
  }

  return { showError, showSuccess }
}
