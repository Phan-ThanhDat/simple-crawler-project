CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `manufacturer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bottle_size` float NOT NULL,
  `price` float NOT NULL,
  `price_per_liter` float DEFAULT NULL,
  `alcohol` float DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `quantity` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=34989 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci