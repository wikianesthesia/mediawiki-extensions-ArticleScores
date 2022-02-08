<?php

// TODO extends ApiUserAchievementsBasePost

namespace MediaWiki\Extension\ArticleScores\Api;

use ApiBase;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Wikimedia\ParamValidator\ParamValidator;
use Title;

class ApiArticleScoresSetScore extends ApiArticleScoresBaseGet {
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

        $title = Title::newFromID( $params[ 'pageid' ] );

        if( !$title ) {
            $output[ $asaction ][ 'status' ] = 'error';
            $output[ $asaction ][ 'message' ] = wfMessage(
                'articlescores-invalidpageid',
                $params[ 'pageid' ]
            );

            $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );

            return;
        }

        $metric = ArticleScores::getMetric( $params[ 'metricid' ] );

        if( !$metric ) {
            $output[ $asaction ][ 'status' ] = 'error';
            $output[ $asaction ][ 'message' ] = wfMessage(
                'articlescores-invalidmetric',
                $params[ 'metricid' ]
            );

            $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );

            return;
        }

        $submetricId = $params[ 'submetricid' ];
        $value = $params[ 'value' ];

        $result = $metric->setArticleScoreValue( $title, $value, $submetricId );

        if( !$result->isOK() ) {
            $output[ $asaction ][ 'status' ] = 'error';
            $output[ $asaction ][ 'message' ] = $result->getHTML();

            $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );

            return;
        }

        $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );
    }

    /**
     * @inheritDoc
     */
    protected function getAction() {
        return 'setscore';
    }

    public function getAllowedParams() {
        return [
            'pageid' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'metricid' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'submetricid' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string',
                ParamValidator::PARAM_DEFAULT => 'main'
            ],
            'value' => [
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ],
        ];
    }
}