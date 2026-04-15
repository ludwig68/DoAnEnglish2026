<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn">
    <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4">
      <button @click="openModal('add')" class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300" style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
        <i class="fa-solid fa-plus"></i> Tạo lớp mới
      </button>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6">
      <div class="relative w-full md:w-1/2">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input v-model="searchQuery" type="text" placeholder="Tìm theo tên lớp, khóa học hoặc giảng viên..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all" />
      </div>
    </div>

    <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ errorMessage }}</div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center px-6 py-12 text-slate-500">Đang tải dữ liệu lớp học...</div>
      <div v-else class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-6 py-4 w-16">ID</th>
              <th class="px-6 py-4">Tên lớp</th>
              <th class="px-6 py-4">Khóa học</th>
              <th class="px-6 py-4">Giảng viên chủ nhiệm</th>
              <th class="px-6 py-4">Thời gian học</th>
              <th class="px-6 py-4 text-right w-24">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr v-for="item in filteredClasses" :key="item.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-4 font-bold text-slate-500">#{{ item.id }}</td>
              <td class="px-6 py-4"><span class="font-black text-slate-800 text-base">{{ item.class_name }}</span></td>
              <td class="px-6 py-4 font-medium text-emerald-600">{{ item.course_name || 'Khóa học đã bị xóa' }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-user-tie"></i></div>
                  <span class="font-bold text-slate-700">{{ item.instructor_name || 'Chưa phân công' }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col gap-1">
                  <span><i class="fa-regular fa-calendar-check mr-1"></i> BĐ: {{ formatDate(item.start_date) }}</span>
                  <span><i class="fa-regular fa-calendar-xmark mr-1"></i> KT: {{ formatDate(item.end_date) }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openClassDetailsModal(item)" title="Quản lý nhóm ca học" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-layer-group text-xs"></i></button>
                  <button @click="openModal('edit', item)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-pen text-xs"></i></button>
                  <button @click="deleteClass(item.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors"><i class="fa-solid fa-trash-can text-xs"></i></button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredClasses.length === 0"><td colspan="6" class="px-6 py-12 text-center text-slate-500">Không tìm thấy lớp học nào.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col mt-6">
      <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div><h3 class="text-base font-black text-slate-800">Chi tiết ca học</h3><p class="text-xs text-slate-500 mt-1">Hiển thị {{ paginatedSchedules.length }} / {{ filteredSchedules.length }} ca học</p></div>
        <div class="text-xs font-medium text-slate-500">Trang {{ safeSchedulePage }} / {{ totalSchedulePages }}</div>
      </div>
      <div v-if="isLoading" class="flex items-center justify-center px-6 py-12 text-slate-500 border-t border-slate-100">Đang tải dữ liệu ca học...</div>
      <div v-else class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/60 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-6 py-4 w-16">ID</th><th class="px-6 py-4">Lớp học</th><th class="px-6 py-4">Nhóm học</th><th class="px-6 py-4">Ngày học</th><th class="px-6 py-4">Khung giờ</th><th class="px-6 py-4">Giảng viên dạy</th><th class="px-6 py-4">Trạng thái</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr v-for="schedule in paginatedSchedules" :key="schedule.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 font-bold text-slate-500">#{{ schedule.id }}</td>
              <td class="px-6 py-4"><div class="flex flex-col gap-1"><span class="font-bold text-slate-800">{{ schedule.class_name || 'Lớp đã bị xóa' }}</span><span class="text-xs text-emerald-600 font-medium">{{ schedule.course_name || 'Chưa gắn khóa học' }}</span></div></td>
              <td class="px-6 py-4 text-sm font-medium text-slate-600">{{ schedule.class_detail_name || 'Chưa có nhóm học' }}</td>
              <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(schedule.study_date) }}</td>
              <td class="px-6 py-4 text-sm font-medium text-slate-600">{{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}</td>
              <td class="px-6 py-4 text-sm text-slate-600">{{ schedule.teacher_name || 'Chưa xếp giảng viên' }}</td>
              <td class="px-6 py-4"><span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold" :class="getScheduleStatusClass(schedule.status)">{{ getScheduleStatusLabel(schedule.status) }}</span></td>
            </tr>
            <tr v-if="paginatedSchedules.length === 0"><td colspan="7" class="px-6 py-12 text-center text-slate-500">Không tìm thấy ca học nào.</td></tr>
          </tbody>
        </table>
      </div>
      <div v-if="filteredSchedules.length > 0" class="px-6 py-4 border-t border-slate-100 bg-white flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="text-sm text-slate-500">Đang xem {{ scheduleRangeStart }}-{{ scheduleRangeEnd }} / {{ filteredSchedules.length }} ca học</div>
        <div class="flex items-center gap-2">
          <button type="button" @click="goToPreviousSchedulePage" :disabled="safeSchedulePage === 1" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">Trước</button>
          <span class="px-3 py-2 rounded-xl bg-slate-50 text-sm font-bold text-slate-600 min-w-[96px] text-center">{{ safeSchedulePage }} / {{ totalSchedulePages }}</span>
          <button type="button" @click="goToNextSchedulePage" :disabled="safeSchedulePage === totalSchedulePages" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition">Sau</button>
        </div>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-chalkboard text-[#16a34a]"></i>{{ modalMode === 'add' ? 'Tạo lớp học mới' : 'Chỉnh sửa lớp học' }}</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <form @submit.prevent="saveClass" class="p-6 space-y-4">
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tên lớp <span class="text-red-500">*</span></label><input v-model="formData.class_name" required type="text" placeholder="VD: IELTS Cơ Bản - K01" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" /></div>
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Thuộc khóa học <span class="text-red-500">*</span></label><select v-model="formData.course_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"><option value="" disabled>-- Chọn khóa học --</option><option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option></select></div>
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Giảng viên chủ nhiệm</label><select v-model="formData.instructor_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"><option value="">-- Chưa phân công --</option><option v-for="teacher in instructors" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option></select></div>
          <div class="flex gap-4">
            <div class="w-1/2"><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ngày khai giảng <span class="text-red-500">*</span></label><input v-model="formData.start_date" type="date" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" /></div>
            <div class="w-1/2"><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ngày kết thúc dự kiến <span class="text-red-500">*</span></label><input v-model="formData.end_date" type="date" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" /></div>
          </div>
          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
            <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Hủy</button>
            <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-1" style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"><i class="fa-solid fa-floppy-disk mr-1"></i> Lưu thông tin</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isClassDetailsOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="mx-auto my-6 w-full max-w-5xl overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-2xl animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-emerald-50/50">
          <div><h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fa-solid fa-layer-group text-emerald-600"></i>Quản lý nhóm học - {{ activeClassDetails?.class_name }}</h3><p class="text-xs text-slate-500 mt-1">Sĩ số áp dụng cho từng nhóm/ca học chi tiết.</p></div>
          <button @click="closeClassDetailsModal" class="text-slate-400 hover:text-red-500 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div class="flex max-h-[calc(100vh-6rem)] flex-col gap-4 overflow-hidden p-5">
          <section class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
              <div>
                <h4 class="text-sm font-black text-slate-800">{{ detailFormMode === 'add' ? 'Tạo nhóm học mới' : 'Chỉnh sửa nhóm học' }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ detailFormMode === 'add' ? 'Thiết lập ca học và sĩ số cho nhóm mới.' : 'Cập nhật lại ca học hoặc sĩ số.' }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-[11px] font-bold" :class="detailFormMode === 'add' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'">{{ detailFormMode === 'add' ? 'Thêm mới' : 'Đang sửa' }}</span>
            </div>

            <form @submit.prevent="saveClassDetail" class="space-y-3 detail-form">
              <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_160px_auto] lg:items-end">
              <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ca học <span class="text-red-500">*</span></label><select v-model="detailFormData.shift" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"><option value="" disabled>-- Chọn ca học --</option><option v-for="shift in detailShiftOptions" :key="shift" :value="shift">{{ shift }}</option></select></div>
              <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Lịch học <span class="text-red-500">*</span></label><select v-model="detailFormData.days" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"><option value="" disabled>-- Chọn lịch học --</option><option v-for="days in detailDayOptions" :key="days" :value="days">{{ days }}</option></select></div>
              <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sĩ số tối đa <span class="text-red-500">*</span></label><input v-model="detailFormData.max_students" type="number" min="1" max="500" required class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" /></div>
              <div class="rounded-xl bg-white border border-dashed border-slate-200 px-4 py-3 text-sm text-slate-500"><span class="font-bold text-slate-700">Tên nhóm sẽ lưu:</span> {{ previewDetailName || 'Chọn ca học và lịch học để xem trước' }}</div>
              </div>
              <div class="pt-2 flex justify-end gap-3">
                <button v-if="detailFormMode === 'edit'" type="button" @click="resetDetailForm" class="px-4 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-200 hover:bg-slate-300 transition">Hủy sửa</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2" style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"><i :class="detailFormMode === 'add' ? 'fa-solid fa-plus' : 'fa-solid fa-floppy-disk'"></i>{{ detailFormMode === 'add' ? 'Thêm nhóm học' : 'Lưu cập nhật' }}</button>
              </div>
            </form>
          </section>

          <section class="min-h-0 min-w-0">
            <div class="mb-3 flex items-center justify-between gap-3">
              <div><h4 class="text-sm font-black text-slate-800">Danh sách nhóm học</h4><p class="text-xs text-slate-500 mt-1">Nhấn biểu tượng bút để chỉnh sửa trong khung bên trái.</p></div>
              <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ classDetails.length }} nhóm</span>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 md:p-4">
              <div v-if="isLoadingDetails" class="h-full flex items-center justify-center text-slate-500 text-sm">Đang tải dữ liệu...</div>
              <div v-else-if="classDetails.length === 0" class="h-full flex items-center justify-center text-slate-500 text-sm italic">Chưa có nhóm học nào. Hãy tạo nhóm đầu tiên.</div>
              <div v-else class="max-h-[48vh] space-y-3 overflow-y-auto custom-scrollbar pr-1">
                <article v-for="detail in classDetails" :key="detail.id" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <div class="text-sm font-black text-slate-800 leading-snug break-words">{{ detail.detail_name }}</div>
                      <div class="mt-2 text-xs text-slate-500">Đã tạo: {{ formatDateTime(detail.created_at) }}</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                      <button @click="startEditingClassDetail(detail)" class="w-8 h-8 rounded-lg text-sky-500 hover:bg-sky-50 hover:text-sky-600 transition flex items-center justify-center" title="Chỉnh sửa"><i class="fa-solid fa-pen text-xs"></i></button>
                      <button @click="deleteClassDetail(detail.id)" class="w-8 h-8 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex items-center justify-center" title="Xóa"><i class="fa-solid fa-trash-can text-xs"></i></button>
                    </div>
                  </div>
                  <div class="mt-3">
                    <div class="flex items-center justify-between text-xs font-bold">
                      <span class="text-slate-700">{{ detail.current_students || 0 }}/{{ detail.max_students || 0 }} học viên</span>
                      <span class="rounded-full px-2 py-0.5" :class="getCapacityBadgeClass(detail.current_students, detail.max_students)">{{ getCapacityLabel(detail.current_students, detail.max_students) }}</span>
                    </div>
                    <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full transition-all" :class="getCapacityBarClass(detail.current_students, detail.max_students)" :style="{ width: getCapacityPercent(detail.current_students, detail.max_students) + '%' }"></div></div>
                  </div>
                </article>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "../../utils/api";
