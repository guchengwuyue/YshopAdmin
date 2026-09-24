<template>
  <div class="yst-auth-page">
    <BContainer class="yst-auth-wrap">
      <div v-motion="pageEnter" class="yst-auth-card">
        <div class="yst-auth-tabs">
          <RouterLink class="yst-auth-tabs__item is-active" :to="loginTo">登录</RouterLink>
          <RouterLink class="yst-auth-tabs__item" :to="registerTo">注册</RouterLink>
        </div>
        <div class="yst-auth-body">
          <BAlert v-if="error" variant="danger" :model-value="true" class="mb-3">{{ error }}</BAlert>
          <BForm class="yst-auth-form" @submit.prevent="onSubmit">
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="login-account">账号</label>
              <BFormInput
                id="login-account"
                v-model="form.username"
                required
                autocomplete="username"
                placeholder="邮箱/手机/用户名"
              />
            </div>
            <div class="yst-auth-form__group">
              <label class="yst-auth-form__label" for="login-password">密码</label>
              <BFormInput
                id="login-password"
                v-model="form.password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="密码"
              />
            </div>
            <div class="yst-auth-meta">
              <BFormCheckbox v-model="form.keeplogin" :value="true" :unchecked-value="false">
                保持会话
              </BFormCheckbox>
              <a href="javascript:;" class="yst-auth-forgot" @click.prevent="onForgot">忘记密码?</a>
            </div>
            <BButton type="submit" class="yst-auth-btn" :disabled="loading">
              {{ loading ? '登录中…' : '登录' }}
            </BButton>
          </BForm>
        </div>
      </div>
    </BContainer>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/store/user'
import { useSiteStore } from '@/store/site'
import { pageEnter } from '@/motion/presets'

const route = useRoute()
const router = useRouter()
const user = useUserStore()
const site = useSiteStore()
site.applySeo({ title: '会员登录' })

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

const form = reactive({ username: '', password: '', keeplogin: true })
const loading = ref(false)
const error = ref('')

function onForgot() {
  error.value = '暂未开放找回密码，请联系管理员'
}

async function onSubmit() {
  loading.value = true
  error.value = ''
  try {
    await user.login({
      username: form.username,
      password: form.password,
      keeplogin: form.keeplogin ? '1' : '0',
    })
    const url = redirectUrl.value || '/member'
    router.replace(url)
  } catch (e) {
    error.value = e.message || '登录失败'
  } finally {
    loading.value = false
  }
}
</script>
