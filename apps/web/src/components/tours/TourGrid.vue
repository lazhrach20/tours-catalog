<script setup lang="ts">
import Button from "primevue/button";
import type { PaginatedResponse, TourCard } from "@/types/api";
import TourCardComponent from "./TourCard.vue";

const props = defineProps<{
  tours: PaginatedResponse<TourCard>;
}>();

const emit = defineEmits<{
  pageChange: [page: number];
}>();

function goToPage(page: number) {
  if (page < 1 || page > props.tours.meta.last_page) {
    return;
  }
  emit("pageChange", page);
}
</script>

<template>
  <section class="space-y-6">
    <div
      v-if="tours.data.length"
      class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3"
    >
      <TourCardComponent
        v-for="tour in tours.data"
        :key="tour.slug"
        :tour="tour"
      />
    </div>

    <div
      v-else
      class="rounded-[var(--radius-card)] border border-dashed border-surface-100 bg-surface-0 px-6 py-16 text-center"
    >
      <p class="text-lg font-medium text-surface-900">Туры не найдены</p>
      <p class="mt-2 text-sm text-slate-600">
        Попробуйте изменить фильтры или очистить поисковый запрос.
      </p>
    </div>

    <nav
      v-if="tours.meta.last_page > 1"
      class="flex flex-wrap items-center justify-center gap-2"
      aria-label="Пагинация"
    >
      <Button
        label="Назад"
        severity="secondary"
        outlined
        :disabled="tours.meta.current_page <= 1"
        @click="goToPage(tours.meta.current_page - 1)"
      />
      <span class="px-3 text-sm text-slate-600">
        Страница {{ tours.meta.current_page }} из {{ tours.meta.last_page }}
        <span class="text-slate-400">({{ tours.meta.total }} туров)</span>
      </span>
      <Button
        label="Вперёд"
        severity="secondary"
        outlined
        :disabled="tours.meta.current_page >= tours.meta.last_page"
        @click="goToPage(tours.meta.current_page + 1)"
      />
    </nav>
  </section>
</template>
