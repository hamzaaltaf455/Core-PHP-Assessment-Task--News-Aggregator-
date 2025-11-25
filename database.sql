CREATE DATABASE IF NOT EXISTS news_aggregator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE news_aggregator;

CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    description TEXT,
    url VARCHAR(1000) NOT NULL,
    image_url VARCHAR(1000),
    source VARCHAR(255),
    category VARCHAR(100),
    published_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_published_date (published_date),
    UNIQUE KEY unique_url (url(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
