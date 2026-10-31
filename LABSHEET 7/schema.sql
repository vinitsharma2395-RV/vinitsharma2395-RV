CREATE DATABASE IF NOT EXISTS bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookstore;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    author VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'Fiction',
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_books_title_author (title, author),
    INDEX idx_books_title (title),
    INDEX idx_books_author (author),
    INDEX idx_books_category (category)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Shipped', 'Delivered') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_orders_user_created (user_id, created_at),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NULL,
    title_snapshot VARCHAR(220) NOT NULL,
    author_snapshot VARCHAR(160) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL,
    INDEX idx_order_items_book (book_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY uq_review_user_book (user_id, book_id),
    INDEX idx_reviews_book (book_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wishlists (
    user_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, book_id),
    CONSTRAINT fk_wishlists_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlists_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO books (title, author, category, description, price, stock) VALUES
('The Midnight Library', 'Matt Haig', 'Fiction', 'Between life and death there is a library, and within that library, the shelves go on forever. Every book offers a chance to try another life you could have lived.', 18.00, 12),
('Atomic Habits', 'James Clear', 'Personal Growth', 'A practical guide to building good habits, breaking bad ones, and getting a little better every day.', 21.50, 8),
('Pachinko', 'Min Jin Lee', 'Fiction', 'A sweeping story of four generations of a Korean family who move to Japan, searching for a place to belong.', 19.00, 6),
('A Brief History of Time', 'Stephen Hawking', 'Science', 'A landmark exploration of the universe, from the big bang to black holes, written for curious readers everywhere.', 16.75, 10),
('Crying in H Mart', 'Michelle Zauner', 'Memoir', 'An unforgettable memoir about family, food, grief, and finding your way back to yourself.', 17.25, 4),
('The Creative Act', 'Rick Rubin', 'Art & Design', 'A generous meditation on the nature of creativity and what it means to make something that matters.', 24.00, 9),
('Tomorrow, and Tomorrow, and Tomorrow', 'Gabrielle Zevin', 'Fiction', 'Two friends find each other, collaborate, and build worlds together in this story about play, love, and the lives we make.', 20.00, 7),
('Braiding Sweetgrass', 'Robin Wall Kimmerer', 'Nature', 'Indigenous wisdom, scientific knowledge, and the teachings of plants come together in these essays on living well with the natural world.', 18.50, 11),
('Educated', 'Tara Westover', 'Memoir', 'A memoir about growing up in an isolated home and finding the courage to seek a different life through education.', 18.95, 5),
('The House in the Cerulean Sea', 'TJ Klune', 'Fantasy', 'A quiet case worker discovers a remarkable home and learns how much can change when someone chooses kindness.', 19.50, 6),
('Quiet', 'Susan Cain', 'Psychology', 'A thoughtful look at the strengths and contributions of introverts in a world that often celebrates the loudest voices.', 16.00, 7),
('The Design of Everyday Things', 'Don Norman', 'Art & Design', 'An accessible guide to the principles behind useful, intuitive design in the objects and systems around us.', 23.00, 3)
ON DUPLICATE KEY UPDATE title = VALUES(title);
