CREATE TABLE /*_*/articlescore (
  `articlescore_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT(10) UNSIGNED NOT NULL,
  `metric` VARBINARY(32) NOT NULL,
  `value` TINYBLOB NOT NULL,
  `timestamp` BINARY(14) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`articlescore_id`)
) /*$wgDBTableOptions*/;