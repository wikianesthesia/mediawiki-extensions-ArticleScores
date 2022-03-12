<?php

namespace MediaWiki\Extension\ArticleScores\Metric\EditorRating;

use OutputPage;
use Skin;

class Hooks {
    public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
        // Always need to add module to allow adding link flair to search suggestions
        $out->addModules( 'ext.articleScores.editorRating' );
    }
}