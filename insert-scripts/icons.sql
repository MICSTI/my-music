-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 27. Jul 2015 um 20:15
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
-- Tabellenstruktur für Tabelle `icons`
--

CREATE TABLE IF NOT EXISTS `icons` (
`id` int(10) unsigned NOT NULL COMMENT 'Icon id',
  `name` varchar(100) NOT NULL COMMENT 'Icon name',
  `type` varchar(100) DEFAULT NULL COMMENT 'Icon type',
  `path` varchar(255) NOT NULL COMMENT 'Path to icon'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `icons`
--

INSERT INTO `icons` (`id`, `name`, `type`, `path`) VALUES
(1, 'mobile_phone', 'glyphicon', 'glyphicon-phone');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `icons`
--
ALTER TABLE `icons`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `icons`
--
ALTER TABLE `icons`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Icon id',AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
