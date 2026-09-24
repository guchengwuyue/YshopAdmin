<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
      <a-form-item label="角色名称">
        <a-input v-model:value="query.roleName" allow-clear />
      </a-form-item>
      <a-form-item label="权限字符">
        <a-input v-model:value="query.roleKey" allow-clear />
      </a-form-item>
      <a-form-item label="状态">
        <a-select v-model:value="query.status" allow-clear style="width: 120px" :options="statusOptions" />
      </a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button style="margin-left: 8px" @click="resetQuery">重置</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <div class="toolbar">
      <a-space>
        <a-button v-if="can('system:role:add')" type="primary" @click="openAdd">新增</a-button>
        <a-button v-if="can('system:role:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
      </a-space>
    </div>
    <a-table
      row-key="roleId"
      :columns="columns"
      :data-source="rows"
      :loading="loading"
      :pagination="pagination"
      :row-selection="rowSelection"
      @change="onTableChange"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">
          <a-switch
            :checked="isOn(record.status)"
            :disabled="Number(record.roleId) === 1 || !can('system:role:edit')"
            @change="(checked) => onStatus(record, checked)"
          />
        </template>
        <template v-else-if="column.key === 'action'">
          <a-space v-if="Number(record.roleId) !== 1" :size="6">
            <TableActionBtn v-if="can('system:role:edit')" tip="数据权限" @click="openScope(record)">
              <SafetyOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:role:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:role:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>

    <a-modal v-model:open="open" :title="form.roleId ? '编辑角色' : '新增角色'" :confirm-loading="saving" width="680px" @ok="submit">
      <a-form :model="form" :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
        <a-form-item label="角色名称" required><a-input v-model:value="form.roleName" /></a-form-item>
        <a-form-item label="权限字符" required><a-input v-model:value="form.roleKey" /></a-form-item>
        <a-form-item label="显示顺序"><a-input-number v-model:value="form.roleSort" :min="0" style="width: 100%" /></a-form-item>
        <a-form-item label="状态"><a-radio-group v-model:value="form.status" :options="statusOptions" /></a-form-item>
        <a-form-item label="菜单权限">
          <a-tree
            v-if="menuTree.length"
            checkable
            check-strictly
            default-expand-all
            :tree-data="menuTree"
            :checked-keys="menuChecked"
            @check="onMenuCheck"
          />
        </a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="2" /></a-form-item>
      </a-form>
    </a-modal>

    <a-modal v-model:open="scopeOpen" title="数据权限" :confirm-loading="saving" @ok="submitScope">
      <a-form :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="角色名称"><a-input :value="scope.roleName" disabled /></a-form-item>
        <a-form-item label="权限字符"><a-input :value="scope.roleKey" disabled /></a-form-item>
        <a-form-item label="权限范围">
          <a-select v-model:value="scope.dataScope" :options="scopeOptions" />
        </a-form-item>
        <a-form-item v-if="scope.dataScope === '2'" label="数据权限">
          <a-tree
            v-if="deptTree.length"
            checkable
            check-strictly
            default-expand-all
            :tree-data="deptTree"
            :checked-keys="deptChecked"
            @check="onDeptCheck"
          />
        </a-form-item>
      </a-form>
    </a-modal>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, SafetyOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addRole, authDataScope, changeRoleStatus, getRole, listRole, removeRole, roleDeptTree, roleMenuTree, updateRole } from '@/api/system/role'
import { isOn, nums, ztreeToAnt } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const statusOptions = [
  { label: '正常', value: '0' },
  { label: '停用', value: '1' },
]
const scopeOptions = [
  { label: '全部数据权限', value: '1' },
  { label: '自定数据权限', value: '2' },
  { label: '本部门数据权限', value: '3' },
  { label: '本部门及以下数据权限', value: '4' },
  { label: '仅本人数据权限', value: '5' },
]
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selected = ref([])
const open = ref(false)
const scopeOpen = ref(false)
const menuTree = ref([])
const menuChecked = ref({ checked: [], halfChecked: [] })
const deptTree = ref([])
const deptChecked = ref({ checked: [], halfChecked: [] })
const query = reactive({ pageNum: 1, pageSize: 10, roleName: '', roleKey: '', status: undefined })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (total) => `共 ${total} 条` })
const form = reactive(emptyForm())
const scope = reactive({ roleId: undefined, roleName: '', roleKey: '', dataScope: '1' })

