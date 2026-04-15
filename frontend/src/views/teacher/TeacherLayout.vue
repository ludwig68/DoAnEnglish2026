<template>
  <div class="teacher-dashboard-root flex min-h-screen bg-white font-body selection:bg-emerald-100 overflow-x-hidden">

    <!-- ── Profile Modal (Bảng điều khiển thông tin cá nhân) ── -->
    <div v-if="isProfileModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-md">
      <div class="w-full max-w-2xl rounded-[3rem] bg-white shadow-2xl animate-in fade-in zoom-in duration-300 overflow-hidden">
        <!-- Tiêu đề modal -->
        <div class="flex items-center justify-between border-b border-slate-50 px-10 py-8">
          <div>
            <h2 class="text-2xl font-headline font-black text-slate-900 leading-none tracking-tight">Cài đặt tài khoản (Giảng viên)</h2>
            <p class="mt-2 text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] leading-none">Instructor Settings</p>
          </div>
          <button type="button" @click="closeProfileModal" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-red-500 shadow-sm transition-all">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Form cập nhật -->
        <form @submit.prevent="submitProfileUpdate" class="px-10 py-10 space-y-8">
          <!-- Thông báo kết quả trong modal -->
          <div v-if="profileMessage" :class="profileMessageClass" class="rounded-2xl px-6 py-4 text-sm font-bold shadow-sm flex items-center gap-3 border transition-all">
            <div class="w-8 h-8 rounded-xl bg-white/50 flex items-center justify-center shadow-inner">
               <i :class="profileMessageType === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'"></i>
            </div>
            {{ profileMessage }}
          </div>

          <div class="grid gap-8 sm:grid-cols-2">
            <div class="space-y-2.5">
              <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Họ và tên</span>
              <input v-model="profileForm.full_name" type="text" class="w-full rounded-2xl border-none bg-slate-50 px-6 py-4 text-sm text-slate-800 outline-none transition focus:ring-2 focus:ring-emerald-400/20" placeholder="Họ và tên">
            </div>
            <div class="space-y-2.5">
              <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Số điện thoại</span>
              <input v-model="profileForm.phone" type="tel" class="w-full rounded-2xl border-none bg-slate-50 px-6 py-4 text-sm text-slate-800 outline-none transition focus:ring-2 focus:ring-emerald-400/20" placeholder="Số điện thoại của bạn">
            </div>
            <div class="space-y-2.5 sm:col-span-2">
              <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Email</span>
              <input v-model="profileForm.email" type="email" class="w-full rounded-2xl border-none bg-slate-50 px-6 py-4 text-sm text-slate-800 outline-none transition focus:ring-2 focus:ring-emerald-400/20" placeholder="Email">
            </div>
          </div>

          <!-- Phần bảo mật: Đổi mật khẩu -->
          <div class="pt-4 border-t border-slate-50">
            <div class="mb-6">
              <h3 class="text-xs font-black uppercase tracking-[0.1em] text-slate-800">Bảo mật</h3>
              <p class="text-[10px] text-slate-400 font-medium">Để trống nếu không muốn đổi mật khẩu</p>
            </div>
            <div class="grid gap-8 sm:grid-cols-2">
              <div class="space-y-2.5">
                <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Mật khẩu mới</span>
                <input v-model="profileForm.new_password" type="password" class="w-full rounded-2xl border-none bg-slate-50 px-6 py-4 text-sm text-slate-800 outline-none transition focus:ring-2 focus:ring-emerald-400/20" placeholder="••••••••">
              </div>
              <div class="space-y-2.5">
                <span class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Xác nhận mật khẩu</span>
                <input v-model="profileForm.confirm_password" type="password" class="w-full rounded-2xl border-none bg-slate-50 px-6 py-4 text-sm text-slate-800 outline-none transition focus:ring-2 focus:ring-emerald-400/20" placeholder="••••••••">
              </div>
            </div>
          </div>

          <!-- Nút thao tác -->
          <div class="pt-4 flex justify-end gap-6 text-xs font-black uppercase tracking-[0.2em]">
            <button type="button" @click="closeProfileModal" class="text-slate-300 hover:text-slate-500 transition-colors uppercase">Hủy</button>
            <button type="submit" :disabled="isSavingProfile" class="text-emerald-500 hover:text-emerald-500 transition-colors disabled:opacity-50 uppercase shadow-none flex items-center gap-2">
              <span v-if="isSavingProfile" class="w-3 h-3 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></span>
              Lưu thay đổi
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ══ SIDEBAR ══ -->
    <aside class="fixed left-0 top-0 z-50 flex h-full w-72 flex-col border-r border-[#F1F3F3] bg-white overflow-y-auto">
      <!-- Logo Trung tâm -->
      <div class="pt-14 px-10 mb-16">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-full bg-slate-900 flex items-center justify-center shadow-lg group hover:bg-emerald-500 transition-all duration-500">
            <i class="fa-solid fa-graduation-cap text-white text-lg transition-transform group-hover:rotate-12"></i>
          </div>
          <div class="flex flex-col">
            <h1 class="text-[17px] font-headline font-black text-[#1A1C1B] tracking-tight leading-none uppercase">English Center</h1>
            <p class="text-[9px] text-[#94A3B8] font-bold tracking-[0.2em] uppercase mt-1">ACADEMIC FACULTY</p>
          </div>
        </div>
      </div>

      <!-- Menu Navigation -->
      <nav class="flex-1 px-8 space-y-2">
        <router-link to="/teacher/dashboard"
          active-class="bg-emerald-50 text-emerald-500"
          exact-active-class="bg-emerald-50 text-emerald-500"
          class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50">
          <i class="fa-solid fa-shapes text-lg transition-transform group-hover:scale-110 w-6"></i>
          <span>Bảng điều khiển</span>
        </router-link>

        <router-link to="/teacher/schedule"
          active-class="bg-emerald-50 text-emerald-500"
          class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50">
          <i class="fa-regular fa-calendar text-lg transition-transform group-hover:scale-110 w-6"></i>
          <span>Lịch giảng dạy</span>
        </router-link>

        <router-link to="/teacher/classes"
          active-class="bg-emerald-50 text-emerald-500"
          class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50"
          :class="$route.path.startsWith('/teacher/classes') ? 'bg-emerald-50 text-emerald-500' : ''">
          <i class="fa-solid fa-users text-lg transition-transform group-hover:scale-110 w-6"></i>
          <span>Lớp học của tôi</span>
        </router-link>

        <!-- ── Accordion: Bài tập & Chấm điểm ── -->
        <div>
          <!-- Parent trigger -->
          <button @click="toggleAssignments"
            class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all hover:bg-slate-50"
            :class="isAssignmentsOpen || $route.path.startsWith('/teacher/assignments')
              ? 'text-emerald-500 bg-emerald-50'
              : 'text-[#C2C9D1] hover:text-[#1A1C1B]'">
            <i class="fa-solid fa-clipboard-check text-lg transition-transform group-hover:scale-110 w-6"></i>
            <span class="flex-1 text-left">Bài tập &amp; Chấm điểm</span>
            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"
              :class="isAssignmentsOpen ? 'rotate-180' : ''"></i>
          </button>

          <!-- Sub-items -->
          <div class="overflow-hidden transition-all duration-300"
            :style="isAssignmentsOpen ? 'max-height: 300px; opacity: 1' : 'max-height: 0; opacity: 0'">
            <div class="pl-6 pr-2 pt-1 pb-2 space-y-0.5">
              <router-link v-for="sub in assignmentSubNav" :key="sub.tab"
                :to="{ path: '/teacher/assignments', query: { tab: sub.tab } }"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-[13px] font-bold transition-all"
                :class="$route.path === '/teacher/assignments' && ($route.query.tab === sub.tab || (!$route.query.tab && sub.tab === 'queue'))
                  ? 'bg-emerald-50 text-emerald-600'
                  : 'text-slate-400 hover:text-slate-700 hover:bg-slate-50'">
                <i :class="sub.icon" class="w-4 text-center text-sm"></i>
                <span>{{ sub.label }}</span>
                <span v-if="sub.badge" class="ml-auto text-[9px] font-black bg-red-500 text-white rounded-full px-2 py-0.5">
                  {{ sub.badge }}
                </span>
              </router-link>
            </div>
          </div>
        </div>

        <!-- ── Divider ── -->
        <div class="pt-4 pb-2">
          <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-200 px-4">Sắp ra mắt</p>
        </div>

        <div class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight text-slate-200 cursor-not-allowed select-none relative">
          <i class="fa-solid fa-chart-line text-lg w-6"></i>
          <span>Tiến độ học tập</span>
          <span class="ml-auto text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full">Soon</span>
        </div>

        <div class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight text-slate-200 cursor-not-allowed select-none">
          <i class="fa-regular fa-bell text-lg w-6"></i>
          <span>Thông báo</span>
          <span class="ml-auto text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full">Soon</span>
        </div>

        <router-link to="/teacher/attendance"
          active-class="bg-emerald-50 text-emerald-500"
          class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50"
          :class="$route.path.startsWith('/teacher/attendance') ? 'bg-emerald-50 text-emerald-500' : ''">
          <i class="fa-solid fa-user-check text-lg transition-transform group-hover:scale-110 w-6"></i>
          <span>Điểm danh</span>
        </router-link>
      </nav>

       <!-- Hồ sơ & Đăng xuất (Dưới cùng Sidebar) -->
       <div class="mt-auto px-8 pb-12">
           <div class="flex items-center gap-4 px-2 py-4 mb-3 border-t border-slate-50 pt-6">
               <div class="relative cursor-pointer group" @click="openProfileCard">
                   <div class="bg-white rounded-full p-[1.5px] border border-[#059669]">
                       <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user?.full_name || 'T')}&background=059669&color=fff&bold=true`" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                   </div>
                   <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-[#10B981] rounded-full border-[2.5px] border-white shadow-sm z-10"></div>
               </div>
               <div class="flex flex-col min-w-0">
                   <span class="text-[14px] font-headline font-black text-[#1A1C1B] truncate tracking-tight leading-none">{{ user?.full_name || 'Giảng viên' }}</span>
                   <span class="text-[10px] text-[#C2C9D1] font-black uppercase tracking-[0.1em] mt-1.5 leading-none capitalize">Giảng viên / {{ user?.role || 'Instructor' }}</span>
               </div>
           </div>
           
           <button @click="handleLogout" class="flex w-full items-center gap-4 px-6 py-3.5 text-[#C2C9D1] hover:text-red-500 rounded-2xl transition-all font-headline text-[14px] font-bold tracking-tight group/logout cursor-pointer">
               <i class="fa-solid fa-right-from-bracket text-lg transition-transform group-hover/logout:translate-x-1"></i>
               <span>Đăng xuất</span>
           </button>
       </div>
    </aside>

    <!-- ══ MAIN CANVAS ══ -->
    <main class="ml-72 min-h-screen flex-1 flex flex-col bg-slate-50/50 overflow-x-hidden">
      <!-- Header cố định -->
      <header class="sticky top-0 z-40 shrink-0 bg-white/80 backdrop-blur-3xl border-b border-slate-100/50 px-10 pt-8">
        <div class="flex items-center justify-between w-full mx-auto pb-4">
            
          <!-- Header Left (Search) -->
          <div class="relative group shrink-0">
             <i class="fa-solid fa-magnifying-glass absolute left-6 top-1/2 -translate-y-1/2 text-[#C2C9D1] text-sm"></i>
             <input class="pl-14 pr-10 py-3 w-[28rem] bg-slate-100/80 border border-transparent rounded-full focus:ring-2 focus:ring-emerald-400/20 font-body text-[14px] font-medium placeholder:text-[#94A3B8] transition-all outline-none" placeholder="Tìm kiếm học viên, lớp học, tài liệu..." type="text"/>
          </div>

          <!-- Header Right -->
          <div class="flex items-center gap-8 shrink-0">
            <!-- Icons -->
            <div class="flex items-center gap-4">
                 <button class="w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors relative">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                 </button>
                 <button class="w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-circle-question text-lg"></i>
                 </button>
            </div>
            
            <div class="h-8 w-px bg-slate-200"></div>
          </div>
        </div>
      </header>

      <!-- Nội dung trang con (được inject bởi router-view) -->
      <router-view :user="user" />
    </main>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { authSession, clearAuthSession, updateAuthUser } from '../../utils/auth'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'

const router = useRouter()
const route = useRoute()
const user = computed(() => authSession.value?.user || null)

// ── Assignments Accordion ──
const isAssignmentsOpen = ref(false)

const assignmentSubNav = ref([
  { tab: 'queue',    label: 'Hàng chờ chấm điểm', icon: 'fa-solid fa-inbox',             badge: null },
  { tab: 'history',  label: 'Lịch sử nộp bài',     icon: 'fa-solid fa-clock-rotate-left', badge: null },
  { tab: 'rubric',   label: 'Quản lý tiêu chí',     icon: 'fa-solid fa-ruler-combined',    badge: null },
  { tab: 'analytics',label: 'Thống kê',             icon: 'fa-solid fa-chart-line',        badge: null },
])

const toggleAssignments = () => {
  if (!isAssignmentsOpen.value) {
    router.push({ path: '/teacher/assignments', query: { tab: 'queue' } })
  }
  isAssignmentsOpen.value = !isAssignmentsOpen.value
}

// Auto-open when route is /teacher/assignments
watch(() => route.path, (path) => {
  if (path.startsWith('/teacher/assignments')) {
    isAssignmentsOpen.value = true
  }
}, { immediate: true })

// ── State: Profile Modal ──
const isProfileModalOpen = ref(false)
const isSavingProfile = ref(false)
const profileForm = ref({ full_name: '', email: '', phone: '', new_password: '', confirm_password: '' })
const profileMessage = ref('')
const profileMessageType = ref('success')

const profileMessageClass = computed(() =>
  profileMessageType.value === 'error'
    ? 'bg-red-50 text-red-600 border-red-100 font-bold'
    : 'bg-emerald-50 text-emerald-500 border-emerald-100 font-bold'
)

const openProfileCard = async () => {
  profileMessage.value = ''
  profileMessageType.value = 'success'
  isProfileModalOpen.value = true
  try {
    const response = await apiFetch('user/profile.php')
    const result = await response.json()
    if (result.status === 'success') {
      profileForm.value = {
        full_name: result.data.full_name ?? '',
        email: result.data.email ?? '',
        phone: result.data.phone ?? '',
        new_password: '',
        confirm_password: ''
      }
    }
  } catch {
    notifyError('Không thể đồng bộ thông tin tài khoản hiện tại.')
  }
}

const closeProfileModal = () => { isProfileModalOpen.value = false }

const submitProfileUpdate = async () => {
  if (!profileForm.value.full_name.trim() || !profileForm.value.email.trim()) {
    notifyWarning('Vui lòng nhập đầy đủ họ tên và email.')
    return
  }
  if (profileForm.value.new_password) {
    if (profileForm.value.new_password.length < 6) {
      notifyWarning('Mật khẩu mới phải có tối thiểu 6 ký tự.')
      return
    }
    if (profileForm.value.new_password !== profileForm.value.confirm_password) {
      notifyWarning('Mật khẩu xác nhận không khớp. Vui lòng kiểm tra lại.')
      return
    }
  }
  isSavingProfile.value = true
  profileMessage.value = ''
  try {
    const response = await apiFetch('user/profile.php', {
      method: 'PUT',
      body: JSON.stringify(profileForm.value)
    })
    const result = await response.json()
    if (result.status === 'success') {
      updateAuthUser(result.data)
      profileMessageType.value = 'success'
      profileMessage.value = 'Dữ liệu hồ sơ đã được lưu thành công!'
      notifySuccess('Cập nhật thông tin thành công.')
    } else {
      profileMessageType.value = 'error'
      profileMessage.value = result.message
      notifyError(result.message)
    }
  } catch {
    profileMessageType.value = 'error'
    profileMessage.value = 'Lỗi mạng khi lưu hồ sơ.'
    notifyError('Lỗi mạng khi cập nhật.')
  } finally {
    isSavingProfile.value = false
  }
}

const handleLogout = () => {
  clearAuthSession()
  router.push('/login')
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

/* Hide scrollbar for sidebar but keep functionality */
aside::-webkit-scrollbar {
  display: none;
}
aside {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
</style>
