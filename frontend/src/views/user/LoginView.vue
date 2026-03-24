<template>
  <div class="flex items-center justify-center min-h-[calc(100vh-140px)] px-4">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-8 animate__animated animate__fadeIn">
      
      <div class="flex items-center gap-3 mb-6">
        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-md bg-[#7AE582]">
          <i class="fa-solid fa-graduation-cap text-white text-lg"></i>
        </div>
        <div class="flex flex-col leading-tight">
          <span class="text-sm font-bold text-slate-900 uppercase">English Learning</span>
          <span class="text-[0.7rem] text-slate-400">Dự án học tập 2026</span>
        </div>
      </div>

      <h1 class="text-2xl font-bold text-slate-900 mb-1">Chào mừng trở lại!</h1>
      <p class="text-sm text-slate-500 mb-6">Đăng nhập để tiếp tục hành trình chinh phục tiếng Anh.</p>

      <div v-if="errorMessage" class="mb-4 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg p-3 flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-700">Địa chỉ Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-regular fa-envelope"></i>
            </span>
            <input v-model="loginData.email" type="email" placeholder="email@vi-du.com" 
              class="w-full rounded-xl bg-slate-50 border border-slate-200 text-sm px-10 py-3 focus:ring-2 focus:ring-[#7AE582] focus:outline-none transition">
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-700">Mật khẩu</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="fa-solid fa-lock"></i>
            </span>
            <input v-model="loginData.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" 
              class="w-full rounded-xl bg-slate-50 border border-slate-200 text-sm px-10 py-3 focus:ring-2 focus:ring-[#7AE582] focus:outline-none transition">
            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
              <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
            </button>
          </div>
        </div>

        <button type="submit" :disabled="isLoading"
          class="w-full py-3 text-sm font-bold text-white rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50"
          style="background: linear-gradient(90deg, #7AE582, #54CC6D);">
          <span v-if="isLoading"><i class="fa-solid fa-spinner animate-spin"></i> Đang xử lý...</span>
          <span v-else>Đăng nhập ngay <i class="fa-solid fa-arrow-right ml-1"></i></span>
        </button>
      </form>

      <p class="mt-6 text-center text-xs text-slate-500">
        Chưa có tài khoản? <router-link to="/register" class="font-bold text-[#16a34a] hover:underline">Đăng ký miễn phí</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { setAuthSession } from '../../utils/auth'

const router = useRouter()
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const loginData = ref({
  email: '',
  password: ''
})

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await apiFetch('auth/login.php', {
      method: 'POST',
      body: JSON.stringify(loginData.value)
    })
    
    const result = await response.json()
    
    if (result.status === 'success') {
      setAuthSession({
        token: result.token,
        user: result.user
      })
      
      if (result.user.role === 'admin') {
        router.push('/admin')
      } else {
        router.push('/user/dashboard')
      }
    } else {
      errorMessage.value = result.message
    }
  } catch (error) {
    errorMessage.value = "Không thể kết nối với máy chủ."
  } finally {
    isLoading.value = false
  }
}
</script>
