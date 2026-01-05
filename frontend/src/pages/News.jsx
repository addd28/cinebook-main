import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './News.css';

const News = () => {
    const [newsList, setNewsList] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedNews, setSelectedNews] = useState(null);

    const API_BASE = "http://localhost:8888/backend/api";

    useEffect(() => {
        fetchNews();
        window.scrollTo(0, 0);
    }, []);

    const fetchNews = async () => {
        try {
            const res = await axios.get(`${API_BASE}/get_news.php`);
            setNewsList(Array.isArray(res.data) ? res.data : []);
            setLoading(false);
        } catch (err) {
            console.error("Error loading news:", err);
            setLoading(false);
        }
    };

    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    };

    if (loading) return (
        <div className="news-loader-container">
            <div className="news-spinner"></div>
        </div>
    );

    return (
        <div className="news-page-v4">
            <div className="container">
                {/* Header Section */}
                <div className="text-center mb-5">
                    <h2 className="section-title">News & Promotions</h2>
                    <div className="title-underline"></div>
                </div>

                {/* News Grid */}
                <div className="row g-4">
                    {newsList.length > 0 ? (
                        newsList.map((item) => (
                            <div className="col-lg-4 col-md-6" key={item.id}>
                                <div className="news-card-v4">
                                    <div className="news-image-wrapper">
                                        <img 
                                            src={`http://localhost:8888/uploads/news/${item.image}`} 
                                            alt={item.title} 
                                            className="news-img"
                                            onError={(e) => e.target.src = "https://via.placeholder.com/400x250?text=CineStar+News"}
                                        />
                                        <div className="news-date-tag">
                                            <i className='far fa-calendar-alt'></i> {formatDate(item.created_at)}
                                        </div>
                                    </div>
                                    <div className="news-body-v4">
                                        <h5 className="news-card-title">{item.title}</h5>
                                        <p className="news-summary-text">
                                            {item.summary || "Click to view more details about this exclusive promotion..."}
                                        </p>
                                        <button 
                                            className="btn-readmore-v4" 
                                            onClick={() => setSelectedNews(item)}
                                            data-bs-toggle="modal" 
                                            data-bs-target="#newsModal"
                                        >
                                            LEARN MORE <i className='fas fa-arrow-right ms-2'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-12 text-center py-5">
                            <p className="text-muted">No news available at the moment.</p>
                        </div>
                    )}
                </div>
            </div>

            {/* News Detail Modal */}
            <div className="modal fade" id="newsModal" tabIndex="-1" aria-hidden="true">
                <div className="modal-dialog modal-lg modal-dialog-centered">
                    <div className="modal-content news-modal-content">
                        {selectedNews && (
                            <>
                                <div className="modal-header border-0">
                                    <button type="button" className="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div className="modal-body p-4 pt-0">
                                    <img 
                                        src={`http://localhost:8888/uploads/news/${selectedNews.image}`} 
                                        className="w-100 rounded-4 mb-4 shadow-sm" 
                                        alt={selectedNews.title} 
                                    />
                                    <h3 className="modal-news-title">{selectedNews.title}</h3>
                                    <div className="d-flex align-items-center mb-4 news-meta-info">
                                        <i className='far fa-clock me-2'></i> Posted on: {formatDate(selectedNews.created_at)}
                                    </div>
                                    <div className="news-detail-rich-text">
                                        <div dangerouslySetInnerHTML={{ __html: selectedNews.content }} />
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default News;