import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import './Logins.css';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    try {
      // Gửi yêu cầu kèm credentials để PHP tạo được Cookie Session
      const res = await axios.post(
        'http://localhost:8888/backend/api/login_json.php', 
        { email, password },
        { withCredentials: true } 
      );
      
      if (res.data.success) {
        localStorage.setItem('user', JSON.stringify(res.data.user));
        
        if (res.data.user.role === 'admin') {
          // Chuyển hướng cứng sang trang quản trị PHP
          window.location.href = "http://localhost:8888/backend/admin/movies.php";
        } else {
          // Về trang chủ React
          window.location.href = "/";
        }
      } else {
        setError(res.data.message);
      }
    } catch (err) {
      setError("Login failed. Check your connection!");
    }
  };

  return (
    <div className="login-container">
      <div className="login-header text-center">
        <div className="brand-logo mb-2">
          <i className='bx bxs-movie-play'></i> <span className="brand-orange">CINESTAR</span>
        </div>
        <h3 className="login-title">LOGIN</h3>
        <p className="login-subtitle">Sign in to book tickets and track your history</p>
      </div>

      <div className="login-card">
        {error && <div className="alert alert-error">{error}</div>}
        
        <form onSubmit={handleLogin}>
          <div className="input-group-custom">
            <label>Email Address</label>
            <div className="input-wrapper">
              <i className='bx bx-envelope'></i>
              <input 
                type="email" 
                placeholder="email@example.com" 
                value={email}
                onChange={(e) => setEmail(e.target.value)} 
                required 
              />
            </div>
          </div>

          <div className="input-group-custom">
            <label>Password</label>
            <div className="input-wrapper">
              <i className='bx bx-lock-alt'></i>
              <input 
                type="password" 
                placeholder="••••••••" 
                value={password}
                onChange={(e) => setPassword(e.target.value)} 
                required 
              />
              <i className='bx bx-show eye-icon'></i>
            </div>
          </div>

          <button type="submit" className="btn-login-submit">Sign In</button>
        </form>

        <div className="login-footer">
          Don't have an account? <Link to="/register" className="link-orange">Register Now</Link>
        </div>
      </div>
    </div>
  );
};

export default Login;