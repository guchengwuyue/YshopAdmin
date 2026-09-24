<template>
  <a-layout-sider
    class="sidebar"
    :collapsed="collapsed"
    collapsible
    :trigger="null"
    theme="light"
    width="220"
    collapsed-width="64"
  >
    <div class="logo">
      <span class="logo-mark">
        <AppstoreOutlined />
      </span>
      <span v-show="!collapsed" class="logo-text">YshopAdmin</span>
    </div>
    <div class="menu-wrap">
      <a-menu
        theme="light"
        mode="inline"
        :inline-collapsed="collapsed"
        :items="menuItems"
        :selected-keys="selectedKeys"
        :open-keys="openKeys"
        @openChange="onOpenChange"
        @click="onMenuClick"
      />
    </div>
  </a-layout-sider>
</template>

<script setup>
import { AppstoreOutlined } from '@ant-design/icons-vue'

defineProps({
  collapsed: { type: Boolean, default: false },
  menuItems: { type: Array, default: () => [] },
  selectedKeys: { type: Array, default: () => [] },
  openKeys: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:openKeys', 'menuClick'])

function onOpenChange(keys) {
  emit('update:openKeys', keys)
}

function onMenuClick(info) {
  emit('menuClick', info)
}
</script>

<style scoped>
.sidebar {
  height: 100%;
  background: #fff !important;
  border-right: 1px solid #f0f0f0;
  display: flex !important;
  flex-direction: column;
}

.sidebar :deep(.ant-layout-sider-children) {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  height: 56px;
  padding: 0 16px;
  flex-shrink: 0;
  border-bottom: 1px solid #f0f0f0;
}

.logo-mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: #1677ff;
  color: #fff;
  font-size: 16px;
  flex-shrink: 0;
}

.logo-text {
  color: #1f1f1f;
  font-weight: 600;
  font-size: 16px;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

.menu-wrap {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 8px 0;
}

.menu-wrap :deep(.ant-menu) {
  border-inline-end: none !important;
  background: transparent;
}

.menu-wrap :deep(.ant-menu-item),
.menu-wrap :deep(.ant-menu-submenu-title) {
  margin-inline: 8px;
  width: calc(100% - 16px);
  border-radius: 6px;
}

.menu-wrap :deep(.ant-menu-item-selected) {
  background: #e6f4ff !important;
  color: #1677ff !important;
}

.menu-wrap :deep(.ant-menu-item-selected .anticon),
.menu-wrap :deep(.ant-menu-item-selected .ant-menu-title-content) {
  color: #1677ff !important;
}

.menu-wrap :deep(.ant-menu-submenu-selected > .ant-menu-submenu-title),
.menu-wrap :deep(.ant-menu-submenu-open > .ant-menu-submenu-title) {
  color: #1677ff;
}

.menu-wrap :deep(.ant-menu-submenu-selected > .ant-menu-submenu-title .anticon),
.menu-wrap :deep(.ant-menu-submenu-open > .ant-menu-submenu-title .anticon) {
  color: #1677ff;
}
</style>
