<template>
  <div class="page-card">
    <a-spin :spinning="loading">
      <a-tabs v-model:activeKey="activeTab">
        <a-tab-pane key="wechat" tab="微信支付">
          <a-form :label-col="{ span: 6 }" :wrapper-col="{ span: 14 }">
            <a-form-item label="APP的app_id">
              <a-input v-model:value="form.wechat.appid" placeholder="APP应用中支付时使用" allow-clear />
            </a-form-item>
            <a-form-item label="公众号的app_id">
              <a-input v-model:value="form.wechat.app_id" placeholder="公众号中支付时使用" allow-clear />
            </a-form-item>
            <a-form-item label="公众号的app_secret">
              <a-input v-model:value="form.wechat.app_secret" placeholder="公众号中支付时使用" allow-clear />
            </a-form-item>
            <a-form-item label="小程序的app_id">
              <a-input v-model:value="form.wechat.miniapp_id" placeholder="仅在小程序支付时使用" allow-clear />
            </a-form-item>
            <a-form-item label="微信支付商户号">
              <a-input v-model:value="form.wechat.mch_id" allow-clear />
            </a-form-item>

            <a-form-item label="V3配置">
              <div class="v3-box">
                <a-form-item label="商户APIv3密钥" :label-col="{ span: 7 }" :wrapper-col="{ span: 16 }">
                  <a-input v-model:value="form.wechat.key_v3" allow-clear />
                </a-form-item>
                <a-form-item label="公钥ID" :label-col="{ span: 7 }" :wrapper-col="{ span: 16 }">
                  <a-input v-model:value="form.wechat.public_key_id" allow-clear />
                </a-form-item>
                <a-form-item label="公钥证书路径" :label-col="{ span: 7 }" :wrapper-col="{ span: 16 }">
                  <div class="cert-row">
                    <a-input v-model:value="form.wechat.public_key" allow-clear />
                    <a-upload :show-upload-list="false" :before-upload="(file) => upload(file, 'public_key', 'wechat', 'public_key')">
                      <a-button danger>上传</a-button>
                    </a-upload>
                  </div>
                  <a class="help-link" href="https://pay.weixin.qq.com/doc/v3/merchant/4012154180" target="_blank" rel="noopener noreferrer">如何获取微信支付公钥证书?</a>
                </a-form-item>
              </div>
            </a-form-item>

            <a-form-item label="商户API证书cert">
              <div class="cert-row">
                <a-input v-model:value="form.wechat.cert_client" allow-clear />
                <a-upload :show-upload-list="false" :before-upload="(file) => upload(file, 'apiclient_cert', 'wechat', 'cert_client')">
                  <a-button danger>上传</a-button>
                </a-upload>
              </div>
              <a class="help-link" href="https://pay.weixin.qq.com/doc/v3/merchant/4012069852" target="_blank" rel="noopener noreferrer">如何获取微信支付商户API证书?</a>
            </a-form-item>
            <a-form-item label="商户API证书key">
              <div class="cert-row">
                <a-input v-model:value="form.wechat.cert_key" allow-clear />
                <a-upload :show-upload-list="false" :before-upload="(file) => upload(file, 'apiclient_key', 'wechat', 'cert_key')">
                  <a-button danger>上传</a-button>
                </a-upload>
              </div>
              <a class="help-link" href="https://pay.weixin.qq.com/doc/v3/merchant/4012069852" target="_blank" rel="noopener noreferrer">如何获取微信支付商户API证书?</a>
            </a-form-item>

            <a-form-item label="支付模式">
              <a-radio-group v-model:value="form.wechat.mode" :options="modeOptions" />
            </a-form-item>
            <template v-if="form.wechat.mode === 'service'">
              <a-form-item label="子商户号">
                <a-input v-model:value="form.wechat.sub_mch_id" allow-clear />
              </a-form-item>
              <a-form-item label="子商户APP appid">
                <a-input v-model:value="form.wechat.sub_app_id" allow-clear />
              </a-form-item>
              <a-form-item label="子商户公众号appid">
                <a-input v-model:value="form.wechat.sub_appid" allow-clear />
              </a-form-item>
              <a-form-item label="子商户小程序appid">
                <a-input v-model:value="form.wechat.sub_miniapp_id" allow-clear />
              </a-form-item>
            </template>
            <a-form-item label="回调通知地址">
              <a-input v-model:value="form.wechat.notify_url" placeholder="留空则使用默认 /api/pay/notifyx/wechat" allow-clear />
              <div class="form-tip">请勿随意修改，实际以逻辑代码中请求的为准</div>
            </a-form-item>
            <a-form-item label="记录日志">
              <a-radio-group v-model:value="form.wechat.log" :options="switchOptions" />
            </a-form-item>
            <a-form-item :wrapper-col="{ offset: 6 }">
              <a-button v-if="can('pay:config:edit')" type="primary" :loading="saving" @click="save">保存</a-button>
            </a-form-item>
          </a-form>
        </a-tab-pane>

        <a-tab-pane key="alipay" tab="支付宝">
          <a-form :label-col="{ span: 6 }" :wrapper-col="{ span: 14 }">
            <a-form-item label="支付模式">
              <a-radio-group v-model:value="form.alipay.mode" :options="modeOptions" />
            </a-form-item>
            <a-form-item v-if="form.alipay.mode === 'service'" label="服务商PID">
              <a-input v-model:value="form.alipay.pid" allow-clear />
            </a-form-item>
            <a-form-item label="应用ID(app_id)">
              <a-input v-model:value="form.alipay.app_id" allow-clear />
            </a-form-item>
            <a-form-item label="回调通知地址">
              <a-input v-model:value="form.alipay.notify_url" placeholder="留空则使用默认 /api/pay/notifyx/alipay" allow-clear />
              <div class="form-tip">请勿随意修改，实际以逻辑代码中请求的为准</div>
            </a-form-item>
            <a-form-item label="支付跳转地址">
              <a-input v-model:value="form.alipay.return_url" placeholder="留空则使用默认 /api/pay/returnx/alipay" allow-clear />
            </a-form-item>
            <a-form-item label="签名方式">
              <a-radio-group v-model:value="form.alipay.signtype" :options="signOptions" />
              <div class="form-tip">转账、提现须使用公钥证书</div>
            </a-form-item>

            <template v-if="form.alipay.signtype === 'publickey'">
              <a-form-item label="支付宝公钥">
                <a-input v-model:value="form.alipay.ali_public_key" allow-clear />
              </a-form-item>
              <a-form-item label="应用私钥">
                <a-textarea v-model:value="form.alipay.private_key" :rows="6" allow-clear />
              </a-form-item>
            </template>
            <template v-else>
              <a-form-item label="应用私钥">
                <a-textarea v-model:value="form.alipay.private_key" :rows="6" allow-clear />
              </a-form-item>
              <a-form-item label="应用公钥证书">
                <div class="cert-row">
                  <a-input v-model:value="form.alipay.app_cert_public_key" allow-clear />
                  <a-upload :show-upload-list="false" :before-upload="(file) => upload(file, 'appCertPublicKey', 'alipay', 'app_cert_public_key')">
                    <a-button danger>上传</a-button>
                  </a-upload>
                </div>
              </a-form-item>
              <a-form-item label="支付宝公钥证书">
                <div class="cert-row">
                  <a-input v-model:value="form.alipay.ali_public_key" allow-clear />
                  <a-upload :show-upload-list="false" :before-upload="(file) => upload(file, 'alipayCertPublicKey', 'alipay', 'ali_public_key')">
                    <a-button danger>上传</a-button>
                  </a-upload>
                </div>
              </a-form-item>
              <a-form-item label="支付宝根证书">
                <div class="cert-row">
                  <a-input v-model:value="form.alipay.alipay_root_cert" allow-clear />
                  <a-upload :show-upload-list="false" :before-upload="(file) => upload(file, 'alipayRootCert', 'alipay', 'alipay_root_cert')">
                    <a-button danger>上传</a-button>
                  </a-upload>
                </div>
              </a-form-item>
            </template>

            <a-form-item label="记录日志">
              <a-radio-group v-model:value="form.alipay.log" :options="switchOptions" />
            </a-form-item>
            <a-form-item label="PC端使用扫码支付">
              <a-radio-group v-model:value="form.alipay.scanpay" :options="switchOptions" />
            </a-form-item>
            <a-form-item :wrapper-col="{ offset: 6 }">
              <a-button v-if="can('pay:config:edit')" type="primary" :loading="saving" @click="save">保存</a-button>
            </a-form-item>
          </a-form>
        </a-tab-pane>
      </a-tabs>
    </a-spin>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { message } from 'ant-design-vue'
