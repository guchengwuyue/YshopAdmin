<template>
  <div class="page-stack">
    <div class="page-card">
      <a-form layout="inline" class="search">
        <a-form-item label="资源标题"><a-input v-model:value="query.title" allow-clear /></a-form-item>
        <a-form-item label="类型">
          <a-select v-model:value="query.type" allow-clear style="width: 120px" :options="typeOptions" />
        </a-form-item>
        <a-form-item>
          <a-button type="primary" @click="search">搜索</a-button>
          <a-button v-if="can('wechat:response:add')" style="margin-left: 8px" @click="openAdd">新增</a-button>
        </a-form-item>
      </a-form>
    </div>
    <div class="page-card">
      <a-table row-key="id" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'status'">
            <a-badge :status="record.status === 'normal' ? 'success' : 'default'" :text="statusText(record.status)" />
          </template>
          <template v-else-if="column.key === 'action'">
            <a-space :size="6">
              <TableActionBtn v-if="can('wechat:response:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
                <EditOutlined />
              </TableActionBtn>
              <TableActionBtn v-if="can('wechat:response:remove')" tip="删除" tone="danger" @click="onRemove(record)">
                <DeleteOutlined />
              </TableActionBtn>
            </a-space>
          </template>
        </template>
      </a-table>
      <a-modal v-model:open="open" :title="form.id ? '编辑资源' : '新增资源'" :confirm-loading="saving" @ok="submit">
        <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
          <a-form-item label="资源标题" required><a-input v-model:value="form.title" /></a-form-item>
          <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="3" /></a-form-item>
          <a-form-item label="类型">
            <a-radio-group v-model:value="form.type" :options="typeOptions" />
          </a-form-item>
          <a-form-item v-if="form.type === 'text'" label="文本内容" required>
            <a-textarea v-model:value="form.contentText" :rows="4" />
          </a-form-item>
          <a-form-item v-else label="应用" required>
            <a-input v-model:value="form.app" placeholder="应用标识" />
          </a-form-item>
          <a-form-item label="状态">
            <a-radio-group v-model:value="form.status" :options="statusOptions" />
          </a-form-item>
        </a-form>
      </a-modal>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addResponse, getResponse, listResponse, removeResponse, updateResponse } from '@/api/builtin'
import { num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, title: '', type: undefined })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const form = reactive(empty())
const typeOptions = [
  { label: '文本', value: 'text' },
  { label: '应用', value: 'app' },
]
const statusOptions = [
  { label: '正常', value: 'normal' },
  { label: '隐藏', value: 'hidden' },
]
const columns = [
  { title: 'ID', dataIndex: 'id', width: 70 },
  { title: '类型', dataIndex: 'type', width: 90 },
  { title: '资源标题', dataIndex: 'title' },
  { title: '事件标识', dataIndex: 'eventkey' },
  { title: '状态', dataIndex: 'status', width: 100 },
  { title: '操作', key: 'action', width: 100 },
]

function empty() {
  return { id: undefined, title: '', eventkey: '', type: 'text', contentText: '', app: '', status: 'normal', remark: '' }
}
function statusText(status) {
  return status === 'normal' ? '正常' : status === 'hidden' ? '隐藏' : (status || '-')
}
async function load() {
  loading.value = true
  try {
    const res = await listResponse(query)
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
  Object.assign(form, empty())
  open.value = true
}
async function openEdit(record) {
  const res = await getResponse(record.id)
  const row = res.data || {}
  const contentArr = row.contentArr || {}
  const content = contentArr.content ?? row.content ?? ''
  Object.assign(form, empty(), {
    id: num(row.id),
    title: row.title,
    eventkey: row.eventkey,
    type: row.type || 'text',
    contentText: typeof content === 'string' ? content : '',
    app: contentArr.app || '',
    status: row.status || 'normal',
    remark: row.remark || '',
  })
  open.value = true
}
function buildPayload() {
  const payload = {
    id: form.id,
    title: form.title,
    eventkey: form.eventkey,
    type: form.type,
    status: form.status,
    remark: form.remark,
  }
  if (form.type === 'text') {
    payload.contentText = form.contentText
  } else {
    payload.content = { app: form.app }
  }
  return payload
}
async function submit() {
  if (!form.title) {
    message.warning('请填写资源标题')
    return
  }
  if (form.type === 'text' && !form.contentText) {
    message.warning('请填写文本内容')
    return
  }
  if (form.type === 'app' && !form.app) {
    message.warning('请填写应用标识')
    return
  }
  saving.value = true
  try {
    const payload = buildPayload()
    if (form.id) await updateResponse(payload)
    else await addResponse(payload)
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}
function onRemove(record) {
  Modal.confirm({
    title: '确认删除？',
    onOk: async () => {
      await removeResponse(String(record.id))
      message.success('删除成功')
      load()
    },
  })
}
onMounted(load)
</script>
