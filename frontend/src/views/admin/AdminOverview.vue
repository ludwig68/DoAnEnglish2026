<template>
  <div class="space-y-6 p-4 md:p-6">
    <div v-if="isLoading" class="flex min-h-[420px] items-center justify-center rounded-[2rem] bg-white">
      <div class="text-center text-slate-500">
        <div class="mx-auto mb-4 h-14 w-14 animate-spin rounded-full border-4 border-emerald-100 border-t-[#16a34a]"></div>
        <p>Đang tải số liệu quản trị...</p>
      </div>
    </div>

    <template v-else>
      <div v-if="errorMessage" class="rounded-[1.75rem] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
        {{ errorMessage }}
      </div>

      <section class="overflow-hidden rounded-[2rem] border border-emerald-100 bg-gradient-to-br from-[#f6fff7] via-white to-[#edf8ff]">
        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[minmax(0,1.2fr)_320px] lg:px-8 lg:py-8">
          <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-bold text-[#16a34a] shadow-sm">
              <i class="fa-solid fa-sparkles"></i> Hello, L u d w i g :)
            </div>
            <h1 class="mt-5 max-w-3xl text-3xl font-black leading-tight tracking-tight text-slate-900 md:text-4xl">
              Theo dõi công việc quản lý hằng ngày bằng số liệu thực từ hệ thống.
            </h1>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-600 md:text-base">
              Bạn đang quản lý {{ summary.students_total }} học viên, {{ summary.courses_total }} khóa học và {{ summary.instructors_total }} giảng viên.
              Hiện có {{ summary.pending_tasks_total }} việc cần lưu ý từ tư vấn và liên hệ.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
              <router-link
                to="/admin/users"
                class="inline-flex items-center gap-2 rounded-full bg-[#7AE582] px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-emerald-300"
              >
                Mở quản lý tài khoản <i class="fa-solid fa-arrow-right"></i>
              </router-link>
              <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 shadow-sm">
                <i class="fa-solid fa-clock"></i> {{ syncLabel }}
              </span>
            </div>
          </div>

          <div class="grid gap-4">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
              <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Tình trạng hệ thống</p>
              <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                  <p class="text-xs text-slate-500">Khóa học nổi bật</p>
                  <p class="mt-1 text-3xl font-black text-slate-900">{{ summary.featured_courses_total }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                  <p class="text-xs text-slate-500">Nội dung website</p>
                  <p class="mt-1 text-3xl font-black text-slate-900">{{ websiteContentCount }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-[1.75rem] bg-gradient-to-br from-[#7AE582] to-[#c9f7d2] p-5 shadow-sm">
              <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-700">Lưu ý hiện tại</p>
              <h2 class="mt-3 text-2xl font-black leading-tight text-slate-900">
                {{ summary.pending_tasks_total > 0 ? `${summary.pending_tasks_total} việc cần kiểm tra ngay hôm nay.` : 'Hiện không có việc gấp cần xử lý.' }}
              </h2>
              <p class="mt-3 text-sm leading-relaxed text-slate-700">
                {{ summary.latest_content_date ? `Nội dung website được cập nhật lần gần nhất vào ${formatDateTime(summary.latest_content_date)}.` : 'Chưa có lịch sử cập nhật nội dung website.' }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article
          v-for="card in overviewCards"
          :key="card.label"
          class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ card.label }}</p>
              <p class="mt-3 text-4xl font-black tracking-tight text-slate-900">{{ card.value }}</p>
              <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ card.description }}</p>
            </div>
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl text-xl" :class="card.iconClass">
              <i :class="card.icon"></i>
            </span>
          </div>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_360px]">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm md:p-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Các khu vực quản lý</p>
              <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Số liệu thực theo từng nhóm công việc</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600">{{ modules.length }} mục</span>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <component
              :is="module.to ? 'router-link' : 'div'"
              v-for="module in modules"
              :key="module.key"
              :to="module.to"
              class="rounded-[1.6rem] border p-5 transition"
              :class="[module.to ? 'cursor-pointer hover:-translate-y-1 hover:shadow-md' : '', moduleCardClass(module.accent)]"
            >
              <div class="flex items-start justify-between gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-lg" :class="moduleIconClass(module.accent)">
                  <i :class="moduleIcon(module.key)"></i>
                </span>
                <span class="rounded-full bg-white/70 px-3 py-1 text-[0.68rem] font-bold uppercase tracking-[0.16em] text-slate-600">
                  {{ module.unit }}
                </span>
              </div>
              <h3 class="mt-4 text-lg font-black tracking-tight text-slate-900">{{ module.title }}</h3>
              <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ module.description }}</p>
              <div class="mt-4 text-3xl font-black text-slate-900">{{ formatNumber(module.count) }}</div>
            </component>
          </div>
        </div>

        <div class="space-y-6">
          <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Cần lưu ý hôm nay</p>
            <div class="mt-5 space-y-4">
              <article
                v-for="item in priorities"
                :key="item.title"
                class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4"
              >
                <div class="flex items-center justify-between gap-3">
                  <h3 class="text-sm font-black text-slate-900">{{ item.title }}</h3>
                  <span class="rounded-full px-3 py-1 text-[0.68rem] font-bold uppercase tracking-[0.16em]" :class="priorityBadgeClass(item.level)">
                    {{ item.level }}
                  </span>
                </div>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ formatNumber(item.count) }}</p>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ item.description }}</p>
              </article>
            </div>
          </section>

          <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Chỉ số vận hành</p>
            <div class="mt-5 space-y-4">
              <div v-for="item in kpis" :key="item.label">
                <div class="mb-2 flex items-center justify-between text-sm">
                  <span class="font-semibold text-slate-600">{{ item.label }}</span>
                  <span class="font-black text-slate-900">{{ item.value }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-500" :style="{ width: `${item.value}%` }"></div>
                </div>
              </div>
            </div>
          </section>
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
  summary: {
    students_total: 0,
    courses_total: 0,
    instructors_total: 0,
    pending_tasks_total: 0,
    featured_courses_total: 0,
    latest_content_date: null,
  },
  modules: [],
  priorities: [],
  kpis: [],
})

