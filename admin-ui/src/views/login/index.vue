<template>
  <div class="login-page" :class="{ 'is-alt': altStyle }">
    <div class="login-card">
      <aside class="brand-panel">
        <div class="brand-head">
          <span class="brand-logo" aria-hidden="true">
            <svg viewBox="0 0 32 32" width="22" height="22">
              <path
                fill="currentColor"
                d="M16 4L6 10v12l10 6 10-6V10L16 4zm0 3.2l7 4.1v7.4l-7 4.1-7-4.1v-7.4l7-4.1zM12.5 14.2l3.5 6.2 3.5-6.2h-2.1l-1.4 2.5-1.4-2.5h-2.1z"
              />
            </svg>
          </span>
          <span class="brand-name">YshopAdmin</span>
        </div>

        <div class="brand-body">
          <h2 class="brand-title">构建高效、智能的<br />企业级管理平台</h2>
          <p class="brand-desc">现代化技术栈，极致的用户体验，助力企业数字化转型。</p>
          <ul class="feature-list">
            <li>
              <RocketOutlined />
              <span>极速开发体验</span>
            </li>
            <li>
              <BgColorsOutlined />
              <span>现代化 UI 设计</span>
            </li>
            <li>
              <SafetyCertificateOutlined />
              <span>企业级安全保障</span>
            </li>
          </ul>
        </div>

        <p class="brand-copy">© {{ year }} YshopAdmin. All rights reserved.</p>
      </aside>

      <section class="form-panel">
        <div class="form-wrap">
          <h1 class="form-title">欢迎登录</h1>
          <p class="form-subtitle">请输入您的账号信息以继续</p>

          <a-form :model="form" layout="vertical" @finish="onSubmit">
            <a-form-item name="username" :rules="[{ required: true, message: '请输入账号' }]">
              <a-input
                v-model:value="form.username"
                size="large"
                placeholder="admin"
                allow-clear
              >
                <template #prefix>
                  <UserOutlined class="input-icon" />
                </template>
              </a-input>
            </a-form-item>

            <a-form-item name="password" :rules="[{ required: true, message: '请输入密码' }]">
              <a-input-password
                v-model:value="form.password"
                size="large"
                placeholder="请输入密码"
              >
                <template #prefix>
                  <LockOutlined class="input-icon" />
                </template>
              </a-input-password>
            </a-form-item>

            <a-form-item name="validateCode" :rules="[{ required: true, message: '请输入验证码' }]">
              <div class="captcha-row">
                <a-input
                  v-model:value="form.validateCode"
                  size="large"
                  placeholder="验证码"
                  allow-clear
                >
                  <template #prefix>
                    <SafetyOutlined class="input-icon" />
                  </template>
                </a-input>
                <img
                  class="captcha-img"
                  :src="captchaUrl"
                  alt="验证码"
                  title="点击刷新"
                  @click="refreshCaptcha"
                />
              </div>
            </a-form-item>

            <div class="form-extra">
              <a-checkbox v-model:checked="form.remember">记住我</a-checkbox>
              <a class="link" href="javascript:;" @click.prevent="onForgot">忘记密码?</a>
            </div>

            <a-button
              type="primary"
              html-type="submit"
              size="large"
              block
              class="login-btn"
              :loading="loading"
            >
              登录
            </a-button>
          </a-form>

          <div class="form-footer">
            <span class="muted">没有账号？</span>
            <a class="link" href="javascript:;" @click.prevent="onRegister">立即注册</a>
            <span class="sep" />
            <a class="link" href="javascript:;" @click.prevent="toggleStyle">切换风格</a>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { message } from 'ant-design-vue'
import {
  UserOutlined,
  LockOutlined,
  SafetyOutlined,
  RocketOutlined,
  BgColorsOutlined,
  SafetyCertificateOutlined,
} from '@ant-design/icons-vue'
import { getCaptchaImageUrl } from '@/api/login'
import { useUserStore } from '@/store/user'

const REMEMBER_KEY = 'YshopAdmin-Login-Remember'
const STYLE_KEY = 'YshopAdmin-Login-Style'
const CAPTCHA_TYPE = 'math'

const router = useRouter()
const userStore = useUserStore()
const loading = ref(false)
const captchaUrl = ref('')
const year = new Date().getFullYear()
const altStyle = ref(localStorage.getItem(STYLE_KEY) === '1')

const saved = (() => {
  try {
    return JSON.parse(localStorage.getItem(REMEMBER_KEY) || 'null')
  } catch {
    return null
  }
})()