import { clearAuthSession } from "../../utils/auth";
import { openConfirm } from "../../utils/confirm";
import { notifyError, notifySuccess, notifyWarning } from "../../utils/notify";

const router = useRouter();
const classes = ref([]), schedules = ref([]), courses = ref([]), instructors = ref([]), searchQuery = ref(""), isLoading = ref(false), errorMessage = ref("");
const schedulePage = ref(1), schedulesPerPage = 10;
const isModalOpen = ref(false), modalMode = ref("add");
const isClassDetailsOpen = ref(false), activeClassDetails = ref(null), classDetails = ref([]), isLoadingDetails = ref(false);
const detailShiftOptions = ["Sáng", "Chiều", "Tối"], detailDayOptions = ["2/4/6", "3/5/7", "T7/CN"];
const createDefaultFormData = () => ({ id: null, class_name: "", course_id: "", instructor_id: "", start_date: "", end_date: "" });
const createDefaultDetailForm = () => ({ id: null, shift: "", days: "", max_students: 20 });
const formData = ref(createDefaultFormData()), detailFormMode = ref("add"), detailFormData = ref(createDefaultDetailForm());
const redirectToLogin = () => { clearAuthSession(); router.push("/login"); };

const loadData = async () => {
  isLoading.value = true; errorMessage.value = "";
  try {
    const [classRes, scheduleRes] = await Promise.all([apiFetch("admin/classes.php"), apiFetch("admin/schedules.php")]);
    if (classRes.status === 401 || classRes.status === 403 || scheduleRes.status === 401 || scheduleRes.status === 403) return redirectToLogin();
    const [classResult, scheduleResult] = await Promise.all([classRes.json(), scheduleRes.json()]);
    if (classResult.status === "success") {
      classes.value = Array.isArray(classResult.data) ? classResult.data : [];
      courses.value = Array.isArray(classResult.courses) ? classResult.courses : [];
      instructors.value = Array.isArray(classResult.instructors) ? classResult.instructors : [];
    } else {
      classes.value = []; courses.value = []; instructors.value = [];
      errorMessage.value = classResult.message || "Không tải được dữ liệu lớp học.";
    }
    if (scheduleResult.status === "success") schedules.value = Array.isArray(scheduleResult.data) ? scheduleResult.data : [];
    else { schedules.value = []; if (!errorMessage.value) errorMessage.value = scheduleResult.message || "Không tải được dữ liệu ca học."; }
  } catch (error) {
    console.error("Lỗi tải dữ liệu lớp học", error); errorMessage.value = "Không thể kết nối tới máy chủ.";
  } finally { isLoading.value = false; }
};

