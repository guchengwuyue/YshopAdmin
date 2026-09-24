<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
      <a-form-item label="字典名称"><a-input v-model:value="query.dictName" allow-clear /></a-form-item>
      <a-form-item label="字典类型"><a-input v-model:value="query.dictType" allow-clear /></a-form-item>
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
        <a-button v-if="can('system:dict:add')" type="primary" @click="openAdd">新增</a-button>
        <a-button v-if="can('system:dict:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
      </a-space>
    </div>
    <a-table row-key="dictId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">{{ isOn(record.status) ? '正常' : '停用' }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn tip="字典数据" @click="openData(record)">
              <UnorderedListOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:dict:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:dict:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="open" :title="form.dictId ? '编辑字典' : '新增字典'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="字典名称" required><a-input v-model:value="form.dictName" /></a-form-item>
        <a-form-item label="字典类型" required><a-input v-model:value="form.dictType" /></a-form-item>
        <a-form-item label="状态"><a-radio-group v-model:value="form.status" :options="statusOptions" /></a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="2" /></a-form-item>
      </a-form>
    </a-modal>
    <a-drawer v-model:open="dataOpen" :title="'字典数据 · ' + dataType" width="760">
      <a-button v-if="can('system:dict:add')" type="primary" style="margin-bottom: 12px" @click="openDataAdd">新增</a-button>
      <a-table row-key="dictCode" :columns="dataColumns" :data-source="dataRows" :loading="dataLoading" :pagination="false" size="small">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'action'">
            <a-space :size="6">
              <TableActionBtn v-if="can('system:dict:edit')" tip="编辑" tone="edit" @click="openDataEdit(record)">
                <EditOutlined />
              </TableActionBtn>
              <TableActionBtn v-if="can('system:dict:remove')" tip="删除" tone="danger" @click="onDataRemove(record)">
                <DeleteOutlined />
              </TableActionBtn>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-drawer>
    <a-modal v-model:open="dataFormOpen" :title="dataForm.dictCode ? '编辑字典数据' : '新增字典数据'" :confirm-loading="saving" @ok="submitData">
      <a-form :model="dataForm" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="字典标签" required><a-input v-model:value="dataForm.dictLabel" /></a-form-item>
        <a-form-item label="字典键值" required><a-input v-model:value="dataForm.dictValue" /></a-form-item>
        <a-form-item label="排序"><a-input-number v-model:value="dataForm.dictSort" :min="0" style="width: 100%" /></a-form-item>
        <a-form-item label="状态"><a-radio-group v-model:value="dataForm.status" :options="statusOptions" /></a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="dataForm.remark" :rows="2" /></a-form-item>
      </a-form>
    </a-modal>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, UnorderedListOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addDict, addDictData, getDict, listDict, listDictData, removeDict, removeDictData, updateDict, updateDictData } from '@/api/system/dict'
import { isOn, num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const statusOptions = [{ label: '正常', value: '0' }, { label: '停用', value: '1' }]
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selected = ref([])
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, dictName: '', dictType: '', status: undefined })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (total) => `共 ${total} 条` })
const form = reactive(emptyForm())
const columns = [
  { title: '字典编号', dataIndex: 'dictId', width: 90 },
  { title: '字典名称', dataIndex: 'dictName' },
  { title: '字典类型', dataIndex: 'dictType' },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '备注', dataIndex: 'remark' },
  { title: '操作', key: 'action', width: 200 },
]
const dataOpen = ref(false)
const dataType = ref('')
const dataRows = ref([])
const dataLoading = ref(false)
const dataFormOpen = ref(false)
const dataForm = reactive(emptyData())
const dataColumns = [
  { title: '标签', dataIndex: 'dictLabel' },
  { title: '键值', dataIndex: 'dictValue' },
  { title: '排序', dataIndex: 'dictSort', width: 70 },
  { title: '操作', key: 'action', width: 100 },
]

function emptyForm() {
  return { dictId: undefined, dictName: '', dictType: '', status: '0', remark: '' }
}
function emptyData() {
  return { dictCode: undefined, dictType: '', dictLabel: '', dictValue: '', dictSort: 0, status: '0', remark: '' }
}
async function load() {
  loading.value = true
  try {
    const res = await listDict(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
    pagination.pageSize = query.pageSize
  } finally {
    loading.value = false
  }
}
function search() { query.pageNum = 1; load() }
function resetQuery() { query.dictName = ''; query.dictType = ''; query.status = undefined; search() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function openAdd() { Object.assign(form, emptyForm()); open.value = true }
async function openEdit(record) {
  const res = await getDict(record.dictId)
  const row = res.data || {}
  Object.assign(form, emptyForm(), { dictId: num(row.dictId), dictName: row.dictName, dictType: row.dictType, status: String(row.status ?? '0'), remark: row.remark || '' })
  open.value = true
}
async function submit() {
  if (!form.dictName || !form.dictType) { message.warning('请填写必填项'); return }
  saving.value = true
  try {
    if (form.dictId) await updateDict(form)
    else await addDict(form)
    message.success('保存成功')
    open.value = false
    load()
  } finally { saving.value = false }
}
function onRemove(record) {
  const ids = record ? String(record.dictId) : selected.value.join(',')
  Modal.confirm({ title: '确认删除所选字典？', onOk: async () => { await removeDict(ids); message.success('删除成功'); selected.value = []; load() } })
}
async function openData(record) {
  dataType.value = record.dictType
  dataOpen.value = true
  await loadData()
}
async function loadData() {
  dataLoading.value = true
  try {
    const res = await listDictData({ dictType: dataType.value, pageNum: 1, pageSize: 200 })
    dataRows.value = res.rows || []
  } finally { dataLoading.value = false }
}
function openDataAdd() { Object.assign(dataForm, emptyData(), { dictType: dataType.value }); dataFormOpen.value = true }
function openDataEdit(record) {
  Object.assign(dataForm, emptyData(), { dictCode: num(record.dictCode), dictType: record.dictType, dictLabel: record.dictLabel, dictValue: record.dictValue, dictSort: Number(record.dictSort || 0), status: String(record.status ?? '0'), remark: record.remark || '' })
  dataFormOpen.value = true
}
async function submitData() {
  if (!dataForm.dictLabel || !dataForm.dictValue) { message.warning('请填写必填项'); return }
  saving.value = true
  try {
    if (dataForm.dictCode) await updateDictData(dataForm)
    else await addDictData(dataForm)
    message.success('保存成功')
    dataFormOpen.value = false
    loadData()
  } finally { saving.value = false }
}
function onDataRemove(record) {
  Modal.confirm({ title: '确认删除该字典数据？', onOk: async () => { await removeDictData(String(record.dictCode)); message.success('删除成功'); loadData() } })
}
onMounted(load)
</script>