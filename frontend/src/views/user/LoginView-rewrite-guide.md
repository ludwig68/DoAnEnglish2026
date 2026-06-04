# Hướng Dẫn Viết Lại `LoginView.vue` Từ Đầu

Tài liệu này hướng dẫn viết lại file `frontend/src/views/user/LoginView.vue` theo từng bước. Trang này là màn hình đăng nhập của hệ thống English Learning, chịu trách nhiệm hiển thị form đăng nhập, gọi API xác thực, lưu phiên đăng nhập và điều hướng người dùng theo vai trò.

> Lưu ý quan trọng: file `LoginView.vue` hiện tại bị lỗi encoding tiếng Việt ở nhiều đoạn text và comment. Khi viết lại, hãy lưu file bằng UTF-8 và gõ lại toàn bộ tiếng Việt có dấu chuẩn, không copy các chuỗi đang bị lỗi như `ChÃ o má»«ng`, `ÄÄƒng nháº­p`, `QuÃªn máº­t kháº©u`.

## 1. Hiểu Nhiệm Vụ Của File

`LoginView.vue` là route công khai tại `/login`, được khai báo trong `frontend/src/router/index.js`:

```js
{ path: '/login', name: 'login', component: LoginView }
```

Router guard hiện tại có logic:

```js
if ((to.path === '/login' || to.path === '/register') && user) {
  if (user.role === 'admin') return next('/admin')
  if (user.role === 'instructor') return next('/teacher/dashboard')
  return next('/user/dashboard')
}
```

Nghĩa là:

- Nếu chưa đăng nhập, người dùng được phép vào `/login`.
- Nếu đã đăng nhập, người dùng bị chuyển khỏi `/login`.
- Admin đi tới `/admin`.
- Giảng viên đi tới `/teacher/dashboard`.
- Học viên đi tới `/user/dashboard`.

Nhiệm vụ chính của trang đăng nhập:

- Hiển thị giao diện chia 2 cột trên desktop.
- Hiển thị form đăng nhập trên mobile và desktop.
- Cho phép nhập email hoặc tên đăng nhập.
- Cho phép nhập mật khẩu và bật/tắt hiển thị mật khẩu.
- Gọi API `auth/login.php`.
- Lưu token và user bằng `setAuthSession`.
- Điều hướng theo `user.role`.
- Hiển thị thông báo lỗi hoặc thành công.

## 2. Cấu Trúc File Nên Viết

Một Vue Single File Component nên giữ đúng 3 phần:

```vue
<template>
  <!-- Giao diện đăng nhập -->
</template>

<script setup>
// Import, state, computed/methods nếu cần
</script>

<style scoped>
/* CSS riêng cho LoginView */
</style>
```

Thứ tự viết nên là:

1. Viết `<script setup>` trước để xác định state và hàm xử lý.
2. Viết `<template>` theo từng khối: panel trái, panel phải, form.
3. Viết `<style scoped>` cuối cùng.

Cách này giúp template không bị thiếu biến như `formData`, `showPassword`, `isLoading`, `errorMessage`.

## 3. Import Cần Có

Trong `<script setup>`, trang đăng nhập chỉ cần 4 import chính:

```js
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { setAuthSession } from '../../utils/auth'
```

Giải thích:

- `ref`: tạo state reactive cho form, loading, message.
- `useRouter`: dùng `router.push()` sau khi đăng nhập thành công.
- `apiFetch`: gọi backend theo base URL đã cấu hình trong `utils/api.js`.
- `setAuthSession`: lưu token và user vào localStorage thông qua `utils/auth.js`.

Không cần import `computed`, `onMounted`, `onUnmounted` nếu trang không có dữ liệu tính toán hoặc timer.

## 4. Khai Báo State

Khởi tạo router:

```js
const router = useRouter()
```

State form:

```js
const formData = ref({
  email: '',
  password: '',
  remember: false
})
```

Tên field `email` đang được backend dùng khi gọi API. Nếu giao diện ghi “Email hoặc tên đăng nhập”, vẫn có thể giữ key là `email` để không phải sửa backend, nhưng nên hiểu đây là định danh đăng nhập.

State UI:

```js
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
```

Nên dùng tên `showPassword` thay vì `showPw` để code dễ đọc hơn.

## 5. Hàm Điều Hướng Theo Vai Trò

Nên tách logic điều hướng ra thành hàm riêng để `handleSubmit` gọn hơn:

