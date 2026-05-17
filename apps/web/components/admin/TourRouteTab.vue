<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import { api } from '@/composables/useApi'
import { useYandexMaps } from '@/composables/useYandexMaps'
import { useAppToast } from '@/composables/useToast'
import type { RoutePoint } from '@/types/admin'

const props = defineProps<{
  tourId: number
  points: RoutePoint[]
}>()

const emit = defineEmits<{
  update: [points: RoutePoint[]]
}>()

const localPoints = ref<RoutePoint[]>([])
const mapEl = ref<HTMLElement | null>(null)
const saving = ref(false)
let map: YMap | null = null
let placemarks: unknown[] = []
let polyline: unknown | null = null

const { load } = useYandexMaps()
const { showError, showSuccess } = useAppToast()

watch(
  () => props.points,
  (value) => {
    localPoints.value = value.map((p, i) => ({ ...p, sort_order: i }))
  },
  { immediate: true, deep: true },
)

function defaultCenter(): number[] {
  if (localPoints.value.length) {
    return [localPoints.value[0].lat, localPoints.value[0].lng]
  }
  return [55.751244, 37.618423]
}

async function initMap() {
  if (!mapEl.value) return
  try {
    const ymaps = await load()
    map = new ymaps.Map(mapEl.value, {
      center: defaultCenter(),
      zoom: localPoints.value.length > 1 ? 6 : 8,
      controls: ['zoomControl', 'typeSelector'],
    })
    map.events.add('click', (e: YMapClickEvent) => {
      const coords = e.get('coords')
      addPoint(coords[0], coords[1])
    })
    renderMarkers(ymaps)
  } catch (error) {
    showError(error, 'Карта недоступна')
  }
}

function renderMarkers(ymaps: YMapsNamespace) {
  if (!map) return
  placemarks.forEach((m) => map!.geoObjects.remove(m))
  placemarks = []
  if (polyline) {
    map.geoObjects.remove(polyline)
    polyline = null
  }

  localPoints.value.forEach((point, index) => {
    const placemark = new ymaps.Placemark(
      [point.lat, point.lng],
      { iconContent: String(index + 1), balloonContent: point.title },
      { preset: 'islands#greenCircleIcon' },
    )
    map!.geoObjects.add(placemark)
    placemarks.push(placemark)
  })

  if (localPoints.value.length >= 2) {
    polyline = new ymaps.Polyline(
      localPoints.value.map((p) => [p.lat, p.lng]),
      {},
      { strokeColor: '#0d6e6e', strokeWidth: 3 },
    )
    map.geoObjects.add(polyline)
  }
}

function addPoint(lat: number, lng: number) {
  localPoints.value.push({
    title: `Точка ${localPoints.value.length + 1}`,
    lat,
    lng,
    sort_order: localPoints.value.length,
  })
  load().then(renderMarkers).catch(() => {})
}

function removePoint(index: number) {
  localPoints.value.splice(index, 1)
  localPoints.value.forEach((p, i) => {
    p.sort_order = i
  })
  load().then(renderMarkers).catch(() => {})
}

function movePoint(index: number, direction: -1 | 1) {
  const target = index + direction
  if (target < 0 || target >= localPoints.value.length) return
  const arr = [...localPoints.value]
  ;[arr[index], arr[target]] = [arr[target], arr[index]]
  arr.forEach((p, i) => {
    p.sort_order = i
  })
  localPoints.value = arr
  load().then(renderMarkers).catch(() => {})
}

async function saveRoute() {
  saving.value = true
  try {
    const res = await api<{ route_points: RoutePoint[] }>(`admin/tours/${props.tourId}/route-points`, {
      method: 'PUT',
      body: {
        points: localPoints.value.map((p, i) => ({
          title: p.title,
          lat: p.lat,
          lng: p.lng,
          sort_order: i,
        })),
      },
    })
    emit('update', res.route_points)
    showSuccess('Маршрут сохранён')
  } catch (error) {
    showError(error)
  } finally {
    saving.value = false
  }
}

onMounted(initMap)
onBeforeUnmount(() => {
  map?.destroy()
  map = null
})
</script>

<template>
  <div class="grid gap-4 lg:grid-cols-2">
    <div ref="mapEl" class="h-96 w-full rounded-lg border bg-slate-100 lg:h-[32rem]" />
    <div class="space-y-3">
      <p class="text-sm text-slate-600">Кликните по карте, чтобы добавить точку. Минимум 2 точки для публикации.</p>
      <ul class="max-h-96 space-y-2 overflow-y-auto">
        <li
          v-for="(point, index) in localPoints"
          :key="index"
          class="rounded-lg border bg-white p-3 text-sm"
        >
          <InputText v-model="point.title" class="mb-2 w-full" placeholder="Название" />
          <div class="mb-2 grid grid-cols-2 gap-2">
            <InputNumber v-model="point.lat" :min-fraction-digits="4" :max-fraction-digits="6" placeholder="Широта" />
            <InputNumber v-model="point.lng" :min-fraction-digits="4" :max-fraction-digits="6" placeholder="Долгота" />
          </div>
          <div class="flex gap-1">
            <Button icon="pi pi-arrow-up" size="small" text :disabled="index === 0" @click="movePoint(index, -1)" />
            <Button
              icon="pi pi-arrow-down"
              size="small"
              text
              :disabled="index === localPoints.length - 1"
              @click="movePoint(index, 1)"
            />
            <Button icon="pi pi-trash" size="small" severity="danger" text @click="removePoint(index)" />
          </div>
        </li>
      </ul>
      <Button label="Сохранить маршрут" icon="pi pi-save" :loading="saving" @click="saveRoute" />
    </div>
  </div>
</template>
