<template>
  <a-tooltip v-if="iconOnly" :title="tip">
    <a-button
      type="primary"
      size="small"
      :danger="tone === 'danger'"
      :class="{ 'table-action-btn--edit': tone === 'edit' }"
      v-bind="$attrs"
      @click="$emit('click', $event)"
    >
      <template #icon>
        <slot />
      </template>
    </a-button>
  </a-tooltip>
  <a-button
    v-else
    type="primary"
    size="small"
    :danger="tone === 'danger'"
    :class="{ 'table-action-btn--edit': tone === 'edit' }"
    v-bind="$attrs"
    @click="$emit('click', $event)"
  >
    <template #icon>
      <slot />
    </template>
    {{ tip }}
  </a-button>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  /** @type {'edit' | 'danger' | 'primary'} */
  tone: {
    type: String,
    default: 'primary',
    validator: (v) => ['edit', 'danger', 'primary'].includes(v),
  },
  tip: {
    type: String,
    required: true,
  },
})

defineEmits(['click'])

// edit / delete: icon only; others: icon + tip text
const ICON_ONLY_TIPS = new Set(['\u7f16\u8f91', '\u5220\u9664'])
const iconOnly = computed(() => props.tone === 'edit' || ICON_ONLY_TIPS.has(props.tip))
</script>

<style scoped>
.table-action-btn--edit {
  background: #13c2c2;
  border-color: #13c2c2;
}
.table-action-btn--edit:hover,
.table-action-btn--edit:focus {
  background: #36cfc9 !important;
  border-color: #36cfc9 !important;
}
</style>
