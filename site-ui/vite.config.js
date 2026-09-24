import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import Components from 'unplugin-vue-components/vite'
import { BootstrapVueNextResolver } from 'bootstrap-vue-next/resolvers'
import { fileURLToPath, URL } from 'node:url'
import path from 'node:path'
import fs from 'node:fs'

const siteRoot = fileURLToPath(new URL('.', import.meta.url))
const projectRoot = fileURLToPath(new URL('..', import.meta.url))
const nm = path.join(siteRoot, 'node_modules')

function resolveTheme(name) {
  const dir = path.join(projectRoot, 'addons', 'yscms', 'web', 'site', 'themes', name)
  if (fs.existsSync(dir)) {
    return dir
  }
  return path.join(projectRoot, 'addons', 'yscms', 'web', 'site', 'themes', 'ystheme')
}

/** SPA shell prerender: copy index.html for key routes (SEO entry points). */
function prerenderKeyPages(routes, outDir) {
  return {
    name: 'prerender-key-pages',
    closeBundle() {
      const indexFile = path.join(outDir, 'index.html')
      if (!fs.existsSync(indexFile)) {
        return
      }
      const html = fs.readFileSync(indexFile, 'utf8')
      const list = Array.isArray(routes) && routes.length ? routes : ['/', '/s']
      let written = 0
      for (const route of list) {
        const clean = String(route || '/').split('?')[0]
        if (clean === '/' || clean === '') {
          continue
        }
        const rel = clean.replace(/^\/+/, '').replace(/\/+$/, '')
        if (!rel || rel.includes('..')) {
          continue
        }
        const targetDir = path.join(outDir, rel)
        fs.mkdirSync(targetDir, { recursive: true })
        const injected = html
          .replace(/<html([^>]*)>/i, `<html$1 data-prerender-route="${clean}">`)
          .replace(/<\/head>/i, `  <meta name="prerender-route" content="${clean}" />\n  </head>`)
        fs.writeFileSync(path.join(targetDir, 'index.html'), injected, 'utf8')
        written += 1
      }
      console.log(`[prerender] wrote ${written} route shell(s) under ${outDir}`)
    },
  }
}

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const dev = mode !== 'production'
  const themeName = env.VITE_THEME || 'ystheme'
  const themeDir = resolveTheme(themeName)
  const prerenderRoutes = (env.VITE_PRERENDER_ROUTES || '/,/s,/member/login,/pay/result')
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean)
  const outDir = path.join(projectRoot, 'public', 'site')

  return {
    // Dev: `/` for Vite HMR. Prod: `/site/` so CSS/JS resolve to public/site/assets
    // (SPA HTML still served at domain root via rewrite; router base stays `/`).
    base: dev ? '/' : '/site/',
    plugins: [
      vue(),
      // createBootstrap() only wires services; components need this resolver
      Components({
        resolvers: [BootstrapVueNextResolver()],
        dts: false,
      }),
      prerenderKeyPages(prerenderRoutes, outDir),
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        // Theme Vue/assets (optional). Styles enter via src/styles/site.scss + loadPaths.
        '@theme': themeDir,
        vue: path.join(nm, 'vue'),
        'vue-router': path.join(nm, 'vue-router'),
        pinia: path.join(nm, 'pinia'),
        axios: path.join(nm, 'axios'),
        bootstrap: path.join(nm, 'bootstrap'),
        // Do NOT alias bootstrap-vue-next to package root — breaks
        // `bootstrap-vue-next/components/*` package exports used by the resolver.
      },
    },
    css: {
      preprocessorOptions: {
        scss: {
          // Resolve `@import "theme"` / `"variables"` from the active theme pack
          // without exposing Windows absolute /@fs/D:/… URLs to the browser.
          loadPaths: [path.join(themeDir, 'styles')],
          silenceDeprecations: ['import', 'legacy-js-api'],
        },
      },
    },
    server: {
      host: true, // 0.0.0.0 — allow LAN access
      port: 5174,
      // Avoid sticky 304 of earlier broken /@fs CSS modules during local debug
      headers: {
        'Cache-Control': 'no-store',
      },
      fs: {
        allow: [siteRoot, projectRoot],
      },
      proxy: {
        '/api': {
          target: env.VITE_PROXY_TARGET || 'http://127.0.0.1:8080',
          changeOrigin: true,
        },
        // 会员注册 / 自定义表单图片验证码（Captcha/captchaImage）
        '/captcha': {
          target: env.VITE_PROXY_TARGET || 'http://127.0.0.1:8080',
          changeOrigin: true,
        },
        '/addons': {
          target: env.VITE_PROXY_TARGET || 'http://127.0.0.1:8080',
          changeOrigin: true,
        },
        '/storage': {
          target: env.VITE_PROXY_TARGET || 'http://127.0.0.1:8080',
          changeOrigin: true,
        },
      },
    },
    build: {
      outDir,
      emptyOutDir: true,
    },
  }
})
