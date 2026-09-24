<template>
  <section v-if="enabled" class="comment-box">
    <h2 class="comment-box__title">评论</h2>
    <div class="comment-box__divider" />

    <div v-if="loading" class="comment-box__empty text-body-secondary">加载评论…</div>
    <div v-else-if="!list.length" class="comment-box__empty text-body-secondary">暂无评论</div>
    <ul v-else class="comment-box__list">
      <li v-for="item in list" :key="item.id" class="comment-box__item">
        <div class="comment-box__meta">
          <span class="comment-box__user">{{ item.user?.nickname || '游客' }}</span>
          <span class="comment-box__time">{{ item.humanDate || formatTime(item.createTime) }}</span>
        </div>
        <div class="comment-box__content">{{ item.content }}</div>
      </li>
    </ul>

    <div class="comment-box__form">
      <h3 class="comment-box__form-title">发表评论</h3>
      <textarea
        v-model="content"
        class="comment-box__textarea"
        rows="4"
        :disabled="!canPost"
        :placeholder="textareaPlaceholder"
      />
      <div class="comment-box__actions">
        <template v-if="!user.isLogin">
          <RouterLink class="btn btn-primary comment-box__login" :to="loginTo">登录</RouterLink>
          <RouterLink class="comment-box__register" to="/member/register">注册新账号</RouterLink>
        </template>
        <template v-else>
          <BButton variant="primary" :disabled="submitting || !content.trim()" @click="submit">
            {{ submitting ? '提交中…' : '发表评论' }}
          </BButton>
          <span v-if="tip" class="comment-box__tip" :class="{ 'text-danger': tipError }">{{ tip }}</span>
        </template>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { fetchComments, postComment } from '@/api/cms'
import { useSiteStore } from '@/store/site'
import { useUserStore } from '@/store/user'

const props = defineProps({
  type: { type: String, default: 'archives' },
  aid: { type: [Number, String], default: 0 },
  enabled: { type: Boolean, default: true },
})

const route = useRoute()
const site = useSiteStore()
const user = useUserStore()

const list = ref([])
const loading = ref(false)
const content = ref('')
const submitting = ref(false)
const tip = ref('')
const tipError = ref(false)

const needLogin = computed(() => String(site.config?.commentneedlogin ?? '1') === '1')
const canPost = computed(() => user.isLogin || !needLogin.value)
const textareaPlaceholder = computed(() =>
  canPost.value ? '请输入评论内容' : '请登录后再发表评论',
)
const loginTo = computed(() => ({
  path: '/member/login',
  query: { url: route.fullPath },
}))

function formatTime(v) {
  if (!v) return ''
  return String(v).slice(0, 16).replace('T', ' ')
}

async function load() {
  const id = Number(props.aid)
  if (!props.enabled || id <= 0) {
    list.value = []
    return
  }
  loading.value = true
  tip.value = ''
  try {
    const res = await fetchComments({ type: props.type, aid: id, page: 1, pageSize: 50 })
    list.value = res.data?.list || []
  } catch (e) {
    console.warn(e)
    list.value = []
  } finally {
    loading.value = false
  }
}

async function submit() {
  const id = Number(props.aid)
  const text = content.value.trim()
  if (!id || !text || submitting.value) return
  submitting.value = true
  tip.value = ''
  tipError.value = false
  try {
    await postComment({ type: props.type, aid: id, content: text })
    content.value = ''
    tip.value = '评论成功'
    await load()
  } catch (e) {
    tipError.value = true
    tip.value = e.message || '评论失败'
  } finally {
    submitting.value = false
  }
}

watch(() => [props.aid, props.enabled, props.type], load)
onMounted(load)
</script>
