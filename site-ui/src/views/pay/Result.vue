<template>
  <BContainer class="py-5 text-center" style="max-width: 480px">
    <h1 class="h4 mb-3">{{ success ? '支付成功' : '支付结果' }}</h1>
    <p class="text-body-secondary mb-4">
      渠道：{{ typeLabel }} · 状态：{{ status }}
    </p>
    <BButton to="/" variant="primary">返回首页</BButton>
  </BContainer>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSiteStore } from '@/store/site'

const route = useRoute()
useSiteStore().applySeo({ title: '支付结果' })
const type = computed(() => String(route.query.type || ''))
const status = computed(() => String(route.query.status || ''))
const success = computed(() => status.value === 'success')
const typeLabel = computed(() => (type.value === 'alipay' ? '支付宝' : type.value === 'wechat' ? '微信' : type.value || '-'))
</script>
