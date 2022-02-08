<?php

namespace MediaWiki\Extension\ArticleScores\Api;

use ApiBase;
use MediaWiki\Extension\ArticleScores\ArticleScores;

class ApiArticleScoresGetScores extends ApiArticleScoresBaseGet {
    public function __construct( $api, $modName ) {
        parent::__construct( $api, $modName, '' );
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        $asaction = $this->getAction();

        $output = [ $asaction => [
            'result' => [],
            'status' => 'ok',
        ] ];

        $params = $this->extractRequestParams();

        $articleScores = ArticleScores::getArticleScoresForPageId( $params[ 'pageid' ] );

        if( !$articleScores ) {
            $output[ $asaction ][ 'status' ] = 'error';
            $output[ $asaction ][ 'message' ] = wfMessage(
                'articlescores-invalidpageid',
                $params[ 'pageid' ]
            );

            $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );

            return;
        }

        $output[ $asaction ][ 'result' ] = $articleScores->getValues();

        $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );
    }

    /**
     * @inheritDoc
     */
    protected function getAction() {
        return 'getscores';
    }

    public function getAllowedParams() {
        return [
            'pageid' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ]
        ];
    }
}