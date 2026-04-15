<template>
  <div class="h-full flex flex-col p-4 md:p-6 animate__animated animate__fadeIn font-sans space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-end items-start md:items-center gap-4">
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-plus"></i> Lên lịch mới
      </button>
    </div>

    <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50/80 backdrop-blur-sm px-5 py-4 text-sm font-medium text-red-700 shadow-sm flex items-center gap-3">
      <i class="fa-solid fa-circle-exclamation text-lg"></i>
      {{ errorMessage }}
    </div>

    <!-- Calendar Card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 flex flex-col overflow-hidden transition-all duration-500">
      
      <!-- Calendar Controls -->
      <div class="px-6 py-5 flex justify-between items-center bg-white/50 border-b border-emerald-50/50">
        <div class="flex items-center gap-2 md:gap-4 bg-slate-50 border border-slate-100 p-1.5 rounded-2xl shadow-inner">
          <button @click="changeMonth(-1)" class="w-10 h-10 rounded-xl hover:bg-white hover:shadow-md flex items-center justify-center text-slate-500 hover:text-emerald-600 transition-all duration-300">
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <div class="flex flex-col items-center justify-center w-40">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest leading-none mb-1">Tháng {{ currentMonth + 1 }}</span>
            <span class="text-lg font-black text-slate-800 leading-none">{{ currentYear }}</span>
          </div>
          <button @click="changeMonth(1)" class="w-10 h-10 rounded-xl hover:bg-white hover:shadow-md flex items-center justify-center text-slate-500 hover:text-emerald-600 transition-all duration-300">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
        <button @click="goToToday" class="px-5 py-2.5 rounded-xl border-2 border-slate-100 text-sm font-bold text-slate-600 bg-white hover:border-emerald-200 hover:text-emerald-600 hover:shadow-md transition-all duration-300 active:scale-95">
          <i class="fa-regular fa-calendar-check mr-2"></i>Hôm nay
        </button>
      </div>

      <!-- Calendar View -->
      <div class="flex-1 flex flex-col bg-slate-50/30">
        <div v-if="isLoading" class="flex-1 flex items-center justify-center py-24">
          <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest animate-pulse">Đang tải lịch...</p>
          </div>
        </div>

        <template v-else>
          <!-- Weekdays Header -->
          <div class="grid grid-cols-7 border-b border-slate-100 bg-white/40">
            <div v-for="day in weekDays" :key="day" class="py-4 text-center text-[0.65rem] font-black text-slate-400 uppercase tracking-[0.2em] relative">
              {{ day }}
              <div v-if="day === 'CN'" class="absolute inset-x-0 bottom-0 h-0.5 bg-red-100 mx-auto w-4"></div>
            </div>
          </div>

          <!-- Days Grid -->
          <div class="grid grid-cols-7 gap-px bg-slate-100/50 flex-1">
            <div
              v-for="(cell, index) in calendarCells"
              :key="index"
              class="bg-white min-h-[140px] p-2 md:p-3 flex flex-col group relative transition-all duration-300 hover:z-10 hover:shadow-xl hover:scale-[1.02] border border-transparent hover:border-emerald-100 hover:rounded-xl"
              :class="{ 'opacity-40 bg-slate-50/50 grayscale-[50%]': !cell.isCurrentMonth }"
            >
              <!-- Day Number Header -->
              <div class="flex justify-between items-start mb-3 relative z-10">
                <span
                  class="w-8 h-8 flex items-center justify-center rounded-xl text-sm font-bold transition-all duration-300"
                  :class="isToday(cell.date) ? 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-100 ring-offset-1' : 'text-slate-500 group-hover:text-emerald-600 group-hover:bg-emerald-50'"
                >
                  {{ cell.dayNumber }}
                </span>
                <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-all duration-300">
                  <button v-if="cell.schedules.length > 0" @click.stop="cell.schedules.length === 1 ? openModal('edit', cell.schedules[0]) : openDayDetails(cell)" class="text-slate-400 hover:text-emerald-600 hover:scale-110 bg-slate-50 hover:bg-emerald-50 w-8 h-8 rounded-xl flex items-center justify-center" title="Sửa ca dạy">
                    <i class="fa-solid fa-pen text-[0.85rem]"></i>
                  </button>
                  <button @click.stop="openModal('add', cell.date)" class="text-emerald-400 hover:text-emerald-600 hover:scale-110 bg-emerald-50 w-8 h-8 rounded-xl flex items-center justify-center" title="Lên lịch mới">
                    <i class="fa-solid fa-plus text-[0.85rem]"></i>
                  </button>
                </div>
              </div>

              <!-- Schedules -->
              <div class="flex-1 space-y-2 relative z-10 custom-scrollbar overflow-y-auto max-h-[120px] pr-1">
                <div
                  v-for="schedule in getVisibleSchedules(cell.schedules)"
                  :key="schedule.id"
                  @click.stop="$router.push('/admin/schedules/' + schedule.id)"
                  class="p-2.5 rounded-xl border text-left cursor-pointer transition-all duration-300 hover:-translate-y-1 shadow-sm relative overflow-hidden group/item backdrop-blur-md flex flex-col"
                  :class="getScheduleStyle(schedule)"
                >
                  <div>
                    <div class="text-[0.65rem] font-bold truncate tracking-wider mb-1 flex items-center gap-1.5 opacity-70">
                      <i class="fa-regular fa-clock"></i> {{ formatScheduleTime(schedule.start_time) }}
                    </div>
                    <div class="text-xs font-black truncate leading-tight group-hover/item:text-emerald-700 transition-colors">{{ schedule.class_name }}</div>
                    <div v-if="schedule.course_name" class="text-[0.6rem] font-medium truncate mt-0.5 opacity-60">{{ schedule.course_name }}</div>
                  </div>
                </div>

                <!-- More button -->
                <button
                  v-if="getHiddenSchedulesCount(cell.schedules) > 0"
                  type="button"
                  @click.stop="openDayDetails(cell)"
                  class="w-full rounded-xl border border-dashed border-slate-300 bg-slate-50/50 px-3 py-2 text-center text-xs font-bold text-slate-500 hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50/50 transition-all duration-300"
                >
                  +{{ getHiddenSchedulesCount(cell.schedules) }} ca khác
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Bottom Dashboard Widgets -->
    <div class="grid gap-6 xl:grid-cols-2 mt-2">
      <!-- Lịch Hôm Nay -->
      <section class="rounded-3xl border border-slate-100 bg-white/80 backdrop-blur-xl p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition-all duration-500 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-50 rounded-full blur-3xl -mr-20 -mt-20 opacity-50 group-hover:opacity-70 transition-opacity"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-4">
              <div>
                <h3 class="text-lg font-black text-slate-800">Lịch dạy hôm nay</h3>
                <p class="text-xs font-bold uppercase tracking-[0.1em] text-emerald-600 mt-1">{{ formatDate(formatDateKey(new Date())) }}</p>
              </div>
            </div>
            <span class="rounded-xl bg-emerald-100/80 px-4 py-1.5 text-xs font-black text-emerald-800 border border-emerald-200 shadow-sm backdrop-blur-sm">
              {{ todaySchedules.length }} ca
            </span>
          </div>

          <div class="mt-6 space-y-4">
            <div
              v-for="schedule in todaySchedules"
              :key="`today-${schedule.id}`"
              @click="$router.push('/admin/schedules/' + schedule.id)"
              class="w-full relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 text-left transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-100/50 hover:border-emerald-200 group/btn cursor-pointer"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-1.5">
                    <span class="rounded-lg bg-slate-50 border border-slate-100 px-2.5 py-1 text-[0.65rem] font-bold text-slate-500 flex items-center gap-1">
                      <i class="fa-regular fa-clock"></i> {{ formatScheduleTime(schedule.start_time) }} - {{ formatScheduleTime(schedule.end_time) }}
                    </span>
                    <span class="rounded-lg px-2.5 py-1 text-[0.65rem] font-bold shadow-sm" :class="getAgendaBadgeClass(schedule)">
                      {{ getStatusLabel(schedule.status) }}
                    </span>
                  </div>
                  <div class="text-[0.95rem] font-black text-slate-800 group-hover/btn:text-emerald-700 transition-colors">{{ schedule.class_name }}</div>
                  <div class="mt-1 text-xs font-medium text-slate-500 line-clamp-1">{{ schedule.course_name || 'Chưa gắn khóa học' }}</div>
                  
                  <div class="mt-3 flex items-center gap-3 text-[0.7rem] font-semibold text-slate-500 justify-between">
                    <div class="flex items-center gap-1.5">
                      <div class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i :class="schedule.teaching_type === 'online' ? 'fa-solid fa-laptop' : 'fa-solid fa-chalkboard-user' "></i>
                      </div>
                      {{ schedule.teaching_type === 'online' ? 'Lớp Online' : 'Lớp Offline' }}
                    </div>
                    <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg">
                      <i class="fa-solid fa-user-tie opacity-60"></i>
                      <span class="truncate max-w-[100px]">{{ schedule.teacher_name || 'Chưa xếp' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="todaySchedules.length === 0" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 px-4 py-10">
              <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4 text-emerald-200">
                 <i class="fa-solid fa-mug-hot text-3xl"></i>
              </div>
              <p class="text-sm font-bold text-slate-600">Hôm nay chưa có ca dạy nào</p>
              <p class="text-xs text-slate-400 mt-1">Hãy tận hưởng một ngày nghỉ ngơi!</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Sắp Tới -->
      <section class="rounded-3xl border border-slate-100 bg-white/80 backdrop-blur-xl p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition-all duration-500 relative overflow-hidden group">
        <div class="absolute top-0 left-0 w-64 h-64 bg-sky-50 rounded-full blur-3xl -ml-20 -mt-20 opacity-50 group-hover:opacity-70 transition-opacity"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-4">
              <div>
                <h3 class="text-lg font-black text-slate-800">Các ca dạy kế tiếp</h3>
                <p class="text-xs font-bold uppercase tracking-[0.1em] text-sky-600 mt-1">Tổng quan sắp tới</p>
              </div>
            </div>
            <span class="rounded-xl bg-sky-100/80 px-4 py-1.5 text-xs font-black text-sky-800 border border-sky-200 shadow-sm backdrop-blur-sm">
              {{ upcomingSchedules.length }} ca
            </span>
          </div>

          <div class="mt-6 space-y-4">
            <div
              v-for="schedule in upcomingSchedules"
              :key="`upcoming-${schedule.id}`"
              @click="$router.push('/admin/schedules/' + schedule.id)"
              class="w-full relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 text-left transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-100/50 hover:border-sky-200 group/btn cursor-pointer"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-1.5">
                    <span class="rounded-lg bg-sky-50/50 border border-sky-100/50 px-2.5 py-1 text-[0.65rem] font-bold text-sky-700 flex items-center gap-1.5">
                      <i class="fa-solid fa-calendar-alt opacity-70"></i> {{ formatDate(schedule.study_date) }}
                    </span>
                    <span class="rounded-lg px-2.5 py-1 text-[0.65rem] font-bold shadow-sm" :class="getAgendaBadgeClass(schedule)">
                      {{ getStatusLabel(schedule.status) }}
                    </span>
                  </div>
                  
                  <div class="text-[0.95rem] font-black text-slate-800 group-hover/btn:text-sky-700 transition-colors">{{ schedule.class_name }}</div>
                  
                  <div class="mt-3 flex items-center gap-3 text-[0.7rem] font-semibold flex-wrap">
                    <div class="flex items-center gap-1.5 text-slate-600">
                      <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center">
                        <i class="fa-regular fa-clock"></i>
                      </div>
                      {{ formatScheduleTime(schedule.start_time) }} - {{ formatScheduleTime(schedule.end_time) }}
                    </div>
                    <div v-if="schedule.teacher_name" class="flex items-center gap-1.5 text-slate-600 ml-auto">
                      <i class="fa-solid fa-user-tie opacity-60"></i>
                      <span>{{ schedule.teacher_name }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="upcomingSchedules.length === 0" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 px-4 py-10">
              <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4 text-sky-200">
                 <i class="fa-solid fa-wind text-3xl"></i>
              </div>
              <p class="text-sm font-bold text-slate-600">Không có ca dạy nào trong thời gian tới</p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Modals -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate__animated animate__fadeIn animate__faster">
      <div class="bg-white/95 backdrop-blur-3xl rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto no-scrollbar animate__animated animate__zoomIn animate__faster border border-white">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-emerald-50/50 to-teal-50/50">
          <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
            {{ modalMode === 'add' ? 'Lên lịch ca dạy mới' : 'Chi tiết ca dạy' }}
          </h3>
          <button @click="closeModal" class="w-10 h-10 rounded-full bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm active:scale-95">
            <i class="fa-solid fa-xmark text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveSchedule" class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col md:flex-row gap-4 md:col-span-2">
              <div class="w-full md:w-1/2 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Lớp học <span class="text-red-500">*</span></label>
                <select v-model="formData.class_id" @change="handleClassChange" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100">
                  <option value="" disabled>-- Chọn lớp học --</option>
                  <option v-for="item in classOptions" :key="item.id" :value="item.id">{{ item.class_name }}{{ item.course_name ? ` - ${item.course_name}` : '' }}</option>
                </select>
              </div>

              <div class="w-full md:w-1/2 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Nhóm lịch học <span class="text-red-500">*</span></label>
                <select v-model="formData.class_detail_id" required :disabled="!formData.class_id || isLoadingDetails" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                  <option value="" disabled>{{ formData.class_id ? (isLoadingDetails ? 'Đang tải...' : '-- Chọn nhóm học --') : '-- Chọn lớp trước --' }}</option>
                  <option v-for="group in classDetails" :key="group.id" :value="group.id">{{ group.detail_name }}</option>
                </select>
                <p v-if="formData.class_id && classDetails.length === 0 && !isLoadingDetails" class="mt-1.5 text-[0.7rem] font-bold text-red-500"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Lớp này chưa được tạo danh sách Nhóm. Hãy vào Quản lý Lớp để tạo.</p>
              </div>
            </div>

            <div class="group">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Ngày học <span class="text-red-500">*</span></label>
              <input v-model="formData.study_date" type="date" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100" />
            </div>

            <div class="flex gap-4">
              <div class="w-1/2 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Giờ bắt đầu <span class="text-red-500">*</span></label>
                <input v-model="formData.start_time" type="time" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100" />
              </div>
              <div class="w-1/2 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Giờ kết thúc <span class="text-red-500">*</span></label>
                <input v-model="formData.end_time" type="time" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100" />
              </div>
            </div>

            <template v-if="modalMode === 'add'">
              <div class="group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Kiểu lặp lịch</label>
                <select v-model="formData.recurrence_pattern" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100">
                  <option value="none">Không lặp</option>
                  <option value="mon_wed_fri">Thứ 2 / 4 / 6</option>
                  <option value="tue_thu_sat">Thứ 3 / 5 / 7</option>
                </select>
              </div>

              <div v-if="formData.recurrence_pattern !== 'none'" class="group animate__animated animate__fadeIn animate__faster">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Lặp đến ngày</label>
                <input v-model="formData.recurrence_end_date" type="date" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100" />
              </div>

              <div class="md:col-span-2 rounded-2xl border border-emerald-100 bg-emerald-50/50 backdrop-blur-sm px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg shadow-inner">
                  <i class="fa-solid fa-calculator"></i>
                </div>
                <div>
                  <p class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Dự kiến</p>
                  <p class="text-sm font-black text-emerald-900 mt-0.5">Tổng cộng <span class="text-xl mx-1">{{ totalLessons }}</span> buổi học</p>
                </div>
              </div>

              <div v-if="formData.recurrence_pattern !== 'none'" class="md:col-span-2 rounded-2xl border border-sky-100 bg-sky-50/50 backdrop-blur-sm px-5 py-4 flex items-start gap-4 animate__animated animate__fadeIn">
                <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-lg shrink-0 shadow-inner">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="text-sm font-medium text-sky-800 leading-relaxed pt-1">
                  Hệ thống sẽ tự tạo nhiều buổi học từ ngày bắt đầu đến ngày kết thúc, theo nhóm ngày lặp lại đã chọn. Các lịch trùng bằng giờ sẽ được hệ thống cảnh báo trước khi lưu.
                </div>
              </div>
            </template>

            <div class="group">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Giảng viên đứng lớp</label>
              <select v-model="formData.teacher_id" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100">
                <option value="">-- Mặc định (GV chủ nhiệm) --</option>
                <option v-for="teacher in teacherOptions" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option>
              </select>
            </div>

            <div class="flex gap-4">
              <div class="w-1/2 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Hình thức</label>
                <select v-model="formData.teaching_type" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100">
                  <option value="offline">Trực tiếp (Offline)</option>
                  <option value="online">Trực tuyến (Online)</option>
                </select>
              </div>
              <div class="w-1/2 group">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Trạng thái</label>
                <select v-model="formData.status" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100">
                  <option value="scheduled">Sắp tới</option>
                  <option value="completed">Đã hoàn thành</option>
                  <option value="canceled">Hủy/Nghỉ</option>
                </select>
              </div>
            </div>

            <div class="md:col-span-2 group">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">
                {{ formData.teaching_type === 'online' ? 'Link học (Zoom/Meet)' : 'Phòng học (VD: P.302 Cơ sở 1)' }}
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                  <i :class="formData.teaching_type === 'online' ? 'fa-solid fa-link' : 'fa-solid fa-door-open'"></i>
                </div>
                <input v-model="formData.room_info" type="text" :placeholder="formData.teaching_type === 'online' ? 'https://...' : 'Nhập phòng học...'" class="w-full pl-11 pr-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100" />
              </div>
            </div>

            <div class="md:col-span-2 group">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 transition-colors group-focus-within:text-emerald-600">Ghi chú thêm</label>
              <textarea v-model="formData.note" rows="2" placeholder="Bài học, tài liệu chuản bị..." class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all duration-300 hover:bg-slate-100 resize-none"></textarea>
            </div>
          </div>

          <div class="pt-6 mt-8 border-t border-slate-100 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div v-if="modalMode === 'edit'" class="flex flex-wrap gap-3">
              <button type="button" @click="deleteSchedule(formData.id)" class="px-5 py-2.5 rounded-xl font-bold text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition-all duration-300 border border-red-100 hover:border-red-600 shadow-sm">
                <i class="fa-solid fa-trash-can mr-2"></i> Xóa ca này
              </button>
              <button type="button" @click="deleteSchedule(formData.id, 'group')" class="px-5 py-2.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 transition-all duration-300 shadow-md shadow-red-500/20 hover:shadow-red-500/40">
                <i class="fa-solid fa-calendar-xmark mr-2"></i> Xóa toàn bộ nhóm lịch này
              </button>
            </div>
            <div v-else></div>

            <div class="flex flex-wrap gap-3">
              <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                Đóng
              </button>
              <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-1" style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
                <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isDayDetailsOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate__animated animate__fadeIn animate__faster">
      <div class="bg-white/95 backdrop-blur-3xl rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden animate__animated animate__zoomIn animate__faster border border-white">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50/50 to-emerald-50/50">
          <div class="flex items-center gap-4">
            <div>
              <h3 class="text-xl font-black text-slate-800">Lịch dạy ngày {{ formatDate(activeDayDetails.date) }}</h3>
              <p class="text-sm font-medium text-emerald-600 mt-0.5">{{ activeDayDetails.schedules.length }} ca dạy trong ngày này</p>
            </div>
          </div>
          <button @click="closeDayDetails" class="w-10 h-10 rounded-full bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm active:scale-95">
            <i class="fa-solid fa-xmark text-lg"></i>
          </button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto p-8 custom-scrollbar bg-slate-50/30">
          <div class="space-y-4">
            <button
              v-for="schedule in activeDayDetails.schedules"
              :key="`day-detail-${schedule.id}`"
              type="button"
              @click="openScheduleFromDayDetails(schedule)"
              class="w-full relative overflow-hidden rounded-2xl border bg-white px-5 py-5 text-left transition-all duration-300 hover:-translate-y-1 hover:shadow-xl group"
              :class="getScheduleStyle(schedule)"
            >
              <div class="absolute left-0 top-0 bottom-0 w-1.5 opacity-60" :class="schedule.teaching_type === 'online' ? 'bg-blue-500' : 'bg-emerald-500'"></div>
              
              <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 pl-2">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <span class="rounded-lg bg-slate-50 border border-slate-100 px-2 py-1 text-xs font-bold text-slate-600 flex items-center gap-1.5">
                      <i class="fa-regular fa-clock text-slate-400"></i> {{ formatScheduleTime(schedule.start_time) }} - {{ formatScheduleTime(schedule.end_time) }}
                    </span>
                    <span class="rounded-lg px-2 py-1 text-[0.65rem] font-bold" :class="getAgendaBadgeClass(schedule)">
                      {{ getStatusLabel(schedule.status) }}
                    </span>
                  </div>
                  <div class="text-[1.05rem] font-black group-hover:text-emerald-700 transition-colors">{{ schedule.class_name }}</div>
                  <div class="mt-1 text-sm font-medium opacity-70">{{ schedule.course_name || 'Chưa gắn khóa học' }}</div>
                </div>
                
                <div class="text-left md:text-right mt-2 md:mt-0 flex flex-col justify-center">
                  <div class="font-bold flex items-center md:justify-end gap-2 text-sm text-slate-700">
                    <i class="fa-solid fa-user-tie opacity-50"></i> {{ schedule.teacher_name || 'Chưa xếp giảng viên' }}
                  </div>
                  <div class="mt-1.5 text-xs font-medium opacity-80 flex items-center md:justify-end gap-2">
                    <i :class="schedule.teaching_type === 'online' ? 'fa-solid fa-link' : 'fa-solid fa-door-open' " class="opacity-50"></i>
                    {{ schedule.room_info || 'Chưa có phòng/link' }}
                  </div>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession } from '../../utils/auth'
import { openConfirm } from '../../utils/confirm'
import { notify, notifyError, notifyWarning } from '../../utils/notify'

const router = useRouter()
const schedules = ref([])
const classOptions = ref([])
const teacherOptions = ref([])
const isLoading = ref(false)
const errorMessage = ref('')

const weekDays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN']
const currentDate = new Date()
const currentMonth = ref(currentDate.getMonth())
const currentYear = ref(currentDate.getFullYear())

const isModalOpen = ref(false)
const isDayDetailsOpen = ref(false)
const activeDayDetails = ref({
  date: '',
  schedules: [],
})
const modalMode = ref('add')

const classDetails = ref([])
const isLoadingDetails = ref(false)

const createDefaultFormData = (studyDate = '') => ({
  id: null,
  class_id: '',
  class_detail_id: '',
  teacher_id: '',
  study_date: studyDate,
  start_time: '18:00',
  end_time: '19:30',
  teaching_type: 'offline',
  room_info: '',
  status: 'scheduled',
  note: '',
  recurrence_pattern: 'none',
  recurrence_end_date: '',
})
const formData = ref(createDefaultFormData())

const recurrenceWeekdaysMap = {
  none: [],
  mon_wed_fri: [1, 3, 5],
  tue_thu_sat: [2, 4, 6],
}

const totalLessons = computed(() => {
  const startDate = String(formData.value.study_date || '').trim()
  const endDate = String(formData.value.recurrence_end_date || '').trim()
  const recurrencePattern = String(formData.value.recurrence_pattern || 'none').trim()

  if (!startDate) return 0
  if (recurrencePattern === 'none') return 1
  if (!endDate) return 0

  const weekdays = recurrenceWeekdaysMap[recurrencePattern] || []
  if (weekdays.length === 0) return 0

  const start = new Date(`${startDate}T00:00:00`)
  const end = new Date(`${endDate}T00:00:00`)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || start > end) return 0

  let total = 0
  const cursor = new Date(start)
  while (cursor <= end) {
    const weekday = cursor.getDay() === 0 ? 7 : cursor.getDay()
    if (weekdays.includes(weekday)) {
      total += 1
    }
    cursor.setDate(cursor.getDate() + 1)
  }

  return total
})

const todayKey = computed(() => formatDateKey(new Date()))

const sortedSchedules = computed(() => (
  [...schedules.value].sort((left, right) => {
    const leftKey = `${left.study_date || ''} ${left.start_time || ''}`
    const rightKey = `${right.study_date || ''} ${right.start_time || ''}`
    return leftKey.localeCompare(rightKey)
  })
))

const todaySchedules = computed(() => (
  sortedSchedules.value.filter((schedule) => schedule.study_date === todayKey.value)
))

const upcomingSchedules = computed(() => (
  sortedSchedules.value
    .filter((schedule) => {
      const scheduleKey = `${schedule.study_date || ''} ${String(schedule.start_time || '').slice(0, 5)}`
      const now = new Date()
      const currentKey = `${formatDateKey(now)} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
      return scheduleKey > currentKey
    })
    .slice(0, 6)
))

const redirectToLogin = () => {
  clearAuthSession()
  router.push('/login')
}

const loadSchedules = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await apiFetch('admin/schedules.php')
    if (res.status === 401 || res.status === 403) {
      redirectToLogin()
      return
    }

    const result = await res.json()
    if (result.status === 'success') {
      schedules.value = Array.isArray(result.data) ? result.data : []
      classOptions.value = Array.isArray(result.classes) ? result.classes : []
      teacherOptions.value = Array.isArray(result.teachers) ? result.teachers : []
    } else {
      errorMessage.value = result.message || 'Không tải được lịch giảng dạy.'
    }
  } catch (error) {
    console.error('Lỗi tải lịch:', error)
    errorMessage.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadSchedules()
})

const formatDateKey = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

const createCellObj = (dayNumber, dateStr, isCurrentMonth) => ({
  dayNumber,
  date: dateStr,
  isCurrentMonth,
  schedules: schedules.value.filter((schedule) => schedule.study_date === dateStr),
})

const calendarCells = computed(() => {
  const cells = []
  const year = currentYear.value
  const month = currentMonth.value

  const firstDayOfMonth = new Date(year, month, 1)
  let startingDayOfWeek = firstDayOfMonth.getDay()
  startingDayOfWeek = startingDayOfWeek === 0 ? 6 : startingDayOfWeek - 1

  const lastDateOfMonth = new Date(year, month + 1, 0).getDate()
  const lastDateOfPrevMonth = new Date(year, month, 0).getDate()

  for (let index = startingDayOfWeek - 1; index >= 0; index--) {
    const date = new Date(year, month - 1, lastDateOfPrevMonth - index)
    cells.push(createCellObj(lastDateOfPrevMonth - index, formatDateKey(date), false))
  }

  for (let day = 1; day <= lastDateOfMonth; day++) {
    const date = new Date(year, month, day)
    cells.push(createCellObj(day, formatDateKey(date), true))
  }

  const cellsToAdd = cells.length % 7 === 0 ? 0 : 7 - (cells.length % 7)
  for (let day = 1; day <= cellsToAdd; day++) {
    const date = new Date(year, month + 1, day)
    cells.push(createCellObj(day, formatDateKey(date), false))
  }

  return cells
})

const changeMonth = (offset) => {
  let newMonth = currentMonth.value + offset
  if (newMonth > 11) {
    newMonth = 0
    currentYear.value++
  } else if (newMonth < 0) {
    newMonth = 11
    currentYear.value--
  }
  currentMonth.value = newMonth
}

const goToToday = () => {
  const today = new Date()
  currentMonth.value = today.getMonth()
  currentYear.value = today.getFullYear()
}

const isToday = (dateStr) => dateStr === formatDateKey(new Date())
const formatScheduleTime = (time) => String(time || '').slice(0, 5)
const formatDate = (dateStr) => {
  if (!dateStr) return 'Chưa xác định'
  const date = new Date(`${dateStr}T00:00:00`)
  if (Number.isNaN(date.getTime())) return dateStr
  const d = String(date.getDate()).padStart(2, '0')
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const y = date.getFullYear()
  return `${d}/${m}/${y}`
}
const getVisibleSchedules = (items) => items.slice(0, 2)
const getHiddenSchedulesCount = (items) => Math.max(items.length - 2, 0)

const getStatusLabel = (status) => {
  if (status === 'completed') return 'Đã xong'
  if (status === 'canceled') return 'Đã hủy'
  return 'Sắp tới'
}

const getAgendaBadgeClass = (schedule) => {
  if (schedule.status === 'completed') return 'bg-slate-100 text-slate-600'
  if (schedule.status === 'canceled') return 'bg-red-100 text-red-600'
  if (schedule.teaching_type === 'online') return 'bg-blue-100 text-blue-700'
  return 'bg-emerald-100 text-emerald-700'
}

const getScheduleStyle = (schedule) => {
  if (schedule.status === 'canceled') return 'bg-red-50 text-red-600 border-red-200 line-through opacity-70'
  if (schedule.status === 'completed') return 'bg-slate-100 text-slate-500 border-slate-200'
  if (schedule.teaching_type === 'online') return 'bg-blue-50 text-blue-700 border-blue-200'
  return 'bg-emerald-50 text-emerald-700 border-emerald-200'
}

const handleClassChange = async () => {
  const classId = formData.value.class_id
  const selectedClass = classOptions.value.find((item) => Number(item.id) === Number(classId))
  if (!selectedClass) return

  if (selectedClass.start_date) {
    formData.value.study_date = selectedClass.start_date
  }

  if (selectedClass.end_date) {
    formData.value.recurrence_end_date = selectedClass.end_date
  }

  formData.value.class_detail_id = ''
  await loadClassDetails(classId)
}

const loadClassDetails = async (classId) => {
  if (!classId) return
  isLoadingDetails.value = true
  try {
    const query = new URLSearchParams({ class_id: classId })
    const res = await apiFetch(`admin/class_details.php?${query.toString()}`)
    const result = await res.json()
    if (result.status === 'success') {
      classDetails.value = result.data || []
    } else {
      classDetails.value = []
    }
  } catch (error) {
    classDetails.value = []
  } finally {
    isLoadingDetails.value = false
  }
}

watch(() => formData.value.class_detail_id, (newVal) => {
  if (!newVal || modalMode.value !== 'add') return

  const group = classDetails.value.find(g => Number(g.id) === Number(newVal))
  if (!group || !group.detail_name) return

  const name = group.detail_name

  if (name.includes('Lịch học:')) {
     // Format cũ hoặc tự do, bỏ qua
  } else {
    if (name.includes('Sáng')) {
      formData.value.start_time = '08:00'
      formData.value.end_time = '09:30'
    } else if (name.includes('Chiều')) {
      formData.value.start_time = '14:00'
      formData.value.end_time = '15:30'
    } else if (name.includes('Tối')) {
      formData.value.start_time = '18:00'
      formData.value.end_time = '19:30'
    }

    if (name.includes('2/4/6')) {
      formData.value.recurrence_pattern = 'mon_wed_fri'
    } else if (name.includes('3/5/7')) {
      formData.value.recurrence_pattern = 'tue_thu_sat'
    } else {
      formData.value.recurrence_pattern = 'none'
    }
  }
})

const openDayDetails = (cell) => {
  activeDayDetails.value = {
    date: cell.date,
    schedules: [...cell.schedules].sort((left, right) => String(left.start_time || '').localeCompare(String(right.start_time || ''))),
  }
  isDayDetailsOpen.value = true
}

const closeDayDetails = () => {
  isDayDetailsOpen.value = false
  activeDayDetails.value = {
    date: '',
    schedules: [],
  }
}

const openScheduleFromDayDetails = (schedule) => {
  closeDayDetails()
  openModal('edit', schedule)
}

const openModal = async (mode, data = null) => {
  isDayDetailsOpen.value = false
  modalMode.value = mode

  if (mode === 'edit' && data) {
    formData.value = {
      ...createDefaultFormData(),
      ...data,
      recurrence_pattern: 'none',
      recurrence_end_date: '',
      start_time: String(data.start_time || '').slice(0, 5),
      end_time: String(data.end_time || '').slice(0, 5),
    }
    
    if (data.class_id) {
      await loadClassDetails(data.class_id)
    }
  } else {
    classDetails.value = []
    const selectedDate = typeof data === 'string' ? data : ''
    formData.value = createDefaultFormData(selectedDate)
  }

  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  formData.value = createDefaultFormData()
}

const parseTimeToMinutes = (value) => {
  if (!value) return NaN
  const [hour, minute] = String(value).split(':').map(Number)
  if (Number.isNaN(hour) || Number.isNaN(minute)) return NaN
  return hour * 60 + minute
}

const getIsoWeekday = (dateStr) => {
  const date = new Date(`${dateStr}T00:00:00`)
  const day = date.getDay()
  return day === 0 ? 7 : day
}

const validateForm = () => {
  const classId = Number(formData.value.class_id)
  const teacherId = formData.value.teacher_id === '' ? null : Number(formData.value.teacher_id)
  const studyDate = String(formData.value.study_date || '').trim()
  const startTime = String(formData.value.start_time || '').trim()
  const endTime = String(formData.value.end_time || '').trim()
  const teachingType = String(formData.value.teaching_type || 'offline').trim()
  const status = String(formData.value.status || 'scheduled').trim()
  const roomInfo = String(formData.value.room_info || '').trim()
  const note = String(formData.value.note || '').trim()
  const recurrencePattern = String(formData.value.recurrence_pattern || 'none').trim()
  const recurrenceEndDate = String(formData.value.recurrence_end_date || '').trim()

  if (!Number.isInteger(classId) || classId <= 0) {
    alert('Vui lòng chọn lớp học hợp lệ.')
    return false
  }

  if (teacherId !== null && (!Number.isInteger(teacherId) || teacherId <= 0)) {
    alert('Giảng viên được chọn không hợp lệ.')
    return false
  }

  if (!/^\d{4}-\d{2}-\d{2}$/.test(studyDate) || Number.isNaN(new Date(`${studyDate}T00:00:00`).getTime())) {
    alert('Ngày học không hợp lệ.')
    return false
  }

  const selectedClass = classOptions.value.find((c) => Number(c.id) === classId)
  if (selectedClass) {
    if (selectedClass.start_date && studyDate < selectedClass.start_date) {
      alert('Ngày học không được diễn ra trước ngày khai giảng của lớp.')
      return false
    }
    if (selectedClass.end_date && studyDate > selectedClass.end_date) {
      alert('Ngày học không được diễn ra sau ngày kết thúc dự kiến của lớp.')
      return false
    }
  }

  if (!/^\d{2}:\d{2}$/.test(startTime) || !/^\d{2}:\d{2}$/.test(endTime)) {
    alert('Giờ học không hợp lệ.')
    return false
  }

  const startMinutes = parseTimeToMinutes(startTime)
  const endMinutes = parseTimeToMinutes(endTime)
  if (Number.isNaN(startMinutes) || Number.isNaN(endMinutes) || startMinutes >= endMinutes) {
    alert('Giờ kết thúc phải lớn hơn giờ bắt đầu.')
    return false
  }

  if (!['offline', 'online'].includes(teachingType)) {
    alert('Hình thức dạy không hợp lệ.')
    return false
  }

  if (!['scheduled', 'completed', 'canceled'].includes(status)) {
    alert('Trạng thái lịch học không hợp lệ.')
    return false
  }

  if (roomInfo && (roomInfo.length < 2 || roomInfo.length > 255)) {
    alert('Thông tin phòng học/link học phải từ 2 đến 255 ký tự.')
    return false
  }

  if (teachingType === 'online' && roomInfo && !/^https?:\/\/\S+$/i.test(roomInfo)) {
    alert('Link học online phải bắt đầu bằng http:// hoặc https://')
    return false
  }

  if (note && (note.length < 3 || note.length > 1000)) {
    alert('Ghi chú phải từ 3 đến 1000 ký tự nếu có nhập.')
    return false
  }

  if (modalMode.value === 'add' && recurrencePattern !== 'none') {
    if (!['mon_wed_fri', 'tue_thu_sat'].includes(recurrencePattern)) {
      alert('Kiểu lặp lịch không hợp lệ.')
      return false
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(recurrenceEndDate) || Number.isNaN(new Date(`${recurrenceEndDate}T00:00:00`).getTime())) {
      alert('Ngày kết thúc lặp lịch không hợp lệ.')
      return false
    }

    if (recurrenceEndDate < studyDate) {
      alert('Ngày kết thúc lặp lịch không được nhỏ hơn ngày bắt đầu.')
      return false
    }

    if (selectedClass && selectedClass.end_date && recurrenceEndDate > selectedClass.end_date) {
      alert('Ngày kết thúc lặp lịch không được vượt quá ngày kết thúc khóa học.')
      return false
    }
  }

  const overlappingSchedule = schedules.value.find((schedule) => {
    if (Number(schedule.id) === Number(formData.value.id || 0)) return false
    if (schedule.study_date !== studyDate) return false

    const scheduleStart = parseTimeToMinutes(String(schedule.start_time || '').slice(0, 5))
    const scheduleEnd = parseTimeToMinutes(String(schedule.end_time || '').slice(0, 5))
    const overlap = startMinutes < scheduleEnd && endMinutes > scheduleStart
    if (!overlap) return false

    const sameClass = Number(schedule.class_id) === classId
    const sameTeacher = teacherId !== null && Number(schedule.teacher_id) === teacherId
    return sameClass || sameTeacher
  })

  if (overlappingSchedule) {
    if (Number(overlappingSchedule.class_id) === classId) {
      alert('Lớp học này đã có lịch trùng giờ trong ngày được chọn.')
      return false
    }
    alert('Giảng viên này đã có lịch trùng giờ trong ngày được chọn.')
    return false
  }

  return true
}

const saveSchedule = async () => {
  if (!validateForm()) return

  try {
    const method = modalMode.value === 'add' ? 'POST' : 'PUT'
    const res = await apiFetch('admin/schedules.php', {
      method,
      body: JSON.stringify(formData.value),
    })

    if (res.status === 401 || res.status === 403) {
      redirectToLogin()
      return
    }

    const result = await res.json()
    if (result.status === 'success') {
      if (result.message) {
        notify({ type: 'success', title: 'Thành công', message: result.message, duration: 15000 })
      }
      closeModal()
      loadSchedules()
    } else {
      notifyError(result.message)
    }
  } catch (error) {
    notifyError('Lỗi kết nối máy chủ')
  }
}

const deleteSchedule = async (id, scope = 'single') => {
  const isGroupDelete = scope === 'group'
  const confirmMessage = isGroupDelete
    ? 'Bạn có chắc chắn muốn xóa toàn bộ các ca học của nhóm lịch học này khỏi hệ thống không?'
    : 'Bạn có chắc chắn muốn xóa hoặc hủy riêng ca dạy này khỏi lịch?'

  const confirmed = await openConfirm({
    title: isGroupDelete ? 'Xóa nhóm lịch học' : 'Xóa ca dạy',
    message: confirmMessage,
    confirmText: isGroupDelete ? 'Xóa toàn bộ nhóm lịch' : 'Xóa ca này',
    cancelText: 'Không xóa',
    tone: 'danger',
  })
  if (!confirmed) return

  try {
    const query = new URLSearchParams({
      id: String(id),
      scope,
    })

    const res = await apiFetch(`admin/schedules.php?${query.toString()}`, { method: 'DELETE' })
    if (res.status === 401 || res.status === 403) {
      redirectToLogin()
      return
    }

    const result = await res.json()
    if (result.status === 'success') {
      closeModal()
      loadSchedules()
      if (result.message) {
        notify({ type: 'success', title: 'Đã xóa thành công', message: result.message, duration: 15000 })
      }
    } else {
      notifyError(result.message)
    }
  } catch (error) {
    notifyError('Lỗi xóa dữ liệu')
  }
}</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>




<style scoped>
.no-scrollbar {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE & Edge */
}
.no-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome, Safari */
}
</style>
