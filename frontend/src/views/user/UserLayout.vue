<template>
  <div class="user-dashboard-root flex min-h-screen bg-white font-body selection:bg-emerald-100 overflow-x-hidden">

    <!-- ── Profile Modal (Bảng điều khiển thông tin cá nhân) ── -->
    <div v-if="isProfileModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-md">
      <div class="w-full max-w-2xl rounded-[3rem] bg-white shadow-2xl animate-in fade-in zoom-in duration-300 overflow-hidden">
        <!-- Tiêu đề modal -->
        <div class="flex items-center justify-between border-b border-slate-50 px-10 py-8">
          <div>
            <h2 class="text-2xl font-headline font-black text-slate-900 leading-none tracking-tight">Cài đặt tài khoản</h2>
            <p class="mt-2 text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] leading-none">Global Settings</p>
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
            <button type="submit" :disabled="isSavingProfile" class="text-emerald-500 hover:text-emerald-600 transition-colors disabled:opacity-50 uppercase shadow-none flex items-center gap-2">
              <span v-if="isSavingProfile" class="w-3 h-3 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></span>
              Lưu thay đổi
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ══ SIDEBAR ══ -->
    <aside class="fixed left-0 top-0 z-50 flex h-full w-72 flex-col border-r border-[#F1F3F3] bg-white">
      <!-- Logo Trung tâm -->
      <div class="pt-14 px-10 mb-16">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-full bg-slate-900 flex items-center justify-center shadow-lg group hover:bg-emerald-500 transition-all duration-500">
            <i class="fa-solid fa-graduation-cap text-white text-lg transition-transform group-hover:rotate-12"></i>
          </div>
          <div class="flex flex-col">
            <h1 class="text-[17px] font-headline font-black text-[#1A1C1B] tracking-tight leading-none uppercase">English Learning</h1>
            <p class="text-[9px] text-[#94A3B8] font-bold tracking-[0.2em] uppercase mt-1">Academy Center</p>
          </div>
        </div>
      </div>

      <!-- Menu Navigation -->
      <nav class="flex-1 px-8 space-y-2">
        <router-link to="/user/dashboard" active-class="bg-emerald-50 text-emerald-600" exact-active-class="bg-emerald-50 text-emerald-600" class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50">
          <i class="fa-solid fa-book-open-reader text-lg transition-transform group-hover:scale-110"></i>
          <span>Khóa học của tôi</span>
        </router-link>
        <a v-for="item in navItems" :key="item.label" href="#" @click.prevent class="flex items-center gap-4 px-6 py-4 text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50 rounded-2xl transition-all duration-300 font-headline text-[14px] font-bold tracking-tight">
          <i :class="item.icon" class="text-base"></i>
          <span>{{ item.label }}</span>
        </a>
      </nav>

      <!-- Hồ sơ học viên (phía dưới Sidebar) -->
      <div class="mt-auto px-8 pb-12">
        <div class="flex items-center gap-4 px-2 py-4 mb-3 border-t border-slate-50 pt-6">
          <div class="relative cursor-pointer group" @click="openProfileCard">
            <!-- Vòng tròn cầu vồng (Rainbow Ring) -->
            <div class="p-[1.5px] rounded-full bg-gradient-to-tr from-[#FFD600] via-[#FF006E] to-[#3A86FF] shadow-sm transition-all duration-[800ms] group-hover:rotate-[360deg]">
               <div class="bg-white rounded-full p-[1.5px]">
                  <img :src="avatarUrl" alt="Avatar" class="w-11 h-11 rounded-full object-cover">
               </div>
            </div>
            <!-- Chỉ báo trạng thái Online -->
            <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-[#7AE582] rounded-full border-[3px] border-white shadow-sm z-10"></div>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-[15px] font-headline font-black text-[#1A1C1B] truncate tracking-tight leading-none">{{ user.full_name || 'Học viên' }}</span>
            <span class="text-[10px] text-[#C2C9D1] font-black uppercase tracking-[0.1em] mt-1.5 leading-none">ID: #8829</span>
          </div>
        </div>
        <!-- Nút Đăng xuất -->
        <button @click="handleLogout" class="flex w-full items-center gap-4 px-6 py-3.5 text-[#C2C9D1] hover:text-red-500 rounded-2xl transition-all font-headline text-[14px] font-bold tracking-tight group/logout">
          <i class="fa-solid fa-right-from-bracket text-lg transition-transform group-hover/logout:translate-x-1"></i>
          <span>Đăng xuất</span>
        </button>
      </div>
    </aside>

    <!-- ══ MAIN CANVAS ══ -->
    <main class="ml-72 min-h-screen flex-1 flex flex-col bg-white overflow-x-hidden">
      <!-- Header cố định -->
      <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-3xl border-b border-white px-10 pt-10">
        <div class="flex items-center justify-between w-full mx-auto">
          <nav class="flex items-center gap-14">
            <router-link to="/user/dashboard" class="font-headline text-[17px] font-black tracking-tight pb-4 border-b-[3.5px] uppercase transition-colors"
              active-class="text-emerald-500 border-emerald-500"
              :class="$route.path === '/user/dashboard' ? '' : 'text-[#E2E8F0] border-transparent hover:text-[#C2C9D1]'">
              Dashboard
            </router-link>
            <a class="text-[#E2E8F0] hover:text-[#C2C9D1] transition-colors font-headline text-[17px] font-black tracking-tight pb-4 border-b-[3.5px] border-transparent uppercase" href="#">Lộ trình</a>
            <a class="text-[#E2E8F0] hover:text-[#C2C9D1] transition-colors font-headline text-[17px] font-black tracking-tight pb-4 border-b-[3.5px] border-transparent uppercase" href="#">Thư viện</a>
          </nav>

          <div class="flex items-center gap-10 pb-4">
            <div class="relative group">
              <i class="fa-solid fa-magnifying-glass absolute left-6 top-1/2 -translate-y-1/2 text-[#C2C9D1] text-sm"></i>
              <input class="pl-16 pr-10 py-3.5 w-64 bg-slate-50 border border-slate-100 rounded-[1.25rem] focus:ring-2 focus:ring-emerald-400/10 font-body text-[13px] font-medium placeholder:text-[#C2C9D1] transition-all duration-500 hover:w-80 outline-none shadow-none" placeholder="Tìm kiếm tài liệu..." type="text"/>
            </div>
            <div class="flex items-center gap-2">
              <button class="w-12 h-12 rounded-2xl flex items-center justify-center text-[#C2C9D1] hover:bg-slate-50 hover:text-emerald-500 transition-all duration-300 shadow-none"><i class="fa-solid fa-bell text-lg"></i></button>
              <button class="w-12 h-12 rounded-2xl flex items-center justify-center text-[#C2C9D1] hover:bg-slate-50 hover:text-emerald-500 transition-all duration-300 shadow-none"><i class="fa-solid fa-envelope text-lg"></i></button>
            </div>
          </div>
        </div>
      </header>

      <!-- Nội dung trang con (được inject bởi router-view) -->
      <router-view :user="user" />
    </main>
  </div>
</template>

<script setup>
/**
 * UserLayout.vue
 * Layout chung cho tất cả các trang trong khu vực người dùng (/user/...).
 * Chứa: Sidebar, Header cố định, và Profile Modal.
 * Truyền dữ liệu user xuống trang con qua prop.
 */
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession, updateAuthUser } from '../../utils/auth'
import { openConfirm } from '../../utils/confirm'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'

