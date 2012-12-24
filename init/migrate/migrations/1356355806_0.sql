
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `transaction` (
    `id` int(11) unsigned NOT NULL COMMENT '' auto_increment,
    `sender_id` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `recipient_id` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `coins_type` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `coins_count` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `status` int(11) unsigned NULL DEFAULT NULL COMMENT '',
    `message` varchar(128) NULL DEFAULT NULL COMMENT '' COLLATE latin1_swedish_ci,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

DROP TABLE `day`;

DROP TABLE `day_comment`;

DROP TABLE `day_favorite`;

DROP TABLE `day_interest`;

DROP TABLE `day_like`;

DROP TABLE `moment`;

DROP TABLE `moment_comment`;

DROP TABLE `moment_like`;

SET FOREIGN_KEY_CHECKS = 1;
