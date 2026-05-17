<script setup lang="ts">
import { usePageContext } from "vike-vue/usePageContext";

const pageContext = usePageContext();
const { abortReason, abortStatusCode, is404 } = pageContext;

const message = (() => {
  if (typeof abortReason === "string" && abortReason.trim()) {
    return abortReason;
  }
  if (is404 || abortStatusCode === 404) {
    return "Страница не найдена.";
  }
  return "Что-то пошло не так. Попробуйте обновить страницу или вернуться в каталог.";
})();
</script>

<template>
  <div class="mx-auto max-w-lg px-4 py-16 text-center sm:px-6">
    <p class="text-sm font-medium uppercase tracking-wide text-brand-700">
      {{ is404 || abortStatusCode === 404 ? "404" : "Ошибка" }}
    </p>
    <h1 class="mt-2 text-2xl font-bold text-surface-900">{{ message }}</h1>
    <p class="mt-4">
      <a
        href="/tours"
        class="inline-flex items-center justify-center rounded-[var(--radius-button)] bg-brand-600 px-5 py-2.5 text-sm font-medium text-white no-underline hover:bg-brand-700"
      >
        В каталог туров
      </a>
    </p>
  </div>
</template>
