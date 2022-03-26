<?php

namespace MediaWiki\Extension\ArticleScores\Action;

use Action;
use MediaWiki\Extension\ArticleScores\ArticleScores;

class ScoreAction extends Action {
    public function getName() {
        return 'score';
    }

    public function show() {
        // This will throw exceptions if there's a problem
        $this->checkCanExecute( $this->getUser() );

        $title = $this->getTitle();

        $out = $this->getOutput();

        $out->setPageTitle( wfMessage( 'articlescores-action-title', $title->getText() )->text() );
        $out->addBacklinkSubtitle( $title );

        if( !ArticleScores::canTitleStoreArticleScore( $title ) ) {
            $out->addHTML( wfMessage( 'articlescores-action-notallowed', $title->getFullText() ) );

            return;
        }

        $out->addHTML( $out->parseAsContent( '<articlescores />' ) );
    }

    public function doesWrites() {
        return true;
    }
}