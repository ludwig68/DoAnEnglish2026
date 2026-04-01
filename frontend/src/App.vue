<template>
  <div class="bg-white text-slate-900 min-h-screen flex flex-col">

    <!-- ═══ NAVBAR ═══ -->
    <header
      v-if="!isDashboard"
      class="sticky top-0 z-40 w-full border-b border-slate-100 bg-white/95 backdrop-blur-md shadow-sm"
    >
      <div class="max-w-7xl mx-auto px-5 h-16 flex items-center justify-between gap-4">

        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2.5 shrink-0 group">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#7AE582] to-emerald-600 flex items-center justify-center shadow-md shadow-emerald-200 group-hover:shadow-emerald-300 transition-shadow">
            <i class="fa-solid fa-graduation-cap text-white text-sm"></i>
          </div>
          <div class="flex flex-col leading-tight">
            <span class="font-black tracking-tight text-slate-900 text-[15px]">English Learning</span>
            <span class="text-[0.62rem] text-emerald-600 font-semibold">Học mỗi ngày · Tiến bộ mỗi ngày</span>
          </div>
        </router-link>

        <!-- Nav links (desktop) -->
        <nav class="hidden md:flex flex-1 items-center justify-center gap-1">
          <router-link to="/about"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-600 hover:text-emerald-700 hover:bg-emerald-50"
            active-class="text-emerald-700 bg-emerald-50"
          >Giới thiệu</router-link>
          <router-link to="/courses"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-600 hover:text-emerald-700 hover:bg-emerald-50"
            active-class="text-emerald-700 bg-emerald-50"
          >Khóa học</router-link>
          <router-link to="/support"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-600 hover:text-emerald-700 hover:bg-emerald-50"
            active-class="text-emerald-700 bg-emerald-50"
          >Hỗ trợ</router-link>
          <router-link to="/contact"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-slate-600 hover:text-emerald-700 hover:bg-emerald-50"
            active-class="text-emerald-700 bg-emerald-50"
          >Liên hệ</router-link>
        </nav>

        <!-- Auth buttons -->
        <div class="flex items-center gap-2.5 shrink-0">
          <template v-if="isLoggedIn">
            <router-link
              v-if="userRole === 'admin'"
              to="/admin"
              class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50 transition-all"
            >
              <i class="fa-solid fa-gauge-high text-xs"></i> Dashboard
            </router-link>
            <router-link
              v-else
              to="/user/dashboard"
              class="hidden sm:inline-flex items-center gap-1.5 h-9 px-4 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50 transition-all"
            >
              <i class="fa-solid fa-play text-xs text-emerald-500"></i> Vào học
            </router-link>
            <span class="hidden sm:inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold">
              <i class="fa-solid fa-circle-user text-emerald-500"></i> {{ username }}
            </span>
            <button
              @click="logout"
              class="h-9 px-4 inline-flex items-center rounded-lg text-sm font-semibold text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors border border-transparent hover:border-red-200"
            >
              <i class="fa-solid fa-right-from-bracket text-xs"></i>
              <span class="hidden sm:inline ml-1.5">Đăng xuất</span>
            </button>
          </template>

          <template v-else>
            <router-link to="/login"
              class="h-9 px-4 inline-flex items-center text-sm font-semibold text-slate-600 hover:text-emerald-700 transition-colors"
            >Đăng nhập</router-link>
            <router-link to="/register"
              class="h-9 px-5 inline-flex items-center rounded-xl text-sm font-bold text-white shadow-md shadow-emerald-200 hover:-translate-y-px hover:shadow-emerald-300 transition-all"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
            >Đăng ký miễn phí</router-link>
          </template>
        </div>

      </div>
    </header>

    <!-- Main content (no max-width — each page controls its own) -->
    <main class="flex-1 w-full">
      <router-view></router-view>
    </main>

    <!-- Floating contact buttons -->
    <div v-if="!isDashboard" class="fixed bottom-5 right-5 z-40 flex flex-col items-end gap-3">
      <a
        href="https://zalo.me/0364132169"
        target="_blank"
        rel="noopener noreferrer"
        class="group flex items-center gap-3 rounded-full bg-white px-3 py-3 text-slate-700 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-sky-50 hover:text-sky-700 hover:ring-sky-200"
      >
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-500 text-xs font-black uppercase tracking-wide text-white shadow-sm">Zalo</span>
      </a>
      <a
        href="tel:+84364132169"
        class="group flex items-center gap-3 rounded-full px-3 py-3 text-white shadow-lg shadow-emerald-300/50 transition hover:-translate-y-0.5"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
        aria-label="Gọi hotline"
      >
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-base">
          <i class="fa-solid fa-phone-volume"></i>
        </span>
      </a>
    </div>

    <!-- ═══ FOOTER ═══ -->
    <footer v-if="!isDashboard" class="bg-white border-t border-slate-100 mt-auto">
      <div class="max-w-7xl mx-auto px-6 pt-12 pb-8">
        <div class="grid gap-10 md:grid-cols-4">

          <!-- Brand -->
          <div class="md:col-span-1">
            <div class="flex items-center gap-2.5 mb-4">
              <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
                <i class="fa-solid fa-graduation-cap text-white text-sm"></i>
              </div>
              <span class="font-black text-slate-800 text-base tracking-tight">English Learning</span>
            </div>
            <p class="text-sm leading-relaxed text-slate-500">
              Hệ thống học tiếng Anh thông minh với lộ trình cá nhân hoá, từ vựng, flashcard và bài luyện tập tương tác.
            </p>
            <div class="flex gap-2 mt-5">
              <a href="#" class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:border-emerald-200 hover:bg-emerald-50 transition-all">
                <i class="fa-brands fa-facebook-f text-sm"></i>
              </a>
              <a href="#" class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-all">
                <i class="fa-brands fa-youtube text-sm"></i>
              </a>
              <a href="#" class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:border-slate-300 hover:bg-slate-100 transition-all">
                <i class="fa-brands fa-tiktok text-sm"></i>
              </a>
            </div>
          </div>

          <!-- Links -->
          <div>
            <h3 class="text-slate-800 font-bold text-[0.7rem] mb-4 uppercase tracking-widest">Điều hướng</h3>
            <ul class="space-y-2.5 text-sm">
              <li><router-link to="/" class="text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2"><i class="fa-solid fa-house text-[0.6rem] text-slate-300"></i>Trang chủ</router-link></li>
              <li><router-link to="/about" class="text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2"><i class="fa-solid fa-circle-info text-[0.6rem] text-slate-300"></i>Giới thiệu</router-link></li>
              <li><router-link to="/courses" class="text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2"><i class="fa-solid fa-book-open text-[0.6rem] text-slate-300"></i>Khóa học</router-link></li>
              <li><router-link to="/support" class="text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2"><i class="fa-solid fa-headset text-[0.6rem] text-slate-300"></i>Hỗ trợ</router-link></li>
              <li><router-link to="/contact" class="text-slate-500 hover:text-emerald-600 transition-colors flex items-center gap-2"><i class="fa-solid fa-envelope text-[0.6rem] text-slate-300"></i>Liên hệ</router-link></li>
            </ul>
          </div>

          <!-- Contact -->
          <div>
            <h3 class="text-slate-800 font-bold text-[0.7rem] mb-4 uppercase tracking-widest">Liên hệ</h3>
            <ul class="space-y-3 text-sm text-slate-500">
              <li class="flex items-start gap-2.5">
                <i class="fa-solid fa-envelope mt-[3px] text-emerald-500 text-xs shrink-0"></i>
                <span>zayluon@gmail.com</span>
              </li>
              <li class="flex items-start gap-2.5">
                <i class="fa-solid fa-phone mt-[3px] text-emerald-500 text-xs shrink-0"></i>
                <span>+84 364 132 169</span>
              </li>
              <li class="flex items-start gap-2.5">
                <i class="fa-solid fa-location-dot mt-[3px] text-emerald-500 text-xs shrink-0"></i>
                <span>Hồ Chí Minh, Việt Nam</span>
              </li>
            </ul>
          </div>

          <!-- Map -->
          <div>
            <h3 class="text-slate-800 font-bold text-[0.7rem] mb-4 uppercase tracking-widest">Bản đồ</h3>
            <div class="h-36 rounded-xl overflow-hidden border border-slate-200">
              <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.9544104258935!2d106.67525717589443!3d10.737997189408455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f62a90e5dbd%3A0x674d5126513db295!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2jhu4cgU8OgaSBHw7Ru!5e0!3m2!1svi!2s!4v1774721804211!5m2!1svi!2s"
                width="100%" height="100%" style="border:0" allowfullscreen="" loading="lazy"
              ></iframe>
            </div>
          </div>

        </div>
      </div>

      <!-- Bottom bar -->
      <div class="border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
          <p>© {{ currentYear }} English Learning System. All rights reserved.</p>
          <p><i class="fa-solid fa-heart text-emerald-400 mr-1"></i> Học mỗi ngày một chút là tiến bộ.</p>
        </div>
      </div>
    </footer>

  </div>
  <AppConfirmDialog />
  <AppToastStack />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppConfirmDialog from './components/AppConfirmDialog.vue'
import AppToastStack from './components/AppToastStack.vue'
import { authSession, clearAuthSession } from './utils/auth'

const route = useRoute()
const router = useRouter()

const isDashboard = computed(() => route.path.startsWith('/user/dashboard') || route.path.startsWith('/admin'))
const currentUser = computed(() => authSession.value?.user ?? null)
const isLoggedIn = computed(() => Boolean(currentUser.value))
const userRole = computed(() => currentUser.value?.role ?? '')
const username = computed(() => currentUser.value?.full_name ?? '')
const currentYear = new Date().getFullYear()

const logout = () => {
  clearAuthSession()
  router.push('/login')
}
</script>

