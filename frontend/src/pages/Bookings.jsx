import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './Bookings.css';

const Bookings = ({ token, user }) => {
    // Lấy showtimeId nếu đi từ trang BookingDetail, hoặc movieId nếu đi từ trang MovieDetail
    const { movieId, showtimeId } = useParams();
    const navigate = useNavigate();

    const [movie, setMovie] = useState(null);
    const [cinemas, setCinemas] = useState([]);
    const [showtimes, setShowtimes] = useState([]);
    const [seats, setSeats] = useState([]);
    const [foods, setFoods] = useState([]);
    const [loading, setLoading] = useState(true);
    const [step, setStep] = useState(1);

    const [selectedCinema, setSelectedCinema] = useState(null);
    const [selectedTime, setSelectedTime] = useState(null);
    const [selectedSeats, setSelectedSeats] = useState([]);
    const [selectedFoods, setSelectedFoods] = useState({});
    const [timeLeft, setTimeLeft] = useState(300); // 5 minutes

    // State mới cho phương thức thanh toán (ảo)
    const [paymentMethod, setPaymentMethod] = useState('momo');

    const API_BASE = "http://localhost:8888/backend/api";

    const formatTime = (timeStr) => {
        if (!timeStr) return '--:--';
        return timeStr.substring(0, 5);
    };

    const formatTimer = (seconds) => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
    };

    // 1. Timer logic: Giữ ghế trong 5 phút
    useEffect(() => {
        let timer;
        if (step >= 2 && timeLeft > 0) {
            timer = setInterval(() => {
                setTimeLeft(prev => prev - 1);
            }, 1000);
        } else if (timeLeft === 0) {
            alert("Session expired! Please try again.");
            window.location.reload();
        }
        return () => clearInterval(timer);
    }, [step, timeLeft]);

    // 2. Initialize Data & Handle Direct Showtime
    useEffect(() => {
        const initData = async () => {
            try {
                const [c, f] = await Promise.all([
                    axios.get(`${API_BASE}/get_cinemas.php`),
                    axios.get(`${API_BASE}/get_foods.php`)
                ]);
                
                setCinemas(c.data);
                setFoods(Array.isArray(f.data) ? f.data : []);

                if (showtimeId) {
                    const res = await axios.get(`${API_BASE}/get_showtime_detail.php?id=${showtimeId}`);
                    if (res.data) {
                        const st = res.data;
                        setSelectedTime(st);
                        setSelectedCinema({ id: st.cinema_id, name: st.cinema_name });
                        
                        const m = await axios.get(`${API_BASE}/get_movies.php?id=${st.movie_id}`);
                        setMovie(m.data);
                        setStep(2);
                    }
                } 
                else if (movieId) {
                    const m = await axios.get(`${API_BASE}/get_movies.php?id=${movieId}`);
                    setMovie(m.data);
                }

                setLoading(false);
            } catch (err) {
                console.error("Initialization error:", err);
                setLoading(false);
            }
        };
        initData();
    }, [movieId, showtimeId]);

    // 3. Fetch Showtimes theo Cinema
    useEffect(() => {
        if (selectedCinema && step === 1) {
            const mId = movie?.id || movieId;
            axios.get(`${API_BASE}/get_showtimes.php?movie_id=${mId}&cinema_id=${selectedCinema.id}`)
                .then(res => {
                    const uniqueShowtimes = Array.isArray(res.data) 
                        ? res.data.filter((v, i, a) => a.findIndex(t => (t.id === v.id)) === i)
                        : [];
                    setShowtimes(uniqueShowtimes);
                })
                .catch(err => console.error("Error fetching showtimes:", err));
        }
    }, [selectedCinema, movieId, movie, step]);

    // 4. Fetch Seats Layout
    const fetchSeats = async () => {
        const currentShowtimeId = selectedTime?.id || selectedTime?.ID || showtimeId;
        if (currentShowtimeId) {
            try {
                const res = await axios.get(`${API_BASE}/get_seats.php?showtime_id=${currentShowtimeId}`);
                let rawSeats = [];
                if (res.data.seats) rawSeats = res.data.seats;
                else if (Array.isArray(res.data)) rawSeats = res.data;

                const uniqueSeats = rawSeats.filter((v, i, a) => a.findIndex(t => (t.id === v.id)) === i);
                setSeats(uniqueSeats);
            } catch (err) {
                console.error("Error fetching seats:", err);
            }
        }
    };

    useEffect(() => {
        if (step === 2) fetchSeats();
    }, [step, selectedTime]);

    // 5. Seat Selection Logic
    const toggleSeat = async (seat) => {
        if (seat.status === 'occupied' || seat.status === 'holding') return;
        if (!user) { alert("Please login to select seats."); return; }

        const currentShowtimeId = selectedTime?.id || selectedTime?.ID || showtimeId;
        const isSelected = selectedSeats.find(s => s.id === seat.id);

        try {
            if (isSelected) {
                await axios.post(`${API_BASE}/handle_seat.php`, {
                    action: 'release',
                    seat_id: seat.id,
                    showtime_id: currentShowtimeId,
                    user_id: user.id
                });
                setSelectedSeats(selectedSeats.filter(s => s.id !== seat.id));
            } else {
                if (selectedSeats.length >= 8) {
                    alert("Maximum 8 seats allowed per booking.");
                    return;
                }
                const res = await axios.post(`${API_BASE}/handle_seat.php`, {
                    action: 'hold',
                    seat_id: seat.id,
                    showtime_id: currentShowtimeId,
                    user_id: user.id
                });

                if (res.data.success) {
                    setSelectedSeats([...selectedSeats, seat]);
                } else {
                    alert(res.data.message || "This seat was just taken!");
                    fetchSeats();
                }
            }
        } catch (err) {
            console.error("Seat toggle error:", err);
        }
    };

    const updateFood = (foodId, delta) => {
        setSelectedFoods(prev => ({
            ...prev,
            [foodId]: Math.max(0, (prev[foodId] || 0) + delta)
        }));
    };

    const calculateTotal = () => {
        const seatTotal = selectedSeats.reduce((sum, s) => sum + (Number(s.price) || 0), 0);
        const foodTotal = foods.reduce((sum, f) => {
            const qty = selectedFoods[f.id] || 0;
            return sum + (Number(f.price) * qty);
        }, 0);
        return seatTotal + foodTotal;
    };

    const handleNext = () => {
        if (step < 3) {
            setStep(step + 1);
            window.scrollTo(0, 0);
        } else {
            handleConfirmBooking();
        }
    };

    // 6. Confirm Booking Logic
    const handleConfirmBooking = async () => {
        if (!user) { alert("Please login to proceed."); return; }

        try {
            const foodData = foods
                .filter(f => selectedFoods[f.id] > 0)
                .map(f => ({
                    id: f.id,
                    qty: selectedFoods[f.id],
                    price: f.price
                }));

            const payload = {
                user_id: user.id,
                showtime_id: selectedTime?.id || selectedTime?.ID || showtimeId,
                seat_ids: selectedSeats.map(s => s.id),
                seat_names: selectedSeats.map(s => s.seat_number).join(', '),
                total_price: calculateTotal(),
                foods: foodData
            };

            const response = await axios.post(`${API_BASE}/add_booking.php`, payload);

            if (response.data.success) {
                navigate(`/booking-detail/${response.data.booking_id}`);
            } else {
                alert("Booking failed: " + response.data.message);
                setStep(2);
                fetchSeats();
                setSelectedSeats([]);
            }
        } catch (err) {
            console.error(err);
            alert("Connection error. Please try again.");
        }
    };

    if (loading) return <div className="loader-container"><div className="loader"></div></div>;

    return (
        <div className="booking-v4" style={{ paddingTop: '100px' }}>
            <div className="container py-4">
                <div className="row">
                    <div className="col-lg-8">
                        {/* Stepper Progress Bar */}
                        <div className="booking-stepper mb-5">
                            {['Showtime', 'Seats', 'Concessions'].map((label, i) => (
                                <div key={`step-${i}`} className={`step-unit ${step >= i + 1 ? 'active' : ''}`}>
                                    <span className="step-num">{i + 1}</span>
                                    <span className="step-label">{label}</span>
                                </div>
                            ))}
                        </div>

                        <div className="booking-main-card">
                            {/* STEP 1: CINEMA & SHOWTIME */}
                            {step === 1 && (
                                <div className="fade-in">
                                    <h5 className="booking-section-title mb-4">Select Cinema</h5>
                                    <div className="row g-3 mb-5">
                                        {cinemas.map(c => (
                                            <div className="col-md-4" key={`cinema-${c.id}`}>
                                                <div
                                                    onClick={() => { setSelectedCinema(c); setSelectedTime(null); }}
                                                    className={`cinema-selector-item ${selectedCinema?.id === c.id ? 'active' : ''}`}
                                                >
                                                    {c.name}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    {selectedCinema && (
                                        <>
                                            <h5 className="booking-section-title mb-4">Available Showtimes</h5>
                                            <div className="showtime-grid">
                                                {showtimes.length > 0 ? showtimes.map(t => (
                                                    <div
                                                        key={`showtime-${t.id || t.ID}`}
                                                        onClick={() => setSelectedTime(t)}
                                                        className={`showtime-item-v4 ${(selectedTime?.id === t.id || selectedTime?.ID === t.ID) ? 'active' : ''}`}
                                                    >
                                                        <span className="st-time">{formatTime(t.show_time || t.time)}</span>
                                                        <span className="st-room">{t.room_name || `Room ${t.room_id}`}</span>
                                                    </div>
                                                )) : <p className="text-muted">No showtimes available.</p>}
                                            </div>
                                        </>
                                    )}
                                </div>
                            )}

                            {/* STEP 2: SEATS LAYOUT */}
                            {step === 2 && (
                                <div className="fade-in text-center">
                                    <div className="screen-container-v4 mb-5">
                                        <div className="screen-glow"></div>
                                        <span className="screen-text">SCREEN</span>
                                    </div>

                                    <div className="seats-layout-v4 mb-5">
                                        {seats.length > 0 ? (
                                            seats.map(seat => (
                                                <div
                                                    key={`seat-${seat.id}`}
                                                    onClick={() => toggleSeat(seat)}
                                                    className={`seat-unit-v4 ${seat.seat_type} ${seat.status} ${selectedSeats.find(s => s.id === seat.id) ? 'selected' : ''}`}
                                                >
                                                    {seat.seat_number}
                                                </div>
                                            ))
                                        ) : <div className="py-5"><div className="spinner-border text-warning"></div></div>}
                                    </div>

                                    <div className="legend-grid-v4 mt-4">
                                        <div className="lg-item"><div className="lg-box occupied"></div><span>Sold</span></div>
                                        <div className="lg-item"><div className="lg-box selected"></div><span>Selected</span></div>
                                        <div className="lg-item"><div className="lg-box available"></div><span>Standard</span></div>
                                        <div className="lg-item"><div className="lg-box gold"></div><span>Gold</span></div>
                                        <div className="lg-item"><div className="lg-box platinum"></div><span>VIP</span></div>
                                    </div>
                                </div>
                            )}

                            {/* STEP 3: FOOD & COMBOS + PAYMENT */}
                            {step === 3 && (
                                <div className="fade-in">
                                    <h5 className="booking-section-title mb-4">Combos & Concessions</h5>
                                    <div className="food-container-v4 mb-5">
                                        {foods.map(f => (
                                            <div key={`food-${f.id}`} className="food-row-v4">
                                                <div className="d-flex align-items-center">
                                                    <img src={`http://localhost:8888/uploads/foods/${f.image}`} className="food-thumb" alt={f.name} />
                                                    <div className="ms-3">
                                                        <div className="food-name">{f.name}</div>
                                                        <div className="food-price">{Number(f.price).toLocaleString()}đ</div>
                                                    </div>
                                                </div>
                                                <div className="food-qty-control">
                                                    <button onClick={() => updateFood(f.id, -1)}>-</button>
                                                    <span>{selectedFoods[f.id] || 0}</span>
                                                    <button onClick={() => updateFood(f.id, 1)}>+</button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {/* PHẦN CHỌN PHƯƠNG THỨC THANH TOÁN (ẢO) */}
                                    <h5 className="booking-section-title mb-4">Payment Method</h5>
                                    <div className="payment-methods-v4 mb-4">
                                        <div 
                                            className={`payment-card ${paymentMethod === 'momo' ? 'active' : ''}`}
                                            onClick={() => setPaymentMethod('momo')}
                                        >
                                            <div className="payment-icon momo">M</div>
                                            <div className="payment-details">
                                                <span className="payment-name">MoMo Wallet</span>
                                                <span className="payment-desc">Payment via MoMo application</span>
                                            </div>
                                            <div className="payment-check">
                                                <i className={`bx ${paymentMethod === 'momo' ? 'bxs-check-circle' : 'bx-circle'}`}></i>
                                            </div>
                                        </div>

                                        <div 
                                            className={`payment-card ${paymentMethod === 'atm' ? 'active' : ''}`}
                                            onClick={() => setPaymentMethod('atm')}
                                        >
                                            <div className="payment-icon atm"><i className='bx bx-credit-card'></i></div>
                                            <div className="payment-details">
                                                <span className="payment-name">ATM card/Online banking</span>
                                                <span className="payment-desc">Supports all domestic banks</span>
                                            </div>
                                            <div className="payment-check">
                                                <i className={`bx ${paymentMethod === 'atm' ? 'bxs-check-circle' : 'bx-circle'}`}></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Navigation Buttons */}
                            <div className="d-flex justify-content-between mt-4">
                                <button className="btn-v4-outline" onClick={() => step > 1 ? setStep(step - 1) : navigate(-1)}>Back</button>
                                <button
                                    className="btn-v4-primary px-5"
                                    disabled={(step === 1 && !selectedTime) || (step === 2 && selectedSeats.length === 0)}
                                    onClick={handleNext}
                                >
                                    {step === 3 ? "Confirm Booking" : "Next Step"}
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* SIDEBAR SUMMARY */}
                    <div className="col-lg-4">
                        <div className="timer-box-v4 d-flex align-items-center justify-content-center mb-3">
                            <i className='bx bx-time-five me-2'></i>
                            <span>Seat holding time: <b>{formatTimer(timeLeft)}</b></span>
                        </div>
                        <div className="booking-summary-v4 sticky-top" style={{ top: '120px' }}>
                            <div className="summary-poster-container">
                                {movie?.image && <img src={`http://localhost:8888/uploads/movies/${movie.image}`} alt="poster" />}
                            </div>
                            <div className="p-4">
                                <h4 className="movie-summary-title">{movie?.title || 'Select Movie'}</h4>
                                <div className="summary-info-item"><span>Cinema:</span> <b>{selectedCinema?.name || '--'}</b></div>
                                <div className="summary-info-item">
                                    <span>Showtime:</span>
                                    <b>{selectedTime ? `${formatTime(selectedTime.show_time || selectedTime.time)} - ${selectedTime.room_name || `Room ${selectedTime.room_id}`}` : '--'}</b>
                                </div>
                                <div className="summary-info-item">
                                    <span>Seats:</span> <b className="text-orange">{selectedSeats.map(s => s.seat_number).join(', ') || '--'}</b>
                                </div>
                                <hr className="summary-divider" />
                                <div className="total-container">
                                    <span className="total-label">Grand Total:</span>
                                    <span className="total-price">{calculateTotal().toLocaleString()}đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Bookings;