import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import vike from 'vike/plugin'
import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'

function adminRootRedirectPlugin() {
  return {
    name: 'admin-root-redirect',
    configureServer(server: {
      middlewares: {
        use: (
          handler: (
            req: { url?: string },
            res: { writeHead: (status: number, headers: Record<string, string>) => void; end: () => void },
            next: () => void,
          ) => void,
        ) => void
      }
    }) {
      server.middlewares.use((req, res, next) => {
        const path = req.url?.split('?')[0] ?? ''
        if (path === '/admin' || path === '/admin/') {
          res.writeHead(302, {
            Location: '/admin/tours',
            'Cache-Control': 'no-store',
          })
          res.end()
          return
        }
        next()
      })
    },
    configurePreviewServer(server: {
      middlewares: {
        use: (
          handler: (
            req: { url?: string },
            res: { writeHead: (status: number, headers: Record<string, string>) => void; end: () => void },
            next: () => void,
          ) => void,
        ) => void
      }
    }) {
      server.middlewares.use((req, res, next) => {
        const path = req.url?.split('?')[0] ?? ''
        if (path === '/admin' || path === '/admin/') {
          res.writeHead(302, {
            Location: '/admin/tours',
            'Cache-Control': 'no-store',
          })
          res.end()
          return
        }
        next()
      })
    },
  }
}

function healthCheckPlugin() {
  return {
    name: 'health-check',
    configureServer(server: { middlewares: { use: (path: string, handler: (req: unknown, res: { setHeader: (k: string, v: string) => void; end: (body: string) => void }) => void) => void } }) {
      server.middlewares.use('/health', (_req, res) => {
        res.setHeader('Content-Type', 'application/json')
        res.end(JSON.stringify({ status: 'ok', service: 'web' }))
      })
    },
    configurePreviewServer(server: { middlewares: { use: (path: string, handler: (req: unknown, res: { setHeader: (k: string, v: string) => void; end: (body: string) => void }) => void) => void } }) {
      server.middlewares.use('/health', (_req, res) => {
        res.setHeader('Content-Type', 'application/json')
        res.end(JSON.stringify({ status: 'ok', service: 'web' }))
      })
    },
  }
}

export default defineConfig({
  plugins: [tailwindcss(), vue(), vike(), adminRootRedirectPlugin(), healthCheckPlugin()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    port: Number(process.env.PORT ?? 3000),
    proxy: {
      // Same-origin proxy so Sanctum CSRF/session cookies work (localhost:3000 → API).
      '/api': {
        target: process.env.API_PROXY_TARGET ?? 'http://localhost:8080',
        changeOrigin: true,
        cookieDomainRewrite: 'localhost',
      },
      '/sanctum': {
        target: process.env.API_PROXY_TARGET ?? 'http://localhost:8080',
        changeOrigin: true,
        cookieDomainRewrite: 'localhost',
      },
      '/storage': {
        target: process.env.API_PROXY_TARGET ?? 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
})
