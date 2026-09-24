<template>
  <div class="diyform-page">
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <div v-else-if="error" class="alert alert-warning m-4">{{ error }}</div>
    <template v-else>
      <section v-motion="pageEnter" class="diyform-hero">
        <div class="container diyform-hero__inner">
          <h1 class="diyform-hero__title">{{ form?.title }}</h1>
          <nav class="diyform-hero__crumb" aria-label="面包屑">
            <RouterLink to="/">首页</RouterLink>
            <span class="mx-1">/</span>
            <span>{{ form?.title }}</span>
          </nav>
        </div>
      </section>

      <div class="diyform-body">
        <BContainer v-motion="pageEnter" class="py-4 py-lg-5">
          <div class="diyform-card">
            <div class="diyform-card__hd">
              <h2 class="diyform-card__title">{{ form?.title }}</h2>
              <RouterLink class="btn btn-primary diyform-card__btn" :to="postPath">填写表单</RouterLink>
            </div>
            <ul v-if="list.length" class="diyform-list">
              <li
                v-for="(item, i) in list"
                :key="item.id"
                v-motion="pageEnterStagger(i)"
                class="diyform-list__item"
              >
                <div class="diyform-list__name">{{ item.displayTitle || item.display_title || `#${item.id}` }}</div>
                <div class="diyform-list__meta">{{ item.humanDate || item.human_date }}</div>
              </li>
            </ul>
            <div v-else class="text-body-secondary py-4 px-3">暂无留言</div>
            <Pager
              class="diyform-pager"
              :page="page"
              :page-size="pageSize"
              :total="total"
              @change="load"
            />
          </div>
        </BContainer>
      </div>

      <RouterLink class="diyform-fab" :to="postPath" aria-label="填写表单" title="填写表单">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
          <path
            fill="currentColor"
            d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.2L4 17.2V4h16v12zM7 9h10v2H7V9zm0-3h10v2H7V6zm0 6h7v2H7v-2z"
          />
        </svg>
      </RouterLink>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchDiyform } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { pageEnter, pageEnterStagger } from '@/motion/presets'
import Pager from '@/components/Pager.vue'

const route = useRoute()
const site = useSiteStore()
const loading = ref(true)
const error = ref('')
const form = ref(null)
const list = ref([])
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

const diyname = computed(() => String(route.params.diyname || '').replace(/\.html$/i, ''))

const postPath = computed(() => {
  const key = diyname.value || form.value?.diyname || form.value?.id
  return key ? `/d/${key}/post` : '#'
})

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const res = await fetchDiyform(diyname.value, { page: p })
    const data = res.data || {}
    site.applySeo(data.seo || {})
    form.value = data.diyform
    list.value = data.list || []
    page.value = data.page || 1
    pageSize.value = data.pageSize || 10
    total.value = data.total || 0
  } catch (e) {
    error.value = e.message || '加载失败'
  } finally {
    loading.value = false
  }
}

watch(diyname, () => load(1))
onMounted(() => load(Number(route.query.page) || 1))
</script>
