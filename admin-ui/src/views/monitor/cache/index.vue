<template>
  <div class="page-card">
    <div class="toolbar">
      <a-button danger @click="onClearAll">清理全部缓存</a-button>
    </div>
    <a-row :gutter="12">
      <a-col :span="8">
        <a-card title="缓存列表" size="small">
          <a-list :data-source="names" size="small">
            <template #renderItem="{ item }">
              <a-list-item>
                <a @click="loadKeys(item)">{{ item }}</a>
                <template #actions><a @click="clearName(item)">清空</a></template>
              </a-list-item>
            </template>
          </a-list>
        </a-card>
      </a-col>
      <a-col :span="8">
        <a-card :title="'键名' + (currentName ? ' · ' + currentName : '')" size="small">
          <a-list :data-source="keys" size="small">
            <template #renderItem="{ item }">
              <a-list-item>
                <a @click="loadValue(item)">{{ item }}</a>
                <template #actions><a @click="clearKey(item)">删除</a></template>
              </a-list-item>
            </template>
          </a-list>
        </a-card>
      </a-col>
      <a-col :span="8">
        <a-card title="缓存内容" size="small">
          <p>缓存名称：{{ value.cacheName }}</p>
          <p>缓存键名：{{ value.cacheKey }}</p>
          <pre class="pre">{{ value.cacheValue }}</pre>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { cacheKeys, cacheNames, cacheValue, clearAllCache, clearCacheKey, clearCacheName } from '@/api/monitor'
const names = ref([])
const keys = ref([])
const currentName = ref('')
const value = reactive({ cacheName: '', cacheKey: '', cacheValue: '' })
async function loadNames() {
  const res = await cacheNames()
  names.value = res.data || []
}
async function loadKeys(name) {
  currentName.value = name
  const res = await cacheKeys(name)
  keys.value = res.data || []
}
async function loadValue(key) {
  const res = await cacheValue(currentName.value, key)
  Object.assign(value, res.data || {})
}
function clearName(name) {
  Modal.confirm({ title: '清空该缓存？', onOk: async () => { await clearCacheName(name); message.success('已清空'); if (currentName.value === name) keys.value = []; loadNames() } })
}
function clearKey(key) {
  Modal.confirm({ title: '删除该键？', onOk: async () => { await clearCacheKey(currentName.value, key); message.success('已删除'); loadKeys(currentName.value) } })
}
function onClearAll() {
  Modal.confirm({ title: '清理全部缓存？', onOk: async () => { await clearAllCache(); message.success('已清理'); keys.value = []; Object.assign(value, { cacheName: '', cacheKey: '', cacheValue: '' }); loadNames() } })
}
onMounted(loadNames)
</script>
<style scoped>.pre { white-space: pre-wrap; word-break: break-all; max-height: 360px; overflow: auto; }</style>
