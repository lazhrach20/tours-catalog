/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL?: string
  readonly VITE_API_BASE_URL?: string
  readonly VITE_API_INTERNAL_URL?: string
  readonly VITE_YANDEX_MAPS_API_KEY?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}

declare global {
  interface Window {
    ymaps?: YMapsNamespace
  }
}

interface YMapsNamespace {
  ready: (callback: () => void) => void
  Map: new (
    element: string | HTMLElement,
    state: { center: number[]; zoom: number; controls?: string[] },
    options?: Record<string, unknown>,
  ) => YMap
  Placemark: new (
    coords: number[],
    properties?: Record<string, unknown>,
    options?: Record<string, unknown>,
  ) => unknown
  Polyline: new (
    coords: number[][],
    properties?: Record<string, unknown>,
    options?: Record<string, unknown>,
  ) => unknown
  geocode?: (query: string) => Promise<unknown>
}

interface YMap {
  geoObjects: {
    add: (obj: unknown) => void
    remove: (obj: unknown) => void
    removeAll: () => void
  }
  events: { add: (event: string, handler: (e: YMapClickEvent) => void) => void }
  destroy: () => void
  setCenter: (center: number[], zoom?: number) => void
  setBounds: (
    bounds: number[][],
    options?: { checkZoomRange?: boolean },
  ) => void
}

interface YMapClickEvent {
  get: (key: 'coords') => number[]
}
