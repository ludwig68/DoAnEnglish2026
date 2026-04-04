<template>
  <div>
    <!-- ═══ HERO ═══ -->
    <section class="relative overflow-hidden py-20 lg:py-28"
      style="background: linear-gradient(160deg, #f0fdf4 0%, #ffffff 50%, #f0fdf4 100%)">
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-200/25 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-emerald-100/35 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative max-w-7xl mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 bg-white rounded-full px-4 py-1.5 text-xs font-bold text-emerald-700 border border-emerald-200 shadow-sm mb-6">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          {{ pageData.hero.tagline }}
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-slate-900 leading-[1.08] tracking-tight mb-6 max-w-3xl mx-auto">
          {{ pageData.hero.title }}
          <span class="text-emerald-600">{{ pageData.hero.highlight }}</span>
        </h1>
        <p class="text-[15px] md:text-base text-slate-500 max-w-xl mx-auto leading-relaxed mb-10">
          {{ pageData.hero.description }}
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <router-link to="/register"
            class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl text-sm font-bold text-white shadow-lg shadow-emerald-500/25 hover:-translate-y-0.5 hover:shadow-emerald-500/35 transition-all"
            style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
            Bắt đầu học ngay <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
          <router-link to="/courses"
            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl border-2 border-slate-200 text-sm font-bold text-slate-600 hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50 transition-all bg-white">
            <i class="fa-solid fa-play text-emerald-500 text-xs"></i> Xem khóa học
          </router-link>
        </div>
      </div>
    </section>

    <!-- ─── CÂU CHUYỆN ─── -->
    <section class="py-20 bg-white scroll-section">
      <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="relative scroll-fade">
          <div class="absolute -inset-3 bg-emerald-50 rounded-3xl -rotate-2"></div>
          <img :src="pageData.story.image_url" alt="Sứ mệnh"
            class="relative z-10 w-full h-auto object-cover rounded-2xl aspect-[4/3]">
          <div class="absolute -bottom-4 -right-4 bg-white rounded-xl p-3.5 shadow-lg border border-slate-100 z-20 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
              <i class="fa-solid fa-rocket"></i>
            </div>
            <div>
              <p class="text-xl font-black text-slate-900 leading-none">{{ pageData.story.year }}</p>
              <p class="text-[0.6rem] font-bold text-slate-400 uppercase tracking-wider">Năm thành lập</p>
            </div>
          </div>
        </div>

        <div class="scroll-fade" style="transition-delay: 150ms">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span class="w-6 h-[2px] bg-emerald-500 rounded-full"></span> Câu chuyện
          </p>
          <h2 class="text-3xl font-black text-slate-900 mb-5 leading-snug">{{ pageData.story.title }}</h2>
          <div class="space-y-3 text-slate-500 leading-relaxed text-[15px]" v-html="pageData.story.content"></div>
          <div class="flex items-center gap-6 mt-10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-user-graduate"></i>
              </div>
              <div>
                <p class="text-lg font-black text-slate-900 leading-none">{{ pageData.stats.students }}</p>
                <p class="text-[11px] text-slate-400 font-medium">Học viên</p>
              </div>
            </div>
            <div class="w-px h-8 bg-slate-200"></div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-book-open"></i>
              </div>
              <div>
                <p class="text-lg font-black text-slate-900 leading-none">{{ pageData.stats.courses }}</p>
                <p class="text-[11px] text-slate-400 font-medium">Khóa học</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── GIÁ TRỊ CỐT LÕI ─── -->
    <section class="py-20 bg-slate-50 scroll-section">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-14 scroll-fade">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Giá trị cốt lõi</p>
          <h2 class="text-3xl font-black text-slate-900 mb-3">{{ pageData.values_title }}</h2>
          <p class="text-slate-500 text-sm leading-relaxed">{{ pageData.values_description }}</p>
        </div>

        <div v-if="pageData.values.length > 0" class="grid md:grid-cols-3 gap-6">
          <div v-for="(item, index) in pageData.values" :key="index"
            class="bg-white p-7 rounded-2xl border border-slate-100 hover:shadow-lg hover:border-emerald-100 transition-all duration-300 group scroll-fade"
            :style="{ transitionDelay: index * 120 + 'ms' }">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl mb-5 transition-transform duration-300 group-hover:scale-110"
              :class="[item.icon_background || 'bg-emerald-50', item.icon_color || 'text-emerald-600']">
              <i :class="['fa-solid', item.icon || 'fa-star']"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">{{ item.title }}</h3>
            <p class="text-sm text-slate-400 leading-relaxed">{{ item.description }}</p>
          </div>
        </div>

        <div v-else class="bg-white rounded-2xl border border-slate-100 py-12 text-center">
          <p class="text-sm text-slate-400">Nội dung đang được cập nhật.</p>
        </div>
      </div>
    </section>

    <!-- ─── ĐỘI NGŨ (redesigned with colored accents) ─── -->
    <section v-if="pageData.team.length > 0" class="py-20 bg-white scroll-section">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-14 scroll-fade">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Đội ngũ</p>
          <h2 class="text-3xl font-black text-slate-900 mb-2">{{ pageData.team_title }}</h2>
          <p class="text-sm text-slate-400">Đội ngũ gồm {{ pageData.team.length }} thành viên tận tâm</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="(member, idx) in pageData.team" :key="member.id"
            class="text-center group scroll-fade"
            :style="{ transitionDelay: idx * 100 + 'ms' }">
            <!-- Photo with colored ring -->
            <div class="relative w-32 h-32 mx-auto mb-5">
              <div class="absolute inset-0 rounded-full opacity-60 group-hover:opacity-100 transition-opacity duration-500"
                :style="{ background: teamColors[idx % teamColors.length] }"></div>
              <div class="absolute inset-[3px] rounded-full overflow-hidden bg-white">
                <img :src="member.avatar" :alt="member.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
              </div>
            </div>
            <h3 class="text-sm font-bold text-slate-800">{{ member.name }}</h3>
            <p class="text-xs font-bold text-emerald-600 mt-0.5">{{ member.role }}</p>
            <div class="flex justify-center gap-2.5 mt-3 text-slate-300">
              <a href="#" class="hover:text-emerald-600 transition-colors"><i class="fa-brands fa-linkedin text-sm"></i></a>
              <a href="#" class="hover:text-emerald-600 transition-colors"><i class="fa-brands fa-github text-sm"></i></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── BÁO CHÍ ─── -->
    <section class="py-16 bg-slate-50 scroll-section">
      <div class="max-w-4xl mx-auto px-6 text-center scroll-fade">
        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Được đánh giá tích cực bởi</p>
        <h3 class="text-xl font-black text-slate-800 mb-10">Báo chí viết về English Learning</h3>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-14 opacity-50 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
          <span v-for="media in pressLogos" :key="media" class="text-lg md:text-xl font-black text-slate-700 tracking-tight">
            {{ media }}
          </span>
        </div>
      </div>
    </section>

    <!-- ─── CTA ─── -->
    <section class="py-20 relative overflow-hidden scroll-section" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%)">
      <div class="absolute -left-20 -top-20 w-60 h-60 bg-white/5 rounded-full"></div>
      <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-black/5 rounded-full"></div>
      <div class="relative max-w-7xl mx-auto px-6 text-center scroll-fade">
        <h2 class="text-3xl md:text-4xl font-black text-white mb-5 leading-tight">{{ pageData.cta.title }}</h2>
        <p class="text-emerald-100 mb-10 max-w-lg mx-auto leading-relaxed">
          {{ pageData.cta.description }}
        </p>
        <router-link :to="pageData.cta.button_link"
          class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white text-emerald-700 text-sm font-bold hover:bg-emerald-50 transition-colors shadow-xl shadow-black/10">
          {{ pageData.cta.button_text }} <i class="fa-solid fa-arrow-right text-xs"></i>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../../utils/api'

