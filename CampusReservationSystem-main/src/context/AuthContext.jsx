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

    // Check if user is already logged in (via localStorage or session)
    useEffect(() => {
        const checkAuthStatus = async () => {
            try {
                // First check localStorage
                const savedUser = localStorage.getItem('user');
                if (savedUser) {
                    setUser(JSON.parse(savedUser));
                    return;
                }
                
                // If not in localStorage, check with the server
                setLoading(true);
                const response = await fetch(`${API_BASE_URL}/getUser.php`, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache'
                    },
                    credentials: 'include' // Include cookies for session
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.user) {
                        setUser(data.user);
                        // Save to localStorage for future use
                        localStorage.setItem('user', JSON.stringify(data.user));
                    }
                }
            } catch (error) {
                console.error('Error checking auth status:', error);
            } finally {
                setLoading(false);
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
            
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                body: JSON.stringify(credentials),
                credentials: 'include' // Include cookies for session
            });
            
            console.log('Response status:', response.status);
            
            // Get the response text first for debugging
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            // Try to parse the response as JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parsing JSON:', e);
                throw new Error('Invalid JSON response from server');
            }
            
            console.log('Parsed response:', data);
            
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
            // Try to call logout endpoint if it exists
            try {
                await fetch(`${API_BASE_URL}/logout.php`, {
                    method: 'POST',
                    credentials: 'include'
                });
            } catch (e) {
                console.log('No server-side logout endpoint available');
            }
            
            // Clear user from state
            setUser(null);
            // Remove from localStorage
            localStorage.removeItem('user');
            
            return { success: true };
        } catch (error) {
            console.error('Error during logout:', error);
            return { success: false, message: 'Logout failed' };
        } finally {
            setLoading(false);
        }
    };

    // Function to get current user profile
    const getCurrentUser = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}/getUser.php`, {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache'
                },
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.user) {
                    // Update user state with latest data
                    setUser(data.user);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    return data.user;
                }
            }
            return null;
        } catch (error) {
            console.error('Error getting current user:', error);
            return null;
        }
    };

    return (
        <AuthContext.Provider value={{ 
            user, 
            loading, 
            error,
            login, 
            logout,
            getCurrentUser,
            isAuthenticated: !!user
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export default AuthProvider;