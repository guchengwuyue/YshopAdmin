<template>
  <div class="rich-editor" :class="{ disabled }">
    <Toolbar
      :editor="editorRef"
      :default-config="toolbarConfig"
      :mode="mode"
      class="rich-toolbar"
    />
    <Editor
      v-model="html"
      :default-config="editorConfig"
      :mode="mode"
      :style="{ height }"
      class="rich-body"
      @onCreated="onCreated"
      @onChange="onChange"
    />
    <MaterialLibrary
      v-model:open="materialOpen"
      multiple
      title="插入图片"
      @confirm="onMaterialConfirm"
    />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, shallowRef, ref, watch } from 'vue'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
import '@wangeditor/editor/dist/css/style.css'
import MaterialLibrary from '@/components/material/MaterialLibrary.vue'

const props = defineProps({
  value: { type: String, default: '' },
  placeholder: { type: String, default: '请输入正文内容' },
  height: { type: String, default: '360px' },
  mode: { type: String, default: 'default' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:value'])

const editorRef = shallowRef()
const materialOpen = ref(false)
/** @type {((url: string, alt: string, href: string) => void) | null} */
let pendingInsertFn = null

const html = computed({
  get: () => props.value || '',
  set: (val) => emit('update:value', val || ''),
})

const toolbarConfig = {
  excludeKeys: ['insertImage', 'group-video', 'uploadVideo', 'insertVideo'],
}

const editorConfig = {
  placeholder: props.placeholder,
  readOnly: props.disabled,
  MENU_CONF: {
    uploadImage: {
      customBrowseAndUpload(insertFn) {
        pendingInsertFn = insertFn
        materialOpen.value = true
      },
    },
  },
}

watch(materialOpen, (open) => {
  if (!open) pendingInsertFn = null
})

function onCreated(editor) {
  editorRef.value = editor
  if (props.disabled) editor.disable()
}

function onChange(editor) {
  emit('update:value', editor.getHtml() || '')
}

function onMaterialConfirm(items) {
  const insertFn = pendingInsertFn
  pendingInsertFn = null
  if (!insertFn) {
    const editor = editorRef.value
    if (!editor) return
    ;(items || []).forEach((item) => {
      const src = item?.url
      if (!src) return
      editor.insertNode({
        type: 'image',
        src,
        alt: item?.name || '',
        href: '',
        children: [{ text: '' }],
      })
    })
    return
  }
  ;(items || []).forEach((item) => {
    const src = item?.url
    if (src) insertFn(src, item?.name || '', '')
  })
}

onBeforeUnmount(() => {
  const editor = editorRef.value
  if (editor == null) return
  editor.destroy()
  editorRef.value = undefined
  pendingInsertFn = null
})
</script>

<style scoped>
.rich-editor {
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  overflow: hidden;
  background: #fff;
}
.rich-editor.disabled {
  opacity: 0.65;
  pointer-events: none;
}
.rich-toolbar {
  border-bottom: 1px solid #f0f0f0;
}
.rich-body {
  overflow-y: auto;
}
.rich-editor :deep(.w-e-text-container) {
  background: #fff;
}
</style>
