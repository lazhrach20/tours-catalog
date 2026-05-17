<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { usePageContext } from 'vike-vue/usePageContext'
import TourEditorTabs from '../../../../../components/admin/TourEditorTabs.vue'
import { api } from '@/composables/useApi'
import { useAppToast } from '@/composables/useToast'
import type { AdminTour } from '@/types/admin'

const pageContext = usePageContext()
const tour = ref<AdminTour | null>(null)
const loading = ref(true)
const { showError } = useAppToast()

onMounted(async () => {
  const id = pageContext.routeParams.id
  try {
    const res = await api<{ data: AdminTour }>(`admin/tours/${id}`)
    tour.value = res.data
  } catch (error) {
    showError(error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="flex flex-1 flex-col gap-4 p-6">
    <h1 class="text-2xl font-semibold text-slate-900">
      {{ tour?.title ? `Редактирование: ${tour.title}` : 'Редактирование тура' }}
    </h1>
    <p v-if="loading" class="text-slate-500">Загрузка…</p>
    <TourEditorTabs v-else-if="tour" :tour="tour" />
  </div>
</template>
