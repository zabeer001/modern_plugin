import React from 'react';
import './Sidebar.css'; // optional, specific styles for sidebar

const Sidebar = () => {
  return (
    <div className="sidebar">
      <div className="logo"></div>
      <nav>
        <ul>
          <li><a href="#dashboard"><i className="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li className="active"><a href="#order"><i className="fas fa-shopping-cart"></i> Order</a></li>
          <li><a href="#active-products"><i className="fas fa-list-alt"></i> Active Product List</a></li>
          <li><a href="#pending-products"><i className="fas fa-hourglass-half"></i> Pending Product List</a></li>
          <li><a href="#my-sales"><i className="fas fa-chart-line"></i> My Sales</a></li>
          <li><a href="#setting"><i className="fas fa-cog"></i> Setting</a></li>
        </ul>
      </nav>
      <div className="logout">
        <a href="#logout"><i className="fas fa-sign-out-alt"></i> Log Out</a>
      </div>
    </div>
  );
};

export default Sidebar;