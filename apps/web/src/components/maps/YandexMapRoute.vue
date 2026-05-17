<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useYandexMaps } from "@/composables/useYandexMaps";
import type { RoutePoint } from "@/types/api";

const props = defineProps<{
  points: RoutePoint[];
}>();

const { load } = useYandexMaps();

const mapContainer = ref<HTMLElement | null>(null);
const loadError = ref<string | null>(null);
const isLoading = ref(true);

let mapInstance: YMap | null = null;

function sortedPoints(): RoutePoint[] {
  return [...props.points].sort((a, b) => a.sortOrder - b.sortOrder);
}

function fitBounds(map: YMap, coords: number[][]) {
  if (!coords.length) {
    return;
  }

  if (coords.length === 1) {
    map.setCenter(coords[0], 10);
    return;
  }

  const lats = coords.map((coord) => coord[0]);
  const lngs = coords.map((coord) => coord[1]);
  map.setBounds(
    [
      [Math.min(...lats), Math.min(...lngs)],
      [Math.max(...lats), Math.max(...lngs)],
    ],
    { checkZoomRange: true },
  );
}

function renderMap(ymaps: YMapsNamespace) {
  const container = mapContainer.value;
  if (!container) {
    return;
  }

  ymaps.ready(() => {
    const points = sortedPoints();
    if (!points.length) {
      isLoading.value = false;
      return;
    }

    if (mapInstance) {
      mapInstance.destroy();
      mapInstance = null;
    }

    const coords = points.map(
      (point) => [point.lat, point.lng] as [number, number],
    );
    const center = coords[Math.floor(coords.length / 2)] ?? [55.751244, 37.618423];

    mapInstance = new ymaps.Map(container, {
      center,
      zoom: 6,
      controls: ["zoomControl"],
    });

    for (const point of points) {
      mapInstance.geoObjects.add(
        new ymaps.Placemark(
          [point.lat, point.lng],
          { balloonContent: point.title },
          { preset: "islands#greenDotIcon" },
        ),
      );
    }

    if (coords.length > 1) {
      mapInstance.geoObjects.add(
        new ymaps.Polyline(coords, {}, {
          strokeColor: "#0d6e6e",
          strokeWidth: 4,
        }),
      );
    }

    fitBounds(mapInstance, coords);
    isLoading.value = false;
  });
}

async function initMap() {
  isLoading.value = true;
  loadError.value = null;

  try {
    const ymaps = await load();
    renderMap(ymaps);
  } catch (error) {
    loadError.value =
      error instanceof Error ? error.message : "Не удалось загрузить карту";
    isLoading.value = false;
  }
}

onMounted(() => {
  void initMap();
});

watch(
  () => props.points,
  async () => {
    if (typeof window !== "undefined" && window.ymaps) {
      renderMap(window.ymaps);
    }
  },
  { deep: true },
);

onBeforeUnmount(() => {
  mapInstance?.destroy();
  mapInstance = null;
});
</script>

<template>
  <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div
      v-if="isLoading"
      class="flex h-80 items-center justify-center text-sm text-slate-500"
    >
      Загрузка карты...
    </div>
    <div
      v-else-if="loadError"
      class="flex h-80 items-center justify-center px-4 text-center text-sm text-red-600"
      role="alert"
    >
      {{ loadError }}
    </div>
    <div
      v-else-if="!points.length"
      class="flex h-80 items-center justify-center text-sm text-slate-500"
    >
      Маршрут не задан
    </div>
    <div
      v-show="!isLoading && !loadError && points.length"
      ref="mapContainer"
      class="h-80 w-full"
      aria-label="Карта маршрута тура"
    />
  </section>
</template>
