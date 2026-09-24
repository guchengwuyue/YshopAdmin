<template>
  <article
    class="archive-card h-100"
    :class="{
      'archive-card--product': isProduct,
      'archive-card--news': isNews,
    }"
  >
    <RouterLink :to="link" class="archive-card__link text-decoration-none text-body">
      <template v-if="isProduct">
        <div class="archive-card__media">
          <img
            :src="item.image || '/addons/yscms/theme/ystheme/img/noimage.svg'"
            :alt="item.title"
            loading="lazy"
          />
        </div>
        <div class="archive-card__body">
          <span v-if="channelLabel" class="archive-card__tag">{{ channelLabel }}</span>
          <h3 class="archive-card__title">{{ item.title }}</h3>
          <p v-if="item.description" class="archive-card__desc">{{ item.description }}</p>
        </div>
      </template>
      <template v-else-if="isNews">
        <div class="archive-card__media">
          <img
            :src="item.image || '/addons/yscms/theme/ystheme/img/noimage.svg'"
            :alt="item.title"
            loading="lazy"
          />
        </div>
        <div class="archive-card__body">
          <h3 class="archive-card__title">{{ item.title }}</h3>
          <p v-if="item.description" class="archive-card__desc">{{ item.description }}</p>
          <div class="archive-card__foot">
            <span class="archive-card__date">
              <svg class="archive-card__date-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  fill="currentColor"
                  d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Zm12 8H5v10h14V10Zm0-2V6H5v2h14ZM8 13a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm4 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm4 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"
                />
              </svg>
              {{ formatDate(item.publishTime || item.createTime) }}
            </span>
            <span class="archive-card__more">查看更多 &gt;</span>
          </div>
        </div>
      </template>
      <template v-else>
        <div v-if="item.image" class="archive-card__media mb-3">
          <img :src="item.image" :alt="item.title" loading="lazy" />
        </div>
        <h3 class="h6 mb-2">{{ item.title }}</h3>
        <p v-if="item.description" class="small text-body-secondary mb-2 line-clamp-2">
          {{ item.description }}
        </p>
        <div class="small text-body-secondary d-flex gap-3">
          <span v-if="item.publishTime || item.createTime">{{
            formatDate(item.publishTime || item.createTime)
          }}</span>
          <span v-if="item.views != null">阅读 {{ item.views }}</span>
        </div>
      </template>
    </RouterLink>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  item: { type: Object, required: true },
  variant: { type: String, default: 'default' },
})

const isProduct = computed(() => props.variant === 'product')
const isNews = computed(() => props.variant === 'news')

const channelLabel = computed(() => {
  return (
    props.item.channelName ||
    props.item.channel_name ||
    props.item.channel?.name ||
    ''
  )
})

const link = computed(() => {
  const url = props.item.url || ''
  if (url.startsWith('/')) return url
  const key = props.item.diyname || props.item.id
  return key ? `/a/${key}` : '#'
})

function formatDate(v) {
  if (!v) return ''
  return String(v).slice(0, 10)
}
</script>
