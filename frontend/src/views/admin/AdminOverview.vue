<template>
  <div class="min-h-full bg-slate-50/50 p-6 xl:p-8 font-sans animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Tổng quan hệ thống</h1>
        <p class="mt-2 text-slate-500 font-medium text-sm">Cập nhật và theo dõi các luồng công việc mới nhất trong ngày.</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-500 shadow-sm border border-slate-200">
          <i class="fa-regular fa-clock text-slate-400"></i> {{ syncLabel }}
        </span>
        <button class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-emerald-500 transition-colors hover:shadow-emerald-200">
          <i class="fa-solid fa-cloud-arrow-down"></i> Báo cáo
        </button>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="isLoading" class="flex min-h-[400px] items-center justify-center rounded-3xl bg-white shadow-sm border border-slate-100">
       <div class="text-center">
         <div class="w-12 h-12 border-4 border-slate-100 border-t-[#7AE582] rounded-full animate-spin mx-auto mb-4"></div>
         <p class="text-slate-500 font-medium text-sm">Đang tải dữ liệu...</p>
       </div>
    </div>

    <template v-else>
      <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-600 flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-lg"></i> {{ errorMessage }}
      </div>

      <!-- Quick Stats -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <article
          v-for="card in overviewCards"
          :key="card.label"
          class="relative overflow-hidden rounded-[2.5rem] bg-white p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-[0_15px_45px_rgb(0,0,0,0.08)] hover:-translate-y-1.5 group cursor-default"
        >
          <div class="flex justify-between items-start mb-6">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl transition-all duration-300 group-hover:scale-110 shadow-sm border border-opacity-50" :class="card.tokenClass">
              <i :class="card.icon"></i>
            </div>
            <span class="text-[42px] font-headline font-black text-slate-100 group-hover:opacity-100 transition-all opacity-40 leading-none tracking-tighter">{{ card.value.toString().padStart(2, '0') }}</span>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2 group-hover:text-emerald-600 transition-colors">{{ card.label }}</p>
            <h3 class="text-[16px] font-black text-slate-800 leading-snug">{{ card.description }}</h3>
          </div>
          
          <!-- Decorative gradient edge -->
          <div class="absolute bottom-0 left-0 w-full h-1 opacity-0 group-hover:opacity-100 transition-opacity" :class="card.borderClass"></div>
        </article>
      </section>

      <!-- Main Layout 2 Cột -->
      <section class="grid gap-8 xl:grid-cols-12 mb-12">
        
        <!-- LEFT: Quản lý bài tập + Cấu trúc Trung tâm (8 cols) -->
        <div class="xl:col-span-8 space-y-12">
          

          <!-- Cấu trúc Trung tâm (Modules) -->
          <div class="rounded-[2.5rem] bg-white border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10">
            <div class="flex items-center justify-between mb-10">
              <div>
                <h2 class="text-[22px] font-headline font-black text-slate-800 tracking-tight">Cấu trúc Trung tâm</h2>
                <p class="text-[13px] font-medium text-slate-500 mt-1">Quản lý và thiết lập các danh mục tính năng cốt lõi.</p>
              </div>
              <div class="px-5 py-2 bg-emerald-50 rounded-full border border-emerald-100">
                <span class="text-[11px] font-black text-emerald-600 uppercase tracking-widest">{{ modules.length }} MỤC</span>
              </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
              <component
                :is="module.to ? 'router-link' : 'div'"
                v-for="module in modules"
                :key="module.key"
                :to="module.to"
                class="relative p-6 rounded-[2rem] border transition-all duration-300 outline-none flex flex-col justify-between overflow-hidden cursor-pointer h-[180px]"
                :class="[
                  module.to ? 'hover:shadow-md hover:-translate-y-1 group hover:border-emerald-100' : 'opacity-80',
                  moduleCardClass(module.accent)
                ]"
              >
                <div class="flex justify-between items-start mb-4 relative z-10">
                  <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-50 flex items-center justify-center text-xl transition-transform duration-300 group-hover:scale-110" :class="moduleIconClass(module.accent)">
                    <i :class="moduleIcon(module.key)"></i>
                  </div>
                  <div class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl bg-white border border-slate-50 text-slate-400 shadow-sm">
                    {{ module.unit }}
                  </div>
                </div>
                
                <div class="relative z-10">
                  <div class="flex items-center justify-between">
                    <h3 class="text-[17px] font-black text-slate-800 group-hover:text-emerald-700 transition-colors uppercase tracking-tight">{{ module.title }}</h3>
                    <div class="text-[26px] font-headline font-black text-slate-800">{{ formatNumber(module.count) }}</div>
                  </div>
                </div>

                <!-- Decorative Arrow -->
                <div v-if="module.to" class="absolute bottom-6 right-6 w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-200 shadow-sm transition-all group-hover:bg-emerald-500 group-hover:text-white border border-slate-50">
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
              </component>
            </div>
          </div>
        </div>

        <!-- RIGHT: Lưu ý + Sức khỏe hệ thống (4 cols) -->
        <div class="xl:col-span-4 space-y-8">

           <!-- THÔNG BÁO / ƯU TIÊN -->
          <div class="rounded-[2.5rem] bg-white border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10 relative overflow-hidden h-fit">
            <div class="flex items-center gap-4 mb-8">
               <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-xl shadow-sm border border-orange-100">
                  <i class="fa-solid fa-bell"></i>
               </div>
               <h2 class="text-[20px] font-headline font-black text-slate-800 tracking-tight leading-tight">Cần xử lý <br><span class="text-slate-400 text-[14px]">trong ngày</span></h2>
            </div>

            <div class="space-y-4">
              <article
                v-for="item in priorities"
                :key="item.title"
                class="rounded-3xl border border-slate-50 p-5 transition-all hover:shadow-sm hover:border-slate-100 flex items-center gap-4 bg-slate-50/30 group"
              >
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border bg-white shadow-sm transition-transform group-hover:scale-105" :class="priorityIconBgClass(item.level)">
                  <i :class="priorityIcon(item.level)"></i>
                </div>
                <div class="flex-1 min-w-0">
                   <h3 class="text-[13px] font-black text-slate-800 mb-0.5 truncate">{{ item.title }}</h3>
                   <span class="text-[9px] font-black uppercase tracking-widest shrink-0" :class="priorityBadgeClass(item.level)">
                      {{ item.level }}
                   </span>
                </div>
                <div class="text-[22px] font-headline font-black text-slate-800">
                  {{ formatNumber(item.count) }}
                </div>
              </article>
            </div>
          </div>

          <!-- SỨC KHỎE HỆ THỐNG / KPI -->
          <div class="rounded-[2.5rem] bg-white border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-10 h-fit">
            <div class="flex items-center gap-4 mb-10">
               <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl shadow-sm border border-rose-100">
                  <i class="fa-solid fa-heart-pulse"></i>
               </div>
               <h2 class="text-[20px] font-headline font-black text-slate-800 tracking-tight">Sức khỏe hệ thống</h2>
            </div>

            <div class="space-y-8">
              <div v-for="item in kpis" :key="item.label" class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-[13px] font-bold text-slate-600">{{ item.label }}</span>
                  <span class="text-[12px] font-black text-emerald-600">{{ item.value }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden shadow-inner border border-slate-200">
                   <!-- Cố định màu xanh nhẹ emerald-400 theo yêu cầu -->
                  <div class="h-full rounded-full transition-all duration-1000 bg-emerald-400" :style="{ width: `${item.value}%` }"></div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession } from '../../utils/auth'

