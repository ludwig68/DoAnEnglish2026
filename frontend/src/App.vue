<template>
  <div class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    
    <nav v-if="!isDashboard" class="w-full border-b border-slate-200 bg-white/90 backdrop-blur sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            
            <router-link to="/" class="flex items-center gap-2 shrink-0">
                <span class="w-3 h-3 rounded-full bg-[#7AE582] shadow-[0_0_10px_#7AE582]"></span>
                <div class="flex flex-col leading-tight">
                    <span class="font-semibold tracking-wide text-base text-slate-800">
                        Learning English
                    </span>
                    <span class="text-[0.7rem] text-slate-400">
                        Học tiếng Anh miễn phí mỗi ngày
                    </span>
                </div>
            </router-link>

            <div class="hidden md:flex flex-1 items-center justify-center">
                <nav class="flex items-center gap-6 text-sm font-medium">
                    <router-link to="/about" class="text-slate-700 hover:text-[#16a34a] transition">Giới thiệu</router-link>
                    <router-link to="/courses" class="text-slate-700 hover:text-[#16a34a] transition">Khóa học</router-link>
                    <router-link to="/news" class="text-slate-700 hover:text-[#16a34a] transition">Tin tức</router-link>
                    <router-link to="/support" class="text-slate-700 hover:text-[#16a34a] transition">Hỗ trợ</router-link>
                    <router-link to="/contact" class="text-slate-700 hover:text-[#16a34a] transition">Liên hệ</router-link>
                </nav>
            </div>

            <div class="flex items-center gap-4 text-sm shrink-0">
            
                <template v-if="isLoggedIn">
                    <router-link v-if="userRole === 'admin'" to="/admin" class="hidden sm:inline-flex items-center justify-center h-10 gap-2 px-4 rounded-full border border-slate-300 text-slate-700 hover:border-[#7AE582] hover:text-[#7AE582] transition">
                        <i class="fa-solid fa-gauge-high text-sm"></i> Dashboard
                    </router-link>
                    <router-link v-else to="/user/dashboard" class="hidden sm:inline-flex items-center justify-center h-10 gap-2 px-4 rounded-full border border-slate-300 text-slate-700 hover:border-[#7AE582] hover:text-[#16a34a] transition">
                        <i class="fa-solid fa-play text-sm"></i> Vào học
                    </router-link>
                    <span class="hidden sm:inline-flex items-center justify-center h-10 gap-2 px-4 rounded-full bg-slate-100 border border-slate-300 text-slate-700 font-medium">
                        <i class="fa-solid fa-user text-slate-500"></i> {{ username }}
                    </span>
                    <button @click="logout" class="h-10 px-4 inline-flex items-center justify-center rounded-full border border-slate-300 text-slate-700 font-medium hover:bg-red-50 hover:border-red-400 hover:text-red-500 transition">
                        Đăng xuất
                    </button>
                </template>

                <template v-else>
                    <router-link to="/login" class="text-slate-600 font-medium hover:text-[#16a34a] transition">
                        Đăng nhập
                    </router-link>
                    <router-link to="/register" class="h-10 px-6 inline-flex items-center justify-center rounded-full bg-[#7AE582] text-slate-900 font-semibold hover:bg-emerald-300 transition shadow-sm">
                        Đăng ký
                    </router-link>
                </template>
            </div>
        </div>
    </nav>

    <main :class="['flex-1 w-full', isDashboard ? '' : 'max-w-6xl mx-auto']">
    <router-view></router-view>
