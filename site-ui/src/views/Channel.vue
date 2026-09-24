<template>
  <div class="channel-page">
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <div v-else-if="error" class="alert alert-warning m-4">{{ error }}</div>
    <template v-else>
      <section v-motion="pageEnter" class="channel-hero" :style="heroStyle">
        <div class="channel-hero__inner">
          <h1 class="channel-hero__title">{{ channel?.name }}</h1>
          <span class="channel-hero__line" aria-hidden="true" />
        </div>
      </section>

      <div v-motion="pageEnter" class="channel-subnav">
        <div class="container channel-subnav__inner">
          <nav v-if="tabChannels.length" class="channel-tabs" aria-label="子栏目">
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
          <nav class="channel-breadcrumb" aria-label="面包屑">
            <RouterLink to="/">首页</RouterLink>
            <span class="mx-1">›</span>
            <span>{{ channel?.name }}</span>
          </nav>
        </div>
      </div>

      <BContainer :key="route.params.diyname" class="channel-body py-4 py-lg-5">
        <!-- 新闻布局 -->
        <BRow v-if="isNewsLayout" class="g-4">
          <BCol lg="8">
            <div class="channel-news-list">
              <div
                v-for="(item, i) in list"
                :key="item.id"
                v-motion="pageEnterStagger(i)"
                class="channel-news-list__item"
              >
                <ArchiveCard :item="item" variant="news" />
              </div>
            </div>
            <div v-if="!list.length" class="text-body-secondary py-4">暂无内容</div>
            <Pager
              class="channel-pager"
              :page="page"
              :page-size="pageSize"
              :total="total"
              @change="load"
            />
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

        <!-- 产品 / 下载布局 -->
        <BRow v-else class="g-4">
          <BCol lg="3" md="4">
            <aside v-motion="pageEnter" class="channel-cate">
              <div class="channel-cate__hd">
                <h2 class="channel-cate__title">{{ cateTitle }}</h2>
                <span class="channel-cate__menu" aria-hidden="true">
                  <i /><i /><i />
                </span>
              </div>
              <ul class="channel-cate__tree">
                <ChannelCateNode
                  v-for="node in cateTree"
                  :key="node.id"
                  :node="node"
                  :active-id="channel?.id"
                />
              </ul>
            </aside>
          </BCol>
          <BCol lg="9" md="8">
            <BRow class="g-4">
              <BCol
                v-for="(item, i) in list"
                :key="item.id"
                v-motion="pageEnterStagger(i)"
                sm="6"
                lg="4"
              >
                <ArchiveCard :item="item" variant="product" />
              </BCol>
            </BRow>
            <div v-if="!list.length" class="text-body-secondary py-4">暂无内容</div>
            <Pager
              class="channel-pager"
              :page="page"
              :page-size="pageSize"
              :total="total"
              @change="load"
            />
          </BCol>
        </BRow>
      </BContainer>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchChannel } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { pageEnter, pageEnterStagger } from '@/motion/presets'
import ArchiveCard from '@/components/ArchiveCard.vue'
import ChannelCateNode from '@/components/ChannelCateNode.vue'
import Pager from '@/components/Pager.vue'

const route = useRoute()
const site = useSiteStore()
const loading = ref(true)
const error = ref('')
const channel = ref(null)
const templateName = ref('')
const list = ref([])
const sideChannels = ref([])
const sideLatest = ref([])
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

const isProductLayout = computed(() => {
  const mid = Number(channel.value?.modelId ?? channel.value?.model_id ?? 0)
  const tpl = String(templateName.value || channel.value?.listtpl || channel.value?.channeltpl || '')
  if (mid === 2 || mid === 3) return true
  return /product|download/i.test(tpl)
})

const isNewsLayout = computed(() => !isProductLayout.value)

const latestTitle = computed(() => {
  const name = String(channel.value?.name || '')
  if (/方案/.test(name)) return '最新方案'
  if (/新闻|资讯/.test(name)) return '最新资讯'
  return '最新文章'
})

/** 右侧最新：优先接口 sideLatest，否则回退当前列表 / 全站最新 */
const latestItems = computed(() => {
  const fromApi = Array.isArray(sideLatest.value) ? sideLatest.value : []
  if (fromApi.length) return fromApi.slice(0, 4)
  const fromList = Array.isArray(list.value) ? list.value : []
  if (fromList.length) return fromList.slice(0, 4)
  const fromSite =
    site.side?.recommendNews ||
    site.side?.latest ||
    []
  return (Array.isArray(fromSite) ? fromSite : []).slice(0, 4)
})

const cateTitle = computed(() => {
  const mid = Number(channel.value?.modelId ?? channel.value?.model_id ?? 0)
  if (mid === 3 || /download/i.test(String(templateName.value || ''))) return '下载分类'
  return '产品分类'
})

const heroStyle = computed(() => {
  const img = channel.value?.image
  if (img) {
    return {
      backgroundImage: `linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.65)), url(${img})`,
    }
  }
  return {}
})

const tabChannels = computed(() => sideChannels.value || [])

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

const cateTree = computed(() => {
  const id = channel.value?.id
  const navNode = findNavNode(site.nav, id)
  if (navNode?.children?.length) return navNode.children

  const root = findRootFor(site.nav, id)
  if (root?.children?.length) return root.children

  return (sideChannels.value || []).map((ch) => ({
    ...ch,
    children: ch.children || [],
  }))
})

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

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const res = await fetchChannel(route.params.diyname, { page: p })
    const data = res.data || {}
    if (data.redirect) {
      const r = String(data.redirect)
      if (r.startsWith('/') && !r.startsWith('//')) {
        window.location.assign(r)
      } else {
        window.location.href = r
      }
      return
    }
    site.applySeo(data.seo || {})
    channel.value = data.channel
    templateName.value = data.template || ''
    list.value = data.list || []
    sideChannels.value = data.sideChannels || []
    sideLatest.value = data.sideLatest || data.side_latest || []
    page.value = data.page || 1
    pageSize.value = data.pageSize || 10
    total.value = data.total || 0
  } catch (e) {
    error.value = e.message || '加载失败'
  } finally {
    loading.value = false
  }
}

watch(
  () => route.params.diyname,
  () => load(1),
)
onMounted(() => load(Number(route.query.page) || 1))
</script>