import { useUserStore } from '@/store/user'
import { getPayConfig, savePayConfig, uploadPayCert } from '@/api/builtin'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const activeTab = ref('wechat')

const modeOptions = [
  { label: '正式环境', value: 'normal' },
  { label: '沙箱环境', value: 'dev' },
  { label: '服务商模式', value: 'service' },
]
const switchOptions = [
  { label: '开启', value: '1' },
  { label: '关闭', value: '0' },
]
const signOptions = [
  { label: '普通公钥', value: 'publickey' },
  { label: '公钥证书', value: 'cert' },
]

const wechatDefaults = {
  appid: '',
  app_id: '',
  app_secret: '',
  miniapp_id: '',
  mch_id: '',
  key_v3: '',
  public_key_id: '',
  public_key: '/data/certs/pay/public_key.pem',
  cert_client: '/data/certs/pay/apiclient_cert.pem',
  cert_key: '/data/certs/pay/apiclient_key.pem',
  mode: 'normal',
  sub_mch_id: '',
  sub_appid: '',
  sub_app_id: '',
  sub_miniapp_id: '',
  notify_url: '',
  log: '1',
}
const alipayDefaults = {
  app_id: '',
  mode: 'normal',
  notify_url: '/api/pay/notifyx/alipay',
  return_url: '/api/pay/returnx/alipay',
  private_key: '',
  signtype: 'publickey',
  pid: '',
  ali_public_key: '',
  app_cert_public_key: '',
  alipay_root_cert: '',
  log: '1',
  scanpay: '0',
}

