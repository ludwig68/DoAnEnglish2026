import { createApp } from 'vue'
import './style.css' // File này đã chứa Tailwind CSS
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(router) // Kích hoạt Vue Router
app.mount('#app')