const router = useRouter()

const isLoading = ref(true)
const errorMessage = ref('')
const dashboard = ref({
  generated_at: null,
  summary: { students_total: 0, courses_total: 0, instructors_total: 0, pending_tasks_total: 0, featured_courses_total: 0, latest_content_date: null },
  modules: [], priorities: [], kpis: []
})

const summary = computed(() => dashboard.value.summary || {})
const modules = computed(() => dashboard.value.modules || [])
const priorities = computed(() => dashboard.value.priorities || [])
const kpis = computed(() => dashboard.value.kpis || [])

const overviewCards = computed(() => ([
  {
    label: 'Tổng học viên',
    value: summary.value.students_total,
    description: 'Nguồn thu chính của trung tâm',
    icon: 'fa-solid fa-graduation-cap',
    tokenClass: 'bg-indigo-50 text-indigo-500 border-indigo-100',
  },
  {
    label: 'Khóa học',
    value: summary.value.courses_total,
    description: 'Chương trình đào tạo hiện hành',
    icon: 'fa-solid fa-book',
    tokenClass: 'bg-emerald-50 text-emerald-500 border-emerald-100',
  },
  {
    label: 'Giảng viên',
    value: summary.value.instructors_total,
    description: 'Nguồn nhân lực giảng dạy',
    icon: 'fa-solid fa-user-tie',
    tokenClass: 'bg-sky-50 text-sky-500 border-sky-100',
  },
  {
    label: 'Cần xử lý',
    value: summary.value.pending_tasks_total,
    description: 'Việc cần theo dõi trong ngày',
    icon: 'fa-solid fa-bell',
    tokenClass: 'bg-orange-50 text-orange-500 border-orange-100',
  },
]))

