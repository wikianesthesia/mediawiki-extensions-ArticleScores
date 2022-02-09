<?php

namespace MediaWiki\Extension\ArticleScores\Parser;

use MediaWiki\Extension\ArticleScores\ArticleScores as ArticleScoresManager;

use Parser;
use PPFrame;

class ArticleScores {
    public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
        $output = '';

        $title = $parser->getTitle();

        if( !$title || !$title->exists() ) {
            return $output;
        }

        $metrics = ArticleScoresManager::getMetrics();

        foreach( $metrics as $metric ) {
            $output .= $metric->getArticleScoreHtml( $title );
        }

        return $output;
    }
}