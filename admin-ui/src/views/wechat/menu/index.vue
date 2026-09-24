<template>
  <div class="page-card menu-page">
    <div class="menu-setting">
      <!-- 左侧手机预览 -->
      <div class="mobile-preview">
        <div class="mobile-head">
          <span class="mobile-back">返回</span>
          <div class="mobile-title">我的网站</div>
          <span class="mobile-user" />
        </div>
        <ul class="menu-list">
          <li
            v-for="(item, mi) in menus"
            :key="'m-' + mi"
            class="menu-item"
            :class="{ current: isMainSelected(mi) }"
            :style="{ width: menuItemWidth }"
            draggable="true"
            @dragstart="onMainDragStart(mi, $event)"
            @dragover.prevent
            @drop="onMainDrop(mi)"
            @click.stop="selectMain(mi)"
          >
            <a href="javascript:;" class="menu-link">
              <i v-if="hasSub(item)" class="icon-menu-dot" />
              <span class="title">{{ displayName(item.name) }}</span>
            </a>
            <div v-show="openMainIndex === mi" class="sub-menu-box" @click.stop>
              <ul class="sub-menu-list">
                <li
                  v-for="(sub, si) in item.sub_button || []"
                  :key="'s-' + mi + '-' + si"
                  class="sub-menu-item"
                  :class="{ current: isSubSelected(mi, si) }"
                  draggable="true"
                  @dragstart="onSubDragStart(mi, si, $event)"
                  @dragover.prevent
                  @drop.stop="onSubDrop(mi, si)"
                  @click.stop="selectSub(mi, si)"
                >
                  <a href="javascript:;">
                    <span class="sub-title">{{ displayName(sub.name) }}</span>
                  </a>
                </li>
                <li
                  v-if="can('wechat:menu:edit') && (item.sub_button || []).length < 5"
                  class="add-sub-item"
                  @click.stop="addSub(mi)"
                >
                  <a href="javascript:;" title="添加子菜单"><span class="add-icon">+</span></a>
                </li>
              </ul>
              <i class="arrow arrow-out" /><i class="arrow arrow-in" />
            </div>
          </li>
          <li
            v-if="can('wechat:menu:edit') && menus.length < 3"
            class="add-item"
            :class="{ extra: menus.length === 0 }"
            :style="{ width: menus.length === 0 ? 'auto' : menuItemWidth }"
            @click.stop="addMain"
          >
            <a href="javascript:;" class="menu-link" title="添加菜单"><span class="add-icon">+</span></a>
          </li>
        </ul>
      </div>

      <!-- 右侧编辑区 -->
      <div class="weixin-body">
        <div v-if="current" class="weixin-content">
          <div class="item-head">
            <h4>{{ formTitle }}</h4>
            <a v-if="can('wechat:menu:edit')" class="item-delete" @click="removeCurrent">删除菜单</a>
          </div>

          <div class="form-item">
            <dl>
              <dt>{{ namePrefix }}菜单标题：</dt>
              <dd>
                <a-input
                  v-model:value="current.name"
                  :disabled="!can('wechat:menu:edit')"
                  style="max-width: 280px"
                  @change="onFieldChange"
                />
              </dd>
            </dl>

            <template v-if="!currentHasChild">
              <dl class="type-row">
                <dt>{{ namePrefix }}菜单内容：</dt>
                <dd>
                  <a-radio-group
                    v-model:value="current.type"
                    :disabled="!can('wechat:menu:edit')"
                    :options="typeOptions"
                    @change="onTypeChange"
                  />
                </dd>
              </dl>

              <div class="menu-content">
                <template v-if="current.type === 'view'">
                  <p class="tips">点击该{{ namePrefix }}菜单会跳转到以下链接</p>
                  <dl>
                    <dt>页面地址：</dt>
                    <dd>
                      <a-input
                        v-model:value="current.url"
                        :disabled="!can('wechat:menu:edit')"
                        placeholder="https://"
                        style="max-width: 360px"
                        @change="onFieldChange"
                      />
                    </dd>
                  </dl>
                </template>

                <template v-else-if="current.type === 'miniprogram'">
                  <p class="tips">点击该{{ namePrefix }}菜单会跳转到以下小程序</p>
                  <dl>
                    <dt>小程序 AppID：</dt>
                    <dd>
                      <a-input
                        v-model:value="current.appid"
                        :disabled="!can('wechat:menu:edit')"
                        placeholder="在小程序后台获取"
                        style="max-width: 360px"
                        @change="onFieldChange"
                      />
                    </dd>
                  </dl>
                  <dl>
                    <dt>小程序页面路径：</dt>
                    <dd>
                      <a-input
                        v-model:value="current.pagepath"
                        :disabled="!can('wechat:menu:edit')"
                        placeholder="pages/index/index"
                        style="max-width: 360px"
                        @change="onFieldChange"
                      />
                    </dd>
                  </dl>
                  <dl>
                    <dt>页面地址：</dt>
                    <dd>
                      <a-input
                        v-model:value="current.url"
                        :disabled="!can('wechat:menu:edit')"
                        placeholder="不支持小程序时跳转此页"
                        style="max-width: 360px"
                        @change="onFieldChange"
                      />
                    </dd>
                  </dl>
                </template>

                <template v-else>
                  <div class="clickbox">
                    <div class="create-click" @click="can('wechat:menu:edit') && openSelectResource()">
                      <div v-if="keyTitle" class="keytitle">资源名:{{ keyTitle }}</div>
                      <PlusOutlined class="create-icon" />
                      <strong>选择现有资源</strong>
                    </div>
                    <div class="create-click" @click="can('wechat:menu:edit') && openAddResource()">
                      <PlusOutlined class="create-icon" />
                      <strong>添加新资源</strong>
                    </div>
                  </div>
                </template>
              </div>
            </template>
            <p v-else class="child-tip">已有子菜单时，主菜单仅可设置标题</p>
          </div>
        </div>
        <div v-else class="no-weixin-content">点击左侧菜单进行编辑操作</div>
      </div>
    </div>

    <div class="menu-actions">
      <div class="drag-tip">
        <BulbOutlined />
        <small>可直接拖动菜单排序</small>
      </div>
      <div class="action-btns">
        <a-button
          v-if="can('wechat:menu:remote')"
          type="primary"
          class="btn-pull"
          :loading="pulling"
          @click="onPull"
        >
          <CloudDownloadOutlined />
          加载服务端菜单
        </a-button>
        <a-button
          v-if="can('wechat:menu:edit') || can('wechat:menu:sync')"
          danger
          type="primary"
          :loading="publishing"
          @click="onPublish"
        >
          <CheckOutlined />
          保存并发布
        </a-button>
      </div>
    </div>

    <a-modal v-model:open="selectOpen" title="选择资源" width="800px" :footer="null">
      <a-form layout="inline" style="margin-bottom: 12px">
        <a-form-item label="资源标题"><a-input v-model:value="selectQuery.title" allow-clear /></a-form-item>
        <a-form-item>
          <a-button type="primary" @click="searchSelect">搜索</a-button>
        </a-form-item>
      </a-form>
      <a-table
        row-key="id"
        size="small"
        :columns="selectColumns"
        :data-source="selectRows"
        :loading="selectLoading"
        :pagination="selectPagination"
        @change="onSelectTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'status'">
            <a-badge :status="record.status === 'normal' ? 'success' : 'default'" :text="statusText(record.status)" />
          </template>
          <template v-else-if="column.key === 'action'">
            <a @click="chooseResource(record)">选择</a>
          </template>
        </template>
      </a-table>
    </a-modal>

    <a-modal v-model:open="resourceOpen" title="添加新资源" :confirm-loading="resourceSaving" @ok="submitResource">
      <a-form :model="resourceForm" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="资源标题" required><a-input v-model:value="resourceForm.title" /></a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="resourceForm.remark" :rows="3" /></a-form-item>
        <a-form-item label="类型">
          <a-radio-group v-model:value="resourceForm.type" :options="resourceTypeOptions" />
        </a-form-item>
        <a-form-item v-if="resourceForm.type === 'text'" label="文本内容" required>
          <a-textarea v-model:value="resourceForm.contentText" :rows="4" />
        </a-form-item>
        <a-form-item v-else label="应用" required>
          <a-input v-model:value="resourceForm.app" placeholder="应用标识" />
        </a-form-item>
        <a-form-item label="状态">
          <a-radio-group v-model:value="resourceForm.status" :options="statusOptions" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import {
  BulbOutlined,
  CheckOutlined,
  CloudDownloadOutlined,
  PlusOutlined,
} from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import {
  addResponse,
  getWechatMenu,
  listResponse,
  pullWechatMenu,
  saveWechatMenu,
  syncWechatMenu,
} from '@/api/builtin'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)

