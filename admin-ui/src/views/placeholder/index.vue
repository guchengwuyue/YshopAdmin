<template>
  <a-result status="info" title="开发中" :sub-title="text" />
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const text = computed(() => (route.meta?.title ? route.meta.title + ' 开发中' : '该菜单还没有对应的管理页'))
</script>
