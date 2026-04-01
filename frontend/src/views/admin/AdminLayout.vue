<template>
  <div class="min-h-screen bg-[#f4f8fb] text-slate-900">
    <div class="flex min-h-screen flex-col xl:flex-row">
      <aside
        class="w-full border-b border-slate-200 bg-white xl:min-h-screen xl:w-[310px] xl:border-b-0 xl:border-r"
      >
        <div class="border-b border-slate-100 px-6 py-6">
          <div class="flex items-center justify-center gap-4 w-full">
            <div>
              <p
                class="text-base font-bold uppercase tracking-[0.22em] text-[#16a34a]"
              >
                English Learning
              </p>
            </div>
          </div>
        </div>

        <div class="space-y-6 px-4 py-5">
          <section v-for="section in navSections" :key="section.title">
            <p
              class="mb-3 px-3 text-[0.7rem] font-bold uppercase tracking-[0.22em] text-slate-400"
            >
              {{ section.title }}
            </p>
            <div class="space-y-2">
              <component
                :is="item.to ? 'router-link' : 'button'"
                v-for="item in section.items"
                :key="item.label"
                :to="item.to"
                type="button"
                class="group flex w-full items-start gap-3 rounded-[1.35rem] border px-4 py-3.5 text-left transition"
                :class="navItemClass(item)"
              >
                <span
                  class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-base transition"
                  :class="navIconClass(item)"
                >
                  <i :class="item.icon"></i>
                </span>
                <span class="min-w-0 flex-1">
                  <span class="flex items-center gap-2">
                    <span class="block truncate text-sm font-bold">{{
                      item.label
                    }}</span>
                    <span
                      v-if="item.badge"
                      class="rounded-full bg-emerald-100 px-2 py-0.5 text-[0.62rem] font-bold uppercase tracking-[0.16em] text-[#16a34a]"
                    >
                      {{ item.badge }}
                    </span>
                  </span>
                  <span
                    class="mt-1 block text-xs leading-relaxed text-slate-500"
                    >{{ item.description }}</span
                  >
                </span>
              </component>
            </div>
          </section>
        </div>

        <div class="mt-auto p-4 flex flex-col items-center">
          <button
            @click="logout"
            class="mt-4 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
          >
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
          </button>
        </div>
      </aside>

      <main class="flex-1">
        <header
          class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur"
        >
          <div
            class="flex flex-wrap items-center justify-between gap-4 px-5 py-5 md:px-8"
          >
            <div>
              <p
                class="text-[0.7rem] font-bold uppercase tracking-[0.22em] text-[#16a34a]"
              >
                Bảng Điều Khiển
              </p>
              <h2
                class="mt-1 text-2xl font-black tracking-tight text-slate-900"
              >
                {{ pageTitle }}
              </h2>
              <p class="mt-1 text-sm text-slate-500">{{ pageDescription }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3"></div>
          </div>
        </header>

        <div class="px-4 py-4 md:px-6 md:py-6">
          <div
            class="rounded-[2rem] border border-slate-200 bg-white shadow-sm"
          >
            <router-view></router-view>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { authSession, clearAuthSession } from "../../utils/auth";

const router = useRouter();
const route = useRoute();

const currentUser = computed(() => authSession.value?.user ?? {});
const roleLabel = computed(() =>
  currentUser.value?.role === "admin" ? "Quản trị viên" : "Giảng viên",
);
const avatarUrl = computed(() => {
  const name = encodeURIComponent(currentUser.value?.full_name || "Admin");
  return `https://ui-avatars.com/api/?name=${name}&background=16a34a&color=ffffff`;
});

const navSections = [
  {
    title: "Tổng quan",
    items: [
      {
        label: "Trang tổng quan",
        icon: "fa-solid fa-chart-pie",
        to: "/admin",
        description: "Xem nhanh tình hình hoạt động và thống kê.",
      },
      {
        label: "Quản lý tài khoản",
        icon: "fa-solid fa-users-gear",
        to: "/admin/users",
        description: "Quản lý người dùng, phân quyền và trạng thái tài khoản.",
      },
    ],
  },
  {
    title: "Đào tạo",
    items: [
      {
        label: "Quản lý khóa học",
        icon: "fa-solid fa-book-open",
        to: "/admin/courses",
        description:
          "Quản lý danh sách khóa học, nội dung và trạng thái hiển thị.",
      },
      {
        label: "Quản lý danh mục khóa học",
        icon: "fa-solid fa-layer-group",
        to: "/admin/categories",
        description: "Sắp xếp khóa học theo danh mục, cấp độ và nhóm nội dung.",
      },
      {
        label: "Quản lý lộ trình học",
        icon: "fa-solid fa-route",
        to: "/admin/learning-paths",
        description:
          "Thiết lập lộ trình học theo mục tiêu và trình độ của học viên.",
      },
      {
        label: "Quản lý lớp học",
        icon: "fa-solid fa-users",
        to: "/admin/classes",
        description: "Quản lý danh sách lớp học, gắn khóa học và giáo viên chủ nhiệm.",
      },
      {
        label: "Ghi danh & Xếp lớp",
        icon: "fa-solid fa-user-plus",
        to: "/admin/enrollments",
        description: "Đăng ký ghi danh và xếp lớp, ca học cho học viên.",
      },
      {
        label: "Quản lý bài tập",
        icon: "fa-solid fa-file-pen",
        to: "/admin/quizzes",
        description: "Quản lý bài tập, quiz và tiến độ hoàn thành.",
      },
      {
        label: "Quản lý lịch giảng dạy",
        icon: "fa-regular fa-calendar-days",
        to: "/admin/schedules",
        description: "Theo dõi thông tin giảng viên và phân công giảng dạy.",
      },
    ],
  },
  {
    title: "Vận hành",
    items: [
      {
        label: "Quản lý đăng ký tư vấn",
        icon: "fa-solid fa-headset",
        to: "/admin/consultations",
        description: "Theo dõi danh sách đăng ký tư vấn và quá trình xử lý.",
      },
      {
        label: "Quản lý liên hệ",
        icon: "fa-solid fa-comments",
        to: "/admin/contacts",
        description: "Tiếp nhận và phản hồi các yêu cầu liên hệ từ website.",
      },
      {
        label: "Quản lý nội dung trang web",
        icon: "fa-solid fa-window-maximize",
        to: "/admin/content",
        description: "Cập nhật nội dung hiển thị cho các trang trên website.",
      },
    ],
  },
];

const routeMetaMap = {
  "/admin": {
    title: "Trang tổng quan",
    description:
      "Theo dõi nhanh các khu vực quản lý và định hướng phát triển tiếp theo.",
  },
  "/admin/users": {
    title: "Quản lý tài khoản",
    description:
      "Quản lý thông tin, vai trò và trạng thái của người dùng trong hệ thống.",
  },
  "/admin/courses": {
    title: "Quản lý khóa học",
    description:
      "Quản lý thông tin khóa học, danh mục, lộ trình và học phí.",
  },
  '/admin/categories': {
    title: "Quản lý danh mục khóa học",
    description:
      "Sắp xếp khóa học theo danh mục, cấp độ và nhóm nội dung.",
  },
  '/admin/learning-paths': {
    title: "Quản lý lộ trình học",
    description:
      "Thiết lập lộ trình học theo mục tiêu và trình độ của học viên.",
  },
  '/admin/classes': {
    title: "Quản lý lớp học",
    description: "Quản lý danh sách lớp học, gắn khóa học và giáo viên chủ nhiệm.",
  },
  "/admin/quizzes": {
    title: "Quản lý quiz",
    description: "Xây dựng quiz tương tác, cấu hình câu hỏi và đáp án theo từng bài học.",
  },
  "/admin/enrollments": {
    title: "Ghi danh & Xếp lớp",
    description: "Xếp học viên vào đợt khai giảng, chọn nhóm học và ca học phù hợp.",
  },
  "/admin/schedules": {
    title: "Quản lý lịch giảng dạy",
    description: "Theo dõi lịch dạy, lớp học và phân công giảng viên theo từng buổi.",
  },
  "/admin/consultations": {
    title: "Quản lý đăng ký tư vấn",
    description: "Theo dõi danh sách đăng ký tư vấn và quá trình xử lý.",
  },
  "/admin/contacts": {
    title: "Quản lý liên hệ",
    description: "Tiếp nhận và phản hồi các yêu cầu liên hệ từ website.",
  },
  "/admin/content": {
    title: "Quản lý nội dung trang web",
    description: "Cập nhật nội dung hiển thị cho các trang trên website.",
  },  
};

const pageTitle = computed(
  () => routeMetaMap[route.path]?.title || "Trang quản trị",
);
const pageDescription = computed(
  () =>
    routeMetaMap[route.path]?.description ||
    "Quản lý toàn bộ hệ thống tại một nơi.",
);
const todayLabel = computed(() =>
  new Intl.DateTimeFormat("vi-VN", {
    weekday: "long",
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  }).format(new Date()),
);

const isActive = (item) => item.to && route.path === item.to;

const navItemClass = (item) => {
  if (isActive(item)) {
    return "border-emerald-200 bg-gradient-to-r from-emerald-50 to-white text-slate-900 shadow-sm";
  }

  if (item.to) {
    return "border-transparent bg-white text-slate-700 hover:border-slate-200 hover:bg-slate-50";
  }

  return "cursor-default border-transparent bg-slate-50/80 text-slate-700";
};

const navIconClass = (item) => {
  if (isActive(item)) {
    return "bg-white text-[#16a34a] shadow-sm";
  }

  if (item.to) {
    return "bg-slate-100 text-slate-500 group-hover:bg-white";
  }

  return "bg-white text-slate-400";
};

const logout = () => {
  clearAuthSession();
  router.push("/login");
};
</script>

