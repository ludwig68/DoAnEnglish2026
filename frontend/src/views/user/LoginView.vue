<template>
  <div class="min-h-[calc(100vh-64px)] flex items-center justify-center p-4 sm:p-8"
    style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #d1fae5 100%)">

    <!-- Card wrapper -->
    <div class="w-full max-w-5xl bg-white rounded-[2rem] overflow-hidden flex flex-col md:flex-row border border-emerald-50"
      style="box-shadow: 0 20px 60px rgba(0,0,0,0.10), 0 4px 16px rgba(0,0,0,0.06)">

      <!-- ═══ LEFT: Image Panel ═══ -->
      <div class="hidden md:block md:w-5/12 relative flex-shrink-0">
        <img
          src="https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&q=80"
          alt="English learning"
          class="absolute inset-0 w-full h-full object-cover"
        >
      </div>

      <!-- ═══ RIGHT: Form Panel ═══ -->
      <div class="w-full md:w-7/12 flex flex-col justify-center p-8 sm:p-12 lg:p-14 bg-white">
        <div class="max-w-md w-full mx-auto">

          <!-- Mobile brand -->
          <div class="md:hidden text-center mb-8">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-emerald-100"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
              <i class="fa-solid fa-graduation-cap text-white text-xl"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900">{{ isLogin ? 'Đăng nhập' : 'Tạo tài khoản' }}</h2>
          </div>

          <!-- TAB switcher -->
          <div class="relative flex p-1.5 rounded-2xl mb-8 border border-emerald-100"
            style="background: rgba(240,253,244,0.8)">
            <button type="button" @click="switchTab(true)"
              class="relative flex-1 py-3 text-sm font-semibold z-10 transition-colors duration-200"
              :class="isLogin ? 'text-slate-900' : 'text-emerald-600 hover:text-emerald-800'">
              Đăng nhập
            </button>
            <button type="button" @click="switchTab(false)"
              class="relative flex-1 py-3 text-sm font-semibold z-10 transition-colors duration-200"
              :class="!isLogin ? 'text-slate-900' : 'text-emerald-600 hover:text-emerald-800'">
              Đăng ký
            </button>
            <!-- Sliding pill -->
            <div class="absolute top-1.5 bottom-1.5 rounded-xl bg-white border border-emerald-100/40 transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
              style="width: calc(50% - 0.375rem); box-shadow: 0 2px 8px rgba(0,0,0,0.06)"
              :style="isLogin ? 'left:0.375rem' : 'left:calc(50% + 0.375rem)'">
            </div>
          </div>

          <!-- Error / Success -->
          <transition name="fade-slide">
            <div v-if="errorMessage" key="err"
              class="mb-5 rounded-xl px-4 py-3 text-sm flex items-center gap-2 border border-red-200 bg-red-50 text-red-600">
              <i class="fa-solid fa-circle-exclamation shrink-0"></i> {{ errorMessage }}
            </div>
          </transition>
          <transition name="fade-slide">
            <div v-if="successMessage" key="suc"
              class="mb-5 rounded-xl px-4 py-3 text-sm flex items-center gap-2 border border-emerald-200 bg-emerald-50 text-emerald-700">
              <i class="fa-solid fa-circle-check shrink-0"></i> {{ successMessage }}
            </div>
          </transition>

          <!-- Form -->
          <form @submit.prevent="handleSubmit" class="space-y-4">

            <!-- Name field (only register) -->
            <transition name="expand">
              <div v-if="!isLogin" class="overflow-hidden">
                <label class="block text-sm font-medium text-slate-700 mb-1.5 ml-0.5">Họ và tên</label>
                <div class="relative group">
                  <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-emerald-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none"></i>
                  <input v-model="formData.full_name" type="text" placeholder="Nguyễn Văn A" :required="!isLogin"
                    class="w-full pl-11 pr-4 py-3.5 bg-white border border-emerald-200 rounded-xl text-sm text-slate-800 outline-none transition-all placeholder:text-slate-300
                           focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 hover:border-emerald-300">
                </div>
              </div>
            </transition>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5 ml-0.5">Email</label>
              <div class="relative group">
                <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-emerald-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none"></i>
                <input v-model="formData.email" type="email" placeholder="name@example.com" autocomplete="email" required
                  class="w-full pl-11 pr-4 py-3.5 bg-white border border-emerald-200 rounded-xl text-sm text-slate-800 outline-none transition-all placeholder:text-slate-300
                         focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 hover:border-emerald-300">
              </div>
            </div>

            <!-- Password -->
            <div>
              <div class="flex items-center justify-between mb-1.5 ml-0.5 pr-0.5">
                <label class="text-sm font-medium text-slate-700">Mật khẩu</label>
                <a v-if="isLogin" href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-800 hover:underline transition-colors">
                  Quên mật khẩu?
                </a>
              </div>
              <div class="relative group">
                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-emerald-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none"></i>
                <input v-model="formData.password" :type="showPw ? 'text' : 'password'" placeholder="••••••••"
                  autocomplete="current-password" required
                  class="w-full pl-11 pr-12 py-3.5 bg-white border border-emerald-200 rounded-xl text-sm text-slate-800 outline-none transition-all placeholder:text-slate-300
                         focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 hover:border-emerald-300">
                <button type="button" @click="showPw = !showPw"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-emerald-300 hover:text-emerald-600 transition-colors">
                  <i :class="showPw ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
            </div>

            <!-- Confirm Password (only register) -->
            <transition name="expand">
              <div v-if="!isLogin" class="overflow-hidden">
                <label class="block text-sm font-medium text-slate-700 mb-1.5 ml-0.5">Xác nhận mật khẩu</label>
                <div class="relative group">
                  <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-emerald-300 group-focus-within:text-emerald-500 transition-colors pointer-events-none"></i>
                  <input v-model="formData.password_confirm" :type="showPwConfirm ? 'text' : 'password'"
                    placeholder="••••••••" :required="!isLogin"
                    class="w-full pl-11 pr-12 py-3.5 bg-white border rounded-xl text-sm text-slate-800 outline-none transition-all placeholder:text-slate-300
                           focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 hover:border-emerald-300"
                    :class="formData.password_confirm && formData.password !== formData.password_confirm
                      ? 'border-red-300 bg-red-50/30'
                      : 'border-emerald-200'">
                  <button type="button" @click="showPwConfirm = !showPwConfirm"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-emerald-300 hover:text-emerald-600 transition-colors">
                    <i :class="showPwConfirm ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                  </button>
                </div>
                <p v-if="formData.password_confirm && formData.password !== formData.password_confirm"
                  class="text-[11px] text-red-500 mt-1.5 flex items-center gap-1">
                  <i class="fa-solid fa-triangle-exclamation text-[0.6rem]"></i> Mật khẩu không khớp
                </p>
              </div>
            </transition>

            <!-- Submit -->
            <button type="submit" :disabled="isLoading"
              class="w-full py-3.5 px-4 text-white rounded-xl font-semibold text-sm flex items-center justify-center gap-2.5 mt-6 transition-all disabled:opacity-60"
              style="background: #16a34a; box-shadow: 0 4px 14px rgba(22,163,74,0.25)"
              @mouseenter="e => e.currentTarget.style.background='#15803d'"
              @mouseleave="e => e.currentTarget.style.background='#16a34a'">
              <i v-if="isLoading" class="fa-solid fa-spinner animate-spin text-xs"></i>
              <span>{{ isLoading ? 'Đang xử lý...' : (isLogin ? 'Đăng nhập' : 'Tạo tài khoản mới') }}</span>
              <i v-if="!isLoading" class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </form>

          <!-- Divider -->
          <div class="flex items-center gap-4 my-6">
            <div class="flex-1 h-px bg-emerald-100"></div>
            <span class="text-[10px] font-bold text-emerald-400/80 uppercase tracking-widest whitespace-nowrap">Hoặc tiếp tục với</span>
            <div class="flex-1 h-px bg-emerald-100"></div>
          </div>

          <!-- Social buttons -->
          <div class="grid grid-cols-2 gap-3">
            <button type="button"
              class="flex items-center justify-center gap-2.5 py-2.5 px-4 bg-white border border-emerald-200 rounded-xl hover:bg-emerald-50 hover:border-emerald-300 transition-all text-sm font-semibold text-slate-700">
              <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
              </svg>
              Google
            </button>
            <button type="button"
              class="flex items-center justify-center gap-2.5 py-2.5 px-4 bg-white border border-emerald-200 rounded-xl hover:bg-emerald-50 hover:border-emerald-300 transition-all text-sm font-semibold text-slate-700">
              <svg class="w-5 h-5 shrink-0 text-slate-800" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
              </svg>
              GitHub
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { setAuthSession } from '../../utils/auth'