const teamColors = [
  'linear-gradient(135deg, #7ae582, #16a34a)',
  'linear-gradient(135deg, #38bdf8, #0284c7)',
  'linear-gradient(135deg, #fbbf24, #f59e0b)',
  'linear-gradient(135deg, #a78bfa, #7c3aed)',
]

const pressLogos = ['HTV9', 'VTV7', 'Thanh Niên', 'Tuổi Trẻ', 'VnExpress']

const defaultPageData = {
  hero: {
    tagline: 'Câu chuyện của chúng tôi',
    title: 'Định hình lại cách bạn ',
    highlight: 'học Tiếng Anh',
    description: 'English Learning được xây dựng để việc học tiếng Anh trở nên dễ tiếp cận, dễ theo dõi và phù hợp hơn với người học Việt Nam.'
  },
  story: {
    image_url: 'https://placehold.co/800x600/f1f5f9/94a3b8?text=Our+Mission',
    title: 'Xóa bỏ rào cản ngôn ngữ cho người Việt',
    content: '<p>Hệ thống kết hợp khóa học, lộ trình và bài luyện tập để hỗ trợ việc học hiệu quả hơn.</p>',
    year: '2026'
  },
  stats: {
    students: '0+',
    courses: '0+'
  },
  values_title: 'Điều gì làm chúng tôi khác biệt?',
  values_description: 'Nội dung và công cụ đều tập trung vào hiệu quả học tập thực tế.',
  values: [],
  team_title: 'Những người đứng sau dự án',
  team: [],
  cta: {
    title: 'Sẵn sàng để thay đổi trình độ của bạn?',
    description: 'Tạo tài khoản miễn phí ngay hôm nay và bắt đầu học cùng hệ thống.',
    button_text: 'Đăng ký học ngay',
    button_link: '/register'
  }
}

const pageData = ref(defaultPageData)

const fetchAboutData = async () => {
  try {
    const response = await apiFetch('public/about_data.php')
    const result = await response.json()

    if (result.status === 'success') {
      pageData.value = {
        ...defaultPageData,
        ...result.data,
        hero: { ...defaultPageData.hero, ...result.data.hero },
        story: { ...defaultPageData.story, ...result.data.story },
        stats: { ...defaultPageData.stats, ...result.data.stats },
        cta: { ...defaultPageData.cta, ...result.data.cta },
        values: Array.isArray(result.data.values) ? result.data.values : defaultPageData.values,
        team: Array.isArray(result.data.team) ? result.data.team : defaultPageData.team
      }
    }
  } catch (error) {
    console.error('Lỗi tải dữ liệu trang giới thiệu:', error)
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
  fetchAboutData().then(() => {
    setTimeout(initScrollObserver, 100)
  })
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})
</script>

<style scoped>
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
