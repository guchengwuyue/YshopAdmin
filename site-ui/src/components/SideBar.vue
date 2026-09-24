<template>
  <aside class="site-sidebar">
    <section v-if="channels.length" class="mb-4">
      <h6 class="sidebar-title">栏目</h6>
      <ul class="list-unstyled mb-0">
        <li v-for="ch in channels" :key="ch.id" class="mb-1">
          <a :href="ch.url" :class="{ active: ch.isActive || ch.is_active }">{{ ch.name }}</a>
        </li>
      </ul>
    </section>
    <section v-if="latest.length" class="mb-4">
      <h6 class="sidebar-title">最新文章</h6>
      <ul class="list-unstyled mb-0">
        <li v-for="item in latest" :key="item.id" class="mb-2">
          <RouterLink :to="item.url || `/a/${item.diyname || item.id}`" class="small">
            {{ item.title }}
          </RouterLink>
        </li>
      </ul>
    </section>
    <section v-if="tags.length">
      <h6 class="sidebar-title">热门标签</h6>
      <div class="d-flex flex-wrap gap-2">
        <RouterLink
          v-for="tag in tags"
          :key="tag.id"
          :to="tag.url || `/t/${encodeURIComponent(tag.name)}`"
          class="badge text-bg-light text-decoration-none"
        >
          {{ tag.name }}
        </RouterLink>
      </div>
    </section>
  </aside>
</template>

<script setup>
defineProps({
  channels: { type: Array, default: () => [] },
  latest: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
})
</script>
