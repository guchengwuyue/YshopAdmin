<template>
  <li
    class="channel-cate__item"
    :class="{
      'is-active': selfActive,
      'is-open': open,
      'has-children': hasChildren,
    }"
  >
    <div class="channel-cate__row">
      <RouterLink :to="path" class="channel-cate__link" :class="{ active: selfActive }">
        {{ node.name }}
      </RouterLink>
      <button
        v-if="hasChildren"
        type="button"
        class="channel-cate__toggle"
        aria-label="展开"
        @click.prevent="open = !open"
      >
        <i class="channel-cate__chevron" />
      </button>
    </div>
    <ul v-if="hasChildren && open" class="channel-cate__tree channel-cate__tree--sub">
      <ChannelCateNode
        v-for="child in node.children"
        :key="child.id"
        :node="child"
        :active-id="activeId"
        :depth="depth + 1"
      />
    </ul>
  </li>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

defineOptions({ name: 'ChannelCateNode' })

const props = defineProps({
  node: { type: Object, required: true },
  activeId: { type: [Number, String], default: 0 },
  depth: { type: Number, default: 0 },
})

const open = ref(false)

const hasChildren = computed(() => (props.node.children || []).length > 0)

const selfActive = computed(
  () => Number(props.node.id) === Number(props.activeId) || !!(props.node.isActive || props.node.is_active),
)

function hasActive(n) {
  if (!n) return false
  if (Number(n.id) === Number(props.activeId) || n.isActive || n.is_active) return true
  return (n.children || []).some((c) => hasActive(c))
}

watch(
  () => [props.node, props.activeId],
  () => {
    open.value = hasActive(props.node)
  },
  { immediate: true, deep: true },
)

const path = computed(() => {
  const url = props.node?.url || ''
  if (typeof url === 'string' && url.startsWith('/')) return url
  const key = props.node?.diyname || props.node?.id
  return key ? `/c/${key}` : '#'
})
</script>
