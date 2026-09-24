<template>
  <div class="icon-select">
    <a-input
      :value="value"
      readonly
      placeholder="点击选择图标"
      allow-clear
      @click="open = true"
      @update:value="onInput"
    >
      <template #prefix>
        <span class="preview">
          <MenuIcon v-if="value" :icon="value" />
          <AppstoreOutlined v-else class="placeholder" />
        </span>
      </template>
    </a-input>

    <a-modal
      v-model:open="open"
      title="选择图标"
      :footer="null"
      width="720px"
      destroy-on-close
      @after-close="keyword = ''"
    >
      <a-input-search
        v-model:value="keyword"
        placeholder="搜索图标"
        allow-clear
        style="margin-bottom: 12px"
      />
      <a-tabs v-model:activeKey="activeKey" size="small">
        <a-tab-pane v-for="cat in iconCategories" :key="cat.key" :tab="cat.label">
          <div class="icon-grid">
            <button
              v-for="name in filteredIcons(cat.icons)"
              :key="name"
              type="button"
              class="icon-cell"
              :class="{ active: name === value }"
              :title="name"
              @click="pick(name)"
            >
              <component :is="iconComponents[name]" />
            </button>
          </div>
          <a-empty v-if="!filteredIcons(cat.icons).length" description="无匹配图标" />
        </a-tab-pane>
      </a-tabs>
    </a-modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { AppstoreOutlined } from '@ant-design/icons-vue'
import MenuIcon from '@/components/MenuIcon.vue'
import { iconCategories, iconComponents } from './icons.js'

defineProps({
  value: { type: String, default: '' },
})
const emit = defineEmits(['update:value'])

const open = ref(false)
const keyword = ref('')
const activeKey = ref(iconCategories[0]?.key || 'custom')

function filteredIcons(icons) {
  const q = keyword.value.trim().toLowerCase()
  if (!q) return icons
  return icons.filter((name) => name.toLowerCase().includes(q))
}

function pick(name) {
  emit('update:value', name)
  open.value = false
}

function onInput(next) {
  if (!next) emit('update:value', '')
}
</script>

<style scoped>
.icon-select {
  width: 100%;
}
.icon-select :deep(.ant-input) {
  cursor: pointer;
}
.preview {
  display: inline-flex;
  align-items: center;
  font-size: 16px;
  color: rgba(0, 0, 0, 0.65);
}
.placeholder {
  color: rgba(0, 0, 0, 0.25);
}
.icon-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 8px;
  max-height: 360px;
  overflow: auto;
  padding: 4px 0 8px;
}
.icon-cell {
  height: 48px;
  border: 1px solid #f0f0f0;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: rgba(0, 0, 0, 0.75);
  transition: border-color 0.15s, color 0.15s, background 0.15s;
}
.icon-cell:hover {
  border-color: #1677ff;
  color: #1677ff;
}
.icon-cell.active {
  border-color: #1677ff;
  color: #1677ff;
  background: #e6f4ff;
}
</style>
