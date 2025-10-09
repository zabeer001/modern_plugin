import React from 'react';
import { NavLink } from 'react-router-dom';


const sidebarLinks = [
  { to: '/', label: 'Dashboard', end: true },
  { to: '/sales-history', label: 'Order' },

];

const Sidebar = () => {
  return (
    <div className="sidebar">
      <div className="logo">React Admin</div>
      <nav>
        <ul>
          {sidebarLinks.map((link, index) => (
            <li key={index}>
              <NavLink
                to={link.to}
                end={link.end || false}
                className={({ isActive }) => (isActive ? 'active' : '')}
              >
                {link.label}
              </NavLink>
            </li>
          ))}
        </ul>
      </nav>
    </div>
  );
};

export default Sidebar;


