import React, { useState, useEffect } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import './Navbar.css';

const Navbar = ({ user }) => { 
    const [isScrolled, setIsScrolled] = useState(false);
    const [isSearchOpen, setIsSearchOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState(""); 
    const navigate = useNavigate();

    // 1. Hiệu ứng đổi màu Navbar khi cuộn trang
    useEffect(() => {
        const handleScroll = () => {
            setIsScrolled(window.scrollY > 30);
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    // 2. Xử lý tìm kiếm khi nhấn Enter
    const handleSearchKeyDown = (e) => {
        if (e.key === 'Enter' && searchTerm.trim() !== "") {
            // Chuyển hướng sang trang movies kèm query parameter
            navigate(`/movies?search=${encodeURIComponent(searchTerm.trim())}`);
            setIsSearchOpen(false); 
            setSearchTerm(""); 
        }
    };

    // 3. Xử lý Đăng xuất
    const handleLogout = () => {
        if (window.confirm('Are you sure you want to log out?')) {
            localStorage.clear();
            window.location.href = '/';
        }
    };

    return (
        <nav className={`navbar navbar-expand-lg fixed-top premium-navbar ${isScrolled ? 'nav-scrolled' : ''}`}>
            <div className="container">
                {/* Logo */}
                <NavLink className="navbar-brand d-flex align-items-center" to="/">
                    <div className="brand-wrapper">
                        <i className="fas fa-star brand-star"></i>
                        <span className="brand-text-white">CINE</span>
                        <span className="brand-text-orange">STAR</span>
                    </div>
                </NavLink>

                {/* Mobile Toggler */}
                <button className="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#cineNavbar">
                    <span className="navbar-toggler-icon"></span>
                </button>

                <div className="collapse navbar-collapse" id="cineNavbar">
                    {/* Main Menu - Ẩn khi đang mở thanh search để tránh chồng chéo */}
                    {!isSearchOpen && (
                        <ul className="navbar-nav mx-auto nav-menu-list">
                            <li className="nav-item">
                                <NavLink className="nav-link-premium" to="/movies">MOVIES</NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink className="nav-link-premium" to="/cinemas">CINEMAS</NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink className="nav-link-premium" to="/news">NEWS</NavLink>
                            </li>
                        </ul>
                    )}

                    {/* Right Group: Search + User + Book Now */}
                    <div className={`nav-right-group d-flex align-items-center gap-3 ms-auto ${isSearchOpen ? 'w-100 justify-content-end' : ''}`}>
                        
                        {/* Search Box */}
                        <div className={`search-box-container ${isSearchOpen ? 'active' : ''}`}>
                            <input 
                                type="text" 
                                className="search-input" 
                                placeholder="Search movies..." 
                                autoFocus={isSearchOpen}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                onKeyDown={handleSearchKeyDown}
                            />
                            <i 
                                className={`fas ${isSearchOpen ? 'fa-times' : 'fa-search'} search-trigger`} 
                                onClick={() => {
                                    setIsSearchOpen(!isSearchOpen);
                                    if(isSearchOpen) setSearchTerm(""); 
                                }}
                            ></i>
                        </div>

                        {/* User Profile Dropdown */}
                        <div className="user-group d-flex align-items-center">
                            {user ? (
                                <div className="dropdown">
                                    <div 
                                        className="user-icon-circle cursor-pointer" 
                                        data-bs-toggle="dropdown"
                                        title={user.role === 'admin' ? "Administrator" : user.name}
                                    >
                                        <i className={`fas ${user.role === 'admin' ? 'fa-user-shield' : 'fa-user'}`}></i>
                                        <span className="login-status-dot"></span>
                                    </div>
                                    <ul className="dropdown-menu dropdown-menu-dark dropdown-menu-end animate__animated animate__fadeIn">
                                        <li className="px-3 py-2 small border-bottom border-secondary mb-1">
                                            Welcome, <span className="text-orange">{user.name}</span>
                                        </li>
                                        <li>
                                            <NavLink className="dropdown-item" to="/my-bookings">
                                                <i className="fas fa-ticket-alt me-2"></i> My Tickets
                                            </NavLink>
                                        </li>
                                        {user.role === 'admin' && (
                                            <li>
                                                <a className="dropdown-item" href="http://localhost:8888/backend/admin/movies.php">
                                                    <i className="fas fa-cog me-2"></i> Admin Panel
                                                </a>
                                            </li>
                                        )}
                                        <li><hr className="dropdown-divider border-secondary" /></li>
                                        <li>
                                            <button className="dropdown-item text-danger" onClick={handleLogout}>
                                                <i className="fas fa-sign-out-alt me-2"></i> Logout
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            ) : (
                                <NavLink to="/login" className="user-icon-circle" title="Login">
                                    <i className="far fa-user"></i>
                                </NavLink>
                            )}
                        </div>

                        {/* Quick Booking Button */}
                        <NavLink to="/movies" className="btn btn-premium-orange px-4 d-none d-sm-block">BOOK NOW</NavLink>
                    </div>
                </div>
            </div>
        </nav>
    );
};

export default Navbar;