import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';

// Components
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import NewsPopup from './components/NewsPopup';

// Pages
import Home from './pages/Home';
import Movies from './pages/Movies';
import MovieDetail from './pages/MovieDetail';
import Cinemas from './pages/Cinemas';
import CinemaDetail from './pages/CinemaDetail'; // Import trang mới
import Bookings from './pages/Bookings';
import BookingDetail from './pages/BookingDetail';
import MyBookings from './pages/MyBookings';
import News from './pages/News'; 
import Login from './pages/Login';
import Register from './pages/Register';

// Styles
import './App.css';

function App() {
  const [user, setUser] = useState(JSON.parse(localStorage.getItem('user')));

  useEffect(() => {
    const handleStorageChange = () => {
      setUser(JSON.parse(localStorage.getItem('user')));
    };
    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, []);

  return (
    <Router>
      <div className="app-container">
        <Navbar user={user} />

        <main className="content-area">
          <Routes>
            {/* --- PUBLIC ROUTES --- */}
            <Route path="/" element={<Home />} />
            <Route path="/movies" element={<Movies />} />
            <Route path="/movie/:id" element={<MovieDetail />} />
            <Route path="/cinemas" element={<Cinemas />} />
            {/* Route chi tiết rạp */}
            <Route path="/cinema/:id" element={<CinemaDetail />} />
            <Route path="/news" element={<News />} />

            {/* --- AUTH ROUTES --- */}
            <Route
              path="/login"
              element={!user ? <Login /> : <Navigate to="/" />}
            />
            <Route
              path="/register"
              element={!user ? <Register /> : <Navigate to="/" />}
            />

            {/* --- PROTECTED ROUTES --- */}
            
            {/* ROUTE MỚI: Nhảy thẳng vào sơ đồ ghế dựa trên showtimeId */}
            <Route
              path="/booking/seat/:showtimeId"
              element={user ? <Bookings user={user} /> : <Navigate to="/login" />}
            />

            <Route
              path="/booking/:movieId"
              element={user ? <Bookings user={user} /> : <Navigate to="/login" />}
            />
            <Route
              path="/booking"
              element={user ? <Bookings user={user} /> : <Navigate to="/login" />}
            />
            <Route
              path="/booking-detail/:bookingId"
              element={user ? <BookingDetail /> : <Navigate to="/login" />}
            />
            <Route
              path="/my-bookings"
              element={user ? <MyBookings /> : <Navigate to="/login" />}
            />

            {/* --- ADMIN ROUTE --- */}
            <Route
              path="/admin/*"
              element={user?.role === 'admin' ? <div>Redirecting to Admin Dashboard...</div> : <Navigate to="/" />}
            />

            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </main>

        <NewsPopup />
        <Footer />
      </div>
    </Router>
  );
}

export default App;