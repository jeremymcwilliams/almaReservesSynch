-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 13, 2015 at 01:01 AM
-- Server version: 5.6.16
-- PHP Version: 5.5.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `reserves`
--
CREATE DATABASE IF NOT EXISTS `reserves` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `reserves`;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(20) NOT NULL,
  `course_name` text NOT NULL,
  `course_section` varchar(10) NOT NULL,
  `instructor` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `readinglist_id` varchar(20) NOT NULL,
  `course_identifier` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=349 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
