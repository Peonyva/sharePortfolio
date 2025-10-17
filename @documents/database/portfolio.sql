-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 17, 2025 at 06:21 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

DROP TABLE IF EXISTS `education`;
CREATE TABLE IF NOT EXISTS `education` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `educationName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facultyName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `majorName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` tinyint(1) NOT NULL,
  `sortOrder` int NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`id`, `userID`, `educationName`, `degree`, `facultyName`, `majorName`, `startDate`, `endDate`, `isCurrent`, `sortOrder`, `remarks`) VALUES
(1, 1, 'King Mongkut\'s University of Technology Thonburi (KMUTT)', 'Bachelor of Science2', 'Faculty of Computer Science', 'Computer Science', '2021-08-01', NULL, 1, 1, 'Currently completing final-year project related to data management systems'),
(2, 2, 'Chulalongkorn University', 'Bachelor of Science', 'Faculty of Engineering', 'Computer Science', '2015-06-01', '2019-04-30', 0, 1, 'GPA: 3.65/4.0, Dean\'s List student'),
(3, 2, 'Udemy Online Courses', 'Certificate', 'Online Learning', 'Full Stack Web Development with React & Node.js', '2020-01-15', '2020-06-30', 0, 2, 'Completed with 95% score'),
(4, 2, 'Google Cloud Skills Boost', 'Professional Certificate', 'Cloud Computing', 'Google Cloud Associate Cloud Engineer', '2023-02-01', '2023-08-15', 0, 3, 'Passed certification exam with distinction');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `professionalTitle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebookUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logoImage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `profileImage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `coverImage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introContent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `skillsContent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isPublic` tinyint(1) NOT NULL DEFAULT '0',
  `isEverPublic` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `userID`, `professionalTitle`, `phone`, `facebook`, `facebookUrl`, `logoImage`, `profileImage`, `coverImage`, `introContent`, `skillsContent`, `isPublic`, `isEverPublic`) VALUES
