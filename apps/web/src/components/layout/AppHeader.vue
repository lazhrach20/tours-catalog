<script setup lang="ts">
</script>

<template>
  <header class="border-b border-surface-100 bg-surface-0/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
      <a href="/tours" class="flex items-center gap-2 no-underline">
        <span
          class="flex h-10 w-10 items-center justify-center rounded-[var(--radius-button)] bg-brand-600 text-lg font-bold text-white"
          aria-hidden="true"
        >Т</span>
        <span class="text-lg font-semibold text-surface-900">Каталог туров</span>
      </a>
      <nav class="text-sm text-surface-800" aria-label="Основная навигация">
        <a href="/tours" class="font-medium text-brand-700 no-underline">Все туры</a>
      </nav>
    </div>
  </header>
</template>
