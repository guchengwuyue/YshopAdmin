<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="文件名"><a-input v-model:value="query.name" allow-clear /></a-form-item>
      <a-form-item label="类型"><a-input v-model:value="query.type" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-upload v-if="can('tool:file:upload')" :show-upload-list="false" :before-upload="onUpload" style="margin-left: 8px">
          <a-button>上传</a-button>
        </a-upload>
        <a-button v-if="can('tool:file:remove')" danger style="margin-left: 8px" :disabled="!selected.length" @click="onRemove()">删除</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="id" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'url'">
          <a :href="record.url" target="_blank">{{ record.url }}</a>
        </template>
        <template v-else-if="column.key === 'action'">
          <TableActionBtn v-if="can('tool:file:remove')" tip="删除" tone="danger" @click="onRemove(record)">
            <DeleteOutlined />
          </TableActionBtn>
        </template>
      </template>
    </a-table>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { listFile, removeFile, uploadFile } from '@/api/tool'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const rows = ref([])
const selected = ref([])
const query = reactive({ pageNum: 1, pageSize: 10, name: '', type: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const columns = [
  { title: '编号', dataIndex: 'id', width: 80 },
  { title: '文件名', dataIndex: 'name' },
  { title: '类型', dataIndex: 'type', width: 140 },
  { title: '大小', dataIndex: 'sizeText', width: 100 },
  { title: '地址', dataIndex: 'url', ellipsis: true },
  { title: '上传时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', width: 70 },
]
async function load() {
  loading.value = true
  try {
    const res = await listFile(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
async function onUpload(file) {
  await uploadFile(file)
  message.success('上传成功')
  load()
  return false
}
function onRemove(record) {
  const ids = record ? String(record.id) : selected.value.join(',')
  Modal.confirm({ title: '确认删除所选文件？', onOk: async () => { await removeFile(ids); message.success('删除成功'); selected.value = []; load() } })
}
onMounted(load)
</script>