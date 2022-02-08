<?php

namespace MediaWiki\Extension\ArticleScores\Action;

use Action;

class ScoreAction extends Action {
    public function getName() {
        return 'score';
    }

    public function show() {
        // This will throw exceptions if there's a problem
        $this->checkCanExecute( $this->getUser() );

        $out = $this->getOutput();

        $out->addHTML( 'Score action!' );
    }

    public function doesWrites() {
        return true;
    }
}