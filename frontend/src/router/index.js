import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue' // Chúng ta sẽ tạo file này ở Bước 3

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    }
    // Sau này chúng ta sẽ thêm các trang Khóa học, Đăng nhập vào đây
  ]
})

export default router