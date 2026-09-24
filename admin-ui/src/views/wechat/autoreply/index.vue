<template>
  <div class="page-stack">
    <div class="page-card">
      <a-form layout="inline" class="search">
        <a-form-item label="标题"><a-input v-model:value="query.title" allow-clear /></a-form-item>
        <a-form-item label="匹配文本"><a-input v-model:value="query.text" allow-clear /></a-form-item>
        <a-form-item>
          <a-button type="primary" @click="search">搜索</a-button>
          <a-button v-if="can('wechat:autoreply:add')" style="margin-left: 8px" @click="openAdd">新增</a-button>
        </a-form-item>
      </a-form>
    </div>
    <div class="page-card">
      <a-table row-key="id" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'createTime' || column.dataIndex === 'updateTime'">
            {{ formatTime(record[column.dataIndex]) }}
          </template>
          <template v-else-if="column.dataIndex === 'status'">
            <a-badge :status="record.status === 'normal' ? 'success' : 'default'" :text="statusText(record.status)" />
          </template>
          <template v-else-if="column.key === 'action'">
            <a-space :size="6">
              <TableActionBtn v-if="can('wechat:autoreply:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
                <EditOutlined />
              </TableActionBtn>
              <TableActionBtn v-if="can('wechat:autoreply:remove')" tip="删除" tone="danger" @click="onRemove(record)">
                <DeleteOutlined />
              </TableActionBtn>
            </a-space>
          </template>
        </template>
      </a-table>

      <a-modal v-model:open="open" :title="form.id ? '编辑回复' : '新增回复'" :confirm-loading="saving" width="720px" @ok="submit">
        <a-form :model="form" :label-col="{ span: 4 }" :wrapper-col="{ span: 18 }">
          <a-form-item label="标题"><a-input v-model:value="form.title" /></a-form-item>
          <a-form-item label="匹配文本" required>
            <a-input v-model:value="form.text" placeholder="支持正则表达式" />
          </a-form-item>
          <a-form-item label="内容" required>
            <div class="clickbox">
              <div class="create-click" @click="openSelectResource">
                <div v-if="form.responseTitle || form.eventkey" class="keytitle">资源名:{{ form.responseTitle || form.eventkey }}</div>
                <PlusOutlined class="create-icon" />
                <strong>选择现有资源</strong>
              </div>
              <div class="create-click" @click="openAddResource">
                <PlusOutlined class="create-icon" />
                <strong>添加新资源</strong>
              </div>
            </div>
          </a-form-item>
          <a-form-item label="备注"><a-input v-model:value="form.remark" /></a-form-item>
          <a-form-item label="状态">
            <a-radio-group v-model:value="form.status" :options="statusOptions" />
          </a-form-item>
        </a-form>
      </a-modal>

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
            <a-radio-group v-model:value="resourceForm.type" :options="typeOptions" />
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
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons-vue'
import dayjs from 'dayjs'
import { useUserStore } from '@/store/user'
import {
  addAutoreply,
  addResponse,
  getAutoreply,
  listAutoreply,
  listResponse,
  removeAutoreply,
  updateAutoreply,
} from '@/api/builtin'
import { num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const open = ref(false)
const selectOpen = ref(false)
const selectLoading = ref(false)
const selectRows = ref([])
const resourceOpen = ref(false)
const resourceSaving = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, title: '', text: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const selectQuery = reactive({ pageNum: 1, pageSize: 10, title: '' })
const selectPagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const form = reactive(empty())
const resourceForm = reactive(emptyResource())
const statusOptions = [
  { label: '正常', value: 'normal' },
  { label: '隐藏', value: 'hidden' },
]
const typeOptions = [
  { label: '文本', value: 'text' },
  { label: '应用', value: 'app' },
]
const columns = [
  { title: 'Id', dataIndex: 'id', width: 70 },
  { title: '标题', dataIndex: 'title' },
  { title: '匹配文本', dataIndex: 'text' },
  { title: '响应标识', dataIndex: 'eventkey' },
  { title: '备注', dataIndex: 'remark' },
  { title: '创建时间', dataIndex: 'createTime', width: 170, sorter: (a, b) => timeValue(a.createTime) - timeValue(b.createTime) },
  { title: '更新时间', dataIndex: 'updateTime', width: 170, sorter: (a, b) => timeValue(a.updateTime) - timeValue(b.updateTime) },
  { title: '状态', dataIndex: 'status', width: 100 },
  { title: '操作', key: 'action', width: 100 },
]
const selectColumns = [
  { title: 'ID', dataIndex: 'id', width: 70 },
  { title: '类型', dataIndex: 'type', width: 90 },
  { title: '资源标题', dataIndex: 'title' },
  { title: '事件标识', dataIndex: 'eventkey' },
  { title: '状态', dataIndex: 'status', width: 100 },
  { title: '操作', key: 'action', width: 80 },
]

function empty() {
  return { id: undefined, title: '', text: '', eventkey: '', responseTitle: '', weigh: 0, status: 'normal', remark: '' }
}
function emptyResource() {
  return { title: '', type: 'text', contentText: '', app: '', status: 'normal', remark: '', eventkey: '' }
}
function statusText(status) {
  return status === 'normal' ? '正常' : status === 'hidden' ? '隐藏' : (status || '-')
}
function timeValue(v) {
  if (v == null || v === '') return 0
  if (typeof v === 'number') return v < 1e12 ? v : Math.floor(v / 1000)
  const n = Number(v)
  if (!Number.isNaN(n) && String(v).trim() !== '' && /^\d+$/.test(String(v).trim())) {
    return n < 1e12 ? n : Math.floor(n / 1000)
  }
  const d = dayjs(v)
  return d.isValid() ? d.unix() : 0
}
function formatTime(v) {
  if (v == null || v === '') return '-'
  if (typeof v === 'number' || /^\d+$/.test(String(v).trim())) {
    const n = Number(v)
    return dayjs(n < 1e12 ? n * 1000 : n).format('YYYY-MM-DD HH:mm:ss')
  }
  const d = dayjs(v)
  return d.isValid() ? d.format('YYYY-MM-DD HH:mm:ss') : String(v)
}
function uniqEventkey() {
  return Date.now().toString(16) + Math.random().toString(16).slice(2, 6)
}
async function load() {
  loading.value = true
  try {
    const res = await listAutoreply(query)
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
  const res = await getAutoreply(record.id)
  const row = res.data || {}
  Object.assign(form, empty(), {
    id: num(row.id),
    title: row.title,
    text: row.text,
    eventkey: row.eventkey,
    responseTitle: row.responseTitle || '',
    weigh: Number(row.weigh || 0),
    status: row.status || 'normal',
    remark: row.remark || '',
  })
  open.value = true
}
async function submit() {
  if (!form.text) {
    message.warning('请填写匹配文本')
    return
  }
  if (!form.eventkey) {
    message.warning('请选择响应资源')
    return
  }
  saving.value = true
  try {
    const payload = {
      id: form.id,
      title: form.title,
      text: form.text,
      eventkey: form.eventkey,
      weigh: form.weigh,
      status: form.status,
      remark: form.remark,
    }
    if (form.id) await updateAutoreply(payload)
    else await addAutoreply(payload)
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
      await removeAutoreply(String(record.id))
      message.success('删除成功')
      load()
    },
  })
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
  form.eventkey = record.eventkey || ''
  form.responseTitle = record.title || ''
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
    form.eventkey = resourceForm.eventkey
    form.responseTitle = resourceForm.title
    message.success('资源已添加')
    resourceOpen.value = false
  } finally {
    resourceSaving.value = false
  }
}
onMounted(load)
</script>
<style scoped>
.clickbox {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
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
.create-click strong {
  font-weight: 600;
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
</style>