```js
const redirectByRole = (user) => {
  if (user.role === 'admin') {
    router.push('/admin')
    return
  }

  if (user.role === 'instructor') {
    router.push('/teacher/dashboard')
    return
  }

  router.push('/user/dashboard')
}
```

Lý do:

- Dễ đọc hơn so với viết nhiều `if` trong `handleSubmit`.
- Dễ sửa nếu sau này thêm role mới.
- Đồng bộ với router guard hiện tại.

## 6. Hàm Submit Đăng Nhập

Form nên dùng `@submit.prevent="handleSubmit"` để tránh reload trang.

Hàm xử lý cơ bản:

```js
const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  isLoading.value = true

  try {
    const response = await apiFetch('auth/login.php', {
      method: 'POST',
      body: JSON.stringify({
        email: formData.value.email.trim(),
        password: formData.value.password
      })
    })

    const result = await response.json()

    if (result.status !== 'success') {
      errorMessage.value = result.message || 'Thông tin đăng nhập không chính xác.'
      return
    }

    setAuthSession({
      token: result.token,
      user: result.user
    })

    successMessage.value = 'Đăng nhập thành công. Đang chuyển hướng...'

    window.setTimeout(() => {
      redirectByRole(result.user)
    }, 800)
  } catch (error) {
    errorMessage.value = 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.'
    console.error('Login error:', error)
  } finally {
    isLoading.value = false
  }
}
```

Điểm cần chú ý:

- Luôn reset `errorMessage` và `successMessage` trước khi submit.
- Dùng `.trim()` cho email/tên đăng nhập.
- Không cần tự set `Content-Type`, vì `apiFetch` đã tự set khi body là JSON string.
- Sau khi login thành công, gọi `setAuthSession` trước rồi mới chuyển trang.
- Message nên viết tiếng Việt thống nhất, không dùng `Welcome home! Redirecting...` nếu UI chính là tiếng Việt.

## 7. Validate Form Phía Frontend

HTML có thể dùng `required`, nhưng vẫn nên validate trong JavaScript để thông báo rõ hơn:

```js
const validateForm = () => {
  if (!formData.value.email.trim()) {
    errorMessage.value = 'Vui lòng nhập email hoặc tên đăng nhập.'
    return false
  }

  if (!formData.value.password) {
    errorMessage.value = 'Vui lòng nhập mật khẩu.'
    return false
  }

  return true
}
```

Sau đó gọi trong `handleSubmit`:

```js
const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!validateForm()) return

  isLoading.value = true
  // Gọi API ở đây
}
```

Lưu ý: nếu thật sự cho phép “email hoặc tên đăng nhập”, input nên dùng `type="text"` thay vì `type="email"`. Nếu để `type="email"`, trình duyệt sẽ chặn username không có định dạng email.

## 8. Viết Template Tổng Thể

Khung ngoài:

```vue
<template>
  <div class="h-screen flex flex-col md:flex-row bg-white overflow-hidden selection:bg-emerald-100 selection:text-emerald-900 font-body">
    <!-- Panel trái -->
    <!-- Panel phải chứa form -->
  </div>
</template>
```

Ý nghĩa class:

- `h-screen`: chiều cao bằng màn hình.
- `flex flex-col md:flex-row`: mobile xếp dọc, desktop chia ngang.
- `overflow-hidden`: tránh scroll ngoài ý muốn.
- `font-body`: dùng font Inter đã khai báo trong style.

## 9. Panel Trái Trên Desktop

Panel trái hiện tại chỉ hiển thị từ breakpoint `md` trở lên:

```vue
<section
  class="hidden md:flex md:w-1/2 relative p-20 flex-col justify-between overflow-hidden"
  style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
>
  <!-- Background image -->
  <!-- Logo -->
  <!-- Quote -->
  <!-- Footer -->
</section>
```

Nội dung nên gồm:

- Ảnh nền thư viện hoặc học tập.
- Logo English Learning.
- Câu trích dẫn tạo cảm hứng.
- Footer bản quyền.

Ví dụ text tiếng Việt/Anh chuẩn:

```vue
<h1 class="text-white font-headline font-black text-xl uppercase tracking-[0.2em]">
  English Learning
</h1>

<p class="text-xs font-black text-emerald-300 uppercase tracking-[0.4em] mb-6">
  Curating Excellence
</p>

<h2 class="text-5xl lg:text-6xl font-headline font-black text-white leading-[1.1] mb-12 drop-shadow-2xl max-w-2xl italic tracking-tight">
  "The beautiful thing about learning is that no one can take it away from you."
</h2>
```

