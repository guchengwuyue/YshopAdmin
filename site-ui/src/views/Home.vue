<template>
  <div>
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <template v-else>
      <section v-if="focusList.length" v-motion="pageEnter" class="yst-banner">
        <FocusSlider :items="focusList" />
      </section>

      <BContainer class="py-5">
        <section v-if="hotProducts.length" class="mb-5">
          <div v-motion="pageEnter" class="section-hd mb-4">
            <h2 class="h4 mb-1 section-title">
              <span class="section-title__bars" aria-hidden="true"></span>
              推荐产品
            </h2>
            <p class="text-body-secondary mb-0">精选热门产品，助力业务增长</p>
          </div>
          <BRow class="g-4">
            <BCol
              v-for="(item, i) in hotProducts"
              :key="item.id"
              v-motion="pageEnterStagger(i)"
              md="6"
              lg="4"
            >
              <ArchiveCard :item="item" variant="product" />
            </BCol>
          </BRow>
        </section>

        <section v-if="aboutPage" class="mb-5 about-band p-4 p-lg-5 rounded-3">
          <BRow class="g-4 align-items-center">
            <BCol v-motion="pageEnterStagger(0)" md="6">
              <h2 class="h4 section-title">
                <span class="section-title__bars" aria-hidden="true"></span>
                公司简介
              </h2>
              <h3 class="about-band__name">{{ aboutPage.title }}</h3>
              <p class="about-band__desc">{{ aboutPage.description }}</p>
              <RouterLink
                class="btn btn-primary about-band__more"
                :to="`/p/${aboutPage.diyname || aboutPage.id}`"
              >
                查看更多 &gt;
              </RouterLink>
            </BCol>
            <BCol v-motion="pageEnterStagger(1)" md="6">
              <img
                class="about-band__img"
                :src="aboutPage.image || '/addons/yscms/theme/ystheme/img/noimage.svg'"
                :alt="aboutPage.title"
              />
            </BCol>
          </BRow>
        </section>

        <section v-if="newsFeatured || newsList.length" class="mb-5">
          <div v-motion="pageEnter" class="section-hd mb-4">
            <h2 class="h4 mb-1 section-title">
              <span class="section-title__bars" aria-hidden="true"></span>
              新闻动态
            </h2>
            <p class="text-body-secondary mb-0">了解最新资讯与行业动态</p>
          </div>
          <BRow class="g-4">
            <BCol v-if="newsFeatured" v-motion="pageEnterStagger(0)" md="6">
              <ArchiveCard :item="newsFeatured" />
            </BCol>
            <BCol md="6">
              <ul class="list-unstyled mb-0">
                <li
                  v-for="(item, i) in newsList"
                  :key="item.id"
                  v-motion="pageEnterStagger(newsFeatured ? i + 1 : i)"
                  class="border-bottom py-3"
                >
                  <RouterLink :to="item.url || `/a/${item.diyname || item.id}`" class="text-decoration-none text-body">
                    {{ item.title }}
                  </RouterLink>
                  <div class="small text-body-secondary mt-1">{{ String(item.publishTime || item.createTime || '').slice(0, 10) }}</div>
                </li>
              </ul>
            </BCol>
          </BRow>
        </section>

        <section v-if="list.length">
          <div v-motion="pageEnter" class="section-hd mb-4">
            <h2 class="h4 mb-1 section-title">
              <span class="section-title__bars" aria-hidden="true"></span>
              最新内容
            </h2>
          </div>
          <BRow class="g-4">
            <BCol
              v-for="(item, i) in list"
              :key="item.id"
              v-motion="pageEnterStagger(i)"
              md="6"
              lg="4"
            >
              <ArchiveCard :item="item" variant="product" />
            </BCol>
          </BRow>
        </section>
      </BContainer>
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { fetchHome } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { pageEnter, pageEnterStagger } from '@/motion/presets'
import ArchiveCard from '@/components/ArchiveCard.vue'
import FocusSlider from '@/components/FocusSlider.vue'

const site = useSiteStore()
const loading = ref(true)
const focusList = ref([])
const hotProducts = ref([])
const aboutPage = ref(null)
const newsFeatured = ref(null)
const newsList = ref([])
const list = ref([])

async function load() {
  loading.value = true
  try {
    const res = await fetchHome({ page: 1, pageSize: 6 })
    const data = res.data || {}
    site.applySeo(data.seo || {})
    focusList.value = data.focusList || []
    hotProducts.value = data.hotProducts || []
    aboutPage.value = data.aboutPage || null
    newsFeatured.value = data.newsFeatured || null
    newsList.value = data.newsList || []
    list.value = (data.list || []).slice(0, 6)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => load())
</script>
