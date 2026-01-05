import React from 'react';
import { NavLink } from 'react-router-dom';
import './Footer.css';

const Footer = () => {
  return (
    <footer className="premium-footer-v4">
      <div className="container">
        <div className="row g-4">
          {/* Column 1: Brand & Social */}
          <div className="col-lg-4 col-md-6">
            <div className="brand-wrapper mb-3">
              <i className="fas fa-star brand-star"></i>
              <span className="brand-text-white">CINE</span>
              <span className="brand-text-orange">STAR</span>
            </div>
            <p className="footer-text-muted">
              The most modern cinema system, bringing a truly immersive cinematic experience with world-leading IMAX and Dolby Atmos technology.
            </p>
            <div className="social-links-v4 d-flex gap-3 mt-4">
              <a href="#" className="social-item"><i className="fab fa-facebook-f"></i></a>
              <a href="#" className="social-item"><i className="fab fa-instagram"></i></a>
              <a href="#" className="social-item"><i className="fab fa-youtube"></i></a>
              <a href="#" className="social-item"><i className="fab fa-tiktok"></i></a>
            </div>
          </div>

          {/* Column 2: Navigation */}
          <div className="col-lg-2 col-md-6">
            <h5 className="footer-heading-v4">SERVICES</h5>
            <ul className="footer-list-v4">
              <li><NavLink to="/">Home</NavLink></li>
              <li><NavLink to="/bookings">Showtimes</NavLink></li>
              <li><NavLink to="/news">Offers</NavLink></li>
              <li><NavLink to="/movies">Now Showing</NavLink></li>
            </ul>
          </div>

          {/* Column 3: Policies */}
          <div className="col-lg-3 col-md-6">
            <h5 className="footer-heading-v4">POLICIES</h5>
            <ul className="footer-list-v4">
              <li><a href="#">Terms of Use</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Refund Policy</a></li>
              <li><a href="#">FAQs</a></li>
            </ul>
          </div>

          {/* Column 4: Contact */}
          <div className="col-lg-3 col-md-6">
            <h5 className="footer-heading-v4">CUSTOMER CARE</h5>
            <div className="contact-info-v4 mb-2">
              Hotline: <span className="text-orange fw-bold">1900 6017</span>
            </div>
            <div className="contact-info-v4 mb-4">
              Email: <span className="text-white-50">support@cinestar.com.vn</span>
            </div>
          </div>
        </div>

        <div className="footer-bottom-v4">
          <p className="mb-0">© 2025 CINESTAR Project. All Rights Reserved. Crafted for Cinema Lovers.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;