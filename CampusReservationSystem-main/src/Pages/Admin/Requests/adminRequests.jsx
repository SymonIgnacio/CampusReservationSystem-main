import React, { useState, useContext } from 'react';
import { EventContext } from '../../../context/EventContext';
import './adminRequests.css';

function AdminRequests() {
  const { events, loading, error, updateEventStatus, refreshData } = useContext(EventContext);
  const [searchTerm, setSearchTerm] = useState('');
  const [filteredRequests, setFilteredRequests] = useState([]);
  const [processingId, setProcessingId] = useState(null);

  // Filter for pending requests only
  React.useEffect(() => {
    const pendingRequests = events.filter(event => event.status === 'pending');
    setFilteredRequests(pendingRequests);
  }, [events]);

  // Handle search
  const handleSearch = (e) => {
    e.preventDefault();
    
    if (!searchTerm.trim()) {
      const pendingRequests = events.filter(event => event.status === 'pending');
      setFilteredRequests(pendingRequests);
      return;
    }
    
    const filtered = events.filter(event => {
      const title = event.name || event.title || event.description || '';
      const requestedBy = event.organizer || event.requestedBy || '';
      const department = event.department || '';
      
      return (event.status === 'pending') && (
        title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        requestedBy.toLowerCase().includes(searchTerm.toLowerCase()) ||
        department.toLowerCase().includes(searchTerm.toLowerCase())
      );
    });
    
    setFilteredRequests(filtered);
  };

  // Reset search
  const handleResetSearch = () => {
    setSearchTerm('');
    const pendingRequests = events.filter(event => event.status === 'pending');
    setFilteredRequests(pendingRequests);
  };

  // Format date
  const formatDate = (dateStr) => {
    if (!dateStr) return 'Date TBD';
    
    try {
      const date = new Date(dateStr);
      if (isNaN(date.getTime())) return dateStr;
      
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    } catch (error) {
      return dateStr;
    }
  };

  // Handle approve request
  const handleApprove = async (id) => {
    setProcessingId(id);
    const result = await updateEventStatus(id, 'approved');
    if (result.success) {
      // The events will be refreshed by the updateEventStatus function
      // Just update the filtered requests
      setFilteredRequests(prev => prev.filter(request => request.id !== id));
    } else {
      alert(`Failed to approve request: ${result.message}`);
    }
    setProcessingId(null);
  };

  // Handle decline request
  const handleDecline = async (id) => {
    setProcessingId(id);
    const result = await updateEventStatus(id, 'declined');
    if (result.success) {
      // The events will be refreshed by the updateEventStatus function
      // Just update the filtered requests
      setFilteredRequests(prev => prev.filter(request => request.id !== id));
    } else {
      alert(`Failed to decline request: ${result.message}`);
    }
    setProcessingId(null);
  };

  return (
    <div className="admin-requests-container">
      <h2 className="page-title">RESERVATION REQUESTS</h2>
      
      <div className="controls">
        <div className="search-container">
          <form onSubmit={handleSearch}>
            <input 
              type="text" 
              placeholder="Search requests..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="search-input"
            />
            <button type="submit" className="search-button">Search</button>
            {searchTerm && (
              <button 
                type="button" 
                className="reset-button"
                onClick={handleResetSearch}
              >
                Reset
              </button>
            )}
          </form>
        </div>
        
        <div className="refresh-container">
          <button 
            className="refresh-button"
            onClick={refreshData}
            disabled={loading}
          >
            {loading ? 'Loading...' : 'Refresh'}
          </button>
        </div>
      </div>
      
      {loading ? (
        <div className="loading">Loading requests...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : filteredRequests.length === 0 ? (
        <div className="no-requests">No pending requests found.</div>
      ) : (
        <div className="requests-table-container">
          <table className="requests-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Requested By</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredRequests.map(request => (
                <tr key={request.id} className="status-pending">
                  <td>{request.id}</td>
                  <td>{request.name || request.title || 'Untitled Event'}</td>
                  <td>{formatDate(request.date || request.formatted_date || request.start_time)}</td>
                  <td>{request.time || request.formatted_time_range || 'Time TBD'}</td>
                  <td>{request.place || request.location || 'Location TBD'}</td>
                  <td>{request.organizer || request.requestedBy || 'Unknown'}</td>
                  <td>
                    <span className="status-badge pending">
                      PENDING
                    </span>
                  </td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        className="approve-btn"
                        onClick={() => handleApprove(request.id)}
                        disabled={processingId === request.id}
                      >
                        {processingId === request.id ? 'Processing...' : 'Approve'}
                      </button>
                      <button 
                        className="decline-btn"
                        onClick={() => handleDecline(request.id)}
                        disabled={processingId === request.id}
                      >
                        {processingId === request.id ? 'Processing...' : 'Decline'}
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

export default AdminRequests;