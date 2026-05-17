import type { OnCreateAppSync } from 'vike-vue/types'
import { setupPrimeVue } from '@/plugins/primevue'
import '@/styles/main.css'

export const onCreateApp: OnCreateAppSync = (pageContext) => {
  setupPrimeVue(pageContext.app)
}
