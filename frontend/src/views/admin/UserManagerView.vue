<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn">
    <div
      class="flex flex-col md:flex-row justify-end items-end md:items-center mb-6 gap-4"
    >
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-user-plus"></i> Thêm tài khoản mới
      </button>
    </div>

    <div
      class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col sm:flex-row gap-4"
    >
      <div class="relative flex-1">
        <i
          class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
        ></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm kiếm theo tên hoặc email..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>
      <select
        v-model="filterRole"
        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] text-slate-700 font-medium min-w-[150px]"
      >
        <option value="all">Tất cả vai trò</option>
        <option value="admin">Quản trị viên (Admin)</option>
        <option value="instructor">Giảng viên</option>
        <option value="student">Học viên</option>
      </select>
    </div>

    <div
      class="bg-white rounded-2xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col"
    >
      <div class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold"
            >
              <th class="px-6 py-4">Người dùng</th>
              <th class="px-6 py-4">Vai trò (Role)</th>
              <th class="px-6 py-4">Ngày tham gia</th>
              <th class="px-6 py-4">Trạng thái</th>
              <th class="px-6 py-4 text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr
              v-for="user in filteredUsers"
              :key="user.id"
              class="hover:bg-slate-50/50 transition-colors group"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-4">
                  <div
                    class="w-10 h-10 rounded-full bg-gradient-to-tr from-emerald-100 to-teal-50 flex items-center justify-center font-bold text-emerald-600 shrink-0"
                  >
                    {{ user.full_name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-bold text-slate-800">{{ user.full_name }}</p>
                    <p class="text-xs text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getRoleBadgeClass(user.role)">
                  {{ getRoleName(user.role) }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-500 font-medium">
                {{ user.created_at }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span
                    class="w-2 h-2 rounded-full"
                    :class="
                      user.status === 'active' ? 'bg-emerald-500' : 'bg-red-500'
                    "
                  ></span>
                  <span
                    class="font-medium"
                    :class="
                      user.status === 'active'
                        ? 'text-emerald-600'
                        : 'text-red-600'
                    "
                  >
                    {{ user.status === "active" ? "Hoạt động" : "Đã khóa" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <div
                  class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <button
                    @click="openModal('edit', user)"
                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors tooltip"
                    title="Chỉnh sửa"
                  >
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button
                    v-if="user.role !== 'admin'"
                    @click="confirmDelete(user)"
                    class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors tooltip"
                    title="Xóa"
                  >
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredUsers.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                <i
                  class="fa-regular fa-folder-open text-4xl mb-3 text-slate-300"
                ></i>
                <p>Không tìm thấy tài khoản nào phù hợp.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div
        class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-sm text-slate-500"
      >
        <span>Tổng: {{ filteredUsers.length }} người dùng</span>
        <div class="flex gap-1">
          <button
            class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-white hover:text-[#16a34a] transition"
          >
            <i class="fa-solid fa-angle-left"></i>
          </button>
          <button
            class="w-8 h-8 rounded-lg bg-[#7AE582] text-slate-900 font-bold flex items-center justify-center"
          >
            1
          </button>
          <button
            class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-white hover:text-[#16a34a] transition"
          >
            <i class="fa-solid fa-angle-right"></i>
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate__animated animate__zoomIn animate__faster"
      >
        <div
          class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50"
        >
          <h3 class="text-lg font-bold text-slate-800">
            {{
              modalMode === "add" ? "Thêm tài khoản mới" : "Chỉnh sửa tài khoản"
            }}
          </h3>
          <button
            @click="closeModal"
            class="text-slate-400 hover:text-red-500 transition"
          >
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveUser" class="p-6 space-y-4">
          <div>
            <label
              class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Họ và tên</label
            >
            <input
              v-model="formData.full_name"
              type="text"
              required
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>

          <div>
            <label
              class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Email</label
            >
            <input
              v-model="formData.email"
              type="email"
              required
              :disabled="modalMode === 'edit'"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] disabled:opacity-60"
            />
          </div>

          <div v-if="modalMode === 'add'">
            <label
              class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Mật khẩu</label
            >
            <input
              v-model="formData.password"
              type="password"
              required
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                >Vai trò</label
              >
              <select
                v-model="formData.role"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              >
                <option value="student">Học viên</option>
                <option value="instructor">Giảng viên</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div>
              <label
                class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                >Trạng thái</label
              >
              <select
                v-model="formData.status"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              >
                <option value="active">Hoạt động</option>
                <option value="blocked">Khóa</option>
              </select>
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
              class="px-5 py-2.5 rounded-xl font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
            >
              {{ modalMode === "add" ? "Tạo tài khoản" : "Lưu thay đổi" }}
            </button>
          </div>
        </form>
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

const router = useRouter();

const users = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const filterRole = ref("all");

const isModalOpen = ref(false);
const modalMode = ref("add");
const formData = ref({
  id: null,
  full_name: "",
  email: "",
  password: "",
  role: "student",
  status: "active",
});

const API_PATH = "admin/users.php";

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const loadUsers = async () => {
  isLoading.value = true;
  try {
    const response = await apiFetch(API_PATH);

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      users.value = result.data;
    }
  } catch (error) {
    console.error("Lỗi khi tải danh sách người dùng:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadUsers();
});

const filteredUsers = computed(() => {
  return users.value.filter((user) => {
    const matchName = user.full_name
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());
    const matchEmail = user.email
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());
    const matchRole =
      filterRole.value === "all" || user.role === filterRole.value;
    return (matchName || matchEmail) && matchRole;
  });
});

const openModal = (mode, user = null) => {
  modalMode.value = mode;
  if (mode === "edit" && user) {
    formData.value = { ...user, password: "" };
  } else {
    formData.value = {
      id: null,
      full_name: "",
      email: "",
      password: "",
      role: "student",
      status: "active",
    };
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const saveUser = async () => {
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
      alert(result.message);
      closeModal();
      loadUsers();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  }
};

const confirmDelete = async (user) => {
  const confirmed = await openConfirm({
    title: "Xóa tài khoản",
    message: `Bạn có chắc chắn muốn xóa tài khoản của ${user.full_name} không?`,
    confirmText: "Xóa tài khoản",
    cancelText: "Không xóa",
    tone: "danger",
  });
  if (!confirmed) return;

  try {
    const response = await apiFetch(`${API_PATH}?id=${user.id}`, {
      method: "DELETE",
    });

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();

    if (result.status === "success") {
      loadUsers();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  }
};

const getRoleName = (role) => {
  const roles = {
    admin: "Quản trị viên",
    instructor: "Giảng viên",
    student: "Học viên",
  };
  return roles[role] || role;
};

const getRoleBadgeClass = (role) => {
  if (role === "admin")
    return "px-2.5 py-1 rounded-md text-[0.65rem] font-bold uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200";
  if (role === "instructor")
    return "px-2.5 py-1 rounded-md text-[0.65rem] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200";
  return "px-2.5 py-1 rounded-md text-[0.65rem] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200";
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
