<template>
  <BContainer class="member-page py-4 py-lg-5">
    <BRow class="g-4">
      <BCol md="3">
        <MemberNav />
      </BCol>
      <BCol md="9">
        <div v-motion="pageEnter" class="member-panel">
          <div class="member-panel__head">
            <h1 class="member-panel__title">会员中心</h1>
            <RouterLink class="btn btn-primary member-panel__action" to="/member/profile">
              <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 20h4l10.5-10.5a2 2 0 0 0-2.8-2.8L5.2 17.2 4 20Z" stroke-linejoin="round" />
                <path d="M13.5 6.5 17.5 10.5" stroke-linecap="round" />
              </svg>
              个人资料
            </RouterLink>
          </div>

          <div class="member-home__profile">
            <div class="member-avatar" :class="{ 'has-img': !!avatarUrl }" aria-hidden="true">
              <img v-if="avatarUrl" :src="avatarUrl" alt="" />
              <span v-else>{{ letter }}</span>
            </div>
            <div class="member-home__meta">
              <div class="member-home__name">{{ displayName }}</div>
              <div class="member-home__bio">{{ bio }}</div>
            </div>
          </div>

          <div class="member-home__stats">
            <div class="member-home__stat">
              <span class="member-home__label">余额</span>
              <span class="member-home__value">{{ money }}</span>
            </div>
            <div class="member-home__stat">
              <span class="member-home__label">积分</span>
              <span class="member-home__value">{{ score }}</span>
            </div>
            <div class="member-home__stat">
              <span class="member-home__label">连续登录</span>
              <span class="member-home__value">{{ successions }} 天</span>
            </div>
            <div class="member-home__stat">
              <span class="member-home__label">最长连续登录</span>
              <span class="member-home__value">{{ maxSuccessions }} 天</span>
            </div>
            <div class="member-home__stat">
              <span class="member-home__label">登录时间</span>
              <span class="member-home__value">{{ loginTime }}</span>
            </div>
            <div class="member-home__stat">
              <span class="member-home__label">最后登录</span>
              <span class="member-home__value">{{ prevTime }}</span>
            </div>
          </div>
        </div>
      </BCol>
    </BRow>
  </BContainer>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useUserStore } from '@/store/user'
import { useSiteStore } from '@/store/site'
import MemberNav from '@/components/MemberNav.vue'
import { pageEnter } from '@/motion/presets'

const user = useUserStore()
useSiteStore().applySeo({ title: '会员中心' })
onMounted(() => user.fetchInfo())

const m = computed(() => user.member || {})

const displayName = computed(() => m.value.username || m.value.nickname || user.displayName || '-')

const bio = computed(() => {
  const text = String(m.value.bio || '').trim()
  return text || '这个人很懒,啥也没写'
})

const letter = computed(() => {
  const name = displayName.value
  if (name && name !== '-') return name.charAt(0).toUpperCase()
  return m.value.letter ? String(m.value.letter) : 'U'
})

const avatarUrl = computed(() => String(m.value.avatar || '').trim())

const money = computed(() => {
  const v = m.value.money
  if (v === null || v === undefined || v === '') return '0.00'
  const n = Number(v)
  return Number.isFinite(n) ? n.toFixed(2) : String(v)
})

const score = computed(() => Number(m.value.score ?? 0) || 0)
const successions = computed(() => Number(m.value.successions ?? 0) || 0)
const maxSuccessions = computed(() => Number(m.value.maxsuccessions ?? m.value.maxSuccessions ?? 0) || 0)

const loginTime = computed(
  () => m.value.logintimeText || m.value.loginTimeText || m.value.logintime_text || m.value.createTime || '-'
)
const prevTime = computed(
  () => m.value.prevtimeText || m.value.prevTimeText || m.value.prevtime_text || '-'
)
</script>
