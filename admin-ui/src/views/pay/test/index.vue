<template>
  <div class="page-card">
    <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 12 }">
      <a-form-item label="金额"><a-input v-model:value="form.amount" /></a-form-item>
      <a-form-item label="渠道">
        <a-radio-group v-model:value="form.type" :options="[{ label: '微信', value: 'wechat' }, { label: '支付宝', value: 'alipay' }]" />
      </a-form-item>
      <a-form-item label="场景">
        <a-select v-model:value="form.method" :options="methods" />
      </a-form-item>
      <a-form-item label="openid"><a-input v-model:value="form.openid" /></a-form-item>
      <a-form-item label="付款码"><a-input v-model:value="form.authCode" /></a-form-item>
      <a-form-item :wrapper-col="{ offset: 6 }">
        <a-button v-if="can('pay:test:pay')" type="primary" :loading="saving" @click="submit">发起测试</a-button>
      </a-form-item>
    </a-form>
    <pre v-if="result" class="pre">{{ result }}</pre>
  </div>
</template>
<script setup>
import { reactive, ref } from 'vue'
import { useUserStore } from '@/store/user'
import { payTest } from '@/api/builtin'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const saving = ref(false)
const result = ref('')
const form = reactive({ amount: '0.01', type: 'wechat', method: 'scan', openid: '', authCode: '' })
const methods = ['web', 'wap', 'h5', 'app', 'scan', 'mp', 'mini', 'pos'].map((v) => ({ label: v, value: v }))
async function submit() {
  saving.value = true
  try {
    const res = await payTest({ ...form, auth_code: form.authCode })
    result.value = JSON.stringify(res.data || {}, null, 2)
    if (res.data?.redirect) window.open(res.data.redirect, '_blank')
  } finally { saving.value = false }
}
</script>
<style scoped>.pre { background: #f7f7f7; padding: 12px; }</style>
