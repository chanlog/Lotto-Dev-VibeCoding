import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import api from './services/api'
import './assets/css/tailwind.css'

const app = createApp(App)

// axios 인스턴스를 전역으로 설정
app.config.globalProperties.$http = api

app.use(store)
app.use(router)

app.mount('#app')