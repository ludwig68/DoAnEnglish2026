<template>
  <div class="h-screen flex flex-col md:flex-row bg-white overflow-hidden selection:bg-emerald-100 selection:text-emerald-900 font-body">
    
    <!-- ═══ LEFT: INSPIRATION PANEL ═══ -->
    <div class="hidden md:flex md:w-1/2 relative p-16 lg:p-20 flex-col justify-between overflow-hidden"
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

      <!-- Header: Logo -->
      <div class="relative z-10 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-1000">
        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg transform hover:rotate-6 transition-transform">
          <i class="fa-solid fa-graduation-cap text-[#16a34a] text-xl"></i>
        </div>
        <h1 class="text-white font-headline font-black text-xl uppercase tracking-[0.2em]">English Learning</h1>
      </div>

      <!-- Center: Quote & Join Banner -->
      <div class="relative z-10 space-y-12">
        <!-- Quote -->
        <div class="animate-in fade-in slide-in-from-left-8 duration-1000 delay-300">
          <h2 class="text-5xl lg:text-6xl font-headline font-black text-white leading-[1.1] drop-shadow-2xl max-w-2xl italic tracking-tight">
            "The beautiful thing about learning is that no one can take it away from you."
          </h2>
          <div class="flex items-center gap-4 mt-8">
            <div class="w-12 h-[2px] bg-emerald-500/50"></div>
            <p class="text-sm font-bold text-emerald-200/60 uppercase tracking-widest">— B.B. King</p>
          </div>
        </div>

      </div>

      <!-- Footer branding -->
      <div class="relative z-10 text-emerald-500/30 text-[10px] font-black uppercase tracking-[0.3em]">
        English Learning © 2026
      </div>
    </div>

    <!-- ═══ RIGHT: ACCOUNT CREATION FORM ═══ -->
    <div class="w-full md:w-1/2 flex items-center justify-center bg-white p-8 sm:p-12 lg:p-20 relative overflow-y-auto">
      <div class="max-w-md w-full space-y-8 animate-in fade-in slide-in-from-right-8 duration-1000">
        
        <!-- Top: Back Button -->
        <router-link to="/" class="inline-flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-emerald-600 transition-all group">
          <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
          Quay lại Trang chủ
        </router-link>

        <!-- Header Section -->
        <div class="space-y-4">
          <div class="md:hidden flex items-center gap-3 mb-8">
            <i class="fa-solid fa-graduation-cap text-emerald-600 text-3xl"></i>
            <h1 class="font-headline font-black text-xl uppercase tracking-widest text-[#16a34a]">English Learning</h1>
          </div>
          <h2 class="text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight">Tạo tài khoản</h2>
          <p class="text-slate-400 text-sm font-bold leading-relaxed max-w-sm">
            Tham gia cộng đồng học tập toàn cầu để bắt đầu hành trình của bạn.
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

        <!-- Form Layout -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          
          <!-- Full Name -->
          <div class="space-y-3">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Họ và tên</label>
            <div class="relative group">
              <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                <i class="fa-solid fa-user"></i>
              </div>
              <input 
                v-model="formData.full_name" 
                type="text" 
                placeholder="Nguyễn Văn A"
                required
                class="w-full pl-14 pr-6 py-4 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
              >
            </div>
          </div>

          <!-- Email Address -->
          <div class="space-y-3">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Địa chỉ Email</label>
            <div class="relative group">
              <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <input 
                v-model="formData.email" 
                type="email" 
                placeholder="name@example.com"
                required
                class="w-full pl-14 pr-6 py-4 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
              >
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-3">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Mật khẩu</label>
            <div class="relative group">
              <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                <i class="fa-solid fa-lock"></i>
              </div>
              <input 
                v-model="formData.password" 
                :type="showPw ? 'text' : 'password'" 
                placeholder="••••••••"
                required
                class="w-full pl-14 pr-16 py-4 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
              >
              <button type="button" @click="showPw = !showPw" class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 hover:text-emerald-500 transition-all">
                <i :class="showPw ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
              </button>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-3">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Xác nhận mật khẩu</label>
            <div class="relative group">
              <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                <i class="fa-solid fa-shield-halved"></i>
              </div>
              <input 
                v-model="formData.password_confirm" 
                :type="showPw ? 'text' : 'password'" 
                placeholder="••••••••"
                required
                class="w-full pl-14 pr-6 py-4 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
              >
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full py-4 text-white rounded-[1.25rem] text-sm font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 transition-all hover:shadow-2xl hover:shadow-emerald-500/20 active:scale-95 disabled:opacity-50 mt-4"
            style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
          >
            <span>{{ isLoading ? 'Đang kích hoạt...' : 'Tạo tài khoản' }}</span>
            <i class="fa-solid fa-plus text-xs" v-if="!isLoading"></i>
            <i class="fa-solid fa-spinner animate-spin" v-else></i>
          </button>
        </form>

        <!-- OR Divider -->
        <div class="flex items-center gap-4 py-2">
          <div class="flex-1 h-px bg-slate-100"></div>
          <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Hoặc đăng ký bằng</span>
          <div class="flex-1 h-px bg-slate-100"></div>
        </div>

        <!-- Social Buttons -->
        <div class="grid grid-cols-2 gap-4">
          <button class="bg-[#F3F4F6] py-4 rounded-[1.25rem] flex items-center justify-center gap-3 hover:bg-white border-2 border-transparent hover:border-slate-100 transition-all shadow-sm">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
            <span class="text-[11px] font-black text-slate-600 uppercase tracking-wider">Google</span>
          </button>
          <button class="bg-[#F3F4F6] py-4 rounded-[1.25rem] flex items-center justify-center gap-3 hover:bg-white border-2 border-transparent hover:border-slate-100 transition-all shadow-sm">
            <i class="fa-brands fa-facebook text-blue-600 text-xl"></i>
            <span class="text-[11px] font-black text-slate-600 uppercase tracking-wider">Facebook</span>
          </button>
        </div>

        <!-- Footer Link -->
        <p class="text-center text-slate-400 text-xs font-bold leading-relaxed pt-2">
          Đã có tài khoản? 
          <router-link to="/login" class="text-emerald-600 hover:text-emerald-700 underline underline-offset-4 ml-1">Đăng nhập ngay</router-link>
        </p>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'

const router = useRouter()
const isLoading = ref(false)
const showPw = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const formData = ref({
  full_name: '',
  email: '',
  password: '',
  password_confirm: ''
})

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  // Validation
  if (formData.value.password !== formData.value.password_confirm) {
    errorMessage.value = 'Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.'
    return
  }

  isLoading.value = true

  try {
    const res = await apiFetch('auth/register.php', {
      method: 'POST',
      body: JSON.stringify(formData.value)
    })
    const result = await res.json()
    
    if (result.status === 'success') {
      successMessage.value = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...'
      setTimeout(() => {
        router.push('/login')
      }, 2000)
    } else {
      errorMessage.value = result.message || 'Có lỗi xảy ra trong quá trình đăng ký.'
    }
  } catch (err) {
    errorMessage.value = 'Không thể kết nối với máy chủ. Vui lòng thử lại sau.'
    console.error('Register Error:', err)
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

/* Premium smooth transitions for all interactive elements */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-duration: 300ms;
}

/* Hide scrollbar but allow scrolling on the right form */
.overflow-y-auto {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.overflow-y-auto::-webkit-scrollbar {
  display: none;
}
</style>