const menus = ref([])
const responseList = ref({})
const openMainIndex = ref(-1)
const selected = reactive({ level: '', mainIndex: -1, subIndex: -1 })
const dragMain = ref(-1)
const dragSub = reactive({ mainIndex: -1, subIndex: -1 })
const pulling = ref(false)
const publishing = ref(false)

const selectOpen = ref(false)
const selectLoading = ref(false)
const selectRows = ref([])
const selectQuery = reactive({ pageNum: 1, pageSize: 10, title: '' })
const selectPagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showTotal: (t) => `共 ${t} 条`,
})
const resourceOpen = ref(false)
const resourceSaving = ref(false)
const resourceForm = reactive(emptyResource())

const typeOptions = [
  { label: '发送消息', value: 'click' },
  { label: '跳转网页', value: 'view' },
  { label: '跳转小程序', value: 'miniprogram' },
  { label: '扫码推', value: 'scancode_push' },
  { label: '扫码推提示框', value: 'scancode_waitmsg' },
  { label: '拍照发图', value: 'pic_sysphoto' },
  { label: '拍照相册发图', value: 'pic_photo_or_album' },
  { label: '相册发图', value: 'pic_weixin' },
  { label: '地理位置选择', value: 'location_select' },
]
const resourceTypeOptions = [
  { label: '文本', value: 'text' },
  { label: '应用', value: 'app' },
]
const statusOptions = [
  { label: '正常', value: 'normal' },
  { label: '隐藏', value: 'hidden' },
]
const selectColumns = [
  { title: 'ID', dataIndex: 'id', width: 70 },
  { title: '类型', dataIndex: 'type', width: 90 },
  { title: '资源标题', dataIndex: 'title' },
  { title: '事件标识', dataIndex: 'eventkey' },
  { title: '状态', dataIndex: 'status', width: 100 },
  { title: '操作', key: 'action', width: 80 },
]

