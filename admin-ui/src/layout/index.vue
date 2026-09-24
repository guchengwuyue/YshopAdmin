<template>
  <a-layout class="shell">
    <Sidebar
      v-model:open-keys="openKeys"
      :collapsed="collapsed"
      :menu-items="menuItems"
      :selected-keys="selectedKeys"
      @menu-click="onMenuClick"
    />
    <div class="main-column">
      <Navbar
        :collapsed="collapsed"
        :breadcrumbs="breadcrumbs"
        :user-name="userStore.name"
        @toggle="collapsed = !collapsed"
        @refresh="onRefresh"
        @logout="userStore.logout"
      />
      <TagsView
        :tags="tags"
        :active-path="route.path"
        @select="(tag) => router.push(tag.path)"
        @close="closeTag"
      />
      <AppMain :view-key="viewKey" />
    </div>
  </a-layout>
</template>

<script setup>
import { computed, h, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePermissionStore } from '@/store/permission'
import { useUserStore } from '@/store/user'
import { findNode, findParents } from '@/utils/view'
import MenuIcon from '@/components/MenuIcon.vue'
import Sidebar from './components/Sidebar.vue'
import Navbar from './components/Navbar.vue'
import TagsView from './components/TagsView.vue'
import AppMain from './components/AppMain.vue'

const route = useRoute()
const router = useRouter()
const permissionStore = usePermissionStore()
const userStore = useUserStore()
const collapsed = ref(false)
const openKeys = ref([])
const viewKey = ref(0)
const tags = ref([{ path: '/index', title: '工作台', closable: false }])

function toItems(nodes) {
  return (nodes || []).filter((node) => !node.hidden).map((node) => {
    const children = node.children?.length ? toItems(node.children) : undefined
    return {
      key: node.path,
      icon: () => h(MenuIcon, { icon: node.meta?.icon }),
      label: node.meta?.title || node.name,
      children: children?.length ? children : undefined,
    }
  })
}

const menuItems = computed(() => [
  { key: '/index', icon: () => h(MenuIcon, { icon: 'fa fa-home' }), label: '工作台' },
  ...toItems(permissionStore.routers),
])

const selectedKeys = computed(() => [route.path])

const breadcrumbs = computed(() => {
  if (route.path === '/index' || route.path === '/') {
    return ['工作台']
  }
  const parents = findParents(permissionStore.routers, route.path) || []
  const titles = parents.map((path) => {
    const node = findNode(permissionStore.routers, path)
    return node?.meta?.title || node?.name || path
  })
  const current = route.meta?.title || '页面'
  return [...titles, current]
})

watch(
  () => route.path,
  (path) => {
    if (path === '/login') {
      return
    }
    const parents = findParents(permissionStore.routers, path) || []
    openKeys.value = Array.from(new Set([...openKeys.value, ...parents]))
    if (!tags.value.some((tag) => tag.path === path)) {
      tags.value.push({
        path,
        title: path === '/index' ? '工作台' : route.meta?.title || '页面',
        closable: path !== '/index',
      })
    } else if (path === '/index') {
      const home = tags.value.find((tag) => tag.path === '/index')
      if (home) {
        home.title = '工作台'
      }
    }
  },
  { immediate: true }
)

function onMenuClick({ key }) {
  const node = findNode(permissionStore.routers, key)
  if (node?.meta?.link) {
    window.open(node.meta.link, '_blank')
    return
  }
  if (key !== route.path) {
    router.push(key)
  }
}

function closeTag(tag) {
  tags.value = tags.value.filter((item) => item.path !== tag.path)
  if (route.path === tag.path) {
    const last = tags.value[tags.value.length - 1]
    router.push(last ? last.path : '/index')
  }
}

function onRefresh() {
  viewKey.value += 1
}
</script>

<style scoped>
.shell {
  height: 100vh;
  overflow: hidden;
}

.main-column {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f0f2f5;
}
</style>
