<template>
  <div class="h-screen flex flex-col md:flex-row bg-white overflow-hidden selection:bg-emerald-100 selection:text-emerald-900 font-body">
    
    <!-- ═══ LEFT: INFORMATION PANEL (HIDDEN ON MOBILE) ═══ -->
    <div class="hidden md:flex md:w-1/2 relative p-20 flex-col justify-between overflow-hidden" 
         style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
      <!-- Library Background Image -->
      <div class="absolute inset-0 z-0">
        <img 
          src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=2000" 
          alt="Ancient Library"
          class="w-full h-full object-cover opacity-20 mix-blend-overlay scale-110"
        >
        <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-transparent to-black/5"></div>
      </div>

      <!-- Header: Logo & Brand -->
      <div class="relative z-10 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-1000">
        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg transform hover:rotate-6 transition-transform">
          <i class="fa-solid fa-graduation-cap text-[#16a34a] text-xl"></i>
        </div>
        <h1 class="text-white font-headline font-black text-xl uppercase tracking-[0.2em]">English Learning</h1>
      </div>

      <!-- Center: Inspirational Quote -->
      <div class="relative z-10 animate-in fade-in slide-in-from-left-8 duration-1000 delay-300">
        <p class="text-xs font-black text-emerald-300 uppercase tracking-[0.4em] mb-6">Curating Excellence</p>
        <h2 class="text-5xl lg:text-6xl font-headline font-black text-white leading-[1.1] mb-12 drop-shadow-2xl max-w-2xl italic tracking-tight">
          "The beautiful thing about learning is that no one can take it away from you."
        </h2>
        <div class="flex items-center gap-4">
          <div class="w-12 h-[2px] bg-emerald-500/50"></div>
          <p class="text-sm font-bold text-emerald-200/60 uppercase tracking-widest font-body">B.B. King · Academic Excellence Series</p>
        </div>
      </div>

      <!-- Footer: Branding & Icons -->
      <div class="relative z-10 flex items-center justify-between text-emerald-500/40 text-[10px] font-black uppercase tracking-widest animate-in fade-in slide-in-from-bottom-4 duration-1000 delay-500">
        <p>© 2024 English Learning. All rights reserved.</p>
        <div class="flex gap-6 text-sm">
          <i class="fa-solid fa-globe hover:text-emerald-300 transition-colors cursor-pointer"></i>
          <i class="fa-solid fa-circle-question hover:text-emerald-300 transition-colors cursor-pointer"></i>
        </div>
      </div>
    </div>

    <!-- ═══ RIGHT: AUTHENTICATION FORM ═══ -->
    <div class="w-full md:w-1/2 flex items-center justify-center bg-white p-8 sm:p-20 relative overflow-y-auto">
      <div class="max-w-md w-full space-y-10 animate-in fade-in slide-in-from-right-8 duration-1000">
        
        <!-- Top: Back Button -->
        <router-link to="/" class="inline-flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-emerald-600 transition-all group">
          <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
          Quay lại Trang chủ
        </router-link>

        <!-- Header Section -->
        <div class="space-y-4">
          <div class="md:hidden flex items-center gap-3 mb-8">
            <i class="fa-solid fa-graduation-cap text-[#16a34a] text-3xl"></i>
            <h1 class="font-headline font-black text-xl uppercase tracking-widest text-[#16a34a]">English Learning</h1>
          </div>
          <h2 class="text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight">Chào mừng trở lại</h2>
          <p class="text-slate-400 text-sm font-bold leading-relaxed max-w-sm">
            Đăng nhập để tiếp tục hành trình chinh phục Anh ngữ của bạn tại English Learning.
          </p>
        </div>

        <!-- Feedback Messages -->
        <transition-group name="msg">
          <div v-if="errorMessage" key="err" class="p-4 rounded-2xl bg-red-50 border border-red-100 flex items-center gap-3 text-red-500 text-xs font-bold uppercase tracking-tight shadow-sm">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ errorMessage }}
          </div>
          <div v-if="successMessage" key="suc" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 text-emerald-600 text-xs font-bold uppercase tracking-tight shadow-sm">
            <i class="fa-solid fa-circle-check"></i> {{ successMessage }}
          </div>
        </transition-group>

        <!-- Form Logic -->
        <form @submit.prevent="handleSubmit" class="space-y-8">
          
          <!-- Email / Username -->
          <div class="space-y-4">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Email hoặc Tên đăng nhập</label>
            <div class="relative group">
              <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                <i class="fa-solid fa-at"></i>
              </div>
              <input 
                v-model="formData.email" 
                type="email" 
                placeholder="email@example.com"
                required
                class="w-full pl-14 pr-6 py-5 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
              >
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-4">
            <div class="flex justify-between items-center px-1">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Mật khẩu</label>
              <a href="#" class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] hover:text-emerald-700 transition-all">Quên mật khẩu?</a>
            </div>
            <div class="relative group">
              <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                <i class="fa-solid fa-lock"></i>
              </div>
              <input 
                v-model="formData.password" 
                :type="showPw ? 'text' : 'password'" 
                placeholder="••••••••"
                required
                class="w-full pl-14 pr-16 py-5 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
              >
              <button type="button" @click="showPw = !showPw" class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 hover:text-emerald-500 transition-all">
                <i :class="showPw ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
              </button>
            </div>
          </div>

          <!-- Options -->
          <div class="flex items-center gap-3 px-1">
            <input type="checkbox" id="remember" class="w-5 h-5 rounded-lg border-slate-200 text-emerald-500 focus:ring-emerald-500/20 cursor-pointer">
            <label for="remember" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest cursor-pointer select-none">Ghi nhớ đăng nhập trong 30 ngày</label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full py-5 text-white rounded-[1.25rem] text-sm font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 transition-all hover:shadow-2xl hover:shadow-emerald-500/20 active:scale-95 disabled:opacity-50"
            style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
          >
            <span>{{ isLoading ? 'Đang xử lý...' : 'Đăng nhập' }}</span>
            <i class="fa-solid fa-arrow-right-long text-xs transition-transform group-hover:translate-x-1" v-if="!isLoading"></i>
            <i class="fa-solid fa-spinner animate-spin v-else" v-if="isLoading"></i>
          </button>
        </form>

        <!-- Dynamic Sub-text -->
        <p class="text-center text-slate-400 text-xs font-bold leading-relaxed max-w-sm mx-auto opacity-80">
          Chưa có tài khoản? 
          <router-link to="/register" class="text-emerald-600 hover:text-emerald-700 underline underline-offset-4 ml-1">Đăng ký ngay</router-link>
        </p>

        <!-- Divider -->
        <div class="h-px w-full bg-slate-100"></div>

        <!-- Footer Links -->
        <div class="flex items-center justify-center gap-10 text-[9px] font-black text-slate-300 uppercase tracking-[0.25em]">
          <a href="#" class="hover:text-emerald-500 transition-colors">Chính sách bảo mật</a>
          <a href="#" class="hover:text-emerald-500 transition-colors">Điều khoản sử dụng</a>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { setAuthSession } from '../../utils/auth'

const router = useRouter()
const showPw = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const formData = ref({ email: '', password: '' })

const handleSubmit = async () => {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const res = await apiFetch('auth/login.php', {
      method: 'POST',
      body: JSON.stringify({ email: formData.value.email, password: formData.value.password })
    })
    const result = await res.json()
    
    if (result.status === 'success') {
      successMessage.value = 'Welcome home! Redirecting...'
      setAuthSession({ token: result.token, user: result.user })
      setTimeout(() => {
        if (result.user.role === 'admin') {
          router.push('/admin')
        } else if (result.user.role === 'instructor') {
          router.push('/teacher/dashboard')
        } else {
          router.push('/user/dashboard')
        }
      }, 1000)
    } else {
      errorMessage.value = result.message || 'Invalid credentials. Please try again.'
    }
  } catch (err) {
    errorMessage.value = 'Network error. Please try again later.'
    console.error('Login Error:', err)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

/* Custom animations for the redesign */
.msg-enter-active, .msg-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.msg-enter-from, .msg-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Hide scrollbar but allow scrolling */
.overflow-y-auto {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.overflow-y-auto::-webkit-scrollbar {
  display: none;
}

/* Premium smooth transitions for all elements */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-duration: 300ms;
}
</style>
