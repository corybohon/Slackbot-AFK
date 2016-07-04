SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` text CHARACTER SET utf8 NOT NULL,
  `userName` text CHARACTER SET utf8 NOT NULL,
  `awayMessage` text CHARACTER SET utf8,
  `awayDate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `userToken` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;
