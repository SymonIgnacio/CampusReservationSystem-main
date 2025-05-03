import React, { useState } from "react";
import { Link } from "react-router-dom";
import "./registerPage.css";

function RegisterPage() {
  const [formData, setFormData] = useState({
    firstName: "",
    middleName: "",
    lastName: "",
    email: "",
    username: "",
    password: "",
    confirmPassword: "",
  });

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
  
    if (formData.password !== formData.confirmPassword) {
      alert("Passwords do not match!");
      return;
    }
  
    try {
      const response = await fetch("http://localhost/CampusReservationSystem-main/CampusReservationSystem-main/src/Pages/RegisterPage/signup.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });
  
      const result = await response.text();
      alert(result);
      console.log("Server Response:", result);
    } catch (error) {
      console.error("Error:", error);
      alert("Registration failed. Check the console.");
    }
  };

  return (
    <div className="register-container">
      <h1>SIGN UP</h1>
      <form onSubmit={handleSubmit} className="register-form">
        <div className="left-column">
          <label htmlFor="firstName">First Name:</label>
          <input type="text" id="firstName" name="firstName" value={formData.firstName} onChange={handleChange} required />

          <label htmlFor="middleName">Middle Name:</label>
          <input type="text" id="middleName" name="middleName" value={formData.middleName} onChange={handleChange} />

          <label htmlFor="lastName">Last Name:</label>
          <input type="text" id="lastName" name="lastName" value={formData.lastName} onChange={handleChange} required />

          <label htmlFor="email">Email:</label>
          <input type="email" id="email" name="email" value={formData.email} onChange={handleChange} required />
        </div>

        <div className="right-column">
          <label htmlFor="username">Username:</label>
          <input type="text" id="username" name="username" value={formData.username} onChange={handleChange} required />

          <label htmlFor="password">Password:</label>
          <input type="password" id="password" name="password" value={formData.password} onChange={handleChange} required />

          <label htmlFor="confirmPassword">Confirm Password:</label>
          <input type="password" id="confirmPassword" name="confirmPassword" value={formData.confirmPassword} onChange={handleChange} required />
        </div>
        
        <div><button type="submit" className="signup-button">SIGN UP</button></div>
        
      </form>

      

      <p>Already have an account? <Link to="/">Login here</Link></p>
    </div>
  );
}

export default RegisterPage;
