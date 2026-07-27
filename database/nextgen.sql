-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2026 at 04:36 PM
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
-- Database: `nextgen-learning`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_desc` text NOT NULL,
  `description` text NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `video` varchar(255) NOT NULL,
  `duration` varchar(255) NOT NULL,
  `instructor` int(11) DEFAULT NULL,
  `price` varchar(255) NOT NULL,
  `total_lectures` int(11) NOT NULL,
  `language` varchar(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'upcomming',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `upcoming` varchar(20) DEFAULT 'live'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `short_desc`, `description`, `thumbnail`, `video`, `duration`, `instructor`, `price`, `total_lectures`, `language`, `instructor_id`, `status`, `created_at`, `updated_at`, `upcoming`) VALUES
(34, 'Python for Beginners: From Zero to Hero', 'Learn Python programming from scratch and build real-world projects.', 'This course teaches Python basics, including variables, loops, functions, OOP, and working with files. You will also build 2 real-world projects to practice your skills and gain confidence.', '1770833719_698cc737ef692.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '10h 0m', NULL, '2500', 50, 'English', 15, 'upcoming', '2026-02-07 22:16:22', '2026-02-12 00:15:19', 'live'),
(35, 'Django Web Development Masterclass', 'Become a Django expert and create dynamic web applications.', 'In this course, you will learn Django framework, models, views, templates, authentication, and API development with Django REST framework. By the end, you will have built 2 complete web applications.', '1770833697_698cc721a9014.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '15h 20m', NULL, '0', 60, 'English', 15, 'upcoming', '2026-02-07 22:16:22', '2026-02-12 00:14:57', 'live'),
(36, 'React & Redux: Modern Frontend Development', 'Master React and Redux to build interactive web interfaces.', 'Learn React fundamentals, hooks, context API, Redux Toolkit, routing, and state management. You will also build a project integrating REST APIs and dynamic frontend features.', '1770833673_698cc709ee25e.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '12h 45m', NULL, '3500', 55, 'English', 15, 'upcoming', '2026-02-07 22:16:22', '2026-02-12 00:14:33', 'live'),
(37, 'Full Stack Development with Python and React', 'Combine backend and frontend skills to build full-stack apps.', 'This course covers backend with Django, frontend with React, API integration, database management with MySQL/PostgreSQL, and deployment. You will complete 2 industry-standard projects.', '1770833640_698cc6e8abbeb.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '20h 0m', NULL, '0', 80, 'English', 15, 'upcoming', '2026-02-07 22:16:22', '2026-02-12 00:14:00', 'live'),
(38, 'Artificial Intelligence & Machine Learning Essentials', 'Learn AI & ML concepts and implement real-world models.', 'Explore Python for AI, machine learning algorithms, data preprocessing, neural networks, and model deployment. Build projects including predictive models and AI features integration.', '1770833612_698cc6ccd0d82.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '18h 30m', NULL, '2000', 50, 'English', 15, 'upcoming', '2026-02-07 22:16:22', '2026-02-12 00:13:32', 'live'),
(39, 'React & Redux: Modern Frontend Development', 'Master React and Redux to build interactive web interfaces.', 'Learn React fundamentals, hooks, context API, Redux Toolkit, routing, and state management. You will also build a project integrating REST APIs and dynamic frontend features.', '1770833577_698cc6a9b9ca2.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '12h 45m', NULL, '0', 55, 'English', 15, 'upcoming', '2026-02-07 22:16:22', '2026-02-12 00:12:57', 'live'),
(40, 'HTML, CSS, JS, BootStrap', 'No short description added', 'No description added', '1770829502_698cb6be3dc6a.jpeg', 'https://www.youtube.com/embed/tXHviS-4ygo', '12h 30m', NULL, '3000', 30, 'Bangla', 18, 'upcomming', '2026-02-11 23:05:02', '2026-02-11 23:05:02', 'live');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `tnx_id` varchar(255) NOT NULL,
  `status` enum('pending','success','cancel') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `course_id`, `user_id`, `phone`, `tnx_id`, `status`, `created_at`, `updated_at`) VALUES
(5, 28, 15, '01771868382', 'sdfsdf', 'success', '2026-02-04 09:44:05', '2026-02-04 09:44:05'),
(6, 38, 18, '01716530478', 'fgdghfhfyujjgk', 'success', '2026-02-10 21:37:21', '2026-02-10 21:37:21'),
(7, 34, 20, '01667743888', 'gfghauytu5678665', 'success', '2026-02-11 20:36:00', '2026-02-11 20:36:00'),
(8, 36, 28, '01667743888', 'B5tdjhhj67d', 'success', '2026-02-12 00:25:08', '2026-02-12 00:25:08'),
(9, 40, 20, '01667743888', 'dghtjyrk4f', 'success', '2026-02-12 00:57:54', '2026-02-12 00:57:54'),
(10, 40, 30, '01752937462', 'DBD72PQ4TV', 'success', '2026-02-14 11:06:47', '2026-02-14 11:06:47'),
(11, 34, 20, '01716530478', 'fghfvjhgjh678', 'success', '2026-02-17 13:56:52', '2026-02-17 13:56:52'),
(12, 38, 32, '01545578889', 'gdghfjhgkj', 'success', '2026-02-18 06:42:00', '2026-02-18 06:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `lectures`
--

CREATE TABLE `lectures` (
  `id` int(11) NOT NULL,
  `course_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lectures`
--

INSERT INTO `lectures` (`id`, `course_id`, `title`) VALUES
(44, 40, 'Introduction to Basic HTML'),
(45, 40, 'Introduction to CSS'),
(46, 40, 'Introduction to BootStrap');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `lecture_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video` varchar(255) NOT NULL,
  `duration` varchar(255) NOT NULL,
  `price` enum('free','premium') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `lecture_id`, `course_id`, `title`, `video`, `duration`, `price`) VALUES
(18, 44, 40, 'Introduction to Basic HTML', 'https://www.youtube.com/watch?v=qz0aGYrrlhU&pp=ygUbaHRtbCB0dXRvcmlhbCBmb3IgYmVnaW5uZXJz', '10m', 'premium');

-- --------------------------------------------------------

--
-- Table structure for table `watched_topics`
--

CREATE TABLE `watched_topics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `course_id` bigint(20) NOT NULL,
  `watched_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('student','admin','instructor') NOT NULL DEFAULT 'student',
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `first_name`, `last_name`, `phone`, `email`, `password`, `avatar`, `created_at`, `updated_at`) VALUES
(15, 'admin', 'Mithila Das ', 'Purba', '01771868382', 'mithila@gmail.com', '$2y$10$j1fFjun3TZLOIoNF7dc8Wu8KONjpNuVfm2K3MYNUN0Es6.T7Pildq', '1770827990_698cb0d69b60f.jpeg', '2026-02-03 19:37:04', '2026-02-03 19:37:04'),
(18, 'instructor', 'Puja', 'Roy', '01716530478', 'Puja10@gmail.com', '$2y$10$wE45g9Zzk0XNbDPbjuTCresHXLFE7qx8nLsnvv.zPC6PHabjPNhLm', '1770832968_698cc448057c4.jpeg', '2026-02-10 21:31:40', '2026-02-10 21:31:40'),
(20, 'student', 'Barnali', 'Sarker', '01667743888', 'barnali20@gmail.com', '$2y$10$9unQydwt8Y9caiR5Tvth8uZRrIuOPX1FqCeaaq80IiH2At/mvGZOi', '1770832719_530a8c95c200f555.jpeg', '2026-02-11 20:33:19', '2026-02-11 23:58:39'),
(21, 'instructor', 'Kallayan', 'Das', '01750674830', 'kallayan@gmail.com', '$2y$10$FClguQbtMLh7dLGFW9v/wu73fjT9KksUNtQ.iA6igYIKzoDR6mx5e', '1770833021_176caad74a43b522.jpeg', '2026-02-11 23:27:26', '2026-02-12 00:03:41'),
(23, 'instructor', 'Maushomi', 'Sarker', '01723067036', 'maushomi05@gmail.com', '$2y$10$1wPTBdabmjrojOTTS1uWpujuZTC.ClfAXAOp3QryXhQXPC6ZXZ/8S', '1770833068_d0becbdd4deb2bd0.jpeg', '2026-02-11 23:37:31', '2026-02-12 00:04:28'),
(25, 'student', 'Dwip', 'Sarker', '01234563410', 'dwip@gmail.com', '$2y$10$jEsE5ULYPQZJ/smTfdyUm.ozX8s7LdLwL9ixTZN75EN4AJ4MZ3QPu', NULL, '2026-02-11 23:42:52', '2026-02-11 23:42:52'),
(26, 'student', 'Supriya', 'Roy', '01667743888', 'supriya@gmail.com', '$2y$10$HffEoL16qTnHYBlenalaHeLhkrIhR4psKYYZ3hxCAx8gmc0DSE4Ii', '1770832862_d73d4bf2a86c07ba.jpeg', '2026-02-11 23:47:49', '2026-02-12 00:01:02'),
(27, 'student', 'Rahat', 'Mia', '01734558649', 'rahat65@gmail.com', '$2y$10$5VIMr.g7AhlCL7576su6QOT2.nRQqi0plsTA9ndsb3irMNHtBPk5q', '1770832813_41c997442bf8537b.jpeg', '2026-02-11 23:50:34', '2026-02-12 00:00:13'),
(28, 'student', 'Arpita', 'Roy', '01534678953', 'arpita@gmail.com', '$2y$10$BI4qaVQHef1HzbBELrLJ0uPHhGcVBJruBMx09671OSE7GQhSYvXAS', '1770832764_87481c49cdf7ed83.jpeg', '2026-02-11 23:53:04', '2026-02-11 23:59:24'),
(29, 'student', 'Nipa Das', 'Rakhi', '01856432789', 'nipa35@gmail.com', '$2y$10$cr56gxboz3f2gz4Ly8oBuufk66FlJHfy389HaVvJj20Q4B4147Q7K', '1770832579_037fae7370144d3f.jpeg', '2026-02-11 23:55:15', '2026-02-11 23:56:19'),
(30, 'student', 'Sajib', 'Das', '01752937462', 'sajibdas@gmail.com', '$2y$10$11H1eyXwUN0r/l7VUGjT6eYn89yneGeDb.E6NVlpw35z4a16UoHDW', '1771044839_3e16354bfebaf900.jpeg', '2026-02-14 10:53:28', '2026-02-14 10:53:59'),
(31, 'student', 'Kamal', 'Chowdhury', '01837503847', 'kamalchowdhury@gmail.com', '$2y$10$sfW5svGZSeUeAFYXja5OWuxzqyj1vIf7VrNWoQk8ZrmbZS4wzikhO', '1771045139_69900113bf7f7.jpeg', '2026-02-14 10:57:09', '2026-02-14 10:57:09'),
(32, 'student', 'Tanjina', 'Chowdhury', '01745678567', 'tanjina10@gmail.com', '$2y$10$3QW2zvvj9J57NeQuuQa7g.dFk8.N.ROc91WDGC8vZgMj7UuVAFeqq', '1771375257_8cafe8eebd31976f.jpeg', '2026-02-18 06:40:07', '2026-02-18 06:40:57'),
(33, 'instructor', 'sabiha', 'Begum', '01356546758', 'sabiha@gmail.com', '$2y$10$BKpHj9ZkPJGgiWIljRYI3OgWGYZhCm4yM//Mu/ohPgToNnTCmfM56', '1771375445_4e2b080bc3c14b07.jpeg', '2026-02-18 06:43:40', '2026-02-18 06:44:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecture_id` (`lecture_id`);

--
-- Indexes for table `watched_topics`
--
ALTER TABLE `watched_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_user_course` (`user_id`,`course_id`),
  ADD KEY `idx_user_date` (`user_id`,`watched_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lectures`
--
ALTER TABLE `lectures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `watched_topics`
--
ALTER TABLE `watched_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `fk_lectures_courses` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `topics`
--
ALTER TABLE `topics`
  ADD CONSTRAINT `fk_topics_lectures` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `watched_topics`
--
ALTER TABLE `watched_topics`
  ADD CONSTRAINT `fk_watched_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_watched_topics` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_watched_courses` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
