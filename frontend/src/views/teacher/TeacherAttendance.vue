<template>
  <div class="px-10 py-8 min-h-[calc(100vh-100px)] animate__animated animate__fadeIn">

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- PHASE 1: Chọn lớp & buổi học (khi chưa có schedule_id) -->
    <!-- ══════════════════════════════════════════════════════ -->
    <template v-if="!selectedScheduleId">

      <!-- Header -->
      <div class="mb-12">
        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500 mb-3">Điểm danh học viên</p>
        <h1 class="text-4xl font-headline font-black text-slate-900 tracking-tight leading-none">Chọn buổi học để Điểm
          danh</h1>
        <p class="text-slate-400 text-sm mt-3 font-medium">
          Chọn lớp và buổi học bên dưới để bắt đầu điểm danh học viên.
        </p>
      </div>

      <!-- Loading -->
      <div v-if="isLoadingPicker" class="flex items-center justify-center py-32">
        <div class="flex flex-col items-center gap-4">
          <div class="w-12 h-12 border-4 border-emerald-50 border-t-emerald-500 rounded-full animate-spin"></div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-300 animate-pulse text-center">Đang tải
            danh sách buổi học...</p>
        </div>
      </div>

      <template v-else>
        <!-- Date Navigation -->
        <div class="flex items-center gap-4 mb-10">
          <button @click="changeDate(-1)"
            class="w-11 h-11 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all shadow-sm">
            <i class="fa-solid fa-chevron-left text-xs"></i>
          </button>
          <div class="flex items-center gap-3 bg-white border border-slate-100 rounded-2xl px-6 py-3 shadow-sm">
            <i class="fa-regular fa-calendar text-emerald-500 text-sm"></i>
            <span class="text-sm font-black text-slate-700">{{ formatPickerDate(pickerDate) }}</span>
            <span v-if="isToday"
              class="text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-lg">Hôm
              nay</span>
          </div>
          <button @click="changeDate(1)"
            class="w-11 h-11 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all shadow-sm">
            <i class="fa-solid fa-chevron-right text-xs"></i>
          </button>
          <button @click="goToToday"
            class="ml-2 px-5 py-3 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-sm">
            <i class="fa-solid fa-location-crosshairs mr-2"></i>Hôm nay
          </button>
        </div>

        <!-- Schedule Cards Grid -->
        <div v-if="pickerSchedules.length === 0"
          class="flex flex-col items-center justify-center py-32 text-slate-400 bg-white rounded-[3.5rem] border border-slate-50 shadow-sm relative overflow-hidden">
          <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50"></div>
          <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
            <i class="fa-solid fa-calendar-xmark text-4xl"></i>
          </div>
          <p class="font-black text-lg text-slate-700 mt-2">Không có buổi học nào</p>
          <p class="text-[13px] mt-1 font-bold text-slate-400">Không có ca dạy nào được xếp lịch vào ngày này.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
          <div v-for="schedule in pickerSchedules" :key="schedule.id"
            class="bg-white rounded-[3rem] p-8 border border-slate-50 shadow-[0_15px_45px_rgb(0,0,0,0.02)] flex flex-col relative overflow-hidden group"
            :class="(isAttendanceOpen(schedule) || schedule.attendance_checked) ? 'hover:shadow-2xl hover:shadow-emerald-500/5 hover:-translate-y-2 cursor-pointer transition-all duration-500' : 'opacity-80'"
            @click="(isAttendanceOpen(schedule) || schedule.attendance_checked) ? openAttendance(schedule.id) : null">
            <div
              class="absolute -right-20 -top-20 w-48 h-48 bg-emerald-50/30 rounded-full blur-3xl transition-all duration-700"
              :class="(isAttendanceOpen(schedule) || schedule.attendance_checked) ? 'group-hover:bg-emerald-100/50' : ''"></div>

            <!-- Top Badge -->
            <div class="flex items-center justify-between mb-6 relative z-10">
              <span
                class="text-[10px] font-black uppercase tracking-[0.2em] px-4 py-2 rounded-xl border border-slate-50 text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 group-hover:border-emerald-100 transition-all">
                {{ schedule.category_name || 'Chương trình' }}
              </span>
              <div class="flex items-center gap-2">
                <span v-if="schedule.attendance_checked"
                  class="text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg border border-emerald-100">
                  <i class="fa-solid fa-check mr-1"></i>Đã điểm danh
                </span>
                <span v-else
                  class="text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 px-3 py-1.5 rounded-lg border border-amber-100">
                  <i class="fa-regular fa-circle-dot mr-1"></i>Chưa điểm danh
                </span>
              </div>
            </div>

            <!-- Class Name -->
            <div class="mb-6 relative z-10">
              <h3
                class="font-headline font-black text-2xl text-slate-800 tracking-tight leading-tight mb-2 group-hover:text-emerald-600 transition-colors">
                {{ schedule.class_name }}
              </h3>
              <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1 line-clamp-1">
                {{ schedule.course_title }}
              </p>
              <p v-if="schedule.lesson_title" class="text-xs font-bold text-emerald-600 line-clamp-2">
                Bài học: {{ schedule.lesson_title }}
              </p>
            </div>

            <!-- Info Boxes -->
            <div class="grid grid-cols-3 gap-3 mb-8 relative z-10">
              <div
                class="bg-slate-50/70 rounded-2xl p-4 border border-transparent group-hover:border-emerald-50 transition-colors text-center">
                <p class="text-[9px] font-black uppercase text-slate-300 mb-1.5">Giờ bắt đầu</p>
                <p class="text-sm font-black text-slate-700">{{ schedule.start_time }}</p>
              </div>
              <div
                class="bg-slate-50/70 rounded-2xl p-4 border border-transparent group-hover:border-emerald-50 transition-colors text-center">
                <p class="text-[9px] font-black uppercase text-slate-300 mb-1.5">Giờ kết thúc</p>
                <p class="text-sm font-black text-slate-700">{{ schedule.end_time }}</p>
              </div>
              <div
                class="bg-slate-50/70 rounded-2xl p-4 border border-transparent group-hover:border-emerald-50 transition-colors text-center">
                <p class="text-[9px] font-black uppercase text-slate-300 mb-1.5">Sĩ số</p>
                <p class="text-sm font-black text-emerald-600">{{ schedule.student_count }}</p>
              </div>
            </div>

            <!-- Venue + Type -->
            <div class="flex items-center justify-between gap-4 mb-8 relative z-10">
              <div class="flex items-center gap-3 text-[11px] font-bold text-slate-400">
                <i class="fa-solid fa-location-dot text-emerald-400"></i>
                <span>{{ schedule.room_info || 'Phòng học chính' }}</span>
              </div>
              <span class="text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg"
                :class="schedule.teaching_type === 'online' ? 'bg-blue-50 text-blue-500' : 'bg-slate-50 text-slate-500'">
                {{ schedule.teaching_type === 'online' ? 'Trực tuyến' : 'Trực tiếp' }}
              </span>
            </div>

            <!-- Action Button -->
            <button v-if="schedule.attendance_checked"
              class="w-full py-4 rounded-[1.5rem] bg-emerald-50 border-2 border-emerald-100 hover:bg-emerald-100 text-emerald-700 font-headline font-black text-xs uppercase tracking-widest transition-all duration-300 relative z-10 group/btn mt-auto">
              <i class="fa-solid fa-eye mr-2 text-emerald-600"></i>
              Xem lại Điểm danh
              <i class="fa-solid fa-arrow-right-long ml-2 transition-transform group-hover/btn:translate-x-1"></i>
            </button>
            <button v-else-if="isAttendanceOpen(schedule)"
              class="w-full py-4 rounded-[1.5rem] bg-white border-2 border-slate-100 hover:border-emerald-500 hover:bg-emerald-500 hover:text-white text-slate-800 font-headline font-black text-xs uppercase tracking-widest transition-all duration-300 relative z-10 group/btn mt-auto">
              <i class="fa-solid fa-clipboard-check mr-2"></i>
              Bắt đầu Điểm danh
              <i class="fa-solid fa-arrow-right-long ml-2 transition-transform group-hover/btn:translate-x-1"></i>
            </button>
            <button v-else
              class="w-full py-4 rounded-[1.5rem] bg-slate-50 border-2 border-slate-100 text-slate-400 font-headline font-black text-xs uppercase tracking-widest cursor-not-allowed opacity-70 relative z-10 mt-auto"
              title="Chỉ mở điểm danh từ trước đến sau giờ học 15 phút">
              <i class="fa-solid fa-lock mr-2"></i>
              Chưa mở
            </button>
          </div>
        </div>
      </template>
    </template>


    <!-- ══════════════════════════════════════════════════════ -->
    <!-- PHASE 2: Giao diện Điểm danh chi tiết (có schedule_id) -->
    <!-- ══════════════════════════════════════════════════════ -->
    <template v-else>

      <!-- ── Bảng điều khiển tiêu đề ── -->
      <div class="mb-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div>
          <!-- Breadcrumbs -->
          <nav class="mb-4 flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
            <span @click="goBackToPicker" class="hover:text-emerald-500 cursor-pointer transition-colors">
              <i class="fa-solid fa-arrow-left mr-1.5"></i>Chọn buổi học
            </span>
            <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
            <span class="hover:text-emerald-500 cursor-pointer transition-colors">{{ sessionInfo?.course_title || 'Khóa học' }}</span>
            <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
            <span class="text-emerald-500">Điểm danh</span>
          </nav>
          <h1 class="font-headline text-4xl font-black tracking-tight text-slate-900 leading-none mb-3">
            {{ sessionInfo?.attendance_checked ? 'Chi tiết Điểm danh' : 'Quản lý Điểm danh' }}
          </h1>
          <p class="text-slate-400 text-sm font-medium">Lớp: <strong class="text-slate-600">{{ sessionInfo?.class_name }}</strong></p>
          <p v-if="sessionInfo?.lesson_title" class="text-emerald-600 text-sm font-bold mt-1">Bài: {{ sessionInfo?.lesson_title }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4">
          <button @click="exportReport"
            class="flex items-center gap-3 rounded-2xl bg-white border border-slate-100 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
            <i class="fa-solid fa-upload opacity-60"></i>
            Xuất Báo cáo
          </button>
          <button v-if="!sessionInfo?.attendance_checked" @click="submitAttendance" :disabled="isSaving || (sessionInfo && !isAttendanceOpen(sessionInfo))"
            class="flex items-center gap-3 rounded-2xl px-8 py-3.5 text-xs font-black uppercase tracking-widest text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="(sessionInfo && !isAttendanceOpen(sessionInfo)) ? 'bg-slate-400' : 'bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 hover:-translate-y-0.5'"
            :title="(sessionInfo && !isAttendanceOpen(sessionInfo)) ? 'Chỉ mở điểm danh từ trước đến sau giờ học 15 phút' : ''">
            <i v-if="!isSaving" class="fa-solid fa-circle-check"></i>
            <span v-else class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            Lưu điểm danh
          </button>
          <div v-else class="flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 text-xs font-black uppercase tracking-widest shadow-sm">
            <i class="fa-solid fa-lock"></i> Đã chốt sổ
          </div>
        </div>
      </div>

      <!-- ── Thẻ thông tin buổi học (Info Cards) ── -->
      <div class="mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card: Date -->
        <div
          class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
          <div
            class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
            <i class="fa-regular fa-calendar-check text-xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Ngày học</p>
            <h3 class="text-sm font-bold text-slate-800 leading-tight">{{ formatFullDate(sessionInfo?.study_date) }}
            </h3>
          </div>
        </div>

        <!-- Card: Time -->
        <div
          class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
          <div
            class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
            <i class="fa-regular fa-clock text-xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Thời gian</p>
            <h3 class="text-sm font-bold text-slate-800 leading-tight">Ca học: {{ formatTime(sessionInfo?.start_time) }}
              - {{ formatTime(sessionInfo?.end_time) }}</h3>
          </div>
        </div>

        <!-- Card: Venue -->
        <div
          class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
          <div
            class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
            <i class="fa-solid fa-location-dot text-xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Phòng học</p>
            <h3 class="text-sm font-bold text-slate-800 leading-tight truncate max-w-[150px]">{{ sessionInfo?.room_info
              || 'Phòng học lý thuyết' }}</h3>
          </div>
        </div>

        <!-- Card: Total -->
        <div
          class="rounded-[2.5rem] bg-white border border-slate-50/50 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex items-center gap-5 group hover:shadow-md transition-all">
          <div
            class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
            <i class="fa-solid fa-users text-xl"></i>
          </div>
          <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Sĩ số Lớp</p>
            <h3 class="text-sm font-bold text-slate-800 leading-tight">
              <span class="text-emerald-500">{{ students.length }}</span> Học viên
            </h3>
          </div>
        </div>
      </div>

      <!-- ── Khu vực Roll Call & Intel ── -->
      <div class="flex flex-col lg:flex-row gap-10">

        <!-- [LEFT]: Student Roll Call List -->
        <div class="flex-1">
          <div
            class="bg-white rounded-[3.5rem] border border-slate-50 p-10 shadow-[0_20px_60px_rgb(15,23,42,0.03)] flex flex-col h-full">

            <!-- Filters & Search -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-10">
              <h2 class="text-2xl font-headline font-black text-slate-800 tracking-tight">Danh sách Điểm danh</h2>

              <div class="flex items-center gap-6">
                <!-- Tabs -->
                <div class="flex items-center p-1 bg-slate-50 rounded-2xl border border-slate-100">
                  <button @click="filterType = 'all'"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl"
                    :class="filterType === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'">Tất
                    cả</button>
                  <button @click="filterType = 'unmarked'"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl"
                    :class="filterType === 'unmarked' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'">Chưa
                    điểm danh</button>
                </div>

                <!-- Search -->
                <div class="relative group">
                  <i
                    class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
                  <input v-model="searchQuery" type="text" placeholder="Tìm kiếm học viên..."
                    class="pl-11 pr-5 py-2.5 bg-slate-50 border-none rounded-2xl text-xs font-bold text-slate-700 outline-none ring-1 ring-slate-100 focus:ring-2 focus:ring-emerald-400/20 transition-all w-48 sm:w-64">
                </div>
              </div>
            </div>

            <!-- Student List -->
            <div class="overflow-x-auto min-h-[500px]">
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-slate-50">
                    <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Học viên</th>
                    <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Mã HV</th>
                    <th class="pb-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">Chuyên cần %
                    </th>
                    <th class="pb-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4">
                      Hành động</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="student in filteredStudents" :key="student.id"
                    class="group hover:bg-slate-50/40 transition-all">
                    <!-- Student Info -->
                    <td class="py-6 px-4">
                      <div class="flex items-center gap-4">
                        <div class="relative">
                          <img
                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(student.full_name)}&background=random&color=fff&bold=true`"
                            class="w-12 h-12 rounded-2xl object-cover" alt="Avatar">
                          <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white"
                            :class="student.student_type === 'makeup' ? 'bg-amber-400' : 'bg-emerald-400'"></div>
                        </div>
                        <div>
                          <h4 class="font-black text-[15px] text-slate-800 leading-tight mb-1">{{ student.full_name }}
                          </h4>
                          <p class="text-[11px] font-bold text-slate-400 transition-colors group-hover:text-slate-500">
                            {{ student.email }}</p>
                        </div>
                      </div>
                    </td>

                    <!-- Student ID -->
                    <td class="py-6 px-4">
                      <span
                        class="px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-xl text-[10px] font-bold text-slate-500 uppercase tracking-wider">
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
                          <div class="h-full rounded-full transition-all duration-1000"
                            :class="getPctBg(student.attendance_pct)" :style="{ width: student.attendance_pct + '%' }">
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Action Buttons -->
                    <td class="py-6 px-4 text-right">
                      <div class="flex items-center justify-end gap-2.5">
                        <template v-if="student.current_status === 'excused'">
                          <div title="Học viên đã được duyệt đơn xin phép"
                            class="px-5 py-2.5 rounded-xl bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest border border-blue-100 flex items-center gap-2 cursor-not-allowed shadow-sm select-none tooltip-trigger relative overflow-hidden">
                            <i class="fa-solid fa-file-contract"></i>
                            Đã xin phép
                            <div class="absolute inset-0 bg-blue-500/5 opacity-0 hover:opacity-100 transition-opacity">
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <!-- Present -->
                          <button @click="setStatus(student, 'present')"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                            :class="student.current_status === 'present'
                              ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                              : 'bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-500'">
                            Có mặt
                          </button>
                          <!-- Absent -->
                          <button @click="setStatus(student, 'absent')"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                            :class="student.current_status === 'absent'
                              ? 'bg-red-500 text-white shadow-lg shadow-red-500/20'
                              : 'bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500'">
                            Vắng mặt
                          </button>
                          <!-- Late -->
                          <button @click="setStatus(student, 'late')"
                            class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                            :class="student.current_status === 'late'
                              ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20'
                              : 'bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-500'">
                            Đi trễ
                          </button>
                        </template>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Page Nav stub -->
              <div class="mt-12 flex items-center justify-between border-t border-slate-50 pt-8">
                <p class="text-[10px] font-black text-slate-400 italic">Bản nháp được lưu tự động lúc {{ lastSaveTime }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- [RIGHT]: Side Panel Intel -->
        <aside class="w-full lg:w-[380px] space-y-8">

          <!-- Session Intel Card LIGHT THEME -->
          <div
            class="rounded-[3.5rem] bg-emerald-50/50 border border-emerald-100 p-10 text-slate-800 relative overflow-hidden shadow-[0_8px_30px_rgb(16,185,129,0.05)] group">
            <div
              class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-200/20 rounded-full blur-3xl group-hover:bg-emerald-300/20 transition-all duration-700">
            </div>

            <div class="relative z-10">
              <div class="flex items-center justify-between mb-10">
                <h3 class="text-xl font-headline font-black tracking-tight text-emerald-900">Thống kê Buổi học</h3>
                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center">
                  <i class="fa-solid fa-chart-simple text-sm text-emerald-500"></i>
                </div>
              </div>

              <!-- Large Score -->
              <div class="flex items-center gap-6 mb-12">
                <div class="relative w-32 h-32 flex items-center justify-center">
                  <svg class="w-full h-full transform -rotate-90">
                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent"
                      class="text-emerald-100"></circle>
                    <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent"
                      class="text-emerald-500 transition-all duration-1000 ease-out" :stroke-dasharray="364"
                      :stroke-dashoffset="364 - (364 * completionRate / 100)"></circle>
                  </svg>
                  <div class="absolute inset-0 flex flex-col items-center justify-center text-emerald-950">
                    <span class="text-3xl font-black mb-0.5 tracking-tighter">{{ completionRate }}%</span>
                    <i class="fa-solid fa-arrow-trend-up text-emerald-500 text-sm"></i>
                  </div>
                </div>
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">Tỷ lệ Hoàn thành</p>
                  <p class="text-xs font-bold text-slate-500 leading-relaxed">Hệ thống tự động theo dõi tỷ lệ điểm danh
                    theo
                    thời gian thực.</p>
                </div>
              </div>

              <!-- Stats Rows -->
              <div class="space-y-4 mb-2">
                <div
                  class="flex items-center justify-between p-5 rounded-[1.5rem] bg-white border border-emerald-100/50 shadow-sm">
                  <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]"></div>
                    <span class="text-xs font-black text-slate-700">Có mặt</span>
                  </div>
                  <span class="text-lg font-black text-slate-800">{{ stats.present }}</span>
                </div>
                <div
                  class="flex items-center justify-between p-5 rounded-[1.5rem] bg-white border border-emerald-100/50 shadow-sm">
                  <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.3)]"></div>
                    <span class="text-xs font-black text-slate-700">Vắng mặt</span>
                  </div>
                  <span class="text-lg font-black text-slate-800">{{ stats.absent }}</span>
                </div>
                <div
                  class="flex items-center justify-between p-5 rounded-[1.5rem] bg-white border border-emerald-100/50 shadow-sm">
                  <div class="flex items-center gap-4">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.3)]"></div>
                    <span class="text-xs font-black text-slate-700">Đến trễ / Về sớm</span>
                  </div>
                  <span class="text-lg font-black text-slate-800">{{ stats.late }}</span>
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
              <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2">Nhắc nhở Nội quy:</h4>
              <p class="text-[11px] font-bold text-slate-400 leading-relaxed">Học viên vắng quá 3 buổi sẽ tự động kích
                hoạt cảnh
                báo học vụ.</p>
            </div>
          </div>

          <!-- Add Session Notes Button -->
          <div @click="toggleNotes"
            class="rounded-[3.5rem] border-2 border-dashed border-emerald-100 p-10 flex flex-col items-center justify-center gap-4 cursor-pointer hover:bg-emerald-50/30 hover:border-emerald-300 transition-all group py-12">
            <div
              class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-400 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-all shadow-sm">
              <i class="fa-solid fa-file-signature text-xl"></i>
            </div>
            <span
              class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-emerald-600 transition-colors">Thêm
              Ghi chú Buổi học</span>
          </div>

        </aside>

      </div>

      <!-- ── Note Modal ── -->
      <div v-if="isNotesOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md px-4 py-10 transition-all duration-500">
        <div
          class="w-full max-w-xl rounded-[3rem] bg-white shadow-2xl overflow-hidden animate__animated animate__zoomIn animate__faster">
          <div class="p-10">
            <div class="flex items-center justify-between mb-8">
              <h3 class="text-2xl font-headline font-black text-slate-900 tracking-tight">Ghi chú Buổi học</h3>
              <button @click="isNotesOpen = false"
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <p class="text-xs font-bold text-slate-400 mb-6">Ghi chú lại tiến độ bài học, các sự kiện đáng chú ý hoặc
              đánh giá
              chung về tình hình lớp học trong buổi này.</p>
            <textarea v-model="sessionNote" rows="6"
              class="w-full rounded-[2rem] border-2 border-slate-50 bg-slate-50 p-8 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-emerald-400/20 focus:ring-4 focus:ring-emerald-400/5 transition-all"
              placeholder="Viết tóm tắt nội dung tại đây..."></textarea>
            <div class="mt-8 flex justify-end">
              <button @click="isNotesOpen = false"
                class="px-10 py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest text-white bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/35 hover:-translate-y-0.5 transition-all outline-none">Lưu
                Bản nháp</button>
            </div>
          </div>
        </div>
      </div>

    </template>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { notifySuccess, notifyError, notifyWarning } from '../../utils/notify'
import ExcelJS from 'exceljs'
import { saveAs } from 'file-saver'

const route = useRoute()
const router = useRouter()

// ══════════════════════════════════════════
// PHASE 1: Picker State
// ══════════════════════════════════════════
const isLoadingPicker = ref(false)
const pickerSchedules = ref([])
const pickerDate = ref(new Date().toISOString().split('T')[0]) // YYYY-MM-DD

const isToday = computed(() => pickerDate.value === new Date().toISOString().split('T')[0])

const changeDate = (delta) => {
  const d = new Date(pickerDate.value)
  d.setDate(d.getDate() + delta)
  pickerDate.value = d.toISOString().split('T')[0]
}

const goToToday = () => {
  pickerDate.value = new Date().toISOString().split('T')[0]
}

const formatPickerDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

const isAttendanceOpen = (schedule) => {
  if (!schedule || !schedule.study_date || !schedule.start_time || !schedule.end_time) return false;
  const now = new Date();

  const [startH, startM] = schedule.start_time.split(':').map(Number);
  const [endH, endM] = schedule.end_time.split(':').map(Number);
  const [year, month, day] = schedule.study_date.split('-').map(Number);

  const startTime = new Date(year, month - 1, day, startH, startM);
  const startTimeMinus15 = new Date(startTime.getTime() - 15 * 60000);

  const endTime = new Date(year, month - 1, day, endH, endM);
  // Hiểu theo 2 cách, ở đây cấu hình là: Từ trước giờ BẮT ĐẦU 15p đến sau giờ KẾT THÚC 15p. 
  // Nếu user ý là "trước start 15p đến sau start 15p", mình sẽ phải sửa endTimePlus15 thành startTime + 15m.
  // Tuy nhiên, logic chuẩn cho giáo dục là khóa sổ điểm danh sau khi lớp học KẾT THÚC 15 phút.
  const endTimePlus15 = new Date(endTime.getTime() + 15 * 60000);

  return now >= startTimeMinus15 && now <= endTimePlus15;
}

const fetchPickerSchedules = async () => {
  isLoadingPicker.value = true
  try {
    const res = await apiFetch(`teacher/attendance.php?list_schedules=1&date=${pickerDate.value}`)
    const result = await res.json()
    if (result.status === 'success') {
      pickerSchedules.value = result.data || []
    } else {
      pickerSchedules.value = []
    }
  } catch (err) {
    notifyError('Lỗi tải danh sách buổi học.')
    pickerSchedules.value = []
  } finally {
    isLoadingPicker.value = false
  }
}

watch(pickerDate, () => {
  if (!selectedScheduleId.value) {
    fetchPickerSchedules()
  }
})

const openAttendance = (scheduleId) => {
  router.push(`/teacher/attendance/${scheduleId}`)
}

const goBackToPicker = () => {
  router.push('/teacher/attendance')
}

// ══════════════════════════════════════════
// PHASE 2: Attendance Detail State
// ══════════════════════════════════════════
const selectedScheduleId = computed(() => route.params.id || null)

const isLoading = ref(true)
const isSaving = ref(false)
const sessionInfo = ref(null)
const students = ref([])
const filterType = ref('all')
const searchQuery = ref('')
const sessionNote = ref('')
const isNotesOpen = ref(false)
const lastSaveTime = ref(new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }))

// --- Attendance Intel Calculation ---
const stats = computed(() => {
  const s = { present: 0, absent: 0, late: 0, excused: 0, unmarked: 0 }
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
    notifyError("Lỗi đồng bộ dữ liệu điểm danh.")
  } finally {
    isLoading.value = false
  }
}

const setStatus = (student, status) => {
  if (sessionInfo.value && sessionInfo.value.attendance_checked) {
    notifyWarning('Danh sách này đã được chốt sổ. Không thể sửa điểm danh nữa.', 'Đã khóa thao tác')
    return
  }
  if (sessionInfo.value && !isAttendanceOpen(sessionInfo.value)) {
    notifyWarning('Khung giờ điểm danh đã đóng hoặc chưa mở.', 'Hệ thống đã khóa sổ')
    return
  }
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
        note: ""
      })).filter(r => r.status !== 'unmarked')
    }

    const res = await apiFetch('teacher/attendance.php', {
      method: 'POST',
      body: JSON.stringify(body)
    })
    const result = await res.json()

    if (result.status === 'success') {
      notifySuccess("Lưu điểm danh thành công!")
      lastSaveTime.value = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
      fetchAttendanceData(sessionInfo.value.id)
    } else {
      notifyError(result.message)
    }
  } catch (err) {
    notifyError("Mất kết nối đến máy chủ!")
  } finally {
    isSaving.value = false
  }
}

const toggleNotes = () => { isNotesOpen.value = true }

const exportReport = async () => {
  if (!sessionInfo.value || students.value.length === 0) {
    notifyWarning('Chưa có dữ liệu để xuất báo cáo.')
    return
  }

  const session = sessionInfo.value
  const statusMap = {
    present: 'Có mặt',
    absent: 'Vắng mặt',
    late: 'Đi trễ',
    excused: 'Có phép',
    unmarked: 'Chưa điểm danh'
  }

  // ── Style chung ──
  const thinBorder = { style: 'thin', color: { argb: 'FF999999' } }
  const allBorders = { top: thinBorder, left: thinBorder, bottom: thinBorder, right: thinBorder }
  const headerFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10B981' } }
  const headerFont = { bold: true, color: { argb: 'FFFFFFFF' }, size: 11, name: 'Arial' }
  const labelFont = { bold: true, size: 11, name: 'Arial' }
  const normalFont = { size: 11, name: 'Arial' }
  const centerAlign = { vertical: 'middle', horizontal: 'center', wrapText: true }
  const leftAlign = { vertical: 'middle', horizontal: 'left', wrapText: true }

  const wb = new ExcelJS.Workbook()
  wb.creator = 'Hệ thống Quản lý Đào tạo'
  const ws = wb.addWorksheet('Điểm danh', {
    pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true }
  })

  // Cột: STT, Họ tên, Email, Mã HV, Loại HV, Trạng thái, Chuyên cần
  ws.columns = [
    { width: 16 },  // A - STT / Các label thông tin
    { width: 28 },  // B - Họ và tên
    { width: 30 },  // C - Email
    { width: 16 },  // D - Mã HV
    { width: 14 },  // E - Loại HV
    { width: 18 },  // F - Trạng thái
    { width: 14 },  // G - Chuyên cần
  ]

  // ═══ DÒNG 1-2: Tiêu đề báo cáo (merge A1:G1, A2:G2) ═══
  ws.mergeCells('A1:G1')
  const titleCell = ws.getCell('A1')
  titleCell.value = 'BÁO CÁO ĐIỂM DANH HỌC VIÊN'
  titleCell.font = { bold: true, size: 16, name: 'Arial', color: { argb: 'FF059669' } }
  titleCell.alignment = centerAlign
  ws.getRow(1).height = 35

  ws.mergeCells('A2:G2')
  const subtitleCell = ws.getCell('A2')
  subtitleCell.value = 'TRUNG TÂM ĐÀO TẠO TIẾNG ANH'
  subtitleCell.font = { bold: true, size: 12, name: 'Arial', color: { argb: 'FF666666' } }
  subtitleCell.alignment = centerAlign
  ws.getRow(2).height = 22

  // ═══ DÒNG 3: trống ═══
  ws.getRow(3).height = 10

  // ═══ DÒNG 4-10: Thông tin buổi học (label:value, kẻ khung nhẹ) ═══
  const infoData = [
    ['Lớp học:', session.class_name || ''],
    ['Khóa học:', session.course_title || ''],
    ['Giảng viên:', session.teacher_name || ''],
    ['Ngày học:', session.study_date || ''],
    ['Thời gian:', `${formatTime(session.start_time)} – ${formatTime(session.end_time)}`],
    ['Phòng học:', session.room_info || 'Phòng học lý thuyết'],
    ['Tổng sĩ số:', `${students.value.length} học viên`],
  ]
  infoData.forEach((pair, i) => {
    const row = ws.getRow(4 + i)
    row.height = 22
    const cellA = row.getCell(1)
    const cellB = row.getCell(2)
    ws.mergeCells(4 + i, 2, 4 + i, 4) // merge B-D cho giá trị
    cellA.value = pair[0]
    cellA.font = labelFont
    cellA.alignment = leftAlign
    cellA.border = allBorders
    cellA.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF0FDF4' } }
    cellB.value = pair[1]
    cellB.font = normalFont
    cellB.alignment = leftAlign
    cellB.border = allBorders
    // Kẻ border cho các ô merge
    row.getCell(3).border = allBorders
    row.getCell(4).border = allBorders
  })

  // ═══ DÒNG 11: trống ═══
  const gapRow1 = 4 + infoData.length
  ws.getRow(gapRow1).height = 10

  // ═══ DÒNG 12: Header bảng điểm danh ═══
  const tableHeaderRow = gapRow1 + 1
  const headers = ['STT', 'Họ và tên', 'Email', 'Mã học viên', 'Loại HV', 'Trạng thái', 'Chuyên cần (%)']
  const hRow = ws.getRow(tableHeaderRow)
  hRow.height = 28
  headers.forEach((h, i) => {
    const cell = hRow.getCell(i + 1)
    cell.value = h
    cell.font = headerFont
    cell.fill = headerFill
    cell.alignment = centerAlign
    cell.border = allBorders
  })

  // ═══ DÒNG 12+: Dữ liệu học viên ═══
  students.value.forEach((s, idx) => {
    const rowIdx = tableHeaderRow + 1 + idx
    const row = ws.getRow(rowIdx)
    row.height = 24

    const statusText = statusMap[s.current_status] || 'Chưa điểm danh'
    const typeText = s.student_type === 'makeup' ? 'Học bù' : 'Chính thức'
    const values = [
      idx + 1,
      s.full_name,
      s.email,
      `#EG-${String(s.id).padStart(6, '0')}`,
      typeText,
      statusText,
      `${s.attendance_pct}%`
    ]

    // Màu nền xen kẽ
    const stripeFill = idx % 2 === 0
      ? { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFFFF' } }
      : { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } }

    values.forEach((v, ci) => {
      const cell = row.getCell(ci + 1)
      cell.value = v
      cell.font = normalFont
      cell.alignment = ci === 0 || ci >= 4 ? centerAlign : leftAlign
      cell.border = allBorders
      cell.fill = stripeFill
    })

    // Tô màu trạng thái
    const statusCell = row.getCell(6)
    if (s.current_status === 'present') {
      statusCell.font = { ...normalFont, bold: true, color: { argb: 'FF059669' } }
    } else if (s.current_status === 'absent') {
      statusCell.font = { ...normalFont, bold: true, color: { argb: 'FFEF4444' } }
      statusCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEF2F2' } }
    } else if (s.current_status === 'late') {
      statusCell.font = { ...normalFont, bold: true, color: { argb: 'FFF59E0B' } }
    }

    // Tô màu chuyên cần
    const pctCell = row.getCell(7)
    if (s.attendance_pct < 60) {
      pctCell.font = { ...normalFont, bold: true, color: { argb: 'FFEF4444' } }
    } else if (s.attendance_pct < 80) {
      pctCell.font = { ...normalFont, bold: true, color: { argb: 'FFF59E0B' } }
    } else {
      pctCell.font = { ...normalFont, bold: true, color: { argb: 'FF059669' } }
    }
  })

  // ═══ THỐNG KÊ TỔNG HỢP ═══
  const statsStart = tableHeaderRow + 1 + students.value.length + 1
  ws.getRow(statsStart - 1).height = 10 // gap

  ws.mergeCells(statsStart, 1, statsStart, 7)
  const statsTitle = ws.getCell(statsStart, 1)
  statsTitle.value = 'THỐNG KÊ TỔNG HỢP'
  statsTitle.font = { bold: true, size: 13, name: 'Arial', color: { argb: 'FF059669' } }
  statsTitle.alignment = centerAlign
  statsTitle.border = allBorders
  statsTitle.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF0FDF4' } }
  ws.getRow(statsStart).height = 28

  const statsData = [
    ['✅  Có mặt', stats.value.present, '❌  Vắng mặt', stats.value.absent, '⏰  Đi trễ', stats.value.late, ''],
    ['📋  Chưa điểm danh', stats.value.unmarked, '📊  Tỷ lệ hoàn thành', `${completionRate.value}%`, '', '', ''],
  ]
  statsData.forEach((data, i) => {
    const row = ws.getRow(statsStart + 1 + i)
    row.height = 24
    data.forEach((v, ci) => {
      const cell = row.getCell(ci + 1)
      cell.value = v
      cell.font = ci % 2 === 0 ? labelFont : { ...normalFont, bold: true }
      cell.alignment = ci % 2 === 0 ? leftAlign : centerAlign
      cell.border = allBorders
    })
  })

  // ═══ DÒNG CUỐI: Thời gian xuất ═══
  const footerRow = statsStart + 1 + statsData.length + 1
  ws.mergeCells(footerRow, 1, footerRow, 4)
  const footerCell = ws.getCell(footerRow, 1)
  footerCell.value = `Xuất lúc: ${new Date().toLocaleString('vi-VN')}`
  footerCell.font = { italic: true, size: 10, name: 'Arial', color: { argb: 'FF999999' } }
  footerCell.alignment = leftAlign

  ws.mergeCells(footerRow, 5, footerRow, 7)
  const sigTitleCell = ws.getCell(footerRow, 5)
  sigTitleCell.value = 'Giảng viên ký tên'
  sigTitleCell.font = { italic: true, size: 10, name: 'Arial', color: { argb: 'FF999999' } }
  sigTitleCell.alignment = { vertical: 'middle', horizontal: 'center' }

  // Để khoảng trống ký tên
  ws.getRow(footerRow + 1).height = 25
  ws.getRow(footerRow + 2).height = 25
  ws.getRow(footerRow + 3).height = 25

  // Tên giảng viên
  ws.mergeCells(footerRow + 4, 5, footerRow + 4, 7)
  const sigNameCell = ws.getCell(footerRow + 4, 5)
  sigNameCell.value = session.teacher_name || ''
  sigNameCell.font = { bold: true, size: 11, name: 'Arial' }
  sigNameCell.alignment = { vertical: 'middle', horizontal: 'center' }

  // ═══ Xuất file ═══
  const buffer = await wb.xlsx.writeBuffer()
  const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
  const fileName = `Diem-Danh_${(session.class_name || 'Lop').replace(/\s+/g, '-')}_${session.study_date || 'ngay'}.xlsx`
  saveAs(blob, fileName)

  notifySuccess('Đã xuất báo cáo điểm danh thành công!')
}

// --- Helpers ---
const formatFullDate = (d) => {
  if (!d) return 'Hôm nay'
  const date = new Date(d)
  return date.toLocaleDateString('vi-VN', { weekday: 'long', month: 'long', day: 'numeric' })
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

// --- Route watcher: switch between picker and detail ---
watch(selectedScheduleId, (newId) => {
  if (newId) {
    fetchAttendanceData(newId)
  } else {
    // Reset attendance state
    sessionInfo.value = null
    students.value = []
    fetchPickerSchedules()
  }
}, { immediate: false })

onMounted(() => {
  if (selectedScheduleId.value) {
    fetchAttendanceData(selectedScheduleId.value)
  } else {
    fetchPickerSchedules()
  }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@600;700;800;900&display=swap');

.font-headline {
  font-family: 'Manrope', sans-serif;
}

body {
  font-family: 'Inter', sans-serif;
}

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
  0% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}
</style>
