import React, { createContext, useState, useEffect } from 'react';

// Create the AuthContext
export const AuthContext = createContext();

// Base API URL - adjust this to match your server configuration
const API_BASE_URL = 'http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api';

// AuthContext Provider Component
const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Check if user is already logged in (via session)
    useEffect(() => {
        const checkAuthStatus = async () => {
            try {
                // This would typically check a session endpoint
                // For now, we'll just check localStorage
                const savedUser = localStorage.getItem('user');
                if (savedUser) {
                    setUser(JSON.parse(savedUser));
                }
            } catch (error) {
                console.error('Error checking auth status:', error);
            }
        };

        checkAuthStatus();
    }, []);

    // Login function that connects to your actual database
    const login = async (credentials) => {
        setLoading(true);
        setError(null);
        
        try {
            console.log('Attempting login with:', credentials);
            console.log('API URL:', `${API_BASE_URL}/login.php`);
            
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(credentials),
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Login response:', data);
            
            if (data.success) {
                console.log('Login successful:', data.user);
                setUser(data.user);
                // Save user to localStorage for persistence
                localStorage.setItem('user', JSON.stringify(data.user));
                return { success: true, user: data.user };
            } else {
                console.error('Login failed:', data.message);
                setError(data.message || 'Login failed');
                return { success: false, message: data.message || 'Login failed' };
            }
        } catch (error) {
            console.error('Error during login:', error);
            setError('Connection error. Please try again.');
            return { success: false, message: 'Connection error. Please check your API endpoint.' };
        } finally {
            setLoading(false);
        }
    };

    const logout = async () => {
        setLoading(true);
        try {
            // Clear user from state
            setUser(null);
            // Remove from localStorage
            localStorage.removeItem('user');
            
            // In a real app, you would also call a logout endpoint
            // to invalidate the session on the server
            
            return { success: true };
        } catch (error) {
            console.error('Error during logout:', error);
            return { success: false, message: 'Logout failed' };
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthContext.Provider value={{ 
            user, 
            loading, 
            error,
            login, 
            logout,
            isAuthenticated: !!user
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export default AuthProvider;