import React, { useContext } from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation, Navigate } from 'react-router-dom';
import AuthProvider, { AuthContext } from './context/AuthContext';
import EventProvider from './context/EventContext';
import './App.css';
import LoginPage from './Pages/LoginPage/loginPage';
import RegisterPage from './Pages/RegisterPage/registerPage';
import Dashboard from './Pages/Dashboard/dashboard';
import RequestEvent from './Pages/Request Event/requestEvent';
import Settings from './Pages/Settings/settings';
import Navbar from './Components/Navbar';

// Import admin pages - you'll need to create these
// For now, we'll use the Dashboard component as a placeholder
// Replace these with your actual admin components when you create them
const AdminDashboard = Dashboard;
const ManageReservations = () => <div>Manage Reservations Page</div>;
const ManageUsers = () => <div>Manage Users Page</div>;
const AdminSettings = Settings;

// Protected route component
const ProtectedRoute = ({ element, requiredRole }) => {
  const { user, isAuthenticated } = useContext(AuthContext);
  
  if (!isAuthenticated) {
    return <Navigate to="/" replace />;
  }
  
  if (requiredRole && user?.role !== requiredRole) {
    return <Navigate to="/dashboard" replace />;
  }
  
  return element;
};

function AppContent() {
  const location = useLocation();
  const { user } = useContext(AuthContext);
  
  // Check if current route is an admin route
  const isAdminRoute = location.pathname.startsWith('/admin');
  
  // Show navbar on these routes
  const clientNavbarRoutes = ['/dashboard', '/requestEvent', '/settings'];
  const adminNavbarRoutes = ['/admin/dashboard', '/admin/manage-reservations', '/admin/users', '/admin/settings'];
  
  // Determine if navbar should be shown
  const showNavbar = clientNavbarRoutes.includes(location.pathname) || adminNavbarRoutes.includes(location.pathname);

  return (
    <>
      {showNavbar && <Navbar isAdminPage={isAdminRoute} />}
      
      <Routes>
        {/* Public routes */}
        <Route path="/" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        
        {/* Client routes */}
        <Route path="/dashboard" element={
          <ProtectedRoute element={<Dashboard />} />
        } />
        <Route path="/requestEvent" element={
          <ProtectedRoute element={<RequestEvent />} />
        } />
        <Route path="/settings" element={
          <ProtectedRoute element={<Settings />} />
        } />
        
        {/* Admin routes */}
        <Route path="/admin/dashboard" element={
          <ProtectedRoute element={<AdminDashboard />} requiredRole="admin" />
        } />
        <Route path="/admin/manage-reservations" element={
          <ProtectedRoute element={<ManageReservations />} requiredRole="admin" />
        } />
        <Route path="/admin/users" element={
          <ProtectedRoute element={<ManageUsers />} requiredRole="admin" />
        } />
        <Route path="/admin/settings" element={
          <ProtectedRoute element={<AdminSettings />} requiredRole="admin" />
        } />
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