export interface Category {
  id: number;
  name: string;
  slug: string;
  icon: string | null;
  color: string | null;
}

export interface TourCategoryChip {
  slug: string;
  name: string;
  color: string | null;
}

export interface TourDuration {
  days: number;
  nights: number | null;
}

export interface TourCard {
  slug: string;
  title: string;
  coverUrl: string | null;
  categories: TourCategoryChip[];
  duration: TourDuration;
  fromPrice: string | null;
  currency: string;
  nextDepartureDate: string | null;
}

export interface TourGalleryItem {
  url: string | null;
  alt: string | null;
  isCover: boolean;
}

export interface TourDeparture {
  id: number;
  startDate: string;
  endDate: string | null;
  price: string;
  currency: string;
  seats: number | null;
}

export interface RoutePoint {
  title: string;
  lat: number;
  lng: number;
  sortOrder: number;
}

export interface TourDetail {
  slug: string;
  title: string;
  shortDescription: string | null;
  descriptionHtml: string;
  duration: TourDuration;
  categories: Category[];
  gallery: TourGalleryItem[];
  departures: TourDeparture[];
  routePoints: RoutePoint[];
  meta: {
    title: string;
    description: string | null;
    coverUrl: string | null;
  };
  publishedAt: string | null;
}

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  path: string;
  per_page: number;
  to: number | null;
  total: number;
}

export interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
}

export interface ApiValidationErrors {
  [field: string]: string[];
}

export interface ApiErrorBody {
  message: string;
  errors?: ApiValidationErrors;
  code?: string;
}