const summary = computed(() => dashboard.value.summary || {})
const modules = computed(() => dashboard.value.modules || [])
const priorities = computed(() => dashboard.value.priorities || [])
const kpis = computed(() => dashboard.value.kpis || [])
const websiteContentCount = computed(() => {
  const contentModule = modules.value.find((item) => item.key === 'website_contents')
  return contentModule?.count ?? 0
})

const overviewCards = computed(() => ([
  {
    label: 'Tổng học viên',
    value: formatNumber(summary.value.students_total),
    description: 'Số lượng học viên hiện có trong hệ thống.',
    icon: 'fa-solid fa-user-graduate',
    iconClass: 'bg-indigo-50 text-indigo-600',
  },
  {
    label: 'Khóa học',
    value: formatNumber(summary.value.courses_total),
    description: 'Tổng số khóa học đang được quản lý.',
    icon: 'fa-solid fa-book-open',
    iconClass: 'bg-emerald-50 text-[#16a34a]',
  },
  {
    label: 'Giảng viên',
    value: formatNumber(summary.value.instructors_total),
    description: 'Số giảng viên đang có trên hệ thống.',
    icon: 'fa-solid fa-chalkboard-user',
    iconClass: 'bg-cyan-50 text-cyan-600',
  },
  {
    label: 'Việc cần xử lý',
    value: formatNumber(summary.value.pending_tasks_total),
    description: 'Bao gồm đăng ký tư vấn và tin nhắn liên hệ chưa xử lý.',
    icon: 'fa-solid fa-bell',
    iconClass: 'bg-orange-50 text-orange-600',
  },
]))

const syncLabel = computed(() => {
  if (!dashboard.value.generated_at) return 'Đang đồng bộ dữ liệu'
  return `Cập nhật ${formatDateTime(dashboard.value.generated_at)}`
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
    console.error('Lỗi tải admin dashboard:', error)
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

const formatNumber = (value) => new Intl.NumberFormat('vi-VN').format(Number(value || 0))

const formatDateTime = (value) => {
  if (!value) return 'chưa có dữ liệu'
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}

const moduleIcon = (key) => {
  const icons = {
    users: 'fa-solid fa-users-gear',
    courses: 'fa-solid fa-book-open',
    categories: 'fa-solid fa-layer-group',
    consultations: 'fa-solid fa-headset',
    contacts: 'fa-solid fa-comments',
    website_contents: 'fa-solid fa-window-maximize',
    learning_paths: 'fa-solid fa-route',
    statistics: 'fa-solid fa-chart-column',
    assignments: 'fa-solid fa-file-pen',
    instructors: 'fa-solid fa-chalkboard-user',
  }
  return icons[key] || 'fa-solid fa-circle'
}

const moduleCardClass = (accent) => {
  const classes = {
    emerald: 'border-emerald-200 bg-emerald-50/60',
    sky: 'border-sky-200 bg-sky-50/70',
    violet: 'border-violet-200 bg-violet-50/70',
    orange: 'border-orange-200 bg-orange-50/70',
    rose: 'border-rose-200 bg-rose-50/70',
    cyan: 'border-cyan-200 bg-cyan-50/70',
    lime: 'border-lime-200 bg-lime-50/70',
    indigo: 'border-indigo-200 bg-indigo-50/70',
    fuchsia: 'border-fuchsia-200 bg-fuchsia-50/70',
    teal: 'border-teal-200 bg-teal-50/70',
  }
  return classes[accent] || 'border-slate-200 bg-slate-50/70'
}

const moduleIconClass = (accent) => {
  const classes = {
    emerald: 'bg-white text-emerald-700',
    sky: 'bg-white text-sky-700',
    violet: 'bg-white text-violet-700',
    orange: 'bg-white text-orange-700',
    rose: 'bg-white text-rose-700',
    cyan: 'bg-white text-cyan-700',
    lime: 'bg-white text-lime-700',
    indigo: 'bg-white text-indigo-700',
    fuchsia: 'bg-white text-fuchsia-700',
    teal: 'bg-white text-teal-700',
  }
  return classes[accent] || 'bg-white text-slate-700'
}

const priorityBadgeClass = (level) => {
  if (level === 'Ưu tiên') return 'bg-red-100 text-red-700'
  if (level === 'Theo dõi') return 'bg-sky-100 text-sky-700'
  return 'bg-emerald-100 text-emerald-700'
}

onMounted(() => {
  loadDashboard()
})
</script>
