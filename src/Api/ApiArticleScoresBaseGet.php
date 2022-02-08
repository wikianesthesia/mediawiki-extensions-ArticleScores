<?php


namespace MediaWiki\Extension\ArticleScores\Api;


abstract class ApiArticleScoresBaseGet extends ApiArticleScoresBase {

    /**
     * @inheritDoc
     */
    public function mustBePosted() {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function needsToken() {
        return false;
    }
}