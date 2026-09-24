<template>
  <div class="diyform-post-page">
    <div v-if="loading" class="text-center py-5 text-body-secondary">加载中…</div>
    <div v-else-if="error && !form" class="alert alert-warning m-4">{{ error }}</div>
    <template v-else>
      <div class="diyform-post-body">
        <BContainer class="py-4 py-lg-5" style="max-width: 720px">
          <div v-motion="pageEnter" class="diyform-post-card">
            <h1 class="diyform-post-card__title">{{ form?.title }}</h1>
            <BAlert v-if="error" variant="danger" :model-value="true" class="mb-3">{{ error }}</BAlert>
            <BAlert v-if="success" variant="success" :model-value="true" class="mb-3">{{ success }}</BAlert>

            <BForm class="diyform-post-form" @submit.prevent="onSubmit">
              <div
                v-for="field in fields"
                :key="field.name"
                class="diyform-post-form__row"
              >
                <label class="diyform-post-form__label" :for="'f-' + field.name">
                  {{ field.title || field.name }}
                </label>
                <div class="diyform-post-form__control">
                  <BFormTextarea
                    v-if="isTextarea(field)"
                    :id="'f-' + field.name"
                    v-model="values[field.name]"
                    rows="5"
                    :required="isRequired(field)"
                  />
                  <BFormInput
                    v-else
                    :id="'f-' + field.name"
                    v-model="values[field.name]"
                    :required="isRequired(field)"
                    :type="fieldType(field)"
                  />
                </div>
              </div>

              <div v-if="needCaptcha" class="diyform-post-form__row">
                <label class="diyform-post-form__label" for="f-captcha">验证码</label>
                <div class="diyform-post-form__control diyform-post-form__captcha">
                  <BFormInput
                    id="f-captcha"
                    v-model="captcha"
                    required
                    placeholder="请输入图中验证码"
                    autocomplete="off"
                  />
                  <button type="button" class="diyform-post-form__captcha-img" title="点击刷新" @click="refreshCaptcha">
                    <img :src="captchaUrl" alt="验证码" width="110" height="36" />
                  </button>
                </div>
              </div>

              <div class="diyform-post-form__actions">
                <BButton type="submit" variant="primary" :disabled="submitting">
                  {{ submitting ? '提交中…' : '提交' }}
                </BButton>
                <BButton type="button" variant="outline-primary" :disabled="submitting" @click="onReset">
                  重置
                </BButton>
              </div>
            </BForm>
          </div>
        </BContainer>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { captchaImageUrl, fetchDiyformPost, submitDiyform } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { pageEnter } from '@/motion/presets'

const route = useRoute()
const router = useRouter()
const site = useSiteStore()
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const success = ref('')
const form = ref(null)
const fields = ref([])
const needCaptcha = ref(true)
const values = reactive({})
const captcha = ref('')
const captchaUrl = ref('')

const diyname = computed(() => String(route.params.diyname || '').replace(/\.html$/i, ''))

function isTextarea(field) {
  const t = String(field.type || '').toLowerCase()
  return t === 'textarea' || t === 'editor'
}

function fieldType(field) {
  const t = String(field.type || '').toLowerCase()
  if (t === 'number') return 'number'
  if (t === 'password') return 'password'
  return 'text'
}

function isRequired(field) {
  return String(field.rule || '').includes('required')
}

function initValues(list) {
  for (const key of Object.keys(values)) {
    delete values[key]
  }
  for (const f of list) {
    values[f.name] = f.defaultvalue ?? f.defaultValue ?? ''
  }
  captcha.value = ''
}

function refreshCaptcha() {
  captchaUrl.value = captchaImageUrl('char')
}

function onReset() {
  initValues(fields.value)
  error.value = ''
  success.value = ''
  refreshCaptcha()
}

async function load() {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const res = await fetchDiyformPost(diyname.value)
    const data = res.data || {}
    site.applySeo(data.seo || {})
    form.value = data.diyform
    fields.value = data.fields || []
    needCaptcha.value = data.needCaptcha !== false
    initValues(fields.value)
    refreshCaptcha()
  } catch (e) {
    error.value = e.message || '加载失败'
    form.value = null
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  submitting.value = true
  error.value = ''
  success.value = ''
  try {
    const payload = { ...values, captcha: captcha.value }
    const res = await submitDiyform(diyname.value, payload)
    success.value = res.msg || '提交成功'
    const redirect = res.data?.url
    if (redirect && typeof redirect === 'string' && redirect.startsWith('/')) {
      setTimeout(() => router.push(redirect), 600)
    } else {
      const key = diyname.value
      setTimeout(() => router.push(key ? `/d/${key}` : '/'), 600)
    }
  } catch (e) {
    error.value = e.message || '提交失败'
    refreshCaptcha()
    captcha.value = ''
  } finally {
    submitting.value = false
  }
}

watch(diyname, load)
onMounted(load)
</script>
