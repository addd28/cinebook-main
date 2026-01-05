import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import './Login.css';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post('http://localhost:8888/backend/api/login_json.php', { email, password });
      if (res.data.success) {
        localStorage.setItem('user', JSON.stringify(res.data.user));
        // Nếu là admin, có thể chuyển hướng đến link PHP admin hoặc Dashboard React
        if (res.data.user.role === 'admin') {
            window.location.href = "http://localhost:8888/backend/admin/movies.php";
        } else {
            window.location.href = "/";
        }
      } else {
        alert(res.data.message);
      }
    } catch (err) {
      alert("Login failed!");
    }
  };

  return (
    <div className="login-container" style={{paddingTop: '150px', textAlign: 'center'}}>
      <form onSubmit={handleLogin} className="m-auto" style={{maxWidth: '300px'}}>
        <h2 className="text-white mb-4">LOGIN</h2>
        <input type="email" className="form-control mb-3" placeholder="Email" onChange={e => setEmail(e.target.value)} required />
        <input type="password" className="form-control mb-3" placeholder="Password" onChange={e => setPassword(e.target.value)} required />
        <button className="btn btn-premium-orange w-100">Login</button>
      </form>
    </div>
  );
};

export default Login;