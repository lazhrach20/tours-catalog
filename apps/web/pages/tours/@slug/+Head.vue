<script setup lang="ts">
import { computed } from "vue";
import { useData } from "vike-vue/useData";
import { usePageContext } from "vike-vue/usePageContext";
import type { TourDetailPageData } from "./+data";

const pageData = useData<TourDetailPageData>();
const pageContext = usePageContext();

const tour = computed(() => ("tour" in pageData ? pageData.tour : null));

const coverImage = computed(() => {
  const t = tour.value;
  if (!t) return undefined;
  return (
    t.gallery.find((item) => item.isCover)?.url ??
    t.gallery[0]?.url ??
    t.meta.coverUrl
  );
});

const title = computed(() => {
  const t = tour.value;
  if (!t) return "Тур";
  return t.meta.title || t.title;
});

const description = computed(() => {
  const t = tour.value;
  if (!t) return "";
  return t.meta.description || t.shortDescription || "";
});

const canonical = computed(() => {
  const t = tour.value;
  if (!t) return pageContext.urlOriginal;
  const origin = pageContext.urlOriginal.replace(/\/tours\/.*$/, "");
  return `${origin}/tours/${t.slug}`;
});
</script>

<template>
  <title>{{ title }}</title>
  <meta name="description" :content="description" />
  <meta property="og:title" :content="title" />
  <meta property="og:description" :content="description" />
  <meta v-if="coverImage" property="og:image" :content="coverImage" />
  <meta property="og:type" content="website" />
  <link rel="canonical" :href="canonical" />
</template>
