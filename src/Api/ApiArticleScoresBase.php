<?php

namespace MediaWiki\Extension\ArticleScores\Api;

use ApiBase;

abstract class ApiArticleScoresBase extends ApiBase {

    /**
     * @var ApiArticleScores
     */
    protected $apiArticleScores;

    /**
     * @param ApiArticleScores $api
     * @param string $modName
     * @param string $prefix
     */
    public function __construct( ApiArticleScores $api, string $modName, string $prefix = '' ) {
        $this->apiArticleScores = $api;

        parent::__construct( $api->getMain(), $modName, $prefix );
    }

    /**
     * Return the name of the practice groups action
     * @return string
     */
    abstract protected function getAction();

    /**
     * @inheritDoc
     */
    public function getParent() {
        return $this->apiArticleScores;
    }

    public function simplifyError( string $error ) {
        return explode(':', $error )[ 0 ];
    }
}