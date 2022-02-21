CREATE TABLE /*_*/articlescores_scores (
  `page_id` INT(10) UNSIGNED NOT NULL,
  `metric_id` VARBINARY(64) NOT NULL,
  `submetric_id` VARBINARY(32) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `userscore` TINYINT(1) NOT NULL DEFAULT 0,
  `value` TINYBLOB NOT NULL,
  `timestamp` BINARY(14) NOT NULL,
  PRIMARY KEY (`page_id`,`metric_id`,`submetric_id`,`user_id`)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/articlescores_scores_page_id_metric_id_userscore ON /*_*/articlescores_scores (page_id, metric_id, userscore);
