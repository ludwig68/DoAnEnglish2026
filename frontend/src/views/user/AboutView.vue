<template>
  <div class="bg-white min-h-screen">
    
    <section class="relative bg-slate-900 pt-24 pb-32 overflow-hidden">
      <div class="absolute inset-0 z-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#7AE582] rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-20"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
      </div>

      <div class="relative z-10 max-w-6xl mx-auto px-4 text-center">
        <span class="inline-block py-1.5 px-4 rounded-full bg-white/10 text-[#7AE582] text-sm font-bold tracking-wider uppercase mb-6 border border-white/20 backdrop-blur-md animate__animated animate__fadeInDown">
          {{ pageData.hero.tagline }}
        </span>
        <h1 class="text-4xl md:text-5xl lg:text-7xl font-extrabold text-white mb-8 tracking-tight animate__animated animate__fadeInUp">
          {{ pageData.hero.title }} <br>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#7AE582] to-emerald-400">{{ pageData.hero.highlight }}</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed mb-10 animate__animated animate__fadeInUp animate__delay-1s">
          {{ pageData.hero.description }}
        </p>
      </div>
    </section>

    <section class="py-20 bg-white relative -mt-10 z-20">
      <div class="max-w-6xl mx-auto px-4">
        <div class="bg-white rounded-3xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] p-8 md:p-16 border border-slate-100 grid md:grid-cols-2 gap-12 items-center animate__animated animate__fadeInUp">
          
          <div class="relative">
            <div class="absolute -inset-4 bg-gradient-to-r from-emerald-100 to-teal-50 rounded-3xl transform rotate-3"></div>
            <img :src="pageData.story.image_url" alt="Sứ mệnh" class="relative z-10 rounded-2xl shadow-lg w-full object-cover aspect-[4/3]">
            <div class="absolute -bottom-6 -right-6 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 z-20 flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xl">
                <i class="fa-solid fa-rocket"></i>
              </div>
              <div>
                <p class="text-2xl font-black text-slate-800">{{ pageData.story.year }}</p>
                <p class="text-xs font-bold text-slate-500 uppercase">Năm khởi nguồn</p>
              </div>
            </div>
          </div>

          <div>
            <h2 class="text-3xl font-bold text-slate-900 mb-6">{{ pageData.story.title }}</h2>
            <div class="space-y-4 text-slate-600 leading-relaxed" v-html="pageData.story.content"></div>
            
            <div class="mt-8 flex gap-4 items-center">
              <div class="flex -space-x-3">
                <div class="w-10 h-10 rounded-full border-2 border-white bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700">
                  {{ pageData.stats.students }}
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">
                  {{ pageData.stats.courses }}
                </div>
              </div>
              <p class="text-sm text-slate-500 font-medium flex items-center">
                Đang có {{ pageData.stats.students }} học viên và {{ pageData.stats.courses }} khóa học trong hệ thống.
              </p>
            </div>
          </div>

        </div>
      </div>
    </section>

    <section class="py-20 bg-slate-50">
      <div class="max-w-6xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-sm font-bold text-[#16a34a] uppercase tracking-wider mb-2">Giá trị cốt lõi</h2>
          <h3 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">{{ pageData.values_title }}</h3>
          <p class="text-slate-600">{{ pageData.values_description }}</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="(item, index) in pageData.values" :key="index" class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-all duration-300" :class="[item.icon_background || 'bg-emerald-50', item.icon_color || 'text-[#16a34a]']">
              <i :class="['fa-solid', item.icon || 'fa-star']"></i>
            </div>
            <h4 class="text-xl font-bold text-slate-800 mb-3">{{ item.title }}</h4>
            <p class="text-slate-600 leading-relaxed text-sm">{{ item.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-6xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-sm font-bold text-[#16a34a] uppercase tracking-wider mb-2">Đội ngũ</h2>
          <h3 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">{{ pageData.team_title }}</h3>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="member in pageData.team" :key="member.id" class="text-center group">
            <div class="relative w-40 h-40 mx-auto mb-6 overflow-hidden rounded-full p-1 bg-gradient-to-tr from-[#7AE582] to-teal-300">
              <img :src="member.avatar" :alt="member.name" class="w-full h-full object-cover rounded-full border-4 border-white group-hover:scale-110 transition-transform duration-500">
            </div>
            <h4 class="text-lg font-bold text-slate-800">{{ member.name }}</h4>
            <p class="text-sm font-medium text-[#16a34a] mb-3">{{ member.role }}</p>
            <div class="flex justify-center gap-3 text-slate-400">
              <a href="#" class="hover:text-[#16a34a] transition"><i class="fa-brands fa-linkedin text-lg"></i></a>
              <a href="#" class="hover:text-[#16a34a] transition"><i class="fa-brands fa-github text-lg"></i></a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 relative overflow-hidden">
      <div class="absolute inset-0 bg-slate-900"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-[#7AE582]/20 to-transparent"></div>
      <div class="absolute right-0 bottom-0 w-1/2 h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
      
      <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6">{{ pageData.cta.title }}</h2>
        <p class="text-lg text-slate-300 mb-10 max-w-2xl mx-auto">
          {{ pageData.cta.description }}
        </p>
        <router-link :to="pageData.cta.button_link" class="inline-flex items-center justify-center gap-2 px-10 py-4 rounded-full bg-[#7AE582] text-slate-900 font-bold text-lg hover:bg-emerald-300 hover:scale-105 transition-all shadow-[0_0_30px_rgba(122,229,130,0.4)]">
          {{ pageData.cta.button_text }} <i class="fa-solid fa-arrow-right"></i>
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
    title: 'Định hình lại cách bạn học',
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
        hero: {
          ...defaultPageData.hero,
          ...result.data.hero
        },
        story: {
          ...defaultPageData.story,
          ...result.data.story
        },
        stats: {
          ...defaultPageData.stats,
          ...result.data.stats
        },
        cta: {
          ...defaultPageData.cta,
          ...result.data.cta
        },
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
