<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import { api } from '@/composables/useApi'
import { fetchAdminCategories } from '@/lib/admin'
import { useAppToast } from '@/composables/useToast'
import type { Category } from '@/types/admin'

const categories = ref<Category[]>([])
const loading = ref(false)
const dialogVisible = ref(false)
const editing = ref<Category | null>(null)
const form = ref({ name: '', slug: '', icon: '', color: '' })
const saving = ref(false)
const { showError, showSuccess } = useAppToast()

async function load() {
  loading.value = true
  try {
    categories.value = await fetchAdminCategories()
  } catch (error) {
    showError(error)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', slug: '', icon: '', color: '' }
  dialogVisible.value = true
}

function openEdit(row: Category) {
  editing.value = row
  form.value = {
    name: row.name,
    slug: row.slug,
    icon: row.icon ?? '',
    color: row.color ?? '',
  }
  dialogVisible.value = true
}

function slugify(name: string) {
  return name
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
}

async function save() {
  saving.value = true
  const body = {
    name: form.value.name,
    slug: form.value.slug || slugify(form.value.name),
    icon: form.value.icon || null,
    color: form.value.color || null,
  }
  try {
    if (editing.value) {
      const res = await api<{ data: Category }>(`admin/categories/${editing.value.id}`, {
        method: 'PATCH',
        body,
      })
      categories.value = categories.value.map((c) => (c.id === editing.value!.id ? res.data : c))
    } else {
      const res = await api<{ data: Category }>('admin/categories', { method: 'POST', body })
      categories.value = [...categories.value, res.data].sort((a, b) => a.name.localeCompare(b.name))
    }
    dialogVisible.value = false
    showSuccess('Категория сохранена')
  } catch (error) {
    showError(error)
  } finally {
    saving.value = false
  }
}

async function remove(row: Category) {
  try {
    await api(`admin/categories/${row.id}`, { method: 'DELETE' })
    categories.value = categories.value.filter((c) => c.id !== row.id)
    showSuccess('Категория удалена')
  } catch (error) {
    showError(error)
  }
}

onMounted(load)
</script>

<template>
  <div class="flex flex-1 flex-col gap-4 p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-slate-900">Категории</h1>
      <Button label="Добавить" icon="pi pi-plus" @click="openCreate" />
    </div>

    <DataTable
      :value="categories"
      :loading="loading"
      striped-rows
      class="rounded-xl bg-white shadow-sm"
      :empty-message="loading ? undefined : 'Категорий пока нет'"
    >
      <Column field="name" header="Название" />
      <Column field="slug" header="Slug" />
      <Column field="icon" header="Иконка" />
      <Column field="color" header="Цвет" />
      <Column header="" style="width: 120px">
        <template #body="{ data }">
          <Button icon="pi pi-pencil" text size="small" @click="openEdit(data)" />
          <Button icon="pi pi-trash" text size="small" severity="danger" @click="remove(data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog v-model:visible="dialogVisible" modal :header="editing ? 'Редактировать' : 'Новая категория'" class="w-full max-w-md">
      <div class="flex flex-col gap-3">
        <label class="text-sm">Название <InputText v-model="form.name" class="mt-1 w-full" @blur="!form.slug && (form.slug = slugify(form.name))" /></label>
        <label class="text-sm">Slug <InputText v-model="form.slug" class="mt-1 w-full" /></label>
        <label class="text-sm">Иконка <InputText v-model="form.icon" class="mt-1 w-full" /></label>
        <label class="text-sm">Цвет <InputText v-model="form.color" class="mt-1 w-full" placeholder="#0d6e6e" /></label>
      </div>
      <template #footer>
        <Button label="Отмена" severity="secondary" text @click="dialogVisible = false" />
        <Button label="Сохранить" :loading="saving" @click="save" />
      </template>
    </Dialog>
  </div>
</template>
