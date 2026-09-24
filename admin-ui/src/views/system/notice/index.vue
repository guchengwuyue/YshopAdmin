<template>
  <div class="page-stack">
    <div class="page-card">
      <a-form layout="inline" :model="query" class="search">
        <a-form-item label="公告标题">
          <a-input v-model:value="query.noticeTitle" allow-clear />
        </a-form-item>
        <a-form-item label="操作人员">
          <a-input v-model:value="query.createBy" allow-clear />
        </a-form-item>
        <a-form-item label="类型">
          <a-select v-model:value="query.noticeType" allow-clear style="width: 120px" :options="typeOptions" />
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
          <a-button v-if="can('system:notice:add')" type="primary" @click="openAdd">新增</a-button>
          <a-button v-if="can('system:notice:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
        </a-space>
      </div>
      <a-table
        row-key="noticeId"
        :columns="columns"
        :data-source="rows"
        :loading="loading"
        :pagination="pagination"
        :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }"
        @change="onTableChange"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'noticeType'">
            {{ typeLabel(record.noticeType) }}
          </template>
          <template v-else-if="column.dataIndex === 'status'">
            {{ isOn(record.status) ? '正常' : '关闭' }}
          </template>
          <template v-else-if="column.key === 'action'">
            <a-space :size="6">
              <TableActionBtn v-if="can('system:notice:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
                <EditOutlined />
              </TableActionBtn>
              <TableActionBtn v-if="can('system:notice:remove')" tip="删除" tone="danger" @click="onRemove(record)">
                <DeleteOutlined />
              </TableActionBtn>
            </a-space>
          </template>
        </template>
      </a-table>

      <a-modal
        v-model:open="open"
        :title="form.noticeId ? '编辑公告' : '新增公告'"
        :confirm-loading="saving"
        width="720px"
        @ok="submit"
      >
        <a-form :model="form" :label-col="{ span: 4 }" :wrapper-col="{ span: 18 }">
          <a-form-item label="公告标题" required>
            <a-input v-model:value="form.noticeTitle" :maxlength="50" show-count />
          </a-form-item>
          <a-form-item label="公告类型" required>
            <a-select v-model:value="form.noticeType" :options="typeOptions" />
          </a-form-item>
          <a-form-item label="状态">
            <a-radio-group v-model:value="form.status" :options="statusOptions" />
          </a-form-item>
          <a-form-item label="内容">
            <a-textarea v-model:value="form.noticeContent" :rows="8" placeholder="支持纯文本或 HTML" />
          </a-form-item>
          <a-form-item label="备注">
            <a-textarea v-model:value="form.remark" :rows="2" />
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
import { addNotice, getNotice, listNotice, removeNotice, updateNotice } from '@/api/system/notice'
import { isOn, num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)

const typeOptions = [
  { label: '通知', value: '1' },
  { label: '公告', value: '2' },
]
const statusOptions = [
  { label: '正常', value: '0' },
  { label: '关闭', value: '1' },
]

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selected = ref([])
const open = ref(false)
const query = reactive({
  pageNum: 1,
  pageSize: 10,
  noticeTitle: '',
  createBy: '',
  noticeType: undefined,
})
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showTotal: (total) => `共 ${total} 条`,
})
const form = reactive(emptyForm())
const columns = [
  { title: '序号', dataIndex: 'noticeId', width: 80 },
  { title: '公告标题', dataIndex: 'noticeTitle', ellipsis: true },
  { title: '类型', dataIndex: 'noticeType', width: 90 },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '创建者', dataIndex: 'createBy', width: 110 },
  { title: '创建时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', width: 100 },
]

function emptyForm() {
  return {
    noticeId: undefined,
    noticeTitle: '',
    noticeType: '1',
    noticeContent: '',
    status: '0',
    remark: '',
  }
}

function typeLabel(v) {
  const hit = typeOptions.find((o) => o.value === String(v))
  return hit ? hit.label : v
}

async function load() {
  loading.value = true
  try {
    const res = await listNotice(query)
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
  query.noticeTitle = ''
  query.createBy = ''
  query.noticeType = undefined
  search()
}

function onTableChange(page) {
  query.pageNum = page.current
  query.pageSize = page.pageSize
  load()
}

function openAdd() {
  Object.assign(form, emptyForm())
  open.value = true
}

async function openEdit(record) {
  const res = await getNotice(record.noticeId)
  const row = res.data || {}
  Object.assign(form, emptyForm(), {
    noticeId: num(row.noticeId),
    noticeTitle: row.noticeTitle || '',
    noticeType: String(row.noticeType ?? '1'),
    noticeContent: row.noticeContent || '',
    status: String(row.status ?? '0'),
    remark: row.remark || '',
  })
  open.value = true
}

async function submit() {
  if (!form.noticeTitle) {
    message.warning('请填写公告标题')
    return
  }
  saving.value = true
  try {
    if (form.noticeId) {
      await updateNotice(form)
    } else {
      await addNotice(form)
    }
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onRemove(record) {
  const ids = record ? String(record.noticeId) : selected.value.join(',')
  Modal.confirm({
    title: '确认删除所选公告？',
    onOk: async () => {
      await removeNotice(ids)
      message.success('删除成功')
      selected.value = []
      load()
    },
  })
}

onMounted(load)
</script>
