<template>
  <a-modal :open="open" :title="title" width="980px" :footer="null" destroy-on-close @cancel="close">
    <div class="material">
      <aside class="side">
        <div class="row" :class="{ active: groupId === 0 }" @click="selectGroup(0)">
          <span class="name">全部图片</span>
          <a-dropdown v-if="canEdit" :trigger="['click']">
            <span class="more" @click.stop><EllipsisOutlined /></span>
            <template #overlay>
              <a-menu @click="openAdd(0)">
                <a-menu-item key="add">新增分类</a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
        <ul class="tree">
          <MaterialGroupNode
            v-for="node in groups"
            :key="node.id"
            :node="node"
            :current-id="groupId"
            :can-edit="canEdit"
            @select="selectGroup"
            @add="openAdd"
            @rename="openRename"
            @remove="onRemoveGroup"
          />
        </ul>
      </aside>
      <section class="main">
        <div class="toolbar">
          <a-button type="primary" :disabled="!selected.length" @click="confirmPick">使用选中图片</a-button>
          <a-upload v-if="canUpload" :show-upload-list="false" accept="image/*" :before-upload="onUpload">
            <a-button type="primary">上传图片</a-button>
          </a-upload>
          <a-button v-if="canRemove" :disabled="!selected.length" @click="onRemove">删除图片</a-button>
          <a-select
            v-if="canEdit"
            v-model:value="moveTo"
            :disabled="!selected.length"
            allow-clear
            placeholder="图片移动至"
            style="width: 160px"
            :options="moveOptions"
            @change="onMove"
          />
          <a-input-search
            v-model:value="keyword"
            class="search"
            placeholder="请输入图片名"
            allow-clear
            @search="search"
          />
        </div>
        <a-spin :spinning="loading">
          <div v-if="rows.length" class="grid">
            <div v-for="item in rows" :key="item.id" class="cell" :class="{ on: !!orderOf(item) }" @click="toggle(item)">
              <div class="pic">
                <img :src="item.url" :alt="item.name" />
                <span v-if="multiple && orderOf(item)" class="badge">{{ orderOf(item) }}</span>
              </div>
              <div class="fname">{{ item.name }}</div>
            </div>
          </div>
          <a-empty v-else />
        </a-spin>
        <div class="pager">
          <a-pagination
            :current="pageNum"
            :page-size="pageSize"
            :total="total"
            :show-total="(t) => `共 ${t} 条`"
            size="small"
            @change="onPage"
          />
        </div>
      </section>
    </div>
    <a-modal
      v-model:open="groupOpen"
      :title="groupEditId ? '重命名' : '新增分类'"
      :confirm-loading="groupSaving"
      @ok="saveGroup"
    >
      <a-input v-model:value="groupName" placeholder="分类名称" />
    </a-modal>
  </a-modal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { EllipsisOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import { addFileGroup, listFile, moveFile, removeFile, removeFileGroup, treeFileGroup, updateFileGroup, uploadFile } from '@/api/tool'
import MaterialGroupNode from './MaterialGroupNode.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  multiple: { type: Boolean, default: false },
  title: { type: String, default: '选择图片' },
  max: { type: Number, default: 0 },
})
const emit = defineEmits(['update:open', 'confirm'])

const userStore = useUserStore()
const canEdit = computed(() => userStore.hasPerm('tool:file:edit'))
const canUpload = computed(() => userStore.hasPerm('tool:file:upload'))
const canRemove = computed(() => userStore.hasPerm('tool:file:remove'))

const pageSize = 18
const groups = ref([])
const groupId = ref(0)
const keyword = ref('')
const pageNum = ref(1)
const total = ref(0)
const rows = ref([])
const loading = ref(false)
const selected = ref([])
const moveTo = ref(undefined)
const groupOpen = ref(false)
const groupSaving = ref(false)
const groupName = ref('')
const groupParentId = ref(0)
const groupEditId = ref(0)

const moveOptions = computed(() => {
  const out = []
  const walk = (nodes, depth) => {
    nodes.forEach((node) => {
      const pad = depth ? `${'\u3000'.repeat(depth)}` : ''
      out.push({ value: node.id, label: pad + node.name })
      if (node.children?.length) walk(node.children, depth + 1)
    })
  }
  walk(groups.value, 0)
  return out
})

watch(() => props.open, (opened) => {
  if (!opened) return
  selected.value = []
  keyword.value = ''
  groupId.value = 0
  pageNum.value = 1
  moveTo.value = undefined
  groupOpen.value = false
  loadGroups()
  load()
}, { immediate: true })

function close() {
  emit('update:open', false)
}

async function loadGroups() {
  const res = await treeFileGroup()
  groups.value = res.data || []
}

