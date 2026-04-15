<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn">
    <div
      class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4"
    >
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-plus"></i> Thêm tư vấn mới
      </button>
    </div>

    <div
      class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex items-center gap-4"
    >
      <div class="relative flex-1">
        <i
          class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
        ></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm kiếm nhanh khách hàng theo tên, số điện thoại..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>
    </div>

    <div
      v-if="isLoading"
      class="flex-1 flex items-center justify-center text-slate-500"
    >
      <div class="text-center">
        <div
          class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-100 border-t-[#16a34a]"
        ></div>
        <p>Đang tải dữ liệu CRM...</p>
      </div>
    </div>

    <div
      v-else
      class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-6 overflow-hidden"
    >
      <div
        class="flex flex-col bg-red-50/50 rounded-2xl border border-red-100 overflow-hidden"
      >
        <div
          class="p-4 border-b border-red-100 flex justify-between items-center bg-red-100/50"
        >
          <h2 class="font-bold text-red-800 flex items-center gap-2">
            <i class="fa-solid fa-inbox text-red-500"></i> Chờ xử lý
          </h2>
          <span
            class="bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-full"
            >{{ pendingCount }}</span
          >
        </div>
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar space-y-4">
          <div
            v-if="pendingCount === 0"
            class="text-center text-sm text-red-400 py-8 border-2 border-dashed border-red-200 rounded-xl"
          >
            Không có yêu cầu mới
          </div>
          <div
            v-for="item in pendingList"
            :key="item.id"
            @click="openModal('edit', item)"
            class="bg-white p-4 rounded-xl shadow-sm cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all group"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="font-bold text-slate-800">{{ item.full_name }}</h3>
              <span
                class="text-[0.65rem] font-bold text-red-500 bg-red-50 px-2 py-1 rounded-md"
                >{{ timeAgo(item.created_at) }}</span
              >
            </div>
            <p class="text-xs font-semibold text-slate-600 mb-1">
              <i class="fa-solid fa-phone mr-1 text-slate-400"></i>
              {{ item.phone }}
            </p>
            <p
              class="text-xs text-slate-500 line-clamp-2 mt-2 bg-slate-50 p-2 rounded-lg italic border border-slate-100"
            >
              "{{ item.note || "Không có lời nhắn" }}"
            </p>
          </div>
          <div
            v-if="pendingCount > displayLimit"
            class="text-center text-xs text-red-500 font-bold"
          >
            +{{ pendingCount - displayLimit }} thẻ khác (đang ẩn)
          </div>
        </div>
      </div>

      <div
        class="flex flex-col bg-blue-50/50 rounded-2xl border border-blue-100 overflow-hidden"
      >
        <div
          class="p-4 border-b border-blue-100 flex justify-between items-center bg-blue-100/50"
        >
          <h2 class="font-bold text-blue-800 flex items-center gap-2">
            <i class="fa-solid fa-phone-volume text-blue-500"></i> Đang chăm sóc
          </h2>
          <span
            class="bg-blue-500 text-white text-xs font-black px-2.5 py-1 rounded-full"
            >{{ contactedCount }}</span
          >
        </div>
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar space-y-4">
          <div
            v-if="contactedCount === 0"
            class="text-center text-sm text-blue-400 py-8 border-2 border-dashed border-blue-200 rounded-xl"
          >
            Trống
          </div>
          <div
            v-for="item in contactedList"
            :key="item.id"
            @click="openModal('edit', item)"
            class="bg-white p-4 rounded-xl shadow-sm cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all group"
          >
            <div class="flex justify-between items-start mb-2">
              <h3 class="font-bold text-slate-800">{{ item.full_name }}</h3>
            </div>
            <p class="text-xs font-semibold text-slate-600 mb-1">
              <i class="fa-solid fa-phone mr-1 text-slate-400"></i>
              {{ item.phone }}
            </p>
            <p
              class="text-xs text-slate-500 line-clamp-2 mt-2 bg-slate-50 p-2 rounded-lg border border-slate-100"
            >
              <i class="fa-solid fa-pen-clip mr-1"></i>
              {{ item.note || "Chưa ghi chú" }}
            </p>
          </div>
          <div
            v-if="contactedCount > displayLimit"
            class="text-center text-xs text-blue-500 font-bold"
          >
            +{{ contactedCount - displayLimit }} thẻ khác (đang ẩn)
          </div>
        </div>
      </div>

      <div
        class="flex flex-col bg-emerald-50/50 rounded-2xl border border-emerald-100 overflow-hidden"
      >
        <div
          class="p-4 border-b border-emerald-100 flex justify-between items-center bg-emerald-100/50"
        >
          <h2 class="font-bold text-emerald-800 flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-500"></i> Đã chốt /
            Xong
          </h2>
          <span
            class="bg-emerald-500 text-white text-xs font-black px-2.5 py-1 rounded-full"
            >{{ resolvedCount }}</span
          >
        </div>
        <div
          class="p-4 flex-1 overflow-y-auto custom-scrollbar space-y-4 opacity-70 hover:opacity-100 transition-opacity"
        >
          <div
            v-if="resolvedCount === 0"
            class="text-center text-sm text-emerald-400 py-8 border-2 border-dashed border-emerald-200 rounded-xl"
          >
            Trống
          </div>
          <div
            v-for="item in resolvedList"
            :key="item.id"
            @click="openModal('edit', item)"
            class="bg-white p-4 rounded-xl shadow-sm cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all group relative"
          >
            <div class="flex justify-between items-start mb-1">
              <h3
                class="font-bold text-slate-800 line-through decoration-slate-300"
              >
                {{ item.full_name }}
              </h3>
              <button
                @click.stop="confirmDelete(item)"
                class="text-slate-300 hover:text-red-500 transition-colors"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
            <p class="text-xs text-slate-500">
              {{ formatDate(item.created_at) }}
            </p>
          </div>
          <div
            v-if="resolvedCount > displayLimit"
            class="text-center text-xs text-emerald-500 font-bold"
          >
            +{{ resolvedCount - displayLimit }} thẻ khác (đang ẩn)
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div
        class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl overflow-hidden animate__animated animate__zoomIn animate__faster border border-slate-100"
      >
        <div
          class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-white"
        >
          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 rounded-full bg-emerald-100 text-[#16a34a] flex items-center justify-center font-black text-lg"
            >
              {{
                formData.full_name
                  ? formData.full_name.charAt(0).toUpperCase()
                  : "?"
              }}
            </div>
            <div>
              <h3 class="text-lg font-black text-slate-800 leading-tight">
                {{
                  modalMode === "add"
                    ? "Thêm mới khách hàng"
                    : formData.full_name
                }}
              </h3>
              <p
                v-if="modalMode === 'edit'"
                class="text-xs text-slate-500 font-medium"
              >
                ID: #{{ formData.id }} | Đã gửi:
                {{ formatDate(formData.created_at) }}
              </p>
            </div>
          </div>
          <button
            @click="closeModal"
            class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-red-100 hover:text-red-500 transition"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="saveConsultation" class="p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label
                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                >Họ và tên <span class="text-red-500">*</span></label
              >
              <div class="relative">
                <i
                  class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                ></i>
                <input
                  v-model="formData.full_name"
                  type="text"
                  required
                  class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-colors"
                />
              </div>
            </div>

            <div>
              <label
                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                >Số điện thoại <span class="text-red-500">*</span></label
              >
              <div class="relative">
                <i
                  class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                ></i>
                <input
                  v-model="formData.phone"
                  type="text"
                  required
                  class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-bold text-emerald-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-colors"
                />
              </div>
            </div>

            <div class="md:col-span-2">
              <label
                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                >Email</label
              >
              <div class="relative">
                <i
                  class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                ></i>
                <input
                  v-model="formData.email"
                  type="email"
                  placeholder="Trống"
                  class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-colors"
                />
              </div>
            </div>

            <div class="md:col-span-2">
              <label
                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                >Trạng thái phiếu <span class="text-red-500">*</span></label
              >
              <div
                class="flex gap-3 bg-slate-50 p-2 rounded-xl border border-slate-200"
              >
                <label class="flex-1 cursor-pointer">
                  <input
                    type="radio"
                    v-model="formData.status"
                    value="pending"
                    class="peer sr-only"
                  />
                  <div
                    class="text-center py-2 rounded-lg text-sm font-bold text-slate-500 peer-checked:bg-orange-100 peer-checked:text-orange-700 transition-all border border-transparent peer-checked:border-orange-200"
                  >
                    Chờ xử lý
                  </div>
                </label>
                <label class="flex-1 cursor-pointer">
                  <input
                    type="radio"
                    v-model="formData.status"
                    value="contacted"
                    class="peer sr-only"
                  />
                  <div
                    class="text-center py-2 rounded-lg text-sm font-bold text-slate-500 peer-checked:bg-blue-100 peer-checked:text-blue-700 transition-all border border-transparent peer-checked:border-blue-200"
                  >
                    Đang liên hệ
                  </div>
                </label>
                <label class="flex-1 cursor-pointer">
                  <input
                    type="radio"
                    v-model="formData.status"
                    value="resolved"
                    class="peer sr-only"
                  />
                  <div
                    class="text-center py-2 rounded-lg text-sm font-bold text-slate-500 peer-checked:bg-emerald-100 peer-checked:text-emerald-700 transition-all border border-transparent peer-checked:border-emerald-200"
                  >
                    Đã chốt
                  </div>
                </label>
              </div>
            </div>

            <div class="md:col-span-2">
              <label
                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"
                >Ghi chú chăm sóc khách hàng</label
              >
              <textarea
                v-model="formData.note"
                rows="4"
                placeholder="Khách muốn hỏi về học phí lộ trình IELTS..."
                class="w-full px-4 py-3 rounded-xl bg-yellow-50/50 border border-yellow-200 text-sm focus:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-colors"
              ></textarea>
            </div>
          </div>

          <div
            class="pt-4 flex justify-between items-center mt-6 border-t border-slate-100"
          >
            <button
              v-if="modalMode === 'edit'"
              type="button"
              @click="confirmDelete(formData)"
              class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-2"
            >
              <i class="fa-solid fa-trash"></i> Xóa phiếu này
            </button>
            <div v-else></div>

            <div class="flex gap-3">
              <button
                type="button"
                @click="closeModal"
                class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
              >
                Đóng
              </button>
              <button
                type="submit"
                class="px-6 py-3 rounded-xl font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
              >
                <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu thông tin
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "../../utils/api";
import { clearAuthSession } from "../../utils/auth";
import { openConfirm } from "../../utils/confirm";

const router = useRouter();

const consultations = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");

const isModalOpen = ref(false);
const modalMode = ref("add");
const formData = ref({
  id: null,
  full_name: "",
  phone: "",
  email: "",
  note: "",
  status: "pending",
  created_at: "",
});

const API_PATH = "admin/consultations.php";

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const resetForm = () => {
  formData.value = {
    id: null,
    full_name: "",
    phone: "",
    email: "",
    note: "",
    status: "pending",
    created_at: "",
  };
};

const loadConsultations = async () => {
  isLoading.value = true;
  try {
    const response = await apiFetch(API_PATH);
    if (response.status === 401 || response.status === 403) {
      return redirectToLogin();
    }

    const result = await response.json();
    if (result.status === "success") {
      consultations.value = result.data || [];
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => loadConsultations());

// Lọc dữ liệu theo từ khóa tìm kiếm.
const filteredList = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  if (!query) return consultations.value;

  return consultations.value.filter(
    (item) =>
      (item.full_name && item.full_name.toLowerCase().includes(query)) ||
      (item.phone && item.phone.toLowerCase().includes(query)) ||
      (item.note && item.note.toLowerCase().includes(query)),
  );
});

// Tổng số lượng thực tế theo từng trạng thái.
const pendingCount = computed(
  () => filteredList.value.filter((c) => c.status === "pending").length,
);
const contactedCount = computed(
  () => filteredList.value.filter((c) => c.status === "contacted").length,
);
const resolvedCount = computed(
  () => filteredList.value.filter((c) => c.status === "resolved").length,
);

// Danh sách hiển thị trên màn hình, giới hạn để tránh lag.
const displayLimit = 10;
const pendingList = computed(() =>
  filteredList.value
    .filter((c) => c.status === "pending")
    .slice(0, displayLimit),
);
const contactedList = computed(() =>
  filteredList.value
    .filter((c) => c.status === "contacted")
    .slice(0, displayLimit),
);
const resolvedList = computed(() =>
  filteredList.value
    .filter((c) => c.status === "resolved")
    .slice(0, displayLimit),
);

// Tiện ích hiển thị.
const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return new Intl.DateTimeFormat("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date);
};

const timeAgo = (dateString) => {
  if (!dateString) return "Mới";
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + " năm trước";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + " tháng trước";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + " ngày trước";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + " giờ trước";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + " phút trước";
  return "Vừa xong";
};

const openModal = (mode, item = null) => {
  modalMode.value = mode;
  if (mode === "edit" && item) {
    formData.value = { ...item };
  } else {
    resetForm();
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  resetForm();
};

const validateForm = () => {
  if (formData.value.full_name.trim().length < 2) {
    alert("Vui lòng nhập họ tên hợp lệ.");
    return false;
  }
  if (formData.value.phone.trim().length < 8) {
    alert("Vui lòng nhập số điện thoại hợp lệ.");
    return false;
  }
  return true;
};

const saveConsultation = async () => {
  if (!validateForm()) return;
  const method = modalMode.value === "add" ? "POST" : "PUT";
  try {
    const response = await apiFetch(API_PATH, {
      method,
      body: JSON.stringify(formData.value),
    });
    if (response.status === 401 || response.status === 403) {
      return redirectToLogin();
    }
    const result = await response.json();
    if (result.status === "success") {
      closeModal();
      loadConsultations();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  }
};

const confirmDelete = async (item) => {
  const confirmed = await openConfirm({
    title: "Xóa yêu cầu tư vấn",
    message: `Bạn có chắc chắn muốn xóa dữ liệu tư vấn của "${item.full_name}" không?`,
    confirmText: "Xóa dữ liệu",
    cancelText: "Không xóa",
    tone: "danger",
  });
  if (!confirmed) return;

  try {
    const response = await apiFetch(`${API_PATH}?id=${item.id}`, {
      method: "DELETE",
    });
    if (response.status === 401 || response.status === 403) {
      return redirectToLogin();
    }
    const result = await response.json();
    if (result.status === "success") {
      if (isModalOpen.value) closeModal();
      loadConsultations();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
