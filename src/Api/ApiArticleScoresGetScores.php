<?php

namespace MediaWiki\Extension\ArticleScores\Api;

use ApiBase;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Wikimedia\ParamValidator\ParamValidator;
use Title;

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

        if( $params[ 'pageids' ] ) {
            $pageIds = explode( ',', $params[ 'pageids' ] );

            foreach( $pageIds as $pageId ) {
                $title = Title::newFromID( $pageId );

                $output[ $asaction ][ 'result' ][ $pageId ] = ArticleScores::getArticleScoresForTitle(
                    $title,
                    $params[ 'userscores' ],
                    $params[ 'defaults' ],
                );
            }
        }

        if( $params[ 'titles' ] ) {
            $titleTexts = explode( ',', $params[ 'titles' ] );

            foreach( $titleTexts as $titleText ) {
                $title = Title::newFromText( $titleText );

                $output[ $asaction ][ 'result' ][ $titleText ] = ArticleScores::getArticleScoresForTitle(
                    $title,
                    $params[ 'userscores' ],
                    $params[ 'defaults' ],
                );
            }
        }

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
            'pageids' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'titles' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'userscores' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer',
                ParamValidator::PARAM_DEFAULT => 1
            ],
            'defaults' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer',
                ParamValidator::PARAM_DEFAULT => 1
            ]
        ];
    }
}