<template>
  <div class="dashboard">
    <a-card class="welcome-card" :bordered="false">
      <div class="welcome-body">
        <div class="welcome-left">
          <h2 class="welcome-title">
            <span class="welcome-emoji" aria-hidden="true">😊</span>
            欢迎回来，{{ displayName }}!
          </h2>
          <p class="welcome-sub">今天是个美好的一天，开始您的工作吧～</p>
          <div class="welcome-meta">
            <span>
              <ClockCircleOutlined />
              上次登录: {{ lastLogin }}
            </span>
            <span>
              <EnvironmentOutlined />
              登录地点: {{ loginIp }}
            </span>
          </div>
        </div>
        <div class="welcome-actions">
          <a-button type="primary" @click="go('/system/user')">
            <template #icon><UserOutlined /></template>
            用户管理
          </a-button>
          <a-button @click="go('/system/config')">
            <template #icon><SettingOutlined /></template>
            系统设置
          </a-button>
        </div>
      </div>
    </a-card>

    <a-row :gutter="16" class="stat-row">
      <a-col v-for="item in stats" :key="item.key" :xs="24" :sm="12" :lg="6">
        <a-card class="stat-card" :bordered="false">
          <div class="stat-main">
            <div class="stat-icon" :style="{ background: item.bg, color: item.color }">
              <component :is="item.icon" />
            </div>
            <div class="stat-info">
              <div class="stat-label">{{ item.label }}</div>
              <div class="stat-value">{{ item.value }}</div>
            </div>
          </div>
          <div class="stat-trend" :class="item.up ? 'up' : 'down'">
            <CaretUpOutlined v-if="item.up" />
            <CaretDownOutlined v-else />
            {{ item.trend }}% 较上周
          </div>
        </a-card>
      </a-col>
    </a-row>

    <a-row :gutter="16" class="main-row">
      <a-col :xs="24" :lg="16">
        <a-card class="panel-card" :bordered="false">
          <template #title>
            <span class="panel-title">常用功能</span>
          </template>
          <template #extra>
            <a class="panel-link" @click.prevent="go('/system/addon')">
              <GlobalOutlined /> 访问全部
            </a>
          </template>
          <div class="site-list">
            <div v-for="site in sites" :key="site.title" class="site-item" @click="go(site.path)">
              <div class="site-icon" :style="{ background: site.bg, color: site.color }">
                <component :is="site.icon" />
              </div>
              <div class="site-body">
                <div class="site-head">
                  <span class="site-name">{{ site.title }}</span>
                  <span class="site-status">
                    <span class="dot" />
                    {{ site.status }}
                  </span>
                </div>
                <p class="site-desc">{{ site.desc }}</p>
                <div class="site-tags">
                  <a-tag v-for="tag in site.tags" :key="tag.label" :color="tag.color">{{ tag.label }}</a-tag>
                </div>
              </div>
            </div>
          </div>
        </a-card>
      </a-col>

      <a-col :xs="24" :lg="8">
        <a-card class="panel-card sys-card" :bordered="false">
          <template #title>
            <span class="panel-title">系统信息</span>
          </template>
          <div class="sys-list">
            <div v-for="row in sysInfo" :key="row.label" class="sys-row">
              <span class="sys-label">{{ row.label }}</span>
              <span class="sys-value">{{ row.value }}</span>
            </div>
          </div>
        </a-card>
      </a-col>
    </a-row>
    <a-row :gutter="24" class="main-row">
      <a-col :xs="24" :lg="24">
        <a-card class="panel-card notice-card" :bordered="false">
          <template #title>
            <span class="panel-title">最新通知</span>
          </template>
          <template #extra>
            <a class="panel-link" @click.prevent="go('/system/notice')">查看全部</a>
          </template>
          <ul class="notice-list">
            <li v-if="!notices.length" class="notice-item">
              <div class="notice-content">
                <div class="notice-text">暂无通知公告</div>
              </div>
            </li>
            <li v-for="(n, idx) in notices" :key="idx" class="notice-item">
              <span class="notice-dot" :class="n.type" />
              <div class="notice-content">
                <div class="notice-text">
                  <span class="notice-type">{{ n.typeLabel }}</span>
                  {{ n.title }}
                </div>
                <div class="notice-time">{{ n.time }}</div>
              </div>
            </li>
          </ul>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  BookOutlined,
  CaretDownOutlined,
  CaretUpOutlined,
  ClockCircleOutlined,
  CodeOutlined,
  EnvironmentOutlined,
  GlobalOutlined,
  MenuOutlined,
  SettingOutlined,
  TeamOutlined,
  ToolOutlined,
  UserOutlined,
  WifiOutlined,
} from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { listUser } from '@/api/system/user'
import { listRole } from '@/api/system/role'
import { listMenu } from '@/api/system/menu'
import { listNotice } from '@/api/system/notice'
import { listOnline } from '@/api/monitor'

