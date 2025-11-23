-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 23, 2025 at 06:49 PM
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
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`id`, `userID`, `educationName`, `degree`, `facultyName`, `majorName`, `startDate`, `endDate`, `isCurrent`, `sortOrder`, `remark`) VALUES
(1, 1, 'Chulalongkorn University', 'Master\'s Degree', 'Faculty of Engineering', 'Computer Engineering', '2020-08-01', '2022-05-30', 0, 3, 'GPA 3.85'),
(2, 1, 'Kasetsart University', 'Bachelor\'s Degree', 'Faculty of Science', 'Computer Science', '2013-08-01', '2017-05-30', 0, 1, 'GPA 3.65, Second Class Honors'),
(3, 1, 'Bangkok Christian College', 'High School', 'Science and Mathematics Program', 'Science-Math', '2010-05-15', '2013-03-15', 0, 2, 'GPAX 3.50');

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `userID`, `professionalTitle`, `phone`, `facebook`, `facebookUrl`, `logoImage`, `profileImage`, `coverImage`, `introContent`, `skillsContent`, `isPublic`, `isEverPublic`) VALUES
(1, 1, ' Full-Stack Developer', '0812345678', 'Somchai Jaidee', 'https://facebook.com/somchai.jaidee', 'logo.png', 'profile.jpg', 'cover.jpg', 'I am a passionate Full-Stack Developer with 5 years of experience in web development.\r\nSpecialized in building scalable applications using modern technologies.\r\nStrong problem-solving skills and ability to work in agile teams.\r\nAlways eager to learn new technologies and best practices.', 'Proficient in both front-end and back-end development.\r\nExperience with database design and optimization.\r\nFamiliar with modern development tools and version control systems.', 0, 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profileskill`
--

INSERT INTO `profileskill` (`id`, `userID`, `skillsID`) VALUES
(13, 1, 8),
(12, 1, 3),
(11, 1, 1),
(10, 1, 2);

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`projectID`, `projectTitle`, `projectImage`, `keyPoint`, `sortOrder`, `userID`) VALUES
(2, 'E-Commerce Platform', '/uploads/projects/proj_1_6923547a54cec.jpg', 'Developed a full-featured online shopping platform\r\nImplemented shopping cart and payment gateway integration\r\nCreated admin dashboard for inventory management\r\nBuilt responsive design for mobile and desktop\r\nIntegrated email notification system', 1, 1),
(3, 'Inventory Management System', '/uploads/projects/proj_1_692354d99cdd3.jpg', 'Designed and developed inventory tracking system for retail business\r\nReal-time stock monitoring with barcode scanning\r\nGenerate sales reports and analytics\r\nMulti-user role management system\r\nExport data to Excel and PDF formats', 2, 1),
(4, 'Company Portfolio Website', '/uploads/projects/proj_1_692355625574a.jpg', 'Created modern and responsive portfolio website\r\nImplemented contact form with email integration\r\nOptimized for search engines (SEO)\r\nFast loading speed with optimized images\r\nAdmin panel for content management', 3, 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projectskill`
--

INSERT INTO `projectskill` (`id`, `projectID`, `skillsID`) VALUES
(2, 2, 8),
(3, 2, 9),
(4, 2, 3),
(5, 2, 1),
(6, 2, 2),
(20, 3, 11),
(13, 4, 8),
(14, 4, 9),
(15, 4, 3),
(16, 4, 1),
(17, 4, 2),
(18, 4, 13),
(19, 4, 10);

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `firstname`, `lastname`, `birthdate`, `email`, `password`, `timestamp`) VALUES
(1, 'Somchai', 'Jaidee', '1995-05-15', 'somchai.j@email.com', 'Somchai1!', '2025-11-23 18:23:34');

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
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workexperience`
--

INSERT INTO `workexperience` (`id`, `userID`, `companyName`, `position`, `employeeType`, `startDate`, `endDate`, `isCurrent`, `jobDescription`, `sortOrder`, `remark`) VALUES
(1, 1, 'Tech Solutions Co., Ltd.', 'Senior Full-Stack Developer', 'Full-time', '2022-01-15', NULL, 1, 'Lead development team of 5 members\r\nDevelop and maintain ERP system for enterprise clients\r\nImplement RESTful APIs using PHP and Laravel framework\r\nDesign database architecture for high-performance applications\r\nCode review and mentor junior developers', 1, ''),
(2, 1, 'Digital Innovation Hub', 'Web Developer', 'Full-time', '2020-03-01', '2021-12-31', 0, 'Developed responsive web applications using HTML, CSS, and JavaScript\r\nCreated dynamic websites with PHP and MySQL\r\nCollaborated with UX/UI designers to implement user-friendly interfaces\r\nIntegrated third-party APIs for payment systems\r\nPerformed unit testing and bug fixing', 2, ''),
(3, 1, 'Startup Creative Agency', 'Web Development Intern', 'Internship', '2019-06-01', '2019-11-30', 0, 'Assisted senior developers in building client websites\r\nLearned version control using Git and GitHub\r\nFixed bugs and implemented minor features\r\nParticipated in daily stand-up meetings\r\nGained hands-on experience with modern web technologies', 3, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
