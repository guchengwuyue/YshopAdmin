<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
      <a-form-item label="部门名称">
        <a-input v-model:value="query.deptName" allow-clear />
      </a-form-item>
      <a-form-item label="状态">
        <a-select v-model:value="query.status" allow-clear style="width: 120px" :options="statusOptions" />
      </a-form-item>
      <a-form-item>
        <a-button type="primary" @click="load">搜索</a-button>
        <a-button style="margin-left: 8px" @click="resetQuery">重置</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <div class="toolbar">
      <a-button v-if="can('system:dept:add')" type="primary" @click="openAdd()">新增</a-button>
    </div>
    <a-table :key="tree.length" row-key="deptId" :columns="columns" :data-source="tree" :loading="loading" :pagination="false" >
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">
          {{ isOn(record.status) ? '正常' : '停用' }}
        </template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('system:dept:add')" tip="新增" @click="openAdd(record)">
              <PlusOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:dept:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:dept:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>

    <a-modal v-model:open="open" :title="form.deptId ? '编辑部门' : '新增部门'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="上级部门">
          <a-tree-select
            v-model:value="form.parentId"
            :tree-data="parentTree"
            :field-names="{ label: 'deptName', value: 'deptId', children: 'children' }"
            allow-clear

            placeholder="请选择上级部门"
            style="width: 100%"
          />
        </a-form-item>
        <a-form-item label="部门名称" required><a-input v-model:value="form.deptName" /></a-form-item>
        <a-form-item label="显示排序"><a-input-number v-model:value="form.orderNum" :min="0" style="width: 100%" /></a-form-item>
        <a-form-item label="负责人"><a-input v-model:value="form.leader" /></a-form-item>
        <a-form-item label="联系电话"><a-input v-model:value="form.phone" /></a-form-item>
        <a-form-item label="邮箱"><a-input v-model:value="form.email" /></a-form-item>
        <a-form-item label="状态"><a-radio-group v-model:value="form.status" :options="statusOptions" /></a-form-item>
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
import { addDept, deptTree, getDept, listDept, removeDept, updateDept } from '@/api/system/dept'
import { handleTree, isOn, num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const statusOptions = [
  { label: '正常', value: '0' },
  { label: '停用', value: '1' },
]
const loading = ref(false)
const saving = ref(false)
const tree = ref([])
const parentTree = ref([])
const open = ref(false)
const query = reactive({ deptName: '', status: undefined })
const form = reactive(emptyForm())
const columns = [
  { title: '部门名称', dataIndex: 'deptName' },
  { title: '排序', dataIndex: 'orderNum', width: 80 },
  { title: '负责人', dataIndex: 'leader' },
  { title: '电话', dataIndex: 'phone' },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '创建时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', width: 180 },
]

function emptyForm() {
  return { deptId: undefined, parentId: undefined, deptName: '', orderNum: 0, leader: '', phone: '', email: '', status: '0' }
}

function normalize(nodes) {
  return (nodes || []).map((node) => ({
    ...node,
    deptId: Number(node.deptId),
    children: node.children?.length ? normalize(node.children) : undefined,
  }))
}

async function load() {
  loading.value = true
  try {
    const res = await listDept(query)
    tree.value = handleTree(res.data || [], 'deptId', 'parentId')
  } finally {
    loading.value = false
  }
}

function resetQuery() {
  query.deptName = ''
  query.status = undefined
  load()
}

async function loadParents() {
  const res = await deptTree()
  parentTree.value = normalize(res.data || [])
}

async function openAdd(parent) {
  Object.assign(form, emptyForm(), { parentId: parent ? Number(parent.deptId) : undefined })
  await loadParents()
  open.value = true
}

async function openEdit(record) {
  const res = await getDept(record.deptId)
  const dept = res.data || {}
  Object.assign(form, emptyForm(), {
    deptId: num(dept.deptId),
    parentId: Number(dept.parentId) > 0 ? Number(dept.parentId) : undefined,
    deptName: dept.deptName,
    orderNum: Number(dept.orderNum || 0),
    leader: dept.leader || '',
    phone: dept.phone || '',
    email: dept.email || '',
    status: String(dept.status ?? '0'),
  })
  await loadParents()
  open.value = true
}

async function submit() {
  if (!form.deptName) {
    message.warning('请填写部门名称')
    return
  }
  saving.value = true
  try {
    const payload = { ...form, parentId: form.parentId || 0 }
    if (payload.deptId) {
      await updateDept(payload)
    } else {
      await addDept(payload)
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
    title: '确认删除该部门？',
    onOk: async () => {
      await removeDept(record.deptId)
      message.success('删除成功')
      load()
    },
  })
}

onMounted(load)
</script>