</main>

    <div v-if="!isDashboard" class="fixed bottom-5 right-5 z-40 flex flex-col items-end gap-3">
        <a
            href="https://zalo.me/0364132169"
            target="_blank"
            rel="noopener noreferrer"
            class="group flex items-center gap-3 rounded-full bg-white px-3 py-3 text-slate-700 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-sky-50 hover:text-sky-700 hover:ring-sky-200"
        >
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-500 text-xs font-black uppercase tracking-wide text-white shadow-sm">
                Zalo
            </span>
        </a>

        <a
            href="tel:+84364132169"
            class="group flex items-center gap-3 rounded-full bg-[#7AE582] px-3 py-3 text-slate-900 shadow-lg shadow-emerald-200/70 transition hover:-translate-y-0.5 hover:bg-emerald-300"
            aria-label="Gọi hotline"
        >
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-base">
                <i class="fa-solid fa-phone-volume"></i>
            </span>
        </a>
    </div>

    <footer v-if="!isDashboard" class="border-t border-slate-200 bg-white mt-8">
        <div class="max-w-6xl mx-auto px-4 py-8 grid gap-6 md:grid-cols-4 text-xs text-slate-600">
            <div class="md:col-span-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-[#7AE582] shadow-[0_0_10px_#7AE582]"></span>
                    <span class="font-semibold tracking-wide text-sm text-slate-800">English Learning</span>
                </div>
                <p class="text-[0.75rem] leading-relaxed">
                    Hệ thống học tiếng Anh miễn phí với từ vựng, flashcard, bài luyện tập nghe - nhìn - điền từ. Phù hợp cho người mới bắt đầu đến luyện thi.
                </p>
            </div>

            <div>
                <h3 class="text-[0.8rem] font-semibold text-slate-800 mb-2">Liên kết nhanh</h3>
                <ul class="space-y-2">
                    <li><router-link to="/" class="hover:text-[#16a34a] flex items-center gap-2"><i class="fa-solid fa-house text-[0.7rem] text-slate-300"></i> Trang chủ</router-link></li>
                    <li><router-link to="/learn" class="hover:text-[#16a34a] flex items-center gap-2"><i class="fa-solid fa-road text-[0.7rem] text-slate-300"></i> Lộ trình Level</router-link></li>
                    <li><router-link to="/flashcard" class="hover:text-[#16a34a] flex items-center gap-2"><i class="fa-regular fa-clone text-[0.7rem] text-slate-300"></i> Flashcard</router-link></li>
                    <li><router-link to="/practice" class="hover:text-[#16a34a] flex items-center gap-2"><i class="fa-solid fa-clipboard-question text-[0.7rem] text-slate-300"></i> Practice</router-link></li>
                    <li><router-link to="/login" class="hover:text-[#16a34a] flex items-center gap-2"><i class="fa-solid fa-right-to-bracket text-[0.7rem] text-slate-300"></i> Đăng nhập</router-link></li>
                    <li><router-link to="/register" class="hover:text-[#16a34a] flex items-center gap-2"><i class="fa-solid fa-user-plus text-[0.7rem] text-slate-300"></i> Đăng ký</router-link></li>
                </ul>
            </div>

            <div>
                <h3 class="text-[0.8rem] font-semibold text-slate-800 mb-2">Liên hệ</h3>
                <ul class="space-y-1">
                    <li class="flex items-start gap-2"><i class="fa-solid fa-envelope mt-[2px] text-slate-400"></i><span>zayluon@gmail.com</span></li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-phone mt-[2px] text-slate-400"></i><span>+84 364 132 169</span></li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-location-dot mt-[2px] text-slate-400"></i><span>Hồ Chí Minh, Việt Nam</span></li>
                </ul>
                <div class="flex gap-3 mt-3 text-slate-400">
                    <a href="#" class="hover:text-[#16a34a]"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="hover:text-[#16a34a]"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" class="hover:text-[#16a34a]"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <div>
                <h3 class="text-[0.8rem] font-semibold text-slate-800 mb-2">Bản đồ</h3>
                <div class="h-32 sm:h-40 rounded-lg overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.9544104258935!2d106.67525717589443!3d10.737997189408455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f62a90e5dbd%3A0x674d5126513db295!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2jhu4cgU8OgaSBHw7Ju!5e0!3m2!1svi!2s!4v1774721804211!5m2!1svi!2s" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100">
            <div class="max-w-6xl mx-auto px-4 py-3 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-[0.7rem] text-slate-400">
                    © {{ currentYear }} English Learning System. Built with Ludwig.
                </p>
                <p class="text-[0.7rem] text-slate-400">
                    <i class="fa-solid fa-heart text-[#7AE582] mr-1"></i> Học mỗi ngày một chút là tiến bộ.
                </p>
            </div>
        </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authSession, clearAuthSession } from './utils/auth'

const route = useRoute()
const router = useRouter()

const isDashboard = computed(() => route.path.startsWith('/user/dashboard') || route.path.startsWith('/admin'))
const currentUser = computed(() => authSession.value?.user ?? null)
const isLoggedIn = computed(() => Boolean(currentUser.value))
const userRole = computed(() => currentUser.value?.role ?? '')
const username = computed(() => currentUser.value?.full_name ?? '')
const currentYear = new Date().getFullYear()

const searchQuery = ref('')
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.push({ path: '/search', query: { q: searchQuery.value } })
    }
}

const logout = () => {
    clearAuthSession()
    router.push('/login')
}
</script>
