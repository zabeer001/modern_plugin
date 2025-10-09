import React from 'react';

const MainContent = ({ title, children }) => {
  return (
    <div>
      <div className="breadcrumbs">
        <a href="/">Dashboard</a> <span>&gt;</span> {title}
      </div>
      {children}
    </div>
  );
};

export default MainContent;