<script setup lang="ts">
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import MultiSelect from "primevue/multiselect";
import Select from "primevue/select";
import { computed } from "vue";
import type { Category } from "@/types/api";
import type { TourListQuery } from "@/utils/query";

const props = defineProps<{
  categories: Category[];
  query: TourListQuery;
}>();

const emit = defineEmits<{
  change: [patch: TourListQuery];
  reset: [];
}>();

const categoryOptions = computed(() =>
  props.categories.map((category) => ({
    label: category.name,
    value: category.slug,
  })),
);

const selectedCategories = computed({
  get() {
    const raw = props.query.categories;
    if (!raw) {
      return [];
    }
    return raw.split(",").filter(Boolean);
  },
  set(slugs: string[]) {
    emit("change", {
      categories: slugs.length ? slugs.join(",") : undefined,
      page: "1",
    });
  },
});

const sortOptions = [
  { label: "Ближайшая дата", value: "nearest_date" },
  { label: "Цена по возрастанию", value: "price_asc" },
  { label: "Длительность", value: "duration" },
  { label: "Новинки", value: "newest" },
];

const sortValue = computed({
  get: () => props.query.sort ?? "nearest_date",
  set: (value: string) => emit("change", { sort: value, page: "1" }),
});

function emitNumber(key: keyof TourListQuery, value: number | null) {
  emit("change", {
    [key]: value != null ? String(value) : undefined,
    page: "1",
  });
}

function parseDate(value: string | undefined): Date | null {
  if (!value) {
    return null;
  }
  const date = new Date(`${value}T00:00:00`);
  return Number.isNaN(date.getTime()) ? null : date;
}

function formatDate(value: Date | null): string | undefined {
  if (!value) {
    return undefined;
  }
  const year = value.getFullYear();
  const month = String(value.getMonth() + 1).padStart(2, "0");
  const day = String(value.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

const dateFrom = computed({
  get: () => parseDate(props.query.dateFrom),
  set: (value: Date | null) =>
    emit("change", { dateFrom: formatDate(value), page: "1" }),
});

const dateTo = computed({
  get: () => parseDate(props.query.dateTo),
  set: (value: Date | null) =>
    emit("change", { dateTo: formatDate(value), page: "1" }),
});
</script>

<template>
  <aside class="filter-panel">
    <div class="filter-panel__header">
      <h2 class="text-base font-semibold text-surface-900">Фильтры</h2>
      <button
        type="button"
        class="filter-panel__reset"
        @click="emit('reset')"
      >
        Сбросить
      </button>
    </div>

    <div class="filter-field">
      <label class="filter-label" for="filter-categories">Категории</label>
      <MultiSelect
        id="filter-categories"
        v-model="selectedCategories"
        :options="categoryOptions"
        option-label="label"
        option-value="value"
        placeholder="Все категории"
        display="chip"
        filter
        fluid
      />
    </div>

    <fieldset class="filter-group">
      <legend class="filter-legend">Длительность, дней</legend>
      <div class="filter-row">
        <div class="filter-field filter-field--half">
          <label class="filter-label" for="filter-min-days">Мин.</label>
          <InputNumber
            id="filter-min-days"
            :model-value="query.minDays ? Number(query.minDays) : null"
            :min="1"
            fluid
            @update:model-value="emitNumber('minDays', $event)"
          />
        </div>
        <div class="filter-field filter-field--half">
          <label class="filter-label" for="filter-max-days">Макс.</label>
          <InputNumber
            id="filter-max-days"
            :model-value="query.maxDays ? Number(query.maxDays) : null"
            :min="1"
            fluid
            @update:model-value="emitNumber('maxDays', $event)"
          />
        </div>
      </div>
    </fieldset>

    <fieldset class="filter-group">
      <legend class="filter-legend">Цена, ₽</legend>
      <div class="filter-row">
        <div class="filter-field filter-field--half">
          <label class="filter-label" for="filter-price-min">От</label>
          <InputNumber
            id="filter-price-min"
            :model-value="query.priceMin ? Number(query.priceMin) : null"
            :min="0"
            fluid
            @update:model-value="emitNumber('priceMin', $event)"
          />
        </div>
        <div class="filter-field filter-field--half">
          <label class="filter-label" for="filter-price-max">До</label>
          <InputNumber
            id="filter-price-max"
            :model-value="query.priceMax ? Number(query.priceMax) : null"
            :min="0"
            fluid
            @update:model-value="emitNumber('priceMax', $event)"
          />
        </div>
      </div>
    </fieldset>

    <fieldset class="filter-group">
      <legend class="filter-legend">Даты заезда</legend>
      <div class="filter-row">
        <div class="filter-field filter-field--half">
          <label class="filter-label" for="filter-date-from">От</label>
          <DatePicker
            id="filter-date-from"
            v-model="dateFrom"
            date-format="dd.mm.yy"
            show-icon
            icon-display="input"
            fluid
          />
        </div>
        <div class="filter-field filter-field--half">
          <label class="filter-label" for="filter-date-to">До</label>
          <DatePicker
            id="filter-date-to"
            v-model="dateTo"
            date-format="dd.mm.yy"
            show-icon
            icon-display="input"
            fluid
          />
        </div>
      </div>
    </fieldset>

    <div class="filter-field">
      <label class="filter-label" for="filter-sort">Сортировка</label>
      <Select
        id="filter-sort"
        v-model="sortValue"
        :options="sortOptions"
        option-label="label"
        option-value="value"
        fluid
      />
    </div>
  </aside>
</template>

<style scoped>
.filter-panel {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  min-width: 0;
  overflow: hidden;
  border-radius: var(--radius-card);
  border: 1px solid #e2e8f0;
  background: #fff;
  padding: 1rem;
  box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
}

.filter-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.filter-panel__reset {
  flex-shrink: 0;
  border: none;
  background: transparent;
  padding: 0;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--brand-primary, #0d6e6e);
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 2px;
}

.filter-panel__reset:hover {
  color: var(--brand-primary-hover, #0a5858);
}

.filter-group {
  margin: 0;
  min-width: 0;
  border: none;
  padding: 0;
}

.filter-legend {
  margin-bottom: 0.5rem;
  padding: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
}

.filter-row {
  display: flex;
  gap: 0.75rem;
  min-width: 0;
}

.filter-field {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 0.375rem;
}

.filter-field--half {
  flex: 1 1 0;
  min-width: 0;
}

.filter-label {
  font-size: 0.8125rem;
  font-weight: 500;
  color: #475569;
}

/* PrimeVue wrappers must shrink inside the sidebar */
.filter-panel :deep(.p-inputnumber),
.filter-panel :deep(.p-datepicker),
.filter-panel :deep(.p-multiselect),
.filter-panel :deep(.p-select) {
  width: 100%;
  max-width: 100%;
}

.filter-panel :deep(.p-inputnumber-input),
.filter-panel :deep(.p-datepicker-input),
.filter-panel :deep(.p-multiselect-label) {
  width: 100%;
  min-width: 0;
}
</style>
