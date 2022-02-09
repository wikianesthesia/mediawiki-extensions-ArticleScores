<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use MediaWiki\Extension\ArticleScores\AbstractMetric;
use Title;

class EditorRating extends AbstractMetric {
    public function getArticleScoreHtml( Title $title ): string {
        $html = '';

        $html .= 'Editor rating: ';

        $articleScoreValues = $this->getArticleScoreValues( $title );

        $html .= $articleScoreValues[ 'main' ]->value;

        return $html;
    }
}