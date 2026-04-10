<template>
  <div class="flex-1 flex flex-col pt-4">

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 flex items-center justify-center py-32">
      <div class="w-12 h-12 border-4 border-slate-50 border-t-emerald-400 rounded-full animate-spin"></div>
    </div>

    <div v-else class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
      <div class="w-full px-10 pb-14">

        <!-- Page Title -->
        <div class="mb-8">
          <h2 class="text-[32px] font-headline font-black text-slate-800 tracking-tight leading-tight mb-2">Đăng ký học bù</h2>
          <p class="text-[13px] text-slate-500 font-medium leading-relaxed max-w-3xl">
            Hoàn thành tiến độ bằng cách đăng ký các buổi học đã lỡ. Vui lòng chọn buổi học bạn vắng mặt và chọn ca học bù phù hợp từ danh sách khả dụng phía dưới.
          </p>
        </div>

        <div class="grid grid-cols-12 gap-8">

           <!-- ═══ CỘT TRÁI: Chọn lớp đã nghỉ (4/12) ═══ -->
           <div class="col-span-12 lg:col-span-4 space-y-6">

             <!-- Card Lớp đã nghỉ -->
             <div class="bg-white rounded-[2rem] p-8 border shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
               <div class="flex items-center gap-3 mb-8">
                 <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center border border-emerald-100/50">
                   <i class="fa-regular fa-calendar-xmark text-lg"></i>
                 </div>
                 <h3 class="text-[18px] font-headline font-black text-slate-800 tracking-tight">Lớp đã nghỉ</h3>
               </div>

               <!-- Chọn khóa học -->
               <div class="space-y-3 mb-6">
                 <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">CHỌN KHÓA HỌC</label>
                 <div class="relative">
                   <select v-model="filterCourse" class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-5 py-3.5 pr-10 text-[13px] font-bold text-slate-700 outline-none transition focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-300 cursor-pointer">
                     <option value="">Tất cả khóa học</option>
                     <option v-for="c in uniqueCourses" :key="c" :value="c">{{ c }}</option>
                   </select>
                   <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                     <i class="fa-solid fa-chevron-down text-xs"></i>
                   </div>
                 </div>
               </div>

               <!-- Chọn buổi nghỉ (API Data) -->
               <div class="space-y-3">
                 <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">CHỌN BUỔI NGHỈ</label>
                 
                 <div v-if="filteredMissedSessions.length === 0" class="text-[12px] text-slate-500 italic py-4 border border-dashed border-slate-200 rounded-2xl text-center">
                   Không có đơn nghỉ nào (đã được duyệt).
                 </div>
                 <div v-else class="space-y-3 max-h-[300px] overflow-y-auto no-scrollbar pr-1">
                   <label v-for="ms in filteredMissedSessions" :key="ms.leave_request_id" class="flex items-center gap-4 p-4 rounded-2xl border cursor-pointer transition-all"
                     :class="selectedMissedSession === ms.leave_request_id ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-100 hover:border-emerald-200'">
                     <input type="radio" :value="ms.leave_request_id" v-model="selectedMissedSession" class="hidden" />
                     <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                       :class="selectedMissedSession === ms.leave_request_id ? 'border-emerald-500' : 'border-slate-200'">
                       <div v-if="selectedMissedSession === ms.leave_request_id" class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                     </div>
                     <div>
                       <p class="text-[13px] font-headline font-black text-slate-800 leading-tight mb-1">{{ ms.lesson_title }}</p>
                       <p class="text-[11px] font-medium text-slate-500">{{ getFullDayString(ms.study_date) }}</p>
                     </div>
                   </label>
                 </div>
               </div>
             </div>

             <!-- Allowance Widget -->
             <div class="bg-emerald-700 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-lg border border-emerald-800/50">
               <div class="absolute -bottom-8 -right-8 opacity-10 pointer-events-none">
                  <i class="fa-solid fa-book-open text-[120px]"></i>
               </div>
               <div class="relative z-10">
                 <p class="text-[11px] font-black uppercase tracking-widest text-emerald-100 mb-2">Hạn mức học bù</p>
                 <div class="flex items-baseline gap-2 mb-6">
                    <span class="text-[40px] font-headline font-black leading-none text-white">{{ Math.max(0, missedSessions.length - registeredSchedules.length) }} <span class="text-[28px] opacity-70">/{{ Math.max(3, missedSessions.length) }}</span></span>
                    <span class="text-[14px] font-bold text-emerald-200">lượt</span>
                 </div>
                 <div class="h-px bg-emerald-600/50 mb-4 w-full"></div>
                 <p class="text-[11px] font-medium leading-relaxed italic text-emerald-50/80">
                   "Việc đi học đều đặn là chìa khóa để đạt chuẩn đầu ra và thành thạo tiếng Anh."
                 </p>
               </div>
             </div>

           </div>

           <!-- ═══ CỘT PHẢI: Lịch học bù khả dụng (8/12) ═══ -->
           <div class="col-span-12 lg:col-span-8 space-y-6">

             <div class="bg-white rounded-[2rem] border shadow-[0_2px_10px_rgba(0,0,0,0.02)] p-8">
               <!-- Header -->
               <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
                 <div class="flex items-center gap-3">
                   <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center border border-emerald-100/50">
                     <i class="fa-regular fa-calendar-check text-lg"></i>
                   </div>
                   <h3 class="text-[18px] font-headline font-black text-slate-800 tracking-tight">Lịch học bù khả dụng</h3>
                 </div>

                 <div class="flex bg-white rounded-xl p-1 border border-slate-100 shadow-sm shrink-0">
                   <button class="px-5 py-2 text-[12px] font-black bg-slate-50 rounded-lg text-slate-800 shadow-sm border border-slate-100/50">Dạng danh sách</button>
                   <button class="px-5 py-2 text-[12px] font-black text-slate-400 hover:text-slate-600">Lịch</button>
                 </div>
               </div>

               <!-- Tags / Filters -->
               <div class="flex flex-wrap items-center gap-2 mb-8">
                 <button @click="filterTime = 'all'" :class="filterTime === 'all' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'" class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Tất cả</button>
                 <button @click="filterTime = 'morning'" :class="filterTime === 'morning' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'" class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Sáng</button>
                 <button @click="filterTime = 'afternoon'" :class="filterTime === 'afternoon' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'" class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Chiều</button>
                 <button @click="filterTime = 'evening'" :class="filterTime === 'evening' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'" class="px-5 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">Tối</button>
               </div>

               <!-- List Khả dụng -->
               <div v-if="filteredAvailable.length === 0" class="text-center py-12 relative border border-dashed border-slate-200 rounded-[1.5rem]">
                   <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300">
                     <i class="fa-regular fa-folder-open text-2xl"></i>
                   </div>
                   <p class="text-[13px] font-bold text-slate-500">Hiện không có ca học bù nào phù hợp với tìm kiếm.</p>
               </div>

               <div v-else class="space-y-4">
                 <div v-for="sch in filteredAvailable" :key="sch.id" class="flex flex-col md:flex-row md:items-center justify-between p-5 rounded-[1.5rem] border border-slate-100 bg-white hover:border-emerald-200 hover:shadow-md transition-all gap-5 group">
                   
                   <!-- Date Block -->
                   <div class="flex flex-col items-center justify-center px-4 shrink-0 min-w-[70px]">
                     <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-0.5">THG {{ getMonth(sch.study_date) }}</span>
                     <span class="text-[28px] font-headline font-black text-emerald-600 leading-none">{{ getDay(sch.study_date) }}</span>
                   </div>

                   <!-- Info grid -->
                   <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between min-w-0">
                     
                     <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-4 w-full">
                         <!-- Time -->
                         <div class="flex flex-col sm:border-l border-slate-100 sm:pl-5">
                           <span class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 mb-1.5 flex items-center gap-1.5">
                              Thời gian
                           </span>
                           <div class="flex items-start gap-2.5 text-[13px] font-bold text-slate-700 leading-snug">
                             <i class="fa-regular fa-clock text-[#C2C9D1] text-[15px] mt-[1.5px]"></i>
                             <div>
                               <p>{{ sch.start_time }} -</p>
                               <p>{{ sch.end_time }}</p>
                             </div>
                           </div>
                         </div>

                         <!-- Room -->
                         <div class="flex flex-col sm:border-l border-slate-100 sm:pl-5">
                           <span class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 mb-1.5 flex items-center gap-1.5">
                              Phòng học
                           </span>
                           <div class="flex items-start gap-2.5 text-[13px] font-bold text-slate-700 leading-snug truncate">
                             <i class="fa-solid fa-door-open text-[#C2C9D1] text-[15px] mt-[1.5px]"></i>
                             <p class="truncate break-words pr-2 whitespace-normal line-clamp-2 max-w-[100px] sm:max-w-none">{{ sch.room_info }}</p>
                           </div>
                         </div>

                         <!-- Teacher -->
                         <div class="flex flex-col sm:border-l border-slate-100 sm:pl-5">
                           <span class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400 mb-1.5 flex items-center gap-1.5">
                              Giảng viên
                           </span>
                           <div class="flex items-start gap-2.5 text-[13px] font-bold text-slate-700 leading-snug">
                             <i class="fa-regular fa-user text-[#C2C9D1] text-[15px] mt-[1.5px]"></i>
                             <p class="truncate max-w-[120px] sm:max-w-none">{{ sch.teacher_name || 'GV Chuyên môn' }}</p>
                           </div>
                         </div>
                     </div>
                   </div>

                   <!-- Register Button -->
                   <div class="shrink-0 pl-2 mt-4 md:mt-0 flex items-center justify-end">
                     <!-- Cố tình tạo mock Hết chỗ cho UI giống hệt -->
                     <button v-if="sch.id % 4 === 0" disabled class="px-8 py-3 bg-slate-200 text-slate-400 rounded-2xl text-[12px] font-black cursor-not-allowed w-full md:w-auto text-center border border-slate-200">
                       Hết chỗ
                     </button>
                     <button v-else @click="registerMakeUp(sch.id)" :disabled="isRegistering"
                       class="px-8 py-3 bg-[#006A38] text-white rounded-2xl text-[12px] font-black hover:bg-[#005128] transition-colors shadow-[0_4px_12px_rgba(0,106,56,0.2)] disabled:opacity-50 w-full md:w-auto text-center">
                       Đăng ký
                     </button>
                   </div>
                 </div>
               </div>

               <!-- Footer -->
               <div v-if="filteredAvailable.length > 0" class="mt-8 pt-6 flex items-center justify-between">
                 <p class="text-[12px] font-medium text-slate-400">Đang hiển thị <strong class="text-slate-600">{{ filteredAvailable.length }}</strong> ca học khả dụng.</p>
                 <button class="text-[11px] font-black text-emerald-700 flex items-center gap-1.5 transition-colors uppercase tracking-widest hover:text-emerald-800">
                   Tải thêm <i class="fa-solid fa-chevron-down text-[10px]"></i>
                 </button>
               </div>

             </div>

           </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '../../utils/api'
