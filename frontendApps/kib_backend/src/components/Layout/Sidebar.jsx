import React, { useEffect, useState } from "react";
import { NavLink } from "react-router-dom";

const Sidebar = () => {
  const [user, setUser] = useState(null);

  useEffect(() => {
    const storedUser = localStorage.getItem("auth_user");
    console.log(storedUser);
    
    if (storedUser) {
      try {
        const parsedUser = JSON.parse(storedUser);
        setUser(parsedUser);
      } catch (err) {
        console.error("Error parsing auth_user:", err);
      }
    } else {
      console.warn("No auth_user found in localStorage");
    }
  }, []);

  if (!user) {
    return (
      <div className="sidebar h-screen w-64 bg-gray-900 text-gray-100 flex items-center justify-center">
        <span className="text-gray-400 text-sm">No user data found.</span>
      </div>
    );
  }

  const sidebarLinks = [
    { to: "/admin/categories", label: "Categories" },
    { to: "/admin/orders", label: "Orders" },
  ];

  const vendorLinks = [{ to: "/vendor/my-orders", label: "My Orders" }];

  const isVendor = user?.roles?.includes("vendor");

  console.log(isVendor);
  

  
  
  const isAdmin = user?.roles?.includes("administrator");

  // ✅ Vendor Sidebar
  if (isVendor) {
    return (
      <div className="sidebar h-screen w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg">
        <div className="logo text-2xl font-extrabold text-center py-6 border-b border-gray-700">
          Vendor Panel
        </div>

        <div className="px-4 py-4 border-b border-gray-700">
          <div className="text-sm font-semibold">{user.display_name}</div>
          <div className="text-xs text-gray-400">{user.email}</div>
        </div>

        <nav className="flex-1 px-4 py-6">
          <ul className="space-y-2">
            {vendorLinks.map((link, index) => (
              <li key={index}>
                <NavLink
                  to={link.to}
                  className={({ isActive }) =>
                    `block px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 outline-none ${
                      isActive
                        ? "!bg-blue-600 !text-white focus:!bg-blue-700"
                        : "!text-gray-300 hover:!bg-gray-800 hover:!text-white"
                    }`
                  }
                >
                  {link.label}
                </NavLink>
              </li>
            ))}
          </ul>
        </nav>

        <div className="px-4 py-4 border-t border-gray-700 text-xs text-gray-500 text-center">
          © 2025 Vendor Panel
        </div>
      </div>
    );
  }

  // ✅ Admin Sidebar
  return (
    <div className="sidebar h-screen w-64 bg-gray-900 text-gray-100 flex flex-col shadow-lg">
      <div className="logo text-2xl font-extrabold text-center py-6 border-b border-gray-700">
        Admin Panel
      </div>

      <div className="px-4 py-4 border-b border-gray-700">
        <div className="text-sm font-semibold">{user.display_name}</div>
        <div className="text-xs text-gray-400">{user.email}</div>
      </div>

      <nav className="flex-1 px-4 py-6">
        <ul className="space-y-2">
          {sidebarLinks.map((link, index) => (
            <li key={index}>
              <NavLink
                to={link.to}
                className={({ isActive }) =>
                  `block px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 outline-none ${
                    isActive
                      ? "!bg-blue-600 !text-white focus:!bg-blue-700"
                      : "!text-gray-300 hover:!bg-gray-800 hover:!text-white"
                  }`
                }
              >
                {link.label}
              </NavLink>
            </li>
          ))}
        </ul>
      </nav>

      <div className="px-4 py-4 border-t border-gray-700 text-xs text-gray-500 text-center">
        © 2025 Admin Panel
      </div>
    </div>
  );
};

export default Sidebar;
