<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { navigate } from 'vike/client/router'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import Chip from 'primevue/chip'
import MultiSelect from 'primevue/multiselect'
import Select from 'primevue/select'
import TourPhotosTab from './TourPhotosTab.vue'
import TourRouteTab from './TourRouteTab.vue'
import TourDeparturesTab from './TourDeparturesTab.vue'
import { api } from '@/composables/useApi'
import { fetchAdminCategories } from '@/lib/admin'
import { useAppToast } from '@/composables/useToast'
import type { AdminTour, Category, TourStatus } from '@/types/admin'

const props = defineProps<{
  tour: AdminTour
  isNew?: boolean
}>()

const emit = defineEmits<{
  saved: [tour: AdminTour]
}>()

const model = ref<AdminTour>({ ...props.tour })
const categories = ref<Category[]>([])
const categoriesLoading = ref(true)
const categoriesReady = ref(false)
const selectedCategories = ref<Category[]>([])
const saving = ref(false)
const publishing = ref(false)
const activeTab = ref('0')

const { showError, showSuccess } = useAppToast()

const hasId = computed(() => Boolean(model.value.id))

function categoryChipLabel(value: unknown): string {
  if (value && typeof value === 'object' && 'name' in value) {
    const name = (value as Category).name
    if (name) {
      return name
    }
  }

  const id = Number(value)
  if (Number.isFinite(id)) {
    return categories.value.find((c) => c.id === id)?.name ?? String(value)
  }

  return String(value ?? '')
}

function tourCategoryIds(): number[] {
  const source = props.tour.categories ?? model.value.categories ?? []
  return source
    .map((c) => Number(c.id))
    .filter((id) => Number.isFinite(id))
}

function applyCategorySelection(ids: number[]) {
  const idSet = new Set(
    ids.map((id) => Number(id)).filter((id) => Number.isFinite(id)),
  )
  const selected = categories.value.filter((c) => idSet.has(Number(c.id)))
  selectedCategories.value = selected
  model.value.categories = [...selected]
}

watch(selectedCategories, (selected) => {
  if (!categoriesReady.value) {
    return
  }
  model.value.categories = [...selected]
})

const statusOptions = [
  { label: 'Черновик', value: 'DRAFT' },
  { label: 'Опубликован', value: 'PUBLISHED' },
  { label: 'Архив', value: 'ARCHIVED' },
]

onMounted(() => {
  void loadCategories()
})

async function loadCategories() {
  categoriesLoading.value = true
  categoriesReady.value = false
  selectedCategories.value = []

  try {
    categories.value = await fetchAdminCategories()
    await nextTick()
    applyCategorySelection(tourCategoryIds())
    categoriesReady.value = true
  } catch (error) {
    categories.value = []
    applyCategorySelection([])
    showError(error, 'Не удалось загрузить категории')
  } finally {
    categoriesLoading.value = false
  }
}

function buildPayload() {
  return {
    title: model.value.title,
    slug: model.value.slug || undefined,
    short_description: model.value.short_description,
    full_description: model.value.full_description,
    duration_days: model.value.duration_days,
    duration_nights: model.value.duration_nights ?? undefined,
    status: model.value.status,
    meta_title: model.value.meta_title ?? undefined,
    meta_description: model.value.meta_description ?? undefined,
    category_ids: selectedCategories.value.map((c) => c.id),
  }
}

async function saveTour() {
  saving.value = true
  try {
    if (model.value.id) {
      const res = await api<{ data: AdminTour }>(`admin/tours/${model.value.id}`, {
        method: 'PATCH',
        body: buildPayload(),
      })
      model.value = { ...model.value, ...res.data }
      if (categoriesReady.value && res.data.categories) {
        applyCategorySelection(res.data.categories.map((c) => c.id))
      }
    } else {
      const res = await api<{ data: AdminTour }>('admin/tours', {
        method: 'POST',
        body: buildPayload(),
      })
      model.value = res.data
      await navigate(`/admin/tours/${res.data.id}/edit`, { overwriteLastHistoryEntry: true })
    }
    emit('saved', model.value)
    showSuccess('Тур сохранён')
  } catch (error) {
    showError(error)
  } finally {
    saving.value = false
  }
}

