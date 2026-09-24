<template>
  <div class="page-single">
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <div v-else-if="error" class="alert alert-warning m-4">{{ error }}</div>
    <template v-else>
      <section v-motion="pageEnter" class="channel-hero" :style="heroStyle">
        <div class="channel-hero__inner">
          <h1 class="channel-hero__title">{{ heroTitle }}</h1>
          <span class="channel-hero__line" aria-hidden="true" />
        </div>
      </section>

      <div v-motion="pageEnter" class="channel-subnav">
        <div class="container channel-subnav__inner">
          <nav v-if="tabPages.length" class="channel-tabs" aria-label="相关单页">
            <RouterLink
              v-for="tab in tabPages"
              :key="tab.id || tab.url"
              :to="pagePath(tab)"
              class="channel-tabs__item"
              :class="{ active: isTabActive(tab) }"
            >
              {{ tab.name || tab.title }}
            </RouterLink>
          </nav>
          <nav class="channel-breadcrumb" aria-label="面包屑">
            <RouterLink to="/" class="channel-breadcrumb__home" aria-label="首页">
              <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                <path
                  fill="currentColor"
                  d="M12 3.2 3.5 10.2c-.3.25-.2.7.2.7H6v8.3c0 .4.3.8.8.8h3.4c.4 0 .8-.4.8-.8V14h2v4.5c0 .4.3.8.8.8h3.4c.4 0 .8-.4.8-.8V10.9h2.3c.4 0 .5-.45.2-.7L12 3.2z"
                />
              </svg>
              <span>首页</span>
            </RouterLink>
            <template v-if="navParent">
              <span class="mx-1">›</span>
              <RouterLink v-if="parentPath" :to="parentPath">{{ navParent.name }}</RouterLink>
              <span v-else>{{ navParent.name }}</span>
            </template>
            <span class="mx-1">›</span>
            <span>{{ pageData?.title }}</span>
          </nav>
        </div>
      </div>

      <div class="page-single__body">
        <BContainer v-motion="pageEnter" class="py-4 py-lg-5">
          <div class="page-single__content content-html" v-html="pageData?.content"></div>
        </BContainer>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchPage } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { pageEnter } from '@/motion/presets'

const route = useRoute()
const site = useSiteStore()
const loading = ref(true)
const error = ref('')
const pageData = ref(null)
const sideChannels = ref([])

function normalizePath(url) {
  if (!url || typeof url !== 'string') return ''
  const raw = url.trim()
  if (!raw || raw === '#' || raw.startsWith('javascript:')) return ''
  try {
    if (/^(https?:)?\/\//i.test(raw)) {
      const u = new URL(raw, window.location.origin)
      if (u.origin !== window.location.origin) return ''
      return u.pathname.replace(/\/+$/, '') || '/'
    }
  } catch {
    return ''
  }
  const path = raw.split(/[?#]/)[0]
  if (!path.startsWith('/')) return `/${path}`.replace(/\/+$/, '') || '/'
  return path.replace(/\/+$/, '') || '/'
}

function pageMatches(node, path) {
  const url = normalizePath(node?.url)
  if (!url || url === '/') return false
  return path === url || path.startsWith(`${url}/`)
}

function findNavContext(nodes, path, parent = null) {
  for (const n of nodes || []) {
    if (pageMatches(n, path)) {
      return { node: n, parent }
    }
    const found = findNavContext(n.children, path, n)
    if (found) return found
  }
  return null
}

const currentPath = computed(() => normalizePath(route.path) || '/')

const navContext = computed(() => findNavContext(site.nav, currentPath.value))

/** 栏目根：子页的父级，或自身带 children 的节点 */
const sectionRoot = computed(() => {
  const ctx = navContext.value
  if (!ctx) return null
  if (ctx.parent?.children?.length) return ctx.parent
  if (ctx.node?.children?.length) return ctx.node
  return null
})

/** 面包屑中间级：仅当前为子页时显示 */
const navParent = computed(() => {
  const ctx = navContext.value
  if (ctx?.parent?.children?.length) return ctx.parent
  return null
})

const parentPath = computed(() => {
  const url = normalizePath(navParent.value?.url)
  return url && url !== '/' ? url : ''
})

const heroTitle = computed(() => {
  if (sectionRoot.value?.name) return sectionRoot.value.name
  return pageData.value?.title || ''
})

const heroStyle = computed(() => {
  const img = pageData.value?.image || sectionRoot.value?.image
  if (img) {
    return {
      backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.65)), url(${img})`,
    }
  }
  return {}
})

const tabPages = computed(() => {
  const root = sectionRoot.value
  if (root?.children?.length) {
    return root.children.map((ch) => ({
      id: ch.id,
      name: ch.name,
      title: ch.name,
      url: ch.url,
      diyname: ch.diyname,
      isActive: pageMatches(ch, currentPath.value) ? 1 : 0,
    }))
  }
  return sideChannels.value || []
})

function pagePath(ch) {
  const url = ch?.url || ''
  if (typeof url === 'string' && url.startsWith('/')) return url
  const key = ch?.diyname || ch?.id
  return key ? `/p/${key}` : '#'
}

function isTabActive(tab) {
  if (tab.isActive || tab.is_active) return true
  return pageMatches(tab, currentPath.value)
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetchPage(route.params.diyname)
    const data = res.data || {}
    site.applySeo(data.seo || {})
    pageData.value = data.page
    sideChannels.value = data.sideChannels || []
  } catch (e) {
    error.value = e.message || '加载失败'
  } finally {
    loading.value = false
  }
}

watch(() => route.params.diyname, load)
onMounted(load)
</script>
