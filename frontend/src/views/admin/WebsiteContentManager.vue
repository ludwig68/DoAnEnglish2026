<template>
  <div class="h-full flex flex-col p-6 animate__animated animate__fadeIn">
    <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6 gap-4">
      <button
        @click="openModal('add')"
        class="px-5 py-2.5 rounded-xl text-white font-bold flex items-center gap-2 shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
        style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
      >
        <i class="fa-solid fa-plus"></i> Thêm nội dung
      </button>
    </div>

    <div class="grid gap-4 xl:grid-cols-3 mb-6">
      <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 xl:col-span-1">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Hướng dẫn</p>
        <div class="mt-4 space-y-3 text-sm leading-relaxed text-slate-600">
          <p><strong>1.</strong> Chọn đúng vị trí nội dung như "Ảnh banner trang chủ" hoặc "Tiêu đề phần giới thiệu".</p>
          <p><strong>2.</strong> Nếu là ảnh, chỉ cần dán đường dẫn ảnh vào ô nhập liệu.</p>
          <p><strong>3.</strong> Bấm "Mở trang chủ" hoặc "Mở trang giới thiệu" để kiểm tra kết quả.</p>
        </div>
      </section>

      <section
        v-for="group in pageContentGroups"
        :key="group.key"
        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">{{ group.badge }}</p>
            <h2 class="mt-2 text-lg font-black text-slate-800">{{ group.title }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ group.description }}</p>
          </div>
          <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
            {{ countGroupItems(group.prefix) }}/{{ group.fields.length }} mục
          </span>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <button
            type="button"
            @click="applySectionFilter(group.prefix)"
            class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-700 transition"
          >
            Lọc {{ group.title }}
          </button>
          <router-link
            :to="group.previewRoute"
            target="_blank"
            class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-bold hover:bg-emerald-100 transition"
          >
            Mở {{ group.previewLabel }}
          </router-link>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <span
            v-for="field in group.fields"
            :key="field"
            class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600"
          >
            {{ getContentLabel(field) }}
          </span>
        </div>
      </section>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col lg:flex-row gap-4">
      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm theo tên vị trí, mã kỹ thuật hoặc nội dung..."
          class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] transition-all"
        />
      </div>

      <select
        v-model="filterType"
        class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] text-slate-700 font-medium min-w-[220px]"
      >
        <option value="all">Tất cả loại nội dung</option>
        <option value="text">Văn bản thường</option>
        <option value="html">Văn bản có định dạng HTML</option>
        <option value="image">Hình ảnh</option>
        <option value="link">Liên kết</option>
      </select>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex-1 overflow-hidden flex flex-col">
      <div v-if="isLoading" class="flex-1 flex items-center justify-center text-slate-500">
        <div class="text-center">
          <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-100 border-t-[#16a34a]"></div>
          <p>Đang tải cấu hình website...</p>
        </div>
      </div>

      <div v-else class="overflow-x-auto flex-1 custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
              <th class="px-6 py-4 w-16">ID</th>
              <th class="px-6 py-4 min-w-[280px]">Vị trí nội dung</th>
              <th class="px-6 py-4 w-40">Loại</th>
              <th class="px-6 py-4">Nội dung hiện tại</th>
              <th class="px-6 py-4 w-40">Cập nhật lúc</th>
              <th class="px-6 py-4 text-right w-24">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
            <tr
              v-for="item in filteredContents"
              :key="item.id"
              class="hover:bg-slate-50/50 transition-colors group"
            >
              <td class="px-6 py-4 font-bold text-slate-500">#{{ item.id }}</td>
              <td class="px-6 py-4">
                <p class="font-bold text-slate-800">{{ getContentLabel(item.section_key) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ getContentLocation(item.section_key) }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <span class="font-mono text-[11px] bg-slate-100 px-2 py-1 rounded text-slate-600">{{ item.section_key }}</span>
                  <span
                    v-if="isAdvancedSection(item.section_key)"
                    class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-amber-700"
                  >
                    nâng cao
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getTypeBadgeClass(getEffectiveContentType(item.section_key, item.content_type))" class="px-2.5 py-1 rounded-md text-[0.65rem] font-bold uppercase tracking-wider border flex w-fit items-center gap-1">
                  <i :class="getTypeIcon(getEffectiveContentType(item.section_key, item.content_type))"></i> {{ getTypeLabel(getEffectiveContentType(item.section_key, item.content_type)) }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-500">
                <div v-if="item.content_type === 'image'" class="h-12 w-20 rounded border border-slate-200 overflow-hidden bg-slate-50">
                  <img :src="item.content_value" alt="Image Preview" class="h-full w-full object-cover" @error="handleImageError">
                </div>
                <p v-else class="line-clamp-2" :title="item.content_value">{{ item.content_value || "Trống" }}</p>
              </td>
              <td class="px-6 py-4 text-slate-500 text-xs font-medium">{{ formatDate(item.updated_at) }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button
                    @click="openModal('edit', item)"
                    class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"
                    title="Chỉnh sửa"
                  >
                    <i class="fa-solid fa-pen text-xs"></i>
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

            <tr v-if="filteredContents.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                <i class="fa-solid fa-layer-group text-4xl mb-3 text-slate-300"></i>
                <p>Không tìm thấy dữ liệu nào phù hợp.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-sm text-slate-500">
        <span>Tổng cộng: {{ filteredContents.length }} mục nội dung</span>
      </div>
    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-[#16a34a]"></i>
            {{ modalMode === "add" ? "Thêm nội dung mới" : "Chỉnh sửa nội dung" }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-red-500 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveContent" class="p-6 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Vị trí cần chỉnh sửa <span class="text-red-500">*</span>
              </label>

              <select
                v-if="modalMode === 'add'"
                v-model="selectedPresetKey"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              >
                <option :value="CUSTOM_SECTION_KEY">Tự nhập mã kỹ thuật khác</option>
                <optgroup v-for="group in pageContentGroups" :key="group.key" :label="group.title">
                  <option v-for="field in group.fields" :key="field" :value="field">
                    {{ getContentLabel(field) }}
                  </option>
                </optgroup>
              </select>

              <div v-else class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="font-bold text-slate-800">{{ currentDefinition ? getContentLabel(formData.section_key) : "Nội dung tùy chỉnh" }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ getContentLocation(formData.section_key) }}</p>
                <p class="mt-2 font-mono text-xs text-slate-500">{{ formData.section_key }}</p>
              </div>

              <div v-if="currentDefinition" class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900">
                <p class="font-bold">{{ currentDefinition.label }}</p>
                <p class="mt-1">{{ currentDefinition.description }}</p>
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">
                  Gợi ý loại nội dung: {{ getTypeLabel(currentEditorType) }}
                </p>
                <p v-if="currentDefinition.advanced" class="mt-2 text-xs text-amber-700">
                  Mục này dành cho nội dung nâng cao. Chỉ sửa khi bạn biết rõ cấu trúc dữ liệu cần nhập.
                </p>
              </div>
            </div>

            <div v-if="allowManualSectionKey">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Mã kỹ thuật (Key) <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.section_key"
                type="text"
                required
                placeholder="VD: hero_title, footer_address..."
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              />
              <p class="text-[0.65rem] text-slate-400 mt-1">Dành cho trường hợp đặc biệt. Nên dùng danh sách vị trí có sẵn ở trên.</p>
            </div>

            <div :class="allowManualSectionKey ? '' : 'md:col-span-2'">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                Loại nội dung <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.content_type"
                :disabled="Boolean(currentDefinition)"
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582] disabled:bg-slate-100 disabled:text-slate-500"
              >
                <option value="text">Văn bản thường</option>
                <option value="html">Văn bản có định dạng HTML</option>
                <option value="image">Hình ảnh</option>
                <option value="link">Liên kết</option>
              </select>
              <p v-if="currentDefinition" class="text-[0.65rem] text-slate-400 mt-1">Loại nội dung được hệ thống tự nhận diện theo vị trí đã chọn.</p>
            </div>

            <div class="md:col-span-2">
              <div class="flex justify-between items-end mb-1">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                  Nội dung <span class="text-red-500">*</span>
                </label>
              </div>

              <div v-if="currentEditorType === 'image'" class="space-y-3">
                <div class="flex flex-wrap gap-2">
                  <button
                    type="button"
                    @click="imageInputMode = 'url'"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition"
                    :class="imageInputMode === 'url' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                  >
                    Dán URL ảnh
                  </button>
                  <button
                    type="button"
                    @click="imageInputMode = 'upload'"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition"
                    :class="imageInputMode === 'upload' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                  >
                    Chọn ảnh từ máy
                  </button>
                </div>

                <input
                  v-if="imageInputMode === 'url'"
                  v-model="formData.content_value"
                  type="text"
                  required
                  placeholder="Dán đường dẫn ảnh tại đây..."
                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                />

                <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                      <p class="text-sm font-bold text-slate-800">Chọn ảnh từ máy tính</p>
                      <p class="mt-1 text-xs text-slate-500">Hỗ trợ JPG, PNG, WEBP, GIF. Tối đa 5MB. Ảnh sẽ được tải lên khi bấm Lưu.</p>
                    </div>
                    <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#7AE582] px-4 py-2 text-sm font-bold text-slate-900 hover:bg-emerald-300 transition">
                      <i class="fa-solid fa-image"></i> Chọn ảnh
                      <input type="file" accept="image/*" class="hidden" @change="handleImageFileChange" />
                    </label>
                  </div>

                  <div v-if="selectedImageFile" class="mt-3 flex flex-wrap items-center gap-3 rounded-xl bg-white px-4 py-3 border border-slate-200">
                    <div class="text-sm text-slate-700">
                      <p class="font-bold">{{ selectedImageFile.name }}</p>
                      <p class="text-xs text-slate-500">{{ formatFileSize(selectedImageFile.size) }}</p>
                    </div>
                    <button
                      type="button"
                      @click="clearSelectedImage"
                      class="ml-auto rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200 transition"
                    >
                      Bỏ ảnh đã chọn
                    </button>
                  </div>
                </div>
              </div>

              <input
                v-else-if="currentEditorType === 'link'"
                v-model="formData.content_value"
                type="text"
                required
                placeholder="Dán đường dẫn liên kết tại đây..."
                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              />

              <textarea
                v-else-if="currentEditorType === 'html'"
                v-model="formData.content_value"
                rows="6"
                required
                placeholder="<p>Nhập nội dung HTML tại đây...</p>"
                class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-emerald-400 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              ></textarea>

              <textarea
                v-else
                v-model="formData.content_value"
                rows="4"
                required
                placeholder="Nhập nội dung văn bản..."
                class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
              ></textarea>

              <div v-if="currentEditorType === 'image' && previewImageUrl" class="mt-3">
                <p class="text-xs text-slate-500 mb-1">Ảnh xem trước:</p>
                <img :src="previewImageUrl" class="max-h-32 rounded border border-slate-200 shadow-sm" @error="handleImageError" alt="Preview Error" />
              </div>
            </div>
          </div>

          <div class="pt-2 flex justify-end gap-3 border-t border-slate-100 mt-6">
            <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
              Đóng
            </button>
            <button
              type="submit"
              :disabled="isUploadingImage"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-900 bg-[#7AE582] hover:bg-emerald-300 transition shadow-md disabled:cursor-not-allowed disabled:opacity-60"
            >
              <i :class="isUploadingImage ? 'fa-solid fa-spinner fa-spin mr-1' : 'fa-solid fa-floppy-disk mr-1'"></i>
              {{ isUploadingImage ? 'Đang tải ảnh...' : 'Lưu thông tin' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { apiFetch } from "../../utils/api";
import { clearAuthSession } from "../../utils/auth";
import { openConfirm } from "../../utils/confirm";

const router = useRouter();

const CUSTOM_SECTION_KEY = "__custom__";

const contentDefinitionMap = {
  home_banner_tagline: {
    label: "Nhãn nhỏ banner trang chủ",
    location: "Trang chủ / Khối banner",
    description: "Dòng chữ nhỏ nằm phía trên tiêu đề lớn của banner.",
    type: "text",
  },
  home_banner_title: {
    label: "Tiêu đề lớn banner trang chủ",
    location: "Trang chủ / Khối banner",
    description: "Tiêu đề chính nổi bật ở đầu trang chủ.",
    type: "text",
  },
  home_banner_description: {
    label: "Mô tả banner trang chủ",
    location: "Trang chủ / Khối banner",
    description: "Đoạn mô tả ngắn bên dưới tiêu đề banner.",
    type: "text",
  },
  home_banner_image_url: {
    label: "Ảnh banner trang chủ",
    location: "Trang chủ / Khối banner",
    description: "Ảnh lớn hiển thị ở bên phải banner trang chủ.",
    type: "image",
  },
  home_banner_primary_button_text: {
    label: "Chữ trên nút banner trang chủ",
    location: "Trang chủ / Khối banner",
    description: "Nội dung hiển thị trên nút chính của banner.",
    type: "text",
  },
  home_banner_primary_button_link: {
    label: "Liên kết nút banner trang chủ",
    location: "Trang chủ / Khối banner",
    description: "Đường dẫn khi bấm vào nút chính của banner.",
    type: "link",
  },
  home_intro_title: {
    label: "Tiêu đề phần giới thiệu trang chủ",
    location: "Trang chủ / Phần giới thiệu",
    description: "Tiêu đề lớn của khối giới thiệu ở giữa trang chủ.",
    type: "text",
  },
  home_intro_content: {
    label: "Nội dung phần giới thiệu trang chủ",
    location: "Trang chủ / Phần giới thiệu",
    description: "Đoạn giới thiệu có thể dùng nhiều đoạn văn HTML.",
    type: "html",
  },
  home_intro_image_url: {
    label: "Ảnh phần giới thiệu trang chủ",
    location: "Trang chủ / Phần giới thiệu",
    description: "Ảnh minh họa nằm cạnh phần giới thiệu.",
    type: "image",
  },
  about_hero_tagline: {
    label: "Nhãn nhỏ đầu trang giới thiệu",
    location: "Giới thiệu / Hero",
    description: "Dòng chữ nhỏ ở đầu trang giới thiệu.",
    type: "text",
  },
  about_hero_title: {
    label: "Tiêu đề hero trang giới thiệu",
    location: "Giới thiệu / Hero",
    description: "Tiêu đề lớn đầu trang giới thiệu.",
    type: "text",
  },
  about_hero_highlight: {
    label: "Phần chữ nhấn mạnh trong hero",
    location: "Giới thiệu / Hero",
    description: "Phần chữ màu nổi bật trong tiêu đề hero.",
    type: "text",
  },
  about_hero_description: {
    label: "Mô tả hero trang giới thiệu",
    location: "Giới thiệu / Hero",
    description: "Đoạn mô tả ngắn bên dưới tiêu đề hero.",
    type: "text",
  },
  about_story_image_url: {
    label: "Ảnh câu chuyện thương hiệu",
    location: "Giới thiệu / Câu chuyện",
    description: "Ảnh minh họa ở khối câu chuyện hoặc sứ mệnh.",
    type: "image",
  },
  about_story_title: {
    label: "Tiêu đề khối câu chuyện",
    location: "Giới thiệu / Câu chuyện",
    description: "Tiêu đề của phần câu chuyện thương hiệu.",
    type: "text",
  },
  about_story_content: {
    label: "Nội dung khối câu chuyện",
    location: "Giới thiệu / Câu chuyện",
    description: "Đoạn giới thiệu dài, có thể chứa nhiều đoạn HTML.",
    type: "html",
  },
  about_values_title: {
    label: "Tiêu đề phần giá trị cốt lõi",
    location: "Giới thiệu / Giá trị cốt lõi",
    description: "Tiêu đề chính của phần giá trị cốt lõi.",
    type: "text",
  },
  about_values_description: {
    label: "Mô tả phần giá trị cốt lõi",
    location: "Giới thiệu / Giá trị cốt lõi",
    description: "Đoạn mô tả ngắn phía dưới tiêu đề phần giá trị cốt lõi.",
    type: "text",
  },
  about_values_items: {
    label: "Danh sách giá trị cốt lõi",
    location: "Giới thiệu / Giá trị cốt lõi",
    description: "Dữ liệu nâng cao dạng JSON cho các thẻ giá trị cốt lõi.",
    type: "text",
    advanced: true,
  },
  about_team_title: {
    label: "Tiêu đề phần đội ngũ",
    location: "Giới thiệu / Đội ngũ",
    description: "Tiêu đề cho phần hiển thị thành viên đội ngũ.",
    type: "text",
  },
  about_team_members: {
    label: "Danh sách thành viên đội ngũ",
    location: "Giới thiệu / Đội ngũ",
    description: "Dữ liệu nâng cao dạng JSON cho từng thành viên và ảnh đại diện.",
    type: "text",
    advanced: true,
  },
  about_cta_title: {
    label: "Tiêu đề lời kêu gọi hành động",
    location: "Giới thiệu / CTA cuối trang",
    description: "Tiêu đề chính của khối CTA ở cuối trang.",
    type: "text",
  },
  about_cta_description: {
    label: "Mô tả lời kêu gọi hành động",
    location: "Giới thiệu / CTA cuối trang",
    description: "Đoạn mô tả ngắn bên dưới CTA cuối trang.",
    type: "text",
  },
  about_cta_button_text: {
    label: "Chữ trên nút CTA cuối trang",
    location: "Giới thiệu / CTA cuối trang",
    description: "Nội dung hiển thị trên nút của CTA.",
    type: "text",
  },
  about_cta_button_link: {
    label: "Liên kết nút CTA cuối trang",
    location: "Giới thiệu / CTA cuối trang",
    description: "Đường dẫn khi bấm vào nút CTA.",
    type: "link",
  },
};

const pageContentGroups = [
  {
    key: "home",
    badge: "Trang người dùng",
    title: "Trang chủ",
    description: "Sửa banner, phần giới thiệu và các liên kết chính của HomeView.",
    prefix: "home_",
    previewRoute: "/",
    previewLabel: "trang chủ",
    fields: [
      "home_banner_tagline",
      "home_banner_title",
      "home_banner_description",
      "home_banner_image_url",
      "home_banner_primary_button_text",
      "home_banner_primary_button_link",
      "home_intro_title",
      "home_intro_content",
      "home_intro_image_url",
    ],
  },
  {
    key: "about",
    badge: "Trang người dùng",
    title: "Trang giới thiệu",
    description: "Sửa nội dung hero, câu chuyện thương hiệu, đội ngũ và CTA ở AboutView.",
    prefix: "about_",
    previewRoute: "/about",
    previewLabel: "trang giới thiệu",
    fields: [
      "about_hero_tagline",
      "about_hero_title",
      "about_hero_highlight",
      "about_hero_description",
      "about_story_image_url",
      "about_story_title",
      "about_story_content",
      "about_values_title",
      "about_values_description",
      "about_values_items",
      "about_team_title",
      "about_team_members",
      "about_cta_title",
      "about_cta_description",
      "about_cta_button_text",
      "about_cta_button_link",
    ],
  },
];

const contents = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const filterType = ref("all");
const imageInputMode = ref("url");
const selectedImageFile = ref(null);
const selectedImageObjectUrl = ref("");
const isUploadingImage = ref(false);

const isModalOpen = ref(false);
const modalMode = ref("add");
const selectedPresetKey = ref(CUSTOM_SECTION_KEY);
const formData = ref({
  id: null,
  section_key: "",
  content_type: "text",
  content_value: "",
});

const API_PATH = "admin/website_contents.php";
const UPLOAD_API_PATH = "admin/upload_website_image.php";

const normalizeStoredContentType = (type) => (type === "link" ? "text" : type);

const getEffectiveContentType = (sectionKey, fallbackType = "text") => {
  return contentDefinitionMap[sectionKey]?.type ?? fallbackType ?? "text";
};

const currentDefinition = computed(() => contentDefinitionMap[formData.value.section_key] ?? null);
const currentEditorType = computed(() => currentDefinition.value?.type ?? formData.value.content_type);
const allowManualSectionKey = computed(() => modalMode.value === "add" && selectedPresetKey.value === CUSTOM_SECTION_KEY);
const previewImageUrl = computed(() => selectedImageObjectUrl.value || formData.value.content_value || "");

watch(selectedPresetKey, (value) => {
  if (modalMode.value !== "add") return;

  if (value === CUSTOM_SECTION_KEY) {
    formData.value.section_key = "";
    formData.value.content_type = "text";
    return;
  }

  const definition = contentDefinitionMap[value];
  formData.value.section_key = value;
  formData.value.content_type = normalizeStoredContentType(definition?.type ?? "text");
});

const redirectToLogin = () => {
  clearAuthSession();
  router.push("/login");
};

const resetForm = () => {
  revokeSelectedObjectUrl();
  selectedImageFile.value = null;
  imageInputMode.value = "url";
  selectedPresetKey.value = CUSTOM_SECTION_KEY;
  formData.value = {
    id: null,
    section_key: "",
    content_type: "text",
    content_value: "",
  };
};

const loadContents = async () => {
  isLoading.value = true;
  try {
    const response = await apiFetch(API_PATH);
    if (response.status === 401 || response.status === 403) return redirectToLogin();

    const result = await response.json();
    if (result.status === "success") {
      contents.value = result.data || [];
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => loadContents());

const countGroupItems = (prefix) => {
  return contents.value.filter((item) => item.section_key?.startsWith(prefix)).length;
};

const applySectionFilter = (prefix) => {
  searchQuery.value = prefix;
  filterType.value = "all";
};

const getContentLabel = (sectionKey) => {
  return contentDefinitionMap[sectionKey]?.label || "Nội dung tùy chỉnh";
};

const getContentLocation = (sectionKey) => {
  return contentDefinitionMap[sectionKey]?.location || "Mục kỹ thuật hoặc nội dung mở rộng";
};

const isAdvancedSection = (sectionKey) => {
  return Boolean(contentDefinitionMap[sectionKey]?.advanced);
};

const getTypeLabel = (type) => {
  const map = {
    text: "Văn bản",
    html: "HTML",
    image: "Hình ảnh",
    link: "Liên kết",
  };
  return map[type] || type;
};

const filteredContents = computed(() => {
  return contents.value.filter((item) => {
    const query = searchQuery.value.toLowerCase().trim();
    const label = getContentLabel(item.section_key).toLowerCase();
    const location = getContentLocation(item.section_key).toLowerCase();
    const sectionKey = String(item.section_key || "").toLowerCase();
    const contentValue = String(item.content_value || "").toLowerCase();

    const matchQuery = !query ||
      label.includes(query) ||
      location.includes(query) ||
      sectionKey.includes(query) ||
      contentValue.includes(query);

    const effectiveType = getEffectiveContentType(item.section_key, item.content_type);
    const matchType = filterType.value === "all" || effectiveType === filterType.value;

    return matchQuery && matchType;
  });
});

const formatDate = (dateString) => {
  if (!dateString) return "Chưa cập nhật";
  const date = new Date(dateString);
  return new Intl.DateTimeFormat("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date);
};

const getTypeIcon = (type) => {
  const map = {
    text: "fa-solid fa-align-left",
    html: "fa-solid fa-code",
    image: "fa-solid fa-image",
    link: "fa-solid fa-link",
  };
  return map[type] || "fa-solid fa-file";
};

const getTypeBadgeClass = (type) => {
  const map = {
    text: "bg-slate-100 text-slate-700 border-slate-200",
    html: "bg-blue-100 text-blue-700 border-blue-200",
    image: "bg-purple-100 text-purple-700 border-purple-200",
    link: "bg-amber-100 text-amber-700 border-amber-200",
  };
  return map[type] || "bg-slate-100 text-slate-700 border-slate-200";
};

const revokeSelectedObjectUrl = () => {
  if (selectedImageObjectUrl.value) {
    URL.revokeObjectURL(selectedImageObjectUrl.value);
    selectedImageObjectUrl.value = "";
  }
};

const handleImageError = (e) => {
  e.target.src = "https://placehold.co/400x200/f8fafc/94a3b8?text=Image+Not+Found";
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

const clearSelectedImage = () => {
  revokeSelectedObjectUrl();
  selectedImageFile.value = null;
  if (imageInputMode.value === "upload") {
    formData.value.content_value = "";
  }
};

const formatFileSize = (size) => {
  if (!size) return "0 B";
  if (size < 1024) return `${size} B`;
  if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
  return `${(size / (1024 * 1024)).toFixed(1)} MB`;
};

const uploadSelectedImage = async () => {
  if (!selectedImageFile.value) {
    alert("Vui lòng chọn ảnh từ máy trước khi lưu.");
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
      formData.value.content_value = result.data.url || "";
      revokeSelectedObjectUrl();
      selectedImageFile.value = null;
      imageInputMode.value = "url";
      return formData.value.content_value;
    }

    alert("Lỗi: " + result.message);
    return null;
  } catch (error) {
    alert("Không thể tải ảnh lên máy chủ!");
    return null;
  } finally {
    isUploadingImage.value = false;
  }
};

const ensureImageReady = async () => {
  if (currentEditorType.value !== "image") return true;

  if (imageInputMode.value === "upload") {
    if (selectedImageFile.value) {
      const uploadedUrl = await uploadSelectedImage();
      return Boolean(uploadedUrl);
    }

    if (!formData.value.content_value.trim()) {
      alert("Vui lòng chọn ảnh từ máy hoặc dán URL ảnh.");
      return false;
    }
  }

  return true;
};

const openModal = (mode, item = null) => {
  modalMode.value = mode;
  revokeSelectedObjectUrl();
  selectedImageFile.value = null;
  imageInputMode.value = "url";

  if (mode === "edit" && item) {
    formData.value = {
      id: item.id,
      section_key: item.section_key ?? "",
      content_type: normalizeStoredContentType(contentDefinitionMap[item.section_key]?.type ?? item.content_type ?? "text"),
      content_value: item.content_value ?? "",
    };
    selectedPresetKey.value = contentDefinitionMap[item.section_key] ? item.section_key : CUSTOM_SECTION_KEY;
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
  if (formData.value.section_key.trim().length < 3) {
    alert("Vui lòng chọn vị trí nội dung hoặc nhập mã kỹ thuật hợp lệ.");
    return false;
  }

  if (!formData.value.content_value.trim()) {
    alert("Vui lòng nhập nội dung.");
    return false;
  }

  return true;
};

const saveContent = async () => {
  const imageReady = await ensureImageReady();
  if (!imageReady || !validateForm()) return;

  const method = modalMode.value === "add" ? "POST" : "PUT";
  const requestBody = {
    ...formData.value,
    content_type: normalizeStoredContentType(currentEditorType.value),
  };

  try {
    const response = await apiFetch(API_PATH, {
      method,
      body: JSON.stringify(requestBody),
    });

    if (response.status === 401 || response.status === 403) return redirectToLogin();

    const result = await response.json();
    if (result.status === "success") {
      closeModal();
      loadContents();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
  }
};

const confirmDelete = async (item) => {
  const confirmed = await openConfirm({
    title: "Xóa nội dung website",
    message: `Cảnh báo: Xóa nội dung "${getContentLabel(item.section_key)}" có thể làm website hiển thị sai. Bạn có chắc chắn muốn xóa không?`,
    confirmText: "Xóa nội dung",
    cancelText: "Không xóa",
    tone: "warning",
  });
  if (!confirmed) return;

  try {
    const response = await apiFetch(`${API_PATH}?id=${item.id}`, { method: "DELETE" });
    if (response.status === 401 || response.status === 403) return redirectToLogin();

    const result = await response.json();
    if (result.status === "success") {
      loadContents();
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối với máy chủ!");
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



