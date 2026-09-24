<template>
  <div class="build-page">
    <aside class="panel palette">
      <div class="panel-head">组件</div>
      <div class="panel-body">
        <div v-for="group in groups" :key="group.title" class="group">
          <div class="group-title">{{ group.title }}</div>
          <div class="palette-grid">
            <button
              v-for="item in group.items"
              :key="item.type"
              type="button"
              class="palette-item"
              draggable="true"
              @dragstart="onPaletteDrag(item, $event)"
              @click="addField(item.type)"
            >
              {{ item.label }}
            </button>
          </div>
        </div>
      </div>
    </aside>

    <section class="panel canvas">
      <div class="canvas-bar">
        <span class="canvas-title">表单画布</span>
        <div class="canvas-actions">
          <a-button @click="openPreview">预览</a-button>
          <a-button type="primary" @click="openCode">生成代码</a-button>
          <a-button @click="downloadJson">导出 JSON</a-button>
          <a-button @click="importOpen = true">导入 JSON</a-button>
          <a-button danger :disabled="!fields.length" @click="clearAll">清空</a-button>
        </div>
      </div>
      <div class="canvas-body" @dragover.prevent @drop="onCanvasDrop">
        <a-empty v-if="!fields.length" description="从左侧点击或拖入组件" />
        <a-form v-else :label-col="{ span: formConf.labelSpan }" :wrapper-col="{ span: 24 - formConf.labelSpan }">
          <a-row :gutter="16">
            <a-col v-for="(field, index) in fields" :key="field.id" :span="field.span">
              <div
                class="field-wrap"
                :class="{ active: activeId === field.id }"
                @click="activeId = field.id"
                @dragover.prevent
                @drop.stop="onFieldDrop(index)"
              >
                <div class="field-toolbar">
                  <span class="drag-handle" draggable="true" title="拖动排序" @dragstart="onFieldDrag(index, $event)">拖动</span>
                  <span class="field-key">{{ field.field }}</span>
                  <a-space size="small">
                    <a @click.stop="move(index, -1)">上移</a>
                    <a @click.stop="move(index, 1)">下移</a>
                    <a @click.stop="copyField(index)">复制</a>
                    <a class="danger" @click.stop="removeField(index)">删除</a>
                  </a-space>
                </div>
                <a-form-item :label="field.label" :required="field.required">
                  <FieldControl :field="field" :size="formConf.size" preview />
                </a-form-item>
              </div>
            </a-col>
          </a-row>
        </a-form>
      </div>
    </section>

    <aside class="panel props">
      <div class="panel-head">属性</div>
      <div class="panel-body">
        <div class="group-title">表单</div>
        <a-form layout="vertical" class="prop-form">
          <a-form-item label="标签宽度">
            <a-input-number v-model:value="formConf.labelSpan" :min="2" :max="12" style="width: 100%" />
          </a-form-item>
          <a-form-item label="控件尺寸">
            <a-radio-group v-model:value="formConf.size" :options="sizeOptions" />
          </a-form-item>
        </a-form>
        <template v-if="active">
          <div class="group-title">当前组件</div>
          <a-form layout="vertical" class="prop-form">
            <a-form-item label="标题">
              <a-input v-model:value="active.label" />
            </a-form-item>
            <a-form-item label="字段名">
              <a-input v-model:value="active.field" />
            </a-form-item>
            <a-form-item v-if="hasPlaceholder(active.type)" label="占位提示">
              <a-input v-model:value="active.placeholder" />
            </a-form-item>
            <a-form-item label="栅格">
              <a-input-number v-model:value="active.span" :min="1" :max="24" style="width: 100%" />
            </a-form-item>
            <a-form-item label="必填">
              <a-switch v-model:checked="active.required" />
            </a-form-item>
            <a-form-item v-if="active.type === 'textarea'" label="行数">
              <a-input-number v-model:value="active.rows" :min="2" :max="12" style="width: 100%" />
            </a-form-item>
            <a-form-item v-if="hasRange(active.type)" label="最小值">
              <a-input-number v-model:value="active.min" style="width: 100%" />
            </a-form-item>
            <a-form-item v-if="hasRange(active.type)" label="最大值">
              <a-input-number v-model:value="active.max" style="width: 100%" />
            </a-form-item>
            <a-form-item label="默认值">
              <a-switch v-if="active.type === 'switch'" v-model:checked="active.defaultValue" />
              <a-select
                v-else-if="active.type === 'checkbox'"
                v-model:value="active.defaultValue"
                mode="multiple"
                allow-clear
                :options="active.options"
                style="width: 100%"
              />
              <a-select
                v-else-if="active.type === 'select' || active.type === 'radio'"
                v-model:value="active.defaultValue"
                allow-clear
                :options="active.options"
                style="width: 100%"
              />
              <a-input-number
                v-else-if="hasRange(active.type)"
                v-model:value="active.defaultValue"
                :min="active.min"
                :max="active.max"
                style="width: 100%"
              />
              <a-input v-else v-model:value="active.defaultValue" allow-clear />
            </a-form-item>
            <template v-if="hasOptions(active.type)">
              <div class="group-title">选项</div>
              <div v-for="(opt, index) in active.options" :key="index" class="opt-row">
                <a-input v-model:value="opt.label" placeholder="标签" />
                <a-input v-model:value="opt.value" placeholder="值" />
                <a class="danger" @click="active.options.splice(index, 1)">删除</a>
              </div>
              <a-button size="small" block @click="addOption">添加选项</a-button>
            </template>
          </a-form>
        </template>
        <a-empty v-else description="选中画布中的组件后编辑" />
      </div>
    </aside>

    <a-modal v-model:open="previewOpen" title="表单预览" width="760" :footer="null">
      <a-form :model="previewForm" :label-col="{ span: formConf.labelSpan }" :wrapper-col="{ span: 24 - formConf.labelSpan }" @finish="onPreviewSubmit">
        <a-row :gutter="16">
          <a-col v-for="field in fields" :key="field.id" :span="field.span">
            <a-form-item :label="field.label" :name="field.field" :rules="rulesOf(field)">
              <FieldControl :field="field" :size="formConf.size" :model="previewForm" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :wrapper-col="{ offset: formConf.labelSpan }">
          <a-space>
            <a-button type="primary" html-type="submit">提交</a-button>
            <a-button @click="resetPreview">重置</a-button>
          </a-space>
        </a-form-item>
      </a-form>
    </a-modal>

    <a-modal v-model:open="codeOpen" title="生成代码" width="860" :footer="null">
      <a-tabs>
        <a-tab-pane key="vue" tab="Vue">
          <div class="code-actions">
            <a-button size="small" @click="copyText(vueCode)">复制</a-button>
            <a-button size="small" @click="downloadText('form.vue', vueCode)">下载</a-button>
          </div>
          <pre class="code-view">{{ vueCode }}</pre>
        </a-tab-pane>
        <a-tab-pane key="json" tab="JSON">
          <div class="code-actions">
            <a-button size="small" @click="copyText(jsonText)">复制</a-button>
            <a-button size="small" @click="downloadText('form.json', jsonText)">下载</a-button>
          </div>
          <pre class="code-view">{{ jsonText }}</pre>
        </a-tab-pane>
      </a-tabs>
    </a-modal>

    <a-modal v-model:open="importOpen" title="导入 JSON" ok-text="导入" @ok="doImport">
      <a-textarea v-model:value="importText" :rows="12" placeholder="粘贴导出的 JSON" />
    </a-modal>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref, watch } from 'vue'
