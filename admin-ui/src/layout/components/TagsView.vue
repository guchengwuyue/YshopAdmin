<template>
  <div class="tags-view">
    <div
      v-for="tag in tags"
      :key="tag.path"
      class="tag-item"
      :class="{ active: tag.path === activePath }"
      @click="emit('select', tag)"
    >
      <span class="tag-title">{{ tag.title }}</span>
      <span
        v-if="tag.closable"
        class="tag-close"
        @click.stop="emit('close', tag)"
      >
        ×
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  tags: { type: Array, default: () => [] },
  activePath: { type: String, default: '' },
})

const emit = defineEmits(['select', 'close'])
</script>

<style scoped>
.tags-view {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 40px;
  padding: 0 12px;
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
  overflow-x: auto;
  overflow-y: hidden;
  flex-shrink: 0;
}

.tag-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 26px;
  padding: 0 10px;
  border: 1px solid #e8e8e8;
  border-radius: 4px;
  background: #fff;
  color: #595959;
  font-size: 12px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
  user-select: none;
}

.tag-item:hover {
  color: #1677ff;
  border-color: #91caff;
}

.tag-item.active {
  color: #1677ff;
  background: #e6f4ff;
  border-color: #91caff;
}

.tag-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  font-size: 12px;
  line-height: 1;
  color: #8c8c8c;
}

.tag-close:hover {
  background: #1677ff;
  color: #fff;
}
</style>
