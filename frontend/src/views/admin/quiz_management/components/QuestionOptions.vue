<template>
  <section class="rounded-[1.35rem] border border-slate-200 bg-slate-50 p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h4 class="text-sm font-black text-slate-900">Cấu hình đáp án</h4>
        <p class="mt-1 text-xs text-slate-500">
          {{ optionSectionDescription }}
        </p>
      </div>

      <button
        v-if="showAddButton"
        type="button"
        @click="addOption"
        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:text-emerald-600"
      >
        <i class="fa-solid fa-plus text-xs"></i>
        {{ addButtonLabel }}
      </button>
    </div>

    <div v-if="question.question_type === 'multiple_choice'" class="mt-4 space-y-3">
      <div
        v-for="(option, index) in question.options"
        :key="option.id ?? `mc-${index}`"
        class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_110px_44px]"
      >
        <input
          :value="option.option_text"
          type="text"
          :placeholder="`Nội dung đáp án ${index + 1}`"
          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          @input="updateOption(index, { option_text: $event.target.value })"
        />

        <label class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-600">
          <input
            :checked="Boolean(option.is_correct)"
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400"
            @change="updateOption(index, { is_correct: $event.target.checked })"
          />
          Đúng
        </label>

        <button
          type="button"
          @click="removeOption(index)"
          class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-red-200 text-red-500 transition hover:bg-red-50"
          title="Xóa đáp án"
        >
          <i class="fa-solid fa-trash-can text-xs"></i>
        </button>
      </div>
    </div>

    <div v-else-if="question.question_type === 'matching'" class="mt-4 space-y-3">
      <div
        v-for="(option, index) in question.options"
        :key="option.id ?? `pair-${index}`"
        class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_44px]"
      >
        <input
          :value="option.option_text"
          type="text"
          :placeholder="`Vế trái ${index + 1}`"
          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          @input="updateOption(index, { option_text: $event.target.value })"
        />

        <input
          :value="option.match_text"
          type="text"
          :placeholder="`Vế phải ${index + 1}`"
          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          @input="updateOption(index, { match_text: $event.target.value })"
        />

        <button
          type="button"
          @click="removeOption(index)"
          class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-red-200 text-red-500 transition hover:bg-red-50"
          title="Xóa cặp nối"
        >
          <i class="fa-solid fa-trash-can text-xs"></i>
        </button>
      </div>
    </div>

    <div v-else-if="isSingleAnswerType" class="mt-4 space-y-4">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
          Audio URL
          <span v-if="question.question_type === 'dictation'" class="text-red-500">*</span>
        </label>
        <input
          :value="question.audio_url"
          type="url"
          placeholder="https://example.com/audio.mp3"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          @input="patchQuestion({ audio_url: $event.target.value })"
        />
      </div>

      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Đáp án chính xác</label>
        <input
          :value="singleAnswerOption.option_text"
          type="text"
          placeholder="Nhập đáp án cần chấm đúng tuyệt đối"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          @input="setSingleAnswer($event.target.value)"
        />
      </div>
    </div>

    <div v-else class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm leading-relaxed text-slate-500">
      Dạng viết tự luận không cần danh sách đáp án mẫu. Giáo viên sẽ chấm thủ công sau khi học viên nộp bài.
    </div>
  </section>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:question"]);

const createOption = (overrides = {}) => ({
  id: null,
  option_text: "",
  match_text: "",
  is_correct: false,
  ...overrides,
});

const showAddButton = computed(() => {
  return props.question.question_type === "multiple_choice" || props.question.question_type === "matching";
});

const addButtonLabel = computed(() => {
  return props.question.question_type === "matching" ? "Thêm cặp" : "Thêm đáp án";
});

const optionSectionDescription = computed(() => {
  if (props.question.question_type === "multiple_choice") return "Mỗi lựa chọn có thể đánh dấu đúng hoặc sai.";
  if (props.question.question_type === "matching") return "Nhập dữ liệu ở hai cột để tạo từng cặp nối.";
  if (props.question.question_type === "fill_blank") return "Một đáp án đúng duy nhất cho câu điền từ.";
  if (props.question.question_type === "dictation") return "Yêu cầu audio URL và một đáp án chính xác để chấm.";
  return "Tự luận không cần danh sách đáp án mẫu.";
});

const isSingleAnswerType = computed(() => {
  return props.question.question_type === "fill_blank" || props.question.question_type === "dictation";
});

const singleAnswerOption = computed(() => {
  return props.question.options?.[0] ?? createOption({ is_correct: true });
});

const emitQuestion = (nextQuestion) => {
  emit("update:question", nextQuestion);
};

const patchQuestion = (patch) => {
  emitQuestion({
    ...props.question,
    ...patch,
  });
};

const updateOption = (index, patch) => {
  const nextOptions = (props.question.options || []).map((option, optionIndex) => {
    if (optionIndex !== index) {
      return option;
    }

    return {
      ...option,
      ...patch,
    };
  });

  patchQuestion({ options: nextOptions });
};

const addOption = () => {
  const nextOptions = [...(props.question.options || [])];
  nextOptions.push(createOption());
  patchQuestion({ options: nextOptions });
};

const removeOption = (index) => {
  const nextOptions = (props.question.options || []).filter((_, optionIndex) => optionIndex !== index);

  if (props.question.question_type === "multiple_choice" && nextOptions.length < 2) {
    return;
  }

  if (props.question.question_type === "matching" && nextOptions.length < 1) {
    return;
  }

  patchQuestion({ options: nextOptions });
};

const setSingleAnswer = (value) => {
  patchQuestion({
    options: [
      {
        ...singleAnswerOption.value,
        option_text: value,
        match_text: "",
        is_correct: true,
      },
    ],
  });
};
</script>
