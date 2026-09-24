<template>
  <BContainer class="member-page py-4 py-lg-5">
    <BRow class="g-4">
      <BCol md="3"><MemberNav /></BCol>
      <BCol md="9">
        <div v-motion="pageEnter" class="member-panel" style="max-width: 560px">
          <div class="member-panel__head">
            <h1 class="member-panel__title">修改密码</h1>
          </div>
          <BAlert v-if="msg" :variant="ok ? 'success' : 'danger'" :model-value="true">{{ msg }}</BAlert>
          <BForm @submit.prevent="onSubmit">
            <BFormGroup label="旧密码" class="mb-3">
              <BFormInput v-model="form.oldpassword" type="password" required />
            </BFormGroup>
            <BFormGroup label="新密码" class="mb-3">
              <BFormInput v-model="form.newpassword" type="password" required />
            </BFormGroup>
            <BFormGroup label="确认新密码" class="mb-3">
              <BFormInput v-model="form.renewpassword" type="password" required />
            </BFormGroup>
            <BButton type="submit" variant="primary" :disabled="loading">提交</BButton>
          </BForm>
        </div>
      </BCol>
    </BRow>
  </BContainer>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { changePwd } from '@/api/member'
import { useSiteStore } from '@/store/site'
import MemberNav from '@/components/MemberNav.vue'
import { pageEnter } from '@/motion/presets'

useSiteStore().applySeo({ title: '修改密码' })
const form = reactive({ oldpassword: '', newpassword: '', renewpassword: '' })
const loading = ref(false)
const msg = ref('')
const ok = ref(false)

async function onSubmit() {
  loading.value = true
  msg.value = ''
  try {
    const res = await changePwd(form)
    ok.value = true
    msg.value = res.msg || '修改成功'
    form.oldpassword = ''
    form.newpassword = ''
    form.renewpassword = ''
  } catch (e) {
    ok.value = false
    msg.value = e.message || '修改失败'
  } finally {
    loading.value = false
  }
}
</script>
