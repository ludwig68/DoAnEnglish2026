# Huong Dan Viet Lai `UserLayout.vue` Tu Dau

Tai lieu nay huong dan viet lai file `frontend/src/views/user/UserLayout.vue` theo tung buoc. File nay la layout chung cho khu vuc hoc vien `/user/...`, bao gom sidebar, header, modal cap nhat ho so, thong bao, dang xuat va noi dung trang con qua `router-view`.

> Luu y: file  hiện tai dang co dau hieu loi encoding tieng Viet trong comment va text hien thi. Khi viet lai nen luu file bang UTF-8 va go lai chu tieng Viet co dau truc tiep de tranh hien thi ky tu loi.

## 1. Hieu Nhiem Vu Cua File

`UserLayout.vue` khong phai la mot trang rieng le nhu `UserDashboard.vue`. No la khung bao ben ngoai cho cac trang con trong router:

```js
{
  path: '/user',
  component: UserLayout,
  children: [
    { path: 'dashboard', component: UserDashboard },
    { path: 'assignments', component: UserAssignments },
    { path: 'support', component: UserSupport },
    { path: 'leave-request', component: UserLeaveRequest },
    { path: 'makeup-class', component: UserMakeUpClass }
  ]
}
```

Viec can lam cua layout:

- Hien thi sidebar ben trai.
- Hien thi header ben phai.
- Hien thi thong tin hoc vien.
- Cho phep mo modal cap nhat ho so.
- Cho phep dang xuat.
- Lay va hien thi thong bao.
- Render trang con bang `<router-view :user="user" />`.

## 2. Cau Truc File Nen Viet

Mot Single File Component Vue nen chia thanh 3 phan:

```vue
<template>
  <!-- HTML giao dien -->
</template>

<script setup>
// import, state, computed, methods, lifecycle
</script>

<style scoped>
/* CSS rieng cho layout */
</style>
```

Thu tu viet nen la:

1. Viet `script setup` truoc de xac dinh data va ham can dung.
2. Viet template theo tung khoi: modal, sidebar, main/header, router-view.
3. Viet style cuoi cung.

Lam theo thu tu nay giup ban khong bi tao template truoc roi moi thieu state/ham.

## 3. Import Can Co

Trong `<script setup>`, can import cac API cua Vue, router va helper cua project:

```js
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession, updateAuthUser } from '../../utils/auth'
import { openConfirm } from '../../utils/confirm'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'
```

Giai thich:

- `ref`: tao state reactive.
- `computed`: tao gia tri tinh tu state, vi du `avatarUrl`.
- `onMounted`: goi API khi layout duoc mount.
- `onUnmounted`: don timer thong bao khi layout bi huy. File hien tai chua co, nen them de tranh leak timer.
- `useRouter`: chuyen trang khi logout hoac notification co link.
- `apiFetch`: goi backend co tu dong gan token.
- `clearAuthSession`: xoa phien dang nhap.
- `updateAuthUser`: cap nhat user trong localStorage sau khi sua profile.
- `openConfirm`: mo hop thoai xac nhan dang xuat.
- `notifySuccess`, `notifyError`, `notifyWarning`: hien toast.

## 4. Khai Bao State

Bat dau bang router:

```js
const router = useRouter()
```

State thong tin user:

```js
const user = ref({
  full_name: 'Học viên',
  email: '',
  role: 'student'
})
```

State modal ho so:

```js
const isProfileModalOpen = ref(false)
const isSavingProfile = ref(false)
const profileForm = ref({
  full_name: '',
  email: '',
  phone: '',
  new_password: '',
  confirm_password: ''
})
const profileMessage = ref('')
const profileMessageType = ref('success')
```

State notification:

```js
const isNotiOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
let notiTimer = null
```

Menu sidebar nen viet thanh mang de template gon hon:

```js
const menuItems = [
  { to: '/user/dashboard', label: 'Khóa học của tôi', icon: 'fa-solid fa-book-open-reader' },
  { to: '/user/assignments', label: 'Bài tập', icon: 'fa-solid fa-rectangle-list' },
  { to: '/user/leave-request', label: 'Đơn xin nghỉ', icon: 'fa-solid fa-calendar-xmark' },
  { to: '/user/makeup-class', label: 'Đăng ký học bù', icon: 'fa-solid fa-rotate-right' },
  { to: '/user/support', label: 'Hỗ trợ', icon: 'fa-solid fa-life-ring' }
]
```

