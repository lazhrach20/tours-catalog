<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import { api } from '@/composables/useApi'
import { useAppToast } from '@/composables/useToast'
import type { AdminTour, TourDraftGenerate } from '@/types/admin'

const emit = defineEmits<{
  generated: [draft: Partial<AdminTour>]
}>()

const region = ref('')
const type = ref('')
const durationDays = ref<number | null>(7)
const notes = ref('')
const loading = ref(false)
const { showError, showSuccess } = useAppToast()

async function generate() {
  loading.value = true
  try {
    const response = await api<{ data: TourDraftGenerate; message: string }>('admin/tours/generate', {
      method: 'POST',
      body: {
        region: region.value || undefined,
        type: type.value || undefined,
        duration_days: durationDays.value ?? undefined,
        notes: notes.value || undefined,
      },
    })
    emit('generated', response.data)
    showSuccess('Черновик сгенерирован — проверьте поля и сохраните')
  } catch (error) {
    showError(error, 'Не удалось сгенерировать тур')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="mb-6 rounded-xl border border-dashed border-teal-300 bg-teal-50/50 p-4">
    <h2 class="mb-3 text-lg font-semibold text-teal-900">Сгенерировать черновик (LLM)</h2>
    <div class="grid gap-3 md:grid-cols-2">
      <label class="text-sm">
        Регион
        <InputText v-model="region" class="mt-1 w-full" placeholder="Байкал, Кавказ…" />
      </label>
      <label class="text-sm">
        Тип отдыха
        <InputText v-model="type" class="mt-1 w-full" placeholder="экскурсионный, пляжный…" />
      </label>
      <label class="text-sm">
        Длительность (дней)
        <InputNumber v-model="durationDays" class="mt-1 w-full" :min="1" :max="365" />
      </label>
      <label class="text-sm md:col-span-2">
        Пожелания
        <Textarea v-model="notes" class="mt-1 w-full" rows="2" auto-resize />
      </label>
    </div>
    <Button
      class="mt-3"
      label="Сгенерировать"
      icon="pi pi-sparkles"
      :loading="loading"
      @click="generate"
    />
  </section>
</template>
