<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="登录地址"><a-input v-model:value="query.ipaddr" allow-clear /></a-form-item>
      <a-form-item label="用户名称"><a-input v-model:value="query.loginName" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button v-if="can('monitor:logininfor:remove')" danger style="margin-left: 8px" @click="onClean">清空</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="infoId" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">{{ String(record.status) === '0' ? '成功' : '失败' }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('monitor:logininfor:unlock')" tip="解锁" @click="onUnlock(record)">
              <UnlockOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('monitor:logininfor:remove')" tip="删除" tone="danger" @click="onRemove(record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { DeleteOutlined, UnlockOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { cleanLogininfor, listLogininfor, removeLogininfor, unlockLogininfor } from '@/api/monitor'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const rows = ref([])
const query = reactive({ pageNum: 1, pageSize: 10, ipaddr: '', loginName: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const columns = [
  { title: '访问编号', dataIndex: 'infoId', width: 90 },
  { title: '用户名称', dataIndex: 'loginName' },
  { title: '登录地址', dataIndex: 'ipaddr' },
  { title: '登录地点', dataIndex: 'loginLocation' },
  { title: '浏览器', dataIndex: 'browser' },
  { title: '状态', dataIndex: 'status', width: 80 },
  { title: '登录时间', dataIndex: 'loginTime', width: 170 },
  { title: '操作', key: 'action', width: 140 },
]
async function load() {
  loading.value = true
  try {
    const res = await listLogininfor(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function onRemove(record) {
  Modal.confirm({ title: '确认删除？', onOk: async () => { await removeLogininfor(String(record.infoId)); message.success('删除成功'); load() } })
}
function onUnlock(record) {
  Modal.confirm({ title: `解锁 ${record.loginName}？`, onOk: async () => { await unlockLogininfor(record.loginName); message.success('已解锁') } })
}
function onClean() {
  Modal.confirm({ title: '确认清空登录日志？', onOk: async () => { await cleanLogininfor(); message.success('已清空'); load() } })
}
onMounted(load)
</script>