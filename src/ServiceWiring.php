<?php
namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\ArticleScores\Hook\HookRunner;
use MediaWiki\MediaWikiServices;

return [
    'ArticleScoresHookRunner' => static function ( MediaWikiServices $services ): HookRunner {
        return new HookRunner( $services->getHookContainer() );
    },
];
