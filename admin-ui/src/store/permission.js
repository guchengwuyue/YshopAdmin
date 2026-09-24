import { defineStore } from 'pinia'
import { getRouters } from '@/api/login'
import { collectViews, loadView } from '@/utils/view'

export const usePermissionStore = defineStore('permission', {
  state: () => ({
    routers: [],
    added: false,
  }),
  actions: {
    async generate(router) {
      const res = await getRouters()
      this.routers = res.data || []
      collectViews(this.routers).forEach((node) => {
        if (router.hasRoute(node.name)) {
          return
        }
        router.addRoute('Layout', {
          path: String(node.path || '').replace(/^\//, ''),
          name: node.name,
          component: loadView(node.component),
          meta: node.meta || {},
        })
      })
      ;[
        { path: 'yscms/diydata', name: 'YscmsDiydata', component: 'yscms/diydata/index', title: '表单数据' },
        { path: 'yscms/autolink', name: 'YscmsAutolink', component: 'yscms/autolink/index', title: '自动内链' },
      ].forEach((item) => {
        const full = '/' + item.path
        if (router.getRoutes().some((route) => route.path === full || route.path === item.path)) {
          return
        }
        router.addRoute('Layout', {
          path: item.path,
          name: item.name,
          component: loadView(item.component),
          meta: { title: item.title },
        })
      })
      if (!router.hasRoute('NotFound')) {
        router.addRoute({
          path: '/:pathMatch(.*)*',
          name: 'NotFound',
          component: () => import('@/views/placeholder/index.vue'),
          meta: { title: '未找到' },
        })
      }
      this.added = true
    },
  },
})
