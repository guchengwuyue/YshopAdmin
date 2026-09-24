import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Antd from 'ant-design-vue'
import 'ant-design-vue/dist/reset.css'
import App from './App.vue'
import router from './router'
import './permission'
import './assets/admin.css'

createApp(App).use(createPinia()).use(router).use(Antd).mount('#app')
