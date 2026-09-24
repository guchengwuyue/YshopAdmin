<template>
  <header class="site-header" :class="{ 'is-scrolled': scrolled, 'is-open': mobileOpen }">
    <div class="container site-header-inner">
      <RouterLink to="/" class="site-brand" @click="closeMobile">
        <img v-if="site.sitelogo" :src="site.sitelogo" alt="" class="site-logo" />
        <span class="site-brand-text">{{ site.sitename }}</span>
      </RouterLink>

      <button
        type="button"
        class="site-nav-toggle"
        :aria-expanded="mobileOpen"
        aria-label="菜单"
        @click="mobileOpen = !mobileOpen"
      >
        <span /><span /><span />
      </button>

      <nav class="site-nav" :class="{ 'is-open': mobileOpen }">
        <ul class="site-nav-list">
          <li :class="{ active: route.path === '/' }">
            <RouterLink to="/" class="site-nav-link" @click="closeMobile">首页</RouterLink>
          </li>
          <li
            v-for="item in site.nav"
            :key="item.id"
            :class="{ active: isNavActive(item), 'has-children': item.children?.length }"
          >
            <component
              :is="navTag(item)"
              v-bind="navBind(item)"
              class="site-nav-link"
              @click="onTopNavClick(item, $event)"
            >
              {{ item.name }}
            </component>
            <ul v-if="item.children?.length" class="site-dropdown">
              <li
                v-for="child in item.children"
                :key="child.id"
                :class="{ active: isNavActive(child), 'has-children': child.children?.length }"
              >
                <component
                  :is="navTag(child)"
                  v-bind="navBind(child)"
                  class="site-dropdown-link"
                  @click="onSubNavClick(child, $event)"
                >
                  <span>{{ child.name }}</span>
                  <i class="site-dropdown-arrow" aria-hidden="true" />
                </component>
                <ul v-if="child.children?.length" class="site-dropdown site-dropdown-sub">
                  <li
                    v-for="grand in child.children"
                    :key="grand.id"
                    :class="{ active: isNavActive(grand) }"
                  >
                    <component
                      :is="navTag(grand)"
                      v-bind="navBind(grand)"
                      class="site-dropdown-link"
                      @click="closeMobile"
                    >
                      <span>{{ grand.name }}</span>
                      <i class="site-dropdown-arrow" aria-hidden="true" />
                    </component>
                  </li>
                </ul>
              </li>
            </ul>
          </li>
        </ul>

        <ul class="site-nav-tools">
          <li :class="{ active: route.path === '/s' || route.path.startsWith('/s/') }">
            <RouterLink to="/s" class="site-nav-link site-nav-search" title="搜索" aria-label="搜索" @click="closeMobile">
              <svg class="site-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="M20 20l-3.5-3.5" stroke-linecap="round" />
              </svg>
            </RouterLink>
          </li>
          <template v-if="user.isLogin">
            <li
              class="site-user has-children"
              :class="{ active: route.path.startsWith('/member'), 'is-expanded': userMenuOpen }"
            >
              <a
                href="#"
                class="site-nav-link site-user-trigger"
                :aria-expanded="userMenuOpen"
                @click.prevent="onUserMenuClick"
              >
                <span class="site-user-name">{{ user.member?.username || user.displayName || '会员' }}</span>
                <i class="site-user-caret" aria-hidden="true" />
              </a>
              <ul class="site-dropdown site-user-menu">
                <li :class="{ active: route.path === '/member' }">
                  <RouterLink to="/member" class="site-dropdown-link" @click="closeUserMenu">会员中心</RouterLink>
                </li>
                <li :class="{ active: route.path.startsWith('/member/archives') }">
                  <RouterLink to="/member/archives" class="site-dropdown-link" @click="closeUserMenu">我的文档</RouterLink>
                </li>
                <li :class="{ active: route.path.startsWith('/member/comments') }">
                  <RouterLink to="/member/comments" class="site-dropdown-link" @click="closeUserMenu">我的评论</RouterLink>
                </li>
                <li>
                  <a href="#" class="site-dropdown-link" @click.prevent="onLogout">退出</a>
                </li>
              </ul>
            </li>
          </template>
          <template v-else>
            <li class="site-auth" :class="{ active: route.path === '/member/login' }">
              <RouterLink to="/member/login" class="site-auth-login" @click="closeMobile">登录</RouterLink>
            </li>
            <li class="site-auth" :class="{ active: route.path === '/member/register' }">
              <RouterLink to="/member/register" class="site-auth-register" @click="closeMobile">注册</RouterLink>
            </li>
          </template>
        </ul>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSiteStore } from '@/store/site'
import { useUserStore } from '@/store/user'

const site = useSiteStore()
const user = useUserStore()
const route = useRoute()
const router = useRouter()

const scrolled = ref(false)
const mobileOpen = ref(false)
const userMenuOpen = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 8
}

function closeMobile() {
  mobileOpen.value = false
  userMenuOpen.value = false
}

function closeUserMenu() {
  userMenuOpen.value = false
  closeMobile()
}

function onUserMenuClick(e) {
  if (!isMobileNav()) return
  const li = e.currentTarget?.closest('li')
  if (!li) return
  e.preventDefault()
  const open = !li.classList.contains('is-expanded')
  li.parentElement?.querySelectorAll(':scope > .is-expanded').forEach((el) => {
    if (el !== li) el.classList.remove('is-expanded')
  })
  li.classList.toggle('is-expanded', open)
  userMenuOpen.value = open
}

watch(
  () => route.fullPath,
  () => closeMobile(),
)

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
  if (user.token && !user.member) {
    user.fetchInfo()
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})

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

function navTag(item) {
  if (isExternal(item?.url)) return 'a'
  if (internalTo(item?.url)) return 'RouterLink'
  return 'span'
}

function navBind(item) {
  if (isExternal(item?.url)) {
    return {
      href: item.url,
      target: item.isnewtab ? '_blank' : undefined,
      rel: item.isnewtab ? 'noopener' : undefined,
    }
  }
  const to = internalTo(item?.url)
  if (to) {
    return {
      to,
      target: item.isnewtab ? '_blank' : undefined,
    }
  }
  return {}
}

function isMobileNav() {
  return window.matchMedia('(max-width: 991.98px)').matches
}

function toggleExpand(e, item) {
  if (!item.children?.length || !isMobileNav()) return false
  const li = e.currentTarget?.closest('li')
  if (!li || li.classList.contains('is-expanded')) return false
  e.preventDefault()
  li.parentElement?.querySelectorAll(':scope > .is-expanded').forEach((el) => {
    if (el !== li) el.classList.remove('is-expanded')
  })
  li.classList.add('is-expanded')
  return true
}

function onTopNavClick(item, e) {
  if (toggleExpand(e, item)) return
  closeMobile()
}

function onSubNavClick(item, e) {
  if (toggleExpand(e, item)) return
  closeMobile()
}

function isNavActive(item) {
  if (!item) return false
  if (item.isActive || item.is_active) return true
  const path = route.path.replace(/\/+$/, '') || '/'
  const url = normalizePath(item.url)
  if (url && url !== '/' && (path === url || path.startsWith(`${url}/`))) return true
  if (url && url === '/' && path === '/') return true
  return (item.children || []).some((child) => isNavActive(child))
}

async function onLogout() {
  await user.logout()
  closeMobile()
  router.push('/')
}
</script>