(1, 1, '', '0972497203', 'siratchara', '', '/uploads/portfolio/1/logo.jpg', '/uploads/portfolio/1/profile_1760713999.jpg', '', '', '', 0, 1),
(2, 2, 'Full Stack Developer', '0812345678', 'somchai deemeewit', 'https://facebook.com/somchai.deemeewit', '/uploads/portfolio/2/logo.png', '/uploads/portfolio/2/profile.jpg', '/uploads/portfolio/2/cover.jpg', 'I am a passionate Full Stack Developer with 4+ years of experience in building web applications. Specialized in React, Node.js, and cloud technologies. Always eager to learn new technologies and best practices. Love solving complex problems and collaborating with talented teams.', 'React, Vue.js, Angular, Node.js, Express, Laravel, JavaScript, Python, PHP, MongoDB, MySQL, PostgreSQL, RESTful APIs, GraphQL, Docker, Kubernetes, Git, Linux, AWS', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `profileskill`
--

DROP TABLE IF EXISTS `profileskill`;
CREATE TABLE IF NOT EXISTS `profileskill` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `skillsID` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
CREATE TABLE IF NOT EXISTS `project` (
  `projectID` int NOT NULL AUTO_INCREMENT,
  `projectTitle` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `projectImage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyPoint` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sortOrder` int NOT NULL,
  `userID` int NOT NULL,
  PRIMARY KEY (`projectID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`projectID`, `projectTitle`, `projectImage`, `keyPoint`, `sortOrder`, `userID`) VALUES
(6, 'E-Commerce Platform', '/uploads/projects/project_2_1760724438.png', 'Built a full-featured e-commerce platform with product catalog, shopping cart, and payment integration\r\nImplemented user authentication with JWT\r\nCreated admin dashboard for inventory management\r\nIntegrated Stripe payment gateway\r\nAchieved 99.5% uptime', 1, 2),
(7, 'Real-time Chat Application', '/uploads/projects/project_2_1760724540.png', 'Developed a real-time messaging application with WebSocket integration\r\nSupported multiple chat rooms and private messaging\r\nImplemented user notifications and typing indicators\r\nOptimized database queries for large-scale data\r\nDesigned responsive UI for mobile and desktop', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `projectskill`
--

DROP TABLE IF EXISTS `projectskill`;
CREATE TABLE IF NOT EXISTS `projectskill` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projectID` int NOT NULL,
  `skillsID` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projectskill`
--

INSERT INTO `projectskill` (`id`, `projectID`, `skillsID`) VALUES
(26, 7, 11),
(25, 6, 13),
(24, 6, 12),
(27, 7, 8),
(23, 6, 11);

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE IF NOT EXISTS `skills` (
  `skillsID` int NOT NULL AUTO_INCREMENT,
  `skillsName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `skillsUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`skillsID`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`skillsID`, `skillsName`, `skillsUrl`) VALUES
(1, 'HTML5', 'fa-brands fa-html5'),
(2, 'CSS3', 'fa-brands fa-css3-alt'),
(3, 'JavaScript', 'fa-brands fa-js'),
(4, 'Python', 'fa-brands fa-python'),
(5, 'Java', 'fa-brands fa-java'),
(6, 'C++', 'fa-solid fa-code'),
(7, 'C#', 'fa-solid fa-code'),
(8, 'PHP', 'fa-brands fa-php'),
(9, 'SQL', 'fa-solid fa-database'),
(10, 'TypeScript', 'fa-brands fa-js'),
(11, 'React', 'fa-brands fa-react'),
(12, 'Node.js', 'fa-brands fa-node-js'),
(13, 'FastAPI', 'fa-solid fa-bolt'),
(14, 'Bootstrap', 'fa-brands fa-bootstrap'),
(15, 'Tailwind CSS', 'fa-solid fa-wind'),
(16, 'Git', 'fa-brands fa-git-alt'),
(17, 'GitHub', 'fa-brands fa-github'),
(18, 'Figma', 'fa-brands fa-figma'),
(19, 'Visual Studio Code', 'fa-solid fa-code-branch'),
(20, 'Docker', 'fa-brands fa-docker');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `userID` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `firstname`, `lastname`, `birthdate`, `email`, `password`, `timestamp`) VALUES
(1, 'Siratchara', 'Pronvootikul', '2000-09-14', 'siratchara88@gmail.com', 'HelloWord123!', '2025-10-16 19:18:36'),
(2, 'Somchai', 'Deemeewit', '1998-05-15', 'somchai@example.com', 'HelloWeb456!', '2025-10-17 17:34:41');

-- --------------------------------------------------------

--
-- Table structure for table `workexperience`
--

DROP TABLE IF EXISTS `workexperience`;
CREATE TABLE IF NOT EXISTS `workexperience` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `companyName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employeeType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` tinyint(1) NOT NULL,
  `jobDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sortOrder` int NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workexperience`
--

INSERT INTO `workexperience` (`id`, `userID`, `companyName`, `position`, `employeeType`, `startDate`, `endDate`, `isCurrent`, `jobDescription`, `sortOrder`, `remarks`) VALUES
(1, 1, 'KMUTT IT Solutions1', 'Front-End Developer Intern', 'Internship', '2024-05-01', '2024-08-31', 0, 'Developed UI components using HTML, CSS, and Bootstrap\nIntegrated APIs with JavaScript\nCollaborated with backend team using FastAPI', 1, 'Internship project for university credit'),
(2, 1, 'BrightCode Co., Ltd.', 'Junior Web Developer', 'Full-time', '2025-01-10', NULL, 1, 'Maintain and enhance company website\r\nImplement RESTful API connections\r\nDebug and optimize front-end performance', 2, 'Ongoing employment'),
(3, 1, 'Freelance Project', 'Web Designer', 'Freelance', '2023-09-01', '2023-12-15', 0, 'Designed responsive landing pages\r\nCreated mockups using Figma\r\nDelivered final HTML/CSS templates to client', 3, 'Completed successfully'),
(4, 2, 'Tech Innovation Co., Ltd.', 'Senior Full Stack Developer', 'Full-time', '2022-03-15', '2024-10-17', 0, 'Developed and maintained web applications using React and Node.js Led a team of 3 junior developers Implemented RESTful APIs and database optimization Managed CI/CD pipeline with Docker and Kubernetes', 1, 'Received Employee of the Year award in 2023'),
(5, 2, 'Digital Solutions Thailand', 'Full Stack Developer', 'Full-time', '2020-06-01', '2022-02-28', 0, 'Built responsive web applications with Vue.js and Laravel\nDesigned and optimized MySQL databases Collaborated with UI/UX designers on frontend improvements\nFixed 50+ production bugs and performance issues', 2, 'Promoted to Senior position after 18 months'),
(6, 2, 'StartUp Dev House', 'Junior Web Developer', 'Internship', '2019-11-01', '2020-05-31', 0, 'Assisted in developing company website with HTML/CSS/JavaScript\r\nFixed frontend bugs and improved page load speed by 30% Learned version control with Git\r\nParticipated in daily standup meetings and agile ceremonies', 3, 'Converted to Full-time position after internship');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
