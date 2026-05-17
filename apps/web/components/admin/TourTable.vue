<script setup lang="ts">
import { computed } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import type { AdminTour, TourStatus } from '@/types/admin'

const props = defineProps<{
  tours: AdminTour[]
  loading?: boolean
}>()

const statusLabels: Record<TourStatus, string> = {
  DRAFT: 'Черновик',
  PUBLISHED: 'Опубликован',
  ARCHIVED: 'Архив',
}

const statusSeverity: Record<TourStatus, 'secondary' | 'success' | 'warn'> = {
  DRAFT: 'secondary',
  PUBLISHED: 'success',
  ARCHIVED: 'warn',
}

const rows = computed(() => props.tours)
</script>

<template>
  <DataTable
    :value="rows"
    :loading="loading"
    striped-rows
    class="text-sm"
    :empty-message="loading ? undefined : 'Туров пока нет'"
  >
    <Column field="title" header="Название">
      <template #body="{ data }">
        <a :href="`/admin/tours/${data.id}/edit`" class="font-medium text-teal-800 hover:underline">
          {{ data.title }}
        </a>
        <p class="text-xs text-slate-500">{{ data.slug }}</p>
      </template>
    </Column>
    <Column field="status" header="Статус" style="width: 140px">
      <template #body="{ data }">
        <Tag
          :value="statusLabels[data.status as TourStatus] ?? data.status"
          :severity="statusSeverity[data.status as TourStatus] ?? 'secondary'"
        />
      </template>
    </Column>
    <Column field="duration_days" header="Дней" style="width: 80px" />
    <Column field="updated_at" header="Обновлён" style="width: 180px">
      <template #body="{ data }">
        <span v-if="data.updated_at">{{ new Date(data.updated_at).toLocaleString('ru-RU') }}</span>
      </template>
    </Column>
  </DataTable>
</template>