const menuItemWidth = computed(() => {
  const n = menus.value.length + (menus.value.length < 3 && can('wechat:menu:edit') ? 1 : 0)
  const count = Math.max(n, 1)
  return `${100 / count}%`
})

const current = computed(() => {
  const mi = selected.mainIndex
  if (mi < 0 || !menus.value[mi]) return null
  if (selected.level === 'sub') {
    const sub = menus.value[mi].sub_button?.[selected.subIndex]
    return sub || null
  }
  if (selected.level === 'main') return menus.value[mi]
  return null
})

const currentHasChild = computed(() => {
  if (selected.level !== 'main') return false
  const item = menus.value[selected.mainIndex]
  return !!(item?.sub_button && item.sub_button.length)
})

const isSub = computed(() => selected.level === 'sub')
const namePrefix = computed(() => (isSub.value ? '子' : ''))
const formTitle = computed(() => {
  if (!current.value) return ''
  if (selected.level === 'sub') return '添加子菜单'
  return '菜单名称'
})
const keyTitle = computed(() => {
  const key = current.value?.key
  if (!key) return ''
  return responseList.value[key] || key
})

function emptyButton(name = '添加菜单') {
  return { type: 'click', name, key: '', url: '', appid: '', pagepath: '' }
}
function emptyResource() {
  return { title: '', type: 'text', contentText: '', app: '', status: 'normal', remark: '', eventkey: '' }
}
function statusText(status) {
  return status === 'normal' ? '正常' : status === 'hidden' ? '隐藏' : status || '-'
}
function uniqEventkey() {
  return Date.now().toString(16) + Math.random().toString(16).slice(2, 6)
}
function hasSub(item) {
  return !!(item?.sub_button && item.sub_button.length)
}
function displayName(name) {
  const s = String(name || '')
  if (s.length <= 5) return s
  return s.slice(0, 4) + '...'
}
function normalizeMenu(list) {
  return (list || []).map((item) => {
    const row = {
      type: item.type || 'click',
      name: item.name || '',
      key: item.key || '',
      url: item.url || '',
      appid: item.appid || '',
      pagepath: item.pagepath || '',
    }
    if (Array.isArray(item.sub_button) && item.sub_button.length) {
      row.sub_button = item.sub_button.map((s) => ({
        type: s.type || 'click',
        name: s.name || '',
        key: s.key || '',
        url: s.url || '',
        appid: s.appid || '',
        pagepath: s.pagepath || '',
      }))
    }
    return row
  })
}
function toPayload() {
  return menus.value.map((item) => {
    if (item.sub_button?.length) {
      return {
        name: item.name || '',
        sub_button: item.sub_button.map((s) => sanitizeButton(s)),
      }
    }
    return sanitizeButton(item)
  })
}
function sanitizeButton(item) {
  const type = item.type || 'click'
  if (type === 'view') return { type: 'view', name: item.name || '', url: item.url || '' }
  if (type === 'miniprogram') {
    return {
      type: 'miniprogram',
      name: item.name || '',
      url: item.url || '',
      appid: item.appid || '',
      pagepath: item.pagepath || '',
    }
  }
  return { type, name: item.name || '', key: item.key || '' }
}

