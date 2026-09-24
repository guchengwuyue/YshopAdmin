<template>
  <BContainer class="py-5 text-center">
    <h1 class="h3 mb-3">页面不存在</h1>
    <p class="text-body-secondary mb-4">您访问的页面可能已移动或删除。</p>
    <BButton to="/" variant="primary">返回首页</BButton>
  </BContainer>
</template>

<script setup>
import { onMounted } from 'vue'
import { useSiteStore } from '@/store/site'

onMounted(() => {
  useSiteStore().applySeo({ title: '页面不存在' })
})
</script>