async function publishTour() {
  if (!model.value.id) {
    showError(new Error('Сначала сохраните тур'))
    return
  }
  publishing.value = true
  try {
    const res = await api<{ data: AdminTour }>(`admin/tours/${model.value.id}/publish`, {
      method: 'POST',
    })
    model.value = { ...model.value, ...res.data }
    showSuccess('Тур опубликован')
  } catch (error) {
    showError(error)
  } finally {
    publishing.value = false
  }
}

function applyDraft(draft: Partial<AdminTour> & { category_ids?: number[] }) {
  model.value = {
    ...model.value,
    ...draft,
    status: 'DRAFT',
  }
  applyCategorySelection(draft.category_ids ?? [])
}

defineExpose({ applyDraft })
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap gap-2">
      <Button label="Сохранить" icon="pi pi-save" :loading="saving" @click="saveTour" />
      <Button
        v-if="hasId"
        label="Опубликовать"
        icon="pi pi-check"
        severity="success"
        :loading="publishing"
        @click="publishTour"
      />
    </div>

    <Tabs v-model:value="activeTab">
      <TabList>
        <Tab value="0">Основное</Tab>
        <Tab value="1" :disabled="!hasId">Фото</Tab>
        <Tab value="2" :disabled="!hasId">Маршрут</Tab>
        <Tab value="3" :disabled="!hasId">Даты и цены</Tab>
        <Tab value="4">SEO</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <div class="grid max-w-3xl gap-4">
            <label class="text-sm font-medium">Название <InputText v-model="model.title" class="mt-1 w-full" /></label>
            <label class="text-sm font-medium">Slug <InputText v-model="model.slug" class="mt-1 w-full" /></label>
            <label class="text-sm font-medium">
              Категории
              <MultiSelect
                v-if="categoriesReady"
                v-model="selectedCategories"
                :options="categories"
                option-label="name"
                data-key="id"
                class="mt-1 w-full"
                display="chip"
                filter
                placeholder="Выберите категории"
                empty-message="Нет категорий"
              >
                <template #chip="{ value, removeCallback }">
                  <Chip
                    :label="categoryChipLabel(value)"
                    removable
                    @remove="removeCallback"
                  />
                </template>
              </MultiSelect>
              <p v-else-if="categoriesLoading" class="mt-1 text-sm text-slate-500">
                Загрузка категорий…
              </p>
              <p
                v-if="!categoriesLoading && !categories.length"
                class="mt-1 text-sm text-amber-700"
              >
                Список категорий пуст. Добавьте их в разделе «Категории».
              </p>
            </label>
            <div class="grid grid-cols-2 gap-3">
              <label class="text-sm font-medium">Дней <InputNumber v-model="model.duration_days" class="mt-1 w-full" :min="1" /></label>
              <label class="text-sm font-medium">Ночей <InputNumber v-model="model.duration_nights" class="mt-1 w-full" :min="0" /></label>
            </div>
            <label class="text-sm font-medium">
              Статус
              <Select
                v-model="model.status"
                :options="statusOptions"
                option-label="label"
                option-value="value"
                class="mt-1 w-full"
              />
            </label>
            <label class="text-sm font-medium">Краткое описание <Textarea v-model="model.short_description" class="mt-1 w-full" rows="3" auto-resize /></label>
            <label class="text-sm font-medium">Полное описание (Markdown) <Textarea v-model="model.full_description" class="mt-1 w-full" rows="8" /></label>
          </div>
        </TabPanel>
        <TabPanel value="1">
          <TourPhotosTab
            v-if="model.id"
            :tour-id="model.id"
            :photos="model.photos ?? []"
            @update="(photos) => (model.photos = photos)"
          />
        </TabPanel>
        <TabPanel value="2">
          <TourRouteTab
            v-if="model.id"
            :tour-id="model.id"
            :points="model.route_points ?? []"
            @update="(points) => (model.route_points = points)"
          />
        </TabPanel>
        <TabPanel value="3">
          <TourDeparturesTab
            v-if="model.id"
            :tour-id="model.id"
            :departures="model.departures ?? []"
            @update="(departures) => (model.departures = departures)"
          />
        </TabPanel>
        <TabPanel value="4">
          <div class="grid max-w-3xl gap-4">
            <label class="text-sm font-medium">Meta title <InputText v-model="model.meta_title" class="mt-1 w-full" /></label>
            <label class="text-sm font-medium">Meta description <Textarea v-model="model.meta_description" class="mt-1 w-full" rows="3" auto-resize /></label>
          </div>
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>
