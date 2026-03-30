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
        <i class="fa-solid fa-plus"></i> Thêm liên hệ thủ công
      </button>
    </div>

    <div
      class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col lg:flex-row gap-4"
    >
      <div class="relative flex-1">
        <i
          class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
        ></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm theo tên hoặc email người gửi..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>

      <select
        v-model="filterStatus"
        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] text-slate-700 font-medium min-w-[200px]"
      >
        <option value="all">Tất cả thư liên hệ</option>
        <option value="pending">Chưa phản hồi</option>
        <option value="replied">Đã phản hồi</option>
      </select>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col"
    >
      <div
        v-if="isLoading"
        class="flex-1 flex items-center justify-center text-slate-500"
      >
        <div class="text-center">
          <div
            class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-100 border-t-[#16a34a]"
          ></div>
          <p>Đang tải danh sách liên hệ...</p>
        </div>
      </div>

      <div v-else class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold"
            >
              <th class="px-6 py-4 w-16">ID</th>
              <th class="px-6 py-4 w-1/4">Khách hàng</th>
              <th class="px-6 py-4">Nội dung tin nhắn</th>
              <th class="px-6 py-4 w-36">Trạng thái</th>
              <th class="px-6 py-4 w-40">Ngày gửi</th>
              <th class="px-6 py-4 text-right w-24">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr
              v-for="item in filteredContacts"
              :key="item.id"
              class="hover:bg-slate-50/50 transition-colors group"
            >
              <td class="px-6 py-4 font-bold text-slate-500">#{{ item.id }}</td>
              <td class="px-6 py-4">
                <p class="font-bold text-slate-800">{{ item.full_name }}</p>
                <div class="flex items-center text-xs text-slate-500 mt-1">
                  <i class="fa-solid fa-envelope mr-1"></i> {{ item.email }}
                </div>
              </td>
              <td class="px-6 py-4 text-slate-500">
                <p class="line-clamp-2 italic" :title="item.message">
                  "{{ item.message || "Không có nội dung" }}"
                </p>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="
                    item.is_replied == 1
                      ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                      : 'bg-red-100 text-red-700 border-red-200'
                  "
                  class="px-2.5 py-1 rounded-md text-[8.5px] font-bold uppercase tracking-wider border inline-flex items-center gap-1"
                >
                  <i v-if="item.is_replied == 1" class="fa-solid fa-check"></i>
                  <i v-else class="fa-solid fa-clock"></i>
                  {{ item.is_replied == 1 ? "Đã phản hồi" : "Chưa xử lý" }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-500 font-medium">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="px-6 py-4 text-right">
                <div
                  class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <button
                    @click="openModal('edit', item)"
                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Xem chi tiết / Trả lời"
                  >
                    <i class="fa-solid fa-eye text-xs"></i>
                  </button>
                  <button
                    @click="confirmDelete(item)"
                    class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Xóa"
                  >
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredContacts.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                <i
                  class="fa-regular fa-comments text-4xl mb-3 text-slate-300"
                ></i>
                <p>Không tìm thấy thư liên hệ nào phù hợp.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-sm text-slate-500"
      >
        <span>Tổng cộng: {{ filteredContacts.length }} thư liên hệ</span>
        <span v-if="pendingCount > 0" class="font-bold text-red-500">
          <i class="fa-solid fa-bell mr-1 animate-pulse"></i> Cần xử lý:
          {{ pendingCount }}
        </span>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden animate__animated animate__zoomIn animate__faster"
      >
        <div
          class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50"
        >
          <h3 class="text-lg font-bold text-slate-800">
            {{
              modalMode === "add" ? "Thêm liên hệ mới" : "Chi tiết thư liên hệ"
            }}
          </h3>
          <button
            @click="closeModal"
            class="text-slate-400 hover:text-red-500 transition"
          >
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveContact" class="p-6 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                >Họ và tên <span class="text-red-500">*</span></label
              >
              <input
                v-model="formData.full_name"
                type="text"
                required
                :readonly="modalMode === 'edit'"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] read-only:bg-slate-100 read-only:text-slate-500"
              />
            </div>

            <div>
              <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                >Email <span class="text-red-500">*</span></label
              >
              <div class="flex gap-2">
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  :readonly="modalMode === 'edit'"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] read-only:bg-slate-100 read-only:text-slate-500"
                />

                <a
                  v-if="modalMode === 'edit' && formData.email"
                  :href="getMailLink()"
                  target="_blank"
                  class="px-4 py-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition font-bold text-sm shrink-0 flex items-center"
                  title="Soạn thư phản hồi qua Gmail"
                >
                  <i class="fa-solid fa-paper-plane mr-1"></i>
                </a>
              </div>
            </div>

            <div class="md:col-span-2">
              <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                >Nội dung tin nhắn <span class="text-red-500">*</span></label
              >
              <textarea
                v-model="formData.message"
                rows="5"
                required
                :readonly="modalMode === 'edit'"
                placeholder="Nhập nội dung thư..."
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] read-only:bg-blue-50/50 read-only:italic read-only:border-blue-100 read-only:text-slate-700"
              ></textarea>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100">
            <label
              class="inline-flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 w-full cursor-pointer hover:bg-slate-100 transition border border-slate-200"
            >
              <input
                v-model="formData.is_replied"
                type="checkbox"
                class="h-5 w-5 rounded border-slate-300 text-[#16a34a] focus:ring-[#7AE582]"
              />
              <div>
                <p
                  class="font-bold"
                  :class="
                    formData.is_replied ? 'text-emerald-600' : 'text-slate-700'
                  "
                >
                  {{
                    formData.is_replied
                      ? "Đã xử lý và phản hồi khách hàng"
                      : "Đánh dấu là đã phản hồi"
                  }}
                </p>
                <p class="text-xs text-slate-500 font-normal">
                  Chọn vào đây sau khi bạn đã gửi email trả lời khách hàng
                  thành công.
                </p>
              </div>
            </label>
          </div>

          <div class="pt-2 flex justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
            >
              Đóng
            </button>
            <button
              type="submit"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-md"
            >
              <i class="fa-solid fa-floppy-disk mr-1"></i> Lưu trạng thái
            </button>
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

