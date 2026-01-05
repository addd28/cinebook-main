import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, Autoplay, EffectFade, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import './Home.css';

const Home = () => {
    const navigate = useNavigate();
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [trailerId, setTrailerId] = useState(null);

    const API_BASE = "http://localhost:8888/backend/api";

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

    const getEmbedUrl = (url) => {
        if (!url) return null;
        let id = "";
        if (url.includes('v=')) {
            id = url.split('v=')[1].split('&')[0];
        } else {
            id = url.split('/').pop();
        }
        return `https://www.youtube.com/embed/${id}?autoplay=1&mute=1&controls=0&loop=1&playlist=${id}&modestbranding=1&rel=0`;
    };

    // --- PHẦN THAY ĐỔI: Phân loại và lấy 8 phim mới nhất ---
    const nowShowing = movies
        .filter(m => m.status === 'now_showing')
        .slice(0, 8); // Lấy 8 phim đầu tiên (thường là mới nhất từ API)

    const comingSoon = movies
        .filter(m => m.status === 'coming_soon')
        .slice(0, 8); // Lấy 8 phim đầu tiên
    
    // Banner hiển thị top 5 phim từ danh sách Now Showing
    const hotMovies = nowShowing.length > 0 ? nowShowing.slice(0, 5) : movies.slice(0, 5);
    // -------------------------------------------------------

    const handleOpenTrailer = (url) => {
        if (!url) return;
        const id = url.includes('v=') ? url.split('v=')[1].split('&')[0] : url.split('/').pop();
        setTrailerId(id);
    };

    if (loading) return <div className="loading-screen">Loading Cinema Experience...</div>;

    return (
        <div className="home-v4">
            {/* HERO SLIDER */}
            <section className="hero-slider-wrapper">
                <Swiper
                    modules={[Navigation, Autoplay, EffectFade, Pagination]}
                    effect="fade"
                    navigation={true}
                    pagination={{ clickable: true, dynamicBullets: true }}
                    loop={hotMovies.length > 1}
                    speed={1000}
                    autoplay={{ delay: 8000, disableOnInteraction: false }}
                    className="hero-swiper"
                >
                    {hotMovies.map((movie) => (
                        <SwiperSlide key={movie.id}>
                            <div className="hero-slide-item">
                                <div className="hero-video-container">
                                    {movie.trailer_url ? (
                                        <iframe
                                            className="hero-video-iframe"
                                            src={getEmbedUrl(movie.trailer_url)}
                                            frameBorder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowFullScreen
                                            title={movie.title}
                                        ></iframe>
                                    ) : (
                                        <div className="hero-bg" style={{ backgroundImage: `url(${movie.poster_url})` }}></div>
                                    )}
                                    <div className="hero-overlay-v4"></div>
                                </div>

                                <div className="container hero-container">
                                    <div className="hero-content-v4">
                                        <span className="badge-hot mb-3">FEATURED MOVIE</span>
                                        <h1 className="movie-title-v4">{movie.title}</h1>
                                        <div className="d-flex align-items-center gap-3 mb-3">
                                            <span className="text-orange fw-bold">
                                                <i className="fas fa-star me-1"></i>{movie.rating_avg || '0.0'}
                                            </span>
                                            <span className="text-white-50">|</span>
                                            <span className="text-white small fw-bold text-uppercase">{movie.duration} MIN</span>
                                            <span className="text-white-50">|</span>
                                            <span className="badge bg-dark border border-secondary">{movie.genre || 'Action'}</span>
                                        </div>
                                        <p className="movie-desc-v4">{movie.synopsis}</p>
                                        <div className="d-flex flex-wrap gap-3 mt-4">
                                            <button
                                                className="btn btn-premium-orange px-5 py-3 fw-bold"
                                                onClick={() => navigate(`/booking/${movie.id}`)}
                                                disabled={movie.status === 'coming_soon'}
                                                style={{ opacity: movie.status === 'coming_soon' ? 0.5 : 1 }}
                                            >
                                                {movie.status === 'coming_soon' ? 'COMING SOON' : 'BOOK TICKETS'}
                                            </button>
                                            <button className="btn btn-glass px-5 py-3 fw-bold" onClick={() => handleOpenTrailer(movie.trailer_url)}>
                                                WATCH TRAILER
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </SwiperSlide>
                    ))}
                </Swiper>
            </section>

            {/* MAIN LISTINGS */}
            <div className="main-content-v4 container">
                {/* NOW SHOWING */}
                <section className="py-5">
                    <div className="d-flex justify-content-between align-items-center mb-5 section-header">
                        <h2 className="section-title">NOW <span className="text-orange">SHOWING</span></h2>
                        <button className="btn-view-all" onClick={() => navigate('/movies')}>
                            VIEW ALL <i className="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                    <div className="row g-4">
                        {nowShowing.map(movie => (
                            <div key={movie.id} className="col-6 col-md-4 col-lg-3">
                                <div className="movie-card-v4">
                                    <div className="poster-wrapper">
                                        <img src={movie.poster_url} alt={movie.title} loading="lazy" />
                                        <div className="card-hover-overlay">
                                            <div className="rating-tag">★ {movie.rating_avg}</div>
                                            <div className="d-flex flex-column gap-2 w-75">
                                                <button className="btn btn-premium-orange btn-sm fw-bold" onClick={() => navigate(`/booking/${movie.id}`)}>
                                                    BOOK NOW
                                                </button>
                                                <button className="btn btn-light btn-sm fw-bold" onClick={() => navigate(`/movie/${movie.id}`)}>
                                                    DETAILS
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 className="movie-name mt-2">{movie.title}</h6>
                                    <p className="small text-white-50">{movie.duration} MIN</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* COMING SOON */}
                <section className="py-5">
                    <div className="section-header mb-5">
                        <h2 className="section-title">COMING <span className="text-orange">SOON</span></h2>
                    </div>
                    <div className="row g-4">
                        {comingSoon.map(movie => (
                            <div key={movie.id} className="col-6 col-md-4 col-lg-3">
                                <div className="movie-card-v4 soon" onClick={() => navigate(`/movie/${movie.id}`)}>
                                    <div className="poster-wrapper">
                                        <img src={movie.poster_url} alt={movie.title} loading="lazy" />
                                        <div className="release-date-tag">RELEASE: {movie.release_date}</div>
                                        <div className="card-hover-overlay">
                                             <button className="btn btn-premium-orange btn-sm fw-bold">VIEW DETAILS</button>
                                        </div>
                                    </div>
                                    <h6 className="movie-name text-white mt-2">{movie.title}</h6>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>
            </div>

            {/* TRAILER MODAL */}
            {trailerId && (
                <div className="trailer-modal-overlay" onClick={() => setTrailerId(null)}>
                    <div className="trailer-modal-content" onClick={e => e.stopPropagation()}>
                        <button className="close-modal" onClick={() => setTrailerId(null)}>&times;</button>
                        <div className="video-responsive">
                            <iframe
                                src={`https://www.youtube.com/embed/${trailerId}?autoplay=1`}
                                title="YouTube video player"
                                frameBorder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowFullScreen
                            ></iframe>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Home;