const form = reactive({
  wechat: { ...wechatDefaults },
  alipay: { ...alipayDefaults },
})

function normalizeChannel(data, defaults) {
  const next = { ...defaults, ...(data || {}) }
  for (const key of ['log', 'scanpay', 'mode', 'signtype']) {
    if (next[key] !== undefined && next[key] !== null) next[key] = String(next[key])
  }
  return next
}

onMounted(async () => {
  loading.value = true
  try {
    const res = await getPayConfig()
    form.wechat = normalizeChannel(res.data?.wechat, wechatDefaults)
    form.alipay = normalizeChannel(res.data?.alipay, alipayDefaults)
  } finally {
    loading.value = false
  }
})

async function save() {
  saving.value = true
  try {
    await savePayConfig({ wechat: form.wechat, alipay: form.alipay })
    message.success('保存成功')
  } finally {
    saving.value = false
  }
}

async function upload(file, certname, channel, field) {
  try {
    const res = await uploadPayCert(file, certname)
    const path = res.data?.path || ''
    if (path) form[channel][field] = path
    message.success(path || '上传成功')
  } catch (e) {
    message.error(e?.message || '上传失败')
  }
  return false
}
</script>

<style scoped>
.v3-box {
  background: #fafafa;
  border: 1px solid #f0f0f0;
  border-radius: 6px;
  padding: 12px 12px 0;
}
.cert-row {
  display: flex;
  gap: 8px;
  align-items: center;
}
.cert-row .ant-input {
  flex: 1;
}
.help-link {
  display: inline-block;
  margin-top: 6px;
  color: #52c41a;
  font-size: 13px;
}
.form-tip {
  margin-top: 4px;
  color: rgba(0, 0, 0, 0.45);
  font-size: 12px;
  line-height: 1.5;
}
</style>
