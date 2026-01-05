import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './MovieDetail.css';

const MovieDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    
    // Movie states
    const [movie, setMovie] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showTrailer, setShowTrailer] = useState(false);

    // Review states
    const [reviews, setReviews] = useState([]);
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');
    const [reviewMsg, setReviewMsg] = useState({ type: '', text: '' });

    // Get User info from localStorage (assuming saved after login)
    const user = JSON.parse(localStorage.getItem('user')) || null;
    const API_BASE = "http://localhost:8888/backend/api";

    useEffect(() => {
        const fetchMovieData = async () => {
            try {
                // 1. Fetch movie details
                const movieRes = await axios.get(`${API_BASE}/get_movies.php?id=${id}`);
                setMovie(movieRes.data);

                // 2. Fetch reviews list
                const reviewRes = await axios.get(`${API_BASE}/get_reviews.php?movie_id=${id}`);
                setReviews(reviewRes.data);

                setLoading(false);
            } catch (error) {
                console.error("Error fetching data:", error);
                setLoading(false);
            }
        };

        fetchMovieData();
        window.scrollTo(0, 0);
    }, [id]);

    const handleReviewSubmit = async (e) => {
        e.preventDefault();
        if (!user) {
            setReviewMsg({ type: 'danger', text: 'Please log in to leave a review!' });
            return;
        }
        if (!comment.trim()) {
            setReviewMsg({ type: 'warning', text: 'Please enter your review content.' });
            return;
        }

        try {
            const response = await axios.post(`${API_BASE}/post_review.php`, {
                user_id: user.id,
                movie_id: id,
                rating: rating,
                comment: comment
            });

            if (response.data.success) {
                setReviewMsg({ type: 'success', text: 'Thank you for your review!' });
                setComment('');
                // Refresh reviews list
                const refresh = await axios.get(`${API_BASE}/get_reviews.php?movie_id=${id}`);
                setReviews(refresh.data);
            } else {
                setReviewMsg({ type: 'danger', text: response.data.message });
            }
        } catch (error) {
            setReviewMsg({ type: 'danger', text: 'Server connection error.' });
        }
    };

    const getEmbedUrl = (url) => {
        if (!url) return null;
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? `https://www.youtube.com/embed/${match[2]}` : null;
    };

    if (loading) return <div className="loading-container"><div className="cinema-spinner"></div><p>Loading cinematic experience...</p></div>;
    if (!movie) return <div className="error-container text-center"><h2>Oops!</h2><button className="btn-premium-orange" onClick={() => navigate('/')}>Back to Home</button></div>;

    return (
        <div className="movie-detail-v4">
            {/* HERO SECTION */}
            <section className="detail-hero">
                <div className="hero-bg-blur" style={{ backgroundImage: `url(${movie.poster_url})` }}></div>
                <div className="hero-overlay-detail"></div>
                <div className="container detail-container">
                    <div className="row align-items-end">
                        <div className="col-lg-4 col-md-5">
                            <div className="detail-poster-wrapper">
                                <img src={movie.poster_url} alt={movie.title} className="img-fluid main-poster shadow-lg" />
                                {movie.status === 'coming_soon' && <div className="status-label coming-soon">COMING SOON</div>}
                            </div>
                        </div>
                        <div className="col-lg-8 col-md-7 detail-content-main">
                            <div className="premium-badge">Exclusive Release</div>
                            <h1 className="detail-title">{movie.title}</h1>
                            <div className="detail-meta-group">
                                <div className="meta-badge"><i className="fas fa-star text-orange"></i> <span>{movie.rating_avg || '0.0'}</span></div>
                                <span className="meta-divider">•</span>
                                <div className="meta-badge"><i className="far fa-clock text-orange"></i> <span>{movie.duration} mins</span></div>
                                <span className="meta-divider">•</span>
                                <div className="meta-badge"><i className="far fa-calendar-alt text-orange"></i> <span>{new Date(movie.release_date).getFullYear()}</span></div>
                            </div>
                            <div className="detail-genres">
                                {movie.genre?.split(',').map((g, idx) => <span key={idx} className="genre-tag">{g.trim()}</span>)}
                            </div>
                            <div className="detail-summary">
                                <h4>Synopsis</h4>
                                <p>{movie.synopsis || "No synopsis available for this title."}</p>
                            </div>
                            <div className="detail-btn-group">
                                {movie.status !== 'coming_soon' ? (
                                    <button className="btn-premium-orange btn-lg shadow-lg" onClick={() => navigate(`/booking/${movie.id}`)}>
                                        <i className="fas fa-ticket-alt me-2"></i>BOOK TICKETS NOW
                                    </button>
                                ) : (
                                    <button className="btn-status-disabled btn-lg" disabled><i className="fas fa-hourglass-start me-2"></i>COMING SOON</button>
                                )}
                                {movie.trailer_url && (
                                    <button className="btn-glass-detail btn-lg" onClick={() => setShowTrailer(true)}>
                                        <i className="fas fa-play me-2"></i>WATCH TRAILER
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECONDARY DETAILS & REVIEWS */}
            <section className="container py-5">
                <div className="row g-4">
                    <div className="col-lg-8">
                        {/* Production Info */}
                        <div className="info-card-premium mb-5">
                            <h4 className="section-title">Production Details</h4>
                            <div className="row mt-4">
                                <div className="col-md-6 mb-3">
                                    <div className="info-item"><label>Director</label><p>{movie.director || 'N/A'}</p></div>
                                    <div className="info-item"><label>Country</label><p>{movie.country || 'International'}</p></div>
                                </div>
                                <div className="col-md-6 mb-3">
                                    <div className="info-item"><label>Language</label><p>English / Subtitles</p></div>
                                    <div className="info-item"><label>Format</label><p>2D, 3D, IMAX 4K</p></div>
                                </div>
                                <div className="col-12"><div className="info-item"><label>Cast</label><p className="cast-list">{movie.actors_list || 'Updating...'}</p></div></div>
                            </div>
                        </div>

                        {/* REVIEWS SECTION */}
                        <div className="reviews-container text-white">
                            <h4 className="section-title mb-4">Audience <span className="text-orange">Reviews</span></h4>
                            
                            {/* Review Form */}
                            <div className="bg-dark-card p-4 rounded-4 mb-5 shadow-sm border border-secondary">
                                <h5 className="mb-3 small text-uppercase fw-bold">Leave a Review</h5>
                                {reviewMsg.text && <div className={`alert alert-${reviewMsg.type} py-2 small`}>{reviewMsg.text}</div>}
                                <form onSubmit={handleReviewSubmit}>
                                    <div className="mb-3">
                                        {[1, 2, 3, 4, 5].map(star => (
                                            <i key={star} 
                                               className={`fas fa-star fa-lg me-2 cursor-pointer ${star <= rating ? 'text-orange' : 'text-secondary'}`}
                                               onClick={() => setRating(star)}
                                               style={{ cursor: 'pointer' }}></i>
                                        ))}
                                    </div>
                                    <textarea className="form-control bg-dark text-white border-secondary mb-3" 
                                              rows="3" placeholder="How was the movie? Share your thoughts..."
                                              value={comment} onChange={(e) => setComment(e.target.value)}></textarea>
                                    <button className="btn-premium-orange px-4 py-2 fw-bold small">SUBMIT REVIEW</button>
                                </form>
                            </div>

                            {/* Reviews List */}
                            <div className="reviews-list">
                                {reviews.length > 0 ? reviews.map((r, i) => (
                                    <div key={i} className="review-item mb-4 pb-3 border-bottom border-secondary">
                                        <div className="d-flex justify-content-between align-items-center mb-2">
                                            <h6 className="mb-0 fw-bold">{r.user_name} <i className="fas fa-check-circle text-success ms-1 small"></i></h6>
                                            <span className="small text-white-50">{r.created_at}</span>
                                        </div>
                                        <div className="text-warning small mb-2">
                                            {[...Array(5)].map((_, idx) => <i key={idx} className={`${idx < r.rating ? 'fas' : 'far'} fa-star`}></i>)}
                                        </div>
                                        <p className="text-white-50 small mb-0">{r.comment}</p>
                                    </div>
                                )) : <p className="text-muted italic">No reviews yet. Be the first to share your thoughts!</p>}
                            </div>
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="col-lg-4">
                        <div className="sidebar-premium-box position-sticky" style={{ top: '100px' }}>
                            <div className="promo-icon"><i className="fas fa-crown"></i></div>
                            <h5>Premium Membership</h5>
                            <p className="small">Join now to get 10% discount on online bookings and earn points for free snacks.</p>
                            <button className="btn btn-outline-light btn-sm w-100 mt-2">LEARN MORE</button>
                        </div>
                    </div>
                </div>
            </section>

            {/* TRAILER MODAL */}
            {showTrailer && (
                <div className="trailer-modal-overlay" onClick={() => setShowTrailer(false)}>
                    <div className="trailer-modal-content" onClick={e => e.stopPropagation()}>
                        <button className="close-trailer" onClick={() => setShowTrailer(false)}>&times;</button>
                        <div className="ratio ratio-16x9">
                            <iframe src={`${getEmbedUrl(movie.trailer_url)}?autoplay=1`} title="Movie Trailer" allowFullScreen allow="autoplay"></iframe>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default MovieDetail;