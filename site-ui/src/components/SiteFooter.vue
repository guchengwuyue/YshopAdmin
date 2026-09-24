<template>
  <footer class="site-footer">
    <BContainer>
      <div v-if="friendLinks.length" class="footer-friends">
        <span class="footer-friends__label">友情链接</span>
        <div class="footer-friends__list">
          <template v-for="(link, i) in friendLinks" :key="i">
            <span v-if="i" class="footer-friends__sep" aria-hidden="true">|</span>
            <a :href="link.url || link.link || '#'" target="_blank" rel="noopener">
              {{ link.title || link.name }}
            </a>
          </template>
        </div>
      </div>

      <div v-if="friendLinks.length" class="footer-rule" />

      <BRow class="footer-main g-4 g-lg-5">
        <BCol cols="12" md="4">
          <h4 class="footer-title">网站导航</h4>
          <ul v-if="navItems.length" class="footer-nav list-unstyled mb-0">
            <li v-for="item in navItems" :key="item.id">
              <RouterLink v-if="internalTo(item.url)" :to="internalTo(item.url)">
                {{ item.name }}
              </RouterLink>
              <a
                v-else
                :href="externalHref(item.url) || item.url || '#'"
                :target="item.isnewtab ? '_blank' : undefined"
                :rel="item.isnewtab ? 'noopener' : undefined"
              >
                {{ item.name }}
              </a>
            </li>
          </ul>
          <p v-else class="footer-empty mb-0">暂无导航</p>
        </BCol>

        <BCol cols="12" md="4">
          <h4 class="footer-title">联系我们</h4>
          <ul class="footer-contact list-unstyled mb-0">
            <li>
              <span class="footer-contact__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11z" />
                  <circle cx="12" cy="10" r="2.4" />
                </svg>
              </span>
              <span>地址：{{ address }}</span>
            </li>
            <li>
              <span class="footer-contact__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M6.5 4.5h3l1.5 4-2 1.5a12 12 0 0 0 5 5l1.5-2 4 1.5v3a2 2 0 0 1-2 2A15 15 0 0 1 4.5 6.5a2 2 0 0 1 2-2z" />
                </svg>
              </span>
              <span>电话：{{ phone }}</span>
            </li>
            <li>
              <span class="footer-contact__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6">
                  <rect x="3.5" y="5.5" width="17" height="13" rx="1.5" />
                  <path d="m4.5 7.5 7.5 6 7.5-6" />
                </svg>
              </span>
              <span>邮箱：{{ email }}</span>
            </li>
          </ul>
        </BCol>

        <BCol cols="12" md="4">
          <h4 class="footer-title">关注我们</h4>
          <div v-if="qrList.length" class="footer-qr-list">
            <figure v-for="(qr, i) in qrList" :key="i" class="footer-qr">
              <img :src="qr.image" :alt="qr.title || ''" />
              <figcaption>{{ qr.title || qr.name || '' }}</figcaption>
            </figure>
          </div>
          <p v-else class="footer-empty mb-0">暂无二维码</p>
        </BCol>
      </BRow>

      <div class="footer-copy">
        <span>{{ copyrightText }}</span>
        <a
          v-if="icp"
          class="footer-copy__link"
          href="https://beian.miit.gov.cn/"
          target="_blank"
          rel="noopener"
        >{{ icp }}</a>
        <a
          v-if="beian"
          class="footer-copy__beian"
          href="https://www.beian.gov.cn/"
          target="_blank"
          rel="noopener"
        >
          <img src="https://www.beian.gov.cn/img/gongan.png" alt="" width="14" height="14" />
          <span>{{ beian }}</span>
        </a>
      </div>
    </BContainer>
  </footer>

  <aside class="site-floatbar" aria-label="快捷操作">
    <div class="site-floatbar__inner">
      <a class="site-floatbar__btn" href="#" @click.prevent title="在线咨询">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6">
          <path d="M5 18.5 6.2 15A7.5 7.5 0 1 1 9 19.2L5 18.5z" />
        </svg>
        <span class="site-floatbar__tip">在线咨询</span>
      </a>
      <a class="site-floatbar__btn" :href="phoneHref" title="电话咨询">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6">
          <path d="M6.5 4.5h3l1.5 4-2 1.5a12 12 0 0 0 5 5l1.5-2 4 1.5v3a2 2 0 0 1-2 2A15 15 0 0 1 4.5 6.5a2 2 0 0 1 2-2z" />
        </svg>
        <span class="site-floatbar__tip">{{ phone }}</span>
      </a>
      <div class="site-floatbar__btn site-floatbar__btn--hover" title="关注我们">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6">
          <rect x="3.5" y="3.5" width="7" height="7" rx="1" />
          <rect x="13.5" y="3.5" width="7" height="7" rx="1" />
          <rect x="3.5" y="13.5" width="7" height="7" rx="1" />
          <rect x="13.5" y="13.5" width="7" height="7" rx="1" />
        </svg>
        <div v-if="qrList.length" class="site-floatbar__panel">
          <figure v-for="(qr, i) in qrList.slice(0, 2)" :key="i">
            <img :src="qr.image" :alt="qr.title || ''" />
            <figcaption>{{ qr.title || qr.name || '' }}</figcaption>
          </figure>
        </div>
        <span v-else class="site-floatbar__tip">关注我们</span>
      </div>
      <a class="site-floatbar__btn" href="#" @click.prevent="onShare" title="分享">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6">
          <circle cx="6.5" cy="12" r="2.2" />
          <circle cx="17" cy="6.5" r="2.2" />
          <circle cx="17" cy="17.5" r="2.2" />
          <path d="m8.4 11 6.2-3.5M8.4 13l6.2 3.5" />
        </svg>
        <span class="site-floatbar__tip">分享本页</span>
      </a>
      <button
        type="button"
        class="site-floatbar__btn"
        title="返回顶部"
        @click="scrollTop"
      >
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="m6 14 6-6 6 6" />
        </svg>
        <span class="site-floatbar__tip">返回顶部</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useSiteStore } from '@/store/site'

