CREATE TABLE IF NOT EXISTS `cities` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  
  `latitude` decimal(10,6),
  `longitude` decimal(10,6),
  `population` int,

  `country_id` int NOT NULL,
  `api_id` varchar(100) UNIQUE,

  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  `is_api` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',

  FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`)
);