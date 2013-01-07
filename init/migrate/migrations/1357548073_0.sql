
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `internal_shop_deal` (
    `id` int(11) unsigned NOT NULL COMMENT '' auto_increment,
    `title` varchar(127) NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    `price` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `coint_type` enum('usual','big') NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    `coins_count` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `ctime` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `is_active` int(1) unsigned NULL DEFAULT NULL COMMENT '',
    `image_url` varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `partner` (
    `id` int(11) unsigned NOT NULL COMMENT '' auto_increment,
    `title` varchar(127) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    `location` varchar(127) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    `ctime` int(11) NULL DEFAULT NULL COMMENT '',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `partner_deal` (
    `id` int(11) unsigned NOT NULL COMMENT '' auto_increment,
    `partner_id` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `title` varchar(127) NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    `description` varchar(511) NULL DEFAULT '' COMMENT '' COLLATE utf8_general_ci,
    `coint_type` enum('usual','big') NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    `coins_count` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `ctime` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `is_active` int(1) unsigned NULL DEFAULT NULL COMMENT '',
    `image_url` varchar(255) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