const router = useRouter()
const userStore = useUserStore()

const displayName = computed(() => {
  const u = userStore.user || {}
  return u.userName || u.loginName || userStore.name || '管理员'
})

const lastLogin = computed(() => {
  const u = userStore.user || {}
  return u.loginDate || u.login_date || '—'
})

const loginIp = computed(() => {
  const u = userStore.user || {}
  return u.loginIp || u.login_ip || '—'
})

const stats = ref([
  { key: 'user', label: '用户总数', value: '—', trend: '5.2', up: true, icon: UserOutlined, color: '#1677ff', bg: 'rgba(22,119,255,.12)' },
  { key: 'role', label: '角色数量', value: '—', trend: '2.1', up: true, icon: TeamOutlined, color: '#52c41a', bg: 'rgba(82,196,26,.12)' },
  { key: 'menu', label: '菜单数量', value: '—', trend: '1.5', up: true, icon: MenuOutlined, color: '#faad14', bg: 'rgba(250,173,20,.12)' },
  { key: 'online', label: '在线用户', value: '—', trend: '0.8', up: false, icon: WifiOutlined, color: '#ff4d4f', bg: 'rgba(255,77,79,.12)' },
])

const sites = [
  {
    title: 'YshopAdmin CMS',
    status: '在线',
    desc: '栏目文档 · 主题皮肤 · 内容站点',
    path: '/system/addon',
    icon: BookOutlined,
    color: '#722ed1',
    bg: 'rgba(114,46,209,.12)',
    tags: [
      { label: 'Vue', color: 'success' },
      { label: 'ThinkPHP', color: 'orange' },
      { label: 'MySQL', color: 'blue' },
    ],
  },
  {
    title: '权限与组织',
    status: '在线',
    desc: '用户 · 角色 · 菜单 · 部门岗位',
    path: '/system/user',
    icon: ToolOutlined,
    color: '#1677ff',
    bg: 'rgba(22,119,255,.12)',
    tags: [
      { label: 'Ant Design', color: 'blue' },
      { label: 'Pinia', color: 'cyan' },
      { label: 'Vite', color: 'purple' },
    ],
  },
  {
    title: '支付 / 微信',
    status: '在线',
    desc: '系统内置能力 · 配置与回调',
    path: '/pay/config',
    icon: CodeOutlined,
    color: '#52c41a',
    bg: 'rgba(82,196,26,.12)',
    tags: [
      { label: 'Pay', color: 'orange' },
      { label: 'WeChat', color: 'default' },
      { label: 'API', color: 'default' },
    ],
  },
]

const notices = ref([])

const serverTime = ref(formatNow())
const sysInfo = computed(() => [
  { label: '系统版本', value: 'YshopAdmin v2.0.0' },
  { label: '基于框架', value: 'Vue 3.5 + Ant Design Vue' },
  { label: '构建工具', value: 'Vite 6.x' },
  { label: 'UI 组件库', value: 'Ant Design Vue 4' },
  { label: '后端框架', value: 'ThinkPHP 8 + ORM' },
  { label: '服务器时间', value: serverTime.value },
])

let timer = null

