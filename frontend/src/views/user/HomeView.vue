<template>
  <div>
    <!-- ═══ HERO ═══ -->
    <section class="relative overflow-hidden" style="background: linear-gradient(160deg, #ecfdf5 0%, #ffffff 40%, #f0fdf4 100%)">
      <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-emerald-100/25 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-20 -left-20 w-[400px] h-[400px] bg-emerald-50/40 rounded-full blur-3xl pointer-events-none"></div>

      <div v-if="isLoading" class="relative max-w-7xl mx-auto px-6 py-40 text-center text-slate-400">
        <div class="w-10 h-10 border-[3px] border-slate-200 border-t-emerald-500 rounded-full animate-spin mx-auto mb-3"></div>
        <p class="text-sm">Đang tải...</p>
      </div>

      <div v-else class="relative max-w-7xl mx-auto px-6 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
        <!-- Left: Text -->
        <div class="max-w-xl">
          <div class="inline-flex items-center gap-2 bg-white rounded-full px-4 py-1.5 text-xs font-bold text-emerald-700 border border-emerald-200 shadow-sm mb-7">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            {{ pageData.banner.tagline }}
          </div>
          <h1 class="text-4xl sm:text-5xl lg:text-[3.25rem] font-black text-slate-900 leading-[1.08] tracking-tight mb-6">
            {{ pageData.banner.title }}
          </h1>
          <p class="text-slate-500 leading-relaxed mb-8 text-[15px]">
            {{ pageData.banner.description }}
          </p>

          <!-- Bullet features -->
          <ul class="space-y-3 mb-10">
            <li v-for="item in heroBullets" :key="item" class="flex items-start gap-3 text-sm text-slate-600">
              <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-check text-emerald-600 text-[0.6rem]"></i>
              </span>
              {{ item }}
            </li>
          </ul>

          <div class="flex flex-wrap items-center gap-4">
            <router-link :to="pageData.banner.primary_button_link"
              class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl text-sm font-bold text-white shadow-lg shadow-emerald-500/25 hover:-translate-y-0.5 hover:shadow-emerald-500/35 transition-all duration-200"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
            >
              {{ pageData.banner.primary_button_text }}
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </router-link>
            <router-link to="/courses"
              class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-emerald-700 transition-colors group"
            >
              <span class="w-9 h-9 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-emerald-300 transition-colors">
                <i class="fa-solid fa-play text-emerald-500 text-xs"></i>
              </span>
              Khám phá khóa học
            </router-link>
          </div>
        </div>

        <!-- Right: Image grid (staggered 2-col) -->
        <div class="relative justify-self-center lg:justify-self-end">
          <div class="grid grid-cols-2 gap-3 max-w-[420px]">
            <div class="rounded-2xl overflow-hidden shadow-xl aspect-[3/4]">
              <img :src="pageData.banner.image_url" alt="Students" class="w-full h-full object-cover">
            </div>
            <div class="rounded-2xl overflow-hidden shadow-xl aspect-[3/4] mt-8">
              <img :src="pageData.intro.image_url" alt="Learning" class="w-full h-full object-cover">
            </div>
          </div>
          <div class="absolute -bottom-4 -left-6 bg-white rounded-2xl p-4 shadow-xl border border-slate-100 z-20 flex items-center gap-3 animate-float">
            <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 text-sm">
              <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-800">IELTS 7.0+</p>
              <p class="text-[11px] text-slate-400">Cam kết đầu ra</p>
            </div>
          </div>
          <div class="absolute -top-3 -right-3 bg-white rounded-xl px-4 py-2.5 shadow-xl border border-slate-100 z-20 animate-float-slow">
            <p class="text-[11px] text-slate-400 font-medium">Đánh giá học viên</p>
            <div class="flex items-center gap-1 mt-0.5">
              <i v-for="n in 5" :key="n" class="fa-solid fa-star text-amber-400 text-xs"></i>
              <span class="text-sm font-black text-slate-800 ml-1">4.9</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ PROGRAMS BAR (floating) ═══ -->
    <section class="relative -mt-6 z-10 pb-10">
      <div class="max-w-5xl mx-auto px-6">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 px-6 py-5">
          <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-[0.2em] text-center mb-4">Các chương trình học</p>
          <div class="flex flex-wrap justify-center gap-2">
            <div v-for="feat in features" :key="feat.icon"
              class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-emerald-50 hover:border-emerald-200 transition-all duration-200 cursor-pointer group"
            >
              <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-transform group-hover:scale-110" :class="feat.bg">
                <i :class="['fa-solid', feat.icon, feat.color, 'text-sm']"></i>
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors">{{ feat.title }}</p>
                <p class="text-[10px] text-slate-400 leading-tight">{{ feat.sub }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ GIỚI THIỆU ═══ -->
    <section class="py-24 bg-white scroll-section">
      <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="relative order-2 md:order-1 scroll-fade">
          <div class="absolute -inset-4 rounded-3xl -rotate-1" style="background: linear-gradient(135deg, #d1fae5, #ecfdf5)"></div>
          <img :src="pageData.intro.image_url" alt="Giới thiệu"
            class="relative z-10 w-full object-cover rounded-2xl aspect-video shadow-lg"
          >
          <div class="absolute -bottom-5 -right-5 z-20 bg-white rounded-2xl p-4 shadow-xl border border-slate-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-chart-line"></i>
              </div>
              <div>
                <p class="text-sm font-black text-slate-800">95%</p>
                <p class="text-[11px] text-slate-400">Học viên hài lòng</p>
              </div>
            </div>
          </div>
        </div>

        <div class="order-1 md:order-2 scroll-fade">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span class="w-6 h-[2px] bg-emerald-500 rounded-full"></span> Về chúng tôi
          </p>
          <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-5 leading-snug">{{ pageData.intro.title }}</h2>
          <div class="text-slate-500 space-y-3 leading-relaxed text-[15px]" v-html="pageData.intro.content"></div>
          <div class="grid grid-cols-2 gap-4 mt-8">
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
              <p class="text-2xl font-black text-emerald-700 leading-none">{{ pageData.stats.students }}</p>
              <p class="text-xs text-emerald-600 font-medium mt-1">Học viên đang học</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
              <p class="text-2xl font-black text-slate-900 leading-none">{{ pageData.stats.courses }}</p>
              <p class="text-xs text-slate-500 font-medium mt-1">Khóa học đa dạng</p>
            </div>
          </div>
          <router-link to="/about"
            class="inline-flex items-center gap-2 mt-8 px-6 py-3 rounded-xl border-2 border-slate-200 text-sm font-bold text-slate-700 hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200"
          >
            Tìm hiểu thêm <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ═══ KHÓA HỌC NỔI BẬT ═══ -->
    <section class="py-24 bg-slate-50 scroll-section">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-14 scroll-fade">
          <div>
            <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2 flex items-center gap-2">
              <span class="w-6 h-[2px] bg-emerald-500 rounded-full"></span> Lộ trình học thuật
            </p>
            <h2 class="text-3xl md:text-4xl font-black text-slate-900">Khóa Học Nổi Bật</h2>
            <p class="text-slate-500 text-sm mt-2 max-w-lg">Chọn lộ trình phù hợp và nâng cao trình độ ngoại ngữ của bạn.</p>
          </div>
          <router-link to="/courses"
            class="inline-flex items-center gap-2 text-sm font-bold text-emerald-600 hover:text-emerald-700 transition-colors shrink-0"
          >
            Xem tất cả <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
        </div>

        <div v-if="errorMessage" class="bg-white rounded-2xl border border-red-100 py-10 px-6 text-center">
          <p class="text-sm font-bold text-red-600 mb-3">{{ errorMessage }}</p>
          <button @click="fetchHomeData" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-700 transition-colors">Thử lại</button>
        </div>

        <div v-else-if="pageData.featuredCourses.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="(course, idx) in pageData.featuredCourses" :key="course.id"
            class="bg-white rounded-2xl overflow-hidden border border-slate-100 flex flex-col group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 scroll-fade"
            :style="{ transitionDelay: idx * 100 + 'ms' }"
          >
            <div class="relative overflow-hidden aspect-[16/10]">
              <img :src="course.image_url" :alt="course.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
              >
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <span class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-3 py-1 rounded-lg text-[0.62rem] font-bold text-emerald-700 uppercase tracking-wider shadow-sm border border-emerald-100">
                Level {{ course.level }}
              </span>
            </div>
            <div class="p-5 flex flex-col flex-1">
              <h3 class="text-base font-bold text-slate-800 mb-1.5 line-clamp-1 group-hover:text-emerald-700 transition-colors">
                {{ course.title }}
              </h3>
              <p class="text-xs text-slate-400 mb-4 line-clamp-2 flex-1 leading-relaxed">
                {{ course.description }}
              </p>
              <div class="flex items-center justify-between pt-4 border-t border-slate-50 mt-auto">
                <div>
                  <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-users text-slate-300"></i> {{ course.students_count }} học viên
                  </p>
                  <p class="text-sm font-black text-slate-800 mt-0.5">
                    {{ course.is_free ? 'Miễn phí' : `${formatPrice(course.fee)} VNĐ` }}
                  </p>
                </div>
                <router-link :to="'/course/' + course.id"
                  class="h-10 px-4 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 shadow-md shadow-emerald-200/60 hover:-translate-y-0.5 hover:shadow-emerald-300/60 transition-all duration-200"
                  style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
                >
                  Xem ngay <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>
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
        </div>
      </div>
    </section>

    <!-- ═══ WHY US ═══ -->
    <section class="py-24 bg-white scroll-section">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-14 scroll-fade">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Tại sao chọn chúng tôi</p>
          <h2 class="text-3xl md:text-4xl font-black text-slate-900">Học hiệu quả hơn với hệ thống thông minh</h2>
        </div>
        <!-- 2 cards top row -->
        <div class="grid md:grid-cols-2 gap-6 mb-6">
          <div v-for="(w, idx) in whyUs.slice(0, 2)" :key="w.icon"
            class="p-7 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-200 hover:shadow-lg transition-all duration-300 group scroll-fade"
            :style="{ transitionDelay: idx * 120 + 'ms' }"
          >
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl mb-5 group-hover:scale-110 transition-transform">
              <i :class="['fa-solid', w.icon]"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">{{ w.title }}</h3>
            <p class="text-sm text-slate-400 leading-relaxed">{{ w.desc }}</p>
          </div>
        </div>
        <!-- 1 full-width bottom card -->
        <div v-if="whyUs[2]"
          class="p-7 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-200 hover:shadow-lg transition-all duration-300 group scroll-fade md:flex md:items-center md:gap-8"
        >
          <div class="w-14 h-14 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-2xl mb-5 md:mb-0 shrink-0 group-hover:scale-110 transition-transform">
            <i :class="['fa-solid', whyUs[2].icon]"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">{{ whyUs[2].title }}</h3>
            <p class="text-sm text-slate-400 leading-relaxed">{{ whyUs[2].desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ CTA BANNER ═══ -->
    <section class="relative overflow-hidden py-24 scroll-section" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%)">
      <div class="absolute -left-20 -top-20 w-72 h-72 bg-white/5 rounded-full"></div>
      <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-black/5 rounded-full"></div>
      <div class="absolute inset-0 opacity-5"
        style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 24px 24px;"
      ></div>
      <div class="relative max-w-3xl mx-auto px-6 text-center scroll-fade">
        <div class="inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-1.5 text-xs font-bold text-white border border-white/20 mb-6">
          <i class="fa-solid fa-rocket text-amber-300"></i> Bắt đầu miễn phí
        </div>
        <h2 class="text-3xl md:text-5xl font-black text-white mb-5 leading-tight">
          Sẵn sàng thay đổi<br>trình độ tiếng Anh của bạn?
        </h2>
        <p class="text-emerald-100 mb-10 max-w-lg mx-auto leading-relaxed">
          Tạo tài khoản miễn phí ngay hôm nay. Hàng ngàn học viên đã thay đổi sự nghiệp nhờ hệ thống học tập của chúng tôi.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <router-link to="/register"
            class="inline-flex items-center gap-2.5 px-8 py-4 rounded-xl bg-white text-emerald-700 text-sm font-bold hover:bg-emerald-50 transition-colors shadow-xl shadow-black/10"
          >
            Đăng ký học ngay <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
          <router-link to="/courses"
            class="inline-flex items-center gap-2 px-6 py-4 rounded-xl border-2 border-white/30 text-white text-sm font-bold hover:bg-white/10 transition-colors"
          >
            Xem khóa học
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../../utils/api'

const heroBullets = [
  'Lộ trình học chuyên biệt theo từng trình độ',
  'Hệ thống bài tập tương tác đa dạng',
  'Theo dõi tiến độ học tập chi tiết',
  'Đội ngũ giáo viên giàu kinh nghiệm',
]

const features = [
  { icon: 'fa-route', title: 'Lộ trình cá nhân', sub: 'Theo từng trình độ', bg: 'bg-emerald-50', color: 'text-emerald-600' },
  { icon: 'fa-video', title: 'Video bài giảng', sub: 'HD, dễ theo dõi', bg: 'bg-blue-50', color: 'text-blue-600' },
  { icon: 'fa-clone', title: 'Flashcard thông minh', sub: 'Ghi nhớ nhanh hơn', bg: 'bg-amber-50', color: 'text-amber-600' },
  { icon: 'fa-certificate', title: 'Chứng chỉ học tập', sub: 'Được công nhận', bg: 'bg-purple-50', color: 'text-purple-600' },
]

const whyUs = [
  { icon: 'fa-brain', title: 'Học theo lộ trình thông minh', desc: 'Hệ thống phân tích trình độ và đề xuất lộ trình phù hợp nhất cho từng học viên.' },
  { icon: 'fa-users', title: 'Giáo viên chất lượng cao', desc: 'Đội ngũ giáo viên bản ngữ và giáo viên Việt Nam được đào tạo chuyên sâu, giàu kinh nghiệm.' },
  { icon: 'fa-chart-line', title: 'Theo dõi tiến độ chi tiết', desc: 'Dashboard cá nhân giúp bạn quan sát sự tiến bộ từng ngày và điều chỉnh kế hoạch học tập.' },
]

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
  stats: { students: '0+', courses: '0+' },
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
          ? result.data.featuredCourses.map(normalizeCourse) : []
      }
    } else {
      errorMessage.value = result.message || 'Máy chủ không trả về dữ liệu hợp lệ.'
    }
  } catch (error) {
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

// ── Scroll-triggered fade-in ──
let observer = null
const initScrollObserver = () => {
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible')
        observer.unobserve(entry.target)
      }
    })
  }, { threshold: 0.12 })

  document.querySelectorAll('.scroll-fade').forEach(el => observer.observe(el))
}

onMounted(() => {
  fetchHomeData().then(() => {
    setTimeout(initScrollObserver, 100)
  })
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price)
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
@keyframes float-slow {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
.animate-float { animation: float 3s ease-in-out infinite; }
.animate-float-slow { animation: float-slow 4s ease-in-out infinite; }

/* Scroll fade-in-up */
.scroll-fade {
  opacity: 0;
  transform: translateY(32px);
  transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1),
              transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
}
.scroll-fade.is-visible {
  opacity: 1;
  transform: translateY(0);
}
</style>
