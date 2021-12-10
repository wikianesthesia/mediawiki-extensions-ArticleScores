<?php

namespace MediaWiki\Extension\ArticleScores\Hook;

use MediaWiki\Extension\JsonSchemaClasses\ClassRegistry;

interface ArticleScoresRegisterMetricsHook {
    public function onArticleScoresRegisterMetrics( ClassRegistry $metricRegistry );
}