import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import './NewsPopup.css';

const NewsPopup = () => {
    const [news, setNews] = useState(null);
    const [isVisible, setIsVisible] = useState(false);
    const API_BASE = "http://localhost:8888/backend/api";

    useEffect(() => {
        const fetchLatestNews = async () => {
            try {
                const res = await axios.get(`${API_BASE}/get_news.php?limit=1`);
                if (res.data && res.data.length > 0) {
                    const latestNews = res.data[0];
                    setNews(latestNews);

                    // Kiểm tra trạng thái đã đóng trong Session
                    const isClosed = sessionStorage.getItem(`news_closed_${latestNews.id}`);
                    if (!isClosed) {
                        setTimeout(() => setIsVisible(true), 2000);
                    }
                }
            } catch (err) {
                console.error("News Fetch Error:", err);
            }
        };
        fetchLatestNews();
    }, []);

    const handleClose = () => {
        setIsVisible(false);
        if (news) {
            // Đánh dấu đã đóng cho phiên này
            sessionStorage.setItem(`news_closed_${news.id}`, 'true');

            // Tùy chọn: Xóa sau 5 phút để có thể hiện lại sau
            setTimeout(() => {
                sessionStorage.removeItem(`news_closed_${news.id}`);
            }, 300000);
        }
    };

    if (!isVisible || !news) return null;

    return (
        <div className="news-popup-v4 shadow-lg animate-slide-up">
            {/* Nút đóng */}
            <button className="news-popup-close" onClick={handleClose} aria-label="Close">
                <i className="fas fa-times"></i>
            </button>
            <div className="d-flex align-items-center">
                <div className="news-popup-thumb">
                    <img
                        src={`http://localhost:8888/uploads/news/${news.image}`}
                        alt="News"
                        onError={(e) => e.target.src = "https://via.placeholder.com/80"}
                    />
                </div>
                <div className="news-popup-info ms-3">
                    <span className="news-popup-badge">NEW UPDATE</span>
                    <h6 className="news-popup-title mb-1">{news.title}</h6>

                    {/* QUAN TRỌNG: Dùng Link thay cho thẻ <a> và gọi handleClose */}
                    <Link
                        to="/news"
                        className="news-popup-link"
                        onClick={handleClose}
                    >
                        VIEW NOW <i className="fas fa-chevron-right ms-1"></i>
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default NewsPopup;