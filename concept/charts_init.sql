-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 23. Aug 2015 um 14:59
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
-- Tabellenstruktur für Tabelle `charts`
--

CREATE TABLE IF NOT EXISTS `charts` (
`id` int(10) unsigned NOT NULL COMMENT 'Chart id',
  `chart_type` varchar(100) NOT NULL COMMENT 'Chart type',
  `year` int(11) DEFAULT NULL COMMENT 'Chart year',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Compile timestamp'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `chart_additional`
--

CREATE TABLE IF NOT EXISTS `chart_additional` (
`id` int(10) unsigned NOT NULL COMMENT 'Chart nationalities id',
  `chart_id` int(10) unsigned NOT NULL COMMENT 'Chart id',
  `instance_type` varchar(100) NOT NULL COMMENT 'Instance type',
  `instance_id` int(10) unsigned NOT NULL COMMENT 'Instance id',
  `rank` int(10) unsigned NOT NULL COMMENT 'Chart rank',
  `cnt` int(10) unsigned NOT NULL COMMENT 'Nationality instance count',
  `fraction` decimal(6,5) unsigned NOT NULL COMMENT 'Fraction percentage'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `chart_content`
--

CREATE TABLE IF NOT EXISTS `chart_content` (
`id` int(10) unsigned NOT NULL COMMENT 'Chart content id',
  `chart_id` int(10) unsigned NOT NULL COMMENT 'Chart id',
  `instance_type` varchar(100) NOT NULL COMMENT 'Chart instance type',
  `instance_id` int(10) unsigned NOT NULL COMMENT 'Instance id',
  `rank` int(10) unsigned NOT NULL COMMENT 'Chart rank',
  `cnt` int(10) unsigned NOT NULL COMMENT 'Instance count'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `charts`
--
ALTER TABLE `charts`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `chart_additional`
--
ALTER TABLE `chart_additional`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `chart_content`
--
ALTER TABLE `chart_content`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `charts`
--
ALTER TABLE `charts`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Chart id',AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `chart_additional`
--
ALTER TABLE `chart_additional`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Chart nationalities id';
--
-- AUTO_INCREMENT für Tabelle `chart_content`
--
ALTER TABLE `chart_content`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Chart content id';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