import { Modal, message } from 'ant-design-vue'
import {
  DatePicker,
  Input,
  InputNumber,
  InputPassword,
  RadioGroup,
  CheckboxGroup,
  Rate,
  Select,
  Slider,
  Switch,
  Textarea,
  TimePicker,
  Upload,
  Button,
} from 'ant-design-vue'

const STORAGE_KEY = 'yshop-tool-build'
const groups = [
  {
    title: '输入型组件',
    items: [
      { type: 'input', label: '单行文本' },
      { type: 'textarea', label: '多行文本' },
      { type: 'password', label: '密码' },
      { type: 'number', label: '计数器' },
    ],
  },
  {
    title: '选择型组件',
    items: [
      { type: 'select', label: '下拉选择' },
      { type: 'radio', label: '单选框组' },
      { type: 'checkbox', label: '多选框组' },
      { type: 'switch', label: '开关' },
      { type: 'slider', label: '滑块' },
      { type: 'date', label: '日期选择' },
      { type: 'dateRange', label: '日期范围' },
      { type: 'time', label: '时间选择' },
      { type: 'rate', label: '评分' },
    ],
  },
  {
    title: '其他',
    items: [{ type: 'upload', label: '文件上传' }],
  },
]
const sizeOptions = [
  { label: '大', value: 'large' },
  { label: '中', value: 'middle' },
  { label: '小', value: 'small' },
]
const typeLabel = Object.fromEntries(groups.flatMap((group) => group.items.map((item) => [item.type, item.label])))

