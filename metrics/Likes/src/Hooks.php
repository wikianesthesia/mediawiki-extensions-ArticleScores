<?php

namespace MediaWiki\Extension\ArticleScores\Metric\Likes;

use SkinTemplate;
use Title;

class Hooks {
    public static function onPersonalUrls( array &$personal_urls, Title $title, SkinTemplate $skin ) {
        // Quick surrogate to check whether user is logged in
        if( !isset( $personal_urls[ 'logout' ] ) ) {
            return;
        }

        $personal_urls = array_merge(
            array_slice( $personal_urls, 0, count( $personal_urls ) - 1, true ), [
            'likes' => [
                'text' => wfMessage( 'articlescores-likes-mylikes' )->text(),
                'href' => Title::newFromText( 'Special:ArticleScores/Likes/user' )->getLinkURL( 'value=0' )
            ] ],
            array_slice( $personal_urls, count( $personal_urls ) - 1, 1, true )
        );
    }
}