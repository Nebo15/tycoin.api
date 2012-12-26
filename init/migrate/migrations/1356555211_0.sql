
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `transaction` MODIFY `coins_type` enum('usual','big') NOT NULL COLLATE utf8_general_ci;
ALTER TABLE `transaction` MODIFY `coins_count` int(7) unsigned NOT NULL;
ALTER TABLE `transaction` MODIFY `type` enum('transfer','payment','purchase','restore') NOT NULL COLLATE utf8_general_ci;
ALTER TABLE `transaction` ALTER `ctime`  DROP DEFAULT;
ALTER TABLE `transaction` DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
