<template>
  <div class="archives-page">
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
          <nav v-if="isNewsLayout && tabChannels.length" class="channel-tabs" aria-label="子栏目">
            <RouterLink
              v-for="tab in tabChannels"
              :key="tab.id"
              :to="channelPath(tab)"
              class="channel-tabs__item"
              :class="{ active: isTabActive(tab) }"
            >
              {{ tab.name }}
            </RouterLink>
          </nav>
          <nav v-else-if="isProductLayout" class="channel-tabs archives-anchor-tabs" aria-label="详情导航">
            <a
              v-for="sec in productSections"
              :key="sec.id"
              href="#"
              class="channel-tabs__item"
              :class="{ active: activeSection === sec.id }"
              @click.prevent="scrollToSection(sec.id)"
            >
              {{ sec.label }}
            </a>
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
            <template v-if="sectionRoot && sectionRoot.id !== channel?.id">
              <span class="mx-1">›</span>
              <RouterLink :to="channelPath(sectionRoot)">{{ sectionRoot.name }}</RouterLink>
            </template>
            <span class="mx-1">›</span>
            <a v-if="channel" :href="channel.url || channelPath(channel)">{{ channel.name }}</a>
            <template v-if="isProductLayout && archives?.title">
              <span class="mx-1">›</span>
              <span>{{ archives.title }}</span>
            </template>
          </nav>
        </div>
      </div>

      <div class="archives-body">
        <BContainer :key="route.params.diyname" class="py-4 py-lg-5">
          <!-- 新闻详情 -->
          <template v-if="isNewsLayout">
            <BRow class="g-4">
              <BCol lg="8">
                <article v-motion="pageEnter" class="archives-article">
                  <h1 class="archives-article__title">{{ archives?.title }}</h1>
                  <div class="archives-article__meta">
                    <span v-if="archives?.publishTime" class="archives-article__meta-item">
                      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true">
                        <path
                          fill="currentColor"
                          d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Zm12 8H5v10h14V10Zm0-2V6H5v2h14Z"
                        />
                      </svg>
                      发布时间: {{ formatDate(archives.publishTime) }}
                    </span>
                    <span class="archives-article__meta-item">
                      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true">
                        <path
                          fill="currentColor"
                          d="M12 5c5.5 0 9.5 5.2 9.9 5.8a1 1 0 0 1 0 1.2C21.5 12.6 17.5 18 12 18S2.5 12.6 2.1 12a1 1 0 0 1 0-1.2C2.5 10.2 6.5 5 12 5Zm0 2c-3.7 0-6.8 3.3-7.8 4.9C5.2 13.5 8.3 17 12 17s6.8-3.5 7.8-5.1C18.8 10.3 15.7 7 12 7Zm0 2.5A2.5 2.5 0 1 1 12 14a2.5 2.5 0 0 1 0-4.5Z"
                        />
                      </svg>
                      浏览数: {{ archives?.views ?? 0 }}
                    </span>
                    <span class="archives-article__share">
                      分享至:
                      <button type="button" class="archives-share-btn archives-share-btn--wechat" title="分享" @click="onShare">
                        <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                          <path fill="currentColor" d="M9.5 4C5.9 4 3 6.5 3 9.6c0 1.8 1 3.4 2.6 4.4L5 16.5l2.4-1.2c.7.2 1.4.3 2.1.3.2 0 .4 0 .6 0A4.8 4.8 0 0 1 9.5 14c-3 0-5.5-2.2-5.5-5S6.5 4 9.5 4Zm7.2 6c-2.8 0-5 2-5 4.5s2.2 4.5 5 4.5c.5 0 1-.1 1.5-.2l1.8.9-.4-1.6c1.2-.8 2-2.1 2-3.6 0-2.5-2.2-4.5-5-4.5Z" />
                        </svg>
                      </button>
                      <button type="button" class="archives-share-btn archives-share-btn--weibo" title="分享" @click="onShare">
                        <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                          <path fill="currentColor" d="M10.2 17.8c-3.1.6-5.8-1.1-6-3.7-.3-2.6 2.1-5.2 5.2-5.8 3.1-.6 5.8 1.1 6 3.7.3 2.6-2.1 5.2-5.2 5.8Zm8.3-7.5c-.3-.1-.5-.4-.4-.7.2-.6.2-1.1 0-1.5-.4-.8-1.5-1-2.8-.6-.3.1-.6 0-.7-.3-.1-.3 0-.6.3-.7 1.8-.6 3.4-.3 4.1 1 .4.8.4 1.7.1 2.6-.1.3-.4.4-.6.2ZM17.2 8c-.2 0-.3-.2-.3-.4 0-.3.1-.7 0-1-.2-.5-.8-.7-1.5-.5-.2.1-.4 0-.5-.2s0-.4.2-.5c1.1-.3 2.1 0 2.5.8.2.5.2 1 0 1.5 0 .2-.2.3-.4.3Z" />
                        </svg>
                      </button>
                      <button type="button" class="archives-share-btn archives-share-btn--link" title="复制链接" @click="onShare">
                        <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                          <path fill="currentColor" d="M10.6 13.4a1 1 0 0 1 0-1.4l2.8-2.8a1 1 0 1 1 1.4 1.4l-2.8 2.8a1 1 0 0 1-1.4 0Zm-1.2 1.2a3 3 0 0 1 0-4.2l.7-.7a1 1 0 1 1 1.4 1.4l-.7.7a1 1 0 0 0 0 1.4 1 1 0 0 0 1.4 0l.7-.7a1 1 0 1 1 1.4 1.4l-.7.7a3 3 0 0 1-4.2 0Zm7.8-7.8a3 3 0 0 1 0 4.2l-.7.7a1 1 0 1 1-1.4-1.4l.7-.7a1 1 0 0 0 0-1.4 1 1 0 0 0-1.4 0l-.7.7a1 1 0 1 1-1.4-1.4l.7-.7a3 3 0 0 1 4.2 0Z" />
                        </svg>
                      </button>
                    </span>
                  </div>
                  <div class="archives-article__content content-html" v-html="archives?.content"></div>
                  <div class="archives-article__pager">
                    <RouterLink v-if="prev" :to="prev.url || `/a/${prev.diyname || prev.id}`">
                      ← {{ prev.title }}
                    </RouterLink>
                    <span v-else></span>
                    <RouterLink v-if="next" :to="next.url || `/a/${next.diyname || next.id}`">
                      {{ next.title }} →
                    </RouterLink>
                  </div>
                </article>
              </BCol>
              <BCol lg="4">
                <aside v-motion="pageEnterStagger(1)" class="channel-latest">
                  <h2 class="channel-latest__title">
                    <span class="channel-latest__bar" aria-hidden="true" />
                    {{ latestTitle }}
                  </h2>
                  <div class="channel-latest__list">
                    <RouterLink
                      v-for="(item, i) in latestItems"
                      :key="item.id"
                      v-motion="pageEnterStagger(i + 2)"
                      :to="item.url || `/a/${item.diyname || item.id}`"
                      class="channel-latest__card"
                    >
                      <div class="channel-latest__media">
                        <img
                          :src="item.image || '/addons/yscms/theme/ystheme/img/noimage.svg'"
                          :alt="item.title"
                          loading="lazy"
                        />
                      </div>
                      <div class="channel-latest__body">
                        <h3 class="channel-latest__name">{{ item.title }}</h3>
                        <div class="channel-latest__foot">
                          <span>{{ formatDate(item.publishTime || item.createTime) }}</span>
                          <span>查看更多 &gt;</span>
                        </div>
                      </div>
                    </RouterLink>
                  </div>
                </aside>
              </BCol>
            </BRow>
          </template>

          <!-- 产品 / 下载详情 -->
          <template v-else>
            <div id="sec-overview" v-motion="pageEnter" class="archives-product-hero">
              <div class="archives-gallery">
                <div class="archives-gallery__main">
                  <img :src="currentGalleryImage" :alt="archives?.title" />
                </div>
                <div v-if="galleryImages.length > 1" class="archives-gallery__nav">
                  <button type="button" class="archives-gallery__btn" aria-label="上一张" @click="galleryPrev">
                    ‹
                  </button>
                  <button type="button" class="archives-gallery__btn" aria-label="下一张" @click="galleryNext">
                    ›
                  </button>
                </div>
              </div>
              <div class="archives-product-info">
                <h1 class="archives-product-info__title">{{ archives?.title }}</h1>
                <p v-if="productHighlight" class="archives-product-info__highlight">{{ productHighlight }}</p>
                <p v-if="archives?.description" class="archives-product-info__desc">{{ archives.description }}</p>
                <div class="archives-product-info__actions">
                  <a
                    v-if="ctaHref"
                    class="btn btn-primary archives-product-info__cta"
                    :href="ctaHref"
                    :target="ctaExternal ? '_blank' : undefined"
                    :rel="ctaExternal ? 'noopener noreferrer' : undefined"
                  >
                    {{ ctaLabel }}
                  </a>
                  <button
                    v-else-if="downloadList.length"
                    type="button"
                    class="btn btn-primary archives-product-info__cta"
                    @click="scrollToSection('sec-download')"
                  >
                    {{ ctaLabel }}
                  </button>
                </div>
              </div>
            </div>

            <div v-if="archives?.content" id="sec-feature" class="archives-section">
              <h2 class="archives-section__title">
                <span class="archives-section__bar" aria-hidden="true" />
                {{ isDownloadModel ? '应用介绍' : '产品特色' }}
              </h2>
              <div class="archives-section__body content-html" v-html="archives.content"></div>
            </div>

            <div v-if="specRows.length" id="sec-specs" class="archives-section">
              <h2 class="archives-section__title">
                <span class="archives-section__bar" aria-hidden="true" />
                规格参数
              </h2>
              <div class="archives-specs">
                <table>
                  <tbody>
                    <tr v-for="(row, i) in specRows" :key="i">
                      <th>{{ row.label }}</th>
                      <td>{{ row.value }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div v-if="downloadList.length" id="sec-download" class="archives-section">
              <h2 class="archives-section__title">
                <span class="archives-section__bar" aria-hidden="true" />
                相关下载
              </h2>
              <ul class="archives-downloads">
                <li v-for="(item, i) in downloadList" :key="i">
                  <a :href="item.url" target="_blank" rel="noopener noreferrer">
                    <span class="archives-downloads__icon" aria-hidden="true">↓</span>
                    <span>{{ item.title || item.name || '下载文件' }}</span>
                    <span v-if="item.password" class="archives-downloads__pwd">提取码: {{ item.password }}</span>
                  </a>
                </li>
              </ul>
            </div>
          </template>

          <CommentBox
            v-motion="pageEnter"
            class="archives-comments"
            type="archives"
            :aid="archives?.id"
            :enabled="commentEnabled"
          />
        </BContainer>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchArchives } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { pageEnter, pageEnterStagger } from '@/motion/presets'
