-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2026 at 02:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(1, 'Announcements'),
(2, 'Events'),
(3, 'Enrollment'),
(4, 'Exams'),
(5, 'PE Activities'),
(6, 'School News'),
(7, 'Orientation');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lost_items`
--

CREATE TABLE `lost_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('lost','found') DEFAULT 'lost',
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_items`
--

INSERT INTO `lost_items` (`id`, `user_id`, `title`, `description`, `location`, `image`, `status`, `contact_info`, `created_at`) VALUES
(1, 1, 'bag', 'Notebook that contains cash and important document is lost during daytime', 'T-park Talisay City Collage', 'assets/uploads/lost/lost_6a3f7cb64f625.png', 'lost', 'mazespoon@gmail.com', '2026-06-27 07:33:10');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `category_id`, `title`, `slug`, `content`, `image`, `author_id`, `is_pinned`, `published_at`, `created_at`) VALUES
(1, 1, 'School Year 2025-2026 Opening Announcement', 'school-year-2025-2026-opening', '<p>We are excited to announce the opening of School Year 2025-2026! Classes will officially begin on June 10, 2025. All students are required to report to their respective homerooms by 7:30 AM.</p><p>Please check the enrollment portal for your class schedules and section assignments. Orientation programs will be held during the first week.</p><p>Welcome back, everyone! Let us make this school year the best one yet.</p>', NULL, 1, 1, '2025-05-15 00:00:00', '2026-05-25 08:24:05'),
(2, 1, 'Important: Schedule of Enrollment for Second Semester', 'schedule-enrollment-second-semester', '<p>Attention all students! The enrollment for the second semester will commence on November 20, 2025. Please take note of the following schedules:</p><ul><li>Senior High School: November 20-22</li><li>College: November 23-27</li><li>Graduate School: November 28-30</li></ul><p>Enroll early to secure your slots in your preferred classes.</p>', NULL, 1, 1, '2025-11-10 01:00:00', '2026-05-25 08:24:05'),
(3, 2, 'Annual School Foundation Day Celebration', 'annual-school-foundation-day', '<p>Join us as we celebrate our school\'s 50th Foundation Day! This year\'s theme is \"Celebrating Excellence, Building the Future.\"</p><p>Date: December 5, 2025<br>Time: 8:00 AM - 5:00 PM<br>Venue: School Quadrangle</p><p>Activities include a parade, cultural presentations, inter-class competitions, and a grand fireworks display at the end of the day.</p>', NULL, 1, 0, '2025-11-25 02:00:00', '2026-05-25 08:24:05'),
(5, 3, 'Enrollment Guidelines for New Students', 'enrollment-guidelines-new-students', '<p>Welcome to TCC Blog! If you are a new student, here are the steps to complete your enrollment:</p><ol><li>Submit your application form online</li><li>Prepare the following requirements: PSA Birth Certificate, Form 138 (Report Card), Good Moral Certificate, and 2x2 ID pictures</li><li>Proceed to the Registrar\'s Office for evaluation</li><li>Pay the enrollment fee at the Cashier</li><li>Get your class schedule from your department</li></ol><p>For questions, visit the Registrar\'s Office or email registrar@campusconnect.edu.</p>', NULL, 1, 0, '2025-04-01 00:00:00', '2026-05-25 08:24:05'),
(6, 4, 'Midterm Examination Schedule Released', 'midterm-examination-schedule', '<p>The schedule for midterm examinations has been released. Please check the bulletin boards or the student portal for the complete schedule.</p><p>Examination Period: October 15-19, 2025</p><p>Reminders:<br>- Bring your school ID at all times<br>- Follow the examination dress code<br>- No electronic devices allowed during exams<br>- Review your notes and study well!</p><p>Good luck, students!</p>', NULL, 1, 0, '2025-09-30 23:00:00', '2026-05-25 08:24:05'),
(7, 5, 'PE Activity: Zumba Fitness Challenge', 'pe-activity-zumba-fitness-challenge', '<p>All PE students are required to participate in the Zumba Fitness Challenge on Friday, March 14, 2025, at the school gymnasium.</p><p>Time: 1:00 PM - 3:00 PM</p><p>Wear your PE uniforms and bring water bottles. This activity will be part of your PE grade, so attendance is mandatory.</p><p>Let\'s get fit and have fun!</p>', NULL, 1, 0, '2025-02-28 22:00:00', '2026-05-25 08:24:05'),
(8, 6, 'TCC Blog Launches New Library System', 'campusconnect-launches-new-library-system', '<p>We are proud to announce the launch of our new digital library system! Students can now access thousands of e-books, journals, and research materials online.</p><p>The new system features:<br>- Online catalog with real-time availability<br>- Digital book borrowing and returns<br>- 24/7 access from any device<br>- Integrated citation tools</p><p>Visit the library portal at library.campusconnect.edu to get started.</p>', NULL, 1, 0, '2025-02-15 01:00:00', '2026-05-25 08:24:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `school_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `school_id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, '', 'Dorake', 'mazespoon@gmail.com', '$2y$10$vqMPnEYraMhqo5vExAtr.u0eHhTyLzArfprtoNtB7sp./vfD6v4qO', 'admin', 'approved', '2026-05-25 08:24:05'),
(3, '20203-5910', 'dela Cruz', 'fukaisumi@gmail.com', '$2y$10$TabgnE7R.TBrVXacLh.Tl.SnXvGmXf6If.mBd.ZxxPE9bQ.HrHBsC', 'user', 'approved', '2026-06-27 06:27:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lost_items`
--
ALTER TABLE `lost_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD CONSTRAINT `lost_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
