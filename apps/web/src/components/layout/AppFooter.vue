<script setup lang="ts">
const year = new Date().getFullYear();
</script>

<template>
  <footer class="mt-auto border-t border-surface-100 bg-surface-0">
    <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-slate-500 sm:px-6 lg:px-8">
      <p>© {{ year }} Каталог туров. Информация носит ознакомительный характер.</p>
    </div>
  </footer>
</template>
