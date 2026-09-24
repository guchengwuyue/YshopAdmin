<template>
  <div class="user-split">
    <div class="page-card dept-panel">
      <div class="side-title">
        <span>部门</span>
        <a @click="clearDept">全部</a>
      </div>
      <a-tree
        v-if="deptTree.length"
        :tree-data="deptTree"
        :field-names="{ title: 'deptName', key: 'deptId' }"
        @select="onDeptSelect"
      />
    </div>
    <div class="user-panel">
      <div class="page-card">
        <a-form layout="inline" :model="query" class="search">
          <a-form-item label="登录账号">
            <a-input v-model:value="query.loginName" allow-clear placeholder="登录账号" />
          </a-form-item>
          <a-form-item label="手机号">
            <a-input v-model:value="query.phonenumber" allow-clear placeholder="手机号" />
          </a-form-item>
          <a-form-item label="状态">
            <a-select v-model:value="query.status" allow-clear placeholder="状态" style="width: 120px" :options="statusOptions" />
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
            <a-button v-if="can('system:user:add')" type="primary" @click="openAdd">新增</a-button>
            <a-button v-if="can('system:user:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
          </a-space>
        </div>
        <a-table
          row-key="userId"
          :columns="columns"
          :data-source="rows"
          :loading="loading"
          :pagination="pagination"
          :row-selection="rowSelection"
          @change="onTableChange"
          :scroll="{ x: 1500 }"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'status'">
              <a-switch
                :checked="isOn(record.status)"
                :disabled="Number(record.userId) === 1 || !can('system:user:edit')"
                @change="(checked) => onStatus(record, checked)"
              />
            </template>
            <template v-else-if="column.key === 'action'">
              <a-space :size="6">
                <TableActionBtn v-if="can('system:user:resetPwd') && Number(record.userId) !== 1" tip="重置密码" @click="openReset(record)">
                  <KeyOutlined />
                </TableActionBtn>
                <TableActionBtn v-if="can('system:user:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
                  <EditOutlined />
                </TableActionBtn>
                <TableActionBtn v-if="can('system:user:remove') && Number(record.userId) !== 1" tip="删除" tone="danger" @click="onRemove(record)">
                  <DeleteOutlined />
                </TableActionBtn>
              </a-space>
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </div>

  <a-modal v-model:open="open" :title="form.userId ? '编辑用户' : '新增用户'" :confirm-loading="saving" width="720px" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-row>
          <a-col :span="12">
            <a-form-item label="用户昵称" required>
              <a-input v-model:value="form.userName" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="归属部门">
              <a-tree-select
                v-model:value="form.deptId"
                :tree-data="deptTree"
                :field-names="{ label: 'deptName', value: 'deptId', children: 'children' }"
                allow-clear

                style="width: 100%"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="登录账号" required>
              <a-input v-model:value="form.loginName" :disabled="!!form.userId" />
            </a-form-item>
          </a-col>
          <a-col v-if="!form.userId" :span="12">
            <a-form-item label="密码" required>
              <a-input-password v-model:value="form.password" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="手机号">
              <a-input v-model:value="form.phonenumber" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="邮箱">
              <a-input v-model:value="form.email" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="性别">
              <a-select v-model:value="form.sex" :options="sexOptions" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="状态">
              <a-radio-group v-model:value="form.status" :options="statusOptions" />
            </a-form-item>
          </a-col>
          <a-col :span="24">
            <a-form-item label="岗位" :label-col="{ span: 3 }" :wrapper-col="{ span: 20 }">
              <a-select v-model:value="form.postIds" mode="multiple" :options="postOptions" />
            </a-form-item>
          </a-col>
          <a-col :span="24">
            <a-form-item label="角色" :label-col="{ span: 3 }" :wrapper-col="{ span: 20 }">
              <a-select v-model:value="form.roleIds" mode="multiple" :options="roleOptions" />
            </a-form-item>
          </a-col>
          <a-col :span="24">
            <a-form-item label="备注" :label-col="{ span: 3 }" :wrapper-col="{ span: 20 }">
              <a-textarea v-model:value="form.remark" :rows="2" />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
    </a-modal>

    <a-modal v-model:open="pwdOpen" title="重置密码" :confirm-loading="saving" @ok="submitPwd">
      <a-input-password v-model:value="pwd" placeholder="新密码" />
  </a-modal>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, KeyOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addForm, addUser, changeUserStatus, editForm, listUser, removeUser, resetUserPwd, updateUser, userDeptTree } from '@/api/system/user'
import { isOn, num, nums } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const statusOptions = [
  { label: '正常', value: '0' },
  { label: '停用', value: '1' },
]
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selected = ref([])
const deptTree = ref([])
const sexOptions = ref([
  { label: '男', value: '0' },
  { label: '女', value: '1' },
  { label: '未知', value: '2' },
])
const postOptions = ref([])
const roleOptions = ref([])
const open = ref(false)
const pwdOpen = ref(false)
const pwd = ref('')
const pwdUserId = ref(0)
const query = reactive({ pageNum: 1, pageSize: 10, loginName: '', phonenumber: '', status: undefined, deptId: undefined })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (total) => `共 ${total} 条` })
const form = reactive(emptyForm())

