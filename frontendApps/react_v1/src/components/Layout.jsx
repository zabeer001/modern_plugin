import React from 'react';
import Sidebar from './Sidebar';
import MainContent from './MainContent';


const Layout = () => {
  return (
    <div className="App flex">
      <div className="container flex">
        <Sidebar />
        <MainContent />
      </div>
    </div>
  );
};

export default Layout;