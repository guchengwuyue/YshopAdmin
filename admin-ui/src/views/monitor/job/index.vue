<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="任务名称"><a-input v-model:value="query.jobName" allow-clear /></a-form-item>
      <a-form-item label="任务组名"><a-input v-model:value="query.jobGroup" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('monitor:job:add')" style="margin-left: 8px" @click="openAdd">新增</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="jobId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">{{ String(record.status) === '0' ? '正常' : '暂停' }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('monitor:job:changeStatus')" tip="执行" @click="onRun(record)">
              <PlayCircleOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('monitor:job:detail')" tip="日志" @click="openLog(record)">
              <FileTextOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('monitor:job:edit')" tip="编辑" tone="edit" @click="openEdit(record)">
              <EditOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('monitor:job:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="open" :title="form.jobId ? '编辑任务' : '新增任务'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
        <a-form-item label="任务名称" required><a-input v-model:value="form.jobName" /></a-form-item>
        <a-form-item label="任务分组"><a-input v-model:value="form.jobGroup" /></a-form-item>
        <a-form-item label="调用目标" required><a-input v-model:value="form.invokeTarget" /></a-form-item>
        <a-form-item label="cron" required><a-input v-model:value="form.cronExpression" /></a-form-item>
        <a-form-item label="状态">
          <a-radio-group v-model:value="form.status" :options="[{ label: '正常', value: '0' }, { label: '暂停', value: '1' }]" />
        </a-form-item>
        <a-form-item label="备注"><a-textarea v-model:value="form.remark" :rows="2" /></a-form-item>
      </a-form>
    </a-modal>
    <a-drawer v-model:open="logOpen" title="调度日志" width="720">
      <a-button danger style="margin-bottom: 12px" @click="onCleanLog">清空</a-button>
      <a-table row-key="jobLogId" size="small" :columns="logColumns" :data-source="logs" :pagination="false" />
    </a-drawer>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, EditOutlined, FileTextOutlined, PlayCircleOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addJob, cleanJobLog, getJob, listJob, listJobLog, removeJob, runJob, updateJob } from '@/api/monitor'
import { num } from '@/utils/tree'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const open = ref(false)
const query = reactive({ pageNum: 1, pageSize: 10, jobName: '', jobGroup: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const form = reactive(empty())
const logOpen = ref(false)
const logs = ref([])
const logJobId = ref(0)
const columns = [
  { title: '任务编号', dataIndex: 'jobId', width: 90 },
  { title: '任务名称', dataIndex: 'jobName' },
  { title: '任务组名', dataIndex: 'jobGroup', width: 120 },
  { title: '调用目标', dataIndex: 'invokeTarget', ellipsis: true },
  { title: 'cron', dataIndex: 'cronExpression', width: 140 },
  { title: '状态', dataIndex: 'status', width: 80 },
  { title: '操作', key: 'action', width: 220 },
]
const logColumns = [
  { title: '日志编号', dataIndex: 'jobLogId', width: 90 },
  { title: '任务名称', dataIndex: 'jobName' },
  { title: '状态', dataIndex: 'status', width: 70 },
  { title: '信息', dataIndex: 'jobMessage', ellipsis: true },
  { title: '时间', dataIndex: 'createTime', width: 170 },
]
function empty() { return { jobId: undefined, jobName: '', jobGroup: 'DEFAULT', invokeTarget: '', cronExpression: '', status: '1', remark: '' } }
async function load() {
  loading.value = true
  try {
    const res = await listJob(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function openAdd() { Object.assign(form, empty()); open.value = true }
async function openEdit(record) {
  const res = await getJob(record.jobId)
  const row = res.data || {}
  Object.assign(form, empty(), { jobId: num(row.jobId), jobName: row.jobName, jobGroup: row.jobGroup, invokeTarget: row.invokeTarget, cronExpression: row.cronExpression, status: String(row.status ?? '1'), remark: row.remark || '' })
  open.value = true
}
async function submit() {
  if (!form.jobName || !form.invokeTarget || !form.cronExpression) { message.warning('请填写必填项'); return }
  saving.value = true
  try {
    if (form.jobId) await updateJob(form)
    else await addJob(form)
    message.success('保存成功'); open.value = false; load()
  } finally { saving.value = false }
}
function onRemove(record) {
  Modal.confirm({ title: '确认删除该任务？', onOk: async () => { await removeJob(String(record.jobId)); message.success('删除成功'); load() } })
}
function onRun(record) {
  Modal.confirm({ title: '立即执行一次？', onOk: async () => { await runJob(record.jobId); message.success('已执行') } })
}
async function openLog(record) {
  logJobId.value = record.jobId
  logOpen.value = true
  const res = await listJobLog({ jobId: record.jobId, pageNum: 1, pageSize: 50 })
  logs.value = res.rows || []
}
function onCleanLog() {
  Modal.confirm({ title: '清空调度日志？', onOk: async () => { await cleanJobLog(); logs.value = []; message.success('已清空') } })
}
onMounted(load)
</script>
