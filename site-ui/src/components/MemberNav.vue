<template>
  <nav class="member-nav" aria-label="会员菜单">
    <div class="member-nav__group">
      <div class="member-nav__heading">会员中心</div>
      <ul class="member-nav__list">
        <li>
          <RouterLink
            class="member-nav__link"
            :class="{ 'is-active': isActive('/member', true) }"
            to="/member"
          >
            <span class="member-nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="12" cy="8" r="3.2" />
                <path d="M5.5 19c1.2-3.2 3.4-4.8 6.5-4.8s5.3 1.6 6.5 4.8" stroke-linecap="round" />
              </svg>
            </span>
            会员中心
          </RouterLink>
        </li>
        <li>
          <RouterLink
            class="member-nav__link"
            :class="{ 'is-active': isActive('/member/profile') }"
            to="/member/profile"
          >
            <span class="member-nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="12" cy="8" r="3.2" />
                <path d="M5.5 19c1.2-3.2 3.4-4.8 6.5-4.8s5.3 1.6 6.5 4.8" stroke-linecap="round" />
                <path d="M17.5 7.5h3M19 6v3" stroke-linecap="round" />
              </svg>
            </span>
            个人资料
          </RouterLink>
        </li>
        <li>
          <RouterLink
            class="member-nav__link"
            :class="{ 'is-active': isActive('/member/changepwd') }"
            to="/member/changepwd"
          >
            <span class="member-nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="8.5" cy="14" r="3.2" />
                <path d="M11.2 11.8 18 5l2 2-1.2 1.2L20 9.4l-2 2-1.2-1.2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            修改密码
          </RouterLink>
        </li>
        <li>
          <a href="#" class="member-nav__link" @click.prevent="onLogout">
            <span class="member-nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7">
                <path d="M10 5H6.5A1.5 1.5 0 0 0 5 6.5v11A1.5 1.5 0 0 0 6.5 19H10" stroke-linecap="round" />
                <path d="M13 12h7M17.5 8.5 21 12l-3.5 3.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            注销
          </a>
        </li>
      </ul>
    </div>

    <div class="member-nav__group">
      <div class="member-nav__heading">内容管理</div>
      <ul class="member-nav__list">
        <li>
          <RouterLink
            class="member-nav__link"
            :class="{ 'is-active': isActive('/member/comments') }"
            to="/member/comments"
          >
            <span class="member-nav__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7">
                <path
                  d="M5 6.5A1.5 1.5 0 0 1 6.5 5h11A1.5 1.5 0 0 1 19 6.5v7a1.5 1.5 0 0 1-1.5 1.5H10l-3.5 3v-3H6.5A1.5 1.5 0 0 1 5 13.5v-7Z"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            我发表的评论
          </RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/store/user'

const route = useRoute()
const router = useRouter()
const user = useUserStore()

function isActive(path, exact = false) {
  if (exact) return route.path === path
  return route.path === path || route.path.startsWith(`${path}/`)
}

async function onLogout() {
  await user.logout()
  router.push('/member/login')
}
</script>
