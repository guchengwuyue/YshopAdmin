<template>
  <div
    ref="rootEl"
    class="yst-slider"
    @touchstart.passive="onTouchStart"
    @touchend.passive="onTouchEnd"
  >
    <div class="yst-slider-stage">
      <div
        v-for="(block, i) in items"
        :key="i"
        class="yst-slide"
        :class="{
          'is-active': i === current || i === animTo,
          'is-animating': i === animFrom || i === animTo,
        }"
        :ref="(el) => setSlideRef(el, i)"
      >
        <div class="yst-layer yst-layer-text">
          <div class="yst-slide-content" v-html="slideHtml(block)"></div>
          <a v-if="block.url" class="yst-slide-link" :href="block.url">了解更多</a>
        </div>
        <div class="yst-layer yst-layer-media">
          <img v-if="block.image" :src="block.image" :alt="block.title || ''" />
        </div>
      </div>
    </div>

    <template v-if="items.length > 1">
      <div class="yst-slider-arrows">
        <span class="yst-slider-prev" role="button" aria-label="上一张" @click="prev"></span>
        <span class="yst-slider-next" role="button" aria-label="下一张" @click="next"></span>
      </div>
      <nav class="yst-slider-dots" aria-label="轮播指示">
        <span
          v-for="(_, i) in items"
          :key="i"
          :class="{ 'is-active': i === current }"
          @click="goDot(i)"
        ></span>
      </nav>
    </template>
  </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  interval: { type: Number, default: 5000 },
  duration: { type: Number, default: 1200 },
  autoplay: { type: Boolean, default: true },
})

const rootEl = ref(null)
const slideEls = ref([])
const current = ref(0)
const animFrom = ref(-1)
const animTo = ref(-1)

let animating = false
let autoplayOn = true
let timer = null
let rafId = 0
let touchX = 0
let textFactor = 1
let mediaFactor = 0.72

function setSlideRef(el, i) {
  if (el) slideEls.value[i] = el
}

function slideHtml(block) {
  const raw = String(block?.content || block?.title || '')
  if (!raw) return ''
  if (/<(p|h[1-4]|div)\b/i.test(raw)) return raw
  const text = raw
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<[^>]+>/g, '')
    .replace(/\r/g, '')
  const lines = text
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean)
  if (!lines.length) return raw
  return lines.map((line) => `<p class="yst-slide-line">${escapeHtml(line)}</p>`).join('')
}

