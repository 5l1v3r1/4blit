-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Apr 10, 2019 alle 09:24
-- Versione del server: 10.1.37-MariaDB-0+deb9u1
-- Versione PHP: 7.0.33-0+deb9u3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `4blit`
--
CREATE DATABASE IF NOT EXISTS `4blit` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `4blit`;

-- --------------------------------------------------------

--
-- Struttura della tabella `Blog`
--

DROP TABLE IF EXISTS `Blog`;
CREATE TABLE IF NOT EXISTS `Blog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` text NOT NULL,
  `Content` text NOT NULL,
  `Type` smallint(6) NOT NULL,
  `chgDate` datetime NOT NULL,
  `addDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Bots`
--

DROP TABLE IF EXISTS `Bots`;
CREATE TABLE IF NOT EXISTS `Bots` (
  `ID` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `Name` varchar(16) NOT NULL,
  `botToken` varchar(64) NOT NULL,
  `updatesOffset` int(11) NOT NULL,
  `isEnable` tinyint(4) NOT NULL,
  `publishDelay` smallint(6) NOT NULL,
  `errorCounter` smallint(6) NOT NULL,
  `lastError` text NOT NULL,
  `lastPublish` datetime NOT NULL,
  `addDate` datetime NOT NULL,
  `chgDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Chats`
--

DROP TABLE IF EXISTS `Chats`;
CREATE TABLE IF NOT EXISTS `Chats` (
  `ID` varchar(32) NOT NULL,
  `Type` varchar(16) NOT NULL,
  `Title` text NOT NULL,
  `botId` int(11) NOT NULL,
  `AVP` text,
  `isEnable` tinyint(1) NOT NULL,
  `addDate` datetime NOT NULL,
  `chgDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`,`botId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Log`
--

DROP TABLE IF EXISTS `Log`;
CREATE TABLE IF NOT EXISTS `Log` (
  `addDate` datetime NOT NULL,
  `IP` varchar(32) NOT NULL,
  `Context` varchar(32) NOT NULL,
  `Description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Mails`
--

DROP TABLE IF EXISTS `Mails`;
CREATE TABLE IF NOT EXISTS `Mails` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `Subject` text NOT NULL,
  `Body` text NOT NULL,
  `tryCount` smallint(6) NOT NULL,
  `addDate` datetime NOT NULL,
  `sentDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Coda delle e-Mail in uscita';

-- --------------------------------------------------------

--
-- Struttura della tabella `Posts`
--

DROP TABLE IF EXISTS `Posts`;
CREATE TABLE IF NOT EXISTS `Posts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `botId` int(11) NOT NULL,
  `sourceId` int(11) NOT NULL,
  `Title` text NOT NULL,
  `Excerpt` text NOT NULL,
  `Author` text NOT NULL,
  `ImageURL` text NOT NULL,
  `URL` text NOT NULL,
  `lastURLCheck` datetime NOT NULL,
  `Hash` varchar(64) NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `isPublished` tinyint(1) NOT NULL,
  `Views` int(11) NOT NULL,
  `addDate` datetime NOT NULL,
  `publishDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `PostTags`
--

DROP TABLE IF EXISTS `PostTags`;
CREATE TABLE IF NOT EXISTS `PostTags` (
  `postId` int(11) NOT NULL,
  `tagId` int(11) NOT NULL,
  `addDate` datetime NOT NULL,
  PRIMARY KEY (`postId`,`tagId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `SessionMessages`
--

DROP TABLE IF EXISTS `SessionMessages`;
CREATE TABLE IF NOT EXISTS `SessionMessages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `sessionId` varchar(64) NOT NULL,
  `Type` varchar(16) NOT NULL,
  `Message` text NOT NULL,
  `addDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Sessions`
--

DROP TABLE IF EXISTS `Sessions`;
CREATE TABLE IF NOT EXISTS `Sessions` (
  `ID` varchar(64) NOT NULL,
  `IP` varchar(32) NOT NULL,
  `userId` int(11) NOT NULL,
  `Opts` text,
  `nonce` varchar(16) NOT NULL,
  `lastAction` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Sources`
--

DROP TABLE IF EXISTS `Sources`;
CREATE TABLE IF NOT EXISTS `Sources` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` smallint(6) NOT NULL,
  `botId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `Name` text NOT NULL,
  `Description` text NOT NULL,
  `adminEMail` text NOT NULL,
  `Language` varchar(16) NOT NULL,
  `URL` text NOT NULL,
  `sourceIp` varchar(32) NOT NULL,
  `apiKey` varchar(65) NOT NULL,
  `ACL` text NOT NULL,
  `isPublic` tinyint(1) NOT NULL,
  `isEnable` tinyint(1) NOT NULL,
  `isStrictIp` tinyint(4) NOT NULL,
  `addDate` datetime NOT NULL,
  `lastUpdate` datetime NOT NULL,
  `chgDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `SourcesLog`
--

DROP TABLE IF EXISTS `SourcesLog`;
CREATE TABLE IF NOT EXISTS `SourcesLog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `sourceId` int(11) NOT NULL,
  `Type` smallint(6) NOT NULL,
  `Message` text NOT NULL,
  `addDate` datetime NOT NULL,
  `numRepeat` smallint(6) NOT NULL,
  `lastEvent` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `SourcesStats`
--

DROP TABLE IF EXISTS `SourcesStats`;
CREATE TABLE IF NOT EXISTS `SourcesStats` (
  `Day` date NOT NULL,
  `sourceId` int(11) NOT NULL,
  `numPosts` int(11) NOT NULL,
  `numClicks` int(11) NOT NULL,
  UNIQUE KEY `Day` (`Day`,`sourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Statistiche quotidiane sui post del blogs';

-- --------------------------------------------------------

--
-- Struttura della tabella `Tags`
--

DROP TABLE IF EXISTS `Tags`;
CREATE TABLE IF NOT EXISTS `Tags` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Tag` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `UserAuthProvider`
--

DROP TABLE IF EXISTS `UserAuthProvider`;
CREATE TABLE IF NOT EXISTS `UserAuthProvider` (
  `userId` int(11) NOT NULL,
  `authProvider` varchar(32) NOT NULL,
  `authProviderUID` varchar(64) NOT NULL,
  UNIQUE KEY `userId` (`userId`,`authProvider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `UserMessages`
--

DROP TABLE IF EXISTS `UserMessages`;
CREATE TABLE IF NOT EXISTS `UserMessages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `Type` smallint(6) NOT NULL,
  `Title` text NOT NULL,
  `Content` text NOT NULL,
  `replyTo` smallint(6) NOT NULL,
  `isRead` tinyint(4) NOT NULL,
  `addDate` datetime NOT NULL,
  `readDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `Users`
--

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `displayName` varchar(64) NOT NULL,
  `eMail` varchar(64) NOT NULL,
  `ACL` text NOT NULL,
  `Level` smallint(2) NOT NULL,
  `OTP` varchar(16) NOT NULL,
  `isEnable` tinyint(4) NOT NULL,
  `lastLogin` datetime NOT NULL,
  `addDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `Users`
--

INSERT INTO `Users` (`ID`, `displayName`, `eMail`, `ACL`, `Level`, `OTP`, `isEnable`, `lastLogin`, `addDate`) VALUES
(1, 'admin', '', 'a:1:{s:8:\"canLogin\";b:1;}', 10, '', 1, '', NOW());

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
