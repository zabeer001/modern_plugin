import React from 'react';
import Sidebar from './Sidebar';
import { Outlet } from 'react-router-dom';


const Layout = () => {
  return (
    <div className="App flex">
      <div className="container flex">
        <Sidebar />
        <div className="main-content">
          <Outlet /> {/* Renders the current page based on route */}
        </div>
      </div>
    </div>
  );
};

export default Layout;