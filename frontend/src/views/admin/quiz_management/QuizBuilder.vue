<template>
  <div class="flex h-full min-h-0 flex-col gap-6 p-6 animate__animated animate__fadeIn">
    <div v-if="isBootstrapping" class="flex flex-1 items-center justify-center rounded-3xl border border-slate-100 bg-white">
      <div class="text-center text-slate-500">
        <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-100 border-t-emerald-500"></div>
        <p class="font-bold uppercase tracking-[0.18em]">Đang tải bộ tạo bài tập...</p>
      </div>
    </div>

    <div v-else class="grid flex-1 min-h-0 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
      <aside class="flex min-h-0 flex-col overflow-hidden rounded-[1.75rem] border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-5">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-emerald-500">Thư viện bài tập</p>
              <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Ngân hàng bài tập</h2>
              <p class="mt-1 text-sm text-slate-500">Chọn một bài tập để sửa hoặc tạo mới.</p>
            </div>
            <button
              type="button"
              @click="startNewQuiz"
              class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
              title="Tạo bài tập mới"
            >
              <i class="fa-solid fa-plus"></i>
            </button>
          </div>

          <div class="relative mt-4">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Tìm theo bài tập, khóa học hoặc bài học..."
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            />
          </div>
        </div>

        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">
          <span>{{ filteredQuizList.length }} bài tập</span>
          <span>{{ courses.length }} khóa học</span>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-3 custom-scrollbar">
          <button
            type="button"
            @click="startNewQuiz"
            class="mb-3 flex w-full items-start gap-3 rounded-2xl border border-dashed px-4 py-3 text-left transition"
            :class="!selectedQuizId ? 'border-emerald-200 bg-emerald-50 text-slate-900' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-emerald-200 hover:bg-emerald-50/60'"
          >
            <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-500 shadow-sm">
              <i class="fa-solid fa-sparkles"></i>
            </span>
            <span class="min-w-0 flex-1">
              <span class="block text-sm font-black">Bài tập mới</span>
              <span class="mt-1 block text-xs leading-relaxed text-slate-500">Tạo một bộ câu hỏi mới.</span>
            </span>
          </button>

          <div v-if="filteredQuizList.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
            Không tìm thấy bài tập nào.
          </div>

          <div v-else class="space-y-3">
            <button
              v-for="quiz in filteredQuizList"
              :key="quiz.id"
              type="button"
              @click="selectQuiz(quiz.id)"
              class="w-full rounded-2xl border px-4 py-3 text-left transition"
              :class="selectedQuizId === quiz.id ? 'border-emerald-200 bg-emerald-50 shadow-sm' : 'border-slate-200 bg-white hover:border-emerald-200 hover:bg-slate-50'"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-black text-slate-900">{{ quiz.title }}</p>
                  <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-500">
                    {{ quiz.course_title }} - Bai {{ quiz.lesson_order_number }}: {{ quiz.lesson_title }}
                  </p>
                </div>
                <span class="rounded-full bg-white px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-emerald-600 shadow-sm">
                  {{ quiz.question_count }} câu
                </span>
              </div>
              <div class="mt-3 flex items-center justify-between text-[0.72rem] font-semibold text-slate-400">
                <span>{{ categoryLabel(quiz.category) }}</span>
                <span>{{ formatDateTime(quiz.created_at) }}</span>
              </div>
            </button>
          </div>
        </div>
      </aside>

      <section class="flex min-h-0 flex-col overflow-hidden rounded-[1.75rem] border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
          <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
              <p class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-emerald-500">Tạo bài tập</p>
              <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                {{ form.id ? "Chỉnh sửa bài tập" : "Tạo bài tập tương tác mới" }}
              </h1>
              <p class="mt-1 text-sm text-slate-500">
                Tạo và quản lý các bài tập đánh giá cho khóa học.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                {{ form.questions.length }} câu hỏi
              </span>
              <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                {{ form.total_points || 0 }} điểm
              </span>
              <button
                type="button"
                @click="startNewQuiz"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50"
              >
                Làm mới form
              </button>
              <button
                v-if="form.id"
                type="button"
                @click="deleteCurrentQuiz"
                class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50"
              >
                Xóa bài tập
              </button>
              <button
                type="button"
                @click="saveQuiz"
                :disabled="isSaving"
                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-60"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
              >
                <i :class="isSaving ? 'fa-solid fa-spinner animate-spin' : 'fa-solid fa-floppy-disk'"></i>
                {{ isSaving ? "Đang lưu..." : form.id ? "Cập nhật" : "Lưu bài tập" }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="isQuizLoading" class="flex flex-1 items-center justify-center text-slate-500">
          <div class="text-center">
            <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-100 border-t-emerald-500"></div>
            <p class="font-bold uppercase tracking-[0.18em]">Đang tải bài tập...</p>
          </div>
        </div>

        <form v-else class="flex min-h-0 flex-1 flex-col" @submit.prevent="saveQuiz">
          <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-6 py-6 custom-scrollbar">
            <section class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h3 class="text-lg font-black text-slate-900">Thông tin chung</h3>
                  <p class="mt-1 text-sm text-slate-500">
                    Cài đặt các thông tin cơ bản cho bài tập.
                  </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-right shadow-sm">
                  <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-400">Tổng điểm thiết lập</p>
                  <p class="mt-1 text-xl font-black text-slate-900">{{ form.total_points || 0 }}</p>
                </div>
              </div>

              <!-- Hàng 1: Tiêu đề · Danh mục · Tổng điểm -->
              <div class="mt-5 grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Tiêu đề chiếm 2 cột -->
                <div class="lg:col-span-2">
                  <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Tiêu đề bài tập</label>
                  <input
                    v-model="form.title"
                    type="text"
                    placeholder="Ví dụ: Bài kiểm tra Unit 3"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                  />
                </div>

                <div>
                  <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Danh mục</label>
                  <select
                    v-model="form.category"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                  >
                    <option value="">Chọn danh mục</option>
                    <option v-for="category in quizCategories" :key="category.value" :value="category.value">
                      {{ category.label }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Tổng điểm</label>
                  <input
                    v-model.number="form.total_points"
                    type="number"
                    min="1"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                  />
                </div>
              </div>

              <!-- Hàng 2: Khóa học · Bài học -->
              <div class="mt-4 grid gap-4 grid-cols-1 sm:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Khóa học</label>
                  <select
                    :value="selectedCourseId"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                    @change="handleCourseChange($event.target.value)"
                  >
                    <option value="">Chọn khóa học</option>
                    <option v-for="course in courses" :key="course.id" :value="String(course.id)">
                      {{ course.label }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Bài học</label>
                  <select
                    :value="form.lesson_id"
                    :disabled="!selectedCourseId"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                    @change="handleLessonChange($event.target.value)"
                  >
                    <option value="">{{ selectedCourseId ? "Chọn bài học" : "Chọn khóa học trước" }}</option>
                    <option v-for="lesson in filteredLessons" :key="lesson.id" :value="String(lesson.id)">
                      {{ lesson.label }}
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-slate-400">
                    {{ selectedCourseId ? `${filteredLessons.length} bài học trong khóa đã chọn` : "Danh sách bài học sẽ lọc theo khóa học." }}
                  </p>
                </div>
              </div>

              <!-- Hàng 3: Mô tả (toàn chiều rộng) -->
              <div class="mt-4">
                <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Mô tả</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="Mô tả ngắn cho bài tập này..."
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
                ></textarea>
              </div>


              <div class="mt-4 grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                  <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-400">Khóa học đang gắn</p>
                  <p class="mt-1 text-sm font-semibold text-slate-700">{{ selectedCourseLabel }}</p>
                </div>
                <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                  <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-400">Bài học đang gắn</p>
                  <p class="mt-1 text-sm font-semibold text-slate-700">{{ selectedLessonLabel }}</p>
                </div>
                <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                  <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-400">Loại câu hỏi</p>
                  <p class="mt-1 text-sm font-semibold text-slate-700">{{ activeTypeSummary }}</p>
                </div>
                <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                  <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-400">Phạm vi áp dụng</p>
                  <p class="mt-1 text-sm font-semibold text-slate-700">{{ reuseSummary }}</p>
                </div>
              </div>
            </section>

            <section class="rounded-3xl border border-slate-100 bg-slate-50 p-5">
              <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                  <h3 class="text-lg font-black text-slate-900">Danh sách câu hỏi</h3>
                  <p class="mt-1 text-sm text-slate-500">Thêm nhanh các câu hỏi cho bài tập này.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <button
                    v-for="type in questionTypeOptions"
                    :key="type.value"
                    type="button"
                    @click="addQuestion(type.value)"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:text-emerald-600"
                  >
                    <i class="fa-solid fa-plus text-xs"></i>
                    {{ type.shortLabel }}
                  </button>
                </div>
              </div>

              <div v-if="form.questions.length === 0" class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500">
                Chưa có câu hỏi nào. Hãy thêm câu hỏi đầu tiên.
              </div>

              <div v-else class="mt-5 space-y-4">
                <QuestionCard
                  v-for="(question, questionIndex) in form.questions"
                  :key="question.localId"
                  :question="question"
                  :index="questionIndex"
                  @update:question="updateQuestion(questionIndex, $event)"
                  @remove="removeQuestion(questionIndex)"
                />
              </div>
            </section>
          </div>

          <div class="border-t border-slate-100 bg-white px-6 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <p class="text-sm text-slate-500">
                Bài tập hiện có {{ form.questions.length }} câu hỏi. Khi lưu, hệ thống sẽ gắn bài tập vào bài học đã chọn và dùng chung cho mọi lớp học thuộc khóa này.
              </p>
              <button
                type="submit"
                :disabled="isSaving"
                class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5 transition-all duration-300 disabled:cursor-not-allowed disabled:opacity-60"
                style="background: linear-gradient(135deg, #7ae582 0%, #16a34a 100%)"
              >
                <i :class="isSaving ? 'fa-solid fa-spinner animate-spin' : 'fa-solid fa-floppy-disk'"></i>
                {{ isSaving ? "Đang lưu..." : form.id ? "Lưu thay đổi" : "Tạo bài tập" }}
              </button>
            </div>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { openConfirm } from "../../../utils/confirm";
import { notifyError, notifySuccess, notifyWarning } from "../../../utils/notify";
import { getAuthToken } from "../../../utils/auth";
import { API_BASE_URL, buildApiUrl } from "../../../utils/api";
import QuestionCard from "./components/QuestionCard.vue";

const route = useRoute();
const router = useRouter();

const quizCategories = [
  { value: "summary", label: "Tổng hợp" },
  { value: "grammar", label: "Ngữ pháp" },
  { value: "vocabulary", label: "Từ vựng" },
  { value: "reading", label: "Đọc hiểu" },
  { value: "listening", label: "Nghe hiểu" },
  { value: "speaking", label: "Nói" },
  { value: "writing", label: "Viết" },
];

const questionTypeOptions = [
  { value: "multiple_choice", label: "Trắc nghiệm", shortLabel: "Trắc nghiệm" },
  { value: "matching", label: "Nối cặp", shortLabel: "Nối cặp" },
  { value: "fill_blank", label: "Điền từ", shortLabel: "Điền từ" },
  { value: "dictation", label: "Chép chính tả", shortLabel: "Chính tả" },
  { value: "writing", label: "Tự luận", shortLabel: "Tự luận" },
];

const typeLabelMap = questionTypeOptions.reduce((map, item) => {
  map[item.value] = item.label;
  return map;
}, {});

let localSeed = 0;

const isBootstrapping = ref(true);
const isQuizLoading = ref(false);
const isSaving = ref(false);
const searchQuery = ref("");
const courses = ref([]);
const lessons = ref([]);
const quizList = ref([]);
const selectedQuizId = ref(null);
const selectedCourseId = ref("");
// URL chuẩn: backend/api/admin/quiz_management/quiz_api.php
// Hoạt động trên cả localhost và production
const QUIZ_API_URL = buildApiUrl('admin/quiz_management/quiz_api.php');

const createLocalId = () => `question-${++localSeed}`;

const createOption = (overrides = {}) => ({
  id: null,
  localId: `option-${++localSeed}`,
  option_text: "",
  match_text: "",
  is_correct: false,
  ...overrides,
});

const deriveCoursesFromLessons = (lessonItems = []) => {
  const courseMap = new Map();

  lessonItems.forEach((lesson) => {
    const courseId = Number(lesson.course_id || 0);

    if (!courseId || courseMap.has(courseId)) {
      return;
    }

    const lessonCount = lessonItems.filter((item) => Number(item.course_id) === courseId).length;

    courseMap.set(courseId, {
      id: courseId,
      title: lesson.course_title || `Khóa học #${courseId}`,
      level: "",
      description: "",
      lesson_count: lessonCount,
      label: lesson.course_title || `Khóa học #${courseId}`,
    });
  });

  return [...courseMap.values()].sort((a, b) => a.title.localeCompare(b.title, "vi"));
};

const normalizeQuestionShape = (question, forcedType = question.question_type) => {
  const type = forcedType;
  const normalizedOptions = Array.isArray(question.options) ? question.options : [];

  if (type === "multiple_choice") {
    return {
      ...question,
      question_type: type,
      audio_url: "",
      options:
        normalizedOptions.length >= 2
          ? normalizedOptions.map((option) => createOption(option))
          : [createOption(), createOption()],
    };
  }

  if (type === "matching") {
    return {
      ...question,
      question_type: type,
      audio_url: "",
      options:
        normalizedOptions.length >= 1
          ? normalizedOptions.map((option) => createOption(option))
          : [createOption(), createOption()],
    };
  }

  if (type === "fill_blank" || type === "dictation") {
    const firstOption = normalizedOptions[0] ?? createOption();
    return {
      ...question,
      question_type: type,
      options: [createOption({ ...firstOption, is_correct: true, match_text: "" })],
    };
  }

  return {
    ...question,
    question_type: type,
    audio_url: "",
    options: [],
  };
};

const createQuestion = (type = "multiple_choice", overrides = {}) => {
  return normalizeQuestionShape(
    {
      id: null,
      localId: createLocalId(),
      question_type: type,
      question_text: "",
      audio_url: "",
      hint: "",
      explanation: "",
      options: [],
      ...overrides,
    },
    type,
  );
};

const createDefaultForm = () => ({
  id: null,
  title: "",
  lesson_id: "",
  category: "",
  total_points: 10,
  description: "",
  questions: [createQuestion("multiple_choice")],
});

const requestQuizApi = (url = QUIZ_API_URL, options = {}) => {
  const headers = new Headers(options.headers || {});
  const token = getAuthToken();

  if (token && !headers.has("Authorization")) {
    headers.set("Authorization", `Bearer ${token}`);
  }

  if (options.body && !headers.has("Content-Type")) {
    headers.set("Content-Type", "application/json");
  }

  return fetch(url, {
    ...options,
    headers,
  });
};

const readQuizApiResponse = async (response) => {
  const rawText = await response.text();

  try {
    return JSON.parse(rawText);
  } catch {
    const preview = rawText.trim().slice(0, 120);
    throw new Error(
      preview.startsWith("<")
        ? "API đang trả về HTML thay vì JSON. Hãy kiểm tra lại đường dẫn API/PHP."
        : "API trả về dữ liệu không hợp lệ.",
    );
  }
};

const form = ref(createDefaultForm());

const filteredLessons = computed(() => {
  if (!selectedCourseId.value) {
    return [];
  }

  return lessons.value.filter((lesson) => String(lesson.course_id) === String(selectedCourseId.value));
});

const selectedCourse = computed(() => {
  return courses.value.find((course) => String(course.id) === String(selectedCourseId.value)) || null;
});

const selectedCourseLabel = computed(() => {
  return selectedCourse.value?.label || "Chưa chọn khóa học";
});

const reuseSummary = computed(() => {
  if (!selectedCourseId.value || !form.value.lesson_id) {
    return "Áp dụng cho mọi lớp học";
  }

  return `Mọi lớp của khóa học này dùng chung bài tập này`;
});

const filteredQuizList = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase();
  if (!keyword) {
    return quizList.value;
  }

  return quizList.value.filter((quiz) =>
    [quiz.title, quiz.lesson_title, quiz.course_title, quiz.category]
      .filter(Boolean)
      .some((value) => String(value).toLowerCase().includes(keyword)),
  );
});

const selectedLessonLabel = computed(() => {
  const lesson = lessons.value.find((item) => String(item.id) === String(form.value.lesson_id));
  return lesson?.label || "Chưa chọn bài học";
});

const activeTypeSummary = computed(() => {
  if (form.value.questions.length === 0) {
    return "Chưa có câu hỏi";
  }

  const counts = form.value.questions.reduce((map, question) => {
    map[question.question_type] = (map[question.question_type] ?? 0) + 1;
    return map;
  }, {});

  return Object.entries(counts)
    .map(([type, count]) => `${typeLabelMap[type]}: ${count}`)
    .join(" - ");
});

const categoryLabel = (value) => {
  return quizCategories.find((item) => item.value === value)?.label || "Chưa phân loại";
};

const formatDateTime = (value) => {
  if (!value) return "Chưa có thời gian";

  return new Intl.DateTimeFormat("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  }).format(new Date(value));
};

const syncCourseFromLesson = (lessonId, fallbackCourseId = "") => {
  const lesson = lessons.value.find((item) => String(item.id) === String(lessonId));
  selectedCourseId.value = lesson ? String(lesson.course_id) : String(fallbackCourseId || "");
};

const hydrateForm = (quiz) => {
  form.value = {
    id: quiz.id,
    title: quiz.title ?? "",
    lesson_id: String(quiz.lesson_id ?? ""),
    category: quiz.category ?? "",
    total_points: Number(quiz.total_points ?? 0),
    description: quiz.description ?? "",
    questions:
      Array.isArray(quiz.questions) && quiz.questions.length > 0
        ? quiz.questions.map((question) =>
            normalizeQuestionShape(
              {
                id: question.id ?? null,
                localId: createLocalId(),
                question_type: question.question_type ?? "multiple_choice",
                question_text: question.question_text ?? "",
                audio_url: question.audio_url ?? "",
                hint: question.hint ?? "",
                explanation: question.explanation ?? "",
                options: Array.isArray(question.options)
                  ? question.options.map((option) =>
                      createOption({
                        id: option.id ?? null,
                        option_text: option.option_text ?? "",
                        match_text: option.match_text ?? "",
                        is_correct: Boolean(option.is_correct),
                      }),
                    )
                  : [],
              },
              question.question_type ?? "multiple_choice",
            ),
          )
        : [createQuestion("multiple_choice")],
  };
  syncCourseFromLesson(form.value.lesson_id, quiz.course_id ?? "");
  selectedQuizId.value = quiz.id;
};

const syncQuizQuery = async (quizId = null) => {
  const nextQuery = { ...route.query };
  if (quizId) {
    nextQuery.quiz_id = String(quizId);
  } else {
    delete nextQuery.quiz_id;
  }

  await router.replace({ query: nextQuery });
};

const loadBuilderMeta = async () => {
  const response = await requestQuizApi();
  const result = await readQuizApiResponse(response);

  if (result.status !== "success") {
    throw new Error(result.message || "Không thể tải danh sách bài tập.");
  }

  const nextLessons = result.data?.lessons || [];
  lessons.value = nextLessons;
  courses.value = result.data?.courses?.length ? result.data.courses : deriveCoursesFromLessons(nextLessons);
  quizList.value = result.data?.quizzes || [];

  if (form.value.lesson_id) {
    syncCourseFromLesson(form.value.lesson_id, selectedCourseId.value);
  }
};

const loadQuiz = async (quizId) => {
  if (!quizId) return;

  isQuizLoading.value = true;

  try {
    const response = await requestQuizApi(`${QUIZ_API_URL}?quiz_id=${quizId}`);
    const result = await readQuizApiResponse(response);

    if (result.status !== "success" || !result.data) {
      throw new Error(result.message || "Không thể tải bài tập.");
    }

    hydrateForm(result.data);
    await syncQuizQuery(result.data.id);
  } catch (error) {
    notifyError(error.message || "Không thể tải bài tập.");
  } finally {
    isQuizLoading.value = false;
  }
};

const startNewQuiz = async () => {
  form.value = createDefaultForm();
  selectedCourseId.value = "";
  selectedQuizId.value = null;
  await syncQuizQuery(null);
};

const selectQuiz = async (quizId) => {
  if (selectedQuizId.value === quizId) {
    return;
  }

  await loadQuiz(quizId);
};

const handleCourseChange = (courseId) => {
  selectedCourseId.value = String(courseId || "");

  const currentLesson = lessons.value.find((lesson) => String(lesson.id) === String(form.value.lesson_id));
  if (!currentLesson || String(currentLesson.course_id) !== String(selectedCourseId.value)) {
    form.value.lesson_id = "";
  }
};

const handleLessonChange = (lessonId) => {
  form.value.lesson_id = String(lessonId || "");

  if (form.value.lesson_id) {
    syncCourseFromLesson(form.value.lesson_id, selectedCourseId.value);
  }
};

const addQuestion = (type = "multiple_choice") => {
  form.value.questions.push(createQuestion(type));
};

const updateQuestion = (index, nextQuestion) => {
  form.value.questions.splice(index, 1, nextQuestion);
};

const removeQuestion = (index) => {
  if (form.value.questions.length === 1) {
    notifyWarning("Cần tối thiểu một câu hỏi.");
    return;
  }

  form.value.questions.splice(index, 1);
};

const buildPayload = () => ({
  id: form.value.id,
  title: String(form.value.title || "").trim(),
  lesson_id: Number(form.value.lesson_id),
  category: form.value.category,
  total_points: Number(form.value.total_points),
  description: String(form.value.description || "").trim(),
  questions: form.value.questions.map((question) => ({
    id: question.id,
    question_type: question.question_type,
    question_text: String(question.question_text || "").trim(),
    audio_url: String(question.audio_url || "").trim(),
    hint: String(question.hint || "").trim(),
    explanation: String(question.explanation || "").trim(),
    options: Array.isArray(question.options)
      ? question.options.map((option) => ({
          id: option.id,
          option_text: String(option.option_text || "").trim(),
          match_text: String(option.match_text || "").trim(),
          is_correct: Boolean(option.is_correct),
        }))
      : [],
  })),
});

const saveQuiz = async () => {
  if (isSaving.value) return;

  if (!selectedCourseId.value) {
    notifyWarning("Hãy chọn khóa học.");
    return;
  }

  if (!form.value.lesson_id) {
    notifyWarning("Hãy chọn bài học.");
    return;
  }

  isSaving.value = true;

  try {
    const method = form.value.id ? "PUT" : "POST";
    const endpoint = form.value.id
      ? `${QUIZ_API_URL}?id=${form.value.id}`
      : QUIZ_API_URL;

    const response = await requestQuizApi(endpoint, {
      method,
      body: JSON.stringify(buildPayload()),
    });
    const result = await readQuizApiResponse(response);

    if (result.status !== "success" || !result.data) {
      throw new Error(result.message || "Không thể lưu bài tập.");
    }

    hydrateForm(result.data);
    await loadBuilderMeta();
    await syncQuizQuery(result.data.id);
    notifySuccess(result.message || "Lưu bài tập thành công.");
  } catch (error) {
    notifyError(error.message || "Không thể lưu bài tập.");
  } finally {
    isSaving.value = false;
  }
};

const deleteCurrentQuiz = async () => {
  if (!form.value.id) return;

  const confirmed = await openConfirm({
    title: "Xóa bài tập",
    message: "Bài tập này sẽ bị xóa cùng toàn bộ câu hỏi và đáp án liên quan. Tiếp tục?",
    confirmText: "Xóa",
    cancelText: "Hủy",
    tone: "danger",
  });

  if (!confirmed) return;

  try {
    const response = await requestQuizApi(`${QUIZ_API_URL}?id=${form.value.id}`, {
      method: "DELETE",
    });
    const result = await readQuizApiResponse(response);

    if (result.status !== "success") {
      throw new Error(result.message || "Không thể xóa bài tập.");
    }

    notifySuccess(result.message || "Xóa bài tập thành công.");
    await loadBuilderMeta();
    await startNewQuiz();
  } catch (error) {
    notifyError(error.message || "Không thể xóa bài tập.");
  }
};

onMounted(async () => {
  try {
    await loadBuilderMeta();

    const initialQuizId = Number(route.query.quiz_id || 0);
    if (initialQuizId > 0) {
      await loadQuiz(initialQuizId);
    }
  } catch (error) {
    notifyError(error.message || "Không thể khởi tạo module.");
  } finally {
    isBootstrapping.value = false;
  }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 999px;
}
</style>








