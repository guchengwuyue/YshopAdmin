<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="表名称"><a-input v-model:value="query.tableName" allow-clear /></a-form-item>
      <a-form-item label="表描述"><a-input v-model:value="query.tableComment" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('tool:gen:code')" style="margin-left: 8px" @click="openImport">导入</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="tableId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('tool:gen:preview')" tip="预览" @click="onPreview(record)">
              <EyeOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('tool:gen:code')" tip="生成" @click="onDownload(record)">
              <CodeOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('tool:gen:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('tool:gen:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="importOpen" title="导入表" width="720" @ok="doImport">
      <a-table row-key="tableName" :columns="dbColumns" :data-source="dbRows" :pagination="false" :row-selection="{ selectedRowKeys: dbSelected, onChange: (keys) => (dbSelected = keys) }" size="small" />
    </a-modal>
    <a-modal v-model:open="editOpen" title="编辑生成配置" :confirm-loading="saving" width="1100" @ok="saveEdit">
      <a-form layout="inline" style="margin-bottom: 12px">
        <a-form-item label="类名"><a-input v-model:value="edit.className" style="width: 140px" /></a-form-item>
        <a-form-item label="模块"><a-input v-model:value="edit.moduleName" style="width: 120px" /></a-form-item>
        <a-form-item label="业务名"><a-input v-model:value="edit.businessName" style="width: 120px" /></a-form-item>
        <a-form-item label="功能名"><a-input v-model:value="edit.functionName" style="width: 140px" /></a-form-item>
        <a-form-item label="作者"><a-input v-model:value="edit.functionAuthor" style="width: 120px" /></a-form-item>
      </a-form>
      <a-table row-key="columnId" size="small" :pagination="false" :scroll="{ x: 1000 }" :data-source="edit.columns" :columns="colColumns">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'columnComment'"><a-input v-model:value="record.columnComment" size="small" /></template>
          <template v-else-if="column.dataIndex === 'htmlType'">
            <a-select v-model:value="record.htmlType" size="small" style="width: 100px" :options="htmlTypeOptions" />
          </template>
          <template v-else-if="column.dataIndex === 'queryType'">
            <a-select v-model:value="record.queryType" size="small" style="width: 90px" :options="queryTypeOptions" />
          </template>
          <template v-else-if="column.dataIndex === 'isInsert'">
            <a-checkbox :checked="record.isInsert === '1'" @change="(e) => (record.isInsert = e.target.checked ? '1' : '0')" />
          </template>
          <template v-else-if="column.dataIndex === 'isEdit'">
            <a-checkbox :checked="record.isEdit === '1'" @change="(e) => (record.isEdit = e.target.checked ? '1' : '0')" />
          </template>
          <template v-else-if="column.dataIndex === 'isList'">
            <a-checkbox :checked="record.isList === '1'" @change="(e) => (record.isList = e.target.checked ? '1' : '0')" />
          </template>
          <template v-else-if="column.dataIndex === 'isQuery'">
            <a-checkbox :checked="record.isQuery === '1'" @change="(e) => (record.isQuery = e.target.checked ? '1' : '0')" />
          </template>
          <template v-else-if="column.dataIndex === 'isRequired'">
            <a-checkbox :checked="record.isRequired === '1'" @change="(e) => (record.isRequired = e.target.checked ? '1' : '0')" />
          </template>
        </template>
      </a-table>
    </a-modal>
    <a-modal v-model:open="previewOpen" title="代码预览" width="960" :footer="null">
      <a-tabs>
        <a-tab-pane v-for="(content, name) in preview" :key="name" :tab="shortTab(name)">
          <pre class="pre">{{ content }}</pre>
        </a-tab-pane>
      </a-tabs>
    </a-modal>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { CodeOutlined, DeleteOutlined, EditOutlined, EyeOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { downloadGen, getGen, importTable, listDbTable, listGen, previewGen, removeGen, updateGen } from '@/api/tool'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const query = reactive({ pageNum: 1, pageSize: 10, tableName: '', tableComment: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const importOpen = ref(false)
const dbRows = ref([])
const dbSelected = ref([])
const editOpen = ref(false)
const edit = reactive({ tableId: 0, className: '', moduleName: '', businessName: '', functionName: '', functionAuthor: '', columns: [] })
const previewOpen = ref(false)
const preview = ref({})
const htmlTypeOptions = [
  { label: 'input', value: 'input' },
  { label: 'textarea', value: 'textarea' },
  { label: 'select', value: 'select' },
  { label: 'radio', value: 'radio' },
  { label: 'datetime', value: 'datetime' },
]
const queryTypeOptions = [
  { label: 'EQ', value: 'EQ' },
  { label: 'LIKE', value: 'LIKE' },
]
const columns = [
  { title: '表名称', dataIndex: 'tableName' },
  { title: '表描述', dataIndex: 'tableComment' },
  { title: '实体', dataIndex: 'className' },
  { title: '创建时间', dataIndex: 'createTime', width: 170 },
  { title: '操作', key: 'action', width: 240 },
]
const dbColumns = [{ title: '表名称', dataIndex: 'tableName' }, { title: '表描述', dataIndex: 'tableComment' }]
const colColumns = [
  { title: '列名', dataIndex: 'columnName', width: 120, fixed: 'left' },
  { title: '描述', dataIndex: 'columnComment', width: 140 },
  { title: '字段', dataIndex: 'javaField', width: 110 },
  { title: '控件', dataIndex: 'htmlType', width: 110 },
  { title: '查询', dataIndex: 'queryType', width: 100 },
  { title: '插入', dataIndex: 'isInsert', width: 60, align: 'center' },
  { title: '编辑', dataIndex: 'isEdit', width: 60, align: 'center' },
  { title: '列表', dataIndex: 'isList', width: 60, align: 'center' },
  { title: '条件', dataIndex: 'isQuery', width: 60, align: 'center' },
  { title: '必填', dataIndex: 'isRequired', width: 60, align: 'center' },
]
function shortTab(name) {
  const parts = String(name).split('/')
  return parts.slice(-2).join('/')
}
async function load() {
  loading.value = true
  try {
    const res = await listGen(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
async function openImport() {
  const res = await listDbTable({ pageNum: 1, pageSize: 100 })
  dbRows.value = res.rows || []
  dbSelected.value = []
  importOpen.value = true
}
async function doImport() {
  if (!dbSelected.value.length) { message.warning('请选择表'); return }
  await importTable(dbSelected.value.join(','))
  message.success('导入成功')
  importOpen.value = false
  load()
}
async function openEdit(record) {
  const res = await getGen(record.tableId)
  const row = res.data || {}
  Object.assign(edit, {
    tableId: row.tableId,
    className: row.className,
    moduleName: row.moduleName,
    businessName: row.businessName,
    functionName: row.functionName,
    functionAuthor: row.functionAuthor,
    tableComment: row.tableComment,
    packageName: row.packageName,
    genType: row.genType,
    genPath: row.genPath,
    columns: row.columns || [],
  })
  editOpen.value = true
}
async function saveEdit() {
  saving.value = true
  try {
    await updateGen(edit)
    message.success('保存成功')
    editOpen.value = false
    load()
  } finally { saving.value = false }
}
async function onPreview(record) {
  const res = await previewGen(record.tableId)
  preview.value = res.data || {}
  previewOpen.value = true
}
async function onDownload(record) {
  const res = await downloadGen(record.tableId)
  const blob = res.data
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'gen.zip'
  a.click()
  URL.revokeObjectURL(url)
}
function onRemove(record) {
  Modal.confirm({ title: '确认删除？', onOk: async () => { await removeGen(String(record.tableId)); message.success('删除成功'); load() } })
}
onMounted(load)
</script>
<style scoped>
.pre { max-height: 480px; overflow: auto; background: #f7f7f7; padding: 12px; font-size: 12px; }
</style>
