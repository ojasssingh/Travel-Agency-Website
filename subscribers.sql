CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    source VARCHAR(50) DEFAULT 'website',
    CONSTRAINT chk_subscribers_email CHECK (
        email REGEXP '^[^[:space:]@]+@[^[:space:]@]+\\.[^[:space:]@]+$'
    )
);
