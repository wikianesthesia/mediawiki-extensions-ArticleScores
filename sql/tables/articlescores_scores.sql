CREATE TABLE /*_*/articlescores_scores (
  `score_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT(10) UNSIGNED NOT NULL,
  `metric_id` VARBINARY(64) NOT NULL,
  `submetric` VARBINARY(32) NOT NULL,
  `userscore` TINYINT(1) NOT NULL DEFAULT 0,
  `value` TINYBLOB NOT NULL,
  `comment` TINYBLOB NULL,
  `timestamp` BINARY(14) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`score_id`)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/articlescores_scores_page_id_metric_id_userscore ON /*_*/articlescores_scores (page_id, metric_id, userscore);
