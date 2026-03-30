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
        <i class="fa-solid fa-book-medical"></i> Thêm khóa học mới
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
          placeholder="Tìm theo tên khóa học hoặc trình độ..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>

      <select
        v-model="filterCategory"
        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] text-slate-700 font-medium min-w-[180px]"
      >
        <option value="all">Tất cả danh mục</option>
        <option
          v-for="category in categories"
          :key="category.id"
          :value="String(category.id)"
        >
          {{ category.name }}
        </option>
      </select>

      <select
        v-model="filterFeatured"
        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] text-slate-700 font-medium min-w-[160px]"
      >
        <option value="all">Tất cả khóa học</option>
        <option value="featured">Khóa học nổi bật</option>
        <option value="normal">Khóa học thường</option>
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
          <p>Đang tải danh sách khóa học...</p>
        </div>
      </div>

      <div v-else class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold"
            >
              <th class="px-6 py-4">Khóa học</th>
              <th class="px-6 py-4">Danh mục</th>
              <th class="px-6 py-4">Học phí</th>
              <th class="px-6 py-4">Nổi bật</th>
              <th class="px-6 py-4">Ngày tạo</th>
              <th class="px-6 py-4 text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr
              v-for="course in filteredCourses"
              :key="course.id"
              class="hover:bg-slate-50/50 transition-colors group"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-4">
                  <img
                    :src="course.image_url || fallbackImage"
                    :alt="course.title"
                    class="h-14 w-20 rounded-xl border border-slate-200 object-cover shrink-0"
                  />
                  <div>
                    <p class="font-bold text-slate-800">{{ course.title }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                      {{ course.level || "Chưa có trình độ" }}
                      <span class="mx-1">•</span>
                      {{ course.lesson_count }} bài học
                      <span class="mx-1">•</span>
                      {{ course.class_count }} lớp học
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-600 font-medium">
                {{ course.category_name || "Chưa phân loại" }}
              </td>
              <td class="px-6 py-4 text-slate-600 font-medium">
                {{ formatCurrency(course.fee) }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="
                    course.is_featured
                      ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                      : 'bg-slate-100 text-slate-600 border-slate-200'
                  "
                  class="px-2.5 py-1 rounded-md text-[0.65rem] font-bold uppercase tracking-wider border"
                >
                  {{ course.is_featured ? "Nổi bật" : "Thường" }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-500 font-medium">
                {{ course.created_at }}
              </td>
              <td class="px-6 py-4 text-right">
                <div
                  class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <button
                    @click="openModal('edit', course)"
                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Chỉnh sửa"
                  >
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button
                    @click="confirmDelete(course)"
                    class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Xóa"
                  >
                    <i class="fa-solid fa-trash-can text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredCourses.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                <i
                  class="fa-regular fa-folder-open text-4xl mb-3 text-slate-300"
                ></i>
                <p>Không tìm thấy khóa học nào phù hợp.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-sm text-slate-500"
      >
        <span> Tổng: {{ filteredCourses.length }} khóa học</span>
        <span>{{ featuredCount }} khóa học nổi bật</span>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden animate__animated animate__zoomIn animate__faster"
      >
        <div
          class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50"
        >
          <h3 class="text-lg font-bold text-slate-800">
            {{
              modalMode === "add" ? "Thêm khóa học mới" : "Chỉnh sửa khóa học"
            }}
          </h3>
          <button
            @click="closeModal"
            class="text-slate-400 hover:text-red-500 transition"
          >
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveCourse" class="p-6 space-y-5">
          <div
            class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_320px]"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Tên khóa học</label
                >
                <input
                  v-model="formData.title"
                  type="text"
                  required
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                />
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Danh mục</label
                >
                <select
                  v-model="formData.category_id"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                >
                  <option value="">Chưa chọn</option>
                  <option
                    v-for="category in categories"
                    :key="category.id"
                    :value="String(category.id)"
                  >
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Lộ trình học</label
                >
                <select
                  v-model="formData.path_id"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                >
                  <option value="">Chưa chọn</option>
                  <option
                    v-for="path in learningPaths"
                    :key="path.id"
                    :value="String(path.id)"
                  >
                    {{ path.title }}
                  </option>
                </select>
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Trình độ</label
                >
                <input
                  v-model="formData.level"
                  type="text"
                  placeholder="Vi du: A1, B2, IELTS"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                />
              </div>

              <div>
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Học phí</label
                >
                <input
                  v-model="formData.fee"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                />
              </div>

              <div class="md:col-span-2">
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Mô tả</label
                >
                <textarea
                  v-model="formData.description"
                  rows="5"
                  placeholder="Nhập mô tả khóa học..."
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                ></textarea>
              </div>
            </div>

            <div
              class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 space-y-4"
            >
              <div>
                <p
                  class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                >
                  Ảnh khóa học
                </p>
                <div
                  class="mt-3 inline-flex rounded-full bg-white p-1 shadow-sm"
                >
                  <button
                    type="button"
                    @click="setImageMode('url')"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                    :class="
                      imageMode === 'url'
                        ? 'bg-[#7AE582] text-slate-900'
                        : 'text-slate-600'
                    "
                  >
                    Ảnh URL
                  </button>
                  <button
                    type="button"
                    @click="setImageMode('upload')"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                    :class="
                      imageMode === 'upload'
                        ? 'bg-[#7AE582] text-slate-900'
                        : 'text-slate-600'
                    "
                  >
                    Tải ảnh lên
                  </button>
                </div>
              </div>

              <div v-if="imageMode === 'url'">
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Đường dẫn ảnh</label
                >
                <input
                  v-model="formData.image_url"
                  type="text"
                  placeholder="https://..."
                  class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                />
                <p class="mt-2 text-xs text-slate-500">
                  Bạn có thể dán ảnh từ website khác vào đây.
                </p>
              </div>

              <div v-else class="space-y-3">
                <label
                  class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
                  >Chọn tập tin ảnh</label
                >
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                  class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-[#16a34a]"
                  @change="handleImageFileChange"
                />
                <div class="flex flex-wrap gap-3">
                  <button
                    type="button"
                    @click="uploadSelectedImage"
                    :disabled="!selectedImageFile || isUploadingImage"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    <span v-if="isUploadingImage">Đang tải...</span>
                    <span v-else>Tải ảnh lên</span>
                  </button>
                  <button
                    v-if="selectedImageFile || formData.image_url"
                    type="button"
                    @click="clearImageSelection"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700"
                  >
                    Xóa ảnh
                  </button>
                </div>
                <p class="text-xs text-slate-500">
                  Chấp nhận JPG, JPEG, PNG, WEBP, GIF. Tối đa 5MB.
                </p>
              </div>

              <div
                class="rounded-[1.25rem] border border-slate-200 bg-white p-3"
              >
                <p
                  class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                >
                  Xem trước
                </p>
                <img
                  :src="previewImageUrl"
                  alt="Xem trước ảnh khóa học"
                  class="mt-3 h-40 w-full rounded-xl border border-slate-200 object-cover"
                />
                <p class="mt-2 text-xs text-slate-500 break-all">
                  {{ formData.image_url || "Chưa có ảnh được chọn." }}
                </p>
              </div>
            </div>
          </div>

          <label
            class="inline-flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700"
          >
            <input
              v-model="formData.is_featured"
              type="checkbox"
              class="h-4 w-4 rounded border-slate-300 text-[#16a34a] focus:ring-[#7AE582]"
            />
            Đánh dấu nếu đây là khóa học nổi bật (sẽ được ưu tiên hiển thị trên trang chủ)
          </label>

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
              :disabled="isUploadingImage"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-md disabled:opacity-60"
            >
              {{ modalMode === "add" ? "Tạo khóa học" : "Lưu thay đổi" }}
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

const courses = ref([]);
const categories = ref([]);
const learningPaths = ref([]);
const isLoading = ref(false);
const isUploadingImage = ref(false);
const searchQuery = ref("");
const filterCategory = ref("all");
const filterFeatured = ref("all");

const isModalOpen = ref(false);
const modalMode = ref("add");
const imageMode = ref("url");
const selectedImageFile = ref(null);
const selectedImageObjectUrl = ref("");
const fallbackImage = "https://placehold.co/600x400/e2e8f0/64748b?text=Course";
const formData = ref({
  id: null,
  title: "",
  description: "",
  image_url: "",
  level: "",
  fee: 0,
  category_id: "",
  path_id: "",
  is_featured: false,
});

const API_PATH = "admin/courses.php";
const UPLOAD_API_PATH = "admin/upload_course_image.php";

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const revokeSelectedObjectUrl = () => {
  if (selectedImageObjectUrl.value) {
    URL.revokeObjectURL(selectedImageObjectUrl.value);
    selectedImageObjectUrl.value = "";
  }
};

const resetForm = () => {
  revokeSelectedObjectUrl();
  selectedImageFile.value = null;
  imageMode.value = "url";
  formData.value = {
    id: null,
    title: "",
    description: "",
    image_url: "",
    level: "",
    fee: 0,
    category_id: "",
    path_id: "",
    is_featured: false,
  };
};

const loadCourses = async () => {
  isLoading.value = true;

  try {
    const response = await apiFetch(API_PATH);

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      courses.value = result.data.courses || [];
      categories.value = result.data.categories || [];
      learningPaths.value = result.data.paths || [];
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadCourses();
});

const filteredCourses = computed(() => {
  return courses.value.filter((course) => {
    const query = searchQuery.value.toLowerCase().trim();
    const matchQuery =
      !query ||
      course.title.toLowerCase().includes(query) ||
      String(course.level || "")
        .toLowerCase()
        .includes(query);

    const matchCategory =
      filterCategory.value === "all" ||
      String(course.category_id || "") === filterCategory.value;

    const matchFeatured =
      filterFeatured.value === "all" ||
      (filterFeatured.value === "featured" && course.is_featured) ||
      (filterFeatured.value === "normal" && !course.is_featured);

    return matchQuery && matchCategory && matchFeatured;
  });
});

const featuredCount = computed(
  () => courses.value.filter((course) => course.is_featured).length,
);

const previewImageUrl = computed(() => {
  if (imageMode.value === "upload" && selectedImageObjectUrl.value) {
    return selectedImageObjectUrl.value;
  }

  return formData.value.image_url || fallbackImage;
});

const setImageMode = (mode) => {
  imageMode.value = mode;
};

const openModal = (mode, course = null) => {
  modalMode.value = mode;
  revokeSelectedObjectUrl();
  selectedImageFile.value = null;

  if (mode === "edit" && course) {
    formData.value = {
      id: course.id,
      title: course.title ?? "",
      description: course.description ?? "",
      image_url: course.image_url ?? "",
      level: course.level ?? "",
      fee: course.fee ?? 0,
      category_id: course.category_id ? String(course.category_id) : "",
      path_id: course.path_id ? String(course.path_id) : "",
      is_featured: Boolean(course.is_featured),
    };
    imageMode.value = course.image_url ? "url" : "upload";
  } else {
    resetForm();
  }

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  resetForm();
};

const handleImageFileChange = (event) => {
  const [file] = event.target.files || [];
  revokeSelectedObjectUrl();

  if (!file) {
    selectedImageFile.value = null;
    return;
  }

  selectedImageFile.value = file;
  selectedImageObjectUrl.value = URL.createObjectURL(file);
};

const clearImageSelection = () => {
  revokeSelectedObjectUrl();
  selectedImageFile.value = null;
  formData.value.image_url = "";
};

const uploadSelectedImage = async () => {
  if (!selectedImageFile.value) {
    alert("Vui lòng chọn ảnh trước khi tải lên.");
    return null;
  }

  const body = new FormData();
  body.append("image", selectedImageFile.value);

  isUploadingImage.value = true;

  try {
    const response = await apiFetch(UPLOAD_API_PATH, {
      method: "POST",
      body,
    });

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return null;
    }

    const result = await response.json();
    if (result.status === "success") {
      formData.value.image_url = result.data.url || "";
      selectedImageFile.value = null;
      revokeSelectedObjectUrl();
      imageMode.value = "url";
      return formData.value.image_url;
    }

    alert("Lỗi: " + result.message);
    return null;
  } catch (error) {
    alert("Không thể tải ảnh lên!");
    return null;
  } finally {
    isUploadingImage.value = false;
  }
};

const validateForm = () => {
  const title = formData.value.title.trim();
  const description = formData.value.description.trim();
  const imageUrl = formData.value.image_url.trim();
  const level = formData.value.level.trim();
  const fee = Number(formData.value.fee);

  if (title.length < 3 || title.length > 255) {
    alert("Tên khóa học phải từ 3 đến 255 ký tự.");
    return false;
  }

  if (description && description.length < 10) {
    alert("Mô tả khóa học phải từ 10 ký tự nếu có nhập.");
    return false;
  }

  if (imageUrl && imageUrl.length > 255) {
    alert("Đường dẫn ảnh tối đa 255 ký tự.");
    return false;
  }

  if (level && level.length > 20) {
    alert("Trình độ tối đa 20 ký tự.");
    return false;
  }

  if (Number.isNaN(fee) || fee < 0) {
    alert("Học phí phải là số từ 0 trở lên.");
    return false;
  }

  return true;
};

const ensureImageReady = async () => {
  if (imageMode.value === "upload" && selectedImageFile.value) {
    const uploadedUrl = await uploadSelectedImage();
    return Boolean(uploadedUrl);
  }

  return true;
};

const saveCourse = async () => {
  const imageReady = await ensureImageReady();
  if (!imageReady || !validateForm()) return;

  const method = modalMode.value === "add" ? "POST" : "PUT";

  const payload = {
    ...formData.value,
    category_id: formData.value.category_id || null,
    path_id: formData.value.path_id || null,
    fee: Number(formData.value.fee || 0),
  };

  try {
    const response = await apiFetch(API_PATH, {
      method,
      body: JSON.stringify(payload),
    });

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      alert(result.message);
      closeModal();
      loadCourses();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  }
};

const confirmDelete = async (course) => {
  const confirmed = await openConfirm({
    title: "Xóa khóa học",
    message: `Bạn có chắc chắn muốn xóa khóa học "${course.title}" không?`,
    confirmText: "Xóa khóa học",
    cancelText: "Không xóa",
    tone: "danger",
  });
  if (!confirmed) return;

  try {
    const response = await apiFetch(`${API_PATH}?id=${course.id}`, {
      method: "DELETE",
    });

    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === "success") {
      loadCourses();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  }
};

const formatCurrency = (value) =>
  new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(Number(value || 0));
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


