<?php

namespace MediaWiki\Extension\ArticleScores\Parser;

use MediaWiki\Extension\ArticleScores\ArticleScores;
use Html;
use Parser;
use PPFrame;

class ArticleScoresLinkFlair {
    public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
        $parser->getOutput()->addModules( 'ext.articleScores.common' );

        // Use bool $includeRedirects, or make the link flair helper functions more complicated?
        ArticleScores::setUseLinkFlair( true );

        foreach( ArticleScores::getMetrics() as $metric ) {
            if( $metric->hasLinkFlair() ) {
                $metric->addResourceLoaderModules( $parser );
            }
        }

        $output = $parser->recursiveTagParseFully( $input );

        ArticleScores::setUseLinkFlair( false );

        return $output;
    }
}