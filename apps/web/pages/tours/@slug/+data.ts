import type { PageContextServer } from "vike/types";
import { ApiError } from "@/composables/useApi";
import { fetchTourDetail } from "@/lib/tours";

export type TourDetailPageData = Awaited<ReturnType<typeof data>>;

export async function data(pageContext: PageContextServer) {
  const slug = pageContext.routeParams.slug;

  try {
    const tour = await fetchTourDetail(slug);
    return { tour };
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) {
      return { error: "Тур не найден." };
    }

    const message =
      error instanceof ApiError
        ? error.message
        : error instanceof Error
          ? error.message
          : "Не удалось загрузить тур";

    return { error: message };
  }
}
