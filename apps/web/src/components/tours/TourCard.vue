<script setup lang="ts">
import Tag from "primevue/tag";
import type { TourCard } from "@/types/api";
import { formatDateRu, formatDuration, formatPrice } from "@/utils/format";

defineProps<{
  tour: TourCard;
}>();
</script>

<template>
  <article
    class="group flex h-full flex-col overflow-hidden rounded-[var(--radius-card)] border border-surface-100 bg-surface-0 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
  >
    <a :href="`/tours/${tour.slug}`" class="block no-underline">
      <div class="relative aspect-[4/3] overflow-hidden bg-surface-100">
        <img
          v-if="tour.coverUrl"
          :src="tour.coverUrl"
          :alt="tour.title"
          class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
          loading="lazy"
        >
        <div
          v-else
          class="flex h-full items-center justify-center text-sm text-slate-400"
        >
          Нет фото
        </div>
      </div>
    </a>

    <div class="flex flex-1 flex-col gap-3 p-4">
      <div v-if="tour.categories.length" class="flex flex-wrap gap-1.5">
        <Tag
          v-for="category in tour.categories"
          :key="category.slug"
          :value="category.name"
          rounded
          class="text-xs"
          :style="category.color ? { backgroundColor: category.color, color: '#fff' } : undefined"
        />
      </div>

      <h2 class="text-lg font-semibold leading-snug text-surface-900">
        <a :href="`/tours/${tour.slug}`" class="no-underline text-inherit hover:text-brand-700">
          {{ tour.title }}
        </a>
      </h2>

      <p class="text-sm text-slate-600">
        {{ formatDuration(tour.duration.days, tour.duration.nights) }}
      </p>

      <div class="mt-auto flex items-end justify-between gap-3 border-t border-surface-100 pt-3 text-sm">
        <div>
          <p class="text-xs uppercase tracking-wide text-slate-500">от</p>
          <p class="text-lg font-semibold text-brand-700">
            {{ formatPrice(tour.fromPrice, tour.currency) }}
          </p>
        </div>
        <div class="text-right text-slate-600">
          <p class="text-xs uppercase tracking-wide text-slate-500">ближайший выезд</p>
          <p class="font-medium">{{ formatDateRu(tour.nextDepartureDate) }}</p>
        </div>
      </div>
    </div>
  </article>
</template>
