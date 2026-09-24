<template>
  <div class="yst-auth-page">
    <BContainer class="yst-auth-wrap">
      <div v-motion="pageEnter" class="yst-auth-card">
        <div class="yst-auth-tabs">
          <RouterLink class="yst-auth-tabs__item" :to="loginTo">登录</RouterLink>
          <RouterLink class="yst-auth-tabs__item is-active" :to="registerTo">注册</RouterLink>
        </div>
        <div class="yst-auth-body">
          <BAlert v-if="error" variant="danger" :model-value="true" class="mb-3">{{ error }}</BAlert>
          <BForm class="yst-auth-form" @submit.prevent="onSubmit">
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="reg-email">邮箱</label>
              <BFormInput
                id="reg-email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                placeholder="邮箱"
              />
            </div>
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="reg-username">用户名</label>
              <BFormInput
                id="reg-username"
                v-model="form.username"
                required
                autocomplete="username"
                minlength="3"
                maxlength="30"
                placeholder="用户名必须3-30个字符"
              />
            </div>
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="reg-password">密码</label>
              <BFormInput
                id="reg-password"
                v-model="form.password"
                type="password"
                required
                autocomplete="new-password"
                minlength="6"
                maxlength="30"
                placeholder="密码必须6-30个字符"
              />
            </div>
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="reg-mobile">手机号</label>
              <BFormInput
                id="reg-mobile"
                v-model="form.mobile"
                required
                autocomplete="tel"
                placeholder="手机号"
              />
            </div>
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="reg-captcha">验证码</label>
              <div class="yst-auth-captcha">
                <BFormInput
                  id="reg-captcha"
                  v-model="form.captcha"
                  required
                  autocomplete="off"
                  placeholder="验证码"
                />
                <button type="button" class="yst-auth-captcha__img" title="点击刷新" @click="refreshCaptcha">
                  <img :src="captchaUrl" alt="验证码" width="110" height="36" />
                </button>
              </div>
            </div>
            <BButton type="submit" class="yst-auth-btn" :disabled="loading">
              {{ loading ? '提交中…' : '注册' }}
            </BButton>
          </BForm>
        </div>
      </div>
    </BContainer>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { captchaImageUrl } from '@/api/cms'
import { useUserStore } from '@/store/user'
import { useSiteStore } from '@/store/site'
import { pageEnter } from '@/motion/presets'

const route = useRoute()
const router = useRouter()
const user = useUserStore()
useSiteStore().applySeo({ title: '会员注册' })

const redirectUrl = computed(() => {
  const url = String(route.query.url || '')
  return url.startsWith('/') ? url : ''
})

const loginTo = computed(() => ({
  path: '/member/login',
  query: redirectUrl.value ? { url: redirectUrl.value } : {},
}))

const registerTo = computed(() => ({
  path: '/member/register',
  query: redirectUrl.value ? { url: redirectUrl.value } : {},
}))

const form = reactive({
  email: '',
  username: '',
  password: '',
  mobile: '',
  captcha: '',
})
const loading = ref(false)
const error = ref('')
const captchaUrl = ref('')

function refreshCaptcha() {
  captchaUrl.value = captchaImageUrl('char')
}

async function onSubmit() {
  loading.value = true
  error.value = ''
  try {
    await user.register({ ...form })
    router.replace(redirectUrl.value || '/member')
  } catch (e) {
    error.value = e.message || '注册失败'
    form.captcha = ''
    refreshCaptcha()
  } finally {
    loading.value = false
  }
}

onMounted(refreshCaptcha)
</script>
