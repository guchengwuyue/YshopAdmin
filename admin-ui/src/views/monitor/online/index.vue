<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="登录地址"><a-input v-model:value="query.ipaddr" allow-clear /></a-form-item>
      <a-form-item label="用户名称"><a-input v-model:value="query.loginName" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('monitor:online:batchForceLogout')" danger style="margin-left: 8px" :disabled="!selected.length" @click="onKick()">强退</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="sessionId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <TableActionBtn v-if="can('monitor:online:batchForceLogout')" tip="强退" tone="danger" @click="onKick(record)">
            <LogoutOutlined />
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
import { LogoutOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { forceLogout, listOnline } from '@/api/monitor'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const rows = ref([])
const selected = ref([])
const query = reactive({ pageNum: 1, pageSize: 10, ipaddr: '', loginName: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const columns = [
  { title: '会话编号', dataIndex: 'sessionId', ellipsis: true },
  { title: '登录名称', dataIndex: 'loginName', width: 120 },
  { title: '部门', dataIndex: 'deptName', width: 120 },
  { title: '主机', dataIndex: 'ipaddr', width: 140 },
  { title: '登录地点', dataIndex: 'loginLocation' },
  { title: '浏览器', dataIndex: 'browser' },
  { title: '操作系统', dataIndex: 'os' },
  { title: '操作', key: 'action', width: 100 },
]
async function load() {
  loading.value = true
  try {
    const res = await listOnline(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function onKick(record) {
  const ids = record ? String(record.sessionId) : selected.value.join(',')
  Modal.confirm({ title: '确认强退所选会话？', onOk: async () => { await forceLogout(ids); message.success('已强退'); selected.value = []; load() } })
}
onMounted(load)
</script>