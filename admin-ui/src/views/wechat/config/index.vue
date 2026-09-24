<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="名称"><a-input v-model:value="query.name" allow-clear /></a-form-item>
      <a-form-item label="标题"><a-input v-model:value="query.title" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('wechat:config:add')" style="margin-left: 8px" @click="openAdd">新增</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="id" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('wechat:config:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('wechat:config:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="open" :title="form.id ? '编辑配置' : '新增配置'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
        <a-form-item label="名称" required><a-input v-model:value="form.name" /></a-form-item>
        <a-form-item label="标题" required><a-input v-model:value="form.title" /></a-form-item>
        <a-form-item label="内容"><a-textarea v-model:value="form.value" :rows="6" /></a-form-item>
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
import { addWechatConfig, getWechatConfig, listWechatConfig, removeWechatConfig, updateWechatConfig } from '@/api/builtin'
import { num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, name: '', title: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const form = reactive({ id: undefined, name: '', title: '', value: '' })
const columns = [
  { title: '编号', dataIndex: 'id', width: 70 },
  { title: '名称', dataIndex: 'name' },
  { title: '标题', dataIndex: 'title' },
  { title: '内容', dataIndex: 'value', ellipsis: true },
  { title: '操作', key: 'action', width: 100 },
]
async function load() {
  loading.value = true
  try {
    const res = await listWechatConfig(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function openAdd() { Object.assign(form, { id: undefined, name: '', title: '', value: '' }); open.value = true }
async function openEdit(record) {
  const res = await getWechatConfig(record.id)
  const row = res.data || {}
  Object.assign(form, { id: num(row.id), name: row.name, title: row.title, value: row.value || '' })
  open.value = true
}
async function submit() {
  if (!form.name || !form.title) { message.warning('请填写名称和标题'); return }
  saving.value = true
  try {
    if (form.id) await updateWechatConfig(form)
    else await addWechatConfig(form)
    message.success('保存成功'); open.value = false; load()
  } finally { saving.value = false }
}
function onRemove(record) {
  Modal.confirm({ title: '确认删除？', onOk: async () => { await removeWechatConfig(String(record.id)); message.success('删除成功'); load() } })
}
onMounted(load)
</script>