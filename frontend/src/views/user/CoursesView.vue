<template>
  <div>
    <!-- ═══ PAGE HEADER ═══ -->
    <section class="relative overflow-hidden py-14 border-b border-slate-100"
      style="background: linear-gradient(160deg, #f0fdf4 0%, #ffffff 60%, #f0fdf4 100%)">
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-100/30 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute inset-0 opacity-[0.03]"
        style="background-image: radial-gradient(circle, #16a34a 1px, transparent 1px); background-size: 28px 28px;">
      </div>
      <div class="relative max-w-7xl mx-auto px-6">
        <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-2">
          <span class="w-5 h-[2px] bg-emerald-500 rounded-full"></span> Danh sách khóa học
        </p>
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-3 tracking-tight">Khám Phá Khóa Học</h1>
        <p class="text-slate-500 text-sm max-w-md">Tìm lộ trình phù hợp với trình độ và mục tiêu học tiếng Anh của bạn.</p>
        <!-- Breadcrumb -->
        <p class="text-xs text-slate-400 mt-4 flex items-center gap-1.5">
          <router-link to="/" class="hover:text-emerald-600 transition-colors">Trang chủ</router-link>
          <i class="fa-solid fa-chevron-right text-[0.55rem] text-slate-300"></i>
          <span class="text-emerald-600 font-semibold">Tất cả khóa học</span>
        </p>
      </div>
    </section>

    <!-- ═══ MAIN CONTENT ═══ -->
    <section class="py-10 bg-slate-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-7">

          <!-- Sidebar -->
          <aside class="w-full lg:w-60 shrink-0">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm sticky top-24">
              <h2 class="font-black text-slate-800 mb-4 flex items-center gap-2 text-sm">
                <i class="fa-solid fa-layer-group text-emerald-500 text-xs"></i> Danh mục
              </h2>

              <!-- All categories button -->
              <button
                @click="activeCategory = 0"
                class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 mb-1 flex items-center justify-between"
                :class="activeCategory === 0
                  ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200'
                  : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
              >
                <span class="flex items-center gap-2">
                  <i class="fa-solid fa-border-all text-[0.65rem]"></i> Tất cả
                </span>
                <span class="text-[0.65rem] font-bold opacity-70">{{ courses.length }}</span>
              </button>

              <div class="space-y-1 mt-2">
                <button
                  v-for="cat in categories"
                  :key="cat.id"
                  @click="activeCategory = Number(cat.id)"
                  class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-between"
                  :class="activeCategory === Number(cat.id)
                    ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200'
                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
                >
                  <span class="truncate">{{ cat.name }}</span>
                  <i v-if="activeCategory === Number(cat.id)" class="fa-solid fa-check text-[0.6rem] shrink-0 ml-1"></i>
                </button>
              </div>

              <!-- Level filters -->
              <div class="mt-5 pt-5 border-t border-slate-100">
                <h3 class="font-black text-slate-700 text-xs uppercase tracking-widest mb-3">Trình độ</h3>
                <div class="flex flex-wrap gap-1.5">
                  <button
                    v-for="lvl in ['A1', 'A2', 'B1', 'B2', 'C1']" :key="lvl"
                    @click="activeLevel = activeLevel === lvl ? '' : lvl"
                    class="px-3 py-1.5 rounded-lg text-[0.7rem] font-bold transition-all duration-200"
                    :class="activeLevel === lvl
                      ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-200'
                      : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border border-slate-100'"
                  >{{ lvl }}</button>
                </div>
              </div>
            </div>
          </aside>

          <!-- Course grid -->
          <div class="flex-1">
            <!-- Search + sort bar -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
              <div class="flex-1 bg-white border border-slate-200 rounded-xl flex items-center px-4 gap-3 shadow-sm transition-all focus-within:ring-2 focus-within:ring-emerald-200 focus-within:border-emerald-400">
                <i class="fa-solid fa-magnifying-glass text-slate-300 text-sm shrink-0"></i>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Tìm kiếm khóa học..."
                  class="w-full py-3 bg-transparent text-sm text-slate-700 focus:outline-none placeholder:text-slate-300 font-medium"
                />
                <button v-if="searchQuery" @click="searchQuery = ''" class="text-slate-300 hover:text-red-400 transition-colors shrink-0">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
              <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 shadow-sm">
                <i class="fa-solid fa-sort text-slate-300 text-sm"></i>
                <select v-model="sortBy" class="text-sm bg-transparent text-slate-600 font-semibold focus:outline-none cursor-pointer py-3">
                  <option value="newest">Mới nhất</option>
                  <option value="popular">Nhiều học viên nhất</option>
                  <option value="free">Miễn phí trước</option>
                  <option value="price_asc">Giá tăng dần</option>
                </select>
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
            <div v-else-if="filteredCourses.length > 0" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
              <div
                v-for="course in filteredCourses" :key="course.id"
                class="bg-white rounded-2xl overflow-hidden border border-slate-100 flex flex-col group hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
              >
                <!-- Badge FREE -->
                <div class="relative overflow-hidden aspect-video">
                  <img :src="course.image_url" :alt="course.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                  >
                  <!-- Gradient overlay -->
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                  <!-- Level badge -->
                  <span class="absolute bottom-3 right-3 bg-slate-900/75 backdrop-blur-sm text-white px-2.5 py-1 rounded-lg text-[0.6rem] font-bold uppercase tracking-wider">
                    Level {{ course.level }}
                  </span>
                  <!-- Free badge -->
                  <span v-if="course.is_free"
                    class="absolute top-3 left-3 text-white px-3 py-1 rounded-full text-[0.65rem] font-black shadow-sm tracking-wide"
                    style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
                  >MIỄN PHÍ</span>
                </div>

                <!-- Card body -->
                <div class="p-5 flex flex-col flex-1">
                  <p class="text-[0.62rem] font-bold text-emerald-600 uppercase tracking-widest mb-1.5">
                    {{ getCategoryName(course.category_id) }}
                  </p>
                  <h3 class="text-base font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-emerald-700 transition-colors leading-snug">
                    {{ course.title }}
                  </h3>
                  <p class="text-xs text-slate-400 mb-4 line-clamp-2 flex-1 leading-relaxed">
                    {{ course.description }}
                  </p>

                  <div class="pt-4 border-t border-slate-50 flex items-center justify-between mt-auto gap-3">
                    <div>
                      <div class="flex items-center gap-3 text-[11px] text-slate-400 font-medium mb-1">
                        <span class="flex items-center gap-1">
                          <i class="fa-solid fa-users text-slate-300"></i> {{ course.students_count }}
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fa-solid fa-book-open text-slate-300"></i> {{ course.lesson_count }} bài
                        </span>
                      </div>
                      <p class="text-sm font-black text-slate-800">
                        {{ course.is_free ? 'Miễn phí' : `${formatPrice(course.fee)} ₫` }}
                      </p>
                    </div>
                    <router-link :to="'/course/' + course.id"
                      class="h-10 px-4 rounded-xl text-xs font-bold text-white flex items-center gap-1.5 shadow-md shadow-emerald-200/50 hover:-translate-y-0.5 hover:shadow-emerald-300/50 transition-all duration-200 whitespace-nowrap shrink-0"
                      style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
                    >
                      Chi tiết <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>
                    </router-link>
                  </div>
                </div>
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
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'

const activeCategory = ref(0)
const activeLevel = ref('')
const searchQuery = ref('')
const sortBy = ref('newest')
const categories = ref([])
const courses = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

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