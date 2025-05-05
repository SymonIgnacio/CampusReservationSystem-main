import React, { useState, useEffect } from 'react';
import './adminUsers.css';

const AdminUsers = () => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filter, setFilter] = useState('all');
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddModal, setShowAddModal] = useState(false);
  const [newUser, setNewUser] = useState({
    firstname: '',
    middlename: '',
    lastname: '',
    department: 'College of Computer Studies',
    username: '',
    email: '',
    password: '',
    confirmPassword: '',
    role: 'student'
  });
  const [addUserError, setAddUserError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Department options
  const departments = [
    'College of Computer Studies',
    'College of Accountancy',
    'College of Education',
    'College of Hotel Management and Tourism'
  ];

  // Fetch users from the API
  const fetchUsers = async (roleFilter = '') => {
    setLoading(true);
    setError(null);
    
    try {
      const url = `http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api/get_users.php${roleFilter ? `?role=${roleFilter}` : ''}`;
      const response = await fetch(url);
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (data.success) {
        // If role filter is provided, filter the results client-side
        if (roleFilter) {
          setUsers(data.users.filter(user => user.role === roleFilter));
        } else {
          setUsers(data.users);
        }
      } else {
        throw new Error(data.message || 'Failed to fetch users');
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      setError('Failed to load users. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  // Load users on component mount
  useEffect(() => {
    fetchUsers();
  }, []);

  // Handle filter change
  const handleFilterChange = (e) => {
    const newFilter = e.target.value;
    setFilter(newFilter);
    
    if (newFilter === 'all') {
      fetchUsers();
    } else {
      fetchUsers(newFilter);
    }
  };

  // Handle search
  const handleSearch = (e) => {
    e.preventDefault();
    
    if (!searchTerm.trim()) {
      // If search is empty, reset to current filter
      if (filter === 'all') {
        fetchUsers();
      } else {
        fetchUsers(filter);
      }
      return;
    }
    
    // Filter users based on search term (client-side filtering)
    const filteredUsers = users.filter(user => 
      user.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
      user.firstname.toLowerCase().includes(searchTerm.toLowerCase()) ||
      user.lastname.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (user.department && user.department.toLowerCase().includes(searchTerm.toLowerCase()))
    );
    
    setUsers(filteredUsers);
  };

  // Reset search
  const handleResetSearch = () => {
    setSearchTerm('');
    if (filter === 'all') {
      fetchUsers();
    } else {
      fetchUsers(filter);
    }
  };

  // Handle new user form input changes
  const handleNewUserChange = (e) => {
    const { name, value } = e.target;
    setNewUser({
      ...newUser,
      [name]: value
    });
  };

  // Handle add user form submission
  const handleAddUser = async (e) => {
    e.preventDefault();
    setAddUserError('');
    
    // Validate form
    if (newUser.password !== newUser.confirmPassword) {
      setAddUserError('Passwords do not match');
      return;
    }
    
    setIsSubmitting(true);
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api/add_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(newUser),
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Close modal and reset form
        setShowAddModal(false);
        setNewUser({
          firstname: '',
          middlename: '',
          lastname: '',
          department: 'College of Computer Studies',
          username: '',
          email: '',
          password: '',
          confirmPassword: '',
          role: 'student'
        });
        
        // Refresh user list
        fetchUsers(filter !== 'all' ? filter : '');
      } else {
        setAddUserError(result.message || 'Failed to add user');
      }
    } catch (error) {
      console.error('Error adding user:', error);
      setAddUserError('Network error. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  // Handle user deletion
  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Are you sure you want to delete this user?')) {
      return;
    }
    
    try {
      const response = await fetch('http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/api/delete_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ userId }),
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Refresh user list
        fetchUsers(filter !== 'all' ? filter : '');
      } else {
        alert(result.message || 'Failed to delete user');
      }
    } catch (error) {
      console.error('Error deleting user:', error);
      alert('Network error. Please try again.');
    }
  };

  return (
    <div className="admin-users-container">
      <h2 className="page-title">USER MANAGEMENT</h2>
      
      <div className="controls">
        <div className="filter-container">
          <label htmlFor="role-filter">Filter by Role:</label>
          <select 
            id="role-filter" 
            value={filter} 
            onChange={handleFilterChange}
          >
            <option value="all">All Users</option>
            <option value="faculty">Faculty</option>
            <option value="student">Students</option>
          </select>
        </div>
        
        <div className="search-container">
          <form onSubmit={handleSearch}>
            <input 
              type="text" 
              placeholder="Search users..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
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
        
        <button 
          className="add-user-button"
          onClick={() => setShowAddModal(true)}
        >
          Add User
        </button>
      </div>
      
      {loading ? (
        <div className="loading">Loading users...</div>
      ) : error ? (
        <div className="error">{error}</div>
      ) : users.length === 0 ? (
        <div className="no-users">No users found.</div>
      ) : (
        <div className="users-table-container">
          <table className="users-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Department</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.user_id}>
                  <td>{user.user_id}</td>
                  <td>{user.username}</td>
                  <td>{user.firstname}</td>
                  <td>{user.lastname}</td>
                  <td>{user.department || '-'}</td>
                  <td>
                    <span className={`role-badge ${user.role}`}>
                      {user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                    </span>
                  </td>
                  <td>
                    <button 
                      className="delete-button"
                      onClick={() => handleDeleteUser(user.user_id)}
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      
      {/* Add User Modal */}
      {showAddModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <h3>Add New User</h3>
            
            {addUserError && (
              <div className="error-message">{addUserError}</div>
            )}
            
            <form onSubmit={handleAddUser}>
              <div className="form-row">
                <div className="form-group">
                  <label>First Name:</label>
                  <input 
                    type="text" 
                    name="firstname" 
                    value={newUser.firstname} 
                    onChange={handleNewUserChange} 
                    required 
                  />
                </div>
                
                <div className="form-group">
                  <label>Middle Name:</label>
                  <input 
                    type="text" 
                    name="middlename" 
                    value={newUser.middlename} 
                    onChange={handleNewUserChange} 
                  />
                </div>
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Last Name:</label>
                  <input 
                    type="text" 
                    name="lastname" 
                    value={newUser.lastname} 
                    onChange={handleNewUserChange} 
                    required 
                  />
                </div>
                
                <div className="form-group">
                  <label>Department:</label>
                  <select 
                    name="department" 
                    value={newUser.department} 
                    onChange={handleNewUserChange} 
                    required
                  >
                    {departments.map(dept => (
                      <option key={dept} value={dept}>{dept}</option>
                    ))}
                  </select>
                </div>
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Username:</label>
                  <input 
                    type="text" 
                    name="username" 
                    value={newUser.username} 
                    onChange={handleNewUserChange} 
                    required 
                  />
                </div>
                
                <div className="form-group">
                  <label>Email:</label>
                  <input 
                    type="email" 
                    name="email" 
                    value={newUser.email} 
                    onChange={handleNewUserChange} 
                    required 
                  />
                </div>
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Password:</label>
                  <input 
                    type="password" 
                    name="password" 
                    value={newUser.password} 
                    onChange={handleNewUserChange} 
                    required 
                  />
                </div>
                
                <div className="form-group">
                  <label>Confirm Password:</label>
                  <input 
                    type="password" 
                    name="confirmPassword" 
                    value={newUser.confirmPassword} 
                    onChange={handleNewUserChange} 
                    required 
                  />
                </div>
              </div>
              
              <div className="form-row">
                <div className="form-group">
                  <label>Role:</label>
                  <select 
                    name="role" 
                    value={newUser.role} 
                    onChange={handleNewUserChange} 
                    required
                  >
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                  </select>
                </div>
              </div>
              
              <div className="modal-buttons">
                <button 
                  type="submit" 
                  className="add-button"
                  disabled={isSubmitting}
                >
                  {isSubmitting ? 'Adding...' : 'Add User'}
                </button>
                <button 
                  type="button" 
                  className="cancel-button"
                  onClick={() => setShowAddModal(false)}
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default AdminUsers;