function escapeHtml(s) {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

function clamp01(v) {
  return v < 0 ? 0 : v > 1 ? 1 : v
}

function easeInOutCubic(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2
}

function delayed(t, delay) {
  return easeInOutCubic(clamp01((t - delay) / (1 - delay)))
}

function layer(slide, sel) {
  return slide?.querySelector(sel) || null
}

function setLayer(el, xPercent, opacity) {
  if (!el) return
  el.style.transform = `translate3d(${xPercent}%,0,0)`
  el.style.opacity = String(opacity)
}

function resetResting(slide, visible) {
  if (!slide) return
  setLayer(layer(slide, '.yst-layer-text'), 0, visible ? 1 : 0)
  setLayer(layer(slide, '.yst-layer-media'), 0, visible ? 1 : 0)
}

function stopAuto() {
  if (timer) {
    clearTimeout(timer)
    timer = null
  }
}

function startAuto() {
  stopAuto()
  if (!autoplayOn || !props.autoplay || props.items.length < 2) return
  timer = setTimeout(() => {
    goTo(current.value + 1, 'next')
    startAuto()
  }, props.interval)
}

function paint(progress, dir, fromEl, toEl) {
  const outText = delayed(progress, 0)
  const outMedia = delayed(progress, 0.2)
  const inText = delayed(progress, 0.22)
  const inMedia = delayed(progress, 0.4)
  const sign = dir === 'next' ? 1 : -1
  const textDist = 110 * textFactor
  const mediaDist = 110 * mediaFactor

  // 文字先动，图片跟进；入场同样左文先、右图后
  setLayer(layer(fromEl, '.yst-layer-text'), -sign * textDist * outText, 1 - outText)
  setLayer(layer(fromEl, '.yst-layer-media'), sign * mediaDist * outMedia, 1 - outMedia)
  setLayer(layer(toEl, '.yst-layer-text'), -sign * textDist * (1 - inText), inText)
  setLayer(layer(toEl, '.yst-layer-media'), sign * mediaDist * (1 - inMedia), inMedia)
}

function goTo(page, dir) {
  const total = props.items.length
  if (total < 2 || animating) return
  page = ((page % total) + total) % total
  if (page === current.value) return

  if (!dir) {
    dir = page > current.value ? 'next' : 'prev'
    if (current.value === total - 1 && page === 0) dir = 'next'
    else if (current.value === 0 && page === total - 1) dir = 'prev'
  }

  animating = true
  const from = current.value
  const fromEl = slideEls.value[from]
  const toEl = slideEls.value[page]
  animFrom.value = from
  animTo.value = page

  let start = 0
  if (rafId) cancelAnimationFrame(rafId)

  const frame = (ts) => {
    if (!start) start = ts
    const t = clamp01((ts - start) / props.duration)
    paint(t, dir, fromEl, toEl)
    if (t < 1) {
      rafId = requestAnimationFrame(frame)
      return
    }
    resetResting(fromEl, false)
    resetResting(toEl, true)
    current.value = page
    animFrom.value = -1
    animTo.value = -1
    animating = false
    rafId = 0
  }

  paint(0, dir, fromEl, toEl)
  rafId = requestAnimationFrame(frame)
}

function next() {
  autoplayOn = false
  stopAuto()
  goTo(current.value + 1, 'next')
}

function prev() {
  autoplayOn = false
  stopAuto()
  goTo(current.value - 1, 'prev')
}

function goDot(i) {
  autoplayOn = false
  stopAuto()
  goTo(i)
}

function onTouchStart(e) {
  touchX = e.changedTouches?.[0]?.clientX ?? e.touches?.[0]?.clientX ?? 0
}

function onTouchEnd(e) {
  const x = e.changedTouches?.[0]?.clientX ?? 0
  const dx = x - touchX
  if (Math.abs(dx) < 50) return
  autoplayOn = false
  stopAuto()
  if (dx < 0) goTo(current.value + 1, 'next')
  else goTo(current.value - 1, 'prev')
}

/** 首屏入场：文字先出，再出右侧图片 */
function playEnter() {
  const el = slideEls.value[0]
  if (!el) return
  const textDist = 110 * textFactor
  const mediaDist = 110 * mediaFactor
  setLayer(layer(el, '.yst-layer-text'), -textDist, 0)
  setLayer(layer(el, '.yst-layer-media'), mediaDist, 0)
  animTo.value = 0
  animating = true
  let start = 0
  const frame = (ts) => {
    if (!start) start = ts
    const t = clamp01((ts - start) / props.duration)
    const inText = delayed(t, 0)
    const inMedia = delayed(t, 0.28)
    setLayer(layer(el, '.yst-layer-text'), -textDist * (1 - inText), inText)
    setLayer(layer(el, '.yst-layer-media'), mediaDist * (1 - inMedia), inMedia)
    if (t < 1) {
      rafId = requestAnimationFrame(frame)
      return
    }
    resetResting(el, true)
    animTo.value = -1
    animating = false
    rafId = 0
    startAuto()
  }
  rafId = requestAnimationFrame(frame)
}

function initLayers() {
  const root = rootEl.value
  if (root) {
    textFactor = parseFloat(getComputedStyle(root).getPropertyValue('--yst-text-factor')) || 1
    mediaFactor = parseFloat(getComputedStyle(root).getPropertyValue('--yst-media-factor')) || 0.72
  }
  slideEls.value.forEach((el, idx) => {
    resetResting(el, false)
    if (idx === 0) {
      // 入场动画前保持隐藏
      setLayer(layer(el, '.yst-layer-text'), 0, 0)
      setLayer(layer(el, '.yst-layer-media'), 0, 0)
    }
  })
  current.value = 0
}

watch(
  () => props.items,
  async () => {
    stopAuto()
    if (rafId) cancelAnimationFrame(rafId)
    animating = false
    animFrom.value = -1
    animTo.value = -1
    current.value = 0
    slideEls.value = []
    await nextTick()
    if (!props.items.length) return
    initLayers()
    playEnter()
  },
)

onMounted(async () => {
  autoplayOn = props.autoplay
  await nextTick()
  if (!props.items.length) return
  initLayers()
  playEnter()
})

onBeforeUnmount(() => {
  stopAuto()
  if (rafId) cancelAnimationFrame(rafId)
})
</script>
