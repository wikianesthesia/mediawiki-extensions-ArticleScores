<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use MediaWiki\Extension\ArticleScores\AbstractMetric;

class EditorRating extends AbstractMetric {
    public function isSingular(): bool {
        return true;
    }
}