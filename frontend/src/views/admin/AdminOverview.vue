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
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <article
          v-for="card in overviewCards"
          :key="card.label"
          class="relative overflow-hidden rounded-[1.5rem] bg-white p-6 border border-slate-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)] transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:-translate-y-1 group"
        >
          <div class="flex justify-between items-start">
            <div>
              <p class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 mb-2">{{ card.label }}</p>
              <h3 class="text-4xl font-black text-slate-800">{{ card.value }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3" :class="card.iconClass">
              <i :class="card.icon"></i>
            </div>
          </div>
          <p class="mt-4 text-xs font-medium text-slate-500">{{ card.description }}</p>
          <div class="absolute bottom-0 left-0 w-full h-1 opacity-0 group-hover:opacity-100 transition-opacity" :class="card.borderClass"></div>
        </article>
      </section>

      <!-- Main Layout 2 Cột -->
      <section class="grid gap-8 xl:grid-cols-[2fr_1fr]">
        
        <!-- CÁC MODULE QUẢN LÝ -->
        <div class="rounded-[2rem] bg-white border border-slate-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)] p-6 md:p-8">
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-xl font-black text-slate-800">Cấu trúc Trung tâm</h2>
              <p class="text-sm font-medium text-slate-500 mt-1">Quản lý và thiết lập các danh mục tính năng.</p>
            </div>
            <span class="rounded-full bg-slate-50 border border-slate-200 px-4 py-1.5 text-xs font-bold text-slate-600">{{ modules.length }} Mục</span>
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <component
              :is="module.to ? 'router-link' : 'div'"
              v-for="module in modules"
              :key="module.key"
              :to="module.to"
              class="relative p-5 rounded-[1.25rem] border transition-all duration-300 outline-none flex flex-col justify-between overflow-hidden"
              :class="[
                module.to ? 'cursor-pointer hover:shadow-md hover:-translate-y-1 group hover:border-transparent focus:ring-2 focus:ring-emerald-400' : '',
                moduleCardClass(module.accent)
              ]"
            >
              <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-11 h-11 rounded-xl bg-white shadow-sm border border-slate-50 flex items-center justify-center text-lg transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3" :class="moduleIconClass(module.accent)">
                  <i :class="moduleIcon(module.key)"></i>
                </div>
                <div class="text-[0.6rem] font-bold uppercase tracking-widest px-2.5 py-1 rounded-md bg-white border border-slate-100 text-slate-500 shadow-sm">
                  {{ module.unit }}
                </div>
              </div>
              
              <div class="relative z-10">
                <h3 class="text-lg font-black text-slate-800 group-hover:text-emerald-700 transition-colors">{{ module.title }}</h3>
                <p class="text-[0.7rem] font-medium text-slate-500 mt-1 line-clamp-1">{{ module.description }}</p>
              </div>

              <div class="mt-5 flex items-end justify-between relative z-10">
                <div class="text-3xl font-black text-slate-800">{{ formatNumber(module.count) }}</div>
                <div v-if="module.to" class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-300 shadow-sm transition-colors group-hover:bg-[#7AE582] group-hover:text-slate-900 border border-slate-100">
                   <i class="fa-solid fa-arrow-right text-xs"></i>
                </div>
              </div>
            </component>
          </div>
        </div>

        <!-- CỘT BÊN PHẢI -->
        <div class="space-y-8">

           <!-- THÔNG BÁO / ƯU TIÊN -->
          <div class="rounded-[2rem] bg-white border border-slate-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)] p-6 md:p-8 relative overflow-hidden">
            <!-- Cam mờ trang trí -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-orange-400 opacity-5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center gap-3 mb-6 relative z-10">
               <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-lg shadow-sm border border-orange-100">
                  <i class="fa-solid fa-bell"></i>
               </div>
               <h2 class="text-xl font-black text-slate-800">Cần lưu ý hôm nay</h2>
            </div>

            <div class="space-y-4 relative z-10">
              <article
                v-for="item in priorities"
                :key="item.title"
                class="rounded-[1.25rem] border border-slate-100 p-4 transition-colors hover:bg-slate-50 flex items-center gap-4 bg-white shadow-sm"
              >
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 border" :class="priorityIconBgClass(item.level)">
                  <i :class="priorityIcon(item.level)"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-sm font-black text-slate-800 truncate">{{ item.title }}</h3>
                    <span class="px-2 py-0.5 rounded text-[0.6rem] font-bold uppercase tracking-widest shrink-0" :class="priorityBadgeClass(item.level)">
                      {{ item.level }}
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 truncate">{{ item.description }}</p>
                </div>
                <div class="text-xl font-black text-slate-800 pl-2">
                  {{ formatNumber(item.count) }}
                </div>
              </article>

              <div v-if="priorities.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-emerald-50 border border-emerald-100 text-[#16a34a] rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                  <i class="fa-solid fa-check-double text-2xl"></i>
                </div>
                <p class="text-sm font-black text-slate-800">Tuyệt vời!</p>
                <p class="text-[0.8rem] font-medium text-slate-500 mt-1">Chưa có vấn đề khẩn cấp nào cần xử lý.</p>
              </div>
            </div>
          </div>

          <!-- SỨC KHỎE HỆ THỐNG / KPI -->
          <div class="rounded-[2rem] bg-white border border-slate-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)] p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
               <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-lg shadow-sm border border-rose-100">
                  <i class="fa-solid fa-heart-pulse"></i>
               </div>
               <h2 class="text-xl font-black text-slate-800">Sức khỏe hệ thống</h2>
            </div>

            <div class="space-y-6">
              <div v-for="item in kpis" :key="item.label">
                <div class="flex justify-between items-center text-sm mb-2">
                  <span class="font-bold text-slate-600">{{ item.label }}</span>
                  <span class="font-black text-slate-800">{{ item.value }}%</span>
                </div>
                <div class="h-3 rounded-full bg-slate-100 overflow-hidden shadow-inner border border-slate-200">
                  <div class="h-full rounded-full transition-all duration-1000" :class="getKpiColorGrad(item.value)" :style="{ width: `${item.value}%` }"></div>
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
    value: formatNumber(summary.value.students_total),
    description: 'Nguồn thu chính của trung tâm',
    icon: 'fa-solid fa-user-graduate',
    iconClass: 'bg-indigo-50 text-indigo-500 border border-indigo-100',
    borderClass: 'bg-indigo-400'
  },
  {
    label: 'Khóa học',
    value: formatNumber(summary.value.courses_total),
    description: 'Chương trình đào tạo hiện hành',
    icon: 'fa-solid fa-book-open',
    iconClass: 'bg-emerald-50 text-emerald-500 border border-emerald-100',
    borderClass: 'bg-emerald-400'
  },
  {
    label: 'Giảng viên',
    value: formatNumber(summary.value.instructors_total),
    description: 'Nguồn nhân lực giảng dạy',
    icon: 'fa-solid fa-chalkboard-user',
    iconClass: 'bg-sky-50 text-sky-500 border border-sky-100',
    borderClass: 'bg-sky-400'
  },
  {
    label: 'Cần xử lý',
    value: formatNumber(summary.value.pending_tasks_total),
    description: 'Việc cần theo dõi trong ngày',
    icon: 'fa-solid fa-bell',
    iconClass: 'bg-orange-50 text-orange-500 border border-orange-100',
    borderClass: 'bg-orange-400'
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
  if (value < 50) return 'bg-gradient-to-r from-rose-400 to-red-500'
  if (value < 80) return 'bg-gradient-to-r from-amber-400 to-orange-400'
  return 'bg-gradient-to-r from-[#7AE582] to-emerald-500'
}

onMounted(() => {
  loadDashboard()
})
</script>
