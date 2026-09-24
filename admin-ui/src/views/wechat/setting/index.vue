<template>
  <div class="page-card">
    <a-alert v-if="serverUrl" style="margin-bottom: 12px" type="info" show-icon :message="'服务器地址：' + serverUrl" />
    <a-form :label-col="{ span: 6 }" :wrapper-col="{ span: 12 }">
      <a-form-item label="AppID"><a-input v-model:value="form.app_id" /></a-form-item>
      <a-form-item label="AppSecret"><a-input-password v-model:value="form.secret" /></a-form-item>
      <a-form-item label="Token"><a-input v-model:value="form.token" /></a-form-item>
      <a-form-item label="EncodingAESKey"><a-input v-model:value="form.aes_key" /></a-form-item>
      <a-form-item label="调试">
        <a-radio-group v-model:value="form.debug" :options="[{ label: '否', value: '0' }, { label: '是', value: '1' }]" />
      </a-form-item>
      <a-form-item label="日志等级"><a-input v-model:value="form.log_level" /></a-form-item>
      <a-form-item label="OAuth 回调"><a-input v-model:value="form.oauth_callback" /></a-form-item>
      <a-form-item :wrapper-col="{ offset: 6 }">
        <a-button v-if="can('wechat:setting:edit')" type="primary" :loading="saving" @click="save">保存</a-button>
      </a-form-item>
    </a-form>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { message } from 'ant-design-vue'
import { useUserStore } from '@/store/user'
import { getWechatSetting, saveWechatSetting } from '@/api/builtin'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const saving = ref(false)
const serverUrl = ref('')
const form = reactive({ app_id: '', secret: '', token: '', aes_key: '', debug: '0', log_level: 'info', oauth_callback: '' })
onMounted(async () => {
  const res = await getWechatSetting()
  Object.assign(form, res.data?.config || {})
  form.debug = String(form.debug ?? '0')
  serverUrl.value = res.data?.serverUrl || ''
})
async function save() {
  saving.value = true
  try {
    await saveWechatSetting({ ...form })
    message.success('保存成功')
  } finally { saving.value = false }
}
</script>