const fields = ref([])
const activeId = ref('')
const formConf = reactive({ labelSpan: 6, size: 'middle' })
const previewOpen = ref(false)
const previewForm = reactive({})
const codeOpen = ref(false)
const importOpen = ref(false)
const importText = ref('')
let seq = 1
let dragFrom = null

const active = computed(() => fields.value.find((field) => field.id === activeId.value) || null)
const jsonText = computed(() => JSON.stringify({ labelSpan: formConf.labelSpan, size: formConf.size, fields: fields.value }, null, 2))
const vueCode = computed(() => buildVue(fields.value, formConf))

const FieldControl = defineComponent({
  name: 'FieldControl',
  props: {
    field: { type: Object, required: true },
    size: { type: String, default: 'middle' },
    preview: { type: Boolean, default: false },
    model: { type: Object, default: null },
  },
  setup(props) {
    return () => renderControl(props.field, props.size, props.preview, props.model)
  },
})

function hasOptions(type) {
  return type === 'select' || type === 'radio' || type === 'checkbox'
}
function hasPlaceholder(type) {
  return ['input', 'textarea', 'password', 'number', 'select', 'date', 'dateRange', 'time'].includes(type)
}
function hasRange(type) {
  return type === 'number' || type === 'slider' || type === 'rate'
}
function defaultOptions() {
  return [
    { label: '选项一', value: '1' },
    { label: '选项二', value: '2' },
  ]
}
function defaultValue(type) {
  if (type === 'checkbox' || type === 'dateRange') return []
  if (type === 'switch') return false
  if (type === 'number' || type === 'slider') return 0
  if (type === 'rate') return 0
  return ''
}
function createField(type) {
  const n = seq++
  const label = typeLabel[type] || '字段'
  const inputTypes = ['input', 'textarea', 'password', 'number']
  return {
    id: 'f' + n,
    type,
    label,
    field: type + n,
    placeholder: hasPlaceholder(type) ? ((inputTypes.includes(type) ? '请输入' : '请选择') + label) : '',
    required: false,
    span: 24,
    rows: 3,
    min: 0,
    max: type === 'rate' ? 5 : 100,
    defaultValue: defaultValue(type),
    options: hasOptions(type) ? defaultOptions() : [],
  }
}
function addField(type, index) {
  const field = createField(type)
  if (typeof index === 'number') {
    fields.value.splice(index, 0, field)
  } else {
    fields.value.push(field)
  }
  activeId.value = field.id
}
function copyField(index) {
  const source = fields.value[index]
  const field = createField(source.type)
  field.label = source.label
  field.placeholder = source.placeholder
  field.required = source.required
  field.span = source.span
  field.rows = source.rows
  field.min = source.min
  field.max = source.max
  field.defaultValue = JSON.parse(JSON.stringify(source.defaultValue))
  field.options = JSON.parse(JSON.stringify(source.options || []))
  fields.value.splice(index + 1, 0, field)
  activeId.value = field.id
}
function removeField(index) {
  const [removed] = fields.value.splice(index, 1)
  if (removed && removed.id === activeId.value) {
    activeId.value = fields.value[Math.min(index, fields.value.length - 1)]?.id || ''
  }
}
function move(index, step) {
  const next = index + step
  if (next < 0 || next >= fields.value.length) return
  const [item] = fields.value.splice(index, 1)
  fields.value.splice(next, 0, item)
}
function addOption() {
  if (!active.value) return
  const n = active.value.options.length + 1
  active.value.options.push({ label: '选项' + n, value: String(n) })
}
function onPaletteDrag(item, event) {
  dragFrom = { kind: 'palette', type: item.type }
  event.dataTransfer.effectAllowed = 'copy'
  event.dataTransfer.setData('text/plain', item.type)
}
function onFieldDrag(index, event) {
  dragFrom = { kind: 'field', index }
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', String(index))
}
function onFieldDrop(index) {
  if (!dragFrom) return
  if (dragFrom.kind === 'palette') {
    addField(dragFrom.type, index)
  } else if (dragFrom.kind === 'field' && dragFrom.index !== index) {
    const from = dragFrom.index
    const [item] = fields.value.splice(from, 1)
    const target = from < index ? index - 1 : index
    fields.value.splice(target, 0, item)
    activeId.value = item.id
  }
  dragFrom = null
}
function onCanvasDrop() {
  if (!dragFrom) return
  if (dragFrom.kind === 'palette') {
    addField(dragFrom.type)
  }
  dragFrom = null
}
function clearAll() {
  Modal.confirm({
    title: '清空表单？',
    content: '画布上的组件会被全部移除。',
    onOk() {
      fields.value = []
      activeId.value = ''
    },
  })
}
function fillPreview() {
  Object.keys(previewForm).forEach((key) => delete previewForm[key])
  fields.value.forEach((field) => {
    previewForm[field.field] = JSON.parse(JSON.stringify(field.defaultValue))
  })
}
function openPreview() {
  if (!fields.value.length) {
    message.warning('请先添加组件')
    return
  }
  fillPreview()
  previewOpen.value = true
}
function resetPreview() {
  fillPreview()
}
function onPreviewSubmit() {
  message.success('校验通过')
}
function rulesOf(field) {
  if (!field.required) return []
  const choose = ['select', 'radio', 'checkbox', 'date', 'dateRange', 'time', 'upload'].includes(field.type)
  return [{ required: true, message: (choose ? '请选择' : '请输入') + field.label }]
}
function openCode() {
  if (!fields.value.length) {
    message.warning('请先添加组件')
    return
  }
  codeOpen.value = true
}
async function copyText(text) {
  try {
    await navigator.clipboard.writeText(text)
    message.success('已复制')
  } catch {
    message.error('复制失败，请手动选择代码')
  }
}
function downloadText(name, text) {
  const blob = new Blob([text], { type: 'text/plain;charset=utf-8' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = name
  link.click()
  URL.revokeObjectURL(link.href)
}
function downloadJson() {
  downloadText('form.json', jsonText.value)
}
function doImport() {
  let data
  try {
    data = JSON.parse(importText.value)
  } catch {
    message.error('JSON 格式不正确')
    return Promise.reject()
  }
  const list = Array.isArray(data) ? data : data?.fields
  if (!Array.isArray(list)) {
    message.error('JSON 中缺少 fields')
    return Promise.reject()
  }
  fields.value = list.map((item) => {
    const field = createField(item?.type || 'input')
    return { ...field, ...item, id: item?.id || field.id, type: item?.type || field.type }
  })
  if (data && typeof data === 'object' && !Array.isArray(data)) {
    if (data.labelSpan) formConf.labelSpan = Number(data.labelSpan)
    if (data.size) formConf.size = data.size
  }
  activeId.value = fields.value[0]?.id || ''
  importOpen.value = false
  message.success('已导入')
}

function renderControl(field, size, preview, model) {
  const source = preview ? field.defaultValue : model?.[field.field]
  const bind = field.type === 'switch'
    ? {
        checked: source,
        'onUpdate:checked': (value) => {
          if (!preview && model) model[field.field] = value
        },
      }
    : {
        value: source,
        'onUpdate:value': (value) => {
          if (!preview && model) model[field.field] = value
        },
      }
  const common = { size, disabled: preview, style: { width: '100%', pointerEvents: preview ? 'none' : undefined } }
  if (field.type === 'textarea') return h(Textarea, { ...common, ...bind, rows: field.rows, placeholder: field.placeholder })
  if (field.type === 'password') return h(InputPassword, { ...common, ...bind, placeholder: field.placeholder, allowClear: true })
  if (field.type === 'number') return h(InputNumber, { ...common, ...bind, min: field.min, max: field.max, placeholder: field.placeholder })
  if (field.type === 'select') return h(Select, { ...common, ...bind, allowClear: true, placeholder: field.placeholder, options: field.options })
  if (field.type === 'radio') return h(RadioGroup, { ...bind, disabled: preview, options: field.options })
  if (field.type === 'checkbox') return h(CheckboxGroup, { ...bind, disabled: preview, options: field.options })
  if (field.type === 'switch') return h(Switch, { ...bind, disabled: preview })
  if (field.type === 'slider') return h(Slider, { ...bind, disabled: preview, min: field.min, max: field.max })
  if (field.type === 'date') return h(DatePicker, { ...common, ...bind, valueFormat: 'YYYY-MM-DD', placeholder: field.placeholder })
  if (field.type === 'dateRange') return h(DatePicker.RangePicker, { ...common, ...bind, valueFormat: 'YYYY-MM-DD' })
  if (field.type === 'time') return h(TimePicker, { ...common, ...bind, valueFormat: 'HH:mm:ss', placeholder: field.placeholder })
  if (field.type === 'rate') return h(Rate, { ...bind, disabled: preview, count: field.max || 5 })
  if (field.type === 'upload') {
    return h(Upload, { disabled: preview, beforeUpload: () => false, maxCount: 1 }, { default: () => h(Button, { disabled: preview }, () => '选择文件') })
  }
  return h(Input, { ...common, ...bind, placeholder: field.placeholder, allowClear: true })
}

function buildVue(list, conf) {
  const lines = []
  const defaults = {}
  const optionBlocks = []
  list.forEach((field) => {
    defaults[field.field] = field.defaultValue
    const rules = field.required
      ? ` :rules="[{ required: true, message: ${js(promptOf(field))} }]"`
      : ''
    lines.push(`        <a-col :span="${Number(field.span) || 24}">`)
    lines.push(`          <a-form-item label=${attr(field.label)} name=${attr(field.field)}${rules}>`)
    lines.push('            ' + controlTag(field))
    lines.push('          </a-form-item>')
    lines.push('        </a-col>')
    if (hasOptions(field.type)) {
      optionBlocks.push(`const ${optionName(field)} = ${JSON.stringify(field.options || [], null, 2)}`)
    }
  })
  const script = [
    '<script setup>',
    "import { reactive } from 'vue'",
    '',
    ...optionBlocks,
    optionBlocks.length ? '' : '',
    `const form = reactive(${JSON.stringify(defaults, null, 2)})`,
    '',
    'function reset() {',
    `  Object.assign(form, ${JSON.stringify(defaults, null, 2)})`,
    '}',
    '</' + 'script>',
  ].filter((line, index, arr) => !(line === '' && arr[index - 1] === '')).join('\n')
  return [
    '<template>',
    `  <a-form :model="form" :label-col="{ span: ${Number(conf.labelSpan) || 6} }" :wrapper-col="{ span: ${24 - (Number(conf.labelSpan) || 6)} }">`,
    '    <a-row :gutter="16">',
    ...lines,
    '    </a-row>',
    `    <a-form-item :wrapper-col="{ offset: ${Number(conf.labelSpan) || 6} }">`,
    '      <a-space>',
    '        <a-button type="primary" html-type="submit">提交</a-button>',
    '        <a-button @click="reset">重置</a-button>',
    '      </a-space>',
    '    </a-form-item>',
    '  </a-form>',
    '</template>',
    '',
    script,
    '',
  ].join('\n')
}
function optionName(field) {
  return String(field.field || 'field').replace(/[^\w]/g, '_') + 'Options'
}
function promptOf(field) {
  const choose = ['select', 'radio', 'checkbox', 'date', 'dateRange', 'time', 'upload'].includes(field.type)
  return (choose ? '请选择' : '请输入') + (field.label || '')
}
function attr(value) {
  return JSON.stringify(String(value ?? ''))
}
function js(value) {
  return JSON.stringify(String(value ?? ''))
}
function formKey(field) {
  return `form['${String(field.field ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'")}']`
}
function controlTag(field) {
  const model = formKey(field)
  const ph = field.placeholder ? ` placeholder=${attr(field.placeholder)}` : ''
  if (field.type === 'textarea') return `<a-textarea v-model:value="${model}" :rows="${Number(field.rows) || 3}"${ph} />`
  if (field.type === 'password') return `<a-input-password v-model:value="${model}"${ph} allow-clear />`
  if (field.type === 'number') return `<a-input-number v-model:value="${model}" :min="${Number(field.min)}" :max="${Number(field.max)}"${ph} style="width: 100%" />`
  if (field.type === 'select') return `<a-select v-model:value="${model}" :options="${optionName(field)}" allow-clear${ph} style="width: 100%" />`
  if (field.type === 'radio') return `<a-radio-group v-model:value="${model}" :options="${optionName(field)}" />`
  if (field.type === 'checkbox') return `<a-checkbox-group v-model:value="${model}" :options="${optionName(field)}" />`
  if (field.type === 'switch') return `<a-switch v-model:checked="${model}" />`
  if (field.type === 'slider') return `<a-slider v-model:value="${model}" :min="${Number(field.min)}" :max="${Number(field.max)}" />`
  if (field.type === 'date') return `<a-date-picker v-model:value="${model}" value-format="YYYY-MM-DD"${ph} style="width: 100%" />`
  if (field.type === 'dateRange') return `<a-range-picker v-model:value="${model}" value-format="YYYY-MM-DD" style="width: 100%" />`
  if (field.type === 'time') return `<a-time-picker v-model:value="${model}" value-format="HH:mm:ss"${ph} style="width: 100%" />`
  if (field.type === 'rate') return `<a-rate v-model:value="${model}" :count="${Number(field.max) || 5}" />`
  if (field.type === 'upload') return '<a-upload :before-upload="() => false" :max-count="1"><a-button>选择文件</a-button></a-upload>'
  return `<a-input v-model:value="${model}"${ph} allow-clear />`
}

watch([fields, formConf], () => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ labelSpan: formConf.labelSpan, size: formConf.size, fields: fields.value, seq }))
}, { deep: true })

