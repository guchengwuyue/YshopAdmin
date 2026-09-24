<template>
  <BContainer class="py-5 text-center" style="max-width: 520px">
    <h1 class="h4 mb-3">微信支付</h1>
    <div v-if="error" class="alert alert-warning">{{ error }}</div>
    <template v-else-if="loaded">
      <p class="text-body-secondary mb-3">{{ orderData?.title || '请完成支付' }}</p>
      <p class="fs-4 mb-4" v-if="orderData?.amount">￥{{ orderData.amount }}</p>
      <div v-if="payData?.code_url" class="mb-3">
        <img :src="qrSrc" alt="微信支付二维码" width="220" height="220" class="border rounded" />
        <p class="small text-body-secondary mt-2">请使用微信扫码支付</p>
      </div>
      <div v-else-if="payData?.paySign" class="mb-3">
        <BButton variant="success" @click="invokeJsapi">唤起微信支付</BButton>
      </div>
      <p class="small text-body-secondary">{{ statusText }}</p>
    </template>
    <div v-else class="text-body-secondary">加载中…</div>
  </BContainer>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { payQuery, payWechat } from '@/api/pay'
import { useSiteStore } from '@/store/site'

useSiteStore().applySeo({ title: '微信支付' })
const router = useRouter()
const loaded = ref(false)
const error = ref('')
const orderData = ref(null)
const payData = ref(null)
const statusText = ref('等待支付…')
let timer = null

const qrSrc = computed(() => {
  const url = payData.value?.code_url
  if (!url) return ''
  return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(url)
})

async function load() {
  try {
    const res = await payWechat()
    const data = res.data || {}
    orderData.value = data.orderData
    payData.value = data.payData
    loaded.value = true
    if (data.payData?.paySign) {
      invokeJsapi()
    }
    timer = setInterval(poll, 2500)
  } catch (e) {
    error.value = e.message || '加载失败'
  }
}

async function poll() {
  const orderid = orderData.value?.orderid || orderData.value?.out_trade_no
  if (!orderid) return
  try {
    const res = await payQuery({ type: 'wechat', orderid })
    const status = res.data?.status || ''
    statusText.value = '状态：' + status
    if (status === 'SUCCESS' || status === 'TRADE_SUCCESS') {
      clearInterval(timer)
      router.replace({ path: '/pay/result', query: { type: 'wechat', status: 'success' } })
    }
  } catch (e) {
    /* keep polling */
  }
}

function invokeJsapi() {
  const d = payData.value
  const bridge = typeof window !== 'undefined' ? window.WeixinJSBridge : undefined
  if (!d?.paySign || !bridge) return
  bridge.invoke('getBrandWCPayRequest', {
    appId: d.appId,
    timeStamp: d.timeStamp,
    nonceStr: d.nonceStr,
    package: d.package,
    signType: d.signType,
    paySign: d.paySign,
  })
}

onMounted(load)
onUnmounted(() => timer && clearInterval(timer))
</script>
