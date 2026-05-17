<script setup lang="ts">
import { ref } from 'vue'
import { navigate } from 'vike/client/router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import { useAuth } from '@/composables/useAuth'
import { useAppToast } from '@/composables/useToast'

const email = ref('')
const password = ref('')
const { login, loading } = useAuth()
const { showError } = useAppToast()

async function onSubmit() {
  try {
    await login(email.value, password.value)
    await navigate('/admin/tours')
  } catch (error) {
    showError(error, 'Не удалось войти')
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-100 p-4">
    <form class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm" @submit.prevent="onSubmit">
      <h1 class="mb-6 text-2xl font-semibold text-teal-900">Вход в админку</h1>
      <label class="mb-4 block text-sm font-medium text-slate-700">
        Email
        <InputText v-model="email" type="email" class="mt-1 w-full" required autocomplete="username" />
      </label>
      <label class="mb-6 block text-sm font-medium text-slate-700">
        Пароль
        <Password
          v-model="password"
          class="mt-1 w-full"
          :feedback="false"
          toggle-mask
          input-class="w-full"
          required
          autocomplete="current-password"
        />
      </label>
      <Button type="submit" label="Войти" class="w-full" :loading="loading" />
    </form>
  </div>
</template>
