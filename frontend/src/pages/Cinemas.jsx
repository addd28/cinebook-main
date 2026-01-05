import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import './Cinemas.css';

const Cinemas = () => {
    const [cinemas, setCinemas] = useState([]);
    const [cities, setCities] = useState(['All Cities']);
    const [selectedCity, setSelectedCity] = useState('All Cities');
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    const API_BASE = "http://localhost:8888/backend/api";

    useEffect(() => {
        const fetchCinemas = async () => {
            try {
                const response = await axios.get(`${API_BASE}/get_cinemas.php`);
                setCinemas(response.data);
                const uniqueCities = ['All Cities', ...new Set(response.data.map(c => c.city))];
                setCities(uniqueCities);
                setLoading(false);
            } catch (error) {
                console.error("Fetch error:", error);
                setLoading(false);
            }
        };
        fetchCinemas();
    }, []);

    const filteredCinemas = selectedCity === 'All Cities'
        ? cinemas
        : cinemas.filter(c => c.city === selectedCity);

    return (
        <div className="cinemas-container-v4">
            <div className="cinemas-hero-v4">
                <div className="container text-center">
                    <span className="badge-premium mb-2">PREMIUM NETWORK</span>
                    <h1 className="display-4 fw-bold text-white">OUR <span className="text-orange">LOCATIONS</span></h1>
                </div>
            </div>

            <div className="container py-5">
                <div className="cinema-filter-wrapper mb-5 text-center">
                    <div className="city-tabs">
                        {cities.map(city => (
                            <button
                                key={city}
                                className={`city-tab-btn ${selectedCity === city ? 'active' : ''}`}
                                onClick={() => setSelectedCity(city)}
                            >
                                {city}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="row g-4">
                    {filteredCinemas.map((cinema) => (
                        <div key={cinema.id} className="col-md-6 col-lg-4">
                            <div className="cinema-card-v4">
                                <div className="cinema-poster">
                                    <img
                                        src={cinema.image_url}
                                        alt={cinema.name}
                                        onError={(e) => {
                                            e.target.onerror = null;
                                            e.target.src = "https://placehold.co/800x500?text=Cinema+Image"; // Dùng link dự phòng khác
                                        }}
                                    />
                                    <div className="cinema-badge">{cinema.city}</div>
                                    <div className="cinema-overlay">
                                        <button
                                            className="btn btn-premium-orange px-4 fw-bold"
                                            onClick={() => navigate(`/cinema/${cinema.id}`)}
                                        >
                                            SELECT CINEMA
                                        </button>
                                    </div>
                                </div>
                                <div className="cinema-info-v4">
                                    <h5 className="cinema-title">{cinema.name}</h5>
                                    <p className="cinema-address">
                                        <i className="fas fa-map-marker-alt text-orange me-2"></i>
                                        {cinema.address}
                                    </p>
                                    <div className="cinema-amenities">
                                        <span><i className="fas fa-video"></i> 4K</span>
                                        <span><i className="fas fa-volume-up"></i> Atmos</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default Cinemas;