Nếu muốn toàn bộ UI tiếng Việt, có thể đổi quote sang tiếng Việt:

```vue
<h2>
  "Điều đẹp nhất của việc học là không ai có thể lấy nó khỏi bạn."
</h2>
```

## 10. Panel Phải Chứa Form

Panel phải luôn hiển thị:

```vue
<section class="w-full md:w-1/2 flex items-center justify-center bg-white p-8 sm:p-20 relative overflow-y-auto">
  <div class="max-w-md w-full space-y-10">
    <!-- Nút quay lại -->
    <!-- Header -->
    <!-- Message -->
    <!-- Form -->
    <!-- Link đăng ký -->
    <!-- Footer links -->
  </div>
</section>
```

Nút quay lại:

```vue
<router-link
  to="/"
  class="inline-flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-emerald-600 transition-all group"
>
  <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
  Quay lại Trang chủ
</router-link>
```

Header:

```vue
<h2 class="text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight">
  Chào mừng trở lại
</h2>
<p class="text-slate-400 text-sm font-bold leading-relaxed max-w-sm">
  Đăng nhập để tiếp tục hành trình chinh phục Anh ngữ của bạn tại English Learning.
</p>
```

## 11. Hiển Thị Message

File hiện tại dùng `transition-group`. Có thể giữ:

```vue
<transition-group name="msg">
  <div
    v-if="errorMessage"
    key="error"
    class="p-4 rounded-2xl bg-red-50 border border-red-100 flex items-center gap-3 text-red-500 text-xs font-bold uppercase tracking-tight shadow-sm"
  >
    <i class="fa-solid fa-triangle-exclamation"></i>
    {{ errorMessage }}
  </div>

  <div
    v-if="successMessage"
    key="success"
    class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 text-emerald-600 text-xs font-bold uppercase tracking-tight shadow-sm"
  >
    <i class="fa-solid fa-circle-check"></i>
    {{ successMessage }}
  </div>
</transition-group>
```

Nên dùng key rõ nghĩa như `error`, `success`, thay vì `err`, `suc`.

## 12. Form Đăng Nhập

Form:

```vue
<form @submit.prevent="handleSubmit" class="space-y-8">
  <!-- Email hoặc tên đăng nhập -->
  <!-- Mật khẩu -->
  <!-- Ghi nhớ đăng nhập -->
  <!-- Nút submit -->
</form>
```

Input email hoặc username:

```vue
<input
  v-model="formData.email"
  type="text"
  autocomplete="username"
  placeholder="email@example.com hoặc tên đăng nhập"
  required
  class="w-full pl-14 pr-6 py-5 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
>
```

Lý do dùng `type="text"`: label là “Email hoặc tên đăng nhập”, nên input phải cho phép cả username. Nếu backend chỉ nhận email, đổi label thành “Email” và giữ `type="email"`.

Input password:

```vue
<input
  v-model="formData.password"
  :type="showPassword ? 'text' : 'password'"
  autocomplete="current-password"
  placeholder="••••••••"
  required
  class="w-full pl-14 pr-16 py-5 bg-[#F3F4F6] border-2 border-transparent rounded-[1.25rem] text-sm font-bold text-slate-700 outline-none transition-all placeholder:text-slate-300 focus:bg-white focus:border-emerald-500/30 focus:shadow-xl focus:shadow-emerald-500/5"
>
```

Nút bật/tắt mật khẩu:

```vue
<button
  type="button"
  @click="showPassword = !showPassword"
  class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 hover:text-emerald-500 transition-all"
>
  <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
</button>
```

Checkbox ghi nhớ đăng nhập:

```vue
<input
  id="remember"
  v-model="formData.remember"
  type="checkbox"
  class="w-5 h-5 rounded-lg border-slate-200 text-emerald-500 focus:ring-emerald-500/20 cursor-pointer"
>
```

Hiện tại checkbox trong file gốc chưa bind `v-model`, nên click checkbox không ảnh hưởng logic. Nếu backend/chức năng chưa hỗ trợ “ghi nhớ 30 ngày”, có 2 lựa chọn:

- Bỏ checkbox để tránh gây hiểu nhầm.
- Giữ checkbox nhưng cần backend/token lifetime hỗ trợ tương ứng.

## 13. Nút Submit

Nút submit nên disable khi đang gọi API:

