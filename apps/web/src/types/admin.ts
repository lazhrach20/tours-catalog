export type TourStatus = 'DRAFT' | 'PUBLISHED' | 'ARCHIVED'

export interface AdminUser {
  id: number
  name: string
  email: string
}

export interface Category {
  id: number
  name: string
  slug: string
  icon?: string | null
  color?: string | null
}

export interface TourPhoto {
  id: number
  path: string
  url: string
  alt?: string | null
  sort_order: number
  is_cover: boolean
}

export interface RoutePoint {
  id?: number
  title: string
  lat: number
  lng: number
  sort_order: number
}

export interface TourDeparture {
  id?: number
  start_date: string
  end_date?: string | null
  price: number
  currency: string
  seats?: number | null
}

export interface AdminTour {
  id?: number
  slug: string
  title: string
  short_description: string
  full_description: string
  duration_days: number
  duration_nights?: number | null
  status: TourStatus
  meta_title?: string | null
  meta_description?: string | null
  published_at?: string | null
  archived_at?: string | null
  created_at?: string
  updated_at?: string
  categories?: Category[]
  photos?: TourPhoto[]
  route_points?: RoutePoint[]
  departures?: TourDeparture[]
}

export interface PaginatedMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginatedMeta
  links?: Record<string, string | null>
}

export interface TourDraftGenerate {
  title: string
  slug: string
  short_description: string
  full_description: string
  duration_days: number
  duration_nights: number
  status: TourStatus
  meta_title: string
  meta_description: string
  category_ids: number[]
}

export interface ApiErrorBody {
  message: string
  errors?: Record<string, string[]>
  code?: string
}
