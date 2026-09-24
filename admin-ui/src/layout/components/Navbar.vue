<template>
  <div class="navbar">
    <div class="navbar-left">
      <span class="nav-icon" @click="emit('toggle')">
        <MenuUnfoldOutlined v-if="collapsed" />
        <MenuFoldOutlined v-else />
      </span>
      <a-breadcrumb class="breadcrumb">
        <a-breadcrumb-item v-for="(item, idx) in breadcrumbs" :key="`${item}-${idx}`">
          {{ item }}
        </a-breadcrumb-item>
      </a-breadcrumb>
    </div>
    <div class="navbar-right">
    <!--
      <span class="nav-icon" title="搜索" @click="onPlaceholder('搜索')">
        <SearchOutlined />
      </span>
     
      <a-badge :count="3" size="small" class="nav-badge">
        <span class="nav-icon" title="通知" @click="onPlaceholder('通知')">
          <BellOutlined />
        </span>
      </a-badge>
      <span class="nav-icon" title="消息" @click="onPlaceholder('消息')">
        <MailOutlined />
      </span>
     -->
      <span class="nav-icon" title="全屏" @click="toggleFullscreen">
        <FullscreenExitOutlined v-if="isFullscreen" />
        <FullscreenOutlined v-else />
      </span>
      <span class="nav-icon" title="刷新" @click="emit('refresh')">
        <ReloadOutlined />
      </span>
      <!--
      <span class="nav-icon" title="设置" @click="onPlaceholder('设置')">
        <SettingOutlined />
      </span>
       -->
      <a-dropdown>
        <a class="user" @click.prevent>
          <a-avatar :size="28" class="user-avatar">
            <template #icon><UserOutlined /></template>
          </a-avatar>
          <span class="user-name">{{ userName || '管理员' }}</span>
        </a>
        <template #overlay>
          <a-menu>
            <a-menu-item @click="onPlaceholder('个人中心')">个人中心</a-menu-item>
            <a-menu-divider />
            <a-menu-item @click="emit('logout')">退出登录</a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { message } from 'ant-design-vue'
import {
  BellOutlined,
  FullscreenExitOutlined,
  FullscreenOutlined,
  MailOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  ReloadOutlined,
  SearchOutlined,
  SettingOutlined,
  UserOutlined,
} from '@ant-design/icons-vue'

defineProps({
  collapsed: { type: Boolean, default: false },
  breadcrumbs: { type: Array, default: () => [] },
  userName: { type: String, default: '' },
})

const emit = defineEmits(['toggle', 'refresh', 'logout'])

const isFullscreen = ref(false)

function syncFullscreen() {
  isFullscreen.value = !!document.fullscreenElement
}

function toggleFullscreen() {
  if (document.fullscreenElement) {
    document.exitFullscreen?.()
  } else {
    document.documentElement.requestFullscreen?.()
  }
}

function onPlaceholder(label) {
  message.info(`${label}功能开发中`)
}

onMounted(() => {
  document.addEventListener('fullscreenchange', syncFullscreen)
  syncFullscreen()
})

onUnmounted(() => {
  document.removeEventListener('fullscreenchange', syncFullscreen)
})
</script>

<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 48px;
  padding: 0 16px;
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
  flex-shrink: 0;
}

.navbar-left,
.navbar-right {
  display: flex;
  align-items: center;
  gap: 4px;
  min-width: 0;
}

.navbar-left {
  flex: 1;
  gap: 12px;
}

.nav-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 6px;
  color: #595959;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}

.nav-icon:hover {
  background: #f5f5f5;
  color: #1677ff;
}

.nav-badge {
  line-height: 1;
}

.breadcrumb {
  min-width: 0;
  overflow: hidden;
}

.user {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-left: 8px;
  padding: 4px 8px;
  border-radius: 6px;
  color: inherit;
  cursor: pointer;
}

.user:hover {
  background: #f5f5f5;
}

.user-avatar {
  background: #1677ff;
  flex-shrink: 0;
}

.user-name {
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #262626;
}
</style>
