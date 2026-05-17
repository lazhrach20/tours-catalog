<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { navigate } from 'vike/client/router'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Paginator from 'primevue/paginator'
import TourTable from '../../../components/admin/TourTable.vue'
import { api } from '@/composables/useApi'
import { useAppToast } from '@/composables/useToast'
import type { AdminTour, PaginatedResponse, TourStatus } from '@/types/admin'

const tours = ref<AdminTour[]>([])
const loading = ref(false)
const q = ref('')
const status = ref<TourStatus | ''>('')
const page = ref(1)
const total = ref(0)
const perPage = 20

const statusOptions = [
  { label: 'Все статусы', value: '' },
  { label: 'Черновик', value: 'DRAFT' },
  { label: 'Опубликован', value: 'PUBLISHED' },
  { label: 'Архив', value: 'ARCHIVED' },
]

const { showError } = useAppToast()

async function loadTours() {
  loading.value = true
  try {
    const response = await api<PaginatedResponse<AdminTour>>('admin/tours', {
      query: {
        page: page.value,
        per_page: perPage,
        ...(q.value ? { q: q.value } : {}),
        ...(status.value ? { status: status.value } : {}),
      },
    })
    tours.value = response.data
    total.value = response.meta?.total ?? response.data.length
  } catch (error) {
    showError(error)
  } finally {
    loading.value = false
  }
}

let searchTimer: ReturnType<typeof setTimeout> | undefined
watch(q, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    loadTours()
  }, 350)
})

watch(status, () => {
  page.value = 1
  loadTours()
})

function onPage(event: { page: number }) {
  page.value = event.page + 1
  loadTours()
}

onMounted(loadTours)
</script>

<template>
  <div class="flex flex-1 flex-col gap-4 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-semibold text-slate-900">Туры</h1>
      <Button label="Новый тур" icon="pi pi-plus" @click="() => navigate('/admin/tours/new')" />
    </div>

    <div class="flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm">
      <InputText v-model="q" placeholder="Поиск по названию…" class="w-64" />
      <Select
        v-model="status"
        :options="statusOptions"
        option-label="label"
        option-value="value"
        placeholder="Статус"
        class="w-48"
      />
      <Button label="Обновить" icon="pi pi-refresh" severity="secondary" @click="loadTours" />
    </div>

    <div class="rounded-xl bg-white p-2 shadow-sm">
      <TourTable :tours="tours" :loading="loading" />
      <Paginator
        v-if="total > perPage"
        :rows="perPage"
        :total-records="total"
        :first="(page - 1) * perPage"
        class="mt-2"
        @page="onPage"
      />
    </div>
  </div>
</template>
