<?php

namespace MediaWiki\Extension\ArticleScores\Api;

use ApiBase;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Title;

class ApiArticleScoresGetScoreInfo extends ApiArticleScoresBaseGet {
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

        $title = null;
        $articleScoreValues = null;

        if( $params[ 'pageid' ] ) {
            $title = Title::newFromID( $params[ 'pageid' ] );
        } elseif( $params[ 'title' ] ) {
            $title = Title::newFromID( $params[ 'title' ] );
        }

        if( $title ) {
            $articleScoreValues = ArticleScores::getArticleScoresForTitle( $title );
        }

        $metrics = ArticleScores::getMetrics();

        foreach( $metrics as $metric ) {
            $output[ $asaction ][ 'result' ][ $metric->getId() ] = [];

            foreach( $metric->getSubmetrics() as $submetric ) {
                $submetricValueDefinition = $submetric->getValueDefinition();

                $output[ $asaction ][ 'result' ][ $metric->getId() ][ $submetric->getId() ] = [
                    'default' => $submetricValueDefinition->getDefault(),
                    'derived' => $submetricValueDefinition->isDerived(),
                    'max' => $submetricValueDefinition->getMax(),
                    'min' => $submetricValueDefinition->getMin(),
                    'step' => $submetricValueDefinition->getStep(),
                    'unset' => $submetricValueDefinition->getUnset()
                ];

                $submetricValueOptions = $submetricValueDefinition->getOptions();

                if( $submetricValueOptions ) {
                    $output[ $asaction ][ 'result' ][ $metric->getId() ][ $submetric->getId() ][ 'options' ] = [];

                    foreach( $submetricValueOptions as $submetricValueOption ) {
                        $output[ $asaction ][ 'result' ][ $metric->getId() ][ $submetric->getId() ][ 'options' ][ $submetricValueOption->getValue() ] = [
                            'description' => $submetricValueOption->getDescription(),
                            'icon' => $submetricValueOption->getIcon(),
                            'iconColor' => $submetricValueOption->getIconColor(),
                            'name' => $submetricValueOption->getName(),
                            'value' => $submetricValueOption->getValue()
                        ];
                    }
                }

                if( $title ) {
                    $output[ $asaction ][ 'result' ][ $metric->getId() ][ $submetric->getId() ][ 'userCanSet' ] = (int) $metric->userCanSetArticleScore( $this->getUser(), $title, $submetric->getId() );

                    if( $articleScoreValues && isset( $articleScoreValues[ $metric->getId() ][ $submetric->getId() ] ) ) {
                        $output[ $asaction ][ 'result' ][ $metric->getId() ][ $submetric->getId() ][ 'value' ] = $articleScoreValues[ $metric->getId() ][ $submetric->getId() ]->value;
                    }
                }
            }
        }

        $this->getResult()->addValue( null, $this->apiArticleScores->getModuleName(), $output );
    }

    /**
     * @inheritDoc
     */
    protected function getAction() {
        return 'getscoreinfo';
    }

    public function getAllowedParams() {
        return [
            'pageid' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ],
            'title' => [
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ]
        ];
    }
}