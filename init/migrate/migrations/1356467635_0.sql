
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `transaction` MODIFY `coins_type` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '';
#
#  Fieldformat of 'transaction.coins_type' changed from 'int(11) unsigned NULL DEFAULT NULL COMMENT '' to int(11) unsigned NOT NULL DEFAULT 0 COMMENT ''. Possibly data modifications needed!
#

ALTER TABLE `transaction` MODIFY `coins_count` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '';
#
#  Fieldformat of 'transaction.coins_count' changed from 'int(11) unsigned NULL DEFAULT NULL COMMENT '' to int(11) unsigned NOT NULL DEFAULT 0 COMMENT ''. Possibly data modifications needed!
#

ALTER TABLE `transaction` MODIFY `ctime` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '';
#
#  Fieldformat of 'transaction.ctime' changed from 'int(11) unsigned NULL DEFAULT NULL COMMENT '' to int(11) unsigned NOT NULL DEFAULT 0 COMMENT ''. Possibly data modifications needed!
#



SET FOREIGN_KEY_CHECKS = 1;
