import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useI18nStore } from '@/stores/i18n'
import './assets/main.css'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)

// Global translation helper
const i18n = useI18nStore(pinia)
app.config.globalProperties.$t = i18n.t

app.use(router)
app.mount('#app')
