-- FLF — FreeLanceFinder schema
-- Import via HeidiSQL / Adminer / terminal:
--   mysql -u root < sql/schema.sql

DROP DATABASE IF EXISTS flf;
CREATE DATABASE flf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flf;

-- ---------- users ----------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','lancer') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- lancers ----------
CREATE TABLE lancers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  title VARCHAR(150) NOT NULL DEFAULT '',
  skill VARCHAR(150) NOT NULL DEFAULT '',
  category ENUM('IT','Service','Others') NOT NULL DEFAULT 'Others',
  bio TEXT,
  location VARCHAR(120) DEFAULT '',
  rate DECIMAL(10,2) DEFAULT 0,
  rating DECIMAL(2,1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- testimonials (homepage slider) ----------
CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  role VARCHAR(150) DEFAULT '',
  message TEXT NOT NULL,
  rating TINYINT NOT NULL DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- seed testimonials ----------
INSERT INTO testimonials (name, role, message, rating) VALUES
('Maya Rahman', 'Café owner', 'I found a logo designer in two days. The search actually works — no endless scrolling.', 5),
('Joel Santoso', 'Photographer', 'As a Lancer, I finally have a place to be found. My first client came from here.', 5),
('Dewi Nuraini', 'Small business owner', 'Simple, clean, no clutter. I hired a handyman and a copywriter in the same week.', 4),
('Rizky Kurnia', 'Front-end developer', 'The profile page made it easy to show what I do. Clients understand my rates instantly.', 5);

-- ---------- seed demo users ----------
-- All demo accounts use the password:  password
INSERT INTO users (name, email, password_hash, role) VALUES
('Ariana Putri',   'ariana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lancer'),
('Bagas Wijaya',   'bagas@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lancer'),
('Citra Lestari',  'citra@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lancer'),
('Damar Prasetyo', 'damar@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lancer'),
('Eka Saputra',    'eka@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lancer'),
('Fira Ananda',    'fira@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lancer'),
('Hendra Kusuma',  'hendra@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- ---------- seed lancers (with category) ----------
INSERT INTO lancers (user_id, title, skill, category, bio, location, rate, rating) VALUES
(1, 'Brand & UI Designer',   'Design',      'IT',      'I craft clean brand identities and product interfaces for small teams.',   'Jakarta',    350000, 4.9),
(2, 'Full-stack Developer',  'Code',        'IT',      'I build fast web apps with PHP, Laravel, and modern JavaScript.',          'Bandung',    500000, 4.8),
(3, 'Content Writer',        'Writing',     'Others',  'Long-form articles, SEO copy, and newsletters that actually convert.',     'Yogyakarta', 200000, 4.7),
(4, 'Photographer',          'Photography', 'Service', 'Product, event, and portrait photography. Studio or on location.',        'Surabaya',   400000, 5.0),
(5, 'Handyman',              'Repair',      'Service', 'Furniture assembly, small repairs, and home installations.',               'Medan',      150000, 4.6),
(6, 'Music Producer',        'Audio',       'Others',  'Mixing, mastering, and original composition for film and ads.',            'Bali',       450000, 4.9);