Trong file hien tai, 5 menu dang duoc viet lap lai bang 5 `router-link`. Khi viet lai tu dau, dung `v-for` se de bao tri hon.

## 5. Computed Can Co

Avatar tao tu ten user:

```js
const avatarUrl = computed(() => {
  const name = encodeURIComponent(user.value.full_name || 'Học viên')
  return `https://ui-avatars.com/api/?name=${name}&background=111&color=ffffff&rounded=true&size=128&bold=true`
})
```

Class hien thong bao trong modal:

```js
const profileMessageClass = computed(() => {
  return profileMessageType.value === 'error'
    ? 'bg-red-50 text-red-600 border-red-100 font-bold'
    : 'bg-emerald-50 text-emerald-600 border-emerald-100 font-bold'
})
```

## 6. Ham Lay Thong Tin User

Ham nay goi khi layout mount de hien ten hoc vien tren sidebar:

```js
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
    // Layout khong can hien loi lon o day.
    // Cac trang con se xu ly loi chi tiet neu can.
  }
}
```

Ly do xu ly `401` va `403`: token het han hoac khong co quyen thi xoa session va quay ve login.

## 7. Ham Mo Modal Ho So

Khi click avatar, modal mo ra va lay profile moi nhat:

```js
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
```

Ham dong modal:

```js
const closeProfileModal = () => {
  isProfileModalOpen.value = false
}
```

## 8. Ham Cap Nhat Ho So

Can validate truoc khi gui API:

```js
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
```

Diem quan trong:

- `isSavingProfile` dung de disable nut submit.
- `updateAuthUser(result.data)` giup localStorage dong bo voi thong tin moi.
- Khong nen dong modal ngay sau khi save, vi can cho user thay message thanh cong/loi.

## 9. Ham Dang Xuat

Dang xuat nen co confirm:

```js
const handleLogout = async () => {
  const confirmed = await openConfirm({
    title: 'Xác nhận đăng xuất',
    message: 'Hệ thống sẽ kết thúc phiên làm việc của bạn. Bạn có chắc chắn muốn thoát?',
    confirmText: 'Xác nhận thoát',
    tone: 'danger'
  })

  if (!confirmed) return

  if (notiTimer) {
    clearInterval(notiTimer)
    notiTimer = null
  }

  clearAuthSession()
  router.push('/login')
  notifySuccess('Đã đăng xuất khỏi hệ thống.')
}
```

## 10. Logic Notification

Mo/tat dropdown:

```js
const toggleNotifications = () => {
  isNotiOpen.value = !isNotiOpen.value

  if (isNotiOpen.value) {
    fetchNotifications()
  }
}
```

Lay danh sach thong bao:

```js
const fetchNotifications = async () => {
  try {
    const response = await apiFetch('user/notifications.php')
    const result = await response.json()

    if (result.status === 'success') {
      notifications.value = result.data.notifications
      unreadCount.value = result.data.unread_count
    }
  } catch {
    // Khong can spam toast vi ham nay chay dinh ky.
  }
}
```

Danh dau tat ca da doc:

```js
const markAllAsRead = async () => {
  try {
    await apiFetch('user/notifications.php', { method: 'PUT' })
    unreadCount.value = 0
    notifications.value.forEach((notification) => {
      notification.is_read = 1
    })
  } catch {
    notifyError('Không thể đánh dấu thông báo đã đọc.')
  }
}
```

Click vao thong bao:

```js
const handleNotiClick = (notification) => {
  isNotiOpen.value = false

  if (notification.link) {
    router.push(notification.link)
  }
}
```

Dinh dang thoi gian:

```js
const formatTimeAgo = (dateStr) => {
  if (!dateStr) return ''

  const date = new Date(dateStr)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Vừa xong'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} phút trước`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} giờ trước`

  return date.toLocaleDateString('vi-VN')
}
```

## 11. Lifecycle

Khi layout mount:

```js
onMounted(() => {
  fetchUserInfo()
  fetchNotifications()
  notiTimer = setInterval(fetchNotifications, 60000)
})
```

Nen them cleanup:

```js
onUnmounted(() => {
  if (notiTimer) {
    clearInterval(notiTimer)
    notiTimer = null
  }
})
```

Neu khong cleanup, timer van co the tiep tuc chay sau khi user roi khoi layout.

## 12. Viet Template Theo Tung Khoi

Khung ngoai:

```vue
<template>
  <div class="user-dashboard-root flex min-h-screen bg-white font-body selection:bg-emerald-100 overflow-x-hidden">
    <!-- Profile Modal -->
    <!-- Sidebar -->
    <!-- Main -->
  </div>
