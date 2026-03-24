<template>
  <div class="min-h-screen bg-slate-50 pb-16">
    <div v-if="isProfileModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/35 px-4 py-6">
      <div class="w-full max-w-2xl rounded-[2rem] bg-white shadow-2xl shadow-slate-950/20">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
          <div>
            <h2 class="text-xl font-bold text-slate-900">Thông tin cá nhân</h2>
            <p class="mt-1 text-sm text-slate-500">Cập nhật thông tin để tài khoản của bạn luôn chính xác.</p>
          </div>
          <button
            type="button"
            @click="closeProfileModal"
            class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="submitProfileUpdate" class="px-6 py-6">
          <div v-if="profileMessage" :class="profileMessageClass" class="mb-5 rounded-2xl px-4 py-3 text-sm font-medium">
            {{ profileMessage }}
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Họ và tên</span>
              <input
                v-model="profileForm.full_name"
                type="text"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#16a34a] focus:bg-white"
                placeholder="Nhập họ và tên"
              >
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Email</span>
              <input
                v-model="profileForm.email"
                type="email"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#16a34a] focus:bg-white"
                placeholder="email@example.com"
              >
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Số điện thoại</span>
              <input
                v-model="profileForm.phone"
                type="text"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#16a34a] focus:bg-white"
                placeholder="Nhập số điện thoại"
              >
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-semibold text-slate-700">Vai trò</span>
              <input
                :value="formatRole(user.role)"
                type="text"
                disabled
                class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500"
              >
            </label>
          </div>

          <label class="mt-5 block">
            <span class="mb-2 block text-sm font-semibold text-slate-700">Mật khẩu mới</span>
            <input
              v-model="profileForm.password"
              type="password"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#16a34a] focus:bg-white"
              placeholder="Để trống nếu chưa muốn đổi"
            >
            <span class="mt-2 block text-xs text-slate-400">Chỉ nhập khi bạn muốn đổi mật khẩu đăng nhập.</span>
          </label>

          <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button
              type="button"
              @click="closeProfileModal"
              class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
            >
              Đóng
            </button>
            <button
              type="submit"
              :disabled="isSavingProfile"
              class="inline-flex items-center justify-center rounded-full bg-[#7AE582] px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-emerald-300 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <span v-if="isSavingProfile">Đang lưu...</span>
              <span v-else>Lưu thay đổi</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-lime-50 pt-12 pb-20">
      <div class="absolute inset-0 opacity-60">
        <div class="absolute top-0 left-0 h-80 w-80 rounded-full bg-emerald-200 blur-[120px]"></div>
        <div class="absolute right-0 bottom-0 h-96 w-96 rounded-full bg-lime-100 blur-[140px]"></div>
      </div>

      <div class="relative max-w-6xl mx-auto px-4">
        <div v-if="isLoading" class="min-h-[40vh] flex flex-col items-center justify-center text-slate-500">
          <div class="w-16 h-16 rounded-full border-4 border-emerald-100 border-t-[#16a34a] animate-spin mb-4"></div>
          <p>Đang tải trang học tập của bạn...</p>
        </div>

        <template v-else>
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
            <div class="max-w-3xl">
              <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-sm font-bold text-[#16a34a] shadow-sm">
                <i class="fa-solid fa-sparkles"></i> Góc học tập của bạn
              </span>
              <h1 class="mt-5 text-4xl md:text-5xl font-extrabold leading-tight text-slate-900">
                Chào {{ firstName }},
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-[#7AE582] to-emerald-300">
                  tiếp tục hành trình của bạn
                </span>
              </h1>
              <p class="mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                Xem nhanh các khóa học đang học, tiến độ của bạn và kết quả gần đây để tiếp tục học thuận tiện hơn mỗi ngày.
              </p>

              <div class="mt-8 flex flex-wrap gap-3">
                <router-link to="/courses" class="inline-flex items-center gap-2 rounded-full bg-[#7AE582] px-6 py-3 font-bold text-slate-900 shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-300">
                  Khám phá khóa học <i class="fa-solid fa-arrow-right"></i>
                </router-link>
                <router-link to="/" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-6 py-3 font-bold text-slate-700 transition hover:bg-slate-50">
                  Về trang chủ
                </router-link>
              </div>
            </div>

            <div class="w-full max-w-xl lg:pt-2 lg:self-start">
              <div class="flex items-center justify-end gap-2">
                <span class="inline-flex rounded-full border border-emerald-200 bg-white/80 px-4 py-1.5 text-sm font-bold text-[#16a34a] shadow-sm">
                  {{ formatRole(user.role) }}
                </span>

                <div class="flex items-center gap-3">
                  <div ref="notificationMenuRef" class="relative">
                    <button
                      type="button"
                      @click="toggleNotificationMenu"
                      class="flex h-14 w-14 items-center justify-center rounded-full border-2 border-white bg-white text-slate-700 shadow-lg shadow-emerald-950/10 transition hover:bg-emerald-50"
                    >
                      <i class="fa-regular fa-bell text-xl"></i>
                    </button>

                    <div
                      v-if="isNotificationMenuOpen"
                      class="absolute right-0 top-[calc(100%+10px)] z-30 w-72 rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-950/20"
                    >
                      <p class="text-sm font-bold text-slate-800">Thông báo</p>
                      <p class="mt-2 text-sm text-slate-500">Không có thông báo gì.</p>
                    </div>
                  </div>

                  <div ref="profileMenuRef" class="relative">
                    <button
                      type="button"
                      @click="toggleProfileMenu"
                      class="flex h-14 w-14 items-center justify-center rounded-full border-2 border-white bg-white text-slate-700 shadow-lg shadow-emerald-950/10 transition hover:bg-emerald-50"
                    >
                      <img class="h-11 w-11 rounded-full object-cover" :src="avatarUrl" alt="Mở menu cá nhân">
                    </button>

                    <div
                      v-if="isProfileMenuOpen"
                      class="absolute right-0 top-[calc(100%+10px)] z-30 w-64 rounded-[1.6rem] border border-slate-200 bg-white p-2 shadow-2xl shadow-slate-950/20"
                    >
                      <button
                        type="button"
                        @click="openProfileCard"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-slate-800 transition hover:bg-slate-50"
                      >
                        <i class="fa-regular fa-id-card text-slate-500"></i>
                        <span class="font-semibold">Thông tin cá nhân</span>
                      </button>
                      <button
                        type="button"
                        @click="handleLogout"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-slate-800 transition hover:bg-slate-50"
                      >
                        <i class="fa-solid fa-right-from-bracket text-slate-500"></i>
                        <span class="font-semibold">Đăng xuất</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </section>

    <section class="relative -mt-10 z-10 max-w-6xl mx-auto px-4">
      <div v-if="errorMessage" class="mb-6 rounded-3xl border border-red-200 bg-white p-5 text-sm text-red-700 shadow-sm">
        {{ errorMessage }}
      </div>

      <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Khóa học đang học</p>
              <p class="mt-3 text-4xl font-black text-slate-900">{{ stats.activeCourses }}</p>
              <p class="mt-2 text-sm text-slate-500">Những khóa học bạn đang theo học hiện tại.</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-[#16a34a]">
              <i class="fa-solid fa-book-open-reader"></i>
            </div>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Bài tập đã nộp</p>
              <p class="mt-3 text-4xl font-black text-slate-900">{{ stats.submittedAssignments }}</p>
              <p class="mt-2 text-sm text-slate-500">Tổng số bài bạn đã hoàn thành và gửi lên hệ thống.</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-xl text-blue-600">
              <i class="fa-solid fa-file-circle-check"></i>
            </div>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 xl:col-span-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Trung bình điểm</p>
              <p class="mt-3 text-4xl font-black text-slate-900">{{ formatScore(stats.avgScore) }}/10</p>
              <p class="mt-2 text-sm text-slate-500">Điểm trung bình từ các bài đã được chấm.</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-xl text-amber-600">
              <i class="fa-solid fa-ranking-star"></i>
            </div>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Khóa học hoàn thành</p>
              <p class="mt-3 text-4xl font-black text-slate-900">{{ stats.completedCourses }}</p>
              <p class="mt-2 text-sm text-slate-500">Những khóa học bạn đã hoàn thành.</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-xl text-sky-600">
              <i class="fa-solid fa-circle-check"></i>
            </div>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Bài học đã làm</p>
              <p class="mt-3 text-4xl font-black text-slate-900">{{ stats.completedLessons }}</p>
              <p class="mt-2 text-sm text-slate-500">Số bài học bạn đã đi qua trong quá trình học.</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-xl text-violet-600">
              <i class="fa-solid fa-layer-group"></i>
            </div>
          </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Điểm quiz trung bình</p>
              <p class="mt-3 text-4xl font-black text-slate-900">{{ formatScore(stats.avgQuizScore) }}/10</p>
              <p class="mt-2 text-sm text-slate-500">Mức điểm trung bình ở các bài quiz bạn đã làm.</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-xl text-rose-600">
              <i class="fa-solid fa-chart-line"></i>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 mt-8 grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
      <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5">
          <div>
            <h2 class="text-xl font-bold text-slate-900">Khóa học gần đây</h2>
            <p class="mt-1 text-sm text-slate-500">Những khóa học bạn có thể quay lại để tiếp tục học.</p>
          </div>
          <router-link to="/courses" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-emerald-50 hover:text-[#16a34a]">
            Xem tất cả <i class="fa-solid fa-arrow-right-long"></i>
          </router-link>
        </div>

        <div v-if="enrolledCourses.length === 0" class="px-6 py-14 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-2xl text-slate-400">
            <i class="fa-solid fa-book-open"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-800">Bạn chưa tham gia khóa học nào</h3>
          <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-500">
            Hãy chọn một khóa học phù hợp để bắt đầu. Khi tham gia khóa học, nội dung và tiến độ của bạn sẽ xuất hiện tại đây.
          </p>
          <router-link to="/courses" class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#7AE582] px-6 py-3 font-bold text-slate-900 transition hover:bg-emerald-300">
            Khám phá khóa học
          </router-link>
        </div>

        <div v-else class="divide-y divide-slate-100">
          <article v-for="course in enrolledCourses" :key="`${course.id}-${course.class_name}`" class="p-6 transition hover:bg-slate-50/80">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
              <div class="flex items-start gap-4">
                <img :src="course.image_url || fallbackImage" :alt="course.title" class="h-24 w-36 rounded-2xl object-cover border border-slate-200">
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#16a34a]">
                      Level {{ course.level || 'N/A' }}
                    </span>
                    <span :class="statusBadgeClass(course.status)">
                      {{ formatStatus(course.status) }}
                    </span>
                  </div>
                  <h3 class="mt-3 text-lg font-bold text-slate-900">{{ course.title }}</h3>
                  <p class="mt-2 text-sm text-slate-500">Lớp: {{ course.class_name || 'Chưa phân lớp' }}</p>
                  <p class="mt-1 text-sm text-slate-500">
                    Bắt đầu: {{ formatDate(course.start_date) }}
                    <span v-if="course.end_date"> • Kết thúc: {{ formatDate(course.end_date) }}</span>
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <router-link :to="`/course/${course.id}`" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:border-[#7AE582] hover:text-[#16a34a]">
                  Chi tiết
                </router-link>
                <router-link :to="`/course/${course.id}`" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800">
                  Vào học
                </router-link>
              </div>
            </div>
          </article>
        </div>
      </div>

      <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-xl font-bold text-slate-900">Tóm tắt tiến độ</h2>
          <p class="mt-1 text-sm text-slate-500">Một vài con số để bạn dễ theo dõi việc học của mình.</p>

          <div class="mt-6 space-y-5">
            <div>
              <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-medium text-slate-600">Khóa học đang học</span>
                <span class="font-bold text-slate-900">{{ stats.activeCourses }}</span>
              </div>
              <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-[#7AE582] to-emerald-500" :style="{ width: progressWidth(stats.activeCourses, courseTotal) }"></div>
              </div>
            </div>

            <div>
              <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-medium text-slate-600">Khóa học hoàn thành</span>
                <span class="font-bold text-slate-900">{{ stats.completedCourses }}</span>
              </div>
              <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-blue-600" :style="{ width: progressWidth(stats.completedCourses, courseTotal) }"></div>
              </div>
            </div>

            <div>
              <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-medium text-slate-600">Bài học đã làm</span>
                <span class="font-bold text-slate-900">{{ stats.completedLessons }}</span>
              </div>
              <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-violet-400 to-violet-600" :style="{ width: progressWidth(stats.completedLessons, lessonGoal) }"></div>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-xl font-bold text-slate-900">Bắt đầu nhanh</h2>
          <div class="mt-5 space-y-3">
            <router-link to="/courses" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-[#7AE582] hover:bg-emerald-50/60">
              <div>
                <p class="font-bold text-slate-800">Tìm khóa học mới</p>
                <p class="text-sm text-slate-500">Khám phá các khóa học đang có và chọn nội dung phù hợp với bạn.</p>
              </div>
              <i class="fa-solid fa-arrow-right text-slate-400"></i>
            </router-link>

            <router-link to="/about" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-[#7AE582] hover:bg-emerald-50/60">
              <div>
                <p class="font-bold text-slate-800">Xem thêm thông tin</p>
                <p class="text-sm text-slate-500">Tìm hiểu thêm về nền tảng và cách học hiệu quả hơn.</p>
              </div>
              <i class="fa-solid fa-arrow-right text-slate-400"></i>
            </router-link>
          </div>
        </section>

        <section class="rounded-3xl bg-gradient-to-br from-[#7AE582] via-emerald-300 to-emerald-400 p-6 text-slate-900 shadow-lg shadow-emerald-200/60">
          <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-700">Gợi ý</p>
          <h2 class="mt-3 text-2xl font-black">Giữ nhịp học đều mỗi ngày</h2>
          <p class="mt-3 text-sm leading-relaxed text-slate-800/80">
            Chỉ cần duy trì việc học thường xuyên, trang này sẽ giúp bạn nhìn rõ tiến độ, kết quả và những gì cần tiếp tục.
          </p>
        </section>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession, updateAuthUser } from '../../utils/auth'

const router = useRouter()

const user = ref({
  full_name: 'Học viên',
  email: '',
  role: 'student'
})

const stats = ref({
  activeCourses: 0,
  completedCourses: 0,
  submittedAssignments: 0,
  avgScore: 0,
  completedLessons: 0,
  avgQuizScore: 0
})

const enrolledCourses = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const fallbackImage = 'https://placehold.co/600x400/e2e8f0/64748b?text=Course'
const isProfileMenuOpen = ref(false)
const isNotificationMenuOpen = ref(false)
const profileMenuRef = ref(null)
const notificationMenuRef = ref(null)
const isProfileModalOpen = ref(false)
const isSavingProfile = ref(false)
const profileMessage = ref('')
const profileMessageType = ref('success')
const profileForm = ref({
  full_name: '',
  email: '',
  phone: '',
  password: '',
})

const avatarUrl = computed(() => {
  const name = encodeURIComponent(user.value.full_name || 'User')
  return `https://ui-avatars.com/api/?name=${name}&background=0f172a&color=ffffff`
})

const firstName = computed(() => {
  const fullName = (user.value.full_name || '').trim()
  if (!fullName) return 'bạn'
  const parts = fullName.split(/\s+/)
  return parts[parts.length - 1]
})

const profileMessageClass = computed(() => {
  return profileMessageType.value === 'error'
    ? 'border border-red-200 bg-red-50 text-red-700'
    : 'border border-emerald-200 bg-emerald-50 text-emerald-700'
})

const courseTotal = computed(() => Math.max(1, stats.value.activeCourses + stats.value.completedCourses))
const lessonGoal = computed(() => Math.max(5, stats.value.completedLessons || 0))

const fetchDashboard = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await apiFetch('user/dashboard.php')

    if (response.status === 401 || response.status === 403) {
      clearAuthSession()
      router.push('/login')
      return
    }

    const result = await response.json()

    if (result.status === 'success') {
      user.value = result.data.user
      stats.value = result.data.stats
      enrolledCourses.value = Array.isArray(result.data.enrolledCourses) ? result.data.enrolledCourses : []
    } else {
      errorMessage.value = result.message || 'Không tải được thông tin học tập của bạn.'
    }
  } catch (error) {
    console.error('Lỗi tải dashboard:', error)
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

const toggleProfileMenu = () => {
  isNotificationMenuOpen.value = false
  isProfileMenuOpen.value = !isProfileMenuOpen.value
}

const toggleNotificationMenu = () => {
  isProfileMenuOpen.value = false
  isNotificationMenuOpen.value = !isNotificationMenuOpen.value
}

const openProfileCard = async () => {
  isProfileMenuOpen.value = false
  profileMessage.value = ''
  profileMessageType.value = 'success'
  isProfileModalOpen.value = true
  await fetchProfile()
}

const closeProfileModal = () => {
  isProfileModalOpen.value = false
  profileMessage.value = ''
  profileForm.value.password = ''
}

const fetchProfile = async () => {
  try {
    const response = await apiFetch('user/profile.php')

    if (response.status === 401 || response.status === 403) {
      clearAuthSession()
      router.push('/login')
      return
    }

    const result = await response.json()
    if (result.status === 'success') {
      profileForm.value = {
        full_name: result.data.full_name ?? '',
        email: result.data.email ?? '',
        phone: result.data.phone ?? '',
        password: '',
      }
    } else {
      profileMessageType.value = 'error'
      profileMessage.value = result.message || 'Không tải được thông tin cá nhân.'
    }
  } catch (error) {
    profileMessageType.value = 'error'
    profileMessage.value = 'Không thể kết nối tới máy chủ.'
  }
}

const submitProfileUpdate = async () => {
  profileMessage.value = ''

  if (!profileForm.value.full_name.trim()) {
    profileMessageType.value = 'error'
    profileMessage.value = 'Vui lòng nhập họ và tên.'
    return
  }

  if (!profileForm.value.email.trim()) {
    profileMessageType.value = 'error'
    profileMessage.value = 'Vui lòng nhập email.'
    return
  }

  isSavingProfile.value = true

  try {
    const response = await apiFetch('user/profile.php', {
      method: 'PUT',
      body: JSON.stringify({
        full_name: profileForm.value.full_name.trim(),
        email: profileForm.value.email.trim(),
        phone: profileForm.value.phone.trim(),
        password: profileForm.value.password,
      }),
    })

    if (response.status === 401 || response.status === 403) {
      clearAuthSession()
      router.push('/login')
      return
    }

    const result = await response.json()
    if (result.status === 'success') {
      user.value = {
        ...user.value,
        ...result.data,
      }
      updateAuthUser(result.data)
      profileForm.value.password = ''
      profileMessageType.value = 'success'
      profileMessage.value = result.message || 'Đã cập nhật thông tin cá nhân.'
    } else {
      profileMessageType.value = 'error'
      profileMessage.value = result.message || 'Không thể cập nhật thông tin.'
    }
  } catch (error) {
    profileMessageType.value = 'error'
    profileMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isSavingProfile.value = false
  }
}

const handleLogout = () => {
  isProfileMenuOpen.value = false
  isNotificationMenuOpen.value = false
  clearAuthSession()
  router.push('/login')
}

const handleDocumentClick = (event) => {
  if (!profileMenuRef.value?.contains(event.target)) {
    isProfileMenuOpen.value = false
  }

  if (!notificationMenuRef.value?.contains(event.target)) {
    isNotificationMenuOpen.value = false
  }
}

onMounted(() => {
  fetchDashboard()
  document.addEventListener('mousedown', handleDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleDocumentClick)
})

const formatRole = (role) => {
  const roles = {
    student: 'Học viên',
    instructor: 'Giảng viên',
    admin: 'Admin'
  }
  return roles[role] || role
}

const formatStatus = (status) => {
  const labels = {
    active: 'Đang học',
    completed: 'Hoàn thành',
    dropped: 'Đã dừng'
  }
  return labels[status] || status
}

const formatDate = (date) => {
  if (!date) return 'Chưa xác định'
  return new Intl.DateTimeFormat('vi-VN').format(new Date(date))
}

const formatScore = (score) => Number(score || 0).toFixed(1)

const progressWidth = (value, total) => {
  const safeTotal = Math.max(1, Number(total) || 1)
  const width = (Number(value) / safeTotal) * 100
  return `${Math.min(100, Math.max(8, width || 0))}%`
}

const statusBadgeClass = (status) => {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-bold '
  if (status === 'active') return base + 'bg-emerald-50 text-[#16a34a]'
  if (status === 'completed') return base + 'bg-sky-50 text-sky-700'
  if (status === 'dropped') return base + 'bg-rose-50 text-rose-700'
  return base + 'bg-slate-100 text-slate-600'
}
</script>
