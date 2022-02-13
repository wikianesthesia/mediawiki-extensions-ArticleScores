<?php

namespace MediaWiki\Extension\ArticleScores\Parser;

use MediaWiki\Extension\ArticleScores\ArticleScores as ArticleScoresManager;

use Html;
use Parser;
use PPFrame;

class ArticleScores {
    public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
        $parser->getOutput()->updateCacheExpiry( 0 );

        $output = '';

        $title = $parser->getTitle();

        if( !$title || !$title->exists() ) {
            return $output;
        }

        if( !isset( $args[ 'metric' ] ) ) {
            $metrics = ArticleScoresManager::getMetrics();
            $includeLabel = true;
        } else {
            $metrics = [ ArticleScoresManager::getMetric( $args[ 'metric' ] ) ];
            $includeLabel = false;
        }

        foreach( $metrics as $metric ) {
            $output .= Html::rawElement( 'div', [
                'class' => $metric->getMsgKeyPrefix()
            ], $metric->getArticleScoreHtml( $title, $includeLabel ) );
        }

        return [
            $output,
            'markerType' => 'nowiki'
        ];
    }
}