<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn">
    <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4">
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-plus"></i> Tạo lớp mới
      </button>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6">
      <div class="relative w-full md:w-1/2">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm theo tên lớp, khóa học hoặc giảng viên..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>
    </div>

    <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ errorMessage }}
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center px-6 py-12 text-slate-500">
        Đang tải dữ liệu lớp học...
      </div>
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
              <td class="px-6 py-4">
                <span class="font-black text-slate-800 text-base">{{ item.class_name }}</span>
              </td>
              <td class="px-6 py-4 font-medium text-emerald-600">
                {{ item.course_name || 'Khóa học đã bị xóa' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                    <i class="fa-solid fa-user-tie"></i>
                  </div>
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
                  <button @click="openClassDetailsModal(item)" title="Quản lý Nhóm ca học" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                  </button>
                  <button @click="openModal('edit', item)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button @click="deleteClass(item.id)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredClasses.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                Không tìm thấy lớp học nào.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-chalkboard text-[#16a34a]"></i>
            {{ modalMode === 'add' ? 'Tạo lớp học mới' : 'Chỉnh sửa lớp học' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveClass" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tên lớp <span class="text-red-500">*</span></label>
            <input v-model="formData.class_name" required type="text" placeholder="VD: IELTS Cơ Bản - K01" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Thuộc khóa học <span class="text-red-500">*</span></label>
            <select v-model="formData.course_id" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
              <option value="" disabled>-- Chọn khóa học --</option>
              <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Giảng viên chủ nhiệm</label>
            <select v-model="formData.instructor_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]">
              <option value="">-- Chưa phân công --</option>
              <option v-for="teacher in instructors" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option>
            </select>
          </div>

          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ngày khai giảng <span class="text-red-500">*</span></label>
              <input v-model="formData.start_date" type="date" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
            </div>
            <div class="w-1/2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ngày kết thúc dự kiến <span class="text-red-500">*</span></label>
              <input v-model="formData.end_date" type="date" required class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]" />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
            <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Hủy</button>
            <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-md">
              <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu thông tin
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Quản lý Nhóm Lớp -->
    <div v-if="isClassDetailsOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate__animated animate__zoomIn animate__faster border border-emerald-100">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-emerald-50/50">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-emerald-600"></i>
            Quản lý Nhóm Học - {{ activeClassDetails?.class_name }}
          </h3>
          <button @click="closeClassDetailsModal" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <div class="p-6">
          <form @submit.prevent="addClassDetail" class="flex flex-col gap-3 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div class="flex flex-col md:flex-row gap-2">
              <div class="flex-1 flex items-center bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm shadow-sm gap-2 whitespace-nowrap overflow-x-auto custom-scrollbar">
                <span class="font-bold text-slate-700">{{ activeClassDetails?.class_name }}</span>
                <span class="text-slate-300">-</span>
                <select v-model="newDetailShift" required class="bg-transparent font-medium border-none focus:outline-none text-emerald-700 cursor-pointer min-w-[90px]">
                  <option value="" disabled>Ca học</option>
                  <option value="Sáng">Sáng</option>
                  <option value="Chiều">Chiều</option>
                  <option value="Tối">Tối</option>
                </select>
                <span class="text-slate-300">-</span>
                <select v-model="newDetailDays" required class="bg-transparent font-medium border-none focus:outline-none text-sky-700 cursor-pointer min-w-[90px]">
                  <option value="" disabled>Lịch học</option>
                  <option value="2/4/6">2/4/6</option>
                  <option value="3/5/7">3/5/7</option>
                  <option value="T7/CN">T7/CN</option>
                </select>
              </div>
              <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-sm flex items-center justify-center gap-2 shrink-0">
                <i class="fa-solid fa-plus"></i> Thêm
              </button>
            </div>
          </form>

          <div class="bg-slate-50 rounded-xl border border-slate-100 max-h-64 overflow-y-auto custom-scrollbar">
            <div v-if="isLoadingDetails" class="p-4 text-center text-slate-500 text-sm">Đang tải dữ liệu...</div>
            <div v-else-if="classDetails.length === 0" class="p-4 text-center text-slate-500 text-sm italic">Chưa có nhóm học nào. Hãy tạo nhóm đầu tiên.</div>
            <div v-else class="divide-y divide-slate-100">
              <div v-for="detail in classDetails" :key="detail.id" class="flex justify-between items-center p-3 hover:bg-white transition group/item">
                <span class="font-bold text-slate-700 text-sm"><i class="fa-regular fa-clock text-emerald-500 mr-2"></i> {{ detail.detail_name }}</span>
                <button @click="deleteClassDetail(detail.id)" class="w-8 h-8 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex items-center justify-center opacity-0 group-hover/item:opacity-100">
                  <i class="fa-solid fa-trash-can text-xs"></i>
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
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "../../utils/api";
import { clearAuthSession } from "../../utils/auth";
import { openConfirm } from "../../utils/confirm";
import { notifyError, notifySuccess } from "../../utils/notify";

const router = useRouter();
const classes = ref([]);
const courses = ref([]);
const instructors = ref([]);
const searchQuery = ref("");
const isLoading = ref(false);
const errorMessage = ref("");

const isModalOpen = ref(false);
const modalMode = ref("add");

const isClassDetailsOpen = ref(false);
const activeClassDetails = ref(null);
const classDetails = ref([]);
const newDetailShift = ref("");
const newDetailDays = ref("");
const isLoadingDetails = ref(false);
const createDefaultFormData = () => ({
  id: null,
  class_name: "",
  course_id: "",
  instructor_id: "",
  start_date: "",
  end_date: "",
});
const formData = ref(createDefaultFormData());

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const loadData = async () => {
  isLoading.value = true;
  errorMessage.value = "";

  try {
    const res = await apiFetch("admin/classes.php");
    if (res.status === 401 || res.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await res.json();
    if (result.status === "success") {
      classes.value = Array.isArray(result.data) ? result.data : [];
      courses.value = Array.isArray(result.courses) ? result.courses : [];
      instructors.value = Array.isArray(result.instructors) ? result.instructors : [];
    } else {
      errorMessage.value = result.message || "Không tải được dữ liệu lớp học.";
    }
  } catch (error) {
    console.error("Lỗi tải dữ liệu lớp học", error);
    errorMessage.value = "Không thể kết nối tới máy chủ.";
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => loadData());

const filteredClasses = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  if (!query) return classes.value;

  return classes.value.filter((item) =>
    [item.class_name, item.course_name, item.instructor_name]
      .some((value) => String(value || "").toLowerCase().includes(query)),
  );
});

const formatDate = (dateStr) => {
  if (!dateStr) return "Chưa cập nhật";
  const date = new Date(dateStr);
  return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleDateString("vi-VN");
};

const openModal = (mode, item = null) => {
  modalMode.value = mode;
  formData.value = mode === "edit" && item
    ? {
        id: item.id,
        class_name: item.class_name ?? "",
        course_id: item.course_id ?? "",
        instructor_id: item.instructor_id ?? "",
        start_date: item.start_date ?? "",
        end_date: item.end_date ?? "",
      }
    : createDefaultFormData();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const validateForm = () => {
  const className = String(formData.value.class_name || "").trim();
  const courseId = Number(formData.value.course_id);
  const instructorId = formData.value.instructor_id === "" ? null : Number(formData.value.instructor_id);
  const startDate = String(formData.value.start_date || "").trim();
  const endDate = String(formData.value.end_date || "").trim();

  if (className.length < 3 || className.length > 150) {
    alert("Tên lớp phải từ 3 đến 150 ký tự.");
    return false;
  }

  if (!Number.isInteger(courseId) || courseId <= 0) {
    alert("Vui lòng chọn khóa học hợp lệ.");
    return false;
  }

  if (instructorId !== null && (!Number.isInteger(instructorId) || instructorId <= 0)) {
    alert("Giảng viên được chọn không hợp lệ.");
    return false;
  }

  if (!/^\d{4}-\d{2}-\d{2}$/.test(startDate) || Number.isNaN(new Date(`${startDate}T00:00:00`).getTime())) {
    alert("Ngày khai giảng không hợp lệ.");
    return false;
  }

  if (!/^\d{4}-\d{2}-\d{2}$/.test(endDate) || Number.isNaN(new Date(`${endDate}T00:00:00`).getTime())) {
    alert("Ngày kết thúc không hợp lệ.");
    return false;
  }

  if (startDate > endDate) {
    alert("Ngày khai giảng không được lớn hơn ngày kết thúc.");
    return false;
  }

  return true;
};

const saveClass = async () => {
  if (!validateForm()) return;

  try {
    const method = modalMode.value === "add" ? "POST" : "PUT";
    const res = await apiFetch("admin/classes.php", {
      method,
      body: JSON.stringify(formData.value),
    });

    if (res.status === 401 || res.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await res.json();
    if (result.status === "success") {
      closeModal();
      loadData();
    } else {
      alert(result.message);
    }
  } catch (error) {
    alert("Lỗi kết nối API");
  }
};

const deleteClass = async (id) => {
  const confirmed = await openConfirm({
    title: "Xóa lớp học",
    message: "Xóa lớp này sẽ xóa toàn bộ lịch học của lớp đó. Bạn có chắc chắn?",
    confirmText: "Xóa lớp",
    cancelText: "Không xóa",
    tone: "danger",
  });
  if (!confirmed) return;

  try {
    const res = await apiFetch(`admin/classes.php?id=${id}`, { method: "DELETE" });
    if (res.status === 401 || res.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await res.json();
    if (result.status === "success") {
      loadData();
    } else {
      alert(result.message);
    }
  } catch (error) {
    alert("Lỗi kết nối API");
  }
};

const openClassDetailsModal = async (classItem) => {
  activeClassDetails.value = classItem;
  isClassDetailsOpen.value = true;
  newDetailName.value = "";
  await loadClassDetails(classItem.id);
};

const closeClassDetailsModal = () => {
  isClassDetailsOpen.value = false;
  activeClassDetails.value = null;
};

const loadClassDetails = async (classId) => {
  isLoadingDetails.value = true;
  try {
    const res = await apiFetch(`admin/class_details.php?class_id=${classId}`);
    if (res.status === 401 || res.status === 403) return redirectToLogin();
    const result = await res.json();
    if (result.status === "success") {
      classDetails.value = result.data || [];
    } else {
      notifyError(result.message);
    }
  } catch (error) {
    notifyError("Lỗi tải danh sách nhóm học");
  } finally {
    isLoadingDetails.value = false;
  }
};

const addClassDetail = async () => {
  if (!newDetailShift.value || !newDetailDays.value) {
    return notifyError("Vui lòng chọn đầy đủ Ca học và Lịch học.");
  }
  const name = `${activeClassDetails.value.class_name} - ${newDetailShift.value} - ${newDetailDays.value}`;

  try {
    const res = await apiFetch("admin/class_details.php", {
      method: "POST",
      body: JSON.stringify({ class_id: activeClassDetails.value.id, detail_name: name })
    });
    const result = await res.json();
    if (result.status === "success") {
      newDetailShift.value = "";
      newDetailDays.value = "";
      notifySuccess("Đã thêm nhóm lịch học");
      await loadClassDetails(activeClassDetails.value.id);
    } else {
      notifyError(result.message);
    }
  } catch (error) {
    notifyError("Lỗi kết nối API");
  }
};

const deleteClassDetail = async (id) => {
  const confirmed = await openConfirm({
    title: "Xóa nhóm học",
    message: "Hành động này sẽ xóa toàn bộ các ca học thuộc nhóm này. Bạn có chắc chắn?",
    confirmText: "Xóa",
    cancelText: "Hủy",
    tone: "danger"
  });
  if (!confirmed) return;

  try {
    const res = await apiFetch(`admin/class_details.php?id=${id}`, { method: "DELETE" });
    const result = await res.json();
    if (result.status === "success") {
      notifySuccess("Đã xóa nhóm học");
      await loadClassDetails(activeClassDetails.value.id);
    } else {
      notifyError(result.message);
    }
  } catch (error) {
    notifyError("Lỗi kết nối API");
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
</style>


