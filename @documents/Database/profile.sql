-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 06, 2025 at 09:22 AM
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
-- Database: `profile`
--

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

DROP TABLE IF EXISTS `education`;
CREATE TABLE IF NOT EXISTS `education` (
  `id` int NOT NULL AUTO_INCREMENT,
  `educationName` int NOT NULL,
  `degree` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facultyName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `majorName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` tinyint(1) NOT NULL,
  `sortOrder` int NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
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
  `professionalTitle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebookUrl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logoImage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profileImage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coverImage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introContent` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `skillsContent` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `projectTitle` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `projectImage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyPoint` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `userID` int NOT NULL,
  PRIMARY KEY (`projectID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE IF NOT EXISTS `skills` (
  `skillsID` int NOT NULL AUTO_INCREMENT,
  `skillsName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skillsUrl` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`skillsID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `userID` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workexperience`
--

DROP TABLE IF EXISTS `workexperience`;
CREATE TABLE IF NOT EXISTS `workexperience` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userID` int NOT NULL,
  `companyName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employeeType` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `isCurrent` tinyint(1) NOT NULL,
  `jobdescription` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sortOrder` int NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
