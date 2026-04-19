<template>
  <div class="px-10 py-8 min-h-screen">
    <!-- Header -->
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-500 mb-2">Quản lý tài liệu</p>
        <h1 class="text-3xl font-headline font-black text-slate-900 tracking-tight leading-none">Tài liệu giảng dạy</h1>
      </div>
      
      <!-- Lọc khóa học -->
      <div v-if="materials.length > 0" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <i class="fa-solid fa-filter text-slate-400 pl-3"></i>
        <select v-model="selectedCourseFilter" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:outline-none focus:ring-0 pr-4 cursor-pointer">
          <option value="all">Tất cả khóa học</option>
          <option v-for="c in materials" :key="c.course_id" :value="c.course_id">{{ c.course_title }}</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex-1 flex flex-col justify-center items-center py-20">
      <div class="w-12 h-12 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
      <p class="text-xs font-black uppercase tracking-widest text-slate-400 animate-pulse">Đang tải tài liệu...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredMaterials.length === 0" class="flex-1 flex flex-col justify-center items-center py-20 bg-white rounded-[2.5rem] border border-slate-50 shadow-sm">
      <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inner text-emerald-200">
        <i class="fa-solid fa-folder-open text-4xl"></i>
      </div>
      <p class="font-black text-lg text-slate-800">Chưa có tài liệu nào</p>
      <p class="text-sm text-slate-400 mt-2 text-center max-w-sm font-medium">Bạn chưa được phân công lớp nào có tài liệu, hoặc admin chưa upload tài liệu cho các khóa học của bạn.</p>
    </div>

    <!-- Materials List -->
    <div v-else class="space-y-8">
      <div v-for="course in filteredMaterials" :key="course.course_id" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden p-6 md:p-8 space-y-6">
        
        <!-- Khóa học Header (Mockup matched) -->
        <div class="flex items-center gap-5 p-2 cursor-pointer group">
          <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 transition-transform group-hover:scale-105">
            <i class="fa-solid fa-globe text-2xl"></i>
          </div>
          <div>
            <h2 class="text-xl md:text-2xl font-black font-headline text-slate-900 tracking-tight leading-none mb-1.5">{{ course.course_title }}</h2>
            <p class="text-xs md:text-sm font-semibold text-slate-500">
              {{ course.lessons ? course.lessons.length : 0 }} Modules • {{ countTotalMaterials(course) }} Files
            </p>
          </div>
          <div class="ml-auto w-10 h-10 flex items-center justify-center text-slate-400 group-hover:text-slate-900 transition-colors">
            <i class="fa-solid fa-chevron-right"></i>
          </div>
        </div>

        <!-- Modules Container -->
        <div class="bg-slate-50 rounded-[2rem] p-6 md:p-8 space-y-8">
          
          <!-- Tài liệu chung của khóa (Không thuộc bài học cụ thể) -->
          <div v-if="course.general_materials && course.general_materials.length > 0">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
              <h3 class="text-sm font-black uppercase tracking-[0.1em] text-emerald-700">
                TÀI LIỆU CHUNG
              </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              <FileCard v-for="mat in course.general_materials" :key="mat.id" :mat="mat" />
            </div>
          </div>

          <!-- Tài liệu theo từng Bài học -->
          <div v-for="(lesson, index) in course.lessons" :key="lesson.lesson_id" class="space-y-6">
             <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
               <h3 class="text-sm font-black uppercase tracking-[0.1em] text-emerald-700">
                  {{ lesson.lesson_title }}
               </h3>
               <span class="bg-emerald-100 text-emerald-700 text-[11px] font-bold px-3 py-1.5 rounded-full inline-block w-max">
                 Updated recently
               </span>
             </div>
             
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4">
               <FileCard v-for="mat in lesson.materials" :key="mat.id" :mat="mat" />
             </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiFetch } from '../../utils/api'
import { notifyError } from '../../utils/notify'
import FileCard from './components/FileCard.vue' // Sẽ tự tạo nội tuyến bằng JSX hoặc component nhỏ

const isLoading = ref(true)
const materials = ref([])
const selectedCourseFilter = ref('all')

const loadMaterials = async () => {
  isLoading.value = true
  try {
    const res = await apiFetch('teacher/materials.php')
    const result = await res.json()
    if (result.status === 'success') {
      materials.value = result.data || []
    } else {
      notifyError(result.message || 'Lỗi lấy tài liệu')
    }
  } catch (err) {
    notifyError('Lỗi kết nối.')
  } finally {
    isLoading.value = false
  }
}

onMounted(loadMaterials)

const countTotalMaterials = (course) => {
  let count = (course.general_materials || []).length
  for (const l of (course.lessons || [])) {
    count += (l.materials || []).length
  }
  return count
}

const filteredMaterials = computed(() => {
  if (selectedCourseFilter.value === 'all') return materials.value
  return materials.value.filter(c => String(c.course_id) === String(selectedCourseFilter.value))
})
</script>
