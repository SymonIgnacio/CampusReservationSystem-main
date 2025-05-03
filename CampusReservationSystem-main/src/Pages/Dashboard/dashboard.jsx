import React, { useContext, useState, useEffect } from 'react';
import { EventContext } from '../../context/EventContext';
import { AuthContext } from '../../context/AuthContext';
import './dashboard.css';

function Dashboard() {
  const { events, stats, getUpcomingEvents, loading, error, refreshData } = useContext(EventContext);
  const { user } = useContext(AuthContext);
  const [filter, setFilter] = useState('all'); // Filter for events: 'all', 'pending', 'approved', 'declined'

  // Refresh data when component mounts
  useEffect(() => {
    refreshData();
  }, []);

  // Get upcoming events based on filter
  const getFilteredEvents = () => {
    const upcomingEvents = getUpcomingEvents();
    
    if (filter === 'all') {
      return upcomingEvents;
    }
    
    return upcomingEvents.filter(event => event.status === filter);
  };

  const filteredEvents = getFilteredEvents();

  // Format date to be more readable
  const formatDate = (dateString) => {
    try {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', options);
    } catch (error) {
      return dateString; // Return original string if parsing fails
    }
  };

  // Helper function to get field value with fallbacks
  const getFieldValue = (event, fieldNames) => {
    for (const fieldName of fieldNames) {
      if (event[fieldName] !== undefined) {
        return event[fieldName];
      }
    }
    return 'N/A';
  };

  return (
    <div className="dashboard-container">
      {/* Main Content */}
      <main className="main-content">
        <div className="dashboard-header">
          <h1>DASHBOARD</h1>
          {user && <p>Welcome, {getFieldValue(user, ['name', 'firstname', 'username'])}!</p>}
        </div>

        {/* Stats Cards */}
        <div className="stats-cards">
          <div className="card yellow">
            <h2>{stats.total}</h2>
            <p>RESERVATIONS</p>
          </div>
          <div className="card blue">
            <h2>{stats.pending}</h2>
            <p>PENDING</p>
          </div>
          <div className="card red">
            <h2>{stats.declined}</h2>
            <p>DECLINED</p>
          </div>
          <div className="card green">
            <h2>{stats.approved}</h2>
            <p>APPROVED</p>
          </div>
        </div>

        {/* Upcoming Events */}
        <div className="upcoming-events">
          <div className="events-header">
            <h2>UPCOMING RESERVATIONS</h2>
            <div className="filter-controls">
              <label htmlFor="filter">Filter by status:</label>
              <select 
                id="filter" 
                value={filter} 
                onChange={(e) => setFilter(e.target.value)}
              >
                <option value="all">All Reservations</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="declined">Declined</option>
              </select>
              <button 
                className="refresh-button" 
                onClick={refreshData}
                disabled={loading}
              >
                {loading ? 'Loading...' : 'Refresh'}
              </button>
            </div>
          </div>

          {error && (
            <div className="error-message">
              <p>{error}</p>
            </div>
          )}

          {loading ? (
            <p className="loading-message">Loading reservations...</p>
          ) : filteredEvents.length > 0 ? (
            <div className="table-container">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>RESERVATION</th>
                    <th>DATE</th>
                    <th>TIME</th>
                    <th>LOCATION</th>
                    <th>STATUS</th>
                    <th>RESERVED BY</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredEvents.map(event => (
                    <tr key={getFieldValue(event, ['id', 'reservation_id'])} className={`status-${event.status}`}>
                      <td>{getFieldValue(event, ['id', 'reservation_id'])}</td>
                      <td>{getFieldValue(event, ['name', 'title', 'reservation_name', 'event_name', 'description'])}</td>
                      <td>{formatDate(getFieldValue(event, ['date', 'reservation_date', 'event_date']))}</td>
                      <td>{getFieldValue(event, ['time', 'reservation_time', 'event_time'])}</td>
                      <td>{getFieldValue(event, ['place', 'location', 'venue', 'resource_name'])}</td>
                      <td>
                        <span className={`status-badge ${event.status || 'pending'}`}>
                          {(event.status || 'pending').toUpperCase()}
                        </span>
                      </td>
                      <td>{getFieldValue(event, ['organizer', 'reserved_by', 'user_id'])}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="no-events">No upcoming reservations found.</p>
          )}
        </div>
      </main>
    </div>
  );
}

export default Dashboard;