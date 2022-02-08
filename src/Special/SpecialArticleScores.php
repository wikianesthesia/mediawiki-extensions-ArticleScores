<?php

namespace MediaWiki\Extension\ArticleScores\Special;

use MediaWiki\Extension\ArticleScores\ArticleScores;
use MediaWiki\Extension\ArticleScores\ArticleScore;
use SpecialPage;

class SpecialArticleScores extends SpecialPage {

    public function __construct() {
        parent::__construct( 'ArticleScores' );
    }

    public function doesWrites() {
        return true;
    }

    public function execute( $subPage ) {
        //var_dump(ArticleScores::getMetrics());
        var_dump(ArticleScore::newFromTitleId( 321 ) );
        die('woo');
    }

}