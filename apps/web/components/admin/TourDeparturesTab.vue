<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dialog from 'primevue/dialog'
import DatePicker from 'primevue/datepicker'
import { api } from '@/composables/useApi'
import { useAppToast } from '@/composables/useToast'
import type { TourDeparture } from '@/types/admin'

const props = defineProps<{
  tourId: number
  departures: TourDeparture[]
}>()

const emit = defineEmits<{
  update: [departures: TourDeparture[]]
}>()

const dialogVisible = ref(false)
const editing = ref<TourDeparture | null>(null)
const form = ref({
  start_date: null as Date | null,
  end_date: null as Date | null,
  price: 19900,
  currency: 'RUB',
  seats: null as number | null,
})
const saving = ref(false)
const { showError, showSuccess } = useAppToast()

function formatDate(d: Date | null): string | undefined {
  if (!d) return undefined
  return d.toISOString().slice(0, 10)
}

function openCreate() {
  editing.value = null
  form.value = {
    start_date: null,
    end_date: null,
    price: 19900,
    currency: 'RUB',
    seats: null,
  }
  dialogVisible.value = true
}

function openEdit(row: TourDeparture) {
  editing.value = row
  form.value = {
    start_date: row.start_date ? new Date(row.start_date) : null,
    end_date: row.end_date ? new Date(row.end_date) : null,
    price: row.price,
    currency: row.currency,
    seats: row.seats ?? null,
  }
  dialogVisible.value = true
}

async function saveDeparture() {
  saving.value = true
  const body = {
    start_date: formatDate(form.value.start_date),
    end_date: formatDate(form.value.end_date),
    price: form.value.price,
    currency: form.value.currency,
    seats: form.value.seats,
  }
  try {
    if (editing.value?.id) {
      const res = await api<{ data: TourDeparture }>(
        `admin/tours/${props.tourId}/departures/${editing.value.id}`,
        { method: 'PATCH', body },
      )
      emit(
        'update',
        props.departures.map((d) => (d.id === editing.value!.id ? res.data : d)),
      )
    } else {
      const res = await api<{ data: TourDeparture }>(`admin/tours/${props.tourId}/departures`, {
        method: 'POST',
        body,
      })
      emit('update', [...props.departures, res.data].sort((a, b) => a.start_date.localeCompare(b.start_date)))
    }
    dialogVisible.value = false
    showSuccess('Дата сохранена')
  } catch (error) {
    showError(error)
  } finally {
    saving.value = false
  }
}

async function removeDeparture(row: TourDeparture) {
  if (!row.id) return
  try {
    await api(`admin/tours/${props.tourId}/departures/${row.id}`, { method: 'DELETE' })
    emit(
      'update',
      props.departures.filter((d) => d.id !== row.id),
    )
    showSuccess('Дата удалена')
  } catch (error) {
    showError(error)
  }
}

function formatPrice(price: number, currency: string) {
  return new Intl.NumberFormat('ru-RU', { style: 'currency', currency }).format(price)
}
</script>

<template>
  <div class="space-y-3">
    <Button label="Добавить дату" icon="pi pi-plus" @click="openCreate" />
    <DataTable :value="departures" striped-rows class="text-sm">
      <Column field="start_date" header="Начало" />
      <Column field="end_date" header="Окончание">
        <template #body="{ data }">{{ data.end_date ?? '—' }}</template>
      </Column>
      <Column header="Цена">
        <template #body="{ data }">{{ formatPrice(data.price, data.currency) }}</template>
      </Column>
      <Column field="seats" header="Места" />
      <Column header="" style="width: 120px">
        <template #body="{ data }">
          <Button icon="pi pi-pencil" text size="small" @click="openEdit(data)" />
          <Button icon="pi pi-trash" text size="small" severity="danger" @click="removeDeparture(data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="dialogVisible" modal :header="editing ? 'Редактировать дату' : 'Новая дата'" class="w-full max-w-md">
      <div class="flex flex-col gap-3">
        <label class="text-sm">Дата начала <DatePicker v-model="form.start_date" date-format="yy-mm-dd" class="mt-1 w-full" /></label>
        <label class="text-sm">Дата окончания <DatePicker v-model="form.end_date" date-format="yy-mm-dd" class="mt-1 w-full" /></label>
        <label class="text-sm">Цена (копейки/руб — целое) <InputNumber v-model="form.price" class="mt-1 w-full" :min="1" /></label>
        <label class="text-sm">Валюта <InputText v-model="form.currency" class="mt-1 w-full" maxlength="3" /></label>
        <label class="text-sm">Мест <InputNumber v-model="form.seats" class="mt-1 w-full" :min="1" /></label>
      </div>
      <template #footer>
        <Button label="Отмена" severity="secondary" text @click="dialogVisible = false" />
        <Button label="Сохранить" :loading="saving" @click="saveDeparture" />
      </template>
    </Dialog>
  </div>
</template>