const form = reactive({
  username: saved?.username || 'admin',
  password: 'admin123',
  validateCode: '',
  remember: !!saved?.username,
})

function refreshCaptcha() {
  captchaUrl.value = getCaptchaImageUrl(CAPTCHA_TYPE)
  form.validateCode = ''
}

function toggleStyle() {
  altStyle.value = !altStyle.value
  localStorage.setItem(STYLE_KEY, altStyle.value ? '1' : '0')
}

function onForgot() {
  message.info('请联系管理员重置密码')
}

function onRegister() {
  message.info('请联系管理员开通账号')
}

async function onSubmit() {
  loading.value = true
  try {
    await userStore.login({
      username: form.username,
      password: form.password,
      validateCode: form.validateCode,
    })
    if (form.remember) {
      localStorage.setItem(REMEMBER_KEY, JSON.stringify({ username: form.username }))
    } else {
      localStorage.removeItem(REMEMBER_KEY)
    }
    message.success('登录成功')
    router.replace('/')
  } catch {
    refreshCaptcha()
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  refreshCaptcha()
})
</script>

<style scoped>
.login-page {
  min-height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: #f3f4f6;
  box-sizing: border-box;
}

.login-card {
  display: flex;
  width: min(920px, 100%);
  min-height: 560px;
  border-radius: 16px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
}

.brand-panel {
  position: relative;
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 36px 40px;
  color: #fff;
  background: linear-gradient(160deg, #3b82f6 0%, #2563eb 55%, #1d4ed8 100%);
  overflow: hidden;
}

.brand-panel::before,
.brand-panel::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  pointer-events: none;
}

.brand-panel::before {
  width: 280px;
  height: 280px;
  top: -90px;
  right: -70px;
}

.brand-panel::after {
  width: 220px;
  height: 220px;
  left: -80px;
  bottom: -60px;
  background: rgba(255, 255, 255, 0.08);
}

.login-page.is-alt .brand-panel {
  background: linear-gradient(160deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
}

.brand-head,
.brand-body,
.brand-copy {
  position: relative;
  z-index: 1;
}

.brand-head {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #fff;
  color: #2563eb;
}

.brand-name {
  font-size: 20px;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.brand-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 28px 0;
}

.brand-title {
  margin: 0 0 14px;
  font-size: 28px;
  line-height: 1.35;
  font-weight: 700;
}

.brand-desc {
  margin: 0 0 28px;
  font-size: 14px;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.85);
}

.feature-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.feature-list li {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  width: fit-content;
  padding: 10px 16px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.18);
  font-size: 14px;
  backdrop-filter: blur(4px);
}

.brand-copy {
  margin: 0;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.75);
}

.form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 36px;
  background: #fff;
}

.form-wrap {
  width: 100%;
  max-width: 340px;
}

.form-title {
  margin: 0 0 8px;
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
}

.form-subtitle {
  margin: 0 0 28px;
  font-size: 14px;
  color: #9ca3af;
}

.input-icon {
  color: #9ca3af;
}

.form-panel :deep(.ant-input-affix-wrapper),
.form-panel :deep(.ant-input-password) {
  border-radius: 10px;
  padding-block: 8px;
}

.form-panel :deep(.ant-form-item) {
  margin-bottom: 18px;
}

.captcha-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.captcha-row :deep(.ant-input-affix-wrapper) {
  flex: 1;
}

.captcha-img {
  width: 110px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  object-fit: cover;
  background: #f3fbfa;
}

.form-extra {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: -4px 0 22px;
}

.link {
  color: #3b82f6;
  text-decoration: none;
}

.link:hover {
  color: #2563eb;
}

.login-btn {
  height: 44px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  box-shadow: 0 8px 18px rgba(59, 130, 246, 0.28);
}

.form-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 28px;
  font-size: 14px;
}

.muted {
  color: #9ca3af;
}

.sep {
  width: 1px;
  height: 14px;
  background: #d1d5db;
  margin: 0 4px;
}

@media (max-width: 768px) {
  .login-card {
    flex-direction: column;
    min-height: auto;
  }

  .brand-panel {
    padding: 28px 24px 20px;
    min-height: auto;
  }

  .brand-title {
    font-size: 22px;
  }

  .brand-body {
    padding: 16px 0 8px;
  }

  .feature-list {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .brand-copy {
    display: none;
  }

  .form-panel {
    padding: 28px 24px 32px;
  }
}
</style>
