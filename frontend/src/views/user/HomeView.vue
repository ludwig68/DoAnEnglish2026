<template>
  <div class="bg-white">

    <!-- ─── HERO ─── -->
    <section class="relative overflow-hidden">
      <!-- Nền nhẹ -->
      <div class="absolute inset-0 bg-[#f0faf1]"></div>
      <div class="absolute right-0 top-0 w-[50%] h-full bg-[#e6f7e8] rounded-bl-[80px] hidden lg:block"></div>

      <div v-if="isLoading" class="relative max-w-6xl mx-auto px-6 py-32 text-center text-slate-400">
        <div class="w-10 h-10 border-[3px] border-slate-200 border-t-emerald-500 rounded-full animate-spin mx-auto mb-3"></div>
        <p class="text-sm">Đang tải...</p>
      </div>

      <div v-else class="relative max-w-6xl mx-auto px-6 py-16 lg:py-24 grid lg:grid-cols-2 gap-10 items-center">
        <div class="max-w-lg">
          <div class="inline-flex items-center gap-2 bg-white rounded-full px-4 py-1.5 text-xs font-bold text-emerald-700 border border-emerald-200 shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            {{ pageData.banner.tagline }}
          </div>
          <h1 class="text-4xl sm:text-5xl font-black text-slate-900 leading-[1.12] mb-5">
            {{ pageData.banner.title }}
          </h1>
          <p class="text-slate-500 leading-relaxed mb-8 text-[15px]">
            {{ pageData.banner.description }}
          </p>
          <div class="flex flex-wrap items-center gap-4">
            <router-link :to="pageData.banner.primary_button_link"
              class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20">
              {{ pageData.banner.primary_button_text }}
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </router-link>
            <router-link to="/courses" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-emerald-600 transition-colors">
              <i class="fa-solid fa-play-circle text-emerald-500"></i> Khám phá khóa học
            </router-link>
          </div>

          <!-- Mini stats -->
          <div class="flex items-center gap-6 mt-10 pt-8 border-t border-emerald-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 shadow-sm">
                <i class="fa-solid fa-user-graduate"></i>
              </div>
              <div>
                <p class="text-lg font-black text-slate-900 leading-none">{{ pageData.stats.students }}</p>
                <p class="text-[11px] text-slate-400 font-medium">Học viên</p>
              </div>
            </div>
            <div class="w-px h-8 bg-slate-200"></div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 shadow-sm">
                <i class="fa-solid fa-book-open"></i>
              </div>
              <div>
                <p class="text-lg font-black text-slate-900 leading-none">{{ pageData.stats.courses }}</p>
                <p class="text-[11px] text-slate-400 font-medium">Khóa học</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Hero image -->
        <div class="relative justify-self-center lg:justify-self-end">
          <img :src="pageData.banner.image_url" alt="Banner"
            class="relative z-10 w-full max-w-md object-cover rounded-3xl shadow-xl aspect-[4/3]">
          <!-- Decorative cards behind image -->
          <div class="absolute -bottom-4 -left-6 bg-white rounded-2xl p-4 shadow-lg border border-slate-100 z-20 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-sm">
              <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-800">IELTS 7.0+</p>
              <p class="text-[11px] text-slate-400">Cam kết đầu ra</p>
            </div>
          </div>
          <div class="absolute -top-3 -right-3 bg-white rounded-xl px-4 py-2.5 shadow-lg border border-slate-100 z-20">
            <p class="text-[11px] text-slate-400 font-medium">Đánh giá</p>
            <div class="flex items-center gap-1 mt-0.5">
              <i class="fa-solid fa-star text-amber-400 text-xs"></i>
              <i class="fa-solid fa-star text-amber-400 text-xs"></i>
              <i class="fa-solid fa-star text-amber-400 text-xs"></i>
              <i class="fa-solid fa-star text-amber-400 text-xs"></i>
              <i class="fa-solid fa-star text-amber-400 text-xs"></i>
              <span class="text-xs font-bold text-slate-700 ml-1">4.9</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── GIỚI THIỆU ─── -->
    <section class="py-20 bg-white">
      <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="relative order-2 md:order-1">
          <div class="absolute -inset-3 bg-emerald-50 rounded-3xl -rotate-2"></div>
          <img :src="pageData.intro.image_url" alt="Giới thiệu"
            class="relative z-10 w-full object-cover rounded-2xl aspect-video shadow-sm">
        </div>
        <div class="order-1 md:order-2">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span class="w-6 h-[2px] bg-emerald-500 rounded-full"></span> Về chúng tôi
          </p>
          <h2 class="text-3xl font-black text-slate-900 mb-5 leading-snug">{{ pageData.intro.title }}</h2>
          <div class="text-slate-500 space-y-3 leading-relaxed" v-html="pageData.intro.content"></div>
          <router-link to="/about"
            class="inline-flex items-center gap-2 mt-8 px-6 py-3 rounded-xl border-2 border-slate-200 text-sm font-bold text-slate-700 hover:border-emerald-300 hover:text-emerald-700 transition-colors">
            Tìm hiểu thêm <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ─── KHÓA HỌC NỔI BẬT ─── -->
    <section class="py-20 bg-slate-50">
      <div class="max-w-6xl mx-auto px-6">

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
          <div>
            <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2 flex items-center gap-2">
              <span class="w-6 h-[2px] bg-emerald-500 rounded-full"></span> Lộ trình học thuật
            </p>
            <h2 class="text-3xl font-black text-slate-900">Khóa Học Nổi Bật</h2>
            <p class="text-slate-500 text-sm mt-2 max-w-lg">Chọn cho mình một lộ trình phù hợp và nâng cao trình độ ngoại ngữ.</p>
          </div>
          <router-link to="/courses" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-600 hover:text-emerald-700 transition-colors shrink-0">
            Xem tất cả <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
        </div>

        <div v-if="errorMessage" class="bg-white rounded-2xl border border-red-100 py-10 px-6 text-center">
          <p class="text-sm font-bold text-red-600 mb-3">{{ errorMessage }}</p>
          <button @click="fetchHomeData" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-700 transition-colors">
            Thử lại
          </button>
        </div>

        <div v-else-if="pageData.featuredCourses.length > 0" class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="course in pageData.featuredCourses" :key="course.id"
            class="bg-white rounded-2xl overflow-hidden border border-slate-100 flex flex-col group hover:shadow-lg transition-all duration-300">

            <div class="relative overflow-hidden aspect-[16/10]">
              <img :src="course.image_url" :alt="course.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <span class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-3 py-1 rounded-lg text-[0.65rem] font-bold text-slate-600 uppercase tracking-wider shadow-sm">
                Level {{ course.level }}
              </span>
            </div>

            <div class="p-5 flex flex-col flex-1">
              <h3 class="text-lg font-bold text-slate-800 mb-1 line-clamp-1 group-hover:text-emerald-700 transition-colors">
                {{ course.title }}
              </h3>
              <p class="text-[13px] text-slate-400 mb-4 line-clamp-2 flex-1 leading-relaxed">
                {{ course.description }}
              </p>

              <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                <div>
                  <p class="text-xs text-slate-400 flex items-center gap-1">
                    <i class="fa-solid fa-users text-[10px]"></i> {{ course.students_count }} học viên
                  </p>
                  <p class="text-sm font-black text-slate-800 mt-0.5">
                    {{ course.is_free ? 'Miễn phí' : `${formatPrice(course.fee)} VNĐ` }}
                  </p>
                </div>
                <router-link :to="'/course/' + course.id"
                  class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-colors">
                  <i class="fa-solid fa-arrow-right text-sm"></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="bg-white rounded-2xl border border-slate-100 py-16 text-center">
          <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300 text-xl">
            <i class="fa-solid fa-book-open"></i>
          </div>
          <p class="text-sm font-bold text-slate-600">Chưa có khóa học nổi bật</p>
          <p class="text-xs text-slate-400 mt-1">Hệ thống chưa có khóa học nào để hiển thị.</p>
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
        banner: { ...defaultPageData.banner, ...result.data.banner },
        intro: { ...defaultPageData.intro, ...result.data.intro },
        stats: { ...defaultPageData.stats, ...result.data.stats },
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
