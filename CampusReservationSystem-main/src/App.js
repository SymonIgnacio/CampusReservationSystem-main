import React from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import AuthProvider from './context/AuthContext';
import EventProvider from './context/EventContext';
import './App.css';
import LoginPage from './Pages/LoginPage/loginPage';
import RegisterPage from './Pages/RegisterPage/registerPage';
import Dashboard from './Pages/Dashboard/dashboard';
import RequestEvent from './Pages/Request Event/requestEvent';
import Settings from './Pages/Settings/settings';
import Navbar from './Components/Navbar';

function AppContent() {
  const location = useLocation();
  
  // Show navbar on these routes
  const showNavbarRoutes = ['/dashboard', '/requestEvent', '/settings'];

  return (
    <>
      {showNavbarRoutes.includes(location.pathname) && <Navbar />}
      
      <Routes>
        <Route path="/" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/requestEvent" element={<RequestEvent />} />
        <Route path="/settings" element={<Settings />} />
      </Routes>
    </>
  );
}

function App() {
  return (
    <AuthProvider>
      <EventProvider>
        <Router>
          <AppContent />
        </Router>
      </EventProvider>
    </AuthProvider>
  );
}

export default App;