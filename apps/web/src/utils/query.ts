export type TourListQuery = Record<string, string | undefined>;

export function buildTourListQuery(
  search: Record<string, string | string[] | undefined>,
): TourListQuery {
  const keys = [
    "q",
    "page",
    "perPage",
    "sort",
    "categories",
    "minDays",
    "maxDays",
    "priceMin",
    "priceMax",
    "dateFrom",
    "dateTo",
  ] as const;

  const query: TourListQuery = {};

  for (const key of keys) {
    const raw = search[key];
    if (raw === undefined) {
      continue;
    }
    const value = Array.isArray(raw) ? raw[0] : raw;
    if (value !== undefined && value !== "") {
      query[key] = value;
    }
  }

  return query;
}

export function tourListQueryToSearchParams(
  query: TourListQuery,
): URLSearchParams {
  const params = new URLSearchParams();

  for (const [key, value] of Object.entries(query)) {
    if (value !== undefined && value !== "") {
      params.set(key, value);
    }
  }

  return params;
}

export function mergeTourListQuery(
  current: TourListQuery,
  patch: TourListQuery,
): TourListQuery {
  const next: TourListQuery = { ...current };

  for (const [key, value] of Object.entries(patch)) {
    if (value === undefined || value === "") {
      delete next[key];
    } else {
      next[key] = value;
    }
  }

  return next;
}
