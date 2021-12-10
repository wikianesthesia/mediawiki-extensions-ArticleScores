<?php

namespace MediaWiki\Extension\ArticleScores\Hook;

use MediaWiki\Extension\JsonSchemaClasses\ClassRegistry;
use MediaWiki\HookContainer\HookContainer;

class HookRunner implements ArticleScoresRegisterMetricsHook {
    /** @var HookContainer */
    private $hookContainer;

    /**
     * @param HookContainer $hookContainer
     */
    public function __construct( HookContainer $hookContainer ) {
        $this->hookContainer = $hookContainer;
    }

    public function onArticleScoresRegisterMetrics( ClassRegistry $metricRegistry ) {
        return $this->hookContainer->run(
            'ArticleScoresRegisterMetrics',
            [ &$metricRegistry ]
        );
    }
}