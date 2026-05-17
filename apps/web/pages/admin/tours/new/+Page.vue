<script setup lang="ts">
import { ref } from 'vue'
import TourGenerateForm from '../../../../components/admin/TourGenerateForm.vue'
import TourEditorTabs from '../../../../components/admin/TourEditorTabs.vue'
import type { AdminTour } from '@/types/admin'

const editorRef = ref<InstanceType<typeof TourEditorTabs> | null>(null)

const emptyTour: AdminTour = {
  title: '',
  slug: '',
  short_description: '',
  full_description: '',
  duration_days: 5,
  duration_nights: 4,
  status: 'DRAFT',
  meta_title: '',
  meta_description: '',
  categories: [],
  photos: [],
  route_points: [],
  departures: [],
}

function onGenerated(draft: Partial<AdminTour>) {
  editorRef.value?.applyDraft(draft)
}
</script>

<template>
  <div class="flex flex-1 flex-col gap-4 p-6">
    <h1 class="text-2xl font-semibold text-slate-900">Новый тур</h1>
    <TourGenerateForm @generated="onGenerated" />
    <TourEditorTabs ref="editorRef" :tour="emptyTour" is-new />
  </div>
</template>
