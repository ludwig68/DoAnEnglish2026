import { createApp } from 'vue'
import './style.css'
import './assets/css/theme-colors.css'
import App from './App.vue'
import router from './router'
import { notifyFromAlertMessage } from './utils/notify'

window.alert = (message) => {
  notifyFromAlertMessage(message)
}

const app = createApp(App)

app.use(router)
app.mount('#app')