async function load() {
  loading.value = true
  try {
    const params = { pageNum: pageNum.value, pageSize, mimetype: 'image/*', name: keyword.value }
    if (groupId.value) params.groupId = groupId.value
    const res = await listFile(params)
    rows.value = res.rows || []
    total.value = res.total || 0
  } finally {
    loading.value = false
  }
}

function selectGroup(id) {
  groupId.value = id
  pageNum.value = 1
  load()
}

function search() {
  pageNum.value = 1
  load()
}

function onPage(page) {
  pageNum.value = page
  load()
}

function orderOf(item) {
  const index = selected.value.findIndex((row) => row.id === item.id)
  return index >= 0 ? index + 1 : 0
}

function toggle(item) {
  const index = selected.value.findIndex((row) => row.id === item.id)
  if (!props.multiple) {
    selected.value = index >= 0 ? [] : [{ id: item.id, name: item.name, url: item.url }]
    return
  }
  if (index >= 0) {
    selected.value.splice(index, 1)
    return
  }
  if (props.max > 0 && selected.value.length >= props.max) {
    message.warning(`最多选择 ${props.max} 张`)
    return
  }
  selected.value.push({ id: item.id, name: item.name, url: item.url })
}

function confirmPick() {
  if (!selected.value.length) return
  emit('confirm', selected.value.map((item) => ({ id: item.id, name: item.name, url: item.url })))
  close()
}

async function onUpload(file) {
  await uploadFile(file, groupId.value || 0)
  message.success('上传成功')
  load()
  return false
}

function onRemove() {
  const ids = selected.value.map((item) => item.id).join(',')
  if (!ids) return
  Modal.confirm({
    title: '确认删除所选图片？',
    onOk: async () => {
      await removeFile(ids)
      message.success('删除成功')
      selected.value = []
      load()
    },
  })
}

async function onMove(target) {
  if (target == null || target === '' || !selected.value.length) return
  try {
    await moveFile(selected.value.map((item) => item.id).join(','), target)
    message.success('移动成功')
    selected.value = []
    load()
  } finally {
    moveTo.value = undefined
  }
}

function openAdd(parentId = 0) {
  groupEditId.value = 0
  groupParentId.value = parentId || 0
  groupName.value = ''
  groupOpen.value = true
}

function openRename(node) {
  groupEditId.value = node.id
  groupParentId.value = node.parentId || 0
  groupName.value = node.name || ''
  groupOpen.value = true
}

async function saveGroup() {
  const name = groupName.value.trim()
  if (!name) {
    message.warning('请输入分类名称')
    return Promise.reject(new Error('empty'))
  }
  groupSaving.value = true
  try {
    if (groupEditId.value) await updateFileGroup({ id: groupEditId.value, name })
    else await addFileGroup({ parentId: groupParentId.value, name })
    message.success('保存成功')
    groupOpen.value = false
    await loadGroups()
  } finally {
    groupSaving.value = false
  }
}

function onRemoveGroup(node) {
  Modal.confirm({
    title: '确认删除该分类？',
    onOk: async () => {
      await removeFileGroup(node.id)
      message.success('删除成功')
      if (groupId.value === node.id) groupId.value = 0
      await loadGroups()
      load()
    },
  })
}
</script>

<style scoped>
.material {
  display: flex;
  min-height: 520px;
}
.side {
  width: 220px;
  flex: none;
  border-right: 1px solid #f0f0f0;
  padding-right: 8px;
  overflow: auto;
}
.main {
  flex: 1;
  min-width: 0;
  padding-left: 16px;
  display: flex;
  flex-direction: column;
}
.row {
  display: flex;
  align-items: center;
  height: 32px;
  padding: 0 8px;
  border-radius: 4px;
  cursor: pointer;
}
.row.active {
  background: #e6f4ff;
  color: #1677ff;
}
.name {
  flex: 1;
  min-width: 0;
}
.more {
  color: rgba(0, 0, 0, 0.45);
}
.tree {
  list-style: none;
  margin: 0;
  padding: 0;
}
.toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}
.search {
  width: 220px;
  margin-left: auto;
}
.grid {
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 16px 12px;
  margin-top: 16px;
  min-height: 360px;
}
.cell {
  min-width: 0;
  cursor: pointer;
}
.pic {
  position: relative;
  aspect-ratio: 1;
  border: 2px solid transparent;
  border-radius: 4px;
  overflow: hidden;
  background: #fafafa;
}
.cell.on .pic {
  border-color: #1677ff;
}
.pic img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.badge {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #1677ff;
  color: #fff;
  font-size: 12px;
  line-height: 20px;
  text-align: center;
}
.fname {
  margin-top: 6px;
  font-size: 12px;
  color: rgba(0, 0, 0, 0.65);
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pager {
  margin-top: auto;
  padding-top: 16px;
  display: flex;
  justify-content: flex-end;
}
</style>
