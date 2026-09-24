<template>
  <BContainer class="py-4 py-lg-5">
    <h1 class="h3 mb-4">搜索</h1>
    <form class="search-bar mb-4" @submit.prevent="onSubmit">
      <span class="search-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7" />
          <path d="M20 20l-3.5-3.5" stroke-linecap="round" />
        </svg>
      </span>
      <input
        v-model="keyword"
        type="search"
        class="search-bar__input"
        placeholder="搜索感兴趣的内容..."
        autocomplete="off"
        enterkeyhint="search"
      />
      <button type="submit" class="search-bar__btn">搜索</button>
    </form>
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <template v-else>
      <p v-if="keyword" class="text-body-secondary">共 {{ total }} 条结果</p>
      <BRow class="g-4">
        <BCol v-for="item in list" :key="item.id" md="6" lg="4">
          <ArchiveCard :item="item" />
        </BCol>
      </BRow>
      <div v-if="keyword && !list.length" class="text-body-secondary py-4">未找到相关内容</div>
      <Pager :page="page" :page-size="pageSize" :total="total" @change="load" />
    </template>
  </BContainer>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchSearch } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import ArchiveCard from '@/components/ArchiveCard.vue'
import Pager from '@/components/Pager.vue'

const route = useRoute()
const router = useRouter()
const site = useSiteStore()
const keyword = ref(String(route.query.q || ''))
const loading = ref(false)
const list = ref([])
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

async function load(p = 1) {
  loading.value = true
  try {
    const res = await fetchSearch({ q: keyword.value, page: p })
    const data = res.data || {}
    site.applySeo(data.seo || {})
    list.value = data.list || []
    page.value = data.page || 1
    pageSize.value = data.pageSize || 10
    total.value = data.total || 0
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function onSubmit() {
  router.replace({ path: '/s', query: keyword.value ? { q: keyword.value } : {} })
  load(1)
}

watch(
  () => route.query.q,
  (q) => {
    keyword.value = String(q || '')
    load(1)
  }
)

onMounted(() => load(1))
</script>

<style scoped>
.search-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  max-width: 640px;
  height: 48px;
  margin-left: auto;
  margin-right: auto;
  padding: 4px 4px 4px 16px;
  background: #f5f5f5;
  border: 1px solid #EE4036;
  border-radius: 999px;
}

.search-bar__icon {
  flex: none;
  display: inline-flex;
  color: #b0b0b0;
  line-height: 0;
}

.search-bar__input {
  flex: 1 1 auto;
  min-width: 0;
  height: 100%;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--bs-body-color);
  font-size: 0.95rem;
  line-height: 1.4;
}

.search-bar__input::placeholder {
  color: #9a9a9a;
}

.search-bar__input::-webkit-search-decoration,
.search-bar__input::-webkit-search-cancel-button {
  -webkit-appearance: none;
  appearance: none;
}

.search-bar__btn {
  flex: none;
  height: 40px;
  min-width: 88px;
  padding: 0 1.25rem;
  border: 0;
  border-radius: 999px;
  background: #EE4036;
  color: #fff;
  font-size: 0.95rem;
  font-weight: 500;
  line-height: 1;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.search-bar__btn:hover {
  background: #d9382f;
}

.search-bar__btn:active {
  background: #c8322a;
}

.search-bar:focus-within {
  box-shadow: 0 0 0 3px rgba(238, 64, 54, 0.18);
}
</style>
