<template>
  <div class="page-card">
    <a-spin :spinning="loading">
      <a-row :gutter="16">
        <a-col :span="12">
          <a-card title="CPU" size="small">
            <p>核心数：{{ server.cpu?.cpuNum ?? '-' }}</p>
            <p>用户使用率：{{ server.cpu?.used ?? '-' }}%</p>
            <p>系统使用率：{{ server.cpu?.sys ?? '-' }}%</p>
            <p>当前空闲率：{{ server.cpu?.free ?? '-' }}%</p>
          </a-card>
        </a-col>
        <a-col :span="12">
          <a-card title="内存" size="small">
            <p>总内存：{{ server.mem?.total ?? '-' }}</p>
            <p>已用内存：{{ server.mem?.used ?? '-' }}</p>
            <p>剩余内存：{{ server.mem?.free ?? '-' }}</p>
            <p>使用率：{{ server.mem?.usage ?? '-' }}%</p>
          </a-card>
        </a-col>
      </a-row>
      <a-card title="服务器信息" size="small" style="margin-top: 16px">
        <p>服务器名称：{{ server.sys?.computerName }}</p>
        <p>操作系统：{{ server.sys?.osName }}</p>
        <p>服务器 IP：{{ server.sys?.computerIp }}</p>
        <p>系统架构：{{ server.sys?.osArch }}</p>
      </a-card>
      <a-card title="PHP" size="small" style="margin-top: 16px">
        <p>名称：{{ server.jvm?.name }}</p>
        <p>版本：{{ server.jvm?.version }}</p>
        <p>启动时间：{{ server.jvm?.startTime }}</p>
        <p>运行时长：{{ server.jvm?.runTime }}</p>
        <p>安装路径：{{ server.jvm?.home }}</p>
      </a-card>
      <a-table style="margin-top: 16px" row-key="dirName" :columns="columns" :data-source="server.sysFiles || []" :pagination="false" size="small" />
    </a-spin>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { getServer } from '@/api/monitor'
const loading = ref(false)
const server = reactive({})
const columns = [
  { title: '盘符路径', dataIndex: 'dirName' },
  { title: '文件系统', dataIndex: 'sysTypeName' },
  { title: '盘符类型', dataIndex: 'typeName' },
  { title: '总大小', dataIndex: 'total' },
  { title: '可用大小', dataIndex: 'free' },
  { title: '已用大小', dataIndex: 'used' },
  { title: '已用百分比', dataIndex: 'usage' },
]
onMounted(async () => {
  loading.value = true
  try {
    const res = await getServer()
    Object.assign(server, res.data || {})
  } finally { loading.value = false }
})
</script>