function formatNow() {
  const d = new Date()
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`
}

function go(path) {
  if (!path || path === router.currentRoute.value.path) {
    return
  }
  router.push(path).catch(() => {})
}

function setStat(key, value) {
  const row = stats.value.find((s) => s.key === key)
  if (row) {
    row.value = value
  }
}

async function safeTotal(fn, perm) {
  if (perm && !userStore.hasPerm(perm) && !userStore.hasPerm('*:*:*')) {
    return 0
  }
  try {
    const res = await fn({ pageNum: 1, pageSize: 1 })
    return Number(res.total ?? 0)
  } catch (e) {
    return 0
  }
}

async function loadStats() {
  const [userTotal, roleTotal, onlineTotal] = await Promise.all([
    safeTotal(listUser, 'system:user:list'),
    safeTotal(listRole, 'system:role:list'),
    safeTotal(listOnline, 'monitor:online:list'),
  ])
  setStat('user', userTotal)
  setStat('role', roleTotal)
  setStat('online', onlineTotal)

  try {
    if (userStore.hasPerm('system:menu:list') || userStore.hasPerm('*:*:*')) {
      const res = await listMenu({})
      const list = Array.isArray(res.data) ? res.data : []
      setStat('menu', list.length)
    } else {
      setStat('menu', 0)
    }
  } catch (e) {
    setStat('menu', 0)
  }
}

async function loadNotices() {
  if (!userStore.hasPerm('system:notice:list') && !userStore.hasPerm('*:*:*')) {
    notices.value = []
    return
  }
  try {
    const res = await listNotice({ pageNum: 1, pageSize: 5, status: '0' })
    const rows = res.rows || []
    notices.value = rows.map((row) => {
      const isAnnounce = String(row.noticeType) === '2'
      return {
        type: isAnnounce ? 'warn' : 'info',
        typeLabel: isAnnounce ? '公告' : '通知',
        title: row.noticeTitle || '',
        time: row.createTime || '',
      }
    })
  } catch (e) {
    notices.value = []
  }
}

onMounted(() => {
  loadStats()
  loadNotices()
  timer = window.setInterval(() => {
    serverTime.value = formatNow()
  }, 1000)
})

onUnmounted(() => {
  if (timer) {
    window.clearInterval(timer)
  }
})
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.welcome-card,
.stat-card,
.panel-card {
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03), 0 4px 12px rgba(0, 0, 0, 0.04);
}

.welcome-card :deep(.ant-card-body) {
  padding: 24px 28px;
}

.welcome-body {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.welcome-title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: rgba(0, 0, 0, 0.88);
  line-height: 1.4;
}

.welcome-emoji {
  margin-right: 6px;
}

.welcome-sub {
  margin: 8px 0 16px;
  color: rgba(0, 0, 0, 0.45);
  font-size: 14px;
}

.welcome-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  color: rgba(0, 0, 0, 0.45);
  font-size: 13px;
}

.welcome-meta span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.welcome-actions {
  display: flex;
  gap: 12px;
  flex-shrink: 0;
}

.stat-row {
  margin: 0 !important;
}

.stat-card :deep(.ant-card-body) {
  padding: 20px;
}

.stat-main {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}

.stat-label {
  color: rgba(0, 0, 0, 0.45);
  font-size: 13px;
}

.stat-value {
  margin-top: 4px;
  font-size: 28px;
  font-weight: 700;
  line-height: 1.2;
  color: rgba(0, 0, 0, 0.88);
}

.stat-trend {
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  font-size: 13px;
}

.stat-trend.up {
  color: #52c41a;
}

.stat-trend.down {
  color: #ff4d4f;
}

.main-row {
  margin: 0 !important;
  align-items: stretch;
}

.main-row > .ant-col {
  display: flex;
  flex-direction: column;
}

.main-row > .ant-col > .panel-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.main-row > .ant-col > .panel-card :deep(.ant-card-body) {
  flex: 1;
}

.panel-card :deep(.ant-card-head) {
  min-height: 52px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.panel-title {
  position: relative;
  padding-left: 12px;
  font-weight: 600;
}

.panel-title::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 14px;
  border-radius: 2px;
  background: #1677ff;
}

.panel-link {
  color: rgba(0, 0, 0, 0.45);
  font-size: 13px;
}

.panel-link:hover {
  color: #1677ff;
}

.site-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.site-item {
  display: flex;
  gap: 16px;
  padding: 16px 8px;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.2s;
}

.site-item:hover {
  background: rgba(0, 0, 0, 0.02);
}

.site-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}

.site-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.site-name {
  font-weight: 600;
  font-size: 15px;
  color: rgba(0, 0, 0, 0.88);
}

.site-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #52c41a;
}

.site-status .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #52c41a;
}

.site-desc {
  margin: 0 0 8px;
  color: rgba(0, 0, 0, 0.45);
  font-size: 13px;
}

.site-tags :deep(.ant-tag) {
  margin-inline-end: 6px;
}

.notice-card :deep(.ant-card-body) {
  padding-top: 8px;
  padding-bottom: 8px;
}

.notice-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.notice-item {
  display: flex;
  gap: 10px;
  padding: 12px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.notice-item:last-child {
  border-bottom: none;
}

.notice-dot {
  width: 8px;
  height: 8px;
  margin-top: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.notice-dot.info {
  background: #1677ff;
}

.notice-dot.warn {
  background: #fa8c16;
}

.notice-text {
  font-size: 13px;
  color: rgba(0, 0, 0, 0.75);
  line-height: 1.5;
}

.notice-type {
  color: rgba(0, 0, 0, 0.45);
  margin-right: 4px;
}

.notice-time {
  margin-top: 4px;
  font-size: 12px;
  color: rgba(0, 0, 0, 0.35);
}

.sys-card {
  margin-top: 0;
}

.sys-list {
  display: flex;
  flex-direction: column;
}

.sys-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  font-size: 13px;
}

.sys-row:last-child {
  border-bottom: none;
}

.sys-label {
  color: rgba(0, 0, 0, 0.45);
  flex-shrink: 0;
}

.sys-value {
  color: rgba(0, 0, 0, 0.75);
  text-align: right;
}

@media (max-width: 768px) {
  .welcome-title {
    font-size: 18px;
  }

  .stat-value {
    font-size: 22px;
  }
}
</style>