```vue
<button
  type="submit"
  :disabled="isLoading"
  class="w-full py-5 text-white rounded-[1.25rem] text-sm font-black uppercase tracking-[0.2em] flex items-center justify-center gap-3 transition-all hover:shadow-2xl hover:shadow-emerald-500/20 active:scale-95 disabled:opacity-50"
  style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
>
  <span>{{ isLoading ? 'Đang xử lý...' : 'Đăng nhập' }}</span>
  <i v-if="isLoading" class="fa-solid fa-spinner animate-spin"></i>
  <i v-else class="fa-solid fa-arrow-right-long text-xs"></i>
</button>
```

File gốc có đoạn:

```vue
<i class="fa-solid fa-spinner animate-spin v-else" v-if="isLoading"></i>
```

Đây là cách viết không tốt vì `v-else` bị đặt nhầm trong `class`. Khi viết lại, dùng `v-if` và `v-else` đúng như ví dụ ở trên.

## 14. Link Đăng Ký Và Footer

Link đăng ký:

```vue
<p class="text-center text-slate-400 text-xs font-bold leading-relaxed max-w-sm mx-auto opacity-80">
  Chưa có tài khoản?
  <router-link to="/register" class="text-emerald-600 hover:text-emerald-700 underline underline-offset-4 ml-1">
    Đăng ký ngay
  </router-link>
</p>
```

Footer links:

```vue
<div class="flex items-center justify-center gap-10 text-[9px] font-black text-slate-300 uppercase tracking-[0.25em]">
  <a href="#" class="hover:text-emerald-500 transition-colors">Chính sách bảo mật</a>
  <a href="#" class="hover:text-emerald-500 transition-colors">Điều khoản sử dụng</a>
</div>
```

Nếu chưa có trang chính sách/điều khoản, nên để `href="#"` tạm thời hoặc thay bằng route thật khi có.

## 15. Style Scoped

Có thể giữ style tương tự file hiện tại, nhưng comment nên viết tiếng Việt UTF-8 chuẩn:

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline {
  font-family: 'Manrope', sans-serif;
}

.font-body {
  font-family: 'Inter', sans-serif;
}

.msg-enter-active,
.msg-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.msg-enter-from,
.msg-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.overflow-y-auto {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.overflow-y-auto::-webkit-scrollbar {
  display: none;
}
```

Không nên dùng selector `*` trong `scoped` để áp transition cho toàn bộ phần tử nếu không thật sự cần:

```css
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-duration: 300ms;
}
```

Lý do: nó có thể làm mọi element animate ngoài ý muốn, gây cảm giác chậm hoặc khó debug. Nên đặt `transition-all` trực tiếp trên element cần hiệu ứng bằng Tailwind.

## 16. Bộ Khung Code Đầy Đủ Để Viết Lại

Đây là bộ khung có thể dùng làm nền:

```vue
<template>
  <div class="h-screen flex flex-col md:flex-row bg-white overflow-hidden selection:bg-emerald-100 selection:text-emerald-900 font-body">
    <section
      class="hidden md:flex md:w-1/2 relative p-20 flex-col justify-between overflow-hidden"
      style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
    >
      <!-- Panel giới thiệu bên trái -->
    </section>

    <section class="w-full md:w-1/2 flex items-center justify-center bg-white p-8 sm:p-20 relative overflow-y-auto">
      <div class="max-w-md w-full space-y-10">
        <router-link to="/" class="inline-flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-emerald-600 transition-all group">
          <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
          Quay lại Trang chủ
        </router-link>

        <div class="space-y-4">
          <h2 class="text-4xl font-headline font-black text-slate-800 tracking-tight leading-tight">
            Chào mừng trở lại
          </h2>
          <p class="text-slate-400 text-sm font-bold leading-relaxed max-w-sm">
            Đăng nhập để tiếp tục hành trình chinh phục Anh ngữ của bạn tại English Learning.
          </p>
        </div>

        <transition-group name="msg">
          <div v-if="errorMessage" key="error" class="p-4 rounded-2xl bg-red-50 border border-red-100 flex items-center gap-3 text-red-500 text-xs font-bold uppercase tracking-tight shadow-sm">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ errorMessage }}
          </div>
          <div v-if="successMessage" key="success" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 text-emerald-600 text-xs font-bold uppercase tracking-tight shadow-sm">
            <i class="fa-solid fa-circle-check"></i>
            {{ successMessage }}
          </div>
        </transition-group>

        <form @submit.prevent="handleSubmit" class="space-y-8">
          <!-- Input email/username -->
          <!-- Input password -->
          <!-- Checkbox remember -->
          <!-- Submit button -->
        </form>

        <p class="text-center text-slate-400 text-xs font-bold leading-relaxed max-w-sm mx-auto opacity-80">
          Chưa có tài khoản?
          <router-link to="/register" class="text-emerald-600 hover:text-emerald-700 underline underline-offset-4 ml-1">
            Đăng ký ngay
          </router-link>
        </p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { setAuthSession } from '../../utils/auth'

