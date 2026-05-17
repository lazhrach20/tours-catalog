import type { App } from 'vue'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'
import { definePreset } from '@primevue/themes'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'

const ToursPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '#e6f3f3',
      100: '#cce7e7',
      200: '#99cfcf',
      300: '#66b7b7',
      400: '#339f9f',
      500: '#0d6e6e',
      600: '#0a5858',
      700: '#084242',
      800: '#052c2c',
      900: '#031616',
      950: '#020b0b',
    },
  },
})

export function setupPrimeVue(app: App) {
  app.use(PrimeVue, {
    theme: {
      preset: ToursPreset,
      options: {
        darkModeSelector: false,
      },
    },
    ripple: true,
  })
  app.use(ToastService)
  app.use(ConfirmationService)
}
