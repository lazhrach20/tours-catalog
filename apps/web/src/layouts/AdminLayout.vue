<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { usePageContext } from 'vike-vue/usePageContext'
import { navigate } from 'vike/client/router'
import Button from 'primevue/button'
import Toast from 'primevue/toast'
import { useAuth } from '@/composables/useAuth'

const pageContext = usePageContext()
const { user, logout, checkSession, restoreFromCache } = useAuth()
const ready = ref(false)

const isLoginPage = computed(() => pageContext.urlPathname === '/admin/login')

const nav = [
  { label: 'Туры', to: '/admin/tours' },
  { label: 'Категории', to: '/admin/categories' },
]

onMounted(async () => {
  restoreFromCache()
  const ok = await checkSession()

  if (isLoginPage.value) {
    if (ok) await navigate('/admin/tours')
    ready.value = true
    return
  }

  if (!ok) {
    ready.value = true
    if (!isLoginPage.value) {
      await navigate('/admin/login')
    }
    return
  }

  ready.value = true
})

async function handleLogout() {
  await logout()
  await navigate('/admin/login')
}
</script>

<template>
  <Toast position="top-right" />
  <div v-if="!ready" class="flex min-h-screen items-center justify-center text-slate-500">
    Загрузка…
  </div>

  <div v-else-if="isLoginPage" class="min-h-screen">
    <slot />
  </div>

  <div v-else class="flex min-h-screen">
    <aside class="flex w-56 flex-col border-r border-slate-200 bg-white p-4">
      <div class="mb-8 text-lg font-semibold text-teal-800">Админка</div>
      <nav class="flex flex-col gap-1">
        <a
          v-for="item in nav"
          :key="item.to"
          :href="item.to"
          class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-teal-50"
          :class="{ 'bg-teal-100 font-medium text-teal-900': pageContext.urlPathname.startsWith(item.to) }"
        >
          {{ item.label }}
        </a>
      </nav>
      <div class="mt-auto border-t border-slate-100 pt-4 text-xs text-slate-500">
        <p class="mb-2 truncate">{{ user?.email }}</p>
        <Button label="Выйти" severity="secondary" size="small" class="w-full" @click="handleLogout" />
      </div>
    </aside>
    <div class="flex flex-1 flex-col">
      <slot />
    </div>
  </div>
</template>
