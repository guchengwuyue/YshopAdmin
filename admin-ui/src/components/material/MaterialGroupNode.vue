<template>
  <li>
    <div class="row" :class="{ active: currentId === node.id }" @click="emit('select', node.id)">
      <span class="caret" @click.stop="expanded = !expanded">
        <CaretDownOutlined v-if="hasChildren && expanded" />
        <CaretRightOutlined v-else-if="hasChildren" />
      </span>
      <span class="name">{{ node.name }}</span>
      <a-dropdown v-if="canEdit" :trigger="['click']">
        <span class="more" @click.stop><EllipsisOutlined /></span>
        <template #overlay>
          <a-menu @click="onMenu">
            <a-menu-item key="add">新增子分类</a-menu-item>
            <a-menu-item key="rename">重命名</a-menu-item>
            <a-menu-item key="remove">删除</a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
    <ul v-if="hasChildren && expanded">
      <MaterialGroupNode
        v-for="child in node.children"
        :key="child.id"
        :node="child"
        :current-id="currentId"
        :can-edit="canEdit"
        @select="emit('select', $event)"
        @add="emit('add', $event)"
        @rename="emit('rename', $event)"
        @remove="emit('remove', $event)"
      />
    </ul>
  </li>
</template>

<script>
export default { name: 'MaterialGroupNode' }
</script>

<script setup>
import { computed, ref } from 'vue'
import { CaretDownOutlined, CaretRightOutlined, EllipsisOutlined } from '@ant-design/icons-vue'

const props = defineProps({
  node: { type: Object, required: true },
  currentId: { type: Number, default: 0 },
  canEdit: { type: Boolean, default: false },
})
const emit = defineEmits(['select', 'add', 'rename', 'remove'])
const expanded = ref(true)
const hasChildren = computed(() => Array.isArray(props.node.children) && props.node.children.length > 0)

function onMenu({ key }) {
  if (key === 'add') emit('add', props.node.id)
  else if (key === 'rename') emit('rename', props.node)
  else if (key === 'remove') emit('remove', props.node)
}
</script>

<style scoped>
.row {
  display: flex;
  align-items: center;
  gap: 4px;
  height: 32px;
  padding: 0 8px;
  border-radius: 4px;
  cursor: pointer;
}
.row.active {
  background: #e6f4ff;
  color: #1677ff;
}
.caret {
  width: 14px;
  flex: none;
  color: rgba(0, 0, 0, 0.45);
}
.name {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.more {
  color: rgba(0, 0, 0, 0.45);
  padding: 0 4px;
}
ul {
  list-style: none;
  margin: 0;
  padding: 0 0 0 16px;
}
li {
  list-style: none;
}
</style>
