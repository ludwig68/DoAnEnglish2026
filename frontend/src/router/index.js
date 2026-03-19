import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import CoursesView from '../views/CoursesView.vue'
import AboutView from '../views/AboutView.vue'
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'register', component: RegisterView },
    { path: '/courses', name: 'courses', component: CoursesView },
    { path: '/about', name: 'about', component: AboutView }
  ]
})

export default router