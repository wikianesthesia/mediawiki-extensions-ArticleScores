<?php

namespace MediaWiki\Extension\ArticleScores\Parser;

use MediaWiki\Extension\ArticleScores\ArticleScores as ArticleScoresManager;

use Html;
use Parser;
use PPFrame;

class ArticleScores {
    public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
        $output = '';

        $title = $parser->getTitle();

        if( !ArticleScoresManager::canTitleHaveArticleScore( $title ) ) {
            return $output;
        }

        $parser->getOutput()->addModules( 'ext.articleScores.common' );

        if( !isset( $args[ 'metric' ] ) ) {
            $metrics = ArticleScoresManager::getMetrics();
            $includeLabel = true;
        } else {
            $metrics = [ ArticleScoresManager::getMetric( $args[ 'metric' ] ) ];
            $includeLabel = false;
        }

        foreach( $metrics as $metric ) {
            $metricHtml = $metric->getArticleScoreHtml( $title, $includeLabel );

            if( $metricHtml ) {
                $metric->addResourceLoaderModules( $parser );

                $output .= Html::rawElement( 'div', [
                    'class' => $metric->getMsgKeyPrefix()
                ], $metricHtml );
            }
        }

        return [
            $output,
            'markerType' => 'nowiki'
        ];
    }
}