const columns = [
  { title: '用户编号', dataIndex: 'userId', width: 90 },
  { title: '登录账号', dataIndex: 'loginName' },
  { title: '用户昵称', dataIndex: 'userName' },
  { title: '部门', dataIndex: ['dept', 'deptName'] },
  { title: '手机号', dataIndex: 'phonenumber' },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '创建时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', fixed: 'right', width: 200 },
]
const rowSelection = computed(() => ({
  selectedRowKeys: selected.value,
  onChange: (keys) => { selected.value = keys },
  getCheckboxProps: (record) => ({ disabled: Number(record.userId) === 1 }),
}))

function emptyForm() {
  return { userId: undefined, deptId: undefined, loginName: '', userName: '', password: '', phonenumber: '', email: '', sex: '0', status: '0', postIds: [], roleIds: [], remark: '' }
}

function normalizeDept(nodes) {
  return (nodes || []).map((node) => ({
    ...node,
    deptId: Number(node.deptId),
    deptName: node.deptName,
    children: node.children?.length ? normalizeDept(node.children) : undefined,
  }))
}

async function loadDept() {
  const res = await userDeptTree()
  deptTree.value = normalizeDept(res.data || [])
}

async function load() {
  loading.value = true
  try {
    const res = await listUser(query)
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
  query.loginName = ''
  query.phonenumber = ''
  query.status = undefined
  query.deptId = undefined
  search()
}

function clearDept() {
  query.deptId = undefined
  search()
}

function onDeptSelect(keys) {
  query.deptId = keys[0]
  search()
}

function onTableChange(page) {
  query.pageNum = page.current
  query.pageSize = page.pageSize
  load()
}

function fillOptions(payload) {
  sexOptions.value = (payload.sexOptions || []).map((item) => ({ label: item.dictLabel, value: String(item.dictValue) }))
  if (!sexOptions.value.length) {
    sexOptions.value = [
      { label: '男', value: '0' },
      { label: '女', value: '1' },
      { label: '未知', value: '2' },
    ]
  }
  postOptions.value = (payload.posts || []).map((item) => ({ label: item.postName, value: Number(item.postId) }))
  roleOptions.value = (payload.roles || []).map((item) => ({
    label: item.roleName,
    value: Number(item.roleId),
    disabled: String(item.status) === '1',
  }))
}

async function openAdd() {
  const res = await addForm()
  Object.assign(form, emptyForm(), { password: res.data?.initPassword || '' })
  fillOptions(res.data || {})
  open.value = true
}

async function openEdit(record) {
  const res = await editForm(record.userId)
  const user = res.data?.user || {}
  fillOptions(res.data || {})
  Object.assign(form, emptyForm(), {
    userId: num(user.userId),
    deptId: num(user.deptId),
    loginName: user.loginName,
    userName: user.userName,
    phonenumber: user.phonenumber,
    email: user.email,
    sex: String(user.sex ?? '0'),
    status: String(user.status ?? '0'),
    postIds: nums(user.postIds),
    roleIds: nums(user.roleIds),
    remark: user.remark || '',
  })
  open.value = true
}

async function submit() {
  if (!form.userName || !form.loginName || (!form.userId && !form.password)) {
    message.warning('请填写必填项')
    return
  }
  saving.value = true
  try {
    if (form.userId) {
      await updateUser(form)
    } else {
      await addUser(form)
    }
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onRemove(record) {
  const ids = record ? String(record.userId) : selected.value.join(',')
  Modal.confirm({
    title: '确认删除所选用户？',
    onOk: async () => {
      await removeUser(ids)
      message.success('删除成功')
      selected.value = []
      load()
    },
  })
}

async function onStatus(record, checked) {
  const status = checked ? '0' : '1'
  await changeUserStatus({ userId: record.userId, status })
  record.status = status
  message.success('状态已更新')
}

function openReset(record) {
  pwdUserId.value = record.userId
  pwd.value = ''
  pwdOpen.value = true
}

async function submitPwd() {
  if (!pwd.value) {
    message.warning('请输入新密码')
    return
  }
  saving.value = true
  try {
    await resetUserPwd({ userId: pwdUserId.value, password: pwd.value })
    message.success('密码已重置')
    pwdOpen.value = false
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadDept()
  load()
})
</script>

<style scoped>
.user-split {
  display: flex;
  align-items: stretch;
  gap: 16px;
}
.dept-panel {
  flex: 0 0 240px;
  width: 240px;
}
.user-panel {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.side-title {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-weight: 600;
}
</style>