</template>
```

### 12.1. Profile Modal

Dung `v-if="isProfileModalOpen"` de chi render khi can:

```vue
<div
  v-if="isProfileModalOpen"
  class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-md"
>
  <div class="w-full max-w-2xl rounded-[3rem] bg-white shadow-2xl overflow-hidden">
    <!-- Header modal -->
    <!-- Form -->
  </div>
</div>
```

Trong form:

- Dung `@submit.prevent="submitProfileUpdate"`.
- Input dung `v-model="profileForm.full_name"`, `profileForm.email`, `profileForm.phone`.
- Password moi va confirm password de trong neu khong doi mat khau.
- Nut submit co `:disabled="isSavingProfile"`.

### 12.2. Sidebar

Sidebar co 3 phan:

- Logo.
- Menu.
- User card va nut logout.

Menu nen dung `v-for`:

```vue
<router-link
  v-for="item in menuItems"
  :key="item.to"
  :to="item.to"
  active-class="bg-emerald-50 text-emerald-600"
  class="flex items-center gap-4 px-6 py-4 rounded-2xl font-headline text-[14px] font-black tracking-tight group shadow-sm transition-all text-[#C2C9D1] hover:text-[#1A1C1B] hover:bg-slate-50"
>
  <i :class="item.icon" class="text-lg transition-transform group-hover:scale-110"></i>
  <span>{{ item.label }}</span>
</router-link>
```

User card:

```vue
<div class="relative cursor-pointer group" @click="openProfileCard">
  <img :src="avatarUrl" alt="Avatar" class="w-11 h-11 rounded-full object-cover">
</div>
```

Nut logout:

```vue
<button @click="handleLogout">
  Đăng xuất
</button>
```

### 12.3. Main Va Header

Main can cach sidebar bang `ml-72`:

```vue
<main class="ml-72 min-h-screen flex-1 flex flex-col bg-white overflow-x-hidden">
  <header class="sticky top-0 z-40 shrink-0 bg-white/95 backdrop-blur-3xl border-b border-white px-10 pt-10">
    <!-- Search + notification -->
  </header>

  <router-view :user="user" />
</main>
```

`router-view` la diem render cac trang con. Truyen `:user="user"` de cac trang con co the nhan prop `user`.

### 12.4. Notification Dropdown

Nut chuong:

```vue
<button @click="toggleNotifications" class="relative">
  <i class="fa-solid fa-bell"></i>
  <span v-if="unreadCount > 0">
    {{ unreadCount > 9 ? '9+' : unreadCount }}
  </span>
</button>
```

Dropdown:

```vue
<div v-if="isNotiOpen" class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-2xl">
  <button @click="markAllAsRead">Đánh dấu đã đọc</button>

  <div v-if="notifications.length === 0">
    Không có thông báo mới
  </div>

  <div
    v-for="notification in notifications"
    v-else
    :key="notification.id"
    @click="handleNotiClick(notification)"
  >
    <p>{{ notification.title }}</p>
    <p>{{ notification.message }}</p>
    <p>{{ formatTimeAgo(notification.created_at) }}</p>
  </div>
</div>
```

Nen tranh viet `v-if` va `v-for` tren cung mot element neu co the. Viet ro rang hon:

```vue
<template v-if="notifications.length > 0">
  <div
    v-for="notification in notifications"
    :key="notification.id"
  >
    ...
  </div>
</template>
```

## 13. Style Scoped

File hien tai import Google Fonts trong `<style scoped>`. Co the giu:

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline {
  font-family: 'Manrope', sans-serif;
}

.font-body {
  font-family: 'Inter', sans-serif;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
```

Neu project da cau hinh font trong Tailwind/global CSS, nen dua font ra global de tranh moi layout import font rieng.

## 14. Bo Khung Code Day Du De Viet Lai

Day la khung rut gon, dung de bat dau viet lai file:

