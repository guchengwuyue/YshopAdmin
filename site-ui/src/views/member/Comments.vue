<template>
  <BContainer class="member-page py-4 py-lg-5">
    <BRow class="g-4">
      <BCol md="3"><MemberNav /></BCol>
      <BCol md="9">
        <div v-motion="pageEnter" class="member-panel">
          <div class="member-panel__head">
            <h1 class="member-panel__title">我发表的评论</h1>
          </div>
          <div class="member-empty text-body-secondary">暂无评论</div>
        </div>
      </BCol>
    </BRow>
  </BContainer>
</template>

<script setup>
import { useSiteStore } from '@/store/site'
import MemberNav from '@/components/MemberNav.vue'
import { pageEnter } from '@/motion/presets'

useSiteStore().applySeo({ title: '我发表的评论' })
</script>
