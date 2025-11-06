import { toast } from "react-toastify";
import { CheckCircle, XCircle, Info } from "lucide-react";

// 🎨 Elegant base style
const baseToastStyle = {
  borderRadius: "12px",
  padding: "14px 18px",
  fontSize: "0.95rem",
  fontWeight: 600,
  color: "#fff",
  boxShadow: "0 4px 18px rgba(0,0,0,0.15)",
  display: "flex",
  alignItems: "center",
  backdropFilter: "blur(8px)",
  border: "1px solid rgba(255,255,255,0.08)",
  minHeight: "64px",
};

// ⚙️ Common config
const toastConfig = {
  position: "top-right",
  autoClose: 3500,
  hideProgressBar: false,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  // ✅ White progress bar that always stays visible
  progressStyle: {
    background: "#ffffff !important", // ✅ fully enforced white
    height: "3px",
    opacity: 1,
  },
};

// ✅ Success Toast
export const showSuccessToast = (message) => {
  toast.success(message || "Success!", {
    ...toastConfig,
    icon: <CheckCircle size={22} color="white" />,
    style: {
      ...baseToastStyle,
      background: "linear-gradient(135deg, #22c55e 0%, #16a34a 100%)",
      borderLeft: "5px solid #15803d",
    },
  });
};

// ❌ Error Toast
export const showErrorToast = (message) => {
  toast.error(message || "Something went wrong!", {
    ...toastConfig,
    icon: <XCircle size={22} color="white" />,
    style: {
      ...baseToastStyle,
      background: "linear-gradient(135deg, #ef4444 0%, #dc2626 100%)",
      borderLeft: "5px solid #b91c1c",
    },
  });
};

// ℹ️ Info Toast
export const showInfoToast = (message) => {
  toast.info(message || "FYI", {
    ...toastConfig,
    icon: <Info size={22} color="white" />,
    style: {
      ...baseToastStyle,
      background: "linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)",
      borderLeft: "5px solid #1e40af",
    },
  });
};
