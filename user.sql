-- phpMyAdmin SQL Dump
-- version 4.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 15. Apr 2018 um 16:28
-- Server Version: 5.6.36
-- PHP-Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `wortlabor`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`user_id` int(10) unsigned NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(256) NOT NULL DEFAULT '',
  `gender` varchar(11) DEFAULT NULL,
  `firstname` varchar(45) DEFAULT NULL,
  `lastname` varchar(45) DEFAULT NULL,
  `img_src` varchar(45) DEFAULT '0_default_user_image.png',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`user_id`, `email`, `password`, `gender`, `firstname`, `lastname`, `img_src`, `update_time`) VALUES
(7, 'philippe@zubermedien.ch', 'philippe', 'Herr', 'Philippe', 'Zuber', '0_default_user_image.png', '2018-04-06 07:37:24'),
(9, 'linne@isawesome.ch', 'linne', 'Frau', 'Linne', 'Zuber', '0_default_user_image.png', '2018-04-06 10:12:37'),
(10, 'test@testuser.ch', 'test', 'Herr', 'Test1', 'User', '0_default_user_image.png', '2018-04-11 14:10:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
