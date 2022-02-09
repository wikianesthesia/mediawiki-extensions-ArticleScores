<?php

namespace MediaWiki\Extension\ArticleScores\Api;

use ApiBase;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Wikimedia\ParamValidator\ParamValidator;

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

        $output[ $asaction ][ 'result' ] = ArticleScores::getArticleScoresForPageId(
            $params[ 'pageid' ],
            $params[ 'userscores' ],
            $params[ 'defaults' ],
        );

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
            ],
            'userscores' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer',
                ParamValidator::PARAM_DEFAULT => 0
            ],
            'defaults' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer',
                ParamValidator::PARAM_DEFAULT => 0
            ]
        ];
    }
}