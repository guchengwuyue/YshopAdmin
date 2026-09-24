<template>
  <div class="page-card">
    <a-alert :message="msg" type="success" show-icon />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { get } from '@/utils/request'

const msg = ref('')

onMounted(async () => {
  try {
    const res = await get('/{$name}/index')
    msg.value = res?.data?.msg || '{$title}'
  } catch (e) {
    msg.value = '{$title}'
  }
})
</script>
