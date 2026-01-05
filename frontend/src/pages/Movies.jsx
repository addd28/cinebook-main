import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';
import './Movies.css';

const Movies = () => {
    const navigate = useNavigate();
    const location = useLocation(); // Hook để lấy thông tin URL
    
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedGenre, setSelectedGenre] = useState('All');

    const API_BASE = "http://localhost:8888/backend/api";
    const genres = ['All', 'Action', 'Animation', 'Horror', 'Comedy', 'Romance', 'Sci-Fi'];

    // 1. Đồng bộ searchTerm với URL khi trang load hoặc URL thay đổi
    useEffect(() => {
        const queryParams = new URLSearchParams(location.search);
        const searchFromUrl = queryParams.get('search');
        if (searchFromUrl !== null) {
            setSearchTerm(searchFromUrl);
        }
    }, [location.search]);

    // 2. Fetch danh sách phim từ API
    useEffect(() => {
        const fetchMovies = async () => {
            try {
                const response = await axios.get(`${API_BASE}/get_movies.php`);
                setMovies(response.data);
                setLoading(false);
            } catch (error) {
                console.error("API Connection Error:", error);
                setLoading(false);
            }
        };
        fetchMovies();
    }, []);

    // Hàm lọc phim theo Tìm kiếm và Thể loại
    const filterLogic = (movieList) => {
        return movieList.filter(movie => {
            const matchesSearch = movie.title.toLowerCase().includes(searchTerm.toLowerCase());
            
            // Logic lọc theo thể loại
            const matchesGenre = selectedGenre === 'All' || 
                (movie.genre && movie.genre.split(', ').some(g => g.toLowerCase() === selectedGenre.toLowerCase()));
            
            return matchesSearch && matchesGenre;
        });
    };

    // Phân loại phim dựa trên status
    const nowShowing = filterLogic(movies.filter(m => m.status === 'now_showing'));
    const comingSoon = filterLogic(movies.filter(m => m.status === 'coming_soon'));

    if (loading) return (
        <div className="loading-screen">
            <div className="spinner-border text-orange" role="status"></div>
            <span className="ms-3 text-white">Loading Movie Library...</span>
        </div>
    );

    // Helper function render Card phim
    const renderMovieCard = (movie) => (
        <div key={movie.id} className="col-6 col-md-4 col-lg-3 animate__animated animate__fadeIn">
            <div className="movie-card-v4">
                <div className="poster-wrapper">
                    <img src={movie.poster_url} alt={movie.title} loading="lazy" />
                    <div className="card-hover-overlay">
                        {movie.status === 'now_showing' ? (
                            <>
                                <div className="rating-tag">★ {movie.rating_avg || '0.0'}</div>
                                <div className="d-flex flex-column gap-2 w-75">
                                    <button 
                                        className="btn btn-premium-orange btn-sm fw-bold" 
                                        onClick={() => navigate(`/booking/${movie.id}`)}
                                    >
                                        BOOK NOW
                                    </button>
                                    <button 
                                        className="btn btn-light btn-sm fw-bold" 
                                        onClick={() => navigate(`/movie/${movie.id}`)}
                                    >
                                        DETAILS
                                    </button>
                                </div>
                            </>
                        ) : (
                            <div className="d-flex flex-column gap-2 w-75">
                                <button 
                                    className="btn btn-premium-orange btn-sm fw-bold" 
                                    onClick={() => navigate(`/movie/${movie.id}`)}
                                >
                                    VIEW DETAILS
                                </button>
                            </div>
                        )}
                    </div>
                    {movie.status === 'coming_soon' && (
                        <div className="status-badge-soon">SOON</div>
                    )}
                </div>
                <div className="movie-info-v4 mt-3">
                    <h6 className="movie-name text-truncate" title={movie.title}>{movie.title}</h6>
                    <div className="d-flex justify-content-between align-items-center">
                        <span className="small text-white-50">{movie.genre ? movie.genre.split(',')[0] : 'General'}</span>
                        <span className="small text-orange">{movie.duration} MIN</span>
                    </div>
                </div>
            </div>
        </div>
    );

    return (
        <div className="movies-page-v4">
            <div className="movies-header text-center">
                <div className="container">
                    <h1 className="display-4 fw-bold text-white">MOVIE <span className="text-orange">LIBRARY</span></h1>
                    <p className="text-white-50">Explore current hits and upcoming blockbusters</p>
                </div>
            </div>

            <div className="container mt-n5">
                {/* Filter & Search Bar */}
                <div className="filter-bar-v4 shadow-lg p-4 rounded-4 bg-dark-card mb-5">
                    <div className="row g-3 align-items-center">
                        <div className="col-lg-4">
                            <div className="search-input-wrapper position-relative">
                                <i className="fas fa-search search-icon"></i>
                                <input 
                                    type="text" 
                                    placeholder="Search by title..." 
                                    className="form-control-v4 ps-5"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                />
                            </div>
                        </div>
                        <div className="col-lg-8">
                            <div className="genres-wrapper d-flex gap-2 flex-wrap justify-content-lg-end">
                                {genres.map(genre => (
                                    <button 
                                        key={genre}
                                        className={`genre-btn ${selectedGenre === genre ? 'active' : ''}`}
                                        onClick={() => setSelectedGenre(genre)}
                                    >
                                        {genre}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Section: NOW SHOWING */}
                {nowShowing.length > 0 && (
                    <section className="movie-section-mb">
                        <div className="section-header-v4 mb-4 d-flex align-items-center gap-3">
                            <h2 className="section-title mb-0">NOW <span className="text-orange">SHOWING</span></h2>
                            <div className="flex-grow-1 title-line"></div>
                        </div>
                        <div className="row g-4 mb-5">
                            {nowShowing.map(movie => renderMovieCard(movie))}
                        </div>
                    </section>
                )}

                {/* Section: COMING SOON */}
                {comingSoon.length > 0 && (
                    <section className="movie-section-mb mt-5">
                        <div className="section-header-v4 mb-4 d-flex align-items-center gap-3">
                            <h2 className="section-title mb-0">COMING <span className="text-orange">SOON</span></h2>
                            <div className="flex-grow-1 title-line-gray"></div>
                        </div>
                        <div className="row g-4 mb-5">
                            {comingSoon.map(movie => renderMovieCard(movie))}
                        </div>
                    </section>
                )}

                {/* No Results Handling */}
                {(nowShowing.length === 0 && comingSoon.length === 0) && (
                    <div className="text-center py-5 animate__animated animate__fadeIn">
                        <i className="fas fa-film fa-3x text-white-50 mb-3"></i>
                        <h3 className="text-white-50">No movies found for "{searchTerm}"</h3>
                        <button className="btn btn-outline-orange mt-3" onClick={() => {
                            setSearchTerm(''); 
                            setSelectedGenre('All');
                            navigate('/movies'); // Xóa cả query param trên URL
                        }}>
                            Clear all filters
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Movies;