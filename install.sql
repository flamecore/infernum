SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `ww_sessions` (
  `id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lifetime` int(8) NOT NULL,
  `user` int(10) NOT NULL,
  `data` text NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ww_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) NOT NULL,
  `accesslevel` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=4;

INSERT INTO `ww_usergroups` (`id`, `name`, `title`, `accesslevel`) VALUES
(1, 'guest', 'Guest', 0),
(2, 'member', 'Member', 1),
(3, 'admin', 'Administrator', 2);

CREATE TABLE IF NOT EXISTS `ww_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `group` int(10) unsigned NOT NULL,
  `profile` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lastactive` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

INSERT INTO `ww_users` (`id`, `username`, `email`, `password`, `group`, `profile`, `lastactive`) VALUES
(1, 'admin', 'example@example.com', '$2y$10$H6H2qdcXn6yYtshRHTiXMOEMh6ePW/VZ4J1b6nIjkGGRXVinQCOLy', 3, '{}', '0000-00-00 00:00:00');

CREATE TABLE IF NOT EXISTS `ww_locales` (
  `id` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text_direction` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `number_sep_decimal` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `number_sep_thousand` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fmt_money` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fmt_time` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fmt_date_short` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fmt_date_medium` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fmt_date_long` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ww_locales` (`id`, `name`, `text_direction`, `number_sep_decimal`, `number_sep_thousand`, `fmt_money`, `fmt_time`, `fmt_date_short`, `fmt_date_medium`, `fmt_date_long`) VALUES
('en-US', 'English (US)', 'ltr', '.', ',', '$ #,###.##', 'h:i A', 'm/d/Y', 'F j, Y', 'D, F j, Y');

CREATE TABLE IF NOT EXISTS `ww_translations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` varchar(5) NOT NULL,
  `string` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `translation` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pack` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;
