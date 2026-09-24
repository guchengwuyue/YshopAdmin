<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" class="search">
      <a-form-item label="标识"><a-input v-model:value="query.name" allow-clear /></a-form-item>
      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-upload v-if="can('system:addon:install')" :show-upload-list="false" :before-upload="onUpload" accept=".zip" style="margin-left: 8px">
          <a-button>上传安装</a-button>
        </a-upload>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <a-table row-key="name" :columns="columns" :data-source="rows" :loading="loading" :pagination="pagination" @change="onTableChange">
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'status'">{{ statusText(record.status) }}</template>
        <template v-else-if="column.key === 'action'">
          <a-space :size="6">
            <TableActionBtn v-if="can('system:addon:install') && !record.installed" tip="安装" @click="act('install', record)">
              <DownloadOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:addon:edit') && record.status === '0'" tip="启用" @click="act('enable', record)">
              <CheckCircleOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:addon:edit') && record.status === '1'" tip="禁用" tone="danger" @click="act('disable', record)">
              <StopOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:addon:config') && record.hasConfig" tip="配置" @click="openConfig(record)">
              <SettingOutlined />
            </TableActionBtn>
            <TableActionBtn v-if="can('system:addon:uninstall') && record.installed" tip="卸载" tone="danger" @click="act('uninstall', record)">
              <DeleteOutlined />
            </TableActionBtn>
          </a-space>
        </template>
      </template>
    </a-table>
    <a-modal v-model:open="cfgOpen" :title="'插件配置 · ' + cfgName" :confirm-loading="saving" width="640" @ok="saveCfg">
      <a-form :label-col="{ span: 7 }" :wrapper-col="{ span: 16 }">
        <a-form-item v-for="item in schema" :key="item.name" :label="item.title || item.name">
          <a-textarea v-if="item.type === 'array' || item.type === 'text'" v-model:value="item.valueText" :rows="4" />
          <a-input v-else v-model:value="item.valueText" />
        </a-form-item>
      </a-form>
    </a-modal>
    </div>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { CheckCircleOutlined, DeleteOutlined, DownloadOutlined, SettingOutlined, StopOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { disableAddon, enableAddon, getAddonConfig, installAddon, listAddon, saveAddonConfig, uninstallAddon, uploadAddon } from '@/api/system/addon'
import TableActionBtn from '@/components/TableActionBtn.vue'
const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const query = reactive({ pageNum: 1, pageSize: 10, name: '' })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (t) => `共 ${t} 条` })
const cfgOpen = ref(false)
const cfgName = ref('')
const schema = ref([])
const columns = [
  { title: '标识', dataIndex: 'name', width: 140 },
  { title: '名称', dataIndex: 'title' },
  { title: '版本', dataIndex: 'version', width: 90 },
  { title: '作者', dataIndex: 'author', width: 120 },
  { title: '状态', dataIndex: 'status', width: 90 },
  { title: '操作', key: 'action', width: 280 },
]
function statusText(status) {
  if (status === '1') return '已启用'
  if (status === '0') return '已禁用'
  return '未安装'
}
async function load() {
  loading.value = true
  try {
    const res = await listAddon(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
  } finally { loading.value = false }
}
function search() { query.pageNum = 1; load() }
function onTableChange(page) { query.pageNum = page.current; query.pageSize = page.pageSize; load() }
function act(type, record) {
  const map = { install: installAddon, uninstall: uninstallAddon, enable: enableAddon, disable: disableAddon }
  Modal.confirm({
    title: `确认${type === 'install' ? '安装' : type === 'uninstall' ? '卸载' : type === 'enable' ? '启用' : '禁用'} ${record.name}？`,
    onOk: async () => { await map[type](record.name); message.success('操作成功'); load() },
  })
}
async function openConfig(record) {
  const res = await getAddonConfig(record.name)
  cfgName.value = record.name
  schema.value = (res.data?.schema || []).map((item) => ({ ...item, valueText: item.valueText ?? '' }))
  cfgOpen.value = true
}
async function saveCfg() {
  const config = {}
  schema.value.forEach((item) => { config[item.name] = item.valueText })
  saving.value = true
  try {
    await saveAddonConfig({ name: cfgName.value, config })
    message.success('保存成功')
    cfgOpen.value = false
  } finally { saving.value = false }
}
async function onUpload(file) {
  await uploadAddon(file)
  message.success('上传完成')
  load()
  return false
}
onMounted(load)
</script>
