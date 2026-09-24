<template>
  <div class="app-main">
    <router-view :key="viewKey" />
  </div>
</template>

<script setup>
defineProps({
  viewKey: { type: [String, Number], default: 0 },
})
</script>

<style scoped>
.app-main {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px;
  background: #f0f2f5;
}
</style>
