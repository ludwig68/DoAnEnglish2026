<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn">
    <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4">
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-route"></i> Thêm lộ trình mới
      </button>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col lg:flex-row gap-4">
      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm theo tên lộ trình, mô tả hoặc đối tượng mục tiêu..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center text-slate-500">
        <div class="text-center">
          <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-100 border-t-[#16a34a]"></div>
          <p>Đang tải danh sách lộ trình...</p>
        </div>
      </div>

      <div v-else class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-6 py-4 w-16">ID</th>
              <th class="px-6 py-4 w-1/4">Tên lộ trình</th>
              <th class="px-6 py-4 w-1/5">Đối tượng mục tiêu</th>
              <th class="px-6 py-4">Mô tả</th>
              <th class="px-6 py-4 w-40">Ngày tạo</th>
              <th class="px-6 py-4 text-right w-24">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr
              v-for="path in filteredPaths"
              :key="path.id"
              class="hover:bg-slate-50/50 transition-colors group"
            >
              <td class="px-6 py-4 font-bold text-slate-500">#{{ path.id }}</td>
              <td class="px-6 py-4 font-bold text-slate-800">{{ path.title }}</td>
              <td class="px-6 py-4">
                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-semibold border border-blue-100">
                  {{ path.target_audience }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-500">
                <p class="line-clamp-2" :title="path.description">{{ path.description || "Chưa có mô tả" }}</p>
              </td>
              <td class="px-6 py-4 text-slate-500 font-medium">{{ path.created_at }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button
                    @click="openModal('edit', path)"
                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Chỉnh sửa"
                  >
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button
                    @click="confirmDelete(path)"
                    class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Xóa"
                  >
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredPaths.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                <i class="fa-solid fa-route text-4xl mb-3 text-slate-300"></i>
                <p>Không tìm thấy lộ trình nào phù hợp.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-sm text-slate-500">
        <span>Tổng: {{ filteredPaths.length }} lộ trình</span>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-lg font-bold text-slate-800">
            {{ modalMode === "add" ? "Thêm lộ trình mới" : "Chỉnh sửa lộ trình" }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="savePath" class="p-6 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Tên lộ trình <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.title"
                type="text"
                required
                placeholder="VD: Lộ trình mất gốc 3 tháng"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Đối tượng mục tiêu <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.target_audience"
                type="text"
                required
                placeholder="VD: Người đi làm, sinh viên năm cuối"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Mô tả chi tiết
              </label>
              <textarea
                v-model="formData.description"
                rows="4"
                placeholder="Nhập mô tả cho lộ trình này..."
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              ></textarea>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3 mt-6">
            <button
              type="button"
              @click="closeModal"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-md"
            >
              {{ modalMode === "add" ? "Tạo lộ trình" : "Lưu thay đổi" }}
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

const paths = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");

const isModalOpen = ref(false);
const modalMode = ref("add");
const formData = ref({
  id: null,
  title: "",
  description: "",
  target_audience: "",
});

// Chỉnh lại đường dẫn gọi PHP file, tương tự categories.
const API_PATH = "admin/learning_paths.php";

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const resetForm = () => {
  formData.value = {
    id: null,
    title: "",
    description: "",
    target_audience: "",
  };
};

const loadPaths = async () => {
  isLoading.value = true;

  try {
    const response = await apiFetch(API_PATH);

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      paths.value = result.data || [];
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadPaths();
});

const filteredPaths = computed(() => {
  return paths.value.filter((path) => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return true;

    return (
      (path.title && path.title.toLowerCase().includes(query)) ||
      (path.description && path.description.toLowerCase().includes(query)) ||
      (path.target_audience && path.target_audience.toLowerCase().includes(query))
    );
  });
});

const openModal = (mode, path = null) => {
  modalMode.value = mode;

  if (mode === "edit" && path) {
    formData.value = {
      id: path.id,
      title: path.title ?? "",
      description: path.description ?? "",
      target_audience: path.target_audience ?? "",
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
  const title = formData.value.title.trim();
  const targetAudience = formData.value.target_audience.trim();

  if (title.length < 3 || title.length > 255) {
    alert("Tên lộ trình phải từ 3 đến 255 ký tự.");
    return false;
  }

  if (targetAudience.length < 3 || targetAudience.length > 255) {
    alert("Đối tượng mục tiêu phải từ 3 đến 255 ký tự.");
    return false;
  }

  return true;
};

const savePath = async () => {
  if (!validateForm()) return;

  const method = modalMode.value === "add" ? "POST" : "PUT";

  try {
    const response = await apiFetch(API_PATH, {
      method,
      body: JSON.stringify(formData.value),
    });

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      alert(result.message || "Lưu thành công!");
      closeModal();
      loadPaths();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối tới máy chủ!");
  }
};

const confirmDelete = async (path) => {
  const confirmed = await openConfirm({
    title: "Xóa lộ trình học",
    message: `Bạn có chắc chắn muốn xóa lộ trình "${path.title}" không? Các khóa học thuộc lộ trình này có thể bị mất liên kết.`,
    confirmText: "Xóa lộ trình",
    cancelText: "Không xóa",
    tone: "danger",
  });
  if (!confirmed) return;

  try {
    const response = await apiFetch(`${API_PATH}?id=${path.id}`, {
      method: "DELETE",
    });

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      loadPaths();
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

/* Giới hạn hiển thị mô tả quá dài. */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
