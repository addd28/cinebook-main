import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './CinemaDetail.css';

const CinemaDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [cinema, setCinema] = useState(null);
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);

    // Cấu hình URL để lấy ảnh
    const IMAGE_BASE_URL = "http://localhost:8888/uploads/movies/";
    const CINEMA_IMAGE_URL = "http://localhost:8888/uploads/cinemas/";
    const API_BASE = "http://localhost:8888/backend/api";

    useEffect(() => {
        const fetchCinemaDetails = async () => {
            try {
                const response = await axios.get(`${API_BASE}/get_cinema_details.php?id=${id}`);
                if (response.data && !response.data.error) {
                    setCinema(response.data.cinema);
                    setMovies(response.data.movies);
                }
                setLoading(false);
            } catch (error) {
                console.error("Error fetching cinema details:", error);
                setLoading(false);
            }
        };
        fetchCinemaDetails();
    }, [id]);

    if (loading) return (
        <div className="loading-screen text-center py-5">
            <div className="spinner-border text-orange" role="status"></div>
            <p className="mt-3 text-white">Loading Cinema Schedule...</p>
        </div>
    );

    if (!cinema) return <div className="container py-5 text-white">Cinema not found.</div>;

    return (
        <div className="cinema-detail-page bg-dark min-vh-100">
            {/* Header Banner */}
            <div className="cinema-banner" style={{ 
                backgroundImage: `linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url(${CINEMA_IMAGE_URL}${cinema.image})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                padding: '80px 0'
            }}>
                <div className="container text-center text-md-start">
                    <h1 className="display-4 fw-bold text-white text-uppercase">{cinema.name}</h1>
                    <p className="lead text-white-50">
                        <i className="fas fa-map-marker-alt text-orange me-2"></i>
                        {cinema.address}
                    </p>
                </div>
            </div>

            <div className="container py-5">
                <div className="d-flex align-items-center mb-5">
                    <div style={{width: '50px', height: '3px', backgroundColor: '#f37021', marginRight: '15px'}}></div>
                    <h2 className="text-white text-uppercase m-0">Now Showing</h2>
                </div>
                
                {movies.length > 0 ? (
                    <div className="movie-schedule-list">
                        {movies.map((movie) => (
                            <div key={movie.id} className="movie-schedule-item mb-4 p-4 shadow-lg rounded bg-dark-light border border-secondary">
                                <div className="row align-items-center">
                                    {/* Ảnh Poster Phim */}
                                    <div className="col-md-3 col-lg-2 mb-3 mb-md-0">
                                        <div className="poster-wrapper">
                                            <img 
                                                src={`${IMAGE_BASE_URL}${movie.poster}`} 
                                                alt={movie.title} 
                                                className="img-fluid rounded shadow border border-secondary w-100"
                                                onError={(e) => { e.target.src = "https://via.placeholder.com/300x450?text=No+Poster"; }}
                                            />
                                        </div>
                                    </div>
                                    
                                    {/* Thông tin phim */}
                                    <div className="col-md-6 col-lg-7 text-white">
                                        <h3 className="text-orange fw-bold mb-2">{movie.title}</h3>
                                        <div className="mb-3 d-flex align-items-center flex-wrap gap-3">
                                            <span className="badge bg-warning text-dark">Rating: {movie.rating_avg}/5</span>
                                            <span className="text-white-50"><i className="far fa-clock me-1 text-orange"></i> {movie.duration} MINS</span>
                                            <span className="text-white-50 text-uppercase small"><i className="fas fa-film me-1 text-orange"></i> 2D DIGITAL</span>
                                        </div>
                                        
                                        <p className="text-white-50 small mb-0 movie-desc" style={{ display: '-webkit-box', WebkitLineClamp: '3', WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                                            {movie.synopsis || "No description available for this movie."}
                                        </p>
                                    </div>

                                    {/* Nút Buy Tickets duy nhất */}
                                    <div className="col-md-3 col-lg-3 text-center text-md-end mt-3 mt-md-0">
                                        <button 
                                            className="btn btn-orange px-5 py-3 fw-bold text-uppercase rounded-pill shadow-sm"
                                            style={{backgroundColor: '#f37021', color: 'white', border: 'none'}}
                                            onClick={() => navigate(`/booking/${movie.id}?cinema=${id}`)}
                                        >
                                            <i className="fas fa-ticket-alt me-2"></i>
                                            Buy Tickets
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center text-white-50 py-5 bg-dark rounded border border-secondary">
                        <i className="fas fa-calendar-times fa-3x mb-3 text-orange"></i>
                        <p>No movies currently playing at this cinema.</p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default CinemaDetail;