function isMainSelected(mi) {
  return selected.level === 'main' && selected.mainIndex === mi
}
function isSubSelected(mi, si) {
  return selected.level === 'sub' && selected.mainIndex === mi && selected.subIndex === si
}
function selectMain(mi) {
  if (openMainIndex.value === mi && selected.level === 'main' && selected.mainIndex === mi) {
    openMainIndex.value = -1
  } else {
    openMainIndex.value = mi
  }
  selected.level = 'main'
  selected.mainIndex = mi
  selected.subIndex = -1
}
function selectSub(mi, si) {
  openMainIndex.value = mi
  selected.level = 'sub'
  selected.mainIndex = mi
  selected.subIndex = si
}
function addMain() {
  if (menus.value.length >= 3) {
    message.warning('最多添加 3 个一级菜单')
    return
  }
  menus.value.push(emptyButton('添加菜单'))
  const mi = menus.value.length - 1
  openMainIndex.value = mi
  selectMain(mi)
}
function addSub(mi) {
  const item = menus.value[mi]
  if (!item) return
  if (!item.sub_button) item.sub_button = []
  if (item.sub_button.length >= 5) {
    message.warning('最多添加 5 个子菜单')
    return
  }
  // 主菜单有子菜单后，自身不再保留类型字段
  delete item.type
  delete item.key
  delete item.url
  delete item.appid
  delete item.pagepath
  item.sub_button.push(emptyButton('添加子菜单'))
  selectSub(mi, item.sub_button.length - 1)
}
function removeCurrent() {
  if (!current.value) return
  Modal.confirm({
    title: '确认删除该菜单？',
    onOk: () => {
      const mi = selected.mainIndex
      if (selected.level === 'sub') {
        const list = menus.value[mi]?.sub_button || []
        const si = selected.subIndex
        list.splice(si, 1)
        if (!list.length) {
          delete menus.value[mi].sub_button
          Object.assign(menus.value[mi], emptyButton(menus.value[mi].name || '添加菜单'))
          selectMain(mi)
        } else {
          const next = Math.min(si, list.length - 1)
          selectSub(mi, next)
        }
        return
      }
      menus.value.splice(mi, 1)
      if (!menus.value.length) {
        selected.level = ''
        selected.mainIndex = -1
        selected.subIndex = -1
        openMainIndex.value = -1
        return
      }
      const next = Math.min(mi, menus.value.length - 1)
      selectMain(next)
    },
  })
}
function onFieldChange() {
  // reactive binding already updates menus
}
function onTypeChange() {
  if (!current.value) return
  const t = current.value.type
  if (t === 'view') {
    current.value.key = ''
    current.value.appid = ''
    current.value.pagepath = ''
  } else if (t === 'miniprogram') {
    current.value.key = ''
  } else {
    current.value.url = ''
    current.value.appid = ''
    current.value.pagepath = ''
  }
}