const columns = [
  { title: '角色编号', dataIndex: 'roleId', width: 90 },
  { title: '角色名称', dataIndex: 'roleName' },
  { title: '权限字符', dataIndex: 'roleKey' },
  { title: '显示顺序', dataIndex: 'roleSort', width: 100 },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '创建时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', width: 200 },
]
const rowSelection = computed(() => ({
  selectedRowKeys: selected.value,
  onChange: (keys) => { selected.value = keys },
  getCheckboxProps: (record) => ({ disabled: Number(record.roleId) === 1 }),
}))

function emptyForm() {
  return { roleId: undefined, roleName: '', roleKey: '', roleSort: 0, status: '0', menuIds: [], remark: '' }
}

async function load() {
  loading.value = true
  try {
    const res = await listRole(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
    pagination.pageSize = query.pageSize
  } finally {
    loading.value = false
  }
}

function search() {
  query.pageNum = 1
  load()
}

function resetQuery() {
  query.roleName = ''
  query.roleKey = ''
  query.status = undefined
  search()
}

function onTableChange(page) {
  query.pageNum = page.current
  query.pageSize = page.pageSize
  load()
}

async function loadMenuTree(roleId) {
  const res = await roleMenuTree(roleId || 0)
  const parsed = ztreeToAnt(res.data || [])
  menuTree.value = parsed.tree
  menuChecked.value = { checked: parsed.checked, halfChecked: [] }
}

async function openAdd() {
  Object.assign(form, emptyForm())
  await loadMenuTree(0)
  open.value = true
}

async function openEdit(record) {
  const res = await getRole(record.roleId)
  const role = res.data || {}
  Object.assign(form, emptyForm(), {
    roleId: Number(role.roleId),
    roleName: role.roleName,
    roleKey: role.roleKey,
    roleSort: Number(role.roleSort || 0),
    status: String(role.status ?? '0'),
    remark: role.remark || '',
    menuIds: nums(role.menuIds),
  })
  await loadMenuTree(role.roleId)
  menuChecked.value = { checked: nums(role.menuIds), halfChecked: [] }
  open.value = true
}

function onMenuCheck(keys) {
  menuChecked.value = keys
}

async function submit() {
  if (!form.roleName || !form.roleKey) {
    message.warning('请填写必填项')
    return
  }
  saving.value = true
  try {
    const payload = { ...form, menuIds: menuChecked.value.checked || [] }
    if (form.roleId) {
      await updateRole(payload)
    } else {
      await addRole(payload)
    }
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onRemove(record) {
  const ids = record ? String(record.roleId) : selected.value.join(',')
  Modal.confirm({
    title: '确认删除所选角色？',
    onOk: async () => {
      await removeRole(ids)
      message.success('删除成功')
      selected.value = []
      load()
    },
  })
}

async function onStatus(record, checked) {
  const status = checked ? '0' : '1'
  await changeRoleStatus({ roleId: record.roleId, status })
  record.status = status
  message.success('状态已更新')
}

async function openScope(record) {
  scope.roleId = record.roleId
  scope.roleName = record.roleName
  scope.roleKey = record.roleKey
  scope.dataScope = String(record.dataScope || '1')
  const res = await roleDeptTree(record.roleId)
  const parsed = ztreeToAnt(res.data || [])
  deptTree.value = parsed.tree
  deptChecked.value = { checked: parsed.checked, halfChecked: [] }
  scopeOpen.value = true
}

function onDeptCheck(keys) {
  deptChecked.value = keys
}

async function submitScope() {
  saving.value = true
  try {
    await authDataScope({
      roleId: scope.roleId,
      dataScope: scope.dataScope,
      deptIds: scope.dataScope === '2' ? (deptChecked.value.checked || []) : [],
    })
    message.success('保存成功')
    scopeOpen.value = false
    load()
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
