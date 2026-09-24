<template>
  <a-tabs>
    <a-tab-pane key="list" tab="插件脚手架">
      <div class="page-stack">
        <div class="page-card">
          <a-form layout="inline" class="search">
            <a-form-item label="标识"><a-input v-model:value="query.name" allow-clear /></a-form-item>
            <a-form-item>
              <a-button type="primary" @click="search">搜索</a-button>
              <a-button v-if="can('tool:addondev:add')" style="margin-left: 8px" @click="openAdd">新建</a-button>
            </a-form-item>
          </a-form>
        </div>
        <div class="page-card">
          <a-table row-key="name" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'action'">
                <a-space :size="6">
                  <TableActionBtn v-if="can('tool:addondev:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
                    <EditOutlined />
                  </TableActionBtn>
                  <TableActionBtn v-if="can('tool:addondev:package')" tip="打包" @click="onPackage(record)">
                    <InboxOutlined />
                  </TableActionBtn>
                  <TableActionBtn v-if="can('tool:addondev:edit')" tip="同步菜单" @click="onSync(record)">
                    <SyncOutlined />
                  </TableActionBtn>
                </a-space>
              </template>
            </template>
          </a-table>
        </div>
      </div>
    </a-tab-pane>
    <a-tab-pane key="menu" tab="插件菜单">
      <div class="page-stack">
        <div class="page-card">
          <a-form layout="inline" class="search">
            <a-form-item label="插件标识"><a-input v-model:value="menuName" allow-clear /></a-form-item>
            <a-form-item>
              <a-button type="primary" @click="loadMenu">查询</a-button>
              <a-button v-if="can('tool:addondev:edit')" style="margin-left: 8px" type="primary" @click="openMenuAdd()">新增</a-button>
            </a-form-item>
          </a-form>
        </div>
        <div class="page-card">
          <a-table
            :key="menuTree.length"
            row-key="menuId"
            size="small"
            :columns="menuColumns"
            :data-source="menuTree"
            :loading="menuLoading"
            :pagination="false"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.dataIndex === 'icon'">
                <MenuIcon :icon="record.icon" />
              </template>
              <template v-else-if="column.dataIndex === 'menuType'">
                {{ typeText(record.menuType) }}
              </template>
              <template v-else-if="column.dataIndex === 'visible'">
                {{ isOn(record.visible) ? '显示' : '隐藏' }}
              </template>
              <template v-else-if="column.key === 'action'">
                <a-space :size="6">
                  <TableActionBtn v-if="can('tool:addondev:edit')" tip="新增" @click="openMenuAdd(record)">
                    <PlusOutlined />
                  </TableActionBtn>
                  <TableActionBtn v-if="can('tool:addondev:edit')" tip="编辑" tone="edit" @click="openMenuEdit(record)">
                    <EditOutlined />
                  </TableActionBtn>
                  <TableActionBtn v-if="can('tool:addondev:edit')" tip="删除" tone="danger" @click="onMenuRemove(record)">
                    <DeleteOutlined />
                  </TableActionBtn>
                </a-space>
              </template>
            </template>
          </a-table>
        </div>
      </div>
    </a-tab-pane>
  </a-tabs>

  <a-modal v-model:open="open" :title="editing ? '编辑插件' : '新建插件'" :confirm-loading="saving" @ok="submit">
    <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
      <a-form-item label="标识" required><a-input v-model:value="form.name" :disabled="editing" /></a-form-item>
      <a-form-item label="名称" required><a-input v-model:value="form.title" /></a-form-item>
      <a-form-item label="版本"><a-input v-model:value="form.version" /></a-form-item>
      <a-form-item label="作者"><a-input v-model:value="form.author" /></a-form-item>
      <a-form-item label="网站"><a-input v-model:value="form.website" /></a-form-item>
      <a-form-item label="简介"><a-textarea v-model:value="form.intro" :rows="2" /></a-form-item>
    </a-form>
  </a-modal>

  <a-modal
    v-model:open="menuOpen"
    :title="menuForm.menuId ? '编辑菜单' : '新增菜单'"
    :confirm-loading="menuSaving"
    width="680px"
    @ok="submitMenu"
  >
    <a-form :model="menuForm" :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
      <a-form-item label="上级菜单">
        <a-tree-select v-model:value="menuForm.parentId" :tree-data="parentTree" style="width: 100%" />
      </a-form-item>
      <a-form-item label="菜单类型">
        <a-radio-group v-model:value="menuForm.menuType" :options="typeOptions" />
      </a-form-item>
      <a-form-item label="菜单名称" required><a-input v-model:value="menuForm.menuName" /></a-form-item>
      <a-form-item v-if="menuForm.menuType !== 'F'" label="菜单图标">
        <IconSelect v-model:value="menuForm.icon" />
      </a-form-item>
      <a-form-item label="显示排序"><a-input-number v-model:value="menuForm.orderNum" :min="0" style="width: 100%" /></a-form-item>
      <a-form-item v-if="menuForm.menuType === 'C'" label="请求地址">
        <a-input v-model:value="menuForm.url" placeholder="/yscms/setting" />
      </a-form-item>
      <a-form-item v-if="menuForm.menuType !== 'M'" label="权限标识">
        <a-input v-model:value="menuForm.perms" placeholder="yscms:setting:view" />
      </a-form-item>
      <a-form-item v-if="menuForm.menuType !== 'F'" label="显示状态">
        <a-radio-group v-model:value="menuForm.visible" :options="visibleOptions" />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, InboxOutlined, PlusOutlined, SyncOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import {
  addAddonDev,
  addAddonMenu,
  addonMenuTree,
  getAddonDev,
  getAddonMenu,
  listAddonDev,
  listAddonMenu,
  packageAddonDev,
  removeAddonMenu,
  syncAddonMenu,
  updateAddonDev,
  updateAddonMenu,
} from '@/api/tool'
import { handleTree, isOn, num, ztreeToAnt } from '@/utils/tree'
import MenuIcon from '@/components/MenuIcon.vue'
import IconSelect from '@/components/icon/IconSelect.vue'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)

