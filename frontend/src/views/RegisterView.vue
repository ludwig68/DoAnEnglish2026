<template>
  <div class="flex items-center justify-center min-h-[calc(100vh-140px)] px-4 py-8">
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

      <h1 class="text-2xl font-bold text-slate-900 mb-1">Tạo tài khoản mới</h1>
      <p class="text-sm text-slate-500 mb-6">Đăng ký để lưu tiến độ học và lộ trình của bạn.</p>

      <div v-if="errorMessage" class="mb-4 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg p-3 flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> {{ errorMessage }}
      </div>

      <div v-if="successMessage" class="mb-4 text-xs text-[#16a34a] bg-emerald-50 border border-emerald-200 rounded-lg p-3 flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> {{ successMessage }}
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4" v-if="!successMessage">
        
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-700">Họ và Tên</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-regular fa-user"></i></span>
            <input v-model="formData.full_name" type="text" placeholder="Nhập họ tên của bạn" required
              class="w-full rounded-xl bg-slate-50 border border-slate-200 text-sm px-10 py-3 focus:ring-2 focus:ring-[#7AE582] focus:outline-none transition">
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-700">Địa chỉ Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-regular fa-envelope"></i></span>
            <input v-model="formData.email" type="email" placeholder="email@vi-du.com" required
              class="w-full rounded-xl bg-slate-50 border border-slate-200 text-sm px-10 py-3 focus:ring-2 focus:ring-[#7AE582] focus:outline-none transition">
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-700">Mật khẩu</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-solid fa-lock"></i></span>
            <input v-model="formData.password" type="password" placeholder="Tối thiểu 6 ký tự" required
              class="w-full rounded-xl bg-slate-50 border border-slate-200 text-sm px-10 py-3 focus:ring-2 focus:ring-[#7AE582] focus:outline-none transition">
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-700">Nhập lại mật khẩu</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fa-solid fa-lock"></i></span>
            <input v-model="formData.password_confirm" type="password" placeholder="Xác nhận mật khẩu" required
              class="w-full rounded-xl bg-slate-50 border border-slate-200 text-sm px-10 py-3 focus:ring-2 focus:ring-[#7AE582] focus:outline-none transition">
          </div>
        </div>

        <button type="submit" :disabled="isLoading"
          class="w-full py-3 text-sm font-bold text-white rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50"
          style="background: linear-gradient(90deg, #7AE582, #54CC6D);">
          <span v-if="isLoading"><i class="fa-solid fa-spinner animate-spin"></i> Đang xử lý...</span>
          <span v-else>Đăng ký tài khoản <i class="fa-solid fa-user-plus ml-1"></i></span>
        </button>
      </form>

      <p class="mt-6 text-center text-xs text-slate-500">
        Đã có tài khoản? <router-link to="/login" class="font-bold text-[#16a34a] hover:underline">Đăng nhập</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const formData = ref({
  full_name: '',
  email: '',
  password: '',
  password_confirm: ''
})

const handleRegister = async () => {
  // Kiểm tra khớp mật khẩu ngay tại Frontend
  if (formData.value.password !== formData.value.password_confirm) {
    errorMessage.value = "Mật khẩu xác nhận không khớp!"
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await fetch('http://localhost/DoAnEnglish2026/backend/api/auth/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    })
    
    const result = await response.json()
    
    if (result.status === 'success') {
      successMessage.value = "Đăng ký thành công! Đang chuyển hướng đến trang Đăng nhập..."
      // Đợi 2 giây rồi chuyển sang trang login
      setTimeout(() => {
        router.push('/login')
      }, 2000)
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