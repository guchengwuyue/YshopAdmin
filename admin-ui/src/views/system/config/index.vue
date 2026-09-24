<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
      <a-form-item label="参数名称"><a-input v-model:value="query.configName" allow-clear /></a-form-item>
      <a-form-item label="参数键名"><a-input v-model:value="query.configKey" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button style="margin-left: 8px" @click="resetQuery">重置</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <div class="toolbar">
      <a-space>
        <a-button v-if="can('system:config:add')" type="primary" @click="openAdd">新增</a-button>
        <a-button v-if="can('system:config:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
      </a-space>
    </div>
    <a-table row-key="configId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'configType'">{{ record.configType === 'Y' ? '是' : '否' }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('system:config:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:config:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="open" :title="form.configId ? '编辑参数' : '新增参数'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="参数名称" required><a-input v-model:value="form.configName" /></a-form-item>
        <a-form-item label="参数键名" required><a-input v-model:value="form.configKey" /></a-form-item>
        <a-form-item label="参数键值" required><a-textarea v-model:value="form.configValue" :rows="3" /></a-form-item>
        <a-form-item label="系统内置">
          <a-radio-group v-model:value="form.configType" :options="[{ label: '是', value: 'Y' }, { label: '否', value: 'N' }]" />
        </a-form-item>
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
import { addConfig, getConfig, listConfig, removeConfig, updateConfig } from '@/api/system/config'
import { num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selected = ref([])
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, configName: '', configKey: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const form = reactive(empty())
const columns = [
  { title: '参数主键', dataIndex: 'configId', width: 90 },
  { title: '参数名称', dataIndex: 'configName' },
  { title: '参数键名', dataIndex: 'configKey' },
  { title: '参数键值', dataIndex: 'configValue', ellipsis: true },
  { title: '系统内置', dataIndex: 'configType', width: 90 },
  { title: '操作', key: 'action', width: 100 },
]
function empty() { return { configId: undefined, configName: '', configKey: '', configValue: '', configType: 'N', remark: '' } }
async function load() {
  loading.value = true
  try {
    const res = await listConfig(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
    pagination.pageSize = query.pageSize
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function resetQuery() { query.configName = ''; query.configKey = ''; search() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function openAdd() { Object.assign(form, empty()); open.value = true }
async function openEdit(record) {
  const res = await getConfig(record.configId)
  const row = res.data || {}
  Object.assign(form, empty(), { configId: num(row.configId), configName: row.configName, configKey: row.configKey, configValue: row.configValue, configType: row.configType || 'N', remark: row.remark || '' })
  open.value = true
}
async function submit() {
  if (!form.configName || !form.configKey) { message.warning('请填写必填项'); return }
  saving.value = true
  try {
    if (form.configId) await updateConfig(form)
    else await addConfig(form)
    message.success('保存成功'); open.value = false; load()
  } finally { saving.value = false }
}
function onRemove(record) {
  const ids = record ? String(record.configId) : selected.value.join(',')
  Modal.confirm({ title: '确认删除所选参数？', onOk: async () => { await removeConfig(ids); message.success('删除成功'); selected.value = []; load() } })
}
onMounted(load)
</script>