import { reactive } from "vue";

const DEFAULT_DURATION = 4000;
const MAX_TOASTS = 5;

let toastSeed = 0;

export const notificationStore = reactive({
  items: [],
});

const normalizeMessage = (message, fallback) => {
  const value = String(message ?? "").trim();
  return value || fallback;
};

const normalizeForMatch = (message) =>
  String(message ?? "")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase();

export const dismissNotification = (id) => {
  const index = notificationStore.items.findIndex((item) => item.id === id);
  if (index >= 0) {
    notificationStore.items.splice(index, 1);
  }
};

export const notify = ({
  type = "info",
  title = "",
  message = "",
  duration = DEFAULT_DURATION,
} = {}) => {
  const item = {
    id: ++toastSeed,
    type,
    title: normalizeMessage(title, type === "error" ? "Có lỗi xảy ra" : "Thông báo"),
    message: normalizeMessage(message, "Hệ thống vừa gửi một thông báo mới."),
  };

  notificationStore.items.unshift(item);
  if (notificationStore.items.length > MAX_TOASTS) {
    notificationStore.items.splice(MAX_TOASTS);
  }

  if (duration > 0) {
    window.setTimeout(() => dismissNotification(item.id), duration);
  }

  return item.id;
};

export const notifySuccess = (message, title = "Thành công") =>
  notify({ type: "success", title, message });

export const notifyError = (message, title = "Có lỗi xảy ra") =>
  notify({ type: "error", title, message });

export const notifyWarning = (message, title = "Cần lưu ý") =>
  notify({ type: "warning", title, message });

export const notifyInfo = (message, title = "Thông báo") =>
  notify({ type: "info", title, message });

export const inferNotificationType = (message) => {
  const value = normalizeForMatch(message);

  if (
    value.includes("thanh cong") ||
    value.includes("luu thanh cong") ||
    value.includes("da xoa") ||
    value.includes("da tao") ||
    value.includes("da cap nhat")
  ) {
    return "success";
  }

  if (
    value.includes("vui long") ||
    value.includes("canh bao") ||
    value.includes("luu y") ||
    value.includes("khong hop le")
  ) {
    return "warning";
  }

  if (
    value.includes("loi") ||
    value.includes("khong the") ||
    value.includes("that bai") ||
    value.includes("khong ket noi")
  ) {
    return "error";
  }

  return "info";
};

export const notifyFromAlertMessage = (message) => {
  const type = inferNotificationType(message);

  if (type === "success") {
    return notifySuccess(message);
  }

  if (type === "warning") {
    return notifyWarning(message);
  }

  if (type === "error") {
    return notifyError(message);
  }

  return notifyInfo(message);
};
