<?php

namespace MediaWiki\Extension\ArticleScores\Api;

abstract class ApiArticleScoresBasePost extends ApiArticleScoresBase {

    /**
     * @inheritDoc
     */
    public function mustBePosted() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isWriteMode() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function needsToken() {
        return 'csrf';
    }
}