<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="系统模块"><a-input v-model:value="query.title" allow-clear /></a-form-item>
      <a-form-item label="操作人员"><a-input v-model:value="query.operName" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('monitor:operlog:remove')" danger style="margin-left: 8px" @click="onClean">清空</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="operId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">{{ String(record.status) === '0' ? '成功' : '失败' }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('monitor:operlog:detail')" tip="详情" @click="openDetail(record)">
              <ProfileOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('monitor:operlog:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="open" title="操作日志详情" :footer="null" width="720">
      <pre class="pre">{{ detailText }}</pre>
    </a-modal>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, ProfileOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { cleanOperlog, getOperlog, listOperlog, removeOperlog } from '@/api/monitor'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const rows = ref([])
const open = ref(false)
const detailText = ref('')
const query = reactive({ pageNum: 1, pageSize: 10, title: '', operName: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const columns = [
  { title: '日志编号', dataIndex: 'operId', width: 90 },
  { title: '系统模块', dataIndex: 'title' },
  { title: '操作人员', dataIndex: 'operName', width: 120 },
  { title: '操作地址', dataIndex: 'operIp', width: 140 },
  { title: '状态', dataIndex: 'status', width: 80 },
  { title: '操作时间', dataIndex: 'operTime', width: 170 },
  { title: '操作', key: 'action', width: 140 },
]
async function load() {
  loading.value = true
  try {
    const res = await listOperlog(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
async function openDetail(record) {
  const res = await getOperlog(record.operId)
  detailText.value = JSON.stringify(res.data || {}, null, 2)
  open.value = true
}
function onRemove(record) {
  Modal.confirm({ title: '确认删除该日志？', onOk: async () => { await removeOperlog(String(record.operId)); message.success('删除成功'); load() } })
}
function onClean() {
  Modal.confirm({ title: '确认清空操作日志？', onOk: async () => { await cleanOperlog(); message.success('已清空'); load() } })
}
onMounted(load)
</script>
<style scoped>
.pre { max-height: 420px; overflow: auto; background: #f7f7f7; padding: 12px; }
</style>
