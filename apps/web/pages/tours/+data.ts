import type { PageContextServer } from "vike/types";
import { ApiError } from "@/composables/useApi";
import { fetchCategories, fetchTourList } from "@/lib/tours";
import { buildTourListQuery } from "@/utils/query";

export type ToursPageData = Awaited<ReturnType<typeof data>>;

export async function data(pageContext: PageContextServer) {
  const query = buildTourListQuery(pageContext.urlParsed.search);

  try {
    const [tours, categories] = await Promise.all([
      fetchTourList(query),
      fetchCategories(),
    ]);

    return {
      tours,
      categories,
      query,
    };
  } catch (error) {
    const message =
      error instanceof ApiError
        ? error.message
        : error instanceof Error
          ? error.message
          : "Не удалось загрузить каталог";

    return { error: message, query };
  }
}
