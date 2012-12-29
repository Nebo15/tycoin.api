
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `transaction` ADD `to_code` char(6) NULL DEFAULT NULL COMMENT '' COLLATE utf8_general_ci AFTER recipient_id;


SET FOREIGN_KEY_CHECKS = 1;
