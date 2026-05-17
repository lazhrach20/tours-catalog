<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { navigate } from "vike/client/router";
import { useData } from "vike-vue/useData";
import TourFilters from "@/components/tours/TourFilters.vue";
import TourGrid from "@/components/tours/TourGrid.vue";
import TourSearch from "@/components/tours/TourSearch.vue";
import {
  mergeTourListQuery,
  tourListQueryToSearchParams,
  type TourListQuery,
} from "@/utils/query";
import type { ToursPageData } from "./+data";

const pageData = useData<ToursPageData>();

const catalogData = computed(() =>
  "tours" in pageData && pageData.tours ? pageData : null,
);

const localQuery = ref<TourListQuery>({
  ...("query" in pageData ? pageData.query : {}),
});

watch(
  () => ("query" in pageData ? pageData.query : undefined),
  (query) => {
    if (query) {
      localQuery.value = { ...query };
    }
  },
);

const searchQuery = computed({
  get: () => localQuery.value.q ?? "",
  set: (value: string) => {
    applyQuery({ q: value || undefined, page: "1" });
  },
});

function applyQuery(patch: TourListQuery) {
  const next = mergeTourListQuery(localQuery.value, patch);
  localQuery.value = next;
  const params = tourListQueryToSearchParams(next);
  const search = params.toString();
  void navigate(`/tours${search ? `?${search}` : ""}`);
}

function resetFilters() {
  const preservedQ = localQuery.value.q;
  const next: TourListQuery = preservedQ ? { q: preservedQ } : {};
  localQuery.value = next;
  const params = tourListQueryToSearchParams(next);
  const search = params.toString();
  void navigate(`/tours${search ? `?${search}` : ""}`);
}
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold tracking-tight text-surface-900">
        Каталог туров
      </h1>
      <p class="max-w-2xl text-slate-600">
        Подберите путешествие по категориям, датам и бюджету или воспользуйтесь семантическим поиском.
      </p>
    </header>

    <div
      v-if="'error' in pageData && pageData.error"
      class="rounded-[var(--radius-card)] border border-red-200 bg-red-50 px-6 py-12 text-center"
      role="alert"
    >
      <p class="text-lg font-medium text-red-900">Не удалось загрузить каталог</p>
      <p class="mt-2 text-sm text-red-700">{{ pageData.error }}</p>
    </div>

    <div v-else-if="catalogData" class="catalog-layout">
      <div class="catalog-sidebar">
        <TourSearch v-model="searchQuery" />
        <TourFilters
          :categories="catalogData.categories"
          :query="localQuery"
          @change="applyQuery"
          @reset="resetFilters"
        />
      </div>

      <div class="catalog-results">
        <TourGrid
          :tours="catalogData.tours"
          @page-change="applyQuery({ page: String($event) })"
        />
      </div>
    </div>

    <div
      v-else
      class="rounded-[var(--radius-card)] border border-surface-100 bg-surface-0 px-6 py-16 text-center text-slate-600"
      aria-busy="true"
    >
      Загрузка каталога…
    </div>
  </div>
</template>

<style scoped>
.catalog-layout {
  display: grid;
  gap: 2rem;
  min-width: 0;
}

@media (min-width: 1024px) {
  .catalog-layout {
    grid-template-columns: minmax(0, 20rem) minmax(0, 1fr);
    align-items: start;
  }
}

.catalog-sidebar {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 1024px) {
  .catalog-sidebar {
    position: sticky;
    top: 1.5rem;
  }
}

.catalog-results {
  min-width: 0;
}
</style>
