<template>
  <BPagination
    v-if="pages > 1"
    v-model="current"
    :total-rows="total"
    :per-page="pageSize"
    align="center"
    class="mt-4"
    @update:model-value="onChange"
  />
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  page: { type: Number, default: 1 },
  pageSize: { type: Number, default: 10 },
  total: { type: Number, default: 0 },
})
const emit = defineEmits(['change'])

const current = ref(props.page)
watch(
  () => props.page,
  (v) => {
    current.value = v
  }
)
const pages = computed(() => Math.ceil(props.total / Math.max(1, props.pageSize)))

function onChange(p) {
  emit('change', p)
}
</script>
