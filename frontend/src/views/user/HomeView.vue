<template>
  <div class="pb-16">
    
    <section class="relative bg-slate-50 pt-10 pb-16 lg:pt-16 lg:pb-24 overflow-hidden">
      <div v-if="isLoading" class="max-w-6xl mx-auto px-4 py-16 text-center text-slate-500">
        <div class="w-16 h-16 rounded-full border-4 border-emerald-100 border-t-[#16a34a] animate-spin mx-auto mb-4"></div>
        <p>Đang tải dữ liệu trang chủ...</p>
      </div>

      <div v-else class="max-w-6xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">
        <div class="relative z-10 animate__animated animate__fadeInLeft">
          <span class="inline-block py-1 px-3 rounded-full bg-emerald-100 text-[#16a34a] text-sm font-semibold mb-4 border border-emerald-200">
            {{ pageData.banner.tagline }}
          </span>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight mb-6">
            {{ pageData.banner.title }}
          </h1>
          <p class="text-lg text-slate-600 mb-8 max-w-lg leading-relaxed">
            {{ pageData.banner.description }}
          </p>
          <div class="flex flex-wrap gap-4">
            <router-link :to="pageData.banner.primary_button_link" class="px-8 py-3.5 rounded-full bg-[#7AE582] text-slate-900 font-bold hover:bg-emerald-300 transition shadow-lg hover:shadow-xl flex items-center gap-2">
              {{ pageData.banner.primary_button_text }} <i class="fa-solid fa-arrow-right"></i>
            </router-link>
          </div>
        </div>
        
        <div class="relative animate__animated animate__fadeInRight">
          <div class="absolute -inset-1 bg-gradient-to-r from-[#7AE582] to-teal-400 rounded-[2.5rem] blur-lg opacity-30"></div>
          <img :src="pageData.banner.image_url" alt="Banner" class="relative z-10 w-full object-cover rounded-[2rem] shadow-2xl aspect-[4/3] border-4 border-white">
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div class="order-2 md:order-1">
          <img :src="pageData.intro.image_url" alt="Giới thiệu" class="w-full object-cover rounded-2xl shadow-lg aspect-video">
        </div>
        <div class="order-1 md:order-2">
          <h2 class="text-sm font-bold text-[#16a34a] uppercase tracking-wider mb-2">Về chúng tôi</h2>
          <h3 class="text-3xl font-bold text-slate-900 mb-4">{{ pageData.intro.title }}</h3>
          <div class="text-slate-600 space-y-4 leading-relaxed" v-html="pageData.intro.content"></div>
          <div class="mt-8 grid grid-cols-2 gap-6">
            <div class="border-l-4 border-[#7AE582] pl-4">
              <p class="text-3xl font-black text-slate-800">{{ pageData.stats.students }}</p>
              <p class="text-sm text-slate-500 font-medium">Học viên tin tưởng</p>
            </div>
            <div class="border-l-4 border-[#7AE582] pl-4">
              <p class="text-3xl font-black text-slate-800">{{ pageData.stats.courses }}</p>
              <p class="text-sm text-slate-500 font-medium">Khóa học chất lượng</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-slate-50">
      <div class="max-w-6xl mx-auto px-4">
        
        <div class="text-center max-w-2xl mx-auto mb-12">
          <h2 class="text-sm font-bold text-[#16a34a] uppercase tracking-wider mb-2">Lộ trình học thuật</h2>
          <h3 class="text-3xl font-bold text-slate-900 mb-4">Các Khóa Học Nổi Bật</h3>
          <p class="text-slate-600">Chọn cho mình một lộ trình phù hợp để bắt đầu nâng cao trình độ ngoại ngữ ngay hôm nay.</p>
        </div>

        <div v-if="errorMessage" class="bg-white rounded-2xl border border-red-200 py-10 px-6 text-center shadow-sm mb-8">
          <h3 class="text-lg font-bold text-slate-700 mb-2">Không tải được dữ liệu trang chủ</h3>
          <p class="text-sm text-slate-500 mb-4">{{ errorMessage }}</p>
          <button @click="fetchHomeData" class="px-4 py-2 rounded-xl bg-[#7AE582] text-slate-900 font-bold">
            Thử lại
          </button>
        </div>

        <div v-else-if="pageData.featuredCourses.length > 0" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          
          <div v-for="course in pageData.featuredCourses" :key="course.id" 
               class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 group flex flex-col">
            
            <div class="relative overflow-hidden aspect-video">
              <img :src="course.image_url" :alt="course.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
              <div class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-slate-700 shadow-sm">
                Level {{ course.level }}
              </div>
            </div>

            <div class="p-6 flex flex-col flex-1">
              <h4 class="text-xl font-bold text-slate-800 mb-2 line-clamp-1 group-hover:text-[#16a34a] transition">
                {{ course.title }}
              </h4>
              <p class="text-sm text-slate-500 mb-6 line-clamp-3 flex-1">
                {{ course.description }}
              </p>
              
              <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto gap-4">
                <div class="text-xs text-slate-400">
                  <span class="font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-users"></i> {{ course.students_count }} học viên
                  </span>
                  <span class="block mt-1 font-bold text-slate-700">
                    {{ course.is_free ? 'Miễn phí' : `${formatPrice(course.fee)} VNĐ` }}
                  </span>
                </div>
                <router-link :to="'/course/' + course.id" class="text-sm font-bold text-[#16a34a] bg-emerald-50 px-4 py-2 rounded-lg hover:bg-[#7AE582] hover:text-slate-900 transition">
                  Học ngay
                </router-link>
              </div>
            </div>

          </div>
        </div>

        <div v-else class="bg-white rounded-2xl border border-slate-200 py-16 text-center shadow-sm">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl">
            <i class="fa-solid fa-book-open"></i>
          </div>
          <h3 class="text-lg font-bold text-slate-700 mb-1">Chưa có khóa học nổi bật</h3>
          <p class="text-sm text-slate-500">Hiện tại hệ thống chưa có khóa học nào để hiển thị ở trang chủ.</p>
        </div>

        <div class="text-center mt-12">
          <router-link to="/courses" class="inline-flex items-center gap-2 font-bold text-slate-600 hover:text-[#16a34a] transition">
            Xem tất cả khóa học <i class="fa-solid fa-arrow-right-long"></i>
          </router-link>
        </div>

      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'

