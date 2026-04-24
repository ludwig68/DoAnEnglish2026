<template>
  <div class="h-full flex flex-col p-4 md:p-6 animate__animated animate__fadeIn space-y-6">

    <div v-if="isLoading" class="flex-1 flex flex-col justify-center items-center text-slate-400 py-24">
      <div class="w-12 h-12 border-4 border-emerald-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
      <p class="font-bold uppercase tracking-widest text-sm animate-pulse">Đang tải chi tiết...</p>
    </div>

    <div v-else-if="errorMessage" class="flex-1 flex flex-col justify-center items-center text-red-500 py-24">
      <div
        class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-6 shadow-inner ring-4 ring-red-50/50">
        <i class="fa-solid fa-triangle-exclamation text-4xl text-red-400"></i>
      </div>
      <p class="font-black text-lg text-slate-800">{{ errorMessage }}</p>
      <p class="text-sm text-slate-500 mt-2">Vui lòng kiểm tra lại đường dẫn hoặc tải lại trang.</p>
      <button @click="$router.push('/admin/schedules')"
        class="mt-6 px-6 py-3 bg-white border-2 border-slate-200 text-slate-600 font-bold rounded-xl hover:border-emerald-200 hover:text-emerald-600 transition-all duration-300 shadow-sm active:scale-95">
        <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại lịch giảng dạy
      </button>
    </div>

    <template v-else-if="schedule">
      <!-- Premium Header Section -->
      <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 flex flex-col md:flex-row items-start md:items-center gap-6 relative overflow-hidden group">
        <div
          class="absolute top-0 right-0 w-64 h-64 bg-emerald-50 rounded-full blur-3xl -mr-20 -mt-20 opacity-50 group-hover:opacity-70 transition-opacity">
        </div>
        <router-link to="/admin/schedules"
          class="relative z-10 w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:shadow-md border border-slate-100 transition-all duration-300 hover:-translate-x-1 shrink-0">
          <i class="fa-solid fa-arrow-left"></i>
        </router-link>
        <div class="relative z-10 flex-1">
          <div class="flex flex-wrap items-center gap-3 mb-2">
            <h1
              class="text-2xl md:text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500 tracking-tight">
              {{ schedule?.class_name || 'Lớp học' }}
            </h1>
            <span
              class="text-[0.7rem] uppercase tracking-wider font-black px-3 py-1.5 rounded-xl border shadow-sm backdrop-blur-sm"
              :class="schedule?.status === 'completed' ? 'bg-slate-100/80 text-slate-600 border-slate-200' : 'bg-emerald-100/80 text-emerald-800 border-emerald-200'">
              {{ schedule?.status === 'completed' ? 'Đã dạy' : 'Sắp tới' }}
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mt-3">
            <div
              class="flex items-center gap-2 text-sm font-semibold text-slate-600 bg-slate-50/50 px-3 py-1.5 rounded-xl border border-slate-100 backdrop-blur-sm">
              <i class="fa-regular fa-calendar text-emerald-500"></i> {{ formatDate(schedule?.study_date).split(' ')[0]
                || 'Chưa rõ' }}
            </div>
            <div v-if="schedule?.start_time"
              class="flex items-center gap-2 text-sm font-semibold text-slate-600 bg-slate-50/50 px-3 py-1.5 rounded-xl border border-slate-100 backdrop-blur-sm">
              <i class="fa-regular fa-clock text-emerald-500"></i> {{ schedule.start_time.substring(0, 5) }} - {{
                schedule.end_time.substring(0,5) }}
            </div>
            <div
              class="flex items-center gap-2 text-sm font-semibold text-slate-600 bg-slate-50/50 px-3 py-1.5 rounded-xl border border-slate-100 backdrop-blur-sm">
              <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center"><i
                  class="fa-solid fa-user-tie text-xs text-emerald-600"></i></div>
              {{ schedule?.teacher_name || 'Chưa xếp' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div
        class="flex-1 flex flex-col bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/50 overflow-hidden">

        <!-- Tabs -->
        <div class="flex gap-2 p-3 bg-slate-50/50 border-b border-slate-100 overflow-x-auto custom-scrollbar">
          <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
            class="px-5 py-3 font-bold text-sm transition-all duration-300 rounded-xl relative overflow-hidden group whitespace-nowrap flex items-center gap-2"
            :class="activeTab === tab.id ? 'text-emerald-700 bg-white shadow-sm border border-slate-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'">
            <div v-if="activeTab === tab.id"
              class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-1 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-t-full">
            </div>
            <i
              :class="[tab.icon, activeTab === tab.id ? 'text-emerald-500' : 'text-slate-400 group-hover:text-slate-500']"></i>
            {{ tab.name }}
          </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6 md:p-8 flex-1 bg-slate-50/30 overflow-y-auto">

          <!-- Tab Tổng Quan -->
          <div v-if="activeTab === 'overview'" class="animate__animated animate__fadeIn animate__faster h-full">
            <div class="grid md:grid-cols-2 gap-6 h-full">
              <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute right-0 top-0 w-32 h-32 bg-sky-50 rounded-full blur-2xl -mr-10 -mt-10 opacity-60">
                </div>
                <h3 class="font-black text-slate-800 text-sm uppercase tracking-widest mb-6 flex items-center gap-2">
                  <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center"><i
                      class="fa-solid fa-location-dot"></i></div>
                  Thông tin phòng học
                </h3>

                <div class="space-y-5 relative z-10">
                  <div class="flex flex-col gap-1.5">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hình thức tổ chức</span>
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold"
                        :class="schedule?.teaching_type === 'online' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'">
                        <i
                          :class="schedule?.teaching_type === 'online' ? 'fa-solid fa-laptop' : 'fa-solid fa-chalkboard-user'"></i>
                      </div>
                      <span class="font-bold text-slate-700 text-lg">{{ schedule?.teaching_type === 'online' ? 'Trực tuyến (Online)' : 'Trực tiếp (Offline)' }}</span>
                    </div>
                  </div>

                  <div class="flex flex-col gap-1.5">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Địa điểm / Link</span>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 font-medium text-slate-700">
                      <template v-if="schedule?.teaching_type === 'online'">
                        <a v-if="schedule?.room_info"
                          :href="schedule?.room_info.startsWith('http') ? schedule?.room_info : 'https://' + schedule?.room_info"
                          target="_blank"
                          class="text-blue-600 hover:text-blue-700 flex items-center gap-2 underline break-all">
                          <i class="fa-solid fa-link"></i> {{ schedule.room_info }}
                        </a>
                        <span v-else class="text-slate-400 italic">Chưa cập nhật link</span>
                      </template>
                      <template v-else>
                        <span class="flex items-center gap-2">
                          <i class="fa-solid fa-door-open text-slate-400"></i> {{ schedule?.room_info || 'Chưa sắp phòng' }}
                        </span>
                      </template>
                    </div>
                  </div>
                </div>
              </div>

              <div
                class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div
                  class="absolute right-0 top-0 w-32 h-32 bg-amber-50 rounded-full blur-2xl -mr-10 -mt-10 opacity-60">
                </div>
                <h3 class="font-black text-slate-800 text-sm uppercase tracking-widest mb-6 flex items-center gap-2">
                  <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center"><i
                      class="fa-solid fa-clipboard-list"></i></div>
                  Ghi chú buổi học
                </h3>
                <div
                  class="bg-amber-50/50 border border-amber-100/50 p-5 rounded-xl h-[calc(100%-4rem)] relative z-10 text-sm leading-relaxed text-slate-700 font-medium whitespace-pre-line">
                  {{ schedule?.note || 'Không có ghi chú nào cho buổi học này.' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Tab Tài Liệu -->
          <div v-if="activeTab === 'materials'" class="animate__animated animate__fadeIn animate__faster">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
              <h3 class="font-black text-slate-800 text-sm uppercase tracking-widest flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner">
                  <i class="fa-solid fa-book-open"></i></div>
                Tài liệu In-class
              </h3>
              <button @click="isMaterialModalOpen = true"
                class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-emerald-500/30 transition-all duration-300 hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 shadow-sm w-fit">
                <i class="fa-solid fa-cloud-arrow-up"></i> Tải tài liệu lên
              </button>
            </div>

            <div v-if="materials.length === 0"
              class="flex flex-col items-center justify-center py-16 border-2 border-dashed border-slate-200 rounded-2xl bg-white/50">
              <div
                class="w-16 h-16 rounded-full bg-slate-50 shadow-sm flex items-center justify-center mb-4 text-emerald-200">
                <i class="fa-solid fa-folder-open text-3xl"></i>
              </div>
              <p class="text-sm font-bold text-slate-500">Chưa có tài liệu nào</p>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div v-for="mat in materials" :key="mat.id"
                class="p-5 border border-slate-100 rounded-2xl flex items-center gap-4 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 bg-white group hover:-translate-y-0.5">
                <div
                  class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                  <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-slate-800 text-sm truncate group-hover:text-emerald-700 transition-colors">
                    {{
                    mat.title }}</h4>
                  <p class="text-[0.65rem] text-slate-400 mt-0.5 font-semibold"><i class="fa-regular fa-clock"></i> {{
                    formatDate(mat.uploaded_at) }}</p>
                </div>
                <a :href="mat.file_url" target="_blank"
                  class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors shrink-0 tooltip"
                  title="Mở liên kết">
                  <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
              </div>
            </div>
          </div>

          <!-- Tab Bài Tập -->
          <div v-if="activeTab === 'assignments'" class="animate__animated animate__fadeIn animate__faster">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
              <h3 class="font-black text-slate-800 text-sm uppercase tracking-widest flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                  <i class="fa-solid fa-file-signature"></i></div>
                Bài tập Post-class
              </h3>
              <button @click="isAssignmentModalOpen = true"
                class="px-5 py-2.5 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 active:scale-95 flex items-center gap-2 w-fit"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
                <i class="fa-solid fa-plus"></i> Giao bài tập
              </button>
            </div>

            <div v-if="assignments.length === 0"
              class="flex flex-col items-center justify-center py-16 border-2 border-dashed border-slate-200 rounded-2xl bg-white/50">
              <div
                class="w-16 h-16 rounded-full bg-slate-50 shadow-sm flex items-center justify-center mb-4 text-indigo-200">
                <i class="fa-solid fa-pen-ruler text-3xl"></i>
              </div>
              <p class="text-sm font-bold text-slate-500">Chưa có bài tập nào</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="ass in assignments" :key="ass.id"
                class="p-6 border border-slate-100 rounded-2xl bg-white hover:shadow-lg transition-all duration-300 group hover:-translate-y-0.5 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-indigo-400 to-purple-500"></div>
                <div class="pl-2">
                  <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                    <h4 class="font-black text-lg text-slate-800 group-hover:text-indigo-700 transition-colors">{{ ass.title }}</h4>
                    <div class="flex flex-wrap items-center gap-2">
                       <span v-if="isOverdue(ass.deadline)" class="text-[10px] font-black uppercase bg-red-500 text-white px-2 py-1 rounded shadow-sm animate-pulse">
                         Quá hạn
                       </span>
                       <span class="text-xs font-black text-slate-600 bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-lg flex items-center gap-1.5 w-fit">
                         <i class="fa-regular fa-clock"></i> Hạn: {{ formatDate(ass.deadline) }}
                       </span>
                    </div>
                  </div>
                  <p class="text-sm text-slate-600 font-medium leading-relaxed bg-slate-50/50 p-4 rounded-xl border border-slate-50 whitespace-pre-line mb-4">{{ ass.description }}</p>
                  
                  <!-- Submission Stats -->
                  <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                      <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-users"></i>
                      </div>
                      <span>Đã nộp: <span class="text-indigo-600">{{ ass.sub_count || 0 }}/{{ totalStudents }}</span></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                      <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-check-to-slot"></i>
                      </div>
                      <span>Đã chấm: <span class="text-emerald-600">{{ ass.graded_count || 0 }}/{{ ass.sub_count || 0 }}</span></span>
                    </div>
                    <div v-if="totalStudents > (ass.sub_count || 0)" class="flex items-center gap-2 text-xs font-bold text-slate-500">
                      <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-user-clock"></i>
                      </div>
                      <span>Chưa nộp: <span class="text-amber-600">{{ totalStudents - (ass.sub_count || 0) }}</span></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </template>

    <!-- Modals (Add Material / Add Assignment) -->
    <div v-if="isMaterialModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate__animated animate__fadeIn animate__faster">
      <div
        class="bg-white/95 backdrop-blur-3xl rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate__animated animate__zoomIn animate__faster border border-white">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-r from-emerald-50/50 to-teal-50/50">
          <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white flex items-center justify-center shadow-lg shadow-emerald-200 shrink-0">
              <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            Tải tài liệu lên
          </h3>
        </div>

        <div v-if="modalError"
          class="mx-8 mt-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700 text-sm font-medium">
          <i class="fa-solid fa-circle-exclamation text-lg"></i> {{ modalError }}
        </div>

        <form @submit.prevent="submitMaterial" class="p-8 space-y-6">
          <div class="group">
            <label
              class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 group-focus-within:text-emerald-600 transition-colors">Tên
              tài liệu <span class="text-red-500">*</span></label>
            <input v-model="materialForm.title" placeholder="VD: Slide Unit 1" required type="text"
              class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all hover:bg-slate-100" />
          </div>
          <div class="group">
            <label
              class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 group-focus-within:text-emerald-600 transition-colors">Link
              liên kết (Google Drive, vv) <span class="text-red-500">*</span></label>
            <input v-model="materialForm.file_url" placeholder="https://" required type="url"
              class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:bg-white transition-all hover:bg-slate-100" />
          </div>

          <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
            <button type="button" @click="isMaterialModalOpen = false; modalError = ''"
              class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all active:scale-95">Hủy</button>
            <button type="submit"
              class="px-8 py-3 rounded-xl font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 transition-all shadow-lg shadow-emerald-500/30 active:scale-95 flex items-center gap-2">
              <i class="fa-solid fa-floppy-disk"></i> Lưu
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isAssignmentModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate__animated animate__fadeIn animate__faster">
      <div
        class="bg-white/95 backdrop-blur-3xl rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden animate__animated animate__zoomIn animate__faster border border-white">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-r from-indigo-50/50 to-purple-50/50">
          <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-500 text-white flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
              <i class="fa-solid fa-pen-ruler"></i>
            </div>
            Giao bài tập về nhà
          </h3>
        </div>

        <div v-if="modalError"
          class="mx-8 mt-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-700 text-sm font-medium">
          <i class="fa-solid fa-circle-exclamation text-lg"></i> {{ modalError }}
        </div>

        <form @submit.prevent="submitAssignment" class="p-8 space-y-6">
          <div class="group">
            <label
              class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 group-focus-within:text-indigo-600 transition-colors">Tiêu
              đề <span class="text-red-500">*</span></label>
            <input v-model="assignmentForm.title" placeholder="VD: Homework Unit 1" required type="text"
              class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:bg-white transition-all hover:bg-slate-100" />
          </div>
          <div class="group">
            <label
              class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 group-focus-within:text-indigo-600 transition-colors">Yêu
              cầu / Hướng dẫn <span class="text-red-500">*</span></label>
            <textarea v-model="assignmentForm.description" placeholder="Làm bài tập trang..." rows="4" required
              class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:bg-white transition-all hover:bg-slate-100 resize-none"></textarea>
          </div>
          <div class="group">
            <label
              class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5 group-focus-within:text-indigo-600 transition-colors">Hạn
              chót nộp <span class="text-red-500">*</span></label>
            <input v-model="assignmentForm.deadline" required type="datetime-local"
              class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 focus:bg-white transition-all hover:bg-slate-100" />
          </div>

          <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
            <button type="button" @click="isAssignmentModalOpen = false; modalError = ''"
              class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all active:scale-95">Hủy</button>
            <button type="submit"
              class="px-8 py-3 rounded-xl font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 active:scale-95 flex items-center gap-2"
              style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)">
              <i class="fa-solid fa-paper-plane"></i> Giao bài
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { apiFetch } from '../../utils/api';

const route = useRoute();
const scheduleId = route.params.id;

const isLoading = ref(true);
const errorMessage = ref('');
const schedule = ref(null);
const materials = ref([]);
const assignments = ref([]);
const totalStudents = ref(0);

const activeTab = ref('overview');
const tabs = [
  { id: 'overview', name: 'Tổng quan', icon: 'fa-solid fa-layer-group' },
  { id: 'materials', name: 'Tài liệu In-class', icon: 'fa-solid fa-book-open' },
  { id: 'assignments', name: 'Bài tập Post-class', icon: 'fa-solid fa-file-signature' }
];

// Modal States
const isMaterialModalOpen = ref(false);
const materialForm = ref({ title: '', file_url: '', material_type: 'document' });

const isAssignmentModalOpen = ref(false);
const assignmentForm = ref({ title: '', description: '', deadline: '' });

const modalError = ref(''); // Error message for modals

const loadLessonData = async () => {
  isLoading.value = true;
  try {
    const res = await apiFetch(`admin/lesson_detail.php?id=${scheduleId}`);
    const result = await res.json();
    if (result.status === 'success') {
      schedule.value = result.schedule;
      materials.value = result.materials || [];
      assignments.value = result.assignments || [];
      totalStudents.value = result.total_students || 0;
    } else {
      errorMessage.value = result.message;
    }
  } catch (error) {
    errorMessage.value = 'Lỗi kết nối máy chủ.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => { loadLessonData(); });

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  if (isNaN(date.getTime())) return dateStr;

  const d = String(date.getDate()).padStart(2, '0');
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const y = date.getFullYear();

  if (dateStr.includes('T') || dateStr.includes(' ')) {
    const hh = String(date.getHours()).padStart(2, '0');
    const mm = String(date.getMinutes()).padStart(2, '0');
    return `${d}/${m}/${y} ${hh}:${mm}`;
  }

  return `${d}/${m}/${y}`;
};

// Gọi API Thêm Tài liệu
const submitMaterial = async () => {
  try {
    const payload = { action: 'add_material', schedule_id: scheduleId, ...materialForm.value };
    const res = await apiFetch('admin/lesson_detail.php', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      isMaterialModalOpen.value = false;
      modalError.value = '';
      materialForm.value = { title: '', file_url: '', material_type: 'document' };
      loadLessonData(); // Tải lại dữ liệu
    } else {
      modalError.value = result.message;
    }
  } catch (error) { modalError.value = "Lỗi kết nối khi thêm tài liệu."; }
};

// Gọi API Giao Bài tập
const submitAssignment = async () => {
  try {
    modalError.value = '';
    // Chuyển đổi định dạng giờ của HTML cho chuẩn MySQL (YYYY-MM-DD HH:mm:ss)
    const formattedDeadline = assignmentForm.value.deadline.replace('T', ' ') + ':00';
    const payload = { action: 'add_assignment', schedule_id: scheduleId, ...assignmentForm.value, deadline: formattedDeadline };

    const res = await apiFetch('admin/lesson_detail.php', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.status === 'success') {
      isAssignmentModalOpen.value = false;
      modalError.value = '';
      assignmentForm.value = { title: '', description: '', deadline: '' };
      loadLessonData(); // Tải lại dữ liệu
    } else {
      modalError.value = result.message;
    }
  } catch (error) { modalError.value = "Lỗi kết nối khi giao bài tập."; }
};
const isOverdue = (deadline) => {
  if (!deadline) return false;
  return new Date(deadline) < new Date();
};
</script>