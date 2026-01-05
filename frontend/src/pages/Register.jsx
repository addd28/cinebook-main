import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate, Link } from 'react-router-dom';
import './Logins.css';

const Register = () => {
    const [form, setForm] = useState({ name: '', email: '', password: '', phone: '', city: '' });
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const handleBtn = async (e) => {
        e.preventDefault();
        setError('');
        try {
            const res = await axios.post('http://localhost:8888/backend/api/register_json.php', form);
            if (res.data.success) {
                alert("Registration successful!");
                navigate('/login');
            } else {
                // Server response message (should be in English from backend if possible)
                setError(res.data.message);
            }
        } catch (err) {
            setError("Connection error. Please try again later!");
        }
    };

    return (
        <div className="login-container">
            {/* Header matching the Home v4 style */}
            <div className="login-header text-center">
                <div className="brand-logo mb-2">
                    <i className='bx bxs-movie-play'></i> <span className="brand-orange">CINESTAR</span>
                </div>
                <h3 className="login-title">REGISTER</h3>
                <p className="login-subtitle">Become a member to enjoy exclusive offers</p>
            </div>

            <div className="login-card">
                {error && <div className="alert alert-error">{error}</div>}

                <form onSubmit={handleBtn}>
                    {/* Full Name */}
                    <div className="input-group-custom">
                        <label>Full Name</label>
                        <div className="input-wrapper">
                            <i className='bx bx-user'></i>
                            <input
                                type="text"
                                placeholder="Enter your full name"
                                onChange={e => setForm({ ...form, name: e.target.value })}
                                required
                            />
                        </div>
                    </div>

                    {/* Email */}
                    <div className="input-group-custom">
                        <label>Email Address</label>
                        <div className="input-wrapper">
                            <i className='bx bx-envelope'></i>
                            <input
                                type="email"
                                placeholder="email@example.com"
                                onChange={e => setForm({ ...form, email: e.target.value })}
                                required
                            />
                        </div>
                    </div>

                    {/* Password */}
                    <div className="input-group-custom">
                        <label>Password</label>
                        <div className="input-wrapper">
                            <i className='bx bx-lock-alt'></i>
                            <input
                                type="password"
                                placeholder="••••••••"
                                onChange={e => setForm({ ...form, password: e.target.value })}
                                required
                            />
                        </div>
                    </div>

                    {/* Phone Number */}
                    <div className="input-group-custom">
                        <label>Phone Number</label>
                        <div className="input-wrapper">
                            <i className='bx bx-phone'></i>
                            <input
                                type="text"
                                placeholder="e.g., 0901234567"
                                onChange={e => setForm({ ...form, phone: e.target.value })}
                            />
                        </div>
                    </div>

                    {/* City Selection */}
                    <div className="input-group-custom mb-4">
                        <label>City</label>
                        <div className="input-wrapper select-wrapper">
                            <i className='bx bx-map'></i>
                            <select
                                className="select-dark"
                                onChange={e => setForm({ ...form, city: e.target.value })}
                                required
                            >
                                <option value="">Select your city</option>
                                <option value="Hanoi">Hanoi</option>
                                <option value="Ho Chi Minh City">Ho Chi Minh City</option>
                                <option value="Da Nang">Da Nang</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" className="btn-login-submit">Create Account</button>
                </form>

                <div className="login-footer">
                    Already have an account? <Link to="/login" className="link-orange">Login now</Link>
                </div>
            </div>
        </div>
    );
};

export default Register;