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
                  @click="activeCategory = Number(cat.id)"
                  class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 flex items-center justify-between"
                  :class="activeCategory === Number(cat.id) 
                    ? 'bg-emerald-50 text-[#16a34a] border border-emerald-200' 
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'">
                  {{ cat.name }}
                  <i v-if="activeCategory === Number(cat.id)" class="fa-solid fa-check text-xs"></i>
                </button>
              </li>
            </ul>
          </div>
        </aside>

        <div class="flex-1 animate__animated animate__fadeInRight">
          
          <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm mb-6 flex items-center transition-all focus-within:ring-2 focus-within:ring-emerald-200 focus-within:border-emerald-400">
            <div class="pl-4 pr-2 text-slate-400">
              <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm kiếm khóa học theo tên hoặc mục tiêu học..."
              class="w-full py-2.5 px-2 bg-transparent text-sm text-slate-700 focus:outline-none placeholder:text-slate-400 font-medium"
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="px-4 text-slate-400 hover:text-red-500 transition">
               <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 gap-4">
            <p class="text-sm text-slate-600 font-medium">
              Tìm thấy <span class="text-[#16a34a] font-bold">{{ filteredCourses.length }}</span> khóa học phù hợp
            </p>
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500 font-medium">Sắp xếp:</span>
              <select class="text-sm bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-[#7AE582] text-slate-700 font-medium cursor-pointer">
                <option>Mới nhất</option>
                <option>Nhiều học viên nhất</option>
                <option>Miễn phí trước</option>
              </select>
            </div>
          </div>

          <div v-if="isLoading" class="bg-white rounded-2xl border border-slate-200 py-20 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full border-4 border-emerald-100 border-t-[#16a34a] animate-spin mx-auto mb-4"></div>
            <p class="text-sm text-slate-500">Đang tải danh sách khóa học...</p>
          </div>

          <div v-else-if="errorMessage" class="bg-white rounded-2xl border border-red-200 py-10 px-6 text-center shadow-sm">
            <h3 class="text-lg font-bold text-slate-700 mb-2">Không tải được khóa học</h3>
            <p class="text-sm text-slate-500 mb-4">{{ errorMessage }}</p>
            <button @click="fetchCoursesData" class="px-4 py-2 rounded-xl bg-[#7AE582] text-slate-900 font-bold hover:bg-emerald-400 transition">
              Thử lại
            </button>
          </div>

          <div v-else-if="filteredCourses.length > 0" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <div v-for="course in filteredCourses" :key="course.id" 
                 class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 group flex flex-col relative">
              
              <div class="absolute top-3 left-3 z-10 flex gap-2">
                <span v-if="course.is_free" class="bg-gradient-to-r from-[#7AE582] to-[#54CC6D] text-slate-900 px-3 py-1 rounded-full text-xs font-black shadow-sm tracking-wide">
                  MIỄN PHÍ
                </span>
              </div>

              <div class="relative overflow-hidden aspect-video">
                <img :src="course.image_url" :alt="course.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute bottom-2 right-2 bg-slate-900/80 backdrop-blur px-2 py-1 rounded-md text-[0.65rem] font-bold text-white uppercase tracking-wider">
                  Level {{ course.level }}
                </div>
              </div>

              <div class="p-5 flex flex-col flex-1">
                <div class="text-[0.65rem] font-bold text-[#16a34a] uppercase tracking-widest mb-1">
                  {{ getCategoryName(course.category_id) }}
                </div>
                <h4 class="text-lg font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-[#16a34a] transition leading-tight">
                  {{ course.title }}
                </h4>
                <p class="text-xs text-slate-500 mb-5 line-clamp-2 flex-1 leading-relaxed">
                  {{ course.description }}
                </p>
                
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto gap-4">
                  <div class="text-xs text-slate-400">
                    <span class="font-medium flex items-center gap-1.5">
                      <i class="fa-solid fa-users text-slate-300"></i> {{ course.students_count }} học viên
                    </span>
                    <span class="block mt-1 font-medium"><i class="fa-solid fa-book-open text-slate-300 mr-1.5"></i>{{ course.lesson_count }} bài học</span>
                    <span class="block mt-1.5 font-black text-slate-700 text-sm">
                      {{ course.is_free ? '0 VNĐ' : `${formatPrice(course.fee)} VNĐ` }}
                    </span>
                  </div>
                  <router-link :to="'/course/' + course.id" class="text-xs font-bold text-slate-900 bg-slate-100 border border-slate-200 px-4 py-2 rounded-xl group-hover:bg-[#7AE582] group-hover:border-[#7AE582] transition shadow-sm whitespace-nowrap">
                    Xem chi tiết
                  </router-link>
                </div>
              </div>

            </div>
          </div>

          <div v-else class="bg-white rounded-2xl border border-slate-200 py-20 text-center shadow-sm">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
              <i class="fa-solid fa-magnifying-glass-minus"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-700 mb-1">Không tìm thấy khóa học</h3>
            <p class="text-sm text-slate-500">Thử thay đổi từ khóa tìm kiếm hoặc chọn danh mục khác nhé.</p>
            <button v-if="searchQuery || activeCategory !== 0" @click="searchQuery = ''; activeCategory = 0" class="mt-4 px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition text-sm">
              Xóa bộ lọc
            </button>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'

// --- TRẠNG THÁI BIẾN ---
const activeCategory = ref(0)
const searchQuery = ref('') // BIẾN MỚI: Chứa từ khóa tìm kiếm
const categories = ref([])
const courses = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

// --- CHUẨN HÓA DỮ LIỆU ---
const normalizeCourse = (course) => ({
  ...course,
  id: Number(course.id),
  category_id: Number(course.category_id ?? course.categoryId ?? 0),
  title: course.title ?? course.name ?? '',
  description: course.description ?? '',
  image_url: course.image_url ?? course.imageUrl ?? 'https://placehold.co/800x450/e2e8f0/64748b?text=Course',
  level: course.level ?? 'N/A',
  fee: Number(course.fee ?? 0),
  students_count: Number(course.students_count ?? 0),
  lesson_count: Number(course.lesson_count ?? 0),
  is_free: Boolean(course.is_free ?? course.isFree ?? Number(course.fee ?? 0) === 0)
})

// --- LẤY DỮ LIỆU TỪ API ---
const fetchCoursesData = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await apiFetch('public/courses.php')
    const result = await response.json()
    
    if (result.status === 'success') {
      categories.value = result.categories
      courses.value = result.courses.map(normalizeCourse)
    } else {
      errorMessage.value = result.message || 'Máy chủ không trả về dữ liệu hợp lệ.'
    }
  } catch (error) {
    console.error("Lỗi tải dữ liệu khóa học:", error)
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchCoursesData()
})

const getCategoryName = (id) => {
  const cat = categories.value.find(c => Number(c.id) === Number(id))
  return cat ? cat.name : 'Chưa phân loại'
}

// --- LOGIC LỌC & TÌM KIẾM (ĐÃ CẬP NHẬT) ---
const filteredCourses = computed(() => {
  let result = courses.value;

  // 1. Lọc theo danh mục (Sidebar)
  if (activeCategory.value !== 0) {
    result = result.filter(course => course.category_id === activeCategory.value);
  }

  // 2. Lọc theo từ khóa tìm kiếm (Tên khóa học hoặc Mô tả)
  const query = searchQuery.value.toLowerCase().trim();
  if (query) {
    result = result.filter(course => 
      (course.title && course.title.toLowerCase().includes(query)) ||
      (course.description && course.description.toLowerCase().includes(query))
    );
  }

  return result;
})

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price)
</script>