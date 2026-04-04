<template>
  <div>
    <!-- ═══ PAGE HEADER ═══ -->
    <section class="relative overflow-hidden py-14"
      style="background: linear-gradient(160deg, #f0fdf4 0%, #ffffff 60%, #f0fdf4 100%)">
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-100/30 rounded-full blur-3xl pointer-events-none"></div>
      <div class="relative max-w-7xl mx-auto px-6">
        <p class="text-[0.65rem] font-black text-emerald-600 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
          <span class="w-8 h-[1px] bg-emerald-500 rounded-full"></span> Danh sách khóa học
        </p>
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4 tracking-tighter">Khám Phá <span class="text-emerald-600">Khóa Học</span></h1>
        <p class="text-slate-500 text-sm max-w-md leading-relaxed">Tìm lộ trình phù hợp với trình độ và mục tiêu học tiếng Anh của bạn.</p>
      </div>
    </section>

    <!-- ═══ MAIN CONTENT ═══ -->
    <section class="py-10 bg-slate-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-7">

          <!-- Sidebar -->
          <aside class="w-full lg:w-72 shrink-0">
            <div class="sticky top-24 space-y-8">
              
              <!-- Categories -->
              <div>
                <h2 class="font-black text-slate-800 mb-6 flex items-center gap-2 text-[0.7rem] uppercase tracking-[0.15em]">
                  Danh mục
                </h2>

                <div class="space-y-1">
                  <!-- All Category -->
                  <button
                    @click="activeCategory = 0"
                    class="w-full text-left px-5 py-3.5 rounded-xl text-[13px] font-bold transition-all duration-200 mb-1 flex items-center justify-between"
                    :class="activeCategory === 0
                      ? 'bg-emerald-50/80 text-[#16a34a]'
                      : 'text-slate-500 hover:bg-slate-50'"
                  >
                    <span>Tất cả khóa học</span>
                    <i v-if="activeCategory === 0" class="fa-solid fa-chevron-right text-[10px]"></i>
                  </button>

                  <!-- Dynamic Categories (Filtered Duplicates) -->
                  <button
                    v-for="cat in categories.filter(c => c.name !== 'Tất cả khóa học')"
                    :key="cat.id"
                    @click="activeCategory = Number(cat.id)"
                    class="w-full text-left px-5 py-3.5 rounded-xl text-[13px] font-bold transition-all duration-200 flex items-center justify-between"
                    :class="activeCategory === Number(cat.id)
                      ? 'bg-emerald-50/80 text-[#16a34a]'
                      : 'text-slate-500 hover:bg-slate-50'"
                  >
                    <span class="truncate">{{ cat.name }}</span>
                    <i v-if="activeCategory === Number(cat.id)" class="fa-solid fa-chevron-right text-[10px]"></i>
                  </button>
                </div>
              </div>

              <!-- Level filters -->
              <div>
                <h3 class="font-black text-slate-800 text-[0.7rem] uppercase tracking-[0.15em] mb-6">Trình độ</h3>
                <div class="grid grid-cols-2 gap-2">
                  <button
                    v-for="lvl in ['A1', 'A2', 'B1', 'B2', 'C1 - Advanced']" :key="lvl"
                    @click="activeLevel = activeLevel === lvl ? '' : lvl"
                    class="h-11 rounded-xl text-[11px] font-bold transition-all duration-200 border"
                    :class="activeLevel === lvl
                      ? 'bg-[#16a34a] text-white border-[#16a34a] shadow-lg shadow-emerald-200'
                      : 'bg-white text-slate-400 border-slate-100 hover:border-emerald-200 hover:text-[#16a34a]'"
                    :style="lvl === 'C1 - Advanced' ? 'grid-column: span 2' : ''"
                  >{{ lvl }}</button>
                </div>
              </div>

            </div>
          </aside>

          <!-- Course grid -->
          <div class="flex-1">
            <!-- Search + sort bar -->
            <div class="flex flex-col sm:flex-row gap-5 mb-8 items-center">
              <div class="flex-1 w-full bg-white rounded-full flex items-center px-6 gap-3 shadow-sm border border-slate-100 focus-within:shadow-md transition-all">
                <i class="fa-solid fa-magnifying-glass text-slate-300 text-sm shrink-0"></i>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Tìm kiếm khóa học..."
                  class="w-full py-4 bg-transparent text-sm text-slate-600 focus:outline-none placeholder:text-slate-300 font-bold"
                />
              </div>
              <div class="shrink-0 flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400">Sắp xếp:</span>
                <div class="relative">
                  <select v-model="sortBy" class="appearance-none bg-white border border-slate-100 rounded-xl px-5 py-3 pr-10 text-[13px] font-bold text-slate-900 focus:outline-none cursor-pointer shadow-sm">
                    <option value="newest">Mới nhất</option>
                    <option value="popular">Nhiều học viên</option>
                  </select>
                  <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>
              </div>
            </div>

            <!-- Result count -->
            <div class="flex items-center justify-between mb-5">
              <p class="text-sm text-slate-500">
                Tìm thấy <span class="font-bold text-emerald-600">{{ filteredCourses.length }}</span> khóa học
                <span v-if="searchQuery || activeCategory || activeLevel" class="text-slate-400">
                  <button @click="resetFilters" class="ml-2 text-xs font-bold text-slate-400 hover:text-red-500 transition-colors underline underline-offset-2">
                    Xóa bộ lọc
                  </button>
                </span>
              </p>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="bg-white rounded-2xl border border-slate-100 py-24 text-center shadow-sm">
              <div class="w-12 h-12 rounded-full border-4 border-emerald-100 border-t-emerald-500 animate-spin mx-auto mb-4"></div>
              <p class="text-sm text-slate-500 font-medium">Đang tải danh sách khóa học...</p>
            </div>

            <!-- Error -->
            <div v-else-if="errorMessage" class="bg-white rounded-2xl border border-red-100 py-12 px-6 text-center shadow-sm">
              <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
              </div>
              <h3 class="font-bold text-slate-700 mb-1">Không tải được\nkhóa học</h3>
              <p class="text-sm text-slate-500 mb-4">{{ errorMessage }}</p>
              <button @click="fetchCoursesData"
                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
              >Thử lại</button>
            </div>

            <!-- Course grid -->
            <div v-else-if="paginatedCourses.length > 0">
              <div class="grid gap-8 sm:grid-cols-2">
                <div
                  v-for="course in paginatedCourses" :key="course.id"
                  class="bg-white rounded-[2rem] overflow-hidden flex flex-col group hover:shadow-2xl hover:shadow-slate-200 transition-all duration-500 border border-slate-50"
                >
                  <!-- Image Section -->
                  <div class="relative overflow-hidden aspect-[4/3] m-2 rounded-[1.8rem]">
                    <img :src="course.image_url" :alt="course.title"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                    >
                    <!-- Level Badge -->
                    <span class="absolute top-5 left-5 bg-white/95 backdrop-blur-sm text-[#16a34a] px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm border border-emerald-50">
                      LEVEL {{ course.level }}
                    </span>
                  </div>

                  <!-- Card body -->
                  <div class="p-8 pb-10 flex flex-col flex-1">
                    <h3 class="text-xl font-black text-slate-900 mb-4 line-clamp-1 group-hover:text-[#16a34a] transition-colors tracking-tight">
                      {{ course.title }}
                    </h3>
                    <p class="text-[13px] text-slate-500 mb-6 line-clamp-2 flex-1 leading-relaxed font-medium">
                      {{ course.description }}
                    </p>

                    <!-- Meta -->
                    <div class="flex items-center gap-6 mb-8 text-[12px] font-bold text-slate-400">
                      <span class="flex items-center gap-2">
                        <i class="fa-solid fa-users text-[#16a34a]"></i> {{ course.students_count >= 1000 ? (course.students_count/1000).toFixed(1) + 'k' : course.students_count }} Học viên
                      </span>
                      <span class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-play text-[#16a34a]"></i> {{ course.lesson_count }} Bài học
                      </span>
                    </div>

                    <!-- Bottom Action -->
                    <div class="flex items-center justify-between mt-auto">
                      <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Học phí</p>
                        <p class="text-2xl font-black text-slate-900">
                          {{ course.is_free ? 'Miễn phí' : `${formatPrice(course.fee)}đ` }}
                        </p>
                      </div>
                      <router-link :to="'/course/' + course.id"
                        class="h-12 px-8 rounded-2xl bg-[#16a34a] text-white text-[13px] font-bold flex items-center justify-center transition-all hover:bg-[#059669] hover:scale-105 active:scale-95 shadow-lg shadow-emerald-100"
                      >
                        Chi tiết
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Real Pagination (Only show if > 6 items) -->
              <div v-if="totalPages > 1" class="mt-16 flex items-center justify-center gap-2">
                <button 
                  @click="goToPage(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="w-12 h-12 rounded-full flex items-center justify-center border border-slate-100 text-slate-300 hover:text-[#16a34a] transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                >
                  <i class="fa-solid fa-arrow-left"></i>
                </button>
                
                <button 
                  v-for="p in totalPages" :key="p"
                  @click="goToPage(p)"
                  class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all"
                  :class="p === currentPage 
                    ? 'bg-[#16a34a] text-white shadow-lg shadow-emerald-100' 
                    : 'text-slate-400 hover:bg-slate-100'"
                >
                  {{ p }}
                </button>

                <button 
                  @click="goToPage(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="w-12 h-12 rounded-full flex items-center justify-center border border-slate-100 text-slate-300 hover:text-[#16a34a] transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                >
                  <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- Empty -->
            <div v-else class="bg-white rounded-2xl border border-slate-100 py-24 text-center shadow-sm">
              <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-200 text-3xl">
                <i class="fa-solid fa-magnifying-glass-minus"></i>
              </div>
              <h3 class="text-base font-bold text-slate-600 mb-1">Không tìm thấy khóa học</h3>
              <p class="text-sm text-slate-400 mb-5">Thử thay đổi từ khóa hoặc chọn danh mục khác.</p>
              <button @click="resetFilters"
                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
              >Xóa bộ lọc</button>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { apiFetch } from '../../utils/api'

const activeCategory = ref(0)
const activeLevel = ref('')
const searchQuery = ref('')
const sortBy = ref('newest')
const categories = ref([])
const courses = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

// Real Pagination Logic
const currentPage = ref(1)
const itemsPerPage = 6

const totalPages = computed(() => Math.ceil(filteredCourses.value.length / itemsPerPage))

const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredCourses.value.slice(start, end)
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 400, behavior: 'smooth' })
  }
}

