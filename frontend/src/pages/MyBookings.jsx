import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Ticket, Calendar, Clock, ChevronRight, LayoutGrid } from 'lucide-react';
import './MyBookings.css';

const MyBookings = () => {
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();
    
    // Giả sử bạn lưu userId trong localStorage sau khi login
    const user = JSON.parse(localStorage.getItem('user'));
    const userId = user?.id; 

    const API_BASE = "http://localhost:8888/backend/api";
    const IMAGE_BASE_URL = "http://localhost:8888/uploads/movies/";

    useEffect(() => {
        if (!userId) {
            navigate('/login');
            return;
        }

        const fetchMyBookings = async () => {
            try {
                // API này cần viết ở backend để lấy danh sách booking của user
                const response = await axios.get(`${API_BASE}/get_user_bookings.php?user_id=${userId}`);
                setBookings(response.data);
                setLoading(false);
            } catch (error) {
                console.error("Error fetching bookings:", error);
                setLoading(false);
            }
        };
        fetchMyBookings();
    }, [userId, navigate]);

    if (loading) return <div className="loading-screen-v4">Loading your history...</div>;

    return (
        <div className="my-bookings-page">
            <div className="container">
                <div className="header-section mb-5">
                    <h1 className="movie-title-v4 text-white">MY TICKETS</h1>
                    <p className="text-white-50">Manage and view your purchased movie tickets</p>
                    <div className="section-title-line"></div>
                </div>

                {bookings.length === 0 ? (
                    <div className="empty-state text-center py-5 btn-glass">
                        <Ticket size={80} className="text-white-50 mb-4" />
                        <h3>No tickets found</h3>
                        <p className="text-muted">You haven't booked any movies yet.</p>
                        <button className="btn-orange-sm mt-3" onClick={() => navigate('/')}>
                            Book Now
                        </button>
                    </div>
                ) : (
                    <div className="bookings-grid">
                        {bookings.map((item) => (
                            <div key={item.id} className="booking-card-v4 btn-glass">
                                <div className="card-poster">
                                    <img 
                                        src={`${IMAGE_BASE_URL}${item.poster}`} 
                                        alt={item.movie_title} 
                                        onError={(e) => e.target.src = 'https://via.placeholder.com/150x220?text=No+Poster'}
                                    />
                                    <div className="status-badge">{item.status || 'Confirmed'}</div>
                                </div>
                                
                                <div className="card-details">
                                    <h4 className="movie-name-text text-white">{item.movie_title}</h4>
                                    <p className="cinema-name text-orange small mb-3">{item.cinema_name}</p>
                                    
                                    <div className="info-row">
                                        <Calendar size={14} />
                                        <span>{item.show_date}</span>
                                    </div>
                                    <div className="info-row">
                                        <Clock size={14} />
                                        <span>{item.show_time}</span>
                                    </div>
                                    <div className="info-row">
                                        <LayoutGrid size={14} />
                                        <span>Seats: <b className="text-white">{item.seats}</b></span>
                                    </div>

                                    <div className="card-footer-v4 mt-3 pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                        <span className="price-tag">{Number(item.total_price).toLocaleString()}đ</span>
                                        <button 
                                            className="view-btn" 
                                            onClick={() => navigate(`/booking-detail/${item.id}`)}
                                        >
                                            Details <ChevronRight size={16} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default MyBookings;