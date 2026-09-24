<template>
  <BContainer class="member-page py-4 py-lg-5">
    <BRow class="g-4">
      <BCol md="3"><MemberNav /></BCol>
      <BCol md="9">
        <div v-motion="pageEnter" class="member-panel" style="max-width: 560px">
          <div class="member-panel__head">
            <h1 class="member-panel__title">个人资料</h1>
          </div>
          <BAlert v-if="msg" :variant="ok ? 'success' : 'danger'" :model-value="true">{{ msg }}</BAlert>
          <BForm @submit.prevent="onSubmit">
            <BFormGroup label="昵称" class="mb-3">
              <BFormInput v-model="form.nickname" required />
            </BFormGroup>
            <BFormGroup label="邮箱" class="mb-3">
              <BFormInput v-model="form.email" type="email" required />
            </BFormGroup>
            <BFormGroup label="手机号" class="mb-3">
              <BFormInput v-model="form.mobile" required />
            </BFormGroup>
            <BButton type="submit" variant="primary" :disabled="loading">保存</BButton>
          </BForm>
        </div>
      </BCol>
    </BRow>
  </BContainer>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { updateProfile } from '@/api/member'
import { useUserStore } from '@/store/user'
import { useSiteStore } from '@/store/site'
import MemberNav from '@/components/MemberNav.vue'
import { pageEnter } from '@/motion/presets'

const user = useUserStore()
useSiteStore().applySeo({ title: '个人资料' })
const form = reactive({ nickname: '', email: '', mobile: '' })
const loading = ref(false)
const msg = ref('')
const ok = ref(false)

onMounted(async () => {
  await user.fetchInfo()
  form.nickname = user.member?.nickname || ''
  form.email = user.member?.email || ''
  form.mobile = user.member?.mobile || ''
})

async function onSubmit() {
  loading.value = true
  msg.value = ''
  try {
    const res = await updateProfile(form)
    user.member = res.data || user.member
    ok.value = true
    msg.value = res.msg || '保存成功'
  } catch (e) {
    ok.value = false
    msg.value = e.message || '保存失败'
  } finally {
    loading.value = false
  }
}
</script>
