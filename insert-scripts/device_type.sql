-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 27. Jul 2015 um 20:18
-- Server Version: 5.6.21
-- PHP-Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `d01b0305`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `device_type`
--

CREATE TABLE IF NOT EXISTS `device_type` (
`id` int(10) unsigned NOT NULL COMMENT 'Device type id',
  `name` varchar(100) DEFAULT NULL COMMENT 'Name of device type',
  `iconid` int(10) unsigned DEFAULT NULL COMMENT 'Icon id'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `device_type`
--

INSERT INTO `device_type` (`id`, `name`, `iconid`) VALUES
(1, 'Laptop', NULL),
(2, 'MP3-Player', NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `device_type`
--
ALTER TABLE `device_type`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `device_type`
--
ALTER TABLE `device_type`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Device type id',AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
