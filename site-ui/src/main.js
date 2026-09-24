import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createBootstrap } from 'bootstrap-vue-next'
import { MotionPlugin } from '@vueuse/motion'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import '@/styles/site.scss'
import App from './App.vue'
import router from './router'
import { useSiteStore } from './store/site'
import { useUserStore } from './store/user'
import { getToken } from './utils/auth'

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(createBootstrap())
app.use(MotionPlugin)

const site = useSiteStore()
const user = useUserStore()
const boot = [site.loadSite()]
if (getToken()) {
  boot.push(user.fetchInfo())
}
Promise.allSettled(boot).finally(() => {
  app.mount('#app')
})
