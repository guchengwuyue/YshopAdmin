<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
      <a-form-item label="岗位编码">
        <a-input v-model:value="query.postCode" allow-clear />
      </a-form-item>
      <a-form-item label="岗位名称">
        <a-input v-model:value="query.postName" allow-clear />
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
        <a-button v-if="can('system:post:add')" type="primary" @click="openAdd">新增</a-button>
        <a-button v-if="can('system:post:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
      </a-space>
    </div>
    <a-table
      row-key="postId"
      :columns="columns"
      :data-source="rows"
      :loading="loading"
      :pagination="pagination"
      :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }"
      @change="onTableChange"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">
          {{ isOn(record.status) ? '正常' : '停用' }}
        </template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('system:post:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:post:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>

    <a-modal v-model:open="open" :title="form.postId ? '编辑岗位' : '新增岗位'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="岗位名称" required><a-input v-model:value="form.postName" /></a-form-item>
        <a-form-item label="岗位编码" required><a-input v-model:value="form.postCode" /></a-form-item>
        <a-form-item label="显示顺序"><a-input-number v-model:value="form.postSort" :min="0" style="width: 100%" /></a-form-item>
        <a-form-item label="状态"><a-radio-group v-model:value="form.status" :options="statusOptions" /></a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="2" /></a-form-item>
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
import { addPost, getPost, listPost, removePost, updatePost } from '@/api/system/post'
import { isOn, num } from '@/utils/tree'
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
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, postCode: '', postName: '', status: undefined })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (total) => `共 ${total} 条` })
const form = reactive(emptyForm())
const columns = [
  { title: '岗位编号', dataIndex: 'postId', width: 90 },
  { title: '岗位编码', dataIndex: 'postCode' },
  { title: '岗位名称', dataIndex: 'postName' },
  { title: '显示顺序', dataIndex: 'postSort', width: 100 },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '创建时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', width: 100 },
]

function emptyForm() {
  return { postId: undefined, postCode: '', postName: '', postSort: 0, status: '0', remark: '' }
}

async function load() {
  loading.value = true
  try {
    const res = await listPost(query)
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
  query.postCode = ''
  query.postName = ''
  query.status = undefined
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
  const res = await getPost(record.postId)
  const post = res.data || {}
  Object.assign(form, emptyForm(), {
    postId: num(post.postId),
    postCode: post.postCode,
    postName: post.postName,
    postSort: Number(post.postSort || 0),
    status: String(post.status ?? '0'),
    remark: post.remark || '',
  })
  open.value = true
}

async function submit() {
  if (!form.postName || !form.postCode) {
    message.warning('请填写必填项')
    return
  }
  saving.value = true
  try {
    if (form.postId) {
      await updatePost(form)
    } else {
      await addPost(form)
    }
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onRemove(record) {
  const ids = record ? String(record.postId) : selected.value.join(',')
  Modal.confirm({
    title: '确认删除所选岗位？',
    onOk: async () => {
      await removePost(ids)
      message.success('删除成功')
      selected.value = []
      load()
    },
  })
}

onMounted(load)
</script>
