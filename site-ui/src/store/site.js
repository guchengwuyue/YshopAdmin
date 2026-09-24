import { defineStore } from 'pinia'
import { fetchSite } from '@/api/cms'

export const useSiteStore = defineStore('site', {
  state: () => ({
    loaded: false,
    config: {},
    theme: 'ystheme',
    themes: [],
    nav: [],
    side: {},
    member: null,
  }),
  getters: {
    sitename: (s) => s.config.sitename || 'Yshop CMS',
    sitelogo: (s) => s.config.sitelogo || '',
  },
  actions: {
    async loadSite(activeId = 0) {
      try {
        const res = await fetchSite({ activeId })
        const data = res.data || {}
        this.config = data.config || {}
        this.theme = data.theme || 'ystheme'
        this.themes = data.themes || []
        this.nav = data.nav || []
        this.side = data.side || {}
        this.member = data.member || null
        this.loaded = true
      } catch (e) {
        console.warn('[site] load failed', e)
        this.loaded = true
      }
    },
    applySeo(seo = {}) {
      const title = seo.title || this.sitename
      document.title = title
      const setMeta = (name, content) => {
        if (content == null || content === '') return
        let el = document.querySelector(`meta[name="${name}"]`)
        if (!el) {
          el = document.createElement('meta')
          el.setAttribute('name', name)
          document.head.appendChild(el)
        }
        el.setAttribute('content', String(content))
      }
      setMeta('keywords', seo.keywords || this.config.keywords || '')
      setMeta('description', seo.description || this.config.description || '')
    },
  },
})
