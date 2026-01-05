import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { QRCodeSVG } from 'qrcode.react'; 
import { Ticket, Calendar, Clock, MapPin, Coffee, ArrowLeft, Printer, CheckCircle, PlusCircle } from 'lucide-react';
import './BookingDetail.css';

const BookingDetail = () => {
    const { bookingId } = useParams();
    const navigate = useNavigate();
    const [booking, setBooking] = useState(null);
    const [loading, setLoading] = useState(true);

    const API_BASE = "http://localhost:8888/backend/api";
    const IMAGE_BASE_URL = "http://localhost:8888/uploads/movies/";

    useEffect(() => {
        const fetchBookingDetail = async () => {
            try {
                const response = await axios.get(`${API_BASE}/get_booking_detail.php?id=${bookingId}`);
                setBooking(response.data);
                setLoading(false);
            } catch (error) {
                console.error("Error fetching booking:", error);
                setLoading(false);
            }
        };
        if (bookingId) fetchBookingDetail();
    }, [bookingId]);

    const handleRebook = () => {
        if (booking && booking.showtime_id) {
            navigate(`/booking/seat/${booking.showtime_id}`);
        } else {
            alert("Thông tin suất chiếu không khả dụng. Đang quay lại trang chủ.");
            navigate('/');
        }
    };

    if (loading) return (
        <div style={{background: '#000', height: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', flexDirection: 'column'}}>
            <div className="spinner-border" style={{color: '#f37021'}} role="status"></div>
            <span className="mt-3 text-white">Verifying your digital ticket...</span>
        </div>
    );

    if (!booking || booking.message || booking.error) return (
        <div style={{background: '#000', minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
            <div className="text-center">
                <h2 className="text-white mb-4">Booking details not found!</h2>
                <button className="btn" style={{backgroundColor: '#f37021', color: '#fff'}} onClick={() => navigate('/')}>
                    <ArrowLeft size={18} className="me-2" /> Back to Home
                </button>
            </div>
        </div>
    );

    return (
        <div className="booking-detail-v4-main" style={{
            backgroundColor: '#0a0a0a', 
            minHeight: '100vh', 
            color: '#fff', 
            paddingBottom: '50px',
            paddingTop: '80px' // ĐẨY TOÀN BỘ NỘI DUNG XUỐNG DƯỚI NAVBAR
        }}>
            {/* CSS nội bộ để xử lý giao diện */}
            <style>{`
                .text-brand-orange { color: #f37021 !important; }
                .bg-brand-orange { background-color: #f37021 !important; }
                
                .booking-detail-v4-main .section-title-custom {
                    border-left: 4px solid #f37021 !important;
                    padding-left: 15px !important;
                    text-transform: uppercase;
                    font-weight: 800;
                    letter-spacing: 1px;
                }

                .booking-detail-v4-main .badge-official {
                    background-color: #f37021 !important;
                    color: white !important;
                    padding: 3px 10px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                }

                .booking-detail-v4-main svg {
                    color: #f37021 !important;
                }

                .btn-action-orange {
                    background-color: #f37021 !important;
                    color: white !important;
                    border: none !important;
                    padding: 12px 28px;
                    border-radius: 8px;
                    font-weight: 700;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .btn-action-orange:hover {
                    background-color: #d65d18 !important;
                    transform: translateY(-3px);
                    box-shadow: 0 10px 20px rgba(243, 112, 33, 0.3);
                }

                .btn-outline-custom {
                    background: transparent;
                    border: 1px solid #444;
                    color: #fff;
                    padding: 10px 24px;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: 0.3s;
                }

                .btn-outline-custom:hover {
                    border-color: #f37021;
                    color: #f37021;
                }

                .info-box-v4 {
                    background: rgba(255,255,255,0.03);
                    border: 1px solid rgba(255,255,255,0.08);
                    padding: 15px;
                    border-radius: 12px;
                }
                
                @media print {
                    .no-print { display: none !important; }
                    .booking-detail-v4-main { padding-top: 0 !important; }
                }
            `}</style>

            <div className="container py-5">
                {/* Phần Header được thêm Margin Top để đảm bảo không dính Navbar */}
                <div className="text-center mb-5 mt-4">
                    <CheckCircle size={60} className="text-brand-orange mb-3" />
                    <h1 className="fw-bold text-white mb-2" style={{ letterSpacing: '2px' }}>THANK YOU!</h1>
                    <p className="text-white-50">Your booking has been confirmed. Your e-ticket is ready!</p>
                </div>

                <div className="row justify-content-center">
                    <div className="col-lg-10">
                        {/* Ticket Card */}
                        <div style={{background: '#141414', borderRadius: '25px', overflow: 'hidden', border: '1px solid #222', boxShadow: '0 20px 40px rgba(0,0,0,0.5)'}}>
                            <div className="row g-0">
                                {/* Movie Poster */}
                                <div className="col-md-4">
                                    <img 
                                        src={`${IMAGE_BASE_URL}${booking.poster}`} 
                                        className="w-100 h-100" 
                                        style={{objectFit: 'cover', minHeight: '450px'}}
                                        alt="Poster"
                                        onError={(e) => { e.target.src = "https://via.placeholder.com/300x450?text=No+Poster"; }}
                                    />
                                </div>

                                {/* Ticket Content */}
                                <div className="col-md-8 p-4 p-md-5">
                                    <div className="d-flex justify-content-between align-items-start mb-4">
                                        <div>
                                            <span className="badge-official">OFFICIAL TICKET</span>
                                            <h2 className="section-title-custom text-white mt-3 mb-2">{booking.movie_title}</h2>
                                            <div className="d-flex align-items-center gap-2 text-white-50 mt-2">
                                                <MapPin size={18} />
                                                <span className="fw-bold">{booking.cinema_name}</span>
                                            </div>
                                        </div>
                                        <div className="text-end">
                                            <small className="text-white-50 d-block mb-1">BOOKING REF</small>
                                            <h4 className="text-brand-orange fw-bold">{booking.booking_code || `#${booking.id}`}</h4>
                                        </div>
                                    </div>

                                    <div className="row g-3">
                                        <div className="col-sm-6">
                                            <div className="info-box-v4">
                                                <small className="text-brand-orange d-block mb-1 fw-bold">DATE</small>
                                                <span className="text-white fw-bold">{booking.show_date}</span>
                                            </div>
                                        </div>
                                        <div className="col-sm-6">
                                            <div className="info-box-v4">
                                                <small className="text-brand-orange d-block mb-1 fw-bold">TIME</small>
                                                <span className="text-white fw-bold">{booking.show_time?.substring(0, 5)}</span>
                                            </div>
                                        </div>
                                        <div className="col-sm-6">
                                            <div className="info-box-v4">
                                                <small className="text-brand-orange d-block mb-1 fw-bold">HALL & SEATS</small>
                                                <span className="text-white fw-bold">{booking.room_name} — <span className="text-brand-orange">{booking.seats}</span></span>
                                            </div>
                                        </div>
                                        <div className="col-sm-6">
                                            <div className="info-box-v4">
                                                <small className="text-brand-orange d-block mb-1 fw-bold">COMBO F&B</small>
                                                <span className="text-white fw-bold" style={{fontSize: '0.9rem'}}>{booking.foods || "None"}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-5 pt-4 border-top border-secondary d-flex justify-content-between align-items-center">
                                        <div className="bg-white p-2 rounded shadow-sm">
                                            <QRCodeSVG value={booking.booking_code || "TICKET"} size={80} />
                                        </div>
                                        <div className="text-end">
                                            <small className="text-white-50 d-block">TOTAL PAID</small>
                                            <h3 className="text-brand-orange fw-bold mb-0">
                                                {Number(booking.total_price).toLocaleString('vi-VN')} VND
                                            </h3>
                                            <div className="text-white-50 small mt-1 d-flex align-items-center justify-content-end gap-1">
                                                <CheckCircle size={14} className="text-brand-orange" /> Verified
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Control Buttons */}
                        <div className="d-flex justify-content-center flex-wrap gap-3 mt-5 no-print">
                            <button className="btn-outline-custom" onClick={() => navigate('/')}>
                                <ArrowLeft size={18} className="me-2" /> Home
                            </button>
                            
                            <button className="btn-action-orange" onClick={handleRebook}>
                                <PlusCircle size={20} /> Book More / Change Seats
                            </button>

                            <button className="btn-outline-custom" onClick={() => window.print()}>
                                <Printer size={18} className="me-2" /> Print PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default BookingDetail;