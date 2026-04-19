<template>
  <div class="px-10 py-8 min-h-screen">

    <!-- ── Back + Header ── -->
    <div class="mb-8">
      <button @click="$router.push('/teacher/classes')" class="flex items-center gap-2 text-xs font-black uppercase tracking-[0.15em] text-slate-400 hover:text-emerald-500 transition-colors mb-6">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách lớp
      </button>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-500 mb-2">Chi tiết lớp học</p>
          <h1 class="text-3xl font-headline font-black text-slate-900 tracking-tight leading-none">
            {{ className || 'Đang tải...' }}
          </h1>
        </div>
        <!-- Quick Stats Badges -->
        <div class="flex items-center gap-3">
          <div class="px-5 py-2.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-0.5">Học viên</p>
            <p class="text-xl font-headline font-black text-slate-900">{{ students.length }}</p>
          </div>
          <div class="px-5 py-2.5 rounded-2xl bg-blue-50 border border-blue-100 text-center">
            <p class="text-[9px] font-black uppercase tracking-widest text-blue-500 mb-0.5">Điểm danh TB</p>
            <p class="text-xl font-headline font-black text-slate-900">{{ avgAttendance }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-if="isLoading" class="flex items-center justify-center py-32">
      <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin"></div>
        <p class="text-xs font-black uppercase tracking-widest text-slate-400 animate-pulse">Đang tải dữ liệu...</p>
      </div>
    </div>

    <template v-else>
      <!-- ── Tabs ── -->
      <div class="flex items-center gap-1 mb-8 border-b border-slate-100">
        <button v-for="tab in tabs" :key="tab.value"
          @click="activeTab = tab.value"
          class="px-6 py-3.5 text-sm font-black tracking-tight transition-all relative"
          :class="activeTab === tab.value
            ? 'text-slate-900 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-emerald-500 after:rounded-full'
            : 'text-slate-400 hover:text-slate-600'">
          {{ tab.label }}
        </button>
      </div>

      <!-- ══ TAB: Danh sách học viên ══ -->
      <div v-if="activeTab === 'students'" class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
          <h2 class="font-headline font-black text-xl text-slate-900">Danh sách học viên</h2>
          <span class="text-xs font-black text-slate-400 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
            {{ students.length }} học viên
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 border-b border-slate-50">
                <th class="text-left px-8 py-5">Student Name</th>
                <th class="text-left px-4 py-5">ID</th>
                <th class="text-left px-4 py-5 w-48">Attendance</th>
                <th class="text-left px-4 py-5">Grade</th>
                <th class="text-left px-4 py-5">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in students" :key="student.id"
                class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                <td class="px-8 py-5">
                  <div class="flex items-center gap-4">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(student.full_name)}&background=d1fae5&color=059669&bold=true&size=40`"
                      class="w-10 h-10 rounded-2xl object-cover shadow-sm" :alt="student.full_name">
                    <div>
                      <p class="font-headline font-black text-[14px] text-slate-900 leading-none">{{ student.full_name }}</p>
                      <p class="text-[11px] text-slate-400 font-medium mt-1">{{ student.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-5">
                  <span class="text-xs font-black text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    #ST{{ String(student.id).padStart(5, '0') }}
                  </span>
                </td>
                <td class="px-4 py-5">
                  <div class="flex items-center gap-3">
                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden w-24">
                      <div class="h-full rounded-full transition-all"
                        :class="student.attendance_pct >= 80 ? 'bg-emerald-500' : student.attendance_pct >= 60 ? 'bg-amber-400' : 'bg-red-400'"
                        :style="`width: ${student.attendance_pct}%`"></div>
                    </div>
                    <span class="text-xs font-black"
                      :class="student.attendance_pct >= 80 ? 'text-emerald-500' : student.attendance_pct >= 60 ? 'text-amber-500' : 'text-red-500'">
                      {{ student.attendance_pct }}%
                    </span>
                  </div>
                </td>
                <td class="px-4 py-5">
                  <span class="font-headline font-black text-[15px] text-slate-900">
                    {{ student.avg_grade !== null ? student.avg_grade : '–' }}
                  </span>
                </td>
                <td class="px-4 py-5">
                  <span class="px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wide"
                    :class="{
                      'bg-emerald-50 text-emerald-600 border border-emerald-100': student.status === 'active',
                      'bg-amber-50 text-amber-600 border border-amber-100': student.status === 'warning',
                      'bg-red-50 text-red-600 border border-red-100': student.status === 'at-risk',
                    }">
                    {{ student.status === 'active' ? 'Active' : student.status === 'warning' ? 'Warning' : 'At Risk' }}
                  </span>
                </td>
              </tr>
              <tr v-if="students.length === 0">
                <td colspan="5" class="text-center py-16 text-slate-400 font-bold">Chưa có học viên nào.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="students.length > 0" class="px-8 py-5 border-t border-slate-50 text-center">
          <button class="text-sm font-black text-emerald-500 hover:text-emerald-600 transition-colors">
            Xem tất cả {{ students.length }} học viên <i class="fa-solid fa-arrow-down ml-1 text-xs"></i>
          </button>
        </div>
      </div>

      <!-- ══ TAB: Điểm danh ══ -->
      <div v-else-if="activeTab === 'attendance'" class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm p-8">
        <h2 class="font-headline font-black text-xl text-slate-900 mb-6">Lịch sử điểm danh</h2>
        <div v-if="schedules.length === 0" class="text-center py-16 text-slate-400 font-bold">
          Chưa có buổi học nào được ghi nhận.
        </div>
        <div v-else class="space-y-4">
          <div v-for="s in schedules" :key="s.id"
            class="flex items-center justify-between p-5 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all">
            <div class="flex items-center gap-5">
              <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                :class="s.status === 'completed' ? 'bg-emerald-50 text-emerald-500' : s.status === 'canceled' ? 'bg-red-50 text-red-400' : 'bg-slate-50 text-slate-400'">
                <i :class="s.status === 'completed' ? 'fa-solid fa-circle-check' : s.status === 'canceled' ? 'fa-solid fa-circle-xmark' : 'fa-regular fa-calendar'"></i>
              </div>
              <div>
                <p class="font-headline font-black text-[14px] text-slate-900">
                  {{ new Date(s.study_date).toLocaleDateString('vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </p>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                  {{ s.start_time }} – {{ s.end_time }} ·
                  <span :class="s.teaching_type === 'online' ? 'text-blue-500' : 'text-emerald-500'">
                    {{ s.teaching_type === 'online' ? 'Online' : 'Offline' }}
                  </span>
                  <span v-if="s.room_info" class="mx-1">·</span>
                  <span v-if="s.room_info">{{ s.room_info }}</span>
                </p>
              </div>
            </div>
            <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wide"
              :class="{
                'bg-emerald-50 text-emerald-600 border border-emerald-100': s.status === 'completed',
                'bg-red-50 text-red-500 border border-red-100': s.status === 'canceled',
                'bg-slate-50 text-slate-500 border border-slate-100': s.status === 'scheduled',
              }">
              {{ s.status === 'completed' ? 'Đã xong' : s.status === 'canceled' ? 'Đã hủy' : 'Sắp tới' }}
            </span>
          </div>
        </div>
      </div>

      <!-- ══ TAB: Bảng điểm ══ -->
      <div v-else-if="activeTab === 'grades'" class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm p-8">
        <div class="flex items-center justify-center flex-col py-24 text-slate-400">
          <i class="fa-solid fa-chart-bar text-5xl mb-4 opacity-30"></i>
          <p class="font-black text-lg">Bảng điểm & Bài tập</p>
          <p class="text-sm mt-1 opacity-70">Chức năng đang được phát triển</p>
        </div>
      </div>

      <!-- ══ TAB: Tài liệu ══ -->
      <div v-else-if="activeTab === 'resources'" class="space-y-6">
        <div v-if="isLoadingMaterials" class="flex items-center justify-center py-20">
          <div class="h-6 w-6 animate-spin rounded-full border-2 border-emerald-200 border-t-emerald-500"></div>
          <span class="ml-2 text-sm text-slate-400">Đang tải tài liệu...</span>
        </div>
        
        <div v-else-if="materialsData.length === 0" class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm p-8">
          <div class="flex items-center justify-center flex-col py-24 text-slate-400">
            <i class="fa-solid fa-folder-open text-5xl mb-4 opacity-30"></i>
            <p class="font-black text-lg">Tài liệu lớp học</p>
            <p class="text-sm mt-1 opacity-70">Hiện không có tài liệu nào diễn ra trong khóa học này.</p>
          </div>
        </div>

        <div v-else v-for="course in materialsData" :key="course.course_id" class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden p-8 space-y-8">
          <!-- Tài liệu chung của khóa -->
          <div v-if="course.general_materials && course.general_materials.length > 0">
            <h3 class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400 mb-4 flex items-center gap-2">
              <i class="fa-solid fa-folder text-emerald-400"></i> Tài liệu chung
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <FileCard v-for="mat in course.general_materials" :key="mat.id" :mat="mat" />
            </div>
          </div>

          <!-- Tài liệu theo từng Bài học -->
          <div v-for="lesson in course.lessons" :key="lesson.lesson_id" class="space-y-4">
             <h3 class="text-sm font-black text-slate-700 flex items-center gap-2 py-2 border-b border-slate-100 border-dashed">
               <i class="fa-solid fa-layer-group text-emerald-400 text-xs"></i> 
               {{ lesson.lesson_title }}
               <span class="text-[10px] font-black bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full ml-2">{{ lesson.materials.length }}</span>
             </h3>
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
               <FileCard v-for="mat in lesson.materials" :key="mat.id" :mat="mat" />
             </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { notifyError } from '../../utils/notify'
import FileCard from './components/FileCard.vue'

const route = useRoute()
const classId = route.params.id

const isLoading = ref(true)
const isLoadingMaterials = ref(false)
const className = ref('')
const students = ref([])
const schedules = ref([])
const materialsData = ref([])
const activeTab = ref('students')

const tabs = [
  { label: 'Danh sách học viên', value: 'students' },
  { label: 'Điểm danh', value: 'attendance' },
  { label: 'Bảng điểm & Bài tập', value: 'grades' },
  { label: 'Tài liệu lớp học', value: 'resources' },
]

const avgAttendance = computed(() => {
  if (!students.value.length) return 0
  const total = students.value.reduce((sum, s) => sum + (s.attendance_pct || 0), 0)
  return Math.round(total / students.value.length)
})

const loadDetail = async () => {
  isLoading.value = true
  try {
    const res = await apiFetch(`teacher/classes.php?class_id=${classId}`)
    const result = await res.json()
    if (result.status === 'success') {
      className.value = result.class_name
      students.value = result.students
      schedules.value = result.schedules
    } else {
      notifyError(result.message || 'Không thể tải chi tiết lớp.')
    }
  } catch (err) {
    notifyError('Lỗi kết nối máy chủ.')
  } finally {
    isLoading.value = false
  }
}

const loadMaterials = async () => {
  isLoadingMaterials.value = true
  try {
    const res = await apiFetch(`teacher/materials.php?class_id=${classId}`)
    const result = await res.json()
    if (result.status === 'success') {
      materialsData.value = result.data || []
    }
  } catch (error) {
    console.error("Lỗi tải tài liệu:", error)
  } finally {
    isLoadingMaterials.value = false
  }
}

watch(activeTab, (newVal) => {
  if (newVal === 'resources' && materialsData.value.length === 0) {
    loadMaterials()
  }
})

onMounted(loadDetail)
</script>
