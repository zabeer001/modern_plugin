import React, { useEffect, useState } from "react";
import { HashRouter as Router, Routes, Route, Navigate } from "react-router-dom";
import Layout from "./components/Layout/Layout";
import OrderIndexPage from "./pages/orders/OrderIndexPage";
import CategoriesIndexPage from "./pages/categories/CategoriesIndexPage";
import UsersIndexPage from "./pages/users/UsersIndexPage";
import OrderViewPage from "./pages/orders/OrderViewPage";
import OrderEditPage from "./pages/orders/OrderEditPage";
import { ToastContainer } from "react-toastify";
import MyOrderPage from "./pages/orders/MyOrderPage";
import CategoriesCreatePage from "./pages/categories/CategoriesCreatePage";
import CategoriesEditPage from "./pages/categories/CategoriesEditPage";

function App() {
  const [role, setRole] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // ✅ Get stored token and user info
    const storedToken = localStorage.getItem("jwt_token");
    const storedUser = JSON.parse(localStorage.getItem("auth_user") || "{}");

    // ✅ Extract first role safely from array
    const userRole = storedUser?.roles?.[0] || null;

    setToken(storedToken);
    setRole(userRole);
    setLoading(false);
  }, []);

  if (loading) {
    return (
      <div className="flex h-screen items-center justify-center text-gray-500 text-sm">
        Loading...
      </div>
    );
  }

  // 🔒 Redirect to login if not logged in
  if (!token) {
    window.location.href = "/login"; // or your WordPress login URL
    return null;
  }

  return (
    <Router>
      <Routes>
        <Route path="/" element={<Layout />}>
          {/* ✅ If vendor, redirect root to /vendor/my-orders */}
          {role === "vendor" ? (
            <Route index element={<Navigate to="/vendor/my-orders" replace />} />
          ) : (
            <Route index element={<OrderIndexPage />} />
          )}

          {/* ✅ Admin routes */}
          {role !== "vendor" && (
            <>
              <Route path="admin/users" element={<UsersIndexPage />} />
              <Route path="admin/orders" element={<OrderIndexPage />} />
              <Route path="admin/orders/:uniq_id" element={<OrderViewPage />} />
              <Route path="admin/orders/edit/:uniq_id" element={<OrderEditPage />} />
              <Route path="admin/categories" element={<CategoriesIndexPage />} />
              <Route path="admin/categories/create" element={<CategoriesCreatePage />} />
              <Route path="admin/categories/edit/:id" element={<CategoriesEditPage />} />
            </>
          )}

          {/* ✅ Vendor routes */}
          {role === "vendor" && (
            <Route path="vendor/my-orders" element={<MyOrderPage />} />
          )}

          {/* Fallback for unauthorized routes */}
          {role !== "vendor" && (
            <Route path="vendor/*" element={<Navigate to="/" replace />} />
          )}
        </Route>
      </Routes>

      <ToastContainer position="top-right" autoClose={3000} />
    </Router>
  );
}

export default App;
