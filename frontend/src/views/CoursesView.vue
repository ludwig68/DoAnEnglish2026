<template>
  <div class="bg-slate-50 min-h-screen pb-16 pt-8">
    <div class="max-w-6xl mx-auto px-4">
      
      <div class="mb-8 animate__animated animate__fadeIn">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Khám Phá Khóa Học</h1>
        <p class="text-slate-500 text-sm">Trang chủ <span class="mx-2">/</span> <span class="text-[#16a34a] font-semibold">Tất cả khóa học</span></p>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        
        <aside class="w-full lg:w-64 shrink-0 animate__animated animate__fadeInLeft">
          <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm sticky top-24">
            <h2 class="font-bold text-slate-800 mb-4 flex items-center gap-2 uppercase tracking-wide text-sm">
              <i class="fa-solid fa-layer-group text-[#16a34a]"></i> Danh mục học tập
            </h2>
            <ul class="space-y-2">
              <li v-for="cat in categories" :key="cat.id">
                <button 
                  @click="activeCategory = cat.id"
                  class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 flex items-center justify-between"
                  :class="activeCategory === cat.id 
                    ? 'bg-emerald-50 text-[#16a34a] border border-emerald-200' 
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'">
                  {{ cat.name }}
                  <i v-if="activeCategory === cat.id" class="fa-solid fa-check text-xs"></i>
                </button>
              </li>
            </ul>
          </div>
        </aside>

        <div class="flex-1 animate__animated animate__fadeInRight">
          
          <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 gap-4">
            <p class="text-sm text-slate-600 font-medium">
              Tìm thấy <span class="text-[#16a34a] font-bold">{{ filteredCourses.length }}</span> khóa học phù hợp
            </p>
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500 font-medium">Sắp xếp:</span>
              <select class="text-sm bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-[#7AE582] text-slate-700 font-medium">
                <option>Mới nhất</option>
                <option>Học viên nhiều nhất</option>
                <option>Đánh giá cao nhất</option>
              </select>
            </div>
          </div>

          <div v-if="filteredCourses.length > 0" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            
            <div v-for="course in filteredCourses" :key="course.id" 
                 class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 group flex flex-col relative">
              
              <div class="absolute top-3 left-3 z-10 flex gap-2">
                <span v-if="course.isFree" class="bg-gradient-to-r from-[#7AE582] to-[#54CC6D] text-slate-900 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                  Miễn phí
                </span>
              </div>

              <div class="relative overflow-hidden aspect-video">
                <img :src="course.imageUrl" :alt="course.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute bottom-2 right-2 bg-slate-900/80 backdrop-blur px-2 py-1 rounded-md text-[0.65rem] font-bold text-white uppercase tracking-wider">
                  Level {{ course.level }}
                </div>
              </div>

              <div class="p-5 flex flex-col flex-1">
                <div class="text-[0.65rem] font-bold text-[#16a34a] uppercase tracking-widest mb-1">
                  {{ getCategoryName(course.categoryId) }}
                </div>
                <h4 class="text-lg font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-[#16a34a] transition leading-tight">
                  {{ course.name }}
                </h4>
                <p class="text-xs text-slate-500 mb-5 line-clamp-2 flex-1">
                  {{ course.description }}
                </p>
                
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                  <span class="text-xs font-medium text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-users"></i> {{ course.students_count }} học viên
                  </span>
                  <router-link :to="'/course/' + course.id" class="text-xs font-bold text-slate-900 bg-slate-100 border border-slate-200 px-4 py-2 rounded-xl group-hover:bg-[#7AE582] group-hover:border-[#7AE582] transition shadow-sm">
                    Vào học
                  </router-link>
                </div>
              </div>

            </div>

          </div>

          <div v-else class="bg-white rounded-2xl border border-slate-200 py-20 text-center shadow-sm">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
              <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-700 mb-1">Chưa có khóa học nào</h3>
            <p class="text-sm text-slate-500">Danh mục này hiện tại chưa có khóa học. Vui lòng thử lại sau.</p>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// --- DỮ LIỆU MÔ PHỎNG (Sẽ gọi API từ PHP sau) ---

const activeCategory = ref(0) // 0 là "Tất cả"

const categories = ref([
  { id: 0, name: 'Tất cả khóa học' },
  { id: 1, name: 'Tiếng Anh Giao Tiếp' },
  { id: 2, name: 'Luyện thi IELTS' },
  { id: 3, name: 'Luyện thi TOEIC' },
  { id: 4, name: 'Ngữ pháp cơ bản' }
])

const courses = ref([
  { id: 1, categoryId: 1, name: "Giao tiếp cho người mất gốc", description: "Lấy lại nền tảng phát âm và tự tin giao tiếp cơ bản.", imageUrl: "https://placehold.co/600x400/dbeafe/3b82f6?text=Giao+Tiep", level: "A1", students_count: 1250, isFree: true },
  { id: 2, categoryId: 1, name: "Tiếng Anh Giao tiếp Văn phòng", description: "Ứng dụng trong môi trường công sở, viết email, họp hành.", imageUrl: "https://placehold.co/600x400/e0e7ff/6366f1?text=Van+Phong", level: "A2", students_count: 850, isFree: true },
  { id: 3, categoryId: 3, name: "Luyện thi TOEIC 650+ Cấp Tốc", description: "Giải đề thực chiến, mẹo tránh bẫy Part 5, 6, 7.", imageUrl: "https://placehold.co/600x400/dcfce7/22c55e?text=TOEIC", level: "B1", students_count: 3420, isFree: true },
  { id: 4, categoryId: 2, name: "IELTS Masterclass 7.0 (Writing & Speaking)", description: "Nâng band điểm chuyên sâu cho 2 kỹ năng khó nhất.", imageUrl: "https://placehold.co/600x400/fef08a/eab308?text=IELTS", level: "B2", students_count: 890, isFree: false },
  { id: 5, categoryId: 4, name: "12 Thì Tiếng Anh Trọn Bộ", description: "Học hiểu bản chất, không học vẹt, nhớ trọn đời 12 thì.", imageUrl: "https://placehold.co/600x400/ffedd5/f97316?text=Ngu+Phap", level: "A1", students_count: 5210, isFree: true }
])

// --- XỬ LÝ LOGIC ---

// Helper: Lấy tên danh mục dựa vào ID
const getCategoryName = (id) => {
  const cat = categories.value.find(c => c.id === id)
  return cat ? cat.name : 'Chưa phân loại'
}

// Lọc khóa học theo danh mục được chọn
const filteredCourses = computed(() => {
  if (activeCategory.value === 0) return courses.value
  return courses.value.filter(course => course.categoryId === activeCategory.value)
})
</script>