```vue
<template>
  <div class="user-dashboard-root flex min-h-screen bg-white font-body selection:bg-emerald-100 overflow-x-hidden">
    <div v-if="isProfileModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4 py-6 backdrop-blur-md">
      <!-- Viet modal cap nhat ho so o day -->
    </div>

    <aside class="fixed left-0 top-0 z-50 flex h-full w-72 flex-col border-r border-[#F1F3F3] bg-white">
      <!-- Viet logo, menu, user card, logout o day -->
    </aside>

    <main class="ml-72 min-h-screen flex-1 flex flex-col bg-white overflow-x-hidden">
      <header class="sticky top-0 z-40 shrink-0 bg-white/95 backdrop-blur-3xl border-b border-white px-10 pt-10">
        <!-- Viet search va notification o day -->
      </header>

      <router-view :user="user" />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession, updateAuthUser } from '../../utils/auth'
import { openConfirm } from '../../utils/confirm'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'

const router = useRouter()

const user = ref({ full_name: 'Học viên', email: '', role: 'student' })
const isProfileModalOpen = ref(false)
const isSavingProfile = ref(false)
const profileForm = ref({ full_name: '', email: '', phone: '', new_password: '', confirm_password: '' })
const profileMessage = ref('')
const profileMessageType = ref('success')
const isNotiOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
let notiTimer = null

const menuItems = [
  { to: '/user/dashboard', label: 'Khóa học của tôi', icon: 'fa-solid fa-book-open-reader' },
  { to: '/user/assignments', label: 'Bài tập', icon: 'fa-solid fa-rectangle-list' },
  { to: '/user/leave-request', label: 'Đơn xin nghỉ', icon: 'fa-solid fa-calendar-xmark' },
  { to: '/user/makeup-class', label: 'Đăng ký học bù', icon: 'fa-solid fa-rotate-right' },
  { to: '/user/support', label: 'Hỗ trợ', icon: 'fa-solid fa-life-ring' }
]

const avatarUrl = computed(() => {
  const name = encodeURIComponent(user.value.full_name || 'Học viên')
  return `https://ui-avatars.com/api/?name=${name}&background=111&color=ffffff&rounded=true&size=128&bold=true`
})

const profileMessageClass = computed(() =>
  profileMessageType.value === 'error'
    ? 'bg-red-50 text-red-600 border-red-100 font-bold'
    : 'bg-emerald-50 text-emerald-600 border-emerald-100 font-bold'
)

// Them cac ham: fetchUserInfo, openProfileCard, closeProfileModal,
// submitProfileUpdate, handleLogout, toggleNotifications,
// fetchNotifications, markAllAsRead, handleNotiClick, formatTimeAgo.

onMounted(() => {
  fetchUserInfo()
  fetchNotifications()
  notiTimer = setInterval(fetchNotifications, 60000)
})

onUnmounted(() => {
  if (notiTimer) {
    clearInterval(notiTimer)
    notiTimer = null
  }
})
</script>
```

## 15. Checklist Khi Viet Xong

Kiem tra cac muc sau:

- Click avatar mo modal profile.
- Modal load dung `full_name`, `email`, `phone`.
- Submit profile thieu ten/email bi canh bao.
- Doi mat khau duoi 6 ky tu bi canh bao.
- Confirm password khong khop bi canh bao.
- Cap nhat thanh cong thi ten tren sidebar thay doi.
- Click logout mo confirm, dong y thi ve `/login`.
- Nut chuong hien badge unread.
- Mo dropdown thong bao lay du lieu tu API.
- Click "Danh dau da doc" lam `unreadCount` ve 0.
- Click notification co `link` thi router chuyen trang.
- Cac route `/user/dashboard`, `/user/assignments`, `/user/support`, `/user/leave-request`, `/user/makeup-class` van render trong `<router-view>`.

## 16. Loi De Gap

- Quen import `onUnmounted` nhung lai dung trong code.
- Quen `@submit.prevent`, lam form reload trang.
- Quen `JSON.stringify(profileForm.value)` khi goi API `PUT`.
- Quen `Content-Type` khong sao vi `apiFetch` da tu set khi body khong phai `FormData`.
- De `notiTimer` trong `ref` khong can thiet; timer id chi can bien thuong.
- Dung `v-if` va `v-for` tren cung mot element lam template kho doc.
- Khong truyen `:user="user"` vao `router-view`, lam trang con khong nhan duoc prop.
- Khong clear timer khi logout hoac unmount, lam API thong bao tiep tuc chay.

## 17. Goi Y Cai Thien So Voi File Hien Tai

- Them `onUnmounted` de clear interval.
- Dua menu sidebar vao `menuItems` va render bang `v-for`.
- Sua encoding tieng Viet ve UTF-8.
- Tach modal profile va notification dropdown thanh component rieng neu file qua dai.
- Dong dropdown notification khi click ra ngoai neu can UX tot hon.
- Xu ly loi API notification co chon loc, khong nen toast moi 60 giay neu server loi.

