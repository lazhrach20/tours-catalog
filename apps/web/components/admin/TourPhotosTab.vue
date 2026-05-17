<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import FileUpload from 'primevue/fileupload'
import { api } from '@/composables/useApi'
import { useAppToast } from '@/composables/useToast'
import type { AdminTour, TourPhoto } from '@/types/admin'

const props = defineProps<{
  tourId: number
  photos: TourPhoto[]
}>()

const emit = defineEmits<{
  update: [photos: TourPhoto[]]
}>()

const uploading = ref(false)
const reordering = ref(false)
const { showError, showSuccess } = useAppToast()

async function uploadFiles(event: { files: File | File[] }) {
  uploading.value = true
  const updated = [...props.photos]
  const files = Array.isArray(event.files) ? event.files : [event.files]
  try {
    for (const file of files) {
      const form = new FormData()
      form.append('photo', file)
      if (updated.length === 0) {
        form.append('is_cover', '1')
      }
      const res = await api<{ photo: TourPhoto }>(`admin/tours/${props.tourId}/photos`, {
        method: 'POST',
        body: form,
      })
      updated.push(res.photo)
    }
    emit('update', updated.sort((a, b) => a.sort_order - b.sort_order))
    showSuccess('Фото загружены')
  } catch (error) {
    showError(error)
  } finally {
    uploading.value = false
  }
}

async function setCover(photo: TourPhoto) {
  try {
    await api(`admin/photos/${photo.id}/cover`, { method: 'PATCH' })
    emit(
      'update',
      props.photos.map((p) => ({ ...p, is_cover: p.id === photo.id })),
    )
    showSuccess('Обложка обновлена')
  } catch (error) {
    showError(error)
  }
}

async function removePhoto(photo: TourPhoto) {
  try {
    await api(`admin/photos/${photo.id}`, { method: 'DELETE' })
    emit(
      'update',
      props.photos.filter((p) => p.id !== photo.id),
    )
    showSuccess('Фото удалено')
  } catch (error) {
    showError(error)
  }
}

function movePhoto(index: number, direction: -1 | 1) {
  const next = [...props.photos]
  const target = index + direction
  if (target < 0 || target >= next.length) return
  ;[next[index], next[target]] = [next[target], next[index]]
  saveOrder(next)
}

async function saveOrder(ordered: TourPhoto[]) {
  reordering.value = true
  try {
    const res = await api<{ photos: TourPhoto[] }>(`admin/tours/${props.tourId}/photos/reorder`, {
      method: 'PATCH',
      body: { photo_ids: ordered.map((p) => p.id) },
    })
    emit('update', res.photos)
  } catch (error) {
    showError(error)
  } finally {
    reordering.value = false
  }
}
</script>

<template>
  <div class="space-y-4">
    <FileUpload
      mode="basic"
      name="photo"
      accept="image/*"
      :multiple="true"
      choose-label="Загрузить фото"
      :auto="true"
      custom-upload
      :disabled="uploading || reordering"
      @uploader="uploadFiles"
    />

    <p v-if="!photos.length" class="text-sm text-slate-500">Нет фотографий. Загрузите хотя бы одну для публикации.</p>

    <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <li
        v-for="(photo, index) in photos"
        :key="photo.id"
        class="overflow-hidden rounded-lg border bg-white shadow-sm"
        :class="{ 'ring-2 ring-teal-500': photo.is_cover }"
      >
        <img :src="photo.url" :alt="photo.alt ?? ''" class="h-40 w-full object-cover" />
        <div class="flex flex-wrap gap-1 p-2">
          <Button
            v-if="!photo.is_cover"
            label="Обложка"
            size="small"
            severity="secondary"
            @click="setCover(photo)"
          />
          <span v-else class="self-center px-2 text-xs font-medium text-teal-700">Обложка</span>
          <Button icon="pi pi-arrow-up" size="small" text :disabled="index === 0 || reordering" @click="movePhoto(index, -1)" />
          <Button
            icon="pi pi-arrow-down"
            size="small"
            text
            :disabled="index === photos.length - 1 || reordering"
            @click="movePhoto(index, 1)"
          />
          <Button icon="pi pi-trash" size="small" severity="danger" text @click="removePhoto(photo)" />
        </div>
      </li>
    </ul>
  </div>
</template>