onMounted(() => loadData());

const filteredClasses = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return classes.value;
  return classes.value.filter((item) => [item.class_name, item.course_name, item.instructor_name].some((value) => String(value || "").toLowerCase().includes(query)));
});

const filteredSchedules = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  const source = Array.isArray(schedules.value) ? schedules.value : [];
  if (!query) return source;
  return source.filter((item) => [item.class_name, item.course_name, item.class_detail_name, item.teacher_name, item.study_date, getScheduleStatusLabel(item.status)].some((value) => String(value || "").toLowerCase().includes(query)));
});

const totalSchedulePages = computed(() => Math.max(1, Math.ceil(filteredSchedules.value.length / schedulesPerPage)));
const safeSchedulePage = computed(() => Math.min(schedulePage.value, totalSchedulePages.value));
const paginatedSchedules = computed(() => filteredSchedules.value.slice((safeSchedulePage.value - 1) * schedulesPerPage, safeSchedulePage.value * schedulesPerPage));
const scheduleRangeStart = computed(() => filteredSchedules.value.length === 0 ? 0 : (safeSchedulePage.value - 1) * schedulesPerPage + 1);
const scheduleRangeEnd = computed(() => filteredSchedules.value.length === 0 ? 0 : Math.min(safeSchedulePage.value * schedulesPerPage, filteredSchedules.value.length));
const previewDetailName = computed(() => !activeClassDetails.value || !detailFormData.value.shift || !detailFormData.value.days ? "" : `${activeClassDetails.value.class_name} - ${detailFormData.value.shift} - ${detailFormData.value.days}`);

