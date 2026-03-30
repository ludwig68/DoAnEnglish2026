import { reactive } from "vue";

export const confirmStore = reactive({
  visible: false,
  options: {
    title: "",
    message: "",
    confirmText: "Xác nhận",
    cancelText: "Hủy",
    tone: "danger",
  },
});

let confirmResolver = null;

const closeConfirm = (result) => {
  confirmStore.visible = false;

  if (confirmResolver) {
    confirmResolver(result);
    confirmResolver = null;
  }
};

export const acceptConfirm = () => closeConfirm(true);
export const cancelConfirm = () => closeConfirm(false);

export const openConfirm = (options) => {
  if (confirmResolver) {
    confirmResolver(false);
    confirmResolver = null;
  }

  const normalizedOptions =
    typeof options === "string" ? { message: options } : (options ?? {});

  confirmStore.options = {
    title: normalizedOptions.title || "Xác nhận thao tác",
    message: normalizedOptions.message || "Bạn có chắc chắn muốn tiếp tục?",
    confirmText: normalizedOptions.confirmText || "Xác nhận",
    cancelText: normalizedOptions.cancelText || "Hủy",
    tone: normalizedOptions.tone || "danger",
  };
  confirmStore.visible = true;

  return new Promise((resolve) => {
    confirmResolver = resolve;
  });
};
