-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 22, 2025 at 04:32 PM
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 'UI/UX Designer', '0972497203', 'Nadwars Pronvootikul', 'https://www.facebook.com/Nadwara1512', 'logo.jpg', 'profile.jpg', 'cover.jpg', 'I need a nooddle.', 'Eat and Sleep.', 0, 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profileskill`
--

INSERT INTO `profileskill` (`id`, `userID`, `skillsID`) VALUES
(7, 1, 4);

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Nadwara', 'Pronvootikul', '2002-11-01', 'siratchara88@gmail.com', 'HelloWord123!', '2025-11-15 14:17:38');

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workexperience`
--

INSERT INTO `workexperience` (`id`, `userID`, `companyName`, `position`, `employeeType`, `startDate`, `endDate`, `isCurrent`, `jobDescription`, `sortOrder`, `remarks`) VALUES
(1, 1, 'BrightCode Co., Ltd.123', 'Junior Web Developer', 'Part-time', '2025-09-01', NULL, 1, '123', 3, ''),
(2, 1, 'KMUTT IT Solutions', 'xxx', 'Contract', '2025-11-02', '2025-11-19', 0, '123', 2, ''),
(3, 1, 'Tech Innovation Co., Ltd.', 'xxx', 'Contract', '2025-11-18', NULL, 1, 'xx', 1, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
