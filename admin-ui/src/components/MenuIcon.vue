<template>
  <component :is="iconComp" />
</template>

<script setup>
import { computed } from 'vue'
import { AppstoreOutlined } from '@ant-design/icons-vue'
import { iconComponents } from '@/components/icon/icons.js'

const faMap = {
  'fa-home': 'HomeOutlined',
  'fa-gear': 'SettingOutlined',
  'fa-cogs': 'SettingOutlined',
  'fa-cog': 'SettingOutlined',
  'fa-video-camera': 'VideoCameraOutlined',
  'fa-bars': 'MenuOutlined',
  'fa-user-o': 'UserOutlined',
  'fa-user': 'UserOutlined',
  'fa-user-secret': 'TeamOutlined',
  'fa-users': 'TeamOutlined',
  'fa-th-list': 'UnorderedListOutlined',
  'fa-list': 'UnorderedListOutlined',
  'fa-outdent': 'ApartmentOutlined',
  'fa-sitemap': 'ApartmentOutlined',
  'fa-address-card-o': 'IdcardOutlined',
  'fa-address-card': 'IdcardOutlined',
  'fa-bookmark-o': 'BookOutlined',
  'fa-bookmark': 'BookOutlined',
  'fa-sun-o': 'BulbOutlined',
  'fa-bullhorn': 'NotificationOutlined',
  'fa-pencil-square-o': 'FormOutlined',
  'fa-pencil': 'EditOutlined',
  'fa-user-circle': 'UserOutlined',
  'fa-tasks': 'ScheduleOutlined',
  'fa-server': 'CloudServerOutlined',
  'fa-cube': 'DatabaseOutlined',
  'fa-cubes': 'AppstoreOutlined',
  'fa-wpforms': 'FormOutlined',
  'fa-code': 'CodeOutlined',
  'fa-address-book': 'ProfileOutlined',
  'fa-file-image-o': 'FileImageOutlined',
  'fa-file-text-o': 'FileTextOutlined',
  'fa-file-o': 'FileOutlined',
  'fa-folder-o': 'FolderOutlined',
  'fa-folder-open-o': 'FolderOpenOutlined',
  'fa-location-arrow': 'LinkOutlined',
  'fa-dashboard': 'DashboardOutlined',
  'fa-tachometer': 'DashboardOutlined',
  'fa-wechat': 'WechatOutlined',
  'fa-weixin': 'WechatOutlined',
  'fa-envelope': 'MailOutlined',
  'fa-bell': 'BellOutlined',
  'fa-search': 'SearchOutlined',
  'fa-lock': 'LockOutlined',
  'fa-key': 'KeyOutlined',
  'fa-shopping-cart': 'ShoppingCartOutlined',
  'fa-cloud': 'CloudOutlined',
  'fa-database': 'DatabaseOutlined',
  'fa-wrench': 'ToolOutlined',
  'fa-plug': 'ApiOutlined',
}

const antNameRe = /^[A-Z][a-zA-Z0-9]*(Outlined|Filled|TwoTone)$/

const props = defineProps({
  icon: { type: String, default: '' },
})

const iconComp = computed(() => {
  const raw = String(props.icon || '').trim()
  if (!raw) return AppstoreOutlined

  if (antNameRe.test(raw) && iconComponents[raw]) {
    return iconComponents[raw]
  }

  const token = raw.split(/\s+/).filter((part) => part.startsWith('fa-')).pop()
  if (token) {
    const antName = faMap[token]
    if (antName && iconComponents[antName]) return iconComponents[antName]
  }

  return AppstoreOutlined
})
</script>
