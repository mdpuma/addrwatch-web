-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `addrwatch`;
CREATE TABLE `addrwatch` (
  `tstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hostname` varchar(256) NOT NULL,
  `interface` varchar(16) NOT NULL,
  `vlan_tag` int(11) NOT NULL,
  `mac_address` varchar(17) NOT NULL,
  `ip_address` varchar(42) NOT NULL,
  `origin` varchar(8) NOT NULL,
  KEY `interface` (`interface`),
  KEY `vlan_tag` (`vlan_tag`),
  KEY `interface_vlan_tag` (`interface`,`vlan_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `known_pairs`;
CREATE TABLE `known_pairs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_change` datetime NOT NULL,
  `ip_address` varchar(256) NOT NULL,
  `vlan_tag` int(5) NOT NULL,
  `mac_address` varchar(256) NOT NULL,
  `changes` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=678 DEFAULT CHARSET=utf8;


-- 2017-03-08 19:03:32
