<template>
  <div class="picker">
    <template v-if="multiple">
      <div v-for="(url, index) in urls" :key="url + index" class="thumb" @click="open = true">
        <img :src="url" alt="" />
        <span class="clear" @click.stop="removeAt(index)">×</span>
      </div>
      <div class="thumb empty" @click="open = true">
        <PictureOutlined />
      </div>
    </template>
    <div v-else class="thumb" @click="open = true">
      <img v-if="singleUrl" :src="singleUrl" alt="" />
      <PictureOutlined v-else />
      <span v-if="singleUrl" class="clear" @click.stop="clear">×</span>
    </div>
    <MaterialLibrary v-model:open="open" :multiple="multiple" :title="title" :max="max" @confirm="onConfirm" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { PictureOutlined } from '@ant-design/icons-vue'
import MaterialLibrary from './MaterialLibrary.vue'

const props = defineProps({
  value: { type: [String, Array], default: '' },
  multiple: { type: Boolean, default: false },
  title: { type: String, default: '选择图片' },
  max: { type: Number, default: 0 },
})
const emit = defineEmits(['update:value'])
const open = ref(false)

const singleUrl = computed(() => (props.multiple ? '' : String(props.value || '')))
const urls = computed(() => (props.multiple && Array.isArray(props.value) ? props.value.filter(Boolean) : []))

function onConfirm(items) {
  if (props.multiple) emit('update:value', items.map((item) => item.url))
  else emit('update:value', items[0]?.url || '')
}

function clear() {
  emit('update:value', '')
}

function removeAt(index) {
  const next = urls.value.slice()
  next.splice(index, 1)
  emit('update:value', next)
}
</script>

<style scoped>
.picker {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.thumb {
  position: relative;
  width: 104px;
  height: 104px;
  border: 1px dashed #d9d9d9;
  border-radius: 8px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  color: rgba(0, 0, 0, 0.45);
  font-size: 28px;
}
.thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.clear {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.45);
  color: #fff;
  font-size: 14px;
  line-height: 16px;
  text-align: center;
}
</style>