onMounted(() => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return
    const data = JSON.parse(raw)
    if (Array.isArray(data.fields)) fields.value = data.fields
    if (data.labelSpan) formConf.labelSpan = Number(data.labelSpan)
    if (data.size) formConf.size = data.size
    seq = Number(data.seq) || fields.value.length + 1
  } catch {
    localStorage.removeItem(STORAGE_KEY)
  }
})
</script>

<style scoped>
.build-page {
  display: grid;
  grid-template-columns: 240px minmax(0, 1fr) 280px;
  gap: 12px;
  height: calc(100vh - 120px);
  min-height: 520px;
}
.panel {
  display: flex;
  flex-direction: column;
  min-height: 0;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}
.panel-head {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  height: 52px;
  padding: 0 12px;
  border-bottom: 1px solid #f0f0f0;
  font-weight: 600;
}
.canvas-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-shrink: 0;
  min-height: 52px;
  padding: 10px 12px;
  border-bottom: 1px solid #f0f0f0;
}
.canvas-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 8px;
  min-width: 0;
}
.panel-body,
.canvas-body {
  flex: 1;
  min-height: 0;
  overflow: auto;
  padding: 12px;
}
.group + .group {
  margin-top: 16px;
}
.group-title {
  margin-bottom: 8px;
  color: #8c8c8c;
  font-size: 12px;
}
.palette-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.palette-item {
  width: calc(50% - 4px);
  padding: 8px 4px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  background: #fafafa;
  cursor: grab;
  color: #262626;
}
.palette-item:hover {
  border-color: #1677ff;
  color: #1677ff;
  background: #f0f7ff;
}
.canvas-title {
  flex-shrink: 0;
  font-weight: 600;
}
.canvas-body {
  background: #fafafa;
}
.field-wrap {
  margin-bottom: 12px;
  padding: 8px 8px 4px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
}
.field-wrap.active {
  border-color: #1677ff;
  box-shadow: 0 0 0 2px rgba(22, 119, 255, 0.12);
}
.field-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
  color: #8c8c8c;
  font-size: 12px;
}
.drag-handle {
  padding: 0 6px;
  border-radius: 4px;
  background: #f5f5f5;
  cursor: grab;
}
.field-key {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.danger {
  color: #ff4d4f;
}
.prop-form :deep(.ant-form-item) {
  margin-bottom: 12px;
}
.opt-row {
  display: grid;
  grid-template-columns: 1fr 72px auto;
  gap: 6px;
  align-items: center;
  margin-bottom: 8px;
}
.code-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-bottom: 8px;
}
.code-view {
  max-height: 460px;
  margin: 0;
  padding: 12px;
  overflow: auto;
  border-radius: 6px;
  background: #0f172a;
  color: #e2e8f0;
  font-size: 12px;
  line-height: 1.6;
  white-space: pre-wrap;
}
</style>