const syncLabel = computed(() => {
  if (!dashboard.value.generated_at) return 'Đang cập nhật...'
  return `Cập nhật lúc ${formatDateTime(dashboard.value.generated_at)}`
})

const loadDashboard = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await apiFetch('admin/dashboard.php')
    if (response.status === 401 || response.status === 403) {
      clearAuthSession()
      router.push('/login')
      return
    }
    const result = await response.json()
    if (result.status === 'success') {
      dashboard.value = result.data
    } else {
      errorMessage.value = result.message || 'Không tải được dữ liệu dashboard.'
    }
  } catch (error) {
    console.error('Lỗi tải bảng đk:', error)
    errorMessage.value = 'Lỗi kết nối máy chủ.'
  } finally {
    isLoading.value = false
  }
}

const formatNumber = (value) => new Intl.NumberFormat('vi-VN').format(Number(value || 0))
const formatDateTime = (value) => {
  if (!value) return ''
  return new Intl.DateTimeFormat('vi-VN', { hour: '2-digit', minute: '2-digit' }).format(new Date(value))
}

const moduleIcon = (key) => {
  const icons = {
    users: 'fa-solid fa-users-gear', courses: 'fa-solid fa-book-open',
    categories: 'fa-solid fa-layer-group', consultations: 'fa-solid fa-headset',
    contacts: 'fa-solid fa-comments', website_contents: 'fa-solid fa-globe',
    learning_paths: 'fa-solid fa-route', statistics: 'fa-solid fa-chart-pie',
    assignments: 'fa-solid fa-file-signature', instructors: 'fa-solid fa-user-tie',
  }
  return icons[key] || 'fa-solid fa-folder'
}

const moduleCardClass = (accent) => {
  const base = 'border-slate-200 bg-slate-50/50'
  const classes = {
    emerald: 'border-emerald-100 bg-emerald-50/30 hover:bg-emerald-50/80',
    sky: 'border-sky-100 bg-sky-50/30 hover:bg-sky-50/80',
    violet: 'border-violet-100 bg-violet-50/30 hover:bg-violet-50/80',
    orange: 'border-orange-100 bg-orange-50/30 hover:bg-orange-50/80',
    rose: 'border-rose-100 bg-rose-50/30 hover:bg-rose-50/80',
    indigo: 'border-indigo-100 bg-indigo-50/30 hover:bg-indigo-50/80',
  }
  return classes[accent] || base
}

const moduleIconClass = (accent) => {
  const classes = {
    emerald: 'text-emerald-500', sky: 'text-sky-500', violet: 'text-violet-500',
    orange: 'text-orange-500', rose: 'text-rose-500', indigo: 'text-indigo-500',
  }
  return classes[accent] || 'text-slate-500'
}

const priorityBadgeClass = (level) => {
  if (level === 'Ưu tiên') return 'bg-red-100 text-red-600'
  if (level === 'Theo dõi') return 'bg-amber-100 text-amber-600'
  return 'bg-emerald-100 text-emerald-600'
}

const priorityIconBgClass = (level) => {
  if (level === 'Ưu tiên') return 'bg-red-50 border-red-100 text-red-500'
  if (level === 'Theo dõi') return 'bg-amber-50 border-amber-100 text-amber-500'
  return 'bg-emerald-50 border-emerald-100 text-emerald-500'
}

const priorityIcon = (level) => {
  if (level === 'Ưu tiên') return 'fa-solid fa-circle-exclamation'
  if (level === 'Theo dõi') return 'fa-solid fa-eye'
  return 'fa-solid fa-bell'
}

const getKpiColorGrad = (value) => {
  return 'bg-emerald-400'
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap");

.font-headline {
  font-family: "Manrope", sans-serif;
}
.font-body {
  font-family: "Inter", sans-serif;
}

/* Shadow card premium */
.ambient-shadow {
  box-shadow: 0 55px 150px -25px rgba(26, 28, 27, 0.08);
}

/* Hide scrollbar */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

/* Line clamp utility */
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Grid layout fix */
.xl\:grid-cols-12 {
  grid-template-columns: repeat(12, minmax(0, 1fr));
}
</style>
