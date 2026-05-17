<script setup lang="ts">
import Tag from "primevue/tag";
import { computed } from "vue";
import { useData } from "vike-vue/useData";
import YandexMapRoute from "@/components/maps/YandexMapRoute.vue";
import { formatDateRu, formatDuration, formatPrice } from "@/utils/format";
import type { TourDetail } from "@/types/api";
import type { TourDetailPageData } from "./+data";

const pageData = useData<TourDetailPageData>();

const pageError = computed(() =>
  "error" in pageData ? pageData.error : undefined,
);

function isTourDetail(value: unknown): value is TourDetail {
  return (
    value !== null &&
    typeof value === "object" &&
    "slug" in value &&
    "gallery" in value &&
    Array.isArray((value as TourDetail).gallery)
  );
}

const tour = computed(() => {
  if (!("tour" in pageData) || !isTourDetail(pageData.tour)) {
    return undefined;
  }
  return pageData.tour;
});

const gallery = computed(() => tour.value?.gallery ?? []);

const coverImage = computed(() => {
  const t = tour.value;
  if (!t) {
    return undefined;
  }
  return (
    gallery.value.find((item) => item.isCover)?.url ??
    gallery.value[0]?.url ??
    t.meta.coverUrl
  );
});
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div
      v-if="pageError"
      class="rounded-[var(--radius-card)] border border-red-200 bg-red-50 px-6 py-12 text-center"
      role="alert"
    >
      <p class="text-lg font-medium text-red-900">Не удалось загрузить тур</p>
      <p class="mt-2 text-sm text-red-700">{{ pageError }}</p>
    </div>

    <template v-else-if="tour">
      <nav class="mb-6 text-sm text-slate-500">
        <a href="/tours" class="no-underline">Каталог</a>
        <span class="mx-2">/</span>
        <span class="text-surface-900">{{ tour.title }}</span>
      </nav>

      <header class="grid gap-8 lg:grid-cols-2">
        <section class="space-y-3">
          <div
            v-if="gallery.length"
            class="grid gap-2 sm:grid-cols-2"
          >
            <figure
              v-for="(photo, index) in gallery"
              :key="`${photo.url}-${index}`"
              class="overflow-hidden rounded-[var(--radius-card)] bg-surface-100"
              :class="index === 0 ? 'sm:col-span-2' : ''"
            >
              <img
                v-if="photo.url"
                :src="photo.url"
                :alt="photo.alt || tour.title"
                class="aspect-[16/10] w-full object-cover"
                :loading="index === 0 ? 'eager' : 'lazy'"
              >
            </figure>
          </div>
          <div
            v-else-if="coverImage"
            class="overflow-hidden rounded-[var(--radius-card)] bg-surface-100"
          >
            <img
              :src="coverImage"
              :alt="tour.title"
              class="aspect-[16/10] w-full object-cover"
            >
          </div>
        </section>

        <section class="space-y-4">
          <div v-if="tour.categories.length" class="flex flex-wrap gap-2">
            <Tag
              v-for="category in tour.categories"
              :key="category.id"
              :value="category.name"
              rounded
              :style="category.color ? { backgroundColor: category.color, color: '#fff' } : undefined"
            />
          </div>

          <h1 class="text-3xl font-bold tracking-tight text-surface-900">
            {{ tour.title }}
          </h1>

          <p v-if="tour.shortDescription" class="text-lg text-slate-600">
            {{ tour.shortDescription }}
          </p>

          <p class="text-sm text-slate-600">
            {{ formatDuration(tour.duration.days, tour.duration.nights) }}
          </p>
        </section>
      </header>

      <section
        v-if="tour.descriptionHtml"
        class="prose prose-slate mt-10 max-w-none"
        v-html="tour.descriptionHtml"
      />

      <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-semibold text-surface-900">Ближайшие выезды</h2>
        <div
          v-if="tour.departures.length"
          class="overflow-x-auto rounded-[var(--radius-card)] border border-surface-100 bg-surface-0"
        >
          <table class="min-w-full text-left text-sm">
            <thead class="border-b border-surface-100 bg-surface-50 text-slate-600">
              <tr>
                <th class="px-4 py-3 font-medium">Начало</th>
                <th class="px-4 py-3 font-medium">Окончание</th>
                <th class="px-4 py-3 font-medium">Цена</th>
                <th class="px-4 py-3 font-medium">Мест</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="departure in tour.departures"
                :key="departure.id"
                class="border-b border-surface-100 last:border-0"
              >
                <td class="px-4 py-3">{{ formatDateRu(departure.startDate) }}</td>
                <td class="px-4 py-3">{{ formatDateRu(departure.endDate) }}</td>
                <td class="px-4 py-3 font-medium text-brand-700">
                  {{ formatPrice(departure.price, departure.currency) }}
                </td>
                <td class="px-4 py-3">{{ departure.seats ?? "—" }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-sm text-slate-600">
          Нет запланированных выездов.
        </p>
      </section>

      <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-semibold text-surface-900">Маршрут</h2>
        <YandexMapRoute :points="tour.routePoints" />
        <ol
          v-if="tour.routePoints.length"
          class="grid gap-2 sm:grid-cols-2"
        >
          <li
            v-for="point in tour.routePoints"
            :key="`${point.sortOrder}-${point.title}`"
            class="rounded-lg border border-surface-100 bg-surface-0 px-4 py-3 text-sm"
          >
            <span class="font-medium text-brand-700">{{ point.sortOrder }}.</span>
            {{ point.title }}
          </li>
        </ol>
      </section>
    </template>

    <div
      v-else
      class="rounded-[var(--radius-card)] border border-surface-100 bg-surface-0 px-6 py-16 text-center text-slate-600"
      aria-busy="true"
    >
      Загрузка тура…
    </div>
  </div>
</template>
