<template>
  <article class="overflow-hidden rounded-[1.6rem] border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 bg-slate-50 px-5 py-4">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex items-center gap-3">
          <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">
            {{ index + 1 }}
          </span>
          <div>
            <p class="text-sm font-black text-slate-900">Câu hỏi {{ index + 1 }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-500">{{ questionTypeLabel(question.question_type) }}</p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <select
            :value="question.question_type"
            @change="handleTypeChange($event.target.value)"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          >
            <option v-for="type in questionTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select>
          <button
            type="button"
            @click="$emit('remove')"
            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50"
          >
            <i class="fa-solid fa-trash-can text-xs"></i>
            Xóa câu
          </button>
        </div>
      </div>
    </div>

    <div class="space-y-5 p-5">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Nội dung câu hỏi</label>
        <textarea
          :value="question.question_text"
          rows="3"
          placeholder="Nhập nội dung câu hỏi..."
          class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
          @input="patchQuestion({ question_text: $event.target.value })"
        ></textarea>
      </div>

      <div class="grid gap-4 lg:grid-cols-2">
        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Gợi ý</label>
          <input
            :value="question.hint"
            type="text"
            placeholder="Gợi ý ngắn cho học viên"
            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            @input="patchQuestion({ hint: $event.target.value })"
          />
        </div>

        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Giải thích</label>
          <input
            :value="question.explanation"
            type="text"
            placeholder="Giải thích sau khi làm xong"
            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#7AE582]"
            @input="patchQuestion({ explanation: $event.target.value })"
          />
        </div>
      </div>

      <QuestionOptions :question="question" @update:question="emitQuestion" />
    </div>
  </article>
</template>

<script setup>
import QuestionOptions from "./QuestionOptions.vue";

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["update:question", "remove"]);

const questionTypes = [
  { value: "multiple_choice", label: "Trắc nghiệm" },
  { value: "matching", label: "Nối cặp" },
  { value: "fill_blank", label: "Điền từ" },
  { value: "dictation", label: "Chép chính tả" },
  { value: "writing", label: "Tự luận" },
];

const createOption = (overrides = {}) => ({
  id: null,
  option_text: "",
  match_text: "",
  is_correct: false,
  ...overrides,
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

const questionTypeLabel = (type) => {
  return questionTypes.find((item) => item.value === type)?.label || "Câu hỏi";
};

const normalizeOptionsForType = (type) => {
  if (type === "multiple_choice") {
    return [createOption(), createOption()];
  }

  if (type === "matching") {
    return [createOption(), createOption()];
  }

  if (type === "fill_blank" || type === "dictation") {
    return [createOption({ is_correct: true })];
  }

  return [];
};

const handleTypeChange = (nextType) => {
  const nextQuestion = {
    ...props.question,
    question_type: nextType,
    options: normalizeOptionsForType(nextType),
  };

  if (nextType === "multiple_choice" || nextType === "matching" || nextType === "writing") {
    nextQuestion.audio_url = "";
  }

  emitQuestion(nextQuestion);
};
</script>