const defaultPageData = {
  banner: {
    tagline: 'Năm học 2026',
    title: 'Chinh phục tiếng Anh theo cách của bạn',
    description: 'Hệ thống học tập thông minh giúp bạn học đúng lộ trình, đúng trình độ và theo dõi tiến độ rõ ràng.',
    image_url: 'https://placehold.co/800x600/e2e8f0/64748b?text=English+Learning',
    primary_button_text: 'Bắt đầu miễn phí',
    primary_button_link: '/register'
  },
  intro: {
    title: 'Môi trường học tập lý tưởng dành cho mọi cấp độ',
    content: '<p>English Learning kết nối khóa học, bài học và lộ trình học tập trong một giao diện đơn giản, dễ theo dõi.</p>',
    image_url: 'https://placehold.co/800x450/f1f5f9/94a3b8?text=Learning+Environment'
  },
  stats: {
    students: '0+',
    courses: '0+'
  },
  featuredCourses: []
}

const pageData = ref(defaultPageData)
const isLoading = ref(true)
const errorMessage = ref('')

const normalizeCourse = (course) => ({
  id: Number(course.id),
  title: course.title ?? '',
  description: course.description ?? '',
  image_url: course.image_url ?? 'https://placehold.co/600x400/e2e8f0/64748b?text=Course',
  level: course.level ?? 'N/A',
  fee: Number(course.fee ?? 0),
  students_count: Number(course.students_count ?? 0),
  is_free: Boolean(course.is_free ?? Number(course.fee ?? 0) === 0)
})

const fetchHomeData = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await apiFetch('public/home_data.php')
    const result = await response.json()

    if (result.status === 'success') {
      pageData.value = {
        banner: {
          ...defaultPageData.banner,
          ...result.data.banner
        },
        intro: {
          ...defaultPageData.intro,
          ...result.data.intro
        },
        stats: {
          ...defaultPageData.stats,
          ...result.data.stats
        },
        featuredCourses: Array.isArray(result.data.featuredCourses)
          ? result.data.featuredCourses.map(normalizeCourse)
          : []
      }
    } else {
      errorMessage.value = result.message || 'Máy chủ không trả về dữ liệu hợp lệ.'
    }
  } catch (error) {
    console.error('Lỗi tải dữ liệu trang chủ:', error)
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchHomeData()
})

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price)
</script>
