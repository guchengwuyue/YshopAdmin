import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import path from 'node:path'

const adminRoot = fileURLToPath(new URL('.', import.meta.url))
const projectRoot = fileURLToPath(new URL('..', import.meta.url))
const nm = path.join(adminRoot, 'node_modules')

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const dev = mode !== 'production'
  return {
    base: dev ? '/' : '/admin/',
    plugins: [vue()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        // addon web/admin lives outside admin-ui; pin deps to this package
        vue: path.join(nm, 'vue'),
        'vue-router': path.join(nm, 'vue-router'),
        pinia: path.join(nm, 'pinia'),
        axios: path.join(nm, 'axios'),
        'ant-design-vue': path.join(nm, 'ant-design-vue'),
        '@ant-design/icons-vue': path.join(nm, '@ant-design/icons-vue'),
        '@wangeditor/editor': path.join(nm, '@wangeditor/editor'),
        '@wangeditor/editor-for-vue': path.join(nm, '@wangeditor/editor-for-vue'),
        echarts: path.join(nm, 'echarts'),
        dayjs: path.join(nm, 'dayjs'),
      },
    },
    server: {
      port: 5173,
      fs: {
        allow: [adminRoot, projectRoot],
      },
      proxy: {
        '/yshopadmin': {
          target: env.VITE_PROXY_TARGET || 'http://127.0.0.1:8080',
          changeOrigin: true,
        },
      },
    },
    build: {
      outDir: 'dist',
      emptyOutDir: true,
    },
  }
})