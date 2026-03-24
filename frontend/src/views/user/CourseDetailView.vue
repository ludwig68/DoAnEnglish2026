<template>
  <div class="bg-slate-50 min-h-screen pb-20">
    
    <div v-if="isLoading" class="flex flex-col items-center justify-center min-h-[60vh] text-slate-400">
      <i class="fa-solid fa-circle-notch fa-spin text-4xl text-[#16a34a] mb-4"></i>
      <p>Đang tải thông tin khóa học...</p>
    </div>

    <template v-else-if="course">
      <section class="bg-slate-900 text-white pt-24 pb-32 relative overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-20">
          <div class="absolute top-0 right-0 w-96 h-96 bg-[#7AE582] rounded-full mix-blend-multiply filter blur-[100px] animate-pulse"></div>
        </div>
        
        <div class="max-w-6xl mx-auto px-4 relative z-10 flex flex-col md:flex-row gap-8 items-center animate__animated animate__fadeIn">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-4 flex-wrap">
              <span class="px-3 py-1 rounded-full bg-[#7AE582]/20 text-[#7AE582] text-sm font-bold border border-[#7AE582]/30">
                {{ course.category_name || 'Chưa phân loại' }}
              </span>
              <span class="px-3 py-1 rounded-full bg-white/10 text-slate-300 text-sm font-bold border border-white/20">
                Level: {{ course.level || 'N/A' }}
              </span>
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold mb-6 leading-tight">
              {{ course.title }}
            </h1>
            <p class="text-lg text-slate-300 mb-8 max-w-2xl leading-relaxed">
              {{ course.description || 'Khóa học này hiện chưa có mô tả chi tiết.' }}
            </p>
            <div class="flex items-center gap-6 text-sm text-slate-400 flex-wrap">
              <span class="flex items-center gap-2"><i class="fa-solid fa-users text-[#7AE582]"></i> {{ course.students_count }} học viên</span>
              <span class="flex items-center gap-2"><i class="fa-solid fa-book-open text-[#7AE582]"></i> {{ course.lesson_count }} bài học</span>
            </div>
          </div>
        </div>
      </section>

      <section class="max-w-6xl mx-auto px-4 -mt-16 relative z-20 animate__animated animate__fadeInUp">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
              <h2 class="text-2xl font-bold text-slate-800 mb-4">Tổng quan khóa học</h2>
              <p class="text-slate-600 leading-relaxed">
                {{ course.description || 'Khóa học này hiện chưa có mô tả chi tiết.' }}
              </p>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
              <h2 class="text-2xl font-bold text-slate-800 mb-2">Nội dung khóa học</h2>
              <p class="text-slate-500 text-sm mb-6">{{ course.lesson_count }} bài học đang có trong hệ thống</p>
              
              <div v-if="course.lessons.length > 0" class="space-y-4">
                <div v-for="(lesson, index) in course.lessons" :key="lesson.id" class="border border-slate-200 rounded-2xl overflow-hidden group hover:border-[#7AE582] transition-colors">
                  <div class="bg-slate-50 p-4 flex justify-between items-center cursor-pointer">
                    <div class="flex items-center gap-4">
                      <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center font-bold text-slate-500 text-sm">
                        {{ lesson.order_number || index + 1 }}
                      </div>
                      <h3 class="font-bold text-slate-800">{{ lesson.title }}</h3>
                    </div>
                    <div class="text-slate-400 text-sm flex items-center gap-4">
                      <span>{{ lesson.video_url ? 'Có video bài giảng' : 'Chưa có video' }}</span>
                      <i class="fa-solid fa-play group-hover:text-[#16a34a] transition-colors"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center mx-auto mb-3 text-slate-400">
                  <i class="fa-solid fa-book"></i>
                </div>
                <h3 class="font-bold text-slate-700 mb-1">Khóa học chưa có bài học nào</h3>
                <p class="text-sm text-slate-500">Dữ liệu lesson trong database hiện đang trống, nên trang chỉ hiển thị thông tin khóa học thật đang có.</p>
              </div>
            </div>
          </div>

          <div class="relative">
            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 sticky top-24">
              <div class="rounded-2xl overflow-hidden mb-6 aspect-video">
                <img :src="course.image_url" :alt="course.title" class="w-full h-full object-cover">
              </div>
              
              <div class="mb-6 text-center">
                <div v-if="course.fee === 0" class="text-4xl font-black text-[#16a34a]">Miễn phí</div>
                <div v-else class="text-4xl font-black text-slate-800">{{ formatPrice(course.fee) }} <span class="text-xl text-slate-500 font-medium">VNĐ</span></div>
              </div>

              <button class="w-full py-4 rounded-xl bg-[#7AE582] hover:bg-emerald-300 text-slate-900 font-black text-lg transition-all shadow-lg hover:-translate-y-1 hover:shadow-emerald-200 mb-4">
                Đăng ký học ngay
              </button>
              
              <ul class="space-y-3 text-sm text-slate-600 border-t border-slate-100 pt-6">
                <li class="flex items-center gap-3"><i class="fa-solid fa-layer-group w-5 text-center text-slate-400"></i> Danh mục: {{ course.category_name || 'Chưa phân loại' }}</li>
                <li class="flex items-center gap-3"><i class="fa-solid fa-signal w-5 text-center text-slate-400"></i> Trình độ: {{ course.level || 'N/A' }}</li>
                <li class="flex items-center gap-3"><i class="fa-solid fa-book-open w-5 text-center text-slate-400"></i> Tổng bài học: {{ course.lesson_count }}</li>
              </ul>
            </div>
          </div>

        </div>
      </section>
    </template>
    
    <div v-else class="flex flex-col items-center justify-center min-h-[60vh]">
      <img src="https://placehold.co/200x200/f8fafc/cbd5e1?text=404" class="rounded-full mb-4">
      <h2 class="text-2xl font-bold text-slate-700">Không tìm thấy khóa học</h2>
      <button @click="$router.push('/courses')" class="mt-4 px-6 py-2 bg-[#7AE582] rounded-lg font-bold">Quay lại danh sách</button>
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
        image_url: result.data.image_url || 'https://placehold.co/800x450/e2e8f0/64748b?text=Course'
      }
    } else {
      console.error(result.message)
    }
  } catch (error) {
    console.error("Lỗi khi kết nối đến máy chủ:", error)
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
  return new Intl.NumberFormat('vi-VN').format(price)
}
</script>
