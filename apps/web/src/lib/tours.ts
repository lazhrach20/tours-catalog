import { useApi } from "@/composables/useApi";
import type {
  Category,
  PaginatedResponse,
  TourCard,
  TourDetail,
} from "@/types/api";
import type { TourListQuery } from "@/utils/query";

function unwrapResource<T>(response: T | { data: T }): T {
  if (
    response !== null &&
    typeof response === "object" &&
    "data" in response &&
    response.data != null
  ) {
    return response.data;
  }

  return response as T;
}

export async function fetchTourList(
  query: TourListQuery,
): Promise<PaginatedResponse<TourCard>> {
  const { api } = useApi();
  return api<PaginatedResponse<TourCard>>("tours", { query });
}

export async function fetchTourDetail(slug: string): Promise<TourDetail> {
  const { api } = useApi();
  const response = await api<TourDetail | { data: TourDetail }>(`tours/${slug}`);
  return unwrapResource(response);
}

export async function fetchCategories(): Promise<Category[]> {
  const { api } = useApi();
  const response = await api<{ data: Category[] } | Category[]>("categories");
  return Array.isArray(response) ? response : response.data;
}
