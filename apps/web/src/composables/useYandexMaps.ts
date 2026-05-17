const SCRIPT_ID = 'yandex-maps-api'

let loadPromise: Promise<YMapsNamespace> | null = null

export function useYandexMaps() {
  function load(): Promise<YMapsNamespace> {
    if (typeof window === 'undefined') {
      return Promise.reject(new Error('Yandex Maps доступны только в браузере'))
    }

    if (window.ymaps) {
      return new Promise((resolve) => {
        window.ymaps!.ready(() => resolve(window.ymaps!))
      })
    }

    if (loadPromise) return loadPromise

    const apiKey = import.meta.env.VITE_YANDEX_MAPS_API_KEY
    if (!apiKey) {
      return Promise.reject(new Error('Не задан VITE_YANDEX_MAPS_API_KEY'))
    }

    loadPromise = new Promise((resolve, reject) => {
      const existing = document.getElementById(SCRIPT_ID)
      if (existing) {
        existing.addEventListener('load', () => {
          window.ymaps?.ready(() => resolve(window.ymaps!))
        })
        return
      }

      const script = document.createElement('script')
      script.id = SCRIPT_ID
      script.src = `https://api-maps.yandex.ru/2.1/?apikey=${encodeURIComponent(apiKey)}&lang=ru_RU`
      script.async = true
      script.onload = () => {
        window.ymaps?.ready(() => resolve(window.ymaps!))
      }
      script.onerror = () => reject(new Error('Не удалось загрузить Яндекс.Карты'))
      document.head.appendChild(script)
    })

    return loadPromise
  }

  return { load }
}