function onMainDragStart(mi, e) {
  dragMain.value = mi
  e.dataTransfer.effectAllowed = 'move'
}
function onMainDrop(mi) {
  const from = dragMain.value
  if (from < 0 || from === mi) return
  const list = menus.value
  const [row] = list.splice(from, 1)
  list.splice(mi, 0, row)
  dragMain.value = -1
  if (selected.mainIndex === from) selected.mainIndex = mi
  else if (from < selected.mainIndex && mi >= selected.mainIndex) selected.mainIndex -= 1
  else if (from > selected.mainIndex && mi <= selected.mainIndex) selected.mainIndex += 1
  openMainIndex.value = selected.mainIndex
}
function onSubDragStart(mi, si, e) {
  dragSub.mainIndex = mi
  dragSub.subIndex = si
  e.dataTransfer.effectAllowed = 'move'
  e.stopPropagation()
}
function onSubDrop(mi, si) {
  if (dragSub.mainIndex !== mi) return
  const from = dragSub.subIndex
  if (from < 0 || from === si) return
  const list = menus.value[mi].sub_button
  const [row] = list.splice(from, 1)
  list.splice(si, 0, row)
  dragSub.subIndex = -1
  if (selected.level === 'sub' && selected.mainIndex === mi) {
    if (selected.subIndex === from) selected.subIndex = si
    else if (from < selected.subIndex && si >= selected.subIndex) selected.subIndex -= 1
    else if (from > selected.subIndex && si <= selected.subIndex) selected.subIndex += 1
  }
}

async function load() {
  const res = await getWechatMenu()
  const data = res.data || {}
  menus.value = normalizeMenu(data.menu || [])
  responseList.value = data.responseList || {}
  if (menus.value.length) {
    selectMain(0)
  } else {
    selected.level = ''
    selected.mainIndex = -1
    selected.subIndex = -1
    openMainIndex.value = -1
  }
}
function onPull() {
  Modal.confirm({
    title: '确认加载远程微信服务端菜单？',
    content: '本地菜单将会被覆盖！',
    onOk: async () => {
      pulling.value = true
      try {
        await pullWechatMenu()
        message.success('已加载服务端菜单')
        await load()
      } finally {
        pulling.value = false
      }
    },
  })
}
async function onPublish() {
  for (const item of menus.value) {
    if (!item.name) {
      message.warning('请填写菜单标题')
      return
    }
    if (item.sub_button?.length) {
      for (const s of item.sub_button) {
        if (!s.name) {
          message.warning('请填写子菜单标题')
          return
        }
        if (s.type === 'click' && !s.key) {
          message.warning(`子菜单「${s.name}」未绑定资源`)
          return
        }
        if (s.type === 'view' && !s.url) {
          message.warning(`子菜单「${s.name}」未填写页面地址`)
          return
        }
      }
    } else if (item.type === 'click' && !item.key) {
      message.warning(`菜单「${item.name}」未绑定资源`)
      return
    } else if (item.type === 'view' && !item.url) {
      message.warning(`菜单「${item.name}」未填写页面地址`)
      return
    }
  }
  publishing.value = true
  try {
    if (can('wechat:menu:edit')) {
      await saveWechatMenu(toPayload())
    }
    if (can('wechat:menu:sync')) {
      await syncWechatMenu()
      message.success('菜单已保存并发布，生效时间以微信官方说明为准')
    } else {
      message.success('菜单已保存')
    }
  } finally {
    publishing.value = false
  }
}