watch(searchQuery, () => { schedulePage.value = 1; });
watch(totalSchedulePages, (pageCount) => { if (schedulePage.value > pageCount) schedulePage.value = pageCount; });
watch(classes, (items) => {
  if (!activeClassDetails.value) return;
  const latestClass = items.find((item) => Number(item.id) === Number(activeClassDetails.value?.id));
  if (latestClass) activeClassDetails.value = latestClass;
});

const formatDate = (dateStr) => {
  if (!dateStr) return "Chưa cập nhật";
  const date = new Date(`${dateStr}T00:00:00`);
  return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleDateString("vi-VN");
};
const formatDateTime = (dateStr) => {
  if (!dateStr) return "Chưa cập nhật";
  const date = new Date(dateStr);
  return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleString("vi-VN");
};
const formatTime = (timeStr) => String(timeStr || "").slice(0, 5) || "--:--";
const getScheduleStatusLabel = (status) => status === "completed" ? "Đã học" : status === "canceled" ? "Đã hủy" : "Sắp học";
const getScheduleStatusClass = (status) => status === "completed" ? "bg-slate-100 text-slate-600" : status === "canceled" ? "bg-red-100 text-red-600" : "bg-emerald-100 text-emerald-700";
const goToPreviousSchedulePage = () => { if (safeSchedulePage.value > 1) schedulePage.value = safeSchedulePage.value - 1; };
const goToNextSchedulePage = () => { if (safeSchedulePage.value < totalSchedulePages.value) schedulePage.value = safeSchedulePage.value + 1; };