const site = useSiteStore()
const year = new Date().getFullYear()

const friendLinks = computed(() => site.side.friendLinks || [])
const qrList = computed(() => (site.side.footerQr || []).filter((x) => x.image))
const navItems = computed(() => (site.nav || []).filter((x) => x && x.name))

const address = computed(
  () => site.config.companyAddress || site.config.company_address || '郑州市高新区888号',
)
const phone = computed(
  () => site.config.companyPhone || site.config.company_phone || '0571-00000000',
)
const email = computed(
  () => site.config.companyEmail || site.config.company_email || 'yshop@yixiang.co',
)
const phoneHref = computed(() => {
  const num = String(phone.value || '').replace(/[^\d+]/g, '')
  return num ? `tel:${num}` : '#'
})

const copyrightText = computed(() => {
  const raw = site.config.copyright
  if (raw) return raw
  return `Copyright © 2001-${year} ${site.sitename} All Rights Reserved`
})
const icp = computed(() => site.config.icp || '')
const beian = computed(() => site.config.beian || '')

function normalizePath(url) {
  if (!url || typeof url !== 'string') return ''
  const raw = url.trim()
  if (!raw || raw === '#' || raw.startsWith('javascript:')) return ''
  try {
    if (/^(https?:)?\/\//i.test(raw)) {
      const u = new URL(raw, window.location.origin)
      if (u.origin !== window.location.origin) return ''
      return u.pathname.replace(/\/+$/, '') || '/'
    }
  } catch {
    return ''
  }
  const path = raw.split(/[?#]/)[0]
  if (!path.startsWith('/')) return `/${path}`.replace(/\/+$/, '') || '/'
  return path.replace(/\/+$/, '') || '/'
}

function isExternal(url) {
  if (!url || typeof url !== 'string') return false
  if (url.startsWith('javascript:')) return true
  try {
    if (/^(https?:)?\/\//i.test(url)) {
      return new URL(url, window.location.origin).origin !== window.location.origin
    }
  } catch {
    return true
  }
  return false
}

function internalTo(url) {
  if (isExternal(url)) return undefined
  const path = normalizePath(url)
  return path || undefined
}

function externalHref(url) {
  return isExternal(url) ? url : undefined
}

function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function onShare() {
  const url = window.location.href
  const title = document.title
  try {
    if (navigator.share) {
      await navigator.share({ title, url })
      return
    }
  } catch {
    /* user cancel */
  }
  try {
    await navigator.clipboard.writeText(url)
  } catch {
    /* ignore */
  }
}
</script>
