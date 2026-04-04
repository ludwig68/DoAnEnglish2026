<template>
  <div class="bg-slate-50 min-h-screen pb-20">
    
    <!-- Loading (Realistic) -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center min-h-[60vh] text-slate-400 bg-slate-50 text-center px-6">
      <div class="w-12 h-12 rounded-full border-4 border-emerald-100 border-t-[#16a34a] animate-spin mb-6 mx-auto"></div>
      <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Preparing your course...</p>
    </div>

    <!-- ═══ MAIN CONTENT ═══ -->
    <template v-else-if="course">
      <!-- ═══ SPLIT MOCKUP HERO ═══ -->
      <section class="relative overflow-hidden pt-12 pb-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="grid lg:grid-cols-[1.2fr_1fr] gap-12 items-center">
            
            <!-- Left: Info -->
            <div class="animate__animated animate__fadeInLeft">
              <nav class="flex items-center gap-2 mb-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">
                <router-link to="/courses" class="hover:text-[#16a34a] transition-colors">Khóa học</router-link>
                <span>/</span>
                <span class="text-slate-400">{{ course.category_name || 'Tổng quát' }}</span>
              </nav>

              <h1 class="text-5xl md:text-7xl font-black text-slate-900 mb-8 tracking-tighter leading-[0.95]">
                {{ course.title.split(' ').slice(0, -1).join(' ') }}<br>
                <span class="text-[#16a34a]">{{ course.title.split(' ').pop() }}</span>
              </h1>
              
              <p class="text-lg text-slate-500 mb-10 max-w-xl leading-relaxed font-medium">
                {{ course.description || 'Nâng tầm kỹ năng của bạn với lộ trình học bài bản, chuyên sâu được thiết kế để mang lại kết quả vượt trội.' }}
              </p>

              <div class="flex items-center gap-8 text-[11px] font-black text-slate-400 flex-wrap">
                <div class="flex items-center gap-2">
                  <div class="flex text-amber-400 gap-0.5">
                    <i v-for="i in 5" :key="i" class="fa-solid fa-star text-[10px]"></i>
                  </div>
                  <span class="text-slate-900 ml-1 font-black">4.9</span>
                  <span class="text-slate-300">(2,450 đánh giá)</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="fa-solid fa-users text-[#16a34a]"></i> {{ course.students_count }} Học viên đăng ký
                </div>
                <div class="flex items-center gap-2">
                  <i class="fa-solid fa-clock text-[#16a34a]"></i> 48 Giờ học tập
                </div>
              </div>
            </div>

            <!-- Right: Large Image Offset -->
            <div class="relative animate__animated animate__fadeInRight">
              <div class="absolute -right-6 top-6 w-full h-full bg-emerald-50 rounded-[3rem] -z-10"></div>
              <div class="rounded-[3rem] overflow-hidden shadow-2xl shadow-emerald-900/10 border-8 border-white aspect-[4/3] relative">
                <img :src="course.image_url" :alt="course.title" class="w-full h-full object-cover">
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- ═══ MAIN BODY (MOCKUP STYLE) ═══ -->
      <section class="max-w-7xl mx-auto px-6 py-20 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-[1.8fr_1fr] gap-12">
          
          <div class="space-y-16">
            
            <!-- What you will achieve (Mockup Section) -->
            <div>
              <h2 class="text-3xl font-black text-slate-900 mb-10 tracking-tighter">Bạn sẽ đạt được gì</h2>
              <div class="grid sm:grid-cols-2 gap-6">
                <div v-for="item in [
                  'Phát triển vốn từ vựng học thuật chuyên sâu cho các kỳ thi quốc tế.',
                  'Làm chủ các cấu trúc ngữ pháp phức tạp để tăng tính liên kết.',
                  'Nắm vững chiến thuật xử lý các dạng biểu đồ và bản đồ khó.',
                  'Xây dựng lập luận logic, thuyết phục cho mọi chủ đề thảo luận.'
                ]" :key="item" class="bg-white p-6 rounded-3xl border border-slate-50 shadow-sm flex gap-4">
                  <div class="w-6 h-6 rounded-full bg-emerald-50 text-[#16a34a] flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-check text-[10px]"></i>
                  </div>
                  <p class="text-[14px] text-slate-600 font-bold leading-relaxed">{{ item }}</p>
                </div>
              </div>
            </div>

            <!-- Course Content (Accordion) -->
            <div>
              <div class="flex items-center justify-between mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Nội dung khóa học</h2>
                <span class="text-xs font-black text-slate-300 uppercase tracking-widest">{{ course.lesson_count }} Chương · {{ course.lessons.length }} Bài học</span>
              </div>
              
              <div class="space-y-4">
                <!-- Module Accordion -->
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden">
                  <button @click="expandedModule = expandedModule === 1 ? 0 : 1" class="w-full p-6 flex items-center justify-between hover:bg-slate-50 transition-colors focus:outline-none">
                    <div class="flex items-center gap-5">
                      <div class="w-10 h-10 rounded-full bg-emerald-50 text-[#16a34a] flex items-center justify-center font-black text-xs">01</div>
                      <span class="text-lg font-black text-slate-800">Giới thiệu về tài liệu học tập</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-slate-300 transition-transform" :class="expandedModule === 1 ? 'rotate-180' : ''"></i>
                  </button>
                  
                  <div v-if="expandedModule === 1" class="border-t border-slate-100 p-6 space-y-4 bg-slate-50/30">
                    <div v-for="(lesson, idx) in course.lessons" :key="lesson.id" class="flex items-center justify-between group">
                      <div class="flex items-center gap-4">
                        <i class="fa-solid fa-circle-play text-slate-200 group-hover:text-[#16a34a] transition-colors"></i>
                        <span class="text-[14px] font-bold text-slate-500 group-hover:text-slate-900 transition-colors">{{ lesson.title }}</span>
                      </div>
                      <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">{{ lesson.video_url ? 'Video' : 'Text' }}</span>
                    </div>
                    
                    <div v-if="course.lessons.length === 0" class="text-center py-4">
                      <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Nội dung đang chuẩn bị...</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Instructor Section (Mockup Match) -->
            <div class="bg-emerald-50/50 p-10 rounded-[3rem] border border-emerald-100/50 flex flex-col md:flex-row gap-10 items-center">
              <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-xl shrink-0">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=500&auto=format&fit=crop&q=60" class="w-full h-full object-cover">
              </div>
              <div class="flex-1">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Giảng viên hướng dẫn</p>
                <h3 class="text-3xl font-black text-slate-900 mb-3 tracking-tighter">Ms. Elena Rodriguez</h3>
                <p class="text-[14px] text-slate-500 font-medium leading-[1.7] mb-6">
                  Elena là chuyên gia ngôn ngữ cơ học với hơn 15 năm kinh nghiệm giảng dạy quốc tế. Cô đã giúp hơn 50.000 học viên trên toàn thế giới đạt được điểm số mơ ước thông qua phương pháp tiếp cận hệ thống và tư duy phân tích.
                </p>
                <div class="flex gap-3">
                  <span class="px-5 py-2.5 rounded-full bg-white text-[#16a34a] text-[10px] font-black uppercase tracking-widest border border-white shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Giảng viên Quốc tế
                  </span>
                </div>
              </div>
            </div>

            <!-- Success Stories (Mockup Match) -->
            <div>
              <h2 class="text-3xl font-black text-slate-900 mb-10 tracking-tighter">Câu chuyện thành công</h2>
              <div class="grid sm:grid-cols-2 gap-8">
                <div v-for="(story, idx) in [
                  {name: 'Nguyễn Minh Đức', target: 'Mục tiêu 7.5, Đạt được 8.0', text: 'Chỉ riêng chiến thuật phần Task 2 đã xứng đáng với chi phí bỏ ra. Tôi đã từ 6.5 lên 8.0 chỉ trong vòng 6 tuần học tập.'},
                  {name: 'Sophie Trần', target: 'Đạt chứng chỉ Band 8.5', text: 'Phương pháp phản hồi của cô Elena thực sự tuyệt vời. Cách cô ấy chia nhỏ các cấu trúc phức tạp giúp tôi dễ dàng áp dụng trong phòng thi.'}
                ]" :key="idx" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative group">
                   <div class="absolute top-8 right-8 flex text-emerald-100 gap-0.5">
                     <i v-for="i in 5" :key="i" class="fa-solid fa-star text-lg"></i>
                   </div>
                   <div class="mb-4 text-[#16a34a] opacity-20">
                     <i class="fa-solid fa-quote-left text-4xl"></i>
                   </div>
                   <p class="text-slate-600 font-bold italic leading-relaxed mb-10">"{{ story.text }}"</p>
                   <div class="flex items-center gap-4">
                     <div class="w-10 h-10 rounded-full bg-slate-100 italic flex items-center justify-center text-[10px] text-slate-300 font-black">{{ story.name.charAt(0) }}</div>
                     <div>
                       <h4 class="font-black text-slate-900 text-[13px] mb-0.5">{{ story.name }}</h4>
                       <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ story.target }}</p>
                     </div>
                   </div>
                </div>
              </div>
            </div>

          </div>

          <!-- ═══ SIDEBAR (MOCKUP STYLE) ═══ -->
          <div class="relative">
            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-900/5 border border-slate-100 p-8 sticky top-28 overflow-hidden group">
              <!-- Video Preview Match -->
              <div class="relative rounded-[2rem] overflow-hidden mb-8 aspect-video border border-slate-50 shadow-sm">
                <img :src="course.image_url" :alt="course.title" class="w-full h-full object-cover brightness-75 transition-all group-hover:scale-105 duration-700">
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md text-white flex items-center justify-center text-xl shadow-2xl ring-4 ring-white/10 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-play ml-1"></i>
                  </div>
                </div>
              </div>
              
              <div class="mb-10">
                <div class="flex items-baseline gap-3 mb-2 flex-wrap">
                  <span class="text-4xl font-black text-slate-900 tracking-tight">{{ formatPrice(course.fee) }}<span class="text-xl ml-0.5 font-medium">đ</span></span>
                  <span v-if="course.fee > 0" class="text-lg text-slate-300 font-bold line-through">{{ formatPrice(course.fee * 1.5) }}đ</span>
                </div>
                <p class="text-[11px] font-black text-[#16a34a] uppercase tracking-widest flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-[#16a34a] rounded-full animate-pulse"></span> Ưu đãi có hạn: Giảm 30%
                </p>
              </div>

              <div class="space-y-3 mb-10">
                <button class="w-full h-16 rounded-2xl bg-[#16a34a] text-white font-black text-[13px] uppercase tracking-widest transition-all hover:bg-slate-900 shadow-xl shadow-emerald-100 active:scale-95">
                  Đăng ký ngay
                </button>
                <button class="w-full h-14 rounded-2xl bg-white border-2 border-slate-100 text-slate-800 font-black text-[11px] uppercase tracking-widest transition-all hover:border-[#16a34a] hover:text-[#16a34a]">
                  Thêm vào giỏ hàng
                </button>
              </div>
              
              <div class="space-y-5">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Khóa học bao gồm:</p>
                <div v-for="feature in [
                  {icon: 'fa-infinity', text: 'Truy cập tài liệu trọn đời'},
                  {icon: 'fa-check-circle', text: '12 bài đánh giá cá nhân'},
                  {icon: 'fa-award', text: 'Chứng chỉ hoàn thành khóa học'},
                  {icon: 'fa-mobile-screen', text: 'Học trên máy tính, điện thoại & TV'}
                ]" :key="feature.text" class="flex items-center gap-4 group/item">
                  <div class="w-6 h-6 flex items-center justify-center text-[#16a34a]">
                    <i :class="'fa-solid ' + feature.icon + ' text-sm'"></i>
                  </div>
                  <span class="text-[13px] font-bold text-slate-500 leading-tight">{{ feature.text }}</span>
                </div>
              </div>

              <!-- Special Bundle Mockup -->
              <div class="mt-10 bg-emerald-50/50 rounded-3xl p-6 border border-emerald-100/50">
                 <h4 class="text-xs font-black text-slate-900 mb-2 uppercase tracking-widest">Gói Combo Đặc Biệt</h4>
                 <p class="text-[11px] text-slate-400 font-medium leading-relaxed mb-4">Sở hữu trọn bộ khóa học Viết, Đọc và Nói với ưu đãi lên tới 45%.</p>
                 <a href="#" class="text-[11px] font-black text-[#16a34a] hover:underline underline-offset-4">Xem chi tiết gói Combo</a>
              </div>
            </div>
          </div>

        </div>
      </section>
    </template>
    
    <!-- ═══ ERROR / NOT FOUND ═══ -->
    <div v-else class="flex flex-col items-center justify-center min-h-[60vh] bg-white text-center px-6">
      <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 text-slate-200">
        <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
      </div>
      <h2 class="text-2xl font-black text-slate-900 mb-2 tracking-tighter uppercase">Course Not Found</h2>
      <p class="text-slate-400 text-sm mb-8 max-w-xs mx-auto">The course you are looking for might have been moved or the ID is incorrect.</p>
      <button @click="$router.push('/courses')" class="px-10 py-4 bg-emerald-50 text-[#16a34a] rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-100 transition-all">Back to Library</button>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { apiFetch } from '../../utils/api'

const route = useRoute()
const course = ref(null)
const isLoading = ref(true)
const expandedModule = ref(1)

const fetchCourseDetail = async () => {
  isLoading.value = true
  course.value = null

  try {
    const response = await apiFetch(`public/course_detail.php?id=${route.params.id}`)
    const result = await response.json()
    
    if (result.status === 'success') {
      course.value = {
        ...result.data,
        id: Number(result.data.id),
        category_id: Number(result.data.category_id ?? 0),
        fee: Number(result.data.fee ?? 0),
        students_count: Number(result.data.students_count ?? 0),
        lesson_count: Number(result.data.lesson_count ?? result.data.lessons?.length ?? 0),
        lessons: Array.isArray(result.data.lessons) ? result.data.lessons : [],
        image_url: result.data.image_url || 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=1200&auto=format&fit=crop&q=80'
      }
    } else {
      console.error(result.message)
    }
  } catch (error) {
    console.error("Server connection error:", error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchCourseDetail()
})

watch(() => route.params.id, () => {
  fetchCourseDetail()
})

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('vi-VN').format(price)
}
</script>
