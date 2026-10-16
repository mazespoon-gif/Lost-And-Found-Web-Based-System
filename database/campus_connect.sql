

-- Users Table
CREATE TABLE  users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories Table
CREATE TABLE  categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts Table
CREATE TABLE  posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    author_id INT NOT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments Table
CREATE TABLE  comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lost Items Table
CREATE TABLE  lost_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('lost', 'found') DEFAULT 'lost',
    contact_info VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories Table
CREATE TABLE  categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts Table
CREATE TABLE  posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    author_id INT NOT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments Table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin account (password: Admin123!)
INSERT INTO users (school_id, name, email, password, role) VALUES
('ADMIN-001', 'Dorake', 'mazespoon@gmail.com', '$2y$10$vqMPnEYraMhqo5vExAtr.u0eHhTyLzArfprtoNtB7sp./vfD6v4qO', 'admin'),
('STU-001', 'Juan Dela Cruz', 'student@campusconnect.edu', '$2y$10$aHOiaMautDwp2ZAyowbGVewSBOV3ww/T00QbwteLTnfgSD7l/ezzO', 'user');

-- Categories
INSERT INTO categories (category_name) VALUES
('Announcements'),
('Events'),
('Enrollment'),
('Exams'),
('PE Activities'),
('School News'),
('Orientation');

-- Sample Posts
INSERT INTO posts (category_id, title, slug, content, image, author_id, is_pinned, published_at) VALUES
(1, 'School Year 2025-2026 Opening Announcement', 'school-year-2025-2026-opening', '<p>We are excited to announce the opening of School Year 2025-2026! Classes will officially begin on June 10, 2025. All students are required to report to their respective homerooms by 7:30 AM.</p><p>Please check the enrollment portal for your class schedules and section assignments. Orientation programs will be held during the first week.</p><p>Welcome back, everyone! Let us make this school year the best one yet.</p>', NULL, 1, 1, '2025-05-15 08:00:00'),

(1, 'Important: Schedule of Enrollment for Second Semester', 'schedule-enrollment-second-semester', '<p>Attention all students! The enrollment for the second semester will commence on November 20, 2025. Please take note of the following schedules:</p><ul><li>Senior High School: November 20-22</li><li>College: November 23-27</li><li>Graduate School: November 28-30</li></ul><p>Enroll early to secure your slots in your preferred classes.</p>', NULL, 1, 1, '2025-11-10 09:00:00'),

(2, 'Annual School Foundation Day Celebration', 'annual-school-foundation-day', '<p>Join us as we celebrate our school''s 50th Foundation Day! This year''s theme is "Celebrating Excellence, Building the Future."</p><p>Date: December 5, 2025<br>Time: 8:00 AM - 5:00 PM<br>Venue: School Quadrangle</p><p>Activities include a parade, cultural presentations, inter-class competitions, and a grand fireworks display at the end of the day.</p>', NULL, 1, 0, '2025-11-25 10:00:00'),

(2, 'Intramural Sports Festival 2025', 'intramural-sports-festival-2025', '<p>Get ready for the most anticipated sports event of the year! The Intramural Sports Festival 2025 will feature basketball, volleyball, badminton, chess, and track and field events.</p><p>Registration is open until January 15, 2025. Form your teams and represent your departments with pride!</p><p>Let the games begin!</p>', NULL, 1, 0, '2025-12-01 11:00:00'),

(3, 'Enrollment Guidelines for New Students', 'enrollment-guidelines-new-students', '<p>Welcome to TCC Blog! If you are a new student, here are the steps to complete your enrollment:</p><ol><li>Submit your application form online</li><li>Prepare the following requirements: PSA Birth Certificate, Form 138 (Report Card), Good Moral Certificate, and 2x2 ID pictures</li><li>Proceed to the Registrar''s Office for evaluation</li><li>Pay the enrollment fee at the Cashier</li><li>Get your class schedule from your department</li></ol><p>For questions, visit the Registrar''s Office or email registrar@campusconnect.edu.</p>', NULL, 1, 0, '2025-04-01 08:00:00'),

(4, 'Midterm Examination Schedule Released', 'midterm-examination-schedule', '<p>The schedule for midterm examinations has been released. Please check the bulletin boards or the student portal for the complete schedule.</p><p>Examination Period: October 15-19, 2025</p><p>Reminders:<br>- Bring your school ID at all times<br>- Follow the examination dress code<br>- No electronic devices allowed during exams<br>- Review your notes and study well!</p><p>Good luck, students!</p>', NULL, 1, 0, '2025-10-01 07:00:00'),

(5, 'PE Activity: Zumba Fitness Challenge', 'pe-activity-zumba-fitness-challenge', '<p>All PE students are required to participate in the Zumba Fitness Challenge on Friday, March 14, 2025, at the school gymnasium.</p><p>Time: 1:00 PM - 3:00 PM</p><p>Wear your PE uniforms and bring water bottles. This activity will be part of your PE grade, so attendance is mandatory.</p><p>Let''s get fit and have fun!</p>', NULL, 1, 0, '2025-03-01 06:00:00'),

(6, 'TCC Blog Launches New Library System', 'campusconnect-launches-new-library-system', '<p>We are proud to announce the launch of our new digital library system! Students can now access thousands of e-books, journals, and research materials online.</p><p>The new system features:<br>- Online catalog with real-time availability<br>- Digital book borrowing and returns<br>- 24/7 access from any device<br>- Integrated citation tools</p><p>Visit the library portal at library.campusconnect.edu to get started.</p>', NULL, 1, 0, '2025-02-15 09:00:00');