const router = useRouter()

const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const formData = ref({
  email: '',
  password: '',
  remember: false
})

const validateForm = () => {
  if (!formData.value.email.trim()) {
    errorMessage.value = 'Vui lòng nhập email hoặc tên đăng nhập.'
    return false
  }

  if (!formData.value.password) {
    errorMessage.value = 'Vui lòng nhập mật khẩu.'
    return false
  }

  return true
}

const redirectByRole = (user) => {
  if (user.role === 'admin') {
    router.push('/admin')
    return
  }

  if (user.role === 'instructor') {
    router.push('/teacher/dashboard')
    return
  }

  router.push('/user/dashboard')
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!validateForm()) return

  isLoading.value = true

  try {
    const response = await apiFetch('auth/login.php', {
      method: 'POST',
      body: JSON.stringify({
        email: formData.value.email.trim(),
        password: formData.value.password
      })
    })

    const result = await response.json()

    if (result.status !== 'success') {
      errorMessage.value = result.message || 'Thông tin đăng nhập không chính xác.'
      return
    }

    setAuthSession({
      token: result.token,
      user: result.user
    })

    successMessage.value = 'Đăng nhập thành công. Đang chuyển hướng...'

    window.setTimeout(() => {
      redirectByRole(result.user)
    }, 800)
  } catch (error) {
    errorMessage.value = 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.'
    console.error('Login error:', error)
  } finally {
    isLoading.value = false
  }
}
</script>
```

## 17. Checklist Khi Viết Xong

Kiểm tra các mục sau:

- Trang `/login` mở được khi chưa đăng nhập.
- Nếu đã đăng nhập, router tự chuyển khỏi `/login`.
- Text tiếng Việt hiển thị đúng dấu, không còn ký tự lỗi.
- Click “Quay lại Trang chủ” về `/`.
- Nhập thiếu email/tên đăng nhập hiển thị lỗi.
- Nhập thiếu mật khẩu hiển thị lỗi.
- Click icon mắt bật/tắt hiển thị mật khẩu.
- Khi submit, nút bị disable và hiển thị “Đang xử lý...”.
- Sai tài khoản/mật khẩu hiển thị lỗi từ backend hoặc fallback.
- Đăng nhập thành công lưu session bằng `setAuthSession`.
- Admin chuyển tới `/admin`.
- Instructor chuyển tới `/teacher/dashboard`.
- Student/user chuyển tới `/user/dashboard`.
- Link “Đăng ký ngay” chuyển tới `/register`.
- Giao diện mobile không bị tràn ngang.

## 18. Lỗi Dễ Gặp

- Dùng `type="email"` trong khi label ghi “Email hoặc tên đăng nhập”, làm username bị browser chặn.
- Quên `@submit.prevent`, khiến trang reload khi submit.
- Quên `JSON.stringify(...)` khi gửi body.
- Quên gọi `setAuthSession` trước khi `router.push`.
- Dùng `showPw` trong template nhưng khai báo `showPassword` trong script, hoặc ngược lại.
- Đặt `v-else` nhầm trong `class`.
- Không reset `successMessage`, làm message cũ còn hiện khi submit lần sau.
- Không reset `errorMessage`, làm vừa hiện lỗi vừa hiện thành công.
- Checkbox “Ghi nhớ đăng nhập” không có `v-model`, khiến UI có nhưng không có tác dụng.
- Copy lại text bị lỗi encoding từ file cũ.

## 19. Gợi Ý Cải Thiện So Với File Hiện Tại

- Sửa toàn bộ text tiếng Việt sang UTF-8 chuẩn.
- Đổi `showPw` thành `showPassword`.
- Đổi input đăng nhập sang `type="text"` nếu hỗ trợ username.
- Tách `redirectByRole` và `validateForm` thành hàm riêng.
- Đổi message thành công sang tiếng Việt.
- Sửa icon loading bằng `v-if`/`v-else` đúng cú pháp.
- Cân nhắc bỏ hoặc hoàn thiện checkbox “Ghi nhớ đăng nhập trong 30 ngày”.
- Không dùng selector `*` để áp transition toàn cục trong component.