const contacts = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const filterStatus = ref("all");

const isModalOpen = ref(false);
const modalMode = ref("add");
const formData = ref({
  id: null,
  full_name: "",
  email: "",
  message: "",
  is_replied: false, // Map boolean cho checkbox (true = 1, false = 0)
});

const API_PATH = "admin/contacts.php";

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const resetForm = () => {
  formData.value = {
    id: null,
    full_name: "",
    email: "",
    message: "",
    is_replied: false,
  };
};

const loadContacts = async () => {
  isLoading.value = true;
  try {
    const response = await apiFetch(API_PATH);
    if (response.status === 401 || response.status === 403) {
      return redirectToLogin();
    }

    const result = await response.json();
    if (result.status === "success") {
      contacts.value = result.data || [];
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => loadContacts());

const filteredContacts = computed(() => {
  return contacts.value.filter((item) => {
    const query = searchQuery.value.toLowerCase().trim();
    const matchQuery =
      !query ||
      (item.full_name && item.full_name.toLowerCase().includes(query)) ||
      (item.email && item.email.toLowerCase().includes(query));

    const matchStatus =
      filterStatus.value === "all" ||
      (filterStatus.value === "pending" && item.is_replied == 0) ||
      (filterStatus.value === "replied" && item.is_replied == 1);

    return matchQuery && matchStatus;
  });
});

const pendingCount = computed(
  () => contacts.value.filter((item) => item.is_replied == 0).length,
);

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

// Tự động tạo link Gmail với tiêu đề và nội dung soạn sẵn.
const getMailLink = () => {
  if (!formData.value.email) return "#";

  const subject = "Trung tâm Tiếng Anh Learning English - Phản hồi liên hệ";

  const body = `Chào ${formData.value.full_name},

Cảm ơn bạn đã liên hệ với Trung tâm Tiếng Anh Learning English.
Chúng tôi xin phản hồi về nội dung bạn đã gửi:
"${formData.value.message}"

[... Vui lòng nhập câu trả lời của bạn tại đây ...]

Trân trọng,
Đội ngũ CSKH Learning English.`;

  const encodedSubject = encodeURIComponent(subject);
  const encodedBody = encodeURIComponent(body);

  return `https://mail.google.com/mail/?view=cm&fs=1&to=${formData.value.email}&su=${encodedSubject}&body=${encodedBody}`;
};

const openModal = (mode, item = null) => {
  modalMode.value = mode;

  if (mode === "edit" && item) {
    formData.value = {
      id: item.id,
      full_name: item.full_name ?? "",
      email: item.email ?? "",
      message: item.message ?? "",
      is_replied: item.is_replied == 1,
    };
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
  if (!formData.value.email.includes("@")) {
    alert("Vui lòng nhập email hợp lệ.");
    return false;
  }
  return true;
};

const saveContact = async () => {
  if (!validateForm()) return;

  const method = modalMode.value === "add" ? "POST" : "PUT";

  // Chuẩn bị payload, convert boolean ngược lại thành 1 hoặc 0 cho CSDL.
  const payload = {
    ...formData.value,
    is_replied: formData.value.is_replied ? 1 : 0,
  };

  try {
    const response = await apiFetch(API_PATH, {
      method,
      body: JSON.stringify(payload),
    });

    if (response.status === 401 || response.status === 403) {
      return redirectToLogin();
    }

    const result = await response.json();
    if (result.status === "success") {
      closeModal();
      loadContacts();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  }
};

const confirmDelete = async (item) => {
  const confirmed = await openConfirm({
    title: "Xóa thư liên hệ",
    message: `Bạn có chắc chắn muốn xóa thư liên hệ của "${item.full_name}" không?`,
    confirmText: "Xóa thư",
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
      loadContacts();
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
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc;
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
