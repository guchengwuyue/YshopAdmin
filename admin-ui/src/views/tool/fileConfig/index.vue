<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="配置名"><a-input v-model:value="query.name" allow-clear /></a-form-item>
      <a-form-item label="存储器">
        <a-select v-model:value="query.storage" allow-clear style="width: 120px" :options="storageOptions" />
      </a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('tool:file:add')" style="margin-left: 8px" @click="openAdd">新增</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="id" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'master'">{{ String(record.master) === '1' ? '主配置' : '' }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('tool:file:edit') && String(record.master) !== '1'" tip="设主" @click="onMaster(record)">
              <StarOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('tool:file:edit')" tip="测试" @click="onTest(record)">
              <ApiOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('tool:file:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('tool:file:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="open" :title="form.id ? '编辑配置' : '新增配置'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="配置名" required><a-input v-model:value="form.name" /></a-form-item>
        <a-form-item label="存储器"><a-select v-model:value="form.storage" :options="storageOptions" /></a-form-item>
        <a-form-item label="Endpoint"><a-input v-model:value="form.endpoint" /></a-form-item>
        <a-form-item label="Bucket"><a-input v-model:value="form.bucket" /></a-form-item>
        <a-form-item label="AccessKey"><a-input v-model:value="form.accessKey" /></a-form-item>
        <a-form-item label="Secret"><a-input-password v-model:value="form.accessSecret" placeholder="留空则不修改" /></a-form-item>
        <a-form-item label="域名"><a-input v-model:value="form.domain" /></a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="2" /></a-form-item>
      </a-form>
    </a-modal>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { ApiOutlined, DeleteOutlined, EditOutlined, StarOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addFileConfig, getFileConfig, listFileConfig, masterFileConfig, removeFileConfig, testFileConfig, updateFileConfig } from '@/api/tool'
import { num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const storageOptions = [{ label: '本地', value: 'local' }, { label: 'S3', value: 's3' }]
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, name: '', storage: undefined })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const form = reactive(empty())
const columns = [
  { title: '编号', dataIndex: 'id', width: 70 },
  { title: '配置名', dataIndex: 'name' },
  { title: '存储器', dataIndex: 'storage', width: 90 },
  { title: '主配置', dataIndex: 'master', width: 90 },
  { title: '域名', dataIndex: 'domain', ellipsis: true },
  { title: '操作', key: 'action', width: 240 },
]
function empty() { return { id: undefined, name: '', storage: 'local', endpoint: 'public/storage', bucket: '', accessKey: '', accessSecret: '', domain: '', remark: '' } }
async function load() {
  loading.value = true
  try {
    const res = await listFileConfig(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function openAdd() { Object.assign(form, empty()); open.value = true }
async function openEdit(record) {
  const res = await getFileConfig(record.id)
  const row = res.data || {}
  Object.assign(form, empty(), { id: num(row.id), name: row.name, storage: row.storage || 'local', endpoint: row.endpoint, bucket: row.bucket, accessKey: row.accessKey, accessSecret: '', domain: row.domain, remark: row.remark || '' })
  open.value = true
}
async function submit() {
  if (!form.name) { message.warning('请填写配置名'); return }
  saving.value = true
  try {
    if (form.id) await updateFileConfig(form)
    else await addFileConfig(form)
    message.success('保存成功'); open.value = false; load()
  } finally { saving.value = false }
}
function onMaster(record) {
  Modal.confirm({ title: '设为主配置？', onOk: async () => { await masterFileConfig(record.id); message.success('已设置'); load() } })
}
async function onTest(record) {
  await testFileConfig(record.id)
  message.success('连通测试成功')
}
function onRemove(record) {
  Modal.confirm({ title: '确认删除？', onOk: async () => { await removeFileConfig(String(record.id)); message.success('删除成功'); load() } })
}
onMounted(load)
</script>
