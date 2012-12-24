
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `transaction` ADD `type` int(11) unsigned NULL DEFAULT NULL COMMENT '' AFTER coins_count;
ALTER TABLE `transaction` ADD `ctime` int(11) unsigned NULL DEFAULT NULL COMMENT '' AFTER message;
ALTER TABLE `transaction` DROP `status`;


SET FOREIGN_KEY_CHECKS = 1;