const visibleOptions = [
  { label: '显示', value: '0' },
  { label: '隐藏', value: '1' },
]
const typeOptions = [
  { label: '目录', value: 'M' },
  { label: '菜单', value: 'C' },
  { label: '按钮', value: 'F' },
]

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const open = ref(false)
const editing = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, name: '' })
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showTotal: (t) => `共 ${t} 条`,
})
const form = reactive(empty())

const menuName = ref('yscms')
const menuLoading = ref(false)
const menuSaving = ref(false)
const menuTree = ref([])
const menuOpen = ref(false)
const parentTree = ref([])
const menuForm = reactive(emptyMenuForm())

const columns = [
  { title: '标识', dataIndex: 'name', width: 140 },
  { title: '名称', dataIndex: 'title' },
  { title: '版本', dataIndex: 'version', width: 90 },
  { title: '作者', dataIndex: 'author', width: 120 },
  { title: '简介', dataIndex: 'intro', ellipsis: true },
  { title: '操作', key: 'action', width: 240 },
]
const menuColumns = [
  { title: '菜单名称', dataIndex: 'menuName' },
  { title: '图标', dataIndex: 'icon', width: 70 },
  { title: '排序', dataIndex: 'orderNum', width: 80 },
  { title: '请求地址', dataIndex: 'url' },
  { title: '类型', dataIndex: 'menuType', width: 80 },
  { title: '权限标识', dataIndex: 'perms' },
  { title: '可见', dataIndex: 'visible', width: 80 },
  { title: '操作', key: 'action', width: 180 },
]

function empty() {
  return { name: '', title: '', version: '1.0.0', author: 'YshopAdmin', website: '', intro: '' }
}

function emptyMenuForm() {
  return {
    menuId: undefined,
    parentId: 0,
    menuName: '',
    menuType: 'C',
    orderNum: 0,
    url: '',
    perms: '',
    icon: '',
    visible: '0',
    isRefresh: '1',
  }
}

function typeText(type) {
  return { M: '目录', C: '菜单', F: '按钮' }[type] || type
}

async function load() {
  loading.value = true
  try {
    const res = await listAddonDev(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally {
    loading.value = false
  }
}

function search() {
  query.pageNum = 1
  load()
}

function onTableChange(page) {
  query.pageNum = page.current
  query.pageSize = page.pageSize
  load()
}

function openAdd() {
  editing.value = false
  Object.assign(form, empty())
  open.value = true
}

async function openEdit(record) {
  const res = await getAddonDev(record.name)
  const row = res.data || {}
  editing.value = true
  Object.assign(form, empty(), {
    name: row.name,
    title: row.title,
    version: row.version,
    author: row.author,
    website: row.website || '',
    intro: row.intro || '',
  })
  open.value = true
}

async function submit() {
  if (!form.name || !form.title) {
    message.warning('请填写标识和名称')
    return
  }
  saving.value = true
  try {
    if (editing.value) await updateAddonDev(form)
    else await addAddonDev(form)
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onPackage(record) {
  Modal.confirm({
    title: `打包 ${record.name}？`,
    onOk: async () => {
      const res = await packageAddonDev({ name: record.name, version: record.version })
      message.success(res.msg || '打包成功')
    },
  })
}

function onSync(record) {
  Modal.confirm({
    title: `同步 ${record.name} 菜单？`,
    content: '将按当前 menu.php 重建该插件菜单（与库中手工改动已回写文件时保持一致）。',
    onOk: async () => {
      await syncAddonMenu(record.name)
      message.success('已同步')
      if (menuName.value === record.name) {
        loadMenu()
      }
    },
  })
}

async function loadMenu() {
  const name = (menuName.value || '').trim()
  if (!name) {
    message.warning('请先填写插件标识')
    menuTree.value = []
    return
  }
  menuLoading.value = true
  try {
    const res = await listAddonMenu({ name })
    menuTree.value = handleTree(res.rows || [], 'menuId', 'parentId')
  } finally {
    menuLoading.value = false
  }
}

async function loadParents(excludeId) {
  const res = await addonMenuTree(menuName.value.trim(), excludeId || 0)
  parentTree.value = ztreeToAnt(res.data || []).tree
}

async function openMenuAdd(parent) {
  const name = (menuName.value || '').trim()
  if (!name) {
    message.warning('请先填写插件标识')
    return
  }
  Object.assign(menuForm, emptyMenuForm(), { parentId: parent ? Number(parent.menuId) : 0 })
  await loadParents(0)
  menuOpen.value = true
}

async function openMenuEdit(record) {
  const name = (menuName.value || '').trim()
  if (!name) {
    message.warning('请先填写插件标识')
    return
  }
  const res = await getAddonMenu(record.menuId, name)
  const menu = res.data || {}
  Object.assign(menuForm, emptyMenuForm(), {
    menuId: num(menu.menuId),
    parentId: Number(menu.parentId || 0),
    menuName: menu.menuName,
    menuType: menu.menuType || 'C',
    orderNum: Number(menu.orderNum || 0),
    url: menu.url || '',
    perms: menu.perms || '',
    icon: menu.icon || '',
    visible: String(menu.visible ?? '0'),
    isRefresh: String(menu.isRefresh ?? '1'),
  })
  await loadParents(menu.menuId)
  menuOpen.value = true
}

async function submitMenu() {
  if (!menuForm.menuName) {
    message.warning('请填写菜单名称')
    return
  }
  const name = (menuName.value || '').trim()
  if (!name) {
    message.warning('请先填写插件标识')
    return
  }
  const payload = { ...menuForm, name }
  if (payload.menuType !== 'C') {
    payload.url = payload.url || '#'
  }
  menuSaving.value = true
  try {
    if (payload.menuId) {
      await updateAddonMenu(payload)
    } else {
      await addAddonMenu(payload)
    }
    message.success('保存成功')
    menuOpen.value = false
    loadMenu()
  } finally {
    menuSaving.value = false
  }
}

function onMenuRemove(record) {
  const name = (menuName.value || '').trim()
  if (!name) {
    message.warning('请先填写插件标识')
    return
  }
  Modal.confirm({
    title: '确认删除该菜单？',
    onOk: async () => {
      await removeAddonMenu(name, record.menuId)
      message.success('删除成功')
      loadMenu()
    },
  })
}

onMounted(() => {
  load()
  loadMenu()
})
</script>
