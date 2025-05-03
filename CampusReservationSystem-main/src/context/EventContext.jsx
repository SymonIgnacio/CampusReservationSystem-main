import React, { createContext, useState, useEffect } from 'react';

// Create the EventContext
export const EventContext = createContext();

// Base API URL - adjust this to match your server configuration
const API_BASE_URL = 'http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api';

// EventContext Provider Component
const EventProvider = ({ children }) => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [stats, setStats] = useState({
        total: 0,
        pending: 0,
        declined: 0,
        approved: 0
    });

    // Load events from the database
    const fetchEvents = async () => {
        setLoading(true);
        setError(null);
        try {
            console.log('Fetching events from:', `${API_BASE_URL}/events.php`);
            const response = await fetch(`${API_BASE_URL}/events.php`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    console.log('Events loaded:', data.events);
                    setEvents(data.events);
                } else {
                    throw new Error(data.message || 'Failed to fetch events');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error fetching events:', error);
            setError('Failed to load events. Please try again later.');
            
            // Use mock data as fallback if API fails
            setEvents(getMockEvents());
        } finally {
            setLoading(false);
        }
    };

    // Load stats from the database
    const fetchStats = async () => {
        try {
            console.log('Fetching stats from:', `${API_BASE_URL}/stats.php`);
            const response = await fetch(`${API_BASE_URL}/stats.php`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('Raw stats response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    console.log('Stats loaded:', data.stats);
                    setStats(data.stats);
                } else {
                    throw new Error(data.message || 'Failed to fetch statistics');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error fetching stats:', error);
            
            // Calculate stats from events as fallback
            calculateStats(events);
        }
    };

    // Calculate stats from events array
    const calculateStats = (eventsArray) => {
        const total = eventsArray.length;
        const pending = eventsArray.filter(event => event.status === 'pending').length;
        const declined = eventsArray.filter(event => event.status === 'declined').length;
        const approved = eventsArray.filter(event => event.status === 'approved').length;
        
        setStats({
            total,
            pending,
            declined,
            approved
        });
    };

    // Load data on component mount
    useEffect(() => {
        fetchEvents();
        fetchStats();
    }, []);

    // Function to get upcoming events (sorted by date)
    const getUpcomingEvents = () => {
        if (!events || events.length === 0) {
            return [];
        }
        
        const today = new Date();
        
        // Convert date strings to Date objects for comparison
        const upcomingEvents = events
            .filter(event => {
                try {
                    // Handle different date formats that might come from your database
                    // Adjust this based on your actual date field name in the reservations table
                    const dateField = event.date || event.reservation_date || event.event_date;
                    if (!dateField) return true; // Include if no date field exists
                    
                    const eventDate = new Date(dateField);
                    return !isNaN(eventDate) && eventDate >= today;
                } catch (error) {
                    console.error('Error parsing date:', event);
                    return true; // Include events with parsing errors
                }
            })
            .sort((a, b) => {
                const dateFieldA = a.date || a.reservation_date || a.event_date;
                const dateFieldB = b.date || b.reservation_date || b.event_date;
                
                if (!dateFieldA || !dateFieldB) return 0;
                
                const dateA = new Date(dateFieldA);
                const dateB = new Date(dateFieldB);
                
                if (isNaN(dateA) || isNaN(dateB)) return 0;
                return dateA - dateB;
            });
            
        return upcomingEvents;
    };

    // Mock data for fallback if API fails
    const getMockEvents = () => {
        return [
            {
                id: 1,
                name: 'Sample Reservation 1',
                date: '2024-11-20',
                time: '5:00AM - 8:00PM',
                place: 'Conference Room A',
                status: 'approved',
                organizer: 'Department of IT',
                description: 'Sample reservation for testing'
            },
            {
                id: 2,
                name: 'Sample Reservation 2',
                date: '2024-12-13',
                time: '5:00PM - 10:00PM',
                place: 'Auditorium',
                status: 'approved',
                organizer: 'Student Council',
                description: 'Sample reservation for testing'
            },
            {
                id: 3,
                name: 'Sample Reservation 3',
                date: '2024-12-20',
                time: '5:00PM - 8:00PM',
                place: 'Gymnasium',
                status: 'pending',
                organizer: 'Sports Committee',
                description: 'Sample reservation for testing'
            }
        ];
    };

    // Refresh data from the server
    const refreshData = () => {
        fetchEvents();
        fetchStats();
    };

    return (
        <EventContext.Provider value={{ 
            events, 
            loading, 
            error,
            stats, 
            getUpcomingEvents, 
            refreshData
        }}>
            {children}
        </EventContext.Provider>
    );
};

export default EventProvider;