const getCapacityPercent = (currentValue, maxValue) => Math.min((Number(currentValue || 0) / Math.max(Number(maxValue || 0), 1)) * 100, 100);
const getCapacityLabel = (currentValue, maxValue) => {
  const current = Number(currentValue || 0), max = Math.max(Number(maxValue || 0), 1);
  if (current >= max) return "Đã đầy";
  if (current / max >= 0.8) return "Gần đầy";
  return "Còn chỗ";
};
const getCapacityBadgeClass = (currentValue, maxValue) => {
  const current = Number(currentValue || 0), max = Math.max(Number(maxValue || 0), 1);
  if (current >= max) return "bg-red-100 text-red-600";
  if (current / max >= 0.8) return "bg-amber-100 text-amber-700";
  return "bg-emerald-100 text-emerald-700";
};
const getCapacityBarClass = (currentValue, maxValue) => {
  const current = Number(currentValue || 0), max = Math.max(Number(maxValue || 0), 1);
  if (current >= max) return "bg-red-500";
  if (current / max >= 0.8) return "bg-amber-500";
  return "bg-emerald-500";
};
const normalizeCapacityInput = (value) => {
  const normalized = Number(value);
  return Number.isInteger(normalized) && normalized > 0 && normalized <= 500 ? normalized : null;
};

const parseDetailMeta = (detailName) => {
  const segments = String(detailName || "").split(" - ");
  const days = segments.pop() || "";
  const shift = segments.pop() || "";
  return { shift: detailShiftOptions.includes(shift) ? shift : "", days: detailDayOptions.includes(days) ? days : "" };
};
const resetDetailForm = () => { detailFormMode.value = "add"; detailFormData.value = createDefaultDetailForm(); };
const startEditingClassDetail = (detail) => {
  const parsed = parseDetailMeta(detail.detail_name);
  if (!parsed.shift || !parsed.days) return notifyWarning("Không thể tách ca học hiện tại để chỉnh sửa. Hãy kiểm tra lại tên nhóm học.");
  detailFormMode.value = "edit";
  detailFormData.value = { id: detail.id, shift: parsed.shift, days: parsed.days, max_students: Number(detail.max_students || 20) };
};

const openModal = (mode, item = null) => {
  modalMode.value = mode;
  formData.value = mode === "edit" && item ? { id: item.id, class_name: item.class_name ?? "", course_id: item.course_id ?? "", instructor_id: item.instructor_id ?? "", start_date: item.start_date ?? "", end_date: item.end_date ?? "" } : createDefaultFormData();
  isModalOpen.value = true;
};
const closeModal = () => { isModalOpen.value = false; formData.value = createDefaultFormData(); };

const validateForm = () => {
  const className = String(formData.value.class_name || "").trim();
  const courseId = Number(formData.value.course_id);
  const instructorId = formData.value.instructor_id === "" ? null : Number(formData.value.instructor_id);
  const startDate = String(formData.value.start_date || "").trim();
  const endDate = String(formData.value.end_date || "").trim();
  if (className.length < 3 || className.length > 150) return notifyWarning("Tên lớp phải từ 3 đến 150 ký tự."), false;
  if (!Number.isInteger(courseId) || courseId <= 0) return notifyWarning("Vui lòng chọn khóa học hợp lệ."), false;
  if (instructorId !== null && (!Number.isInteger(instructorId) || instructorId <= 0)) return notifyWarning("Giảng viên được chọn không hợp lệ."), false;
  if (!/^\d{4}-\d{2}-\d{2}$/.test(startDate) || Number.isNaN(new Date(`${startDate}T00:00:00`).getTime())) return notifyWarning("Ngày khai giảng không hợp lệ."), false;
  if (!/^\d{4}-\d{2}-\d{2}$/.test(endDate) || Number.isNaN(new Date(`${endDate}T00:00:00`).getTime())) return notifyWarning("Ngày kết thúc không hợp lệ."), false;
  if (startDate > endDate) return notifyWarning("Ngày khai giảng không được lớn hơn ngày kết thúc."), false;
  return true;
};

const validateDetailForm = () => {
  if (!detailFormData.value.shift || !detailFormData.value.days) return notifyWarning("Vui lòng chọn đầy đủ ca học và lịch học."), false;
  if (normalizeCapacityInput(detailFormData.value.max_students) === null) return notifyWarning("Sĩ số nhóm học phải là số nguyên từ 1 đến 500."), false;
  return true;
};

const saveClass = async () => {
  if (!validateForm()) return;
  try {
    const method = modalMode.value === "add" ? "POST" : "PUT";
    const res = await apiFetch("admin/classes.php", { method, body: JSON.stringify(formData.value) });
    if (res.status === 401 || res.status === 403) return redirectToLogin();
    const result = await res.json();
    if (result.status === "success") {
      notifySuccess(result.message || "Lưu lớp học thành công.");
      closeModal();
      await loadData();
    } else notifyError(result.message || "Không thể lưu lớp học.");
  } catch (error) { notifyError("Lỗi kết nối API"); }
};

const deleteClass = async (id) => {
  const confirmed = await openConfirm({ title: "Xóa lớp học", message: "Xóa lớp này sẽ xóa toàn bộ lịch học của lớp đó. Bạn có chắc chắn?", confirmText: "Xóa lớp", cancelText: "Không xóa", tone: "danger" });
  if (!confirmed) return;
  try {
    const res = await apiFetch(`admin/classes.php?id=${id}`, { method: "DELETE" });
    if (res.status === 401 || res.status === 403) return redirectToLogin();
    const result = await res.json();
    if (result.status === "success") { notifySuccess(result.message || "Đã xóa lớp học."); await loadData(); }
    else notifyError(result.message || "Không thể xóa lớp học.");
  } catch (error) { notifyError("Lỗi kết nối API"); }
};

const openClassDetailsModal = async (classItem) => { activeClassDetails.value = classItem; resetDetailForm(); isClassDetailsOpen.value = true; await loadClassDetails(classItem.id); };
const closeClassDetailsModal = () => { isClassDetailsOpen.value = false; activeClassDetails.value = null; classDetails.value = []; resetDetailForm(); };

const loadClassDetails = async (classId) => {
  isLoadingDetails.value = true;
  try {
    const res = await apiFetch(`admin/class_details.php?class_id=${classId}`);
    if (res.status === 401 || res.status === 403) return redirectToLogin();
    const result = await res.json();
    if (result.status === "success") classDetails.value = Array.isArray(result.data) ? result.data : [];
    else notifyError(result.message || "Không tải được danh sách nhóm học.");
  } catch (error) { notifyError("Lỗi tải danh sách nhóm học"); }
  finally { isLoadingDetails.value = false; }
};

const saveClassDetail = async () => {
  if (!activeClassDetails.value || !validateDetailForm()) return;
  const payload = {
    class_id: activeClassDetails.value.id,
    detail_name: `${activeClassDetails.value.class_name} - ${detailFormData.value.shift} - ${detailFormData.value.days}`,
    max_students: normalizeCapacityInput(detailFormData.value.max_students),
  };
  if (detailFormMode.value === "edit") payload.id = detailFormData.value.id;

  try {
    const res = await apiFetch("admin/class_details.php", { method: detailFormMode.value === "add" ? "POST" : "PUT", body: JSON.stringify(payload) });
    const result = await res.json();
    if (result.status === "success") {
      notifySuccess(result.message || (detailFormMode.value === "add" ? "Đã thêm nhóm học." : "Đã cập nhật nhóm học."));
      resetDetailForm();
      await loadClassDetails(activeClassDetails.value.id);
    } else notifyError(result.message || "Không thể lưu nhóm học.");
  } catch (error) { notifyError("Lỗi kết nối API"); }
};

const deleteClassDetail = async (id) => {
  const confirmed = await openConfirm({ title: "Xóa nhóm học", message: "Hành động này sẽ xóa toàn bộ các ca học thuộc nhóm này. Bạn có chắc chắn?", confirmText: "Xóa", cancelText: "Hủy", tone: "danger" });
  if (!confirmed) return;
  try {
    const res = await apiFetch(`admin/class_details.php?id=${id}`, { method: "DELETE" });
    const result = await res.json();
    if (result.status === "success") {
      notifySuccess(result.message || "Đã xóa nhóm học");
      if (Number(detailFormData.value.id) === Number(id)) resetDetailForm();
      await loadClassDetails(activeClassDetails.value.id);
      await loadData();
    } else notifyError(result.message || "Không thể xóa nhóm học.");
  } catch (error) { notifyError("Lỗi kết nối API"); }
};
</script>

<style scoped>
.detail-form > .grid > :nth-child(4) { grid-column: 1 / -1; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
