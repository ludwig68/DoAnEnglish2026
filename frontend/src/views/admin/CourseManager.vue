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
          <tbody class="text-sm text-slate-700">
            <template
              v-for="course in filteredCourses"
              :key="course.id"
            >
              <!-- Main course row -->
              <tr
                class="border-b border-slate-100 hover:bg-slate-50/60 transition-colors"
                :class="{ 'bg-emerald-50/40 border-b-0': expandedCourseId === course.id }"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <!-- Chevron toggle button -->
                    <button
                      @click="toggleExpand(course)"
                      class="chevron-btn w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-200 shrink-0"
                      :class="expandedCourseId === course.id
                        ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200 rotate-180-btn'
                        : 'bg-slate-100 text-slate-500 hover:bg-emerald-100 hover:text-emerald-600'"
                      :title="expandedCourseId === course.id ? 'Thu gọn' : 'Xem bài học'"
                    >
                      <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300"
                         :style="expandedCourseId === course.id ? 'transform: rotate(180deg)' : ''"
                      ></i>
                    </button>
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
                        <span :class="course.lesson_count > 0 ? 'text-emerald-600 font-semibold' : ''">
                          {{ course.lesson_count }} bài học
                        </span>
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
                  <div class="flex justify-end gap-2">
                    <button
                      @click="openModal('edit', course)"
                      class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"
                      title="Chỉnh sửa khóa học"
                    >
                      <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <button
                      @click="confirmDelete(course)"
                      class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors"
                      title="Xóa khóa học"
                    >
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Expandable dropdown panel -->
              <tr
                v-if="expandedCourseId === course.id"
                class="border-b border-slate-100 bg-emerald-50/30"
              >
                <td colspan="6" class="px-6 pb-5 pt-0">
                  <div class="expand-panel rounded-2xl border border-emerald-100 bg-white shadow-sm overflow-hidden">
                    <!-- Panel header -->
                    <div class="flex items-center justify-between px-5 py-3 bg-emerald-50/70 border-b border-emerald-100">
                      <div class="flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-emerald-600 text-sm"></i>
                        <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Danh sách bài học</span>
                        <span class="ml-1 px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[0.6rem] font-bold">
                          {{ course.lesson_count }}
                        </span>
                      </div>
                      <button
                        @click="openLessonModal(course)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-xs font-bold hover:bg-emerald-600 transition shadow-sm"
                      >
                        <i class="fa-solid fa-plus"></i>
                        Thêm bài học
                      </button>
                    </div>

                    <!-- Lesson list -->
                    <div class="p-4">
                      <div v-if="isLoadingLessons" class="flex items-center justify-center py-6 text-slate-400 gap-2">
                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-emerald-200 border-t-emerald-500"></div>
                        <span class="text-xs">Đang tải bài học...</span>
                      </div>

                      <div v-else-if="expandedLessons.length === 0" class="flex flex-col items-center py-8 text-slate-400">
                        <i class="fa-regular fa-file-lines text-3xl mb-2"></i>
                        <p class="text-xs">Khóa học này chưa có bài học nào.</p>
                        <button
                          @click="openLessonModal(course)"
                          class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-bold hover:bg-emerald-100 transition border border-emerald-200"
                        >
                          <i class="fa-solid fa-plus"></i> Tạo bài học đầu tiên
                        </button>
                      </div>

                      <div v-else class="grid gap-2">
                        <div
                          v-for="(lesson, idx) in expandedLessons"
                          :key="lesson.id"
                          class="lesson-item group/lesson flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3 hover:border-emerald-200 hover:bg-emerald-50/40 transition-all"
                        >
                          <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-black flex items-center justify-center shrink-0">
                            {{ idx + 1 }}
                          </span>
                          <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ lesson.title }}</p>
                            <p v-if="lesson.video_url" class="text-xs text-slate-400 mt-0.5 truncate">
                              <i class="fa-brands fa-youtube text-red-400 mr-1"></i>{{ lesson.video_url }}
                            </p>
                          </div>
                          <!-- Action buttons: edit + delete -->
                          <div class="flex items-center gap-1.5 shrink-0 opacity-0 group-hover/lesson:opacity-100 transition-opacity">
                            <button
                              @click="openEditLessonModal(lesson, course)"
                              class="w-7 h-7 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-colors"
                              title="Sửa bài học"
                            >
                              <i class="fa-solid fa-pen text-[0.6rem]"></i>
                            </button>
                            <button
                              @click="confirmDeleteLesson(lesson, course)"
                              class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors"
                              title="Xóa bài học"
                            >
                              <i class="fa-solid fa-trash-can text-[0.6rem]"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </template>

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

    <!-- Modal Thêm bài học mới -->
    <div
      v-if="isLessonModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate__animated animate__zoomIn animate__faster"
      >
        <div
          class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-emerald-50/50"
        >
          <h3 class="text-lg font-bold text-emerald-800 flex items-center gap-2">
            <i class="fa-solid fa-book-medical text-emerald-600"></i> Khởi tạo bài học mới
          </h3>
          <button
            @click="closeLessonModal"
            class="text-slate-400 hover:text-red-500 transition"
          >
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="submitLesson" class="p-6 space-y-5">
          <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 flex items-center gap-4">
             <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
              <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
              <p class="text-[0.65rem] font-bold text-emerald-600 uppercase tracking-widest">Khóa học hiện tại</p>
              <p class="text-sm font-black text-slate-800 line-clamp-1">{{ selectedCourseForLesson?.title }}</p>
            </div>
          </div>

          <div>
             <label
              class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Tên bài học <span class="text-red-500">*</span></label
            >
            <input
              v-model="lessonForm.title"
              type="text"
              required
              placeholder="Ví dụ: Bài 1 - Phát âm cơ bản"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>

          <div>
             <label
              class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Đường dẫn Video (Tùy chọn)</label
            >
            <input
              v-model="lessonForm.video_url"
              type="text"
              placeholder="https://youtube.com/..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>

          <div class="pt-3 flex justify-end gap-3 mt-6 border-t border-slate-100">
            <button
              type="button"
              @click="closeLessonModal"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              :disabled="isSubmittingLesson"
              class="px-5 py-2.5 rounded-xl font-bold flex items-center gap-2 text-slate-900 shadow-md hover:opacity-90 transition disabled:opacity-60"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
            >
              <i :class="isSubmittingLesson ? 'fa-solid fa-spinner animate-spin' : 'fa-solid fa-floppy-disk'"></i>
              {{ isSubmittingLesson ? 'Đang tạo...' : 'Tạo bài học' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Sửa bài học -->
    <div
      v-if="isEditLessonModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate__animated animate__fadeIn animate__faster"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate__animated animate__zoomIn animate__faster"
      >
        <div
          class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-blue-50/50"
        >
          <h3 class="text-lg font-bold text-blue-800 flex items-center gap-2">
            <i class="fa-solid fa-pen text-blue-600"></i> Chỉnh sửa bài học
          </h3>
          <button
            @click="closeEditLessonModal"
            class="text-slate-400 hover:text-red-500 transition"
          >
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="submitEditLesson" class="p-6 space-y-5">
          <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-blue-600 shadow-sm shrink-0">
              <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
              <p class="text-[0.65rem] font-bold text-blue-600 uppercase tracking-widest">Khóa học</p>
              <p class="text-sm font-black text-slate-800 line-clamp-1">{{ editLessonCourse?.title }}</p>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Tên bài học <span class="text-red-500">*</span></label
            >
            <input
              v-model="editLessonForm.title"
              type="text"
              required
              placeholder="Ví dụ: Bài 1 - Phát âm cơ bản"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1"
              >Đường dẫn Video (Tùy chọn)</label
            >
            <input
              v-model="editLessonForm.video_url"
              type="text"
              placeholder="https://youtube.com/..."
              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>

          <div class="pt-3 flex justify-end gap-3 mt-6 border-t border-slate-100">
            <button
              type="button"
              @click="closeEditLessonModal"
              class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              :disabled="isSubmittingEditLesson"
              class="px-5 py-2.5 rounded-xl font-bold flex items-center gap-2 text-white shadow-md hover:opacity-90 transition disabled:opacity-60 bg-blue-500 hover:bg-blue-600"
            >
              <i :class="isSubmittingEditLesson ? 'fa-solid fa-spinner animate-spin' : 'fa-solid fa-floppy-disk'"></i>
              {{ isSubmittingEditLesson ? 'Đang lưu...' : 'Lưu thay đổi' }}
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

// --- LOGIC TẠO BÀI HỌC (THÊM MỚI) ---
const isLessonModalOpen = ref(false)
const isSubmittingLesson = ref(false)
const selectedCourseForLesson = ref(null)
const lessonForm = ref({ title: '', video_url: '' })

// --- LOGIC MỞ RỘNG/THU GỌN BẢNG BÀI HỌC ---
const expandedCourseId = ref(null)
const expandedLessons = ref([])
const isLoadingLessons = ref(false)

const toggleExpand = async (course) => {
  if (expandedCourseId.value === course.id) {
    // Thu gọn nếu đang mở
    expandedCourseId.value = null
    expandedLessons.value = []
    return
  }

  expandedCourseId.value = course.id
  expandedLessons.value = []
  isLoadingLessons.value = true

  try {
    const response = await apiFetch(`${API_PATH}?action=get_lessons&course_id=${course.id}`)
    if (response.ok) {
      const result = await response.json()
      if (result.status === 'success') {
        expandedLessons.value = result.data.lessons || []
      }
    }
  } catch (e) {
    console.error('Không thể tải bài học:', e)
  } finally {
    isLoadingLessons.value = false
  }
}

const openLessonModal = (course) => {
  selectedCourseForLesson.value = course
  lessonForm.value = { title: '', video_url: '' }
  isLessonModalOpen.value = true
}

const closeLessonModal = () => {
  isLessonModalOpen.value = false
  selectedCourseForLesson.value = null
}

const submitLesson = async () => {
  if (!lessonForm.value.title.trim()) {
    alert("Vui lòng nhập tên bài học.");
    return;
  }

  isSubmittingLesson.value = true;
  
  try {
    const payload = {
      action: 'create_lesson',
      course_id: selectedCourseForLesson.value.id,
      title: lessonForm.value.title.trim(),
      video_url: lessonForm.value.video_url.trim()
    };
    
    const response = await apiFetch(API_PATH, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    
    if (response.status === 401 || response.status === 403) {
      redirectToLogin();
      return;
    }

    const result = await response.json();
    if (result.status === 'success') {
      const savedCourse = selectedCourseForLesson.value;
      closeLessonModal();

      // Tăng lesson_count trong danh sách ngay lập tức (không cần reload toàn trang)
      const courseInList = courses.value.find(c => c.id === savedCourse.id);
      if (courseInList) courseInList.lesson_count++;

      // Nếu dropdown đang mở cho khóa học này → reload danh sách bài học luôn
      if (expandedCourseId.value === savedCourse.id) {
        isLoadingLessons.value = true;
        try {
          const r = await apiFetch(`${API_PATH}?action=get_lessons&course_id=${savedCourse.id}`);
          if (r.ok) {
            const data = await r.json();
            if (data.status === 'success') expandedLessons.value = data.data.lessons || [];
          }
        } finally {
          isLoadingLessons.value = false;
        }
      }
    } else {
      alert("Lỗi: " + result.message);
    }
  } catch (error) {
    alert("Không thể kết nối đến máy chủ.");
    console.error(error);
  } finally {
    isSubmittingLesson.value = false;
  }
};

// --- EDIT LESSON ---
const isEditLessonModalOpen = ref(false)
const isSubmittingEditLesson = ref(false)
const editLessonCourse = ref(null)
const editLessonForm = ref({ id: null, title: '', video_url: '' })

const openEditLessonModal = (lesson, course) => {
  editLessonCourse.value = course
  editLessonForm.value = { id: lesson.id, title: lesson.title, video_url: lesson.video_url || '' }
  isEditLessonModalOpen.value = true
}

const closeEditLessonModal = () => {
  isEditLessonModalOpen.value = false
  editLessonCourse.value = null
}

const submitEditLesson = async () => {
  if (!editLessonForm.value.title.trim()) {
    alert('Vui lòng nhập tên bài học.')
    return
  }
  isSubmittingEditLesson.value = true
  try {
    const response = await apiFetch(`${API_PATH}?action=update_lesson`, {
      method: 'PUT',
      body: JSON.stringify({
        lesson_id: editLessonForm.value.id,
        title: editLessonForm.value.title.trim(),
        video_url: editLessonForm.value.video_url.trim()
      })
    })
    if (response.status === 401 || response.status === 403) { redirectToLogin(); return }
    const result = await response.json()
    if (result.status === 'success') {
      // Cập nhật tập tin củc bộ trong expandedLessons
      const idx = expandedLessons.value.findIndex(l => l.id === editLessonForm.value.id)
      if (idx !== -1) {
        expandedLessons.value[idx] = {
          ...expandedLessons.value[idx],
          title: editLessonForm.value.title.trim(),
          video_url: editLessonForm.value.video_url.trim() || null
        }
      }
      closeEditLessonModal()
    } else {
      alert('Lỗi: ' + result.message)
    }
  } catch (e) {
    alert('Không thể kết nối máy chủ.')
  } finally {
    isSubmittingEditLesson.value = false
  }
}

// --- DELETE LESSON ---
const confirmDeleteLesson = async (lesson, course) => {
  const confirmed = await openConfirm({
    title: 'Xóa bài học',
    message: `Bạn có chắc muốn xóa bài học “${lesson.title}” không?`,
    confirmText: 'Xóa bài học',
    cancelText: 'Không xóa',
    tone: 'danger'
  })
  if (!confirmed) return

  try {
    const response = await apiFetch(`${API_PATH}?action=delete_lesson&lesson_id=${lesson.id}`, { method: 'DELETE' })
    if (response.status === 401 || response.status === 403) { redirectToLogin(); return }
    const result = await response.json()
    if (result.status === 'success') {
      // Xóa khỏi danh sách cục bộ
      expandedLessons.value = expandedLessons.value.filter(l => l.id !== lesson.id)
      // Giảm lesson_count trong danh sách khóa học
      const courseInList = courses.value.find(c => c.id === course.id)
      if (courseInList && courseInList.lesson_count > 0) courseInList.lesson_count--
    } else {
      alert('Lỗi: ' + result.message)
    }
  } catch (e) {
    alert('Không thể kết nối máy chủ.')
  }
}

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

/* Expand panel animation */
.expand-panel {
  animation: expandDown 0.25s cubic-bezier(0.4,0,0.2,1);
}

@keyframes expandDown {
  from {
    opacity: 0;
    transform: translateY(-8px) scaleY(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scaleY(1);
  }
}

/* Lesson item hover */
.lesson-item {
  animation: fadeSlideIn 0.2s ease both;
}

@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateX(-6px); }
  to   { opacity: 1; transform: translateX(0); }
}
</style>


