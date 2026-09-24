<template>
  <BContainer class="py-4 py-lg-5">
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <div v-else-if="error" class="alert alert-warning">{{ error }}</div>
    <template v-else>
      <h1 class="h3 mb-2">{{ special?.title }}</h1>
      <p v-if="special?.description" class="text-body-secondary mb-4">{{ special.description }}</p>
      <BRow class="g-4">
        <BCol v-for="item in list" :key="item.id" md="6" lg="4">
          <ArchiveCard :item="item" />
        </BCol>
      </BRow>
      <div v-if="!list.length" class="text-body-secondary py-4">暂无内容</div>
      <Pager :page="page" :page-size="pageSize" :total="total" @change="load" />
    </template>
  </BContainer>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchSpecial } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import ArchiveCard from '@/components/ArchiveCard.vue'
import Pager from '@/components/Pager.vue'

const route = useRoute()
const site = useSiteStore()
const loading = ref(true)
const error = ref('')
const special = ref(null)
const list = ref([])
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const res = await fetchSpecial(route.params.diyname, { page: p })
    const data = res.data || {}
    site.applySeo(data.seo || {})
    special.value = data.special
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

watch(() => route.params.diyname, () => load(1))
onMounted(() => load(1))
</script>
