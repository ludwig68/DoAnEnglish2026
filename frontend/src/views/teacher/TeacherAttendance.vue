<template>
  <div class="px-10 py-8 min-h-[calc(100vh-100px)] animate__animated animate__fadeIn">
    
    <!-- ── Bảng điều khiển tiêu đề (Breadcrumbs & Title) ── -->
    <div class="mb-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
      <div>
        <!-- Breadcrumbs -->
        <nav class="mb-4 flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
          <span class="hover:text-emerald-500 cursor-pointer transition-colors">Class Management</span>
          <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
          <span class="hover:text-emerald-500 cursor-pointer transition-colors">{{ sessionInfo?.course_title || 'IELTS ADVANCED WRITING' }}</span>
          <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
          <span class="text-emerald-500">Attendance Tracking</span>
        </nav>
        <h1 class="font-headline text-4xl font-black tracking-tight text-slate-900 leading-none">
          Attendance Management
        </h1>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-4">
        <button 
          @click="exportReport"
          class="flex items-center gap-3 rounded-2xl bg-white border border-slate-100 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-slate-600 shadow-sm hover:bg-slate-50 transition-all"
        >
          <i class="fa-solid fa-upload opacity-60"></i>
          Export Report
        </button>
        <button 
          @click="submitAttendance"
          :disabled="isSaving"
          class="flex items-center gap-3 rounded-2xl px-8 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
          style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
        >
          <i v-if="!isSaving" class="fa-solid fa-circle-check"></i>
          <span v-else class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          Save Attendance
        </button>
      </div>
    </div>

    <!-- ── Thẻ thông tin buổi học (Info Cards) ── -->
    <div class="mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Card: Date -->
      <div class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
          <i class="fa-regular fa-calendar-check text-xl"></i>
        </div>
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Class Date</p>
          <h3 class="text-sm font-bold text-slate-800 leading-tight">{{ formatFullDate(sessionInfo?.study_date) }}</h3>
        </div>
      </div>

      <!-- Card: Time -->
      <div class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
          <i class="fa-regular fa-clock text-xl"></i>
        </div>
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Time Session</p>
          <h3 class="text-sm font-bold text-slate-800 leading-tight">Session 1: {{ formatTime(sessionInfo?.start_time) }} - {{ formatTime(sessionInfo?.end_time) }}</h3>
        </div>
      </div>

      <!-- Card: Venue -->
      <div class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
          <i class="fa-solid fa-location-dot text-xl"></i>
        </div>
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Course Venue</p>
          <h3 class="text-sm font-bold text-slate-800 leading-tight truncate max-w-[150px]">{{ sessionInfo?.room_info || 'Phòng học lý thuyết' }}</h3>
        </div>
      </div>

      <!-- Card: Total -->
      <div class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
          <i class="fa-solid fa-users text-xl"></i>
        </div>
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Total Students</p>
          <h3 class="text-sm font-bold text-slate-800 leading-tight">
            <span class="text-emerald-500">{{ students.length }}</span> Enrolled
          </h3>
        </div>
      </div>
    </div>

    <!-- ── Khu vực Roll Call & Intel ── -->
    <div class="flex flex-col lg:flex-row gap-10">
      
      <!-- [LEFT]: Student Roll Call List -->
      <div class="flex-1">
        <div class="bg-white rounded-[3.5rem] border border-slate-50 p-10 shadow-[0_20px_60px_rgb(15,23,42,0.03)] flex flex-col h-full">
          
          <!-- Filters & Search -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-10">
             <h2 class="text-2xl font-headline font-black text-slate-800 tracking-tight">Student Roll Call</h2>
             
             <div class="flex items-center gap-6">
                <!-- Tabs -->
                <div class="flex items-center p-1 bg-slate-50 rounded-2xl border border-slate-100">
                   <button 
                    @click="filterType = 'all'"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl"
                    :class="filterType === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                   >All</button>
                   <button 
                    @click="filterType = 'unmarked'"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl"
                    :class="filterType === 'unmarked' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                   >Unmarked</button>
                </div>

                <!-- Search -->
                <div class="relative group">
                  <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
                  <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Search student..."
                    class="pl-11 pr-5 py-2.5 bg-slate-50 border-none rounded-2xl text-xs font-bold text-slate-700 outline-none ring-1 ring-slate-100 focus:ring-2 focus:ring-emerald-400/20 transition-all w-48 sm:w-64"
                  >
                </div>
             </div>
          </div>

          <!-- Student List -->
          <div class="overflow-x-auto min-h-[500px]">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b border-slate-50">
                  <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Học viên / Student</th>
                  <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Student ID</th>
                  <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Attendance %</th>
                  <th class="pb-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="student in filteredStudents" :key="student.id" class="group hover:bg-slate-50/40 transition-all">
                  <!-- Student Info -->
                  <td class="py-6 px-4">
                    <div class="flex items-center gap-4">
                      <div class="relative">
                        <img 
                          :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(student.full_name)}&background=random&color=fff&bold=true`" 
                          class="w-12 h-12 rounded-2xl object-cover" 
                          alt="Avatar"
                        >
                        <div 
                          class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white"
                          :class="student.student_type === 'makeup' ? 'bg-amber-400' : 'bg-emerald-400'"
                        ></div>
                      </div>
                      <div>
                        <h4 class="font-black text-[15px] text-slate-800 leading-tight mb-1">{{ student.full_name }}</h4>
                        <p class="text-[11px] font-bold text-slate-400 transition-colors group-hover:text-slate-500">{{ student.email }}</p>
                      </div>
                    </div>
                  </td>

                  <!-- Student ID -->
                  <td class="py-6 px-4">
                    <span class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                      #EG-{{ student.id.toString().padStart(6, '0') }}
                    </span>
                  </td>

                  <!-- Attendance % Progress Bar -->
                  <td class="py-6 px-4">
                    <div class="flex flex-col gap-2 min-w-[120px]">
                      <div class="flex items-center justify-between text-[11px] font-black">
                        <span :class="getPctColor(student.attendance_pct)">{{ student.attendance_pct }}%</span>
                      </div>
                      <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div 
                          class="h-full rounded-full transition-all duration-1000"
                          :class="getPctBg(student.attendance_pct)"
                          :style="{ width: student.attendance_pct + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>

                  <!-- Action Buttons -->
                  <td class="py-6 px-4 text-right">
                    <div class="flex items-center justify-end gap-2.5">
                      <!-- Present -->
                      <button 
                        @click="setStatus(student, 'present')"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="student.current_status === 'present' 
                          ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                          : 'bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-500'"
                      >
                        Present
                      </button>
                      <!-- Absent -->
                      <button 
                        @click="setStatus(student, 'absent')"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="student.current_status === 'absent' 
                          ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' 
                          : 'bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500'"
                      >
                        Absent
                      </button>
                      <!-- Late -->
                      <button 
                        @click="setStatus(student, 'late')"
                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        :class="student.current_status === 'late' 
                          ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' 
                          : 'bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-500'"
                      >
                        Late
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <!-- Page Nav stub -->
            <div class="mt-12 flex items-center justify-between border-t border-slate-50 pt-8">
               <p class="text-[10px] font-black text-slate-400 italic">Auto-saved draft at {{ lastSaveTime }}</p>
               <div class="flex items-center gap-4">
                  <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Page 1 of 2</span>
                  <div class="flex items-center gap-2">
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors">
                      <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors">
                      <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                  </div>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- [RIGHT]: Side Panel Intel -->
      <aside class="w-full lg:w-[380px] space-y-8">
        
        <!-- Session Intel Card -->
        <div class="rounded-[3.5rem] bg-slate-900 p-10 text-white relative overflow-hidden shadow-2xl group">
          <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-700"></div>
          
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-10">
               <h3 class="text-xl font-headline font-black tracking-tight">Session Intel</h3>
               <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                 <i class="fa-solid fa-chart-simple text-sm"></i>
               </div>
            </div>

            <!-- Large Score -->
            <div class="flex items-center gap-6 mb-12">
               <div class="relative w-32 h-32 flex items-center justify-center">
                  <svg class="w-full h-full transform -rotate-90">
                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-white/5"></circle>
                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" 
                      class="text-emerald-500 transition-all duration-1000 ease-out"
                      :stroke-dasharray="364"
                      :stroke-dashoffset="364 - (364 * completionRate / 100)"></circle>
                  </svg>
                  <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-black mb-0.5 tracking-tighter">{{ completionRate }}%</span>
                    <i class="fa-solid fa-arrow-trend-up text-emerald-500 text-sm"></i>
                  </div>
               </div>
               <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Completion Rate</p>
                  <p class="text-xs font-bold text-white/70 leading-relaxed">System is tracking real-time attendance accuracy.</p>
               </div>
            </div>

            <!-- Stats Rows -->
            <div class="space-y-4 mb-2">
               <div class="flex items-center justify-between p-5 rounded-[1.5rem] bg-white/5 border border-white/5">
                  <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                    <span class="text-xs font-black text-white/90">Present</span>
                  </div>
                  <span class="text-lg font-black">{{ stats.present }}</span>
               </div>
               <div class="flex items-center justify-between p-5 rounded-[1.5rem] bg-white/5 border border-white/5">
                  <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]"></div>
                    <span class="text-xs font-black text-white/90">Absent</span>
                  </div>
                  <span class="text-lg font-black">{{ stats.absent }}</span>
               </div>
               <div class="flex items-center justify-between p-5 rounded-[1.5rem] bg-white/5 border border-white/5">
                  <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]"></div>
                    <span class="text-xs font-black text-white/90">Late / Early</span>
                  </div>
                  <span class="text-lg font-black">{{ stats.late }}</span>
               </div>
            </div>
          </div>
        </div>

        <!-- Policy Reminder -->
        <div class="rounded-[3.5rem] bg-white border border-slate-50 p-10 shadow-sm flex items-start gap-6">
           <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
             <i class="fa-solid fa-lightbulb text-xl"></i>
           </div>
           <div>
             <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2">Policy Reminder:</h4>
             <p class="text-[11px] font-bold text-slate-400 leading-relaxed">Students exceeding 3 absences will trigger an automatic academic warning.</p>
           </div>
        </div>

        <!-- Add Session Notes Button -->
        <div 
          @click="toggleNotes"
          class="rounded-[3.5rem] border-2 border-dashed border-slate-100 p-10 flex flex-col items-center justify-center gap-4 cursor-pointer hover:bg-slate-50/50 hover:border-emerald-200 transition-all group py-12"
        >
           <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-all shadow-sm">
              <i class="fa-solid fa-file-signature text-xl"></i>
           </div>
           <span class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-emerald-600 transition-colors">Add Session Notes</span>
        </div>

      </aside>

    </div>

    <!-- ── Note Modal ── -->
    <div v-if="isNotesOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-md px-4 py-10 transition-all duration-500">
      <div class="w-full max-w-xl rounded-[3rem] bg-white shadow-2xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="p-10">
          <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-headline font-black text-slate-900 tracking-tight">Session Notes</h3>
            <button @click="isNotesOpen = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="text-xs font-bold text-slate-400 mb-6">Record any specific incidents, accomplishments, or materials covered in this session.</p>
          <textarea 
            v-model="sessionNote"
            rows="6" 
            class="w-full rounded-[2rem] border-2 border-slate-50 bg-slate-50 p-8 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-emerald-400/20 focus:ring-4 focus:ring-emerald-400/5 transition-all"
            placeholder="Write session summary here..."
          ></textarea>
          <div class="mt-8 flex justify-end">
            <button 
              @click="isNotesOpen = false"
              class="px-10 py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all outline-none"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
            >Save Note Draft</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'

const route = useRoute()
const router = useRouter()

// --- State ---
const isLoading = ref(true)
const isSaving = ref(false)
const sessionInfo = ref(null)
const students = ref([])
const filterType = ref('all') // 'all', 'unmarked'
const searchQuery = ref('')
const sessionNote = ref('')
const isNotesOpen = ref(false)
const lastSaveTime = ref(new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }))

// --- Attendance Intel Calculation ---
const stats = computed(() => {
  const s = { present: 0, absent: 0, late: 0, unmarked: 0 }
  students.value.forEach(std => {
    if (s[std.current_status] !== undefined) {
      s[std.current_status]++
    } else {
      s.unmarked++
    }
  })
  return s
})

const completionRate = computed(() => {
  const total = students.value.length
  if (total === 0) return 0
  const marked = total - stats.value.unmarked
  return Math.round((marked / total) * 100)
})

// --- Filtering ---
const filteredStudents = computed(() => {
  let list = students.value
  
  if (filterType.value === 'unmarked') {
    list = list.filter(s => s.current_status === 'unmarked')
  }
  
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(s => s.full_name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q))
  }
  
  return list
})

// --- Methods ---
const fetchAttendanceData = async (sid) => {
  isLoading.value = true
  try {
    const url = sid ? `teacher/attendance.php?schedule_id=${sid}` : 'teacher/attendance.php'
    const res = await apiFetch(url)
    const result = await res.json()
    
    if (result.status === 'success') {
      sessionInfo.value = result.data.session
      students.value = result.data.students
      sessionNote.value = result.data.session.session_note || ''
    } else {
      notifyError(result.message)
    }
  } catch (err) {
    notifyError("Failed to sync attendance data.")
  } finally {
    isLoading.value = false
  }
}

const setStatus = (student, status) => {
  if (student.current_status === status) {
    student.current_status = 'unmarked'
  } else {
    student.current_status = status
  }
}

const submitAttendance = async () => {
  isSaving.value = true
  try {
    const body = {
      schedule_id: sessionInfo.value.id,
      session_note: sessionNote.value,
      records: students.value.map(s => ({
        student_id: s.id,
        status: s.current_status,
        note: "" // Could add row-level notes later
      })).filter(r => r.status !== 'unmarked')
    }
    
    const res = await apiFetch('teacher/attendance.php', {
      method: 'POST',
      body: JSON.stringify(body)
    })
    const result = await res.json()
    
    if (result.status === 'success') {
      notifySuccess("Attendance saved successfully!")
      lastSaveTime.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
      // Reload to update percentages
      fetchAttendanceData(sessionInfo.value.id)
    } else {
      notifyError(result.message)
    }
  } catch (err) {
    notifyError("Server connection lost!")
  } finally {
    isSaving.value = false
  }
}

const toggleNotes = () => { isNotesOpen.value = true }

const exportReport = () => {
  notifySuccess("Preparing report download...")
}

// --- Helpers ---
const formatFullDate = (d) => {
  if (!d) return 'Wednesday, March 20'
  const date = new Date(d)
  return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
}

const formatTime = (t) => {
  if (!t) return '08:00'
  return t.substring(0, 5)
}

const getPctColor = (p) => {
  if (p < 60) return 'text-red-500'
  if (p < 80) return 'text-amber-500'
  return 'text-emerald-500'
}

const getPctBg = (p) => {
  if (p < 60) return 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.3)]'
  if (p < 80) return 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.3)]'
  return 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.3)]'
}

onMounted(() => {
  fetchAttendanceData(route.params.id)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline { font-family: 'Manrope', sans-serif; }
body { font-family: 'Inter', sans-serif; }

/* Table hover micro-interaction */
tr:hover td {
  transform: translateY(-1px);
}
td {
  transition: all 0.3s ease;
}

/* Custom Scrollbar for Intel Panel */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Ring pulse for unmarked tab if exists */
@keyframes ring-pulse {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>
