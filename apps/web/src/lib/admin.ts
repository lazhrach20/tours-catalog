import { api } from "@/composables/useApi";
import type { Category } from "@/types/admin";

function unwrapList<T>(response: unknown): T[] {
  if (Array.isArray(response)) {
    return response as T[];
  }

  if (response && typeof response === "object" && "data" in response) {
    const data = (response as { data: unknown }).data;
    if (Array.isArray(data)) {
      return data as T[];
    }
  }

  return [];
}

export async function fetchAdminCategories(): Promise<Category[]> {
  const response = await api<unknown>("admin/categories");
  return unwrapList<Category>(response);
}