import { openConfirm } from '../../utils/confirm'
import { notifySuccess, notifyError } from '../../utils/notify'

const isLoading = ref(true)
const isRegistering = ref(false)

const availableSchedules = ref([])
const registeredSchedules = ref([])
const missedSessions = ref([])
const selectedMissedSession = ref(null)

const filterCourse = ref('')
const filterTime = ref('all') // all, morning, afternoon, evening

const uniqueCourses = computed(() => {
  const courses = new Set([
    ...availableSchedules.value.map(s => s.course_title),
    ...missedSessions.value.map(s => s.course_title)
  ])
  return Array.from(courses)
})

const filteredMissedSessions = computed(() => {
  if (!filterCourse.value) return missedSessions.value
  return missedSessions.value.filter(s => s.course_title === filterCourse.value)
})

const filteredAvailable = computed(() => {
  let res = [...availableSchedules.value]
  
  if (filterCourse.value) {
    res = res.filter(s => s.course_title === filterCourse.value)
  }

  if (filterTime.value !== 'all') {
    res = res.filter(s => {
      const hour = parseInt(s.start_time.split(':')[0])
      if (filterTime.value === 'morning') return hour < 12
      if (filterTime.value === 'afternoon') return hour >= 12 && hour < 18
      if (filterTime.value === 'evening') return hour >= 18
      return true
    })
  }

  return res
})

const fetchData = async () => {
  isLoading.value = true
  try {
    const res = await apiFetch('user/makeup_registrations.php')
    const result = await res.json()
    if (result.status === 'success') {
      registeredSchedules.value = result.data.registered || []
      availableSchedules.value = result.data.available || []
      missedSessions.value = result.data.missed_sessions || []
    }
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

const registerMakeUp = async (scheduleId) => {
  if (!selectedMissedSession.value) {
    notifyError('Vui lòng CHỌN BUỔI NGHỈ ở cột bên trái trước khi đăng ký!')
    return
  }

  const confirmed = await openConfirm({
    title: 'Xác nhận đăng ký học bù',
    message: "Bạn có chắc chắn muốn đăng ký tham gia ca học bù này cho buổi vắng mặt đã chọn?",
    confirmText: 'Đăng ký ngay',
    tone: 'success'
  })

  if (!confirmed) return

  isRegistering.value = true
  try {
    const res = await apiFetch('user/makeup_registrations.php', {
      method: 'POST',
      body: JSON.stringify({ 
        schedule_id: scheduleId,
        leave_request_id: selectedMissedSession.value 
      })
    })
    const result = await res.json()
    if (result.status === 'success') {
      notifySuccess(result.message)
      fetchData()
    } else {
      notifyError(result.message)
    }
  } catch {
    notifyError('Mất kết nối máy chủ.')
  } finally {
    isRegistering.value = false
  }
}

// FORMATTERS
const getFullDayString = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const days = ['Thứ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy']
  return `${days[d.getDay()]}, ${d.getDate().toString().padStart(2, '0')} Tháng ${d.getMonth() + 1}, ${d.getFullYear()}`
}

const getMonth = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).getMonth() + 1
}

const getDay = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).getDate()
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
.font-body { font-family: 'Inter', sans-serif; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
