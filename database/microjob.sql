-- ============================================================
-- MicroJob for Students - Database Schema and Seed Data
-- CSC 3215 Web Technologies, Summer 2025-26
-- Import this file in phpMyAdmin, or run:
--   mysql -u root < microjob.sql
-- ============================================================

DROP DATABASE IF EXISTS microjob_db;
CREATE DATABASE microjob_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE microjob_db;

-- ------------------------------------------------------------
-- 1. users : every account (student seller, client buyer, admin)
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('student','client','admin') NOT NULL DEFAULT 'client',
    university  VARCHAR(150)  DEFAULT '',
    department  VARCHAR(100)  DEFAULT '',
    phone       VARCHAR(20)   DEFAULT '',
    bio         TEXT,
    skills      VARCHAR(255)  DEFAULT '',
    status      ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at  DATETIME      NOT NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. categories : service catalogue, managed by admin
-- ------------------------------------------------------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. gigs : a service offered by a student
-- ------------------------------------------------------------
CREATE TABLE gigs (
    gig_id        INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    category_id   INT NOT NULL,
    title         VARCHAR(150)   NOT NULL,
    description   TEXT           NOT NULL,
    price_bdt     DECIMAL(10,2)  NOT NULL,
    delivery_days INT            NOT NULL,
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at    DATETIME       NOT NULL,
    CONSTRAINT fk_gig_student  FOREIGN KEY (student_id)  REFERENCES users(user_id)          ON DELETE CASCADE,
    CONSTRAINT fk_gig_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
    INDEX idx_gigs_status (status),
    INDEX idx_gigs_student (student_id),
    INDEX idx_gigs_category (category_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. orders : one purchase of one gig by one client
--    amount_bdt is a SNAPSHOT of the gig price at order time
-- ------------------------------------------------------------
CREATE TABLE orders (
    order_id     INT AUTO_INCREMENT PRIMARY KEY,
    gig_id       INT NOT NULL,
    client_id    INT NOT NULL,
    student_id   INT NOT NULL,
    requirement  TEXT           NOT NULL,
    amount_bdt   DECIMAL(10,2)  NOT NULL,
    deadline     DATE           NOT NULL,
    status       ENUM('placed','accepted','delivered','completed','cancelled') NOT NULL DEFAULT 'placed',
    created_at   DATETIME       NOT NULL,
    completed_at DATETIME       NULL,
    CONSTRAINT fk_order_gig     FOREIGN KEY (gig_id)     REFERENCES gigs(gig_id)   ON DELETE CASCADE,
    CONSTRAINT fk_order_client  FOREIGN KEY (client_id)  REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_student FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_orders_client (client_id),
    INDEX idx_orders_student (student_id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. payments : simulated BDT payment, one per order.
--    Only the last 4 digits of the account or card are stored.
-- ------------------------------------------------------------
CREATE TABLE payments (
    payment_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL UNIQUE,
    method        ENUM('bkash','nagad','bank','card') NOT NULL,
    account_last4 VARCHAR(4)    NOT NULL,
    txn_id        VARCHAR(40)   NOT NULL UNIQUE,
    amount_bdt    DECIMAL(10,2) NOT NULL,
    status        ENUM('paid','refunded') NOT NULL DEFAULT 'paid',
    paid_at       DATETIME      NOT NULL,
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. reviews : at most one per completed order
-- ------------------------------------------------------------
CREATE TABLE reviews (
    review_id  INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL UNIQUE,
    client_id  INT NOT NULL,
    student_id INT NOT NULL,
    rating     TINYINT NOT NULL,
    comment    TEXT,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_review_order   FOREIGN KEY (order_id)   REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_review_client  FOREIGN KEY (client_id)  REFERENCES users(user_id)   ON DELETE CASCADE,
    CONSTRAINT fk_review_student FOREIGN KEY (student_id) REFERENCES users(user_id)   ON DELETE CASCADE,
    CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5),
    INDEX idx_reviews_student (student_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. withdrawals : student cash-out request
-- ------------------------------------------------------------
CREATE TABLE withdrawals (
    withdrawal_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    amount_bdt    DECIMAL(10,2) NOT NULL,
    method        ENUM('bkash','nagad','bank') NOT NULL,
    account_no    VARCHAR(30)   NOT NULL,
    status        ENUM('requested','paid') NOT NULL DEFAULT 'requested',
    requested_at  DATETIME      NOT NULL,
    CONSTRAINT fk_withdraw_student FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_withdrawals_student (student_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. messages : order-scoped conversation
-- ------------------------------------------------------------
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    sender_id  INT NOT NULL,
    body       TEXT NOT NULL,
    sent_at    DATETIME NOT NULL,
    CONSTRAINT fk_message_order  FOREIGN KEY (order_id)  REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(user_id)   ON DELETE CASCADE,
    INDEX idx_messages_order (order_id)
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- NOTE: passwords are stored as plain text. This is a deliberate
-- project constraint documented in docs/SECURITY.md, because no
-- hashing function appears in the course slides. A real system
-- must never store passwords this way.
-- ============================================================

INSERT INTO categories (name) VALUES
('Graphic Design'),
('Web Development'),
('Content Writing'),
('Video Editing'),
('Tutoring'),
('Data Entry'),
('Presentation Design'),
('Thesis Formatting');

INSERT INTO users (full_name, email, password, role, university, department, phone, bio, skills, status, created_at) VALUES
('System Admin',   'admin@microjob.test', 'admin123',  'admin',   'AIUB', 'Computer Science', '01700000000', 'Platform administrator.', '', 'active', NOW()),
('Nafiz Rahman',   'nafiz@student.test',  'nafiz123',  'student', 'AIUB', 'Computer Science', '01711111111', 'Third year CS student. I build small websites and fix PHP bugs.', 'HTML,CSS,PHP,MySQL', 'active', NOW()),
('Tanvir Ahmed',   'tanvir@student.test', 'tanvir123', 'student', 'AIUB', 'Media Studies',    '01722222222', 'Video editor and motion graphics learner.', 'Premiere Pro,After Effects,Photoshop', 'active', NOW()),
('Sadia Islam',    'sadia@student.test',  'sadia123',  'student', 'AIUB', 'English',          '01733333333', 'I write clean academic content and format theses.', 'Academic Writing,LaTeX,MS Word', 'active', NOW()),
('Karim Uddin',    'karim@faculty.test',  'karim123',  'client',  'AIUB', 'Computer Science', '01744444444', 'Faculty member, Department of Computer Science.', '', 'active', NOW()),
('Rifat Hasan',    'rifat@client.test',   'rifat123',  'client',  'AIUB', 'Business',         '01755555555', 'Final year student and club president.', '', 'active', NOW());

-- student_id 2 = Nafiz, 3 = Tanvir, 4 = Sadia
INSERT INTO gigs (student_id, category_id, title, description, price_bdt, delivery_days, status, created_at) VALUES
(2, 2, 'I will build a responsive HTML CSS landing page',
    'A clean single page website built with plain HTML and CSS. Includes header, hero section, feature columns and footer. Unlimited minor revisions within the delivery window.',
    1500.00, 3, 'approved', NOW()),
(2, 2, 'I will fix bugs in your PHP and MySQL project',
    'Send me your PHP project and describe the bug. I will debug it, fix it, and explain what was wrong so you can defend it in viva.',
    800.00, 2, 'approved', NOW()),
(3, 4, 'I will edit your event video with music and captions',
    'Up to 5 minutes of footage cut, colour corrected, background music added and captions burned in. Delivered in 1080p.',
    2000.00, 4, 'approved', NOW()),
(3, 1, 'I will design a seminar poster or social media banner',
    'One poster or banner design with two revision rounds. Delivered as PNG and editable PSD.',
    600.00, 2, 'approved', NOW()),
(4, 8, 'I will format your thesis according to department guidelines',
    'Full thesis formatting: margins, headings, table of contents, figure and table captions, and reference list styling.',
    1200.00, 5, 'approved', NOW()),
(4, 3, 'I will write a 1000 word article on your topic',
    'Well researched, plagiarism free article in clear English on any general or technical topic.',
    900.00, 3, 'pending', NOW());
