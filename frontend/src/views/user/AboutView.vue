<template>
  <div class="bg-white">

    <!-- ─── HERO ─── -->
    <section class="relative bg-[#f0faf1] overflow-hidden">
      <div class="absolute left-0 bottom-0 w-72 h-72 bg-emerald-200/40 rounded-full blur-3xl"></div>
      <div class="absolute right-0 top-0 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl"></div>

      <div class="relative max-w-6xl mx-auto px-6 py-20 lg:py-28 text-center">
        <div class="inline-flex items-center gap-2 bg-white rounded-full px-4 py-1.5 text-xs font-bold text-emerald-700 border border-emerald-200 shadow-sm mb-6">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          {{ pageData.hero.tagline }}
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-slate-900 leading-[1.1] tracking-tight mb-6 max-w-3xl mx-auto">
          {{ pageData.hero.title }}
          <span class="text-emerald-600">{{ pageData.hero.highlight }}</span>
        </h1>
        <p class="text-[15px] md:text-base text-slate-500 max-w-xl mx-auto leading-relaxed mb-10">
          {{ pageData.hero.description }}
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <router-link to="/register"
            class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20">
            Bắt đầu học ngay <i class="fa-solid fa-arrow-right text-xs"></i>
          </router-link>
          <router-link to="/courses"
            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl border-2 border-slate-200 text-sm font-bold text-slate-600 hover:border-emerald-300 hover:text-emerald-600 transition-colors bg-white">
            <i class="fa-solid fa-play text-emerald-500 text-xs"></i> Xem khóa học
          </router-link>
        </div>
      </div>
    </section>

    <!-- ─── CÂU CHUYỆN ─── -->
    <section class="py-20 bg-white">
      <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div class="relative">
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

        <div>
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
    <section class="py-20 bg-slate-50">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-14">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Giá trị cốt lõi</p>
          <h2 class="text-3xl font-black text-slate-900 mb-3">{{ pageData.values_title }}</h2>
          <p class="text-slate-500 text-sm leading-relaxed">{{ pageData.values_description }}</p>
        </div>

        <div v-if="pageData.values.length > 0" class="grid md:grid-cols-3 gap-6">
          <div v-for="(item, index) in pageData.values" :key="index"
            class="bg-white p-7 rounded-2xl border border-slate-100 hover:shadow-lg hover:border-emerald-100 transition-all duration-300 group">
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

    <!-- ─── ĐỘI NGŨ ─── -->
    <section v-if="pageData.team.length > 0" class="py-20 bg-white">
      <div class="max-w-6xl mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-14">
          <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3">Đội ngũ</p>
          <h2 class="text-3xl font-black text-slate-900">{{ pageData.team_title }}</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="member in pageData.team" :key="member.id" class="text-center group">
            <div class="w-28 h-28 mx-auto mb-4 overflow-hidden rounded-2xl border-2 border-slate-100 shadow-sm group-hover:shadow-md transition-shadow">
              <img :src="member.avatar" :alt="member.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
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

    <!-- ─── CTA ─── -->
    <section class="py-20 bg-emerald-600 relative overflow-hidden">
      <div class="absolute inset-0">
        <div class="absolute -left-20 -top-20 w-60 h-60 bg-emerald-500 rounded-full opacity-40"></div>
        <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-emerald-700 rounded-full opacity-30"></div>
      </div>
      <div class="relative max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-black text-white mb-5 leading-tight">{{ pageData.cta.title }}</h2>
        <p class="text-emerald-100 mb-10 max-w-lg mx-auto leading-relaxed">
          {{ pageData.cta.description }}
        </p>
        <router-link :to="pageData.cta.button_link"
          class="inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-white text-emerald-700 text-sm font-bold hover:bg-emerald-50 transition-colors shadow-lg">
          {{ pageData.cta.button_text }} <i class="fa-solid fa-arrow-right text-xs"></i>
        </router-link>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'

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

onMounted(() => {
  fetchAboutData()
})
</script>