function openSelectResource() {
  selectOpen.value = true
  loadSelectList()
}
async function loadSelectList() {
  selectLoading.value = true
  try {
    const res = await listResponse(selectQuery)
    selectRows.value = res.rows || []
    selectPagination.total = res.total || 0
    selectPagination.current = selectQuery.pageNum
  } finally {
    selectLoading.value = false
  }
}
function searchSelect() {
  selectQuery.pageNum = 1
  loadSelectList()
}
function onSelectTableChange(page) {
  selectQuery.pageNum = page.current
  selectQuery.pageSize = page.pageSize
  loadSelectList()
}
function chooseResource(record) {
  if (!current.value) return
  current.value.key = record.eventkey || ''
  responseList.value = { ...responseList.value, [record.eventkey]: record.title }
  selectOpen.value = false
}
function openAddResource() {
  Object.assign(resourceForm, emptyResource(), { eventkey: uniqEventkey() })
  resourceOpen.value = true
}
async function submitResource() {
  if (!resourceForm.title) {
    message.warning('请填写资源标题')
    return
  }
  if (resourceForm.type === 'text' && !resourceForm.contentText) {
    message.warning('请填写文本内容')
    return
  }
  if (resourceForm.type === 'app' && !resourceForm.app) {
    message.warning('请填写应用标识')
    return
  }
  resourceSaving.value = true
  try {
    const payload = {
      title: resourceForm.title,
      eventkey: resourceForm.eventkey,
      type: resourceForm.type,
      status: resourceForm.status,
      remark: resourceForm.remark,
    }
    if (resourceForm.type === 'text') payload.contentText = resourceForm.contentText
    else payload.content = { app: resourceForm.app }
    await addResponse(payload)
    if (current.value) {
      current.value.key = resourceForm.eventkey
      responseList.value = { ...responseList.value, [resourceForm.eventkey]: resourceForm.title }
    }
    message.success('资源已添加')
    resourceOpen.value = false
  } finally {
    resourceSaving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.menu-page {
  min-height: 640px;
}
.menu-setting {
  display: flex;
  gap: 0;
  min-height: 550px;
}
.mobile-preview {
  position: relative;
  flex: 0 0 317px;
  width: 317px;
  height: 550px;
  border: 1px solid #e7e7eb;
  background: linear-gradient(180deg, #1e1e1e 0, #1e1e1e 64px, #f5f5f5 64px, #f5f5f5 100%);
  border-radius: 2px;
  overflow: visible;
}
.mobile-head {
  position: relative;
  height: 64px;
  color: #fff;
  padding-top: 28px;
}
.mobile-back {
  position: absolute;
  left: 12px;
  top: 34px;
  font-size: 13px;
  opacity: 0.9;
}
.mobile-back::before {
  content: '‹';
  margin-right: 2px;
  font-size: 16px;
}
.mobile-title {
  text-align: center;
  font-size: 15px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin: 0 48px 0 56px;
}
.mobile-user {
  position: absolute;
  right: 14px;
  top: 34px;
  width: 18px;
  height: 18px;
  border: 1.5px solid #fff;
  border-radius: 50%;
}
.menu-list {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 50px;
  margin: 0;
  padding: 0 0 0 43px;
  list-style: none;
  border-top: 1px solid #e7e7eb;
  background: #fafafa
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='36' height='50' viewBox='0 0 36 50'%3E%3Crect width='36' height='50' fill='%23fafafa'/%3E%3Ccircle cx='18' cy='25' r='10' fill='none' stroke='%23c6c6c6' stroke-width='1.5'/%3E%3Cpath d='M13 25h10M18 20v10' stroke='%23c6c6c6' stroke-width='1.5'/%3E%3C/svg%3E")
    no-repeat 0 0;
}
.menu-item,
.add-item {
  position: relative;
  float: left;
  line-height: 50px;
  text-align: center;
  list-style: none;
}
.add-item.extra {
  float: none;
  width: auto !important;
  overflow: hidden;
}
.menu-link {
  display: block;
  border-left: 1px solid #e7e7eb;
  color: #616161;
  text-decoration: none;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  padding: 0 4px;
}
.menu-item.current > .menu-link {
  border: 1px solid #44b549;
  line-height: 48px;
  background: #fff;
  color: #44b549;
}
.icon-menu-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  margin-right: 3px;
  margin-top: -2px;
  vertical-align: middle;
  border-radius: 50%;
  background: #888;
}
.menu-item.current .icon-menu-dot {
  background: #44b549;
}
.add-icon {
  display: inline-block;
  font-size: 18px;
  font-weight: 400;
  color: #888;
  line-height: 1;
}
.sub-menu-box {
  position: absolute;
  bottom: 60px;
  left: 0;
  width: 100%;
  background: #fff;
  z-index: 2;
}
.sub-menu-list {
  margin: 0;
  padding: 0;
  list-style: none;
  line-height: 50px;
}
.sub-menu-list li {
  line-height: 44px;
  margin: -1px -1px 0;
  list-style: none;
}
.sub-menu-item a,
.add-sub-item a {
  display: block;
  border: 1px solid #d0d0d0;
  position: relative;
  padding: 0 0.5em;
  color: #616161;
  text-decoration: none;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.sub-menu-item.current a {
  border: 1px solid #44b549;
  background: #fff;
  color: #44b549;
  z-index: 1;
}
.sub-menu-item a:hover,
.add-sub-item a:hover {
  background: #f1f1f1;
}
.arrow {
  position: absolute;
  left: 50%;
  margin-left: -6px;
  display: inline-block;
  width: 0;
  height: 0;
  border-width: 6px;
  border-style: dashed;
  border-color: transparent;
  border-bottom-width: 0;
}
.arrow-out {
  bottom: -6px;
  border-top-color: #d0d0d0;
  border-top-style: solid;
}
.arrow-in {
  bottom: -5px;
  border-top-color: #fafafa;
  border-top-style: solid;
}

.weixin-body {
  flex: 1;
  min-width: 0;
  margin-left: 20px;
}
.weixin-content {
  background: #f4f5f9;
  border: 1px solid #e7e7eb;
  padding: 15px 20px 24px;
  min-height: 520px;
}
.no-weixin-content {
  min-height: 520px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(0, 0, 0, 0.45);
  font-size: 14px;
}
.item-head {
  position: relative;
  border-bottom: 1px solid #e7e7eb;
  padding-bottom: 12px;
  margin-bottom: 20px;
}
.item-head h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 500;
  line-height: 28px;
}
.item-delete {
  position: absolute;
  top: 0;
  right: 0;
  color: #1677ff;
  line-height: 28px;
}
.form-item dl {
  position: relative;
  margin: 12px 0;
  min-height: 32px;
}
.form-item dt {
  position: absolute;
  left: 0;
  top: 0;
  width: 110px;
  line-height: 32px;
  text-align: right;
  color: rgba(0, 0, 0, 0.85);
}
.form-item dd {
  margin-left: 118px;
  line-height: 32px;
}
.type-row :deep(.ant-radio-group) {
  display: flex;
  flex-wrap: wrap;
  gap: 4px 12px;
}
.type-row :deep(.ant-radio-wrapper) {
  margin-inline-end: 0;
}
.menu-content {
  background: #fff;
  border: 1px solid #e7e7eb;
  padding: 16px 20px;
  margin-top: 8px;
}
.tips {
  color: #8d8d8d;
  margin: 0 0 12px;
}
.child-tip {
  margin: 24px 0 0 118px;
  color: #8d8d8d;
}
.clickbox {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  justify-content: center;
  padding: 24px 0;
}
.create-click {
  position: relative;
  width: 220px;
  height: 120px;
  border: 2px dotted #d9dadc;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #1677ff;
  user-select: none;
  background: #fff;
}
.create-click:hover {
  border-color: #1677ff;
  background: #f0f7ff;
}
.create-icon {
  font-size: 28px;
  margin-bottom: 6px;
}
.keytitle {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 32px;
  line-height: 32px;
  text-align: center;
  background: #f4f5f9;
  color: rgba(0, 0, 0, 0.65);
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  padding: 0 8px;
}

.menu-actions {
  position: relative;
  margin-top: 16px;
  min-height: 36px;
}
.drag-tip {
  position: absolute;
  left: 0;
  top: 8px;
  width: 317px;
  color: #ff4d4f;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}
.action-btns {
  padding-left: 337px;
  display: flex;
  gap: 12px;
  justify-content: center;
}
.btn-pull {
  background: #52c41a !important;
  border-color: #52c41a !important;
}
.btn-pull:hover {
  background: #73d13d !important;
  border-color: #73d13d !important;
}

@media (max-width: 900px) {
  .menu-setting {
    flex-direction: column;
    align-items: center;
  }
  .weixin-body {
    margin-left: 0;
    margin-top: 20px;
    width: 100%;
  }
  .drag-tip {
    position: static;
    width: auto;
    margin-bottom: 8px;
  }
  .action-btns {
    padding-left: 0;
  }
}
</style>
