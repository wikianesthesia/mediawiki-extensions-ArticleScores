CREATE TABLE /*_*/articlescores_metrics (
  `metric_id` VARBINARY(32) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`metric_id`)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/articlescores_metrics_metric_id ON /*_*/articlescores_metrics (metric_id);