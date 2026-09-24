<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
      <a-form-item label="菜单名称">
        <a-input v-model:value="query.menuName" allow-clear />
      </a-form-item>
      <a-form-item label="状态">
        <a-select v-model:value="query.visible" allow-clear style="width: 120px" :options="visibleOptions" />
      </a-form-item>
      <a-form-item>
        <a-button type="primary" @click="load">搜索</a-button>
        <a-button style="margin-left: 8px" @click="resetQuery">重置</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <div class="toolbar">
      <a-button v-if="can('system:menu:add')" type="primary" @click="openAdd()">新增</a-button>
    </div>
    <a-table
      :key="tree.length"
      row-key="menuId"
      :columns="columns"
      :data-source="tree"
      :loading="loading"
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
            <TableActionBtn v-if="can('system:menu:add')" tip="新增" @click="openAdd(record)">
              <PlusOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:menu:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:menu:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>

    <a-modal v-model:open="open" :title="form.menuId ? '编辑菜单' : '新增菜单'" :confirm-loading="saving" width="680px" @ok="submit">
      <a-form :model="form" :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
        <a-form-item label="上级菜单">
          <a-tree-select
            v-model:value="form.parentId"
            :tree-data="parentTree"

            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="菜单类型">
          <a-radio-group v-model:value="form.menuType" :options="typeOptions" />
        </a-form-item>
        <a-form-item label="菜单名称" required><a-input v-model:value="form.menuName" /></a-form-item>
        <a-form-item v-if="form.menuType !== 'F'" label="菜单图标">
          <IconSelect v-model:value="form.icon" />
        </a-form-item>
        <a-form-item label="显示排序"><a-input-number v-model:value="form.orderNum" :min="0" style="width: 100%" /></a-form-item>
        <a-form-item v-if="form.menuType === 'C'" label="请求地址"><a-input v-model:value="form.url" placeholder="/system/user" /></a-form-item>
        <a-form-item v-if="form.menuType !== 'M'" label="权限标识"><a-input v-model:value="form.perms" placeholder="system:user:list" /></a-form-item>
        <a-form-item v-if="form.menuType !== 'F'" label="显示状态">
          <a-radio-group v-model:value="form.visible" :options="visibleOptions" />
        </a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="2" /></a-form-item>
      </a-form>
    </a-modal>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addMenu, getMenu, listMenu, menuTree, removeMenu, updateMenu } from '@/api/system/menu'
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
const tree = ref([])
const parentTree = ref([])
const open = ref(false)
const query = reactive({ menuName: '', visible: undefined })
const form = reactive(emptyForm())
const columns = [
  { title: '菜单名称', dataIndex: 'menuName' },
  { title: '图标', dataIndex: 'icon', width: 70 },
  { title: '排序', dataIndex: 'orderNum', width: 80 },
  { title: '请求地址', dataIndex: 'url' },
  { title: '类型', dataIndex: 'menuType', width: 80 },
  { title: '权限标识', dataIndex: 'perms' },
  { title: '可见', dataIndex: 'visible', width: 80 },
  { title: '操作', key: 'action', width: 180 },
]

function emptyForm() {
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
    remark: '',
  }
}

function typeText(type) {
  return { M: '目录', C: '菜单', F: '按钮' }[type] || type
}

async function load() {
  loading.value = true
  try {
    const res = await listMenu(query)
    tree.value = handleTree(res.data || [], 'menuId', 'parentId')
  } finally {
    loading.value = false
  }
}

function resetQuery() {
  query.menuName = ''
  query.visible = undefined
  load()
}

async function loadParents(excludeId) {
  const res = await menuTree(excludeId || 0)
  parentTree.value = ztreeToAnt(res.data || []).tree
}

async function openAdd(parent) {
  Object.assign(form, emptyForm(), { parentId: parent ? Number(parent.menuId) : 0 })
  await loadParents(0)
  open.value = true
}

async function openEdit(record) {
  const res = await getMenu(record.menuId)
  const menu = res.data || {}
  Object.assign(form, emptyForm(), {
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
    remark: menu.remark || '',
  })
  await loadParents(menu.menuId)
  open.value = true
}

async function submit() {
  if (!form.menuName) {
    message.warning('请填写菜单名称')
    return
  }
  const payload = { ...form }
  if (payload.menuType !== 'C') {
    payload.url = payload.url || '#'
  }
  saving.value = true
  try {
    if (payload.menuId) {
      await updateMenu(payload)
    } else {
      await addMenu(payload)
    }
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onRemove(record) {
  Modal.confirm({
    title: '确认删除该菜单？',
    onOk: async () => {
      await removeMenu(record.menuId)
      message.success('删除成功')
      load()
    },
  })
}

onMounted(load)
</script>
