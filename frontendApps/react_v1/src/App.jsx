import React from 'react';
import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import Layout from './components/Layout/Layout';
import DummyPage from './pages/DummyPage';
import SalesHistoryPage from './pages/SalesHistoryPage';
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<DummyPage />} />          {/* Default page */}
          <Route path="sales-history" element={<SalesHistoryPage />} />
        </Route>
      </Routes>
    </Router>
  );
}

export default App;
