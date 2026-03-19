<template>
  <div class="pb-16">
    
    <section class="relative bg-slate-50 pt-10 pb-16 lg:pt-16 lg:pb-24 overflow-hidden">
      <div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">
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
            <router-link :to="pageData.banner.primaryButtonLink" class="px-8 py-3.5 rounded-full bg-[#7AE582] text-slate-900 font-bold hover:bg-emerald-300 transition shadow-lg hover:shadow-xl flex items-center gap-2">
              {{ pageData.banner.primaryButtonText }} <i class="fa-solid fa-arrow-right"></i>
            </router-link>
          </div>
        </div>
        
        <div class="relative animate__animated animate__fadeInRight">
          <div class="absolute -inset-1 bg-gradient-to-r from-[#7AE582] to-teal-400 rounded-[2.5rem] blur-lg opacity-30"></div>
          <img :src="pageData.banner.imageUrl" alt="Banner" class="relative z-10 w-full object-cover rounded-[2rem] shadow-2xl aspect-[4/3] border-4 border-white">
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div class="order-2 md:order-1">
          <img :src="pageData.intro.imageUrl" alt="Giới thiệu" class="w-full object-cover rounded-2xl shadow-lg aspect-video">
        </div>
        <div class="order-1 md:order-2">
          <h2 class="text-sm font-bold text-[#16a34a] uppercase tracking-wider mb-2">Về chúng tôi</h2>
          <h3 class="text-3xl font-bold text-slate-900 mb-4">{{ pageData.intro.title }}</h3>
          <div class="text-slate-600 space-y-4 leading-relaxed" v-html="pageData.intro.content">
            </div>
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

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          
          <div v-for="course in pageData.featuredCourses" :key="course.id" 
               class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 group flex flex-col">
            
            <div class="relative overflow-hidden aspect-video">
              <img :src="course.imageUrl" :alt="course.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
              <div class="absolute top-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-slate-700 shadow-sm">
                Level {{ course.level }}
              </div>
            </div>

            <div class="p-6 flex flex-col flex-1">
              <h4 class="text-xl font-bold text-slate-800 mb-2 line-clamp-1 group-hover:text-[#16a34a] transition">
                {{ course.name }}
              </h4>
              <p class="text-sm text-slate-500 mb-6 line-clamp-3 flex-1">
                {{ course.description }}
              </p>
              
              <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                  <i class="fa-solid fa-users"></i> {{ course.students_count }} học viên
                </span>
                <router-link :to="'/course/' + course.id" class="text-sm font-bold text-[#16a34a] bg-emerald-50 px-4 py-2 rounded-lg hover:bg-[#7AE582] hover:text-slate-900 transition">
                  Học ngay
                </router-link>
              </div>
            </div>

          </div>
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

// =========================================================================
// CẤU TRÚC DỮ LIỆU CHUẨN (Sau này sẽ thay bằng kết quả trả về từ API PHP)
// Admin sẽ cập nhật các trường này trong Database
// =========================================================================
const pageData = ref({
  // Bảng: settings hoặc banners
  banner: {
    tagline: "Năm học 2026",
    title: "Chinh phục Tiếng Anh theo cách của bạn",
    description: "Hệ thống học tập thông minh cung cấp lộ trình cá nhân hóa, flashcard sinh động và bài tập tương tác giúp bạn nhớ từ vựng lâu hơn gấp 3 lần.",
    imageUrl: "https://placehold.co/800x600/e2e8f0/64748b?text=Banner+Image+Upload",
    primaryButtonText: "Bắt đầu miễn phí",
    primaryButtonLink: "/register"
  },
  
  // Bảng: settings hoặc pages
  intro: {
    title: "Môi trường học tập lý tưởng dành cho mọi cấp độ",
    // Lưu ý: Dùng HTML tag vì Admin thường dùng CKEditor
    content: "<p>Dù bạn là người mất gốc hay đang trong giai đoạn luyện thi IELTS/TOEIC nước rút, <strong>English Learning</strong> luôn có lộ trình phù hợp.</p><p>Chúng tôi kết hợp phương pháp Spaced Repetition (Lặp lại ngắt quãng) cùng các bài test đa dạng để đảm bảo bạn tiến bộ mỗi ngày.</p>",
    imageUrl: "https://placehold.co/800x450/f1f5f9/94a3b8?text=Intro+Image+Upload"
  },

  // Bảng: stats (thống kê)
  stats: {
    students: "10,250+",
    courses: "45+"
  },

  // Bảng: courses (Lọc WHERE is_featured = 1 LIMIT 3)
  featuredCourses: [
    {
      id: 1,
      name: "Tiếng Anh Giao Tiếp Mất Gốc",
      description: "Khóa học xây dựng nền tảng từ con số 0. Tập trung vào phát âm chuẩn IPA và các mẫu câu giao tiếp thông dụng hàng ngày.",
      imageUrl: "https://placehold.co/600x400/dbeafe/3b82f6?text=Course+1",
      level: "A1",
      students_count: 1250
    },
    {
      id: 2,
      name: "Luyện thi TOEIC 650+ Cấp Tốc",
      description: "Chiến lược làm bài thi TOEIC Format mới nhất. Cung cấp bộ từ vựng chuyên ngành kinh tế và hàng chục đề thi thử sát thực tế.",
      imageUrl: "https://placehold.co/600x400/dcfce7/22c55e?text=Course+2",
      level: "B1",
      students_count: 3420
    },
    {
      id: 3,
      name: "IELTS Masterclass 7.0",
      description: "Chinh phục 4 kỹ năng Nghe - Nói - Đọc - Viết. Chấm chữa bài Writing/Speaking chi tiết theo tiêu chuẩn của cựu giám khảo IELTS.",
      imageUrl: "https://placehold.co/600x400/fef08a/eab308?text=Course+3",
      level: "B2",
      students_count: 890
    }
  ]
})

// Tương lai: Khi nối API, bạn chỉ cần mở comment đoạn này ra
/*
onMounted(async () => {
  try {
    const response = await fetch('http://localhost/DoAnEnglish2026/backend/api/public/home_data.php')
    const result = await response.json()
    if (result.status === 'success') {
      pageData.value = result.data
    }
  } catch (error) {
    console.error("Lỗi tải dữ liệu trang chủ:", error)
  }
})
*/
</script>