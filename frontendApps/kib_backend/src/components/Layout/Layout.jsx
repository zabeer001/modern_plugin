import React from "react";
import Sidebar from "./Sidebar";
import { Outlet } from "react-router-dom";

const Layout = () => {
  console.log("Layout component rendered");

  return (
    <div className="flex min-h-screen bg-gray-50 text-gray-800">
      {/* Sidebar (fixed on the left) */}
      <Sidebar />

      {/* Main content area */}
      <div className="flex-1 p-6 md:p-8 lg:p-10 xl:p-12 overflow-y-auto">
        <Outlet />
      </div>
    </div>
  );
};

export default Layout;
