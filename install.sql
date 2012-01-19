SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `ww_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `ww_users` (`id`, `username`, `password`, `email`, `group`) VALUES
(1, 'admin', SHA1('admin'), 'example@example.com', 1);

CREATE TABLE IF NOT EXISTS `ww_sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(10) NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ww_lang_packs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `isocode` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `direction` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_format` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `date_format_long` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `ww_lang_packs` (`id`, `isocode`, `name`, `direction`, `locale`, `date_format`, `date_format_long`) VALUES
(1, 'en', 'English', 'ltr', 'en_US.UTF-8,en_US,eng,English', 'Y-m-d', 'D, d M Y');

CREATE TABLE IF NOT EXISTS `ww_lang_strings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `langpack` int(10) unsigned NOT NULL,
  `string` text COLLATE utf8_unicode_ci NOT NULL,
  `translated` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pack` (`pack`)
) DEFAULT CHARSET=utf8;
