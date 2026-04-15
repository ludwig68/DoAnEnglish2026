<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn gap-5">

    <!-- ══ TOOLBAR ══════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col md:flex-row gap-3 items-start md:items-center">

      <!-- Student Searchable Dropdown -->
      <div class="relative flex-1 min-w-0 md:max-w-xs" ref="studentDropdownRef">
        <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 mb-1 ml-1">Học viên cần ghi danh *</label>
        <div class="relative">
          <i class="fa-solid fa-user-graduate absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
          <input
            ref="studentInputRef"
            v-model="studentSearchText"
            @focus="isStudentDropOpen = true"
            @input="selectedStudentId = null"
            type="text"
            placeholder="Tìm theo tên, email, SĐT..."
            autocomplete="off"
            class="w-full pl-10 pr-8 py-2.5 rounded-xl bg-slate-50 border text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
            :class="selectedStudentId ? 'border-emerald-400 bg-emerald-50/30 font-semibold text-slate-900' : 'border-slate-200'"
          />
          <button v-if="studentSearchText" type="button" @click="clearStudent"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-500 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-xmark text-[10px]"></i>
          </button>
        </div>
        <!-- Dropdown -->
        <ul v-if="isStudentDropOpen && filteredStudents.length"
          class="absolute z-40 top-full mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-2xl max-h-56 overflow-y-auto custom-scrollbar">
          <li v-for="s in filteredStudents" :key="s.id"
            @mousedown.prevent="selectStudent(s)"
            class="flex items-center gap-3 px-4 py-2.5 hover:bg-emerald-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors"
            :class="selectedStudentId === s.id ? 'bg-emerald-50' : ''">
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-black text-xs flex items-center justify-center shrink-0">{{ s.full_name?.charAt(0) }}</div>
            <div class="min-w-0">
              <p class="text-sm font-bold text-slate-800 truncate">{{ s.full_name }}</p>
              <p class="text-xs text-slate-500 truncate">{{ s.email }}{{ s.phone ? ' · ' + s.phone : '' }}</p>
            </div>
            <i v-if="selectedStudentId === s.id" class="fa-solid fa-check text-emerald-600 ml-auto shrink-0 text-xs"></i>
          </li>
        </ul>
        <div v-else-if="isStudentDropOpen && studentSearchText && !selectedStudentId"
          class="absolute z-40 top-full mt-1 w-72 bg-white border border-slate-200 rounded-xl shadow-xl px-4 py-3 text-sm text-slate-400 text-center">
          Không tìm thấy học viên.
        </div>
      </div>

      <!-- Course filter -->
      <div class="shrink-0 min-w-[200px]">
        <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 mb-1 ml-1">Lọc theo khóa học</label>
        <div class="relative">
          <i class="fa-solid fa-book-open absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
          <select v-model="selectedCourseId"
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] cursor-pointer transition-all hover:bg-white">
            <option value="">Tất cả khóa học</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.title }}</option>
          </select>
        </div>
      </div>

      <!-- Table search -->
      <div class="shrink-0 min-w-[200px]">
        <label class="block text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 mb-1 ml-1">Tìm trong bảng</label>
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
          <input v-model="tableSearch" type="text" placeholder="Tên lớp, ca học..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all" />
        </div>
      </div>

      <!-- Stats (right) -->
      <div class="flex gap-3 md:ml-auto shrink-0">
        <div class="text-center px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
          <p class="text-[0.65rem] font-bold uppercase tracking-wider text-emerald-600">Học viên</p>
          <p class="text-2xl font-black text-slate-800">{{ students.length }}</p>
        </div>
        <div class="text-center px-4 py-2 bg-sky-50 rounded-xl border border-sky-100">
          <p class="text-[0.65rem] font-bold uppercase tracking-wider text-sky-600">Nhóm ca</p>
          <p class="text-2xl font-black text-slate-800">{{ classRows.length }}</p>
        </div>
        <div class="text-center px-4 py-2 bg-purple-50 rounded-xl border border-purple-100">
          <p class="text-[0.65rem] font-bold uppercase tracking-wider text-purple-600">Ghi danh</p>
          <p class="text-2xl font-black text-slate-800">{{ enrollments.length }}</p>
        </div>
      </div>
    </div>

    <!-- ══ SELECTED STUDENT BANNER ══════════════════════════════════ -->
    <div v-if="selectedStudentId"
      class="flex items-center gap-4 px-5 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-sm font-medium text-emerald-800">
      <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-base shrink-0"
        style="background: linear-gradient(135deg,#7ae582,#16a34a); color:white;">
        {{ selectedStudentName?.charAt(0) }}
      </div>
      <div>
        <span class="font-black">{{ selectedStudentName }}</span>
        <span class="text-emerald-600 ml-2 text-xs">đã được chọn. Bấm nút <strong>Ghi danh</strong> trên dòng muốn xếp lớp.</span>
      </div>
      <button @click="clearStudent" class="ml-auto text-emerald-500 hover:text-red-500 transition-colors">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div v-else class="flex items-center gap-3 px-5 py-3 bg-amber-50 border border-amber-200 rounded-2xl text-xs font-medium text-amber-700">
      <i class="fa-solid fa-arrow-up-left"></i>
      Tìm và chọn học viên ở trên trước, sau đó bấm <strong class="mx-1">Ghi danh</strong> trên dòng lớp học bạn muốn xếp.
    </div>

    <!-- ══ CLASS SELECTION TABLE ═════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex-1">
      <div class="px-6 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
          <i class="fa-solid fa-table-list text-[#16a34a]"></i>
          Danh sách Nhóm Ca học
          <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-500 font-bold">{{ filteredRows.length }}</span>
        </div>
        <span class="text-xs text-slate-400">Chọn hàng để ghi danh học viên đã chọn</span>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[900px]">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-5 py-3.5">Khóa học</th>
              <th class="px-5 py-3.5">Tên lớp</th>
              <th class="px-5 py-3.5">Nhóm ca / Lịch học</th>
              <th class="px-5 py-3.5">Hình thức</th>
              <th class="px-5 py-3.5">Khung giờ</th>
              <th class="px-5 py-3.5">Khai giảng</th>
              <th class="px-5 py-3.5">Kết thúc</th>
              <th class="px-5 py-3.5">Sĩ số</th>
              <th class="px-5 py-3.5 text-center w-32">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr v-for="row in paginatedRows" :key="row.detail_id"
              class="hover:bg-slate-50/60 transition-colors group"
              :class="row.current_students >= row.max_students ? 'opacity-60' : ''">

              <!-- Khóa học -->
              <td class="px-5 py-3.5">
                <div class="flex flex-col gap-1">
                  <span class="font-bold text-slate-800 text-sm leading-tight">{{ row.course_name || '—' }}</span>
                  <span v-if="row.level" class="inline-block self-start px-2 py-0.5 rounded-md text-[0.65rem] font-bold uppercase tracking-wide"
                    :class="levelClass(row.level)">{{ row.level }}</span>
                </div>
              </td>

              <!-- Tên lớp -->
              <td class="px-5 py-3.5">
                <span class="font-black text-slate-700 text-sm">{{ row.class_name }}</span>
              </td>

              <!-- Nhóm ca -->
              <td class="px-5 py-3.5">
                <div class="flex flex-col gap-1">
                  <!-- Chỉ hiển thị phần Ca (Sáng/Chiều/Tối), ẩn phần ngày vì đã có bên dưới -->
                  <span class="font-bold text-slate-800 text-sm">{{ shiftOnly(row.detail_name, row.class_name) }}</span>
                  <span v-if="daysLabel(row.detail_name)" class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-calendar-days text-[10px]"></i>
                    {{ daysLabel(row.detail_name) }}
                  </span>
                </div>
              </td>

              <!-- Hình thức -->
              <td class="px-5 py-3.5">
                <span v-if="row.teaching_type" class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg border"
                  :class="row.teaching_type === 'online'
                    ? 'bg-blue-50 text-blue-700 border-blue-100'
                    : 'bg-slate-50 text-slate-600 border-slate-200'">
                  <i :class="row.teaching_type === 'online' ? 'fa-solid fa-wifi' : 'fa-solid fa-door-open'"></i>
                  {{ row.teaching_type === 'online' ? 'Online' : 'Offline' }}
                </span>
                <span v-else class="text-slate-300 text-xs">—</span>
              </td>

              <!-- Khung giờ -->
              <td class="px-5 py-3.5 text-xs font-mono font-bold text-slate-700 whitespace-nowrap">
                <span v-if="row.start_time && row.end_time">{{ fmtTime(row.start_time) }} – {{ fmtTime(row.end_time) }}</span>
                <span v-else class="text-slate-300">—</span>
              </td>

              <!-- Khai giảng -->
              <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">{{ fmtDate(row.start_date) }}</td>

              <!-- Kết thúc -->
              <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">{{ fmtDate(row.end_date) }}</td>

              <!-- Sĩ số -->
              <td class="px-5 py-3.5">
                <div class="flex flex-col gap-1.5">
                  <div class="flex items-center gap-1.5">
                    <div class="w-20 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                      <div class="h-full rounded-full transition-all"
                        :style="{ width: capPct(row) + '%' }"
                        :class="capPct(row) >= 100 ? 'bg-red-500' : capPct(row) >= 80 ? 'bg-amber-500' : 'bg-emerald-500'"></div>
                    </div>
                    <span class="text-xs font-bold whitespace-nowrap"
                      :class="capPct(row) >= 100 ? 'text-red-600' : 'text-slate-600'">
                      {{ row.current_students }}/{{ row.max_students }}
                    </span>
                  </div>
                  <span v-if="row.current_students >= row.max_students"
                    class="text-[0.6rem] font-bold text-red-500 uppercase tracking-wide">Đủ sĩ số</span>
                </div>
              </td>

              <!-- Action -->
              <td class="px-5 py-3.5 text-center">
                <button
                  @click="enrollStudentInRow(row)"
                  :disabled="!selectedStudentId || row.current_students >= row.max_students || enrollingRowId === row.detail_id"
                  class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm border"
                  :class="!selectedStudentId
                    ? 'bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed'
                    : row.current_students >= row.max_students
                    ? 'bg-red-50 border-red-100 text-red-400 cursor-not-allowed'
                    : 'text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300'"
                  :style="selectedStudentId && row.current_students < row.max_students ? 'background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)' : ''"
                >
                  <i :class="enrollingRowId === row.detail_id ? 'fa-solid fa-circle-notch fa-spin' : 'fa-solid fa-user-plus'"></i>
                  {{ enrollingRowId === row.detail_id ? 'Đang xử lý...' : row.current_students >= row.max_students ? 'Đã đầy' : 'Ghi danh' }}
                </button>
              </td>
            </tr>

            <tr v-if="filteredRows.length === 0">
              <td colspan="9" class="px-6 py-14 text-center text-slate-400">
                <i class="fa-solid fa-layer-group text-4xl mb-3 block text-slate-200"></i>
                <p class="text-sm font-medium">Không có nhóm ca học nào{{ tableSearch || selectedCourseId ? ' khớp với bộ lọc' : '' }}.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination: Bảng chọn lớp -->
      <div v-if="totalClassPages > 1" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-2">
        <span class="text-xs text-slate-500">Hiển thị {{ classPageStart + 1 }}–{{ Math.min(classPageStart + PAGE_SIZE, filteredRows.length) }} / {{ filteredRows.length }}</span>
        <div class="flex items-center gap-1">
          <button @click="classPage--" :disabled="classPage === 1"
            class="w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-30 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-chevron-left text-xs"></i>
          </button>
          <template v-for="p in totalClassPages" :key="p">
            <button @click="classPage = p"
              class="w-8 h-8 rounded-lg text-xs font-bold transition-colors"
              :class="classPage === p ? 'text-white' : 'text-slate-600 hover:bg-slate-100'"
              :style="classPage === p ? 'background: linear-gradient(135deg,#7ae582,#16a34a)' : ''">
              {{ p }}
            </button>
          </template>
          <button @click="classPage++" :disabled="classPage === totalClassPages"
            class="w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-30 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ══ ENROLLMENT LIST TABLE ══════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-6 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
          <i class="fa-solid fa-list-check text-[#16a34a]"></i>
          Danh sách ghi danh
          <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-500 font-bold">{{ enrollments.length }}</span>
        </div>
        <div class="relative w-56">
          <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
          <input v-model="enrollSearch" type="text" placeholder="Tìm học viên, lớp..."
            class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all" />
        </div>
      </div>
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-5 py-3 w-10">#</th>
              <th class="px-5 py-3">Học viên</th>
              <th class="px-5 py-3">Lớp</th>
              <th class="px-5 py-3">Ca học</th>
              <th class="px-5 py-3">Sĩ số</th>
              <th class="px-5 py-3">Ngày ghi danh</th>
              <th class="px-5 py-3">Trạng thái</th>
              <th class="px-5 py-3 text-right w-24">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm">
            <tr v-for="e in paginatedEnrollments" :key="e.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-5 py-3 text-xs text-slate-400 font-bold">#{{ e.id }}</td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-black text-xs flex items-center justify-center shrink-0">{{ e.student_name?.charAt(0) }}</div>
                  <div>
                    <p class="font-bold text-slate-800 text-sm">{{ e.student_name }}</p>
                    <p class="text-xs text-slate-500">{{ e.student_email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3 font-bold text-slate-700 text-sm">{{ e.class_name }}</td>
              <td class="px-5 py-3">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-lg">
                  <i class="fa-regular fa-clock"></i> {{ e.detail_name || '—' }}
                </span>
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-14 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full"
                      :style="{width: Math.min(e.current_students/Math.max(e.max_students,1)*100,100)+'%'}"
                      :class="e.current_students>=e.max_students?'bg-red-500':e.current_students/e.max_students>=0.8?'bg-amber-500':'bg-emerald-500'"></div>
                  </div>
                  <span class="text-xs text-slate-500">{{ e.current_students }}/{{ e.max_students }}</span>
                </div>
              </td>
              <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">{{ fmtDateTime(e.enrollment_date) }}</td>
              <td class="px-5 py-3">
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg border"
                  :class="e.status==='active'?'bg-emerald-50 text-emerald-700 border-emerald-100':e.status==='completed'?'bg-sky-50 text-sky-700 border-sky-100':'bg-red-50 text-red-600 border-red-100'">
                  <i :class="e.status==='active'?'fa-solid fa-circle-check':e.status==='completed'?'fa-solid fa-graduation-cap':'fa-solid fa-xmark'"></i>
                  {{ e.status==='active'?'Đang học':e.status==='completed'?'Hoàn thành':'Đã nghỉ' }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                  <!-- ĐỔI CA / LỚP -->
                  <button @click="openEditModal(e)" title="Đổi lớp / Đổi ca"
                    class="w-7 h-7 rounded-lg bg-sky-50 text-sky-500 hover:bg-sky-600 hover:text-white transition-colors flex items-center justify-center">
                    <i class="fa-solid fa-arrow-right-arrow-left text-xs"></i>
                  </button>
                  <button @click="deleteEnrollment(e.id, e.student_name)" title="Hủy ghi danh"
                    class="w-7 h-7 rounded-lg bg-red-50 text-red-400 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="paginatedEnrollments.length === 0">
              <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                <i class="fa-solid fa-inbox text-3xl mb-2 block text-slate-200"></i>
                <p class="text-sm">Chưa có ghi danh nào.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination: Bảng ghi danh -->
      <div v-if="totalEnrollPages > 1" class="px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-2">
        <span class="text-xs text-slate-500">Hiển thị {{ enrollPageStart + 1 }}–{{ Math.min(enrollPageStart + PAGE_SIZE, filteredEnrollments.length) }} / {{ filteredEnrollments.length }}</span>
        <div class="flex items-center gap-1">
          <button @click="enrollPage--" :disabled="enrollPage === 1"
            class="w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-30 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-chevron-left text-xs"></i>
          </button>
          <template v-for="p in totalEnrollPages" :key="p">
            <button @click="enrollPage = p"
              class="w-8 h-8 rounded-lg text-xs font-bold transition-colors"
              :class="enrollPage === p ? 'text-white' : 'text-slate-600 hover:bg-slate-100'"
              :style="enrollPage === p ? 'background: linear-gradient(135deg,#7ae582,#16a34a)' : ''">
              {{ p }}
            </button>
          </template>
          <button @click="enrollPage++" :disabled="enrollPage === totalEnrollPages"
            class="w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-30 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ══ EDIT MODAL (ĐỔI LỚP / CA) ═════════════════════════════════ -->
    <div v-if="editModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-arrow-right-arrow-left text-sky-500"></i> Đổi lớp / Đổi ca học
          </h3>
          <button @click="editModal.open = false" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>
        <div class="p-6 space-y-5">
          <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl">
            <div class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">Học viên</div>
            <div class="font-bold text-slate-800 text-sm">{{ editModal.studentName }}</div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Chọn nhóm ca học mới</label>
            <div class="relative">
              
              <select v-model="editModal.newDetailId"
                class="w-full pl-10 pr-4 py-3 rounded-xl bg-white border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400 appearance-none shadow-sm cursor-pointer hover:border-slate-300 transition-all">
                <option :value="null" disabled>-- Chọn lớp và ca học --</option>
                <option v-for="r in classRows" :key="r.detail_id" :value="r.detail_id"
                  :disabled="r.current_students >= r.max_students && r.detail_id !== editModal.oldDetailId">
                  {{ r.course_name ? '[' + r.course_name + '] ' : '' }}{{ r.class_name }} — {{ fullShiftLabel(r.detail_name, r.class_name) }} ({{ r.current_students }}/{{ r.max_students }})
                </option>
              </select>
              <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>
            <p v-if="editModal.newDetailId === editModal.oldDetailId" class="mt-2 text-xs text-amber-600 font-medium">
              <i class="fa-solid fa-circle-info mr-1"></i> Đây là ca học hiện tại.
            </p>
          </div>
          <div class="pt-4 border-t border-slate-100 flex gap-3">
            <button @click="saveEditShift" :disabled="editModal.newDetailId === editModal.oldDetailId || !editModal.newDetailId"
              class="flex-1 py-3 rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-emerald-200 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
              <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu thay đổi
            </button>
            <button @click="editModal.open = false" class="px-6 py-3 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Hủy</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error toast inline -->
    <transition name="fade">
      <div v-if="errorMessage" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3.5 bg-red-600 text-white text-sm font-medium rounded-2xl shadow-2xl max-w-md">
        <i class="fa-solid fa-circle-exclamation shrink-0"></i>
        <span>{{ errorMessage }}</span>
        <button @click="errorMessage=''" class="ml-2 opacity-70 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { apiFetch } from '../../utils/api'
import { clearAuthSession } from '../../utils/auth'
import { notifySuccess, notifyError } from '../../utils/notify'
import { openConfirm } from '../../utils/confirm'

const router = useRouter()

// ── State ─────────────────────────────────────────────────────────
const students    = ref([])
const courses     = ref([])
const classRows   = ref([])
const enrollments = ref([])
const errorMessage = ref('')
const enrollingRowId = ref(null)

// Filters
const selectedCourseId = ref('')
const tableSearch      = ref('')
const enrollSearch     = ref('')

// Student dropdown
const studentSearchText  = ref('')
const selectedStudentId  = ref(null)
const selectedStudentName = ref('')
const isStudentDropOpen  = ref(false)
const studentDropdownRef = ref(null)
const studentInputRef    = ref(null)

// ── Computed ──────────────────────────────────────────────────────
const filteredStudents = computed(() => {
  const q = studentSearchText.value.trim().toLowerCase()
  if (!q || selectedStudentId.value) return []
  return students.value.filter(s =>
    (s.full_name || '').toLowerCase().includes(q) ||
    (s.email || '').toLowerCase().includes(q) ||
    (s.phone || '').toLowerCase().includes(q)
  ).slice(0, 10)
})

const filteredRows = computed(() => {
  let rows = classRows.value
  if (selectedCourseId.value) rows = rows.filter(r => r.course_id === Number(selectedCourseId.value))
  const q = tableSearch.value.trim().toLowerCase()
  if (q) rows = rows.filter(r =>
    (r.class_name   || '').toLowerCase().includes(q) ||
    (r.detail_name  || '').toLowerCase().includes(q) ||
    (r.course_name  || '').toLowerCase().includes(q)
  )
  return rows
})

const filteredEnrollments = computed(() => {
  const q = enrollSearch.value.trim().toLowerCase()
  if (!q) return enrollments.value
  return enrollments.value.filter(e =>
    (e.student_name || '').toLowerCase().includes(q) ||
    (e.student_email || '').toLowerCase().includes(q) ||
    (e.class_name || '').toLowerCase().includes(q) ||
    (e.detail_name || '').toLowerCase().includes(q)
  )
})

// ── Pagination ────────────────────────────────────────────────────
const PAGE_SIZE   = 10
const classPage   = ref(1)
const enrollPage  = ref(1)

const classPageStart  = computed(() => (classPage.value  - 1) * PAGE_SIZE)
const enrollPageStart = computed(() => (enrollPage.value - 1) * PAGE_SIZE)

const totalClassPages  = computed(() => Math.max(1, Math.ceil(filteredRows.value.length  / PAGE_SIZE)))
const totalEnrollPages = computed(() => Math.max(1, Math.ceil(filteredEnrollments.value.length / PAGE_SIZE)))

const paginatedRows        = computed(() => filteredRows.value.slice(classPageStart.value, classPageStart.value + PAGE_SIZE))
const paginatedEnrollments = computed(() => filteredEnrollments.value.slice(enrollPageStart.value, enrollPageStart.value + PAGE_SIZE))

// Reset page khi filter thay đổi
watch(filteredRows,        () => { classPage.value  = 1 })
watch(filteredEnrollments, () => { enrollPage.value = 1 })

// ── Helpers ───────────────────────────────────────────────────────
const capPct = (row) => Math.min(Math.round((row.current_students / Math.max(row.max_students, 1)) * 100), 100)

const fmtTime = (t) => t ? t.slice(0, 5) : '—'

const fmtDate = (d) => {
  if (!d) return '—'
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(dt)
}

const fmtDateTime = (d) => {
  if (!d) return '—'
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(dt)
}

// Tách phần Ca (Sáng/Chiều/Tối) ra, bỏ phần ngày vì đã hiển thị riêng
// VD: "IELTS 01 - Tối - 2/4/6" → "Tối"
const shiftOnly = (detailName, className) => {
  if (!detailName) return '—'
  const prefix = (className || '') + ' - '
  const afterClass = detailName.startsWith(prefix) ? detailName.slice(prefix.length) : detailName
  // Nếu có phần ngày (2/4/6, 3/5/7, T7/CN) thì chỉ lấy phần trước dấu " - "
  const parts = afterClass.split(' - ')
  const dayKeys = ['2/4/6', '3/5/7', 'T7/CN']
  const hasDays = parts.some(p => dayKeys.includes(p))
  return hasDays ? parts.filter(p => !dayKeys.includes(p)).join(' - ') : afterClass
}

// Giữ cả 2 helper (shiftLabel dùng cho backward compat)
const shiftLabel = shiftOnly

// Khác với shiftOnly, hàm này chỉ cắt tên lớp, giữ lại cả Ca và Ngày (để hiển thị trong dropdown)
// VD: "IELTS 01 - Tối - 2/4/6" → "Tối - 2/4/6"
const fullShiftLabel = (detailName, className) => {
  if (!detailName) return '—'
  const prefix = (className || '') + ' - '
  return detailName.startsWith(prefix) ? detailName.slice(prefix.length) : detailName
}

// Lịch ngày học từ detail_name
const DAYS_MAP = { '2/4/6': 'T2 – T4 – T6', '3/5/7': 'T3 – T5 – T7', 'T7/CN': 'T7 – CN' }
const daysLabel = (detailName) => {
  if (!detailName) return '—'
  for (const [k, v] of Object.entries(DAYS_MAP)) {
    if (detailName.includes(k)) return v
  }
  return ''
}

const levelClass = (level) => {
  const l = (level || '').toUpperCase()
  if (['A1','A2'].includes(l)) return 'bg-emerald-100 text-emerald-700'
  if (['B1','B2'].includes(l)) return 'bg-sky-100 text-sky-700'
  if (['C1','C2'].includes(l)) return 'bg-purple-100 text-purple-700'
  return 'bg-slate-100 text-slate-600'
}

// ── Edit Modal ────────────────────────────────────────────────────
const editModal = ref({ open: false, id: null, studentName: '', oldDetailId: null, newDetailId: null })

const openEditModal = (e) => {
  editModal.value = { 
    open: true, 
    id: e.id, 
    studentName: e.student_name, 
    oldDetailId: e.class_detail_id, 
    newDetailId: e.class_detail_id 
  }
}

const saveEditShift = async () => {
  if (!editModal.value.newDetailId || editModal.value.newDetailId === editModal.value.oldDetailId) return
  
  try {
    const res = await apiFetch(`admin/enrollments.php?id=${editModal.value.id}`, {
      method: 'PUT',
      body: JSON.stringify({ class_detail_id: editModal.value.newDetailId })
    })
    if (res.status === 401 || res.status === 403) { redirectToLogin(); return }
    const result = await res.json()
    if (result.status === 'success') {
      notifySuccess(result.message)
      editModal.value.open = false
      await fetchData()
    } else notifyError(result.message)
  } catch { notifyError('Lỗi kết nối.') }
}

// ── Auth guard ────────────────────────────────────────────────────
const redirectToLogin = () => { clearAuthSession(); router.push('/login') }

// ── Student dropdown ──────────────────────────────────────────────
const selectStudent = (s) => {
  selectedStudentId.value   = s.id
  selectedStudentName.value = s.full_name
  studentSearchText.value   = s.full_name
  isStudentDropOpen.value   = false
}

const clearStudent = () => {
  selectedStudentId.value   = null
  selectedStudentName.value = ''
  studentSearchText.value   = ''
  isStudentDropOpen.value   = false
  studentInputRef.value?.focus()
}

const onClickOutside = (e) => {
  if (studentDropdownRef.value && !studentDropdownRef.value.contains(e.target)) {
    isStudentDropOpen.value = false
    if (!selectedStudentId.value) studentSearchText.value = ''
  }
}

// ── API ───────────────────────────────────────────────────────────
const fetchData = async () => {
  try {
    const res = await apiFetch('admin/enrollments.php')
    if (res.status === 401 || res.status === 403) { redirectToLogin(); return }
    const result = await res.json()
    if (result.status === 'success') {
      students.value    = result.data.students    || []
      courses.value     = result.data.courses     || []
      classRows.value   = result.data.classRows   || []
      enrollments.value = result.data.enrollments || []
    } else {
      errorMessage.value = result.message || 'Không thể tải dữ liệu.'
    }
  } catch {
    errorMessage.value = 'Lỗi kết nối máy chủ.'
  }
}

const enrollStudentInRow = async (row) => {
  if (!selectedStudentId.value) { notifyError('Vui lòng chọn học viên trước.'); return }
  if (row.current_students >= row.max_students) { notifyError('Ca học này đã đủ sĩ số.'); return }

  enrollingRowId.value = row.detail_id
  errorMessage.value   = ''

  try {
    const res = await apiFetch('admin/enrollments.php', {
      method: 'POST',
      body: JSON.stringify({
        student_id: selectedStudentId.value,
        class_id: row.class_id,
        class_detail_id: row.detail_id
      })
    })
    if (res.status === 401 || res.status === 403) { redirectToLogin(); return }
    const result = await res.json()

    if (result.status === 'success') {
      notifySuccess(result.message)
      await fetchData()
    } else {
      errorMessage.value = result.message
      notifyError(result.message)
    }
  } catch {
    notifyError('Lỗi kết nối máy chủ.')
  } finally {
    enrollingRowId.value = null
  }
}

const deleteEnrollment = async (id, studentName) => {
  const ok = await openConfirm({
    title: 'Hủy ghi danh',
    message: `Hủy ghi danh của "${studentName}"?`,
    confirmText: 'Hủy ghi danh', cancelText: 'Giữ lại', tone: 'danger'
  })
  if (!ok) return
  try {
    const res = await apiFetch(`admin/enrollments.php?id=${id}`, { method: 'DELETE' })
    if (res.status === 401 || res.status === 403) { redirectToLogin(); return }
    const result = await res.json()
    if (result.status === 'success') { notifySuccess(result.message); await fetchData() }
    else notifyError(result.message)
  } catch { notifyError('Lỗi kết nối máy chủ.') }
}

// ── Lifecycle ─────────────────────────────────────────────────────
onMounted(() => { fetchData(); document.addEventListener('click', onClickOutside) })
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 8px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