import CommentBox from '@/components/CommentBox.vue'

const route = useRoute()
const site = useSiteStore()
const loading = ref(true)
const error = ref('')
const archives = ref(null)
const channel = ref(null)
const model = ref(null)
const templateName = ref('')
const prev = ref(null)
const next = ref(null)
const sideChannels = ref([])
const sideLatest = ref([])
const galleryIndex = ref(0)
const activeSection = ref('sec-overview')

const isProductLayout = computed(() => {
  const mid = Number(
    archives.value?.modelId ?? archives.value?.model_id ?? model.value?.id ?? channel.value?.modelId ?? 0,
  )
  const tpl = String(templateName.value || channel.value?.showtpl || '')
  if (mid === 2 || mid === 3) return true
  return /product|download/i.test(tpl)
})

const isNewsLayout = computed(() => !isProductLayout.value)

const isDownloadModel = computed(() => {
  const mid = Number(archives.value?.modelId ?? model.value?.id ?? 0)
  return mid === 3 || /download/i.test(String(templateName.value || ''))
})

const commentEnabled = computed(() => String(archives.value?.iscomment ?? '1') === '1')

function findNavNode(nodes, id) {
  const nid = Number(id)
  for (const n of nodes || []) {
    if (Number(n.id) === nid) return n
    const found = findNavNode(n.children, nid)
    if (found) return found
  }
  return null
}