// Reset page when anything changes
watch([searchQuery, activeCategory, activeLevel, sortBy], () => {
  currentPage.value = 1
})

const normalizeCourse = (course) => ({
  ...course,
  id: Number(course.id),
  category_id: Number(course.category_id ?? course.categoryId ?? 0),
  title: course.title ?? course.name ?? '',
  description: course.description ?? '',
  image_url: course.image_url ?? 'https://placehold.co/800x450/e2e8f0/64748b?text=Course',
  level: course.level ?? 'N/A',
  fee: Number(course.fee ?? 0),
  students_count: Number(course.students_count ?? 0),
  lesson_count: Number(course.lesson_count ?? 0),
  is_free: Boolean(course.is_free ?? course.isFree ?? Number(course.fee ?? 0) === 0)
})

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
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => fetchCoursesData())

const getCategoryName = (id) => {
  const cat = categories.value.find(c => Number(c.id) === Number(id))
  return cat ? cat.name : 'Chưa phân loại'
}

const resetFilters = () => {
  searchQuery.value = ''
  activeCategory.value = 0
  activeLevel.value = ''
}

const filteredCourses = computed(() => {
  let result = courses.value

  if (activeCategory.value !== 0) {
    result = result.filter(c => c.category_id === activeCategory.value)
  }

  if (activeLevel.value) {
    result = result.filter(c => c.level === activeLevel.value)
  }

  const query = searchQuery.value.toLowerCase().trim()
  if (query) {
    result = result.filter(c =>
      (c.title && c.title.toLowerCase().includes(query)) ||
      (c.description && c.description.toLowerCase().includes(query))
    )
  }

  if (sortBy.value === 'popular') {
    result = [...result].sort((a, b) => b.students_count - a.students_count)
  } else if (sortBy.value === 'free') {
    result = [...result].sort((a, b) => Number(b.is_free) - Number(a.is_free))
  } else if (sortBy.value === 'price_asc') {
    result = [...result].sort((a, b) => a.fee - b.fee)
  }

  return result
})

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price)
</script>