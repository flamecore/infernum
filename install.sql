SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `ww_lang_packs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isocode` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direction` varchar(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date_format` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date_format_long` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `ww_lang_packs` (`id`, `isocode`, `name`, `direction`, `locale`, `date_format`, `date_format_long`) VALUES
(1, 'en', 'English', 'ltr', 'en_US.UTF-8,en_US,eng,English', 'Y-m-d', 'D, d M Y');

CREATE TABLE IF NOT EXISTS `ww_lang_strings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `langpack` int(10) unsigned NOT NULL,
  `string` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `translated` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pack` (`langpack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ww_sessions` (
  `id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user` int(10) NOT NULL,
  `data` text NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ww_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO `ww_usergroups` (`id`, `title`) VALUES
(1, 'Guest'),
(2, 'User'),
(3, 'Administrator');

CREATE TABLE IF NOT EXISTS `ww_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) NOT NULL,
  `group` int(10) unsigned NOT NULL,
  `lastactive` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `ww_users` (`id`, `username`, `password`, `email`, `group`, `lastactive`) VALUES
(1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'example@example.com', 1, '0000-00-00 00:00:00');