const router = useRouter()
const route = useRoute()

// Determine initial tab from route
const isLogin = ref(route.path === '/login')

const showPw = ref(false)
const showPwConfirm = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const formData = ref({ full_name: '', email: '', password: '', password_confirm: '' })

const switchTab = (toLogin) => {
  isLogin.value = toLogin
  errorMessage.value = ''
  successMessage.value = ''
  formData.value = { full_name: '', email: '', password: '', password_confirm: '' }
  router.replace(toLogin ? '/login' : '/register')
}

const handleSubmit = async () => {
  errorMessage.value = ''
  isLoading.value = true

  if (isLogin.value) {
    // Login
    try {
      const res = await apiFetch('auth/login.php', {
        method: 'POST',
        body: JSON.stringify({ email: formData.value.email, password: formData.value.password })
      })
      const result = await res.json()
      if (result.status === 'success') {
        setAuthSession({ token: result.token, user: result.user })
        router.push(result.user.role === 'admin' ? '/admin' : '/user/dashboard')
      } else {
        errorMessage.value = result.message
      }
    } catch {
      errorMessage.value = 'Không thể kết nối với máy chủ.'
    }
  } else {
    // Register
    if (formData.value.password !== formData.value.password_confirm) {
      errorMessage.value = 'Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.'
      isLoading.value = false
      return
    }
    try {
      const res = await fetch('http://localhost/DoAnEnglish2026/backend/api/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData.value)
      })
      const result = await res.json()
      if (result.status === 'success') {
        successMessage.value = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...'
        setTimeout(() => switchTab(true), 2000)
      } else {
        errorMessage.value = result.message
      }
    } catch {
      errorMessage.value = 'Không thể kết nối với máy chủ.'
    }
  }

  isLoading.value = false
}
</script>

<style scoped>
/* Fade + slide for alert */
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.25s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Expand for name / confirm fields */
.expand-enter-active {
  transition: max-height 0.35s ease, opacity 0.25s ease;
  max-height: 120px;
  opacity: 1;
}
.expand-leave-active {
  transition: max-height 0.25s ease, opacity 0.2s ease;
}
.expand-enter-from {
  max-height: 0;
  opacity: 0;
}
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>
