<script setup lang="ts">
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import { ref, watch } from "vue";

const props = defineProps<{
  modelValue: string;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: string];
}>();

const localValue = ref(props.modelValue);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(
  () => props.modelValue,
  (value) => {
    if (value !== localValue.value) {
      localValue.value = value;
    }
  },
);

watch(localValue, (value) => {
  if (debounceTimer) {
    clearTimeout(debounceTimer);
  }
  debounceTimer = setTimeout(() => {
    emit("update:modelValue", value.trim());
  }, 400);
});
</script>

<template>
  <div class="space-y-2">
    <label class="text-sm font-medium text-slate-700" for="tour-search">
      Семантический поиск
    </label>
    <IconField class="w-full">
      <InputIcon class="pi pi-search" />
      <InputText
        id="tour-search"
        v-model="localValue"
        type="search"
        placeholder="Например: горы, море, выходные..."
        fluid
        autocomplete="off"
      />
    </IconField>
    <p class="text-xs text-slate-500">
      Поиск по смыслу описания тура. Очистите поле, чтобы вернуться к обычному каталогу.
    </p>
  </div>
</template>