const router = useRouter()

// ── State: Dữ liệu người dùng (chia sẻ xuống trang con qua prop) ──
const user = ref({ full_name: 'Học viên', email: '', role: 'student' })

// ── State: Profile Modal ──
const isProfileModalOpen = ref(false)
const isSavingProfile = ref(false)
const profileForm = ref({ full_name: '', email: '', phone: '', new_password: '', confirm_password: '' })
const profileMessage = ref('')
const profileMessageType = ref('success')

// ── Menu Sidebar ──
const navItems = [
  { label: 'Bài tập', icon: 'fa-solid fa-rectangle-list' },
  { label: 'Hỗ trợ', icon: 'fa-solid fa-life-ring' },
  { label: 'Nộp đơn', icon: 'fa-solid fa-calendar-minus' },
  { label: 'Học bù', icon: 'fa-solid fa-rotate-right' },
]

// ── Computed ──
const avatarUrl = computed(() => {
  const name = encodeURIComponent(user.value.full_name || 'Học viên')
  return `https://ui-avatars.com/api/?name=${name}&background=111&color=ffffff&rounded=true&size=128&bold=true`
})

const profileMessageClass = computed(() =>
  profileMessageType.value === 'error'
    ? 'bg-red-50 text-red-600 border-red-100 font-bold'
    : 'bg-emerald-50 text-emerald-600 border-emerald-100 font-bold'
)

// ── Methods ──

/**
 * @function fetchUserInfo
 * @description Tải thông tin người dùng để hiển thị trên Sidebar.
 * Được gọi một lần khi Layout mount.
 */
const fetchUserInfo = async () => {
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
    }
  } catch {
    // Lỗi nhẹ - không hiển thị lỗi, trang con sẽ xử lý chi tiết
  }
}

/**
 * @function openProfileCard
 * @description Mở Modal cập nhật hồ sơ và đồng bộ dữ liệu mới nhất từ API.
 */
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

/**
 * @function closeProfileModal
 */
const closeProfileModal = () => { isProfileModalOpen.value = false }

/**
 * @function submitProfileUpdate
 * @description Gửi yêu cầu cập nhật hồ sơ lên backend.
 */
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
      user.value = { ...user.value, ...result.data }
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
    notifyError('Gặp lỗi kỹ thuật khi gửi yêu cầu cập nhật hồ sơ.')
  } finally {
    isSavingProfile.value = false
  }
}

/**
 * @function handleLogout
 * @description Xử lý đăng xuất với confirm tùy chỉnh.
 */
const handleLogout = async () => {
  const confirmed = await openConfirm({
    title: 'Xác nhận đăng xuất',
    message: 'Hệ thống sẽ kết thúc phiên làm việc của bạn. Bạn có chắc chắn muốn thoát?',
    confirmText: 'Xác nhận thoát',
    tone: 'danger'
  })
  if (confirmed) {
    clearAuthSession()
    router.push('/login')
    notifySuccess('Đã đăng xuất khỏi hệ thống.')
  }
}

// ── Lifecycle ──
onMounted(() => {
  fetchUserInfo()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