function findRootFor(nodes, id) {
  const nid = Number(id)
  for (const n of nodes || []) {
    if (Number(n.id) === nid) return n
    if (containsId(n, nid)) return n
  }
  return null
}

function containsId(node, id) {
  if (Number(node.id) === Number(id)) return true
  return (node.children || []).some((c) => containsId(c, id))
}

const sectionRoot = computed(() => {
  const id = channel.value?.id
  if (!id) return null
  return findRootFor(site.nav, id) || channel.value
})

const heroTitle = computed(() => sectionRoot.value?.name || channel.value?.name || '')

const heroStyle = computed(() => {
  const img = channel.value?.image || sectionRoot.value?.image
  if (img) {
    return {
      backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.65)), url(${img})`,
    }
  }
  return {}
})

const tabChannels = computed(() => {
  const root = sectionRoot.value
  if (root?.children?.length) {
    return root.children.map((ch) => ({
      ...ch,
      isActive: Number(ch.id) === Number(channel.value?.id) ? 1 : 0,
    }))
  }
  return sideChannels.value || []
})

const latestTitle = computed(() => {
  const name = String(channel.value?.name || sectionRoot.value?.name || '')
  if (/方案/.test(name)) return '最新方案'
  if (/新闻|资讯/.test(name)) return '最新新闻'
  return '最新文章'
})

const latestItems = computed(() => {
  const fromApi = Array.isArray(sideLatest.value) ? sideLatest.value : []
  if (fromApi.length) return fromApi.slice(0, 4)
  const fromSite = site.side?.recommendNews || site.side?.latest || []
  return (Array.isArray(fromSite) ? fromSite : []).slice(0, 4)
})

function splitCsvImages(raw) {
  if (Array.isArray(raw)) {
    return raw.map((x) => String(x || '').trim()).filter(Boolean)
  }
  const s = String(raw || '').trim()
  if (!s) return []
  return s.split(',').map((x) => x.trim()).filter(Boolean)
}

const galleryImages = computed(() => {
  const a = archives.value || {}
  const fromProduct = splitCsvImages(a.productdata)
  const fromImages = Array.isArray(a.imagesList)
    ? a.imagesList
    : splitCsvImages(a.images)
  const fromShots = splitCsvImages(a.screenshots)
  const list = [...fromProduct, ...fromImages, ...fromShots]
  if (a.image) list.unshift(a.image)
  return [...new Set(list.filter(Boolean))]
})

const currentGalleryImage = computed(() => {
  const list = galleryImages.value
  if (!list.length) return '/addons/yscms/theme/ystheme/img/noimage.svg'
  const i = ((galleryIndex.value % list.length) + list.length) % list.length
  return list[i]
})

function galleryPrev() {
  const n = galleryImages.value.length
  if (n < 2) return
  galleryIndex.value = (galleryIndex.value - 1 + n) % n
}

function galleryNext() {
  const n = galleryImages.value.length
  if (n < 2) return
  galleryIndex.value = (galleryIndex.value + 1) % n
}

const productHighlight = computed(() => {
  const kw = String(archives.value?.keywords || '').trim()
  if (!kw) return ''
  return kw.split(/[,，|｜]/).map((s) => s.trim()).filter(Boolean).join(' | ')
})

const downloadList = computed(() => {
  const a = archives.value || {}
  const list = a.downloadurlList || a.downloadurl_list || []
  return Array.isArray(list) ? list.filter((x) => x?.url) : []
})

const specRows = computed(() => {
  if (!isDownloadModel.value) return []
  const a = archives.value || {}
  const rows = [
    { label: '操作系统', value: a.os },
    { label: '版本', value: a.version },
    { label: '文件大小', value: a.filesize },
    { label: '语言', value: a.language },
    { label: '下载次数', value: a.downloads != null && a.downloads !== '' ? String(a.downloads) : '' },
  ]
  return rows.filter((r) => String(r.value || '').trim() !== '')
})

const productSections = computed(() => {
  const secs = [{ id: 'sec-overview', label: '产品概览' }]
  if (archives.value?.content) {
    secs.push({ id: 'sec-feature', label: isDownloadModel.value ? '应用介绍' : '产品特色' })
  }
  if (specRows.value.length) secs.push({ id: 'sec-specs', label: '规格参数' })
  if (downloadList.value.length) secs.push({ id: 'sec-download', label: '相关下载' })
  return secs
})

const ctaLabel = computed(() => (isDownloadModel.value ? '立即下载' : '立即购买'))

const ctaHref = computed(() => {
  const link = String(archives.value?.outlink || '').trim()
  return link || ''
})

const ctaExternal = computed(() => /^https?:\/\//i.test(ctaHref.value))

function channelPath(ch) {
  const url = ch?.url || ''
  if (typeof url === 'string' && url.startsWith('/')) return url
  const key = ch?.diyname || ch?.id
  return key ? `/c/${key}` : '#'
}

function isTabActive(tab) {
  return !!(tab.isActive || tab.is_active || Number(tab.id) === Number(channel.value?.id))
}

function formatDate(v) {
  if (!v) return ''
  return String(v).slice(0, 10)
}

function scrollToSection(id) {
  activeSection.value = id
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

async function onShare() {
  const url = window.location.href
  try {
    if (navigator.share) {
      await navigator.share({ title: archives.value?.title || document.title, url })
      return
    }
  } catch {
    /* fall through */
  }
  try {
    await navigator.clipboard?.writeText(url)
  } catch {
    /* ignore */
  }
}

async function load() {
  loading.value = true
  error.value = ''
  galleryIndex.value = 0
  activeSection.value = 'sec-overview'
  try {
    const res = await fetchArchives(route.params.diyname)
    const data = res.data || {}
    if (data.redirect) {
      window.location.href = data.redirect
      return
    }
    site.applySeo(data.seo || {})
    archives.value = data.archives
    channel.value = data.channel
    model.value = data.model
    templateName.value = data.template || ''
    prev.value = data.prev
    next.value = data.next
    sideChannels.value = data.sideChannels || []
    sideLatest.value = data.sideLatest || data.side_latest || []
  } catch (e) {
    error.value = e.message || '加载失败'
  } finally {
    loading.value = false
  }
}

watch(() => route.params.diyname, load)
onMounted(load)
</script>
