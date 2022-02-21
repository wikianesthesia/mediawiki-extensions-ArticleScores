<?php

namespace MediaWiki\Extension\ArticleScores\Special;

use Html;
use MediaWiki\Extension\ArticleScores\AbstractMetric;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use MediaWiki\Extension\ArticleScores\Pager\ArticleScoresTablePager;
use IncludableSpecialPage;
use MediaWiki\Extension\ArticleScores\Submetric;

class SpecialArticleScores extends IncludableSpecialPage {

    /**
     * @var AbstractMetric
     */
    protected $metric;

    /**
     * @var Submetric
     */
    protected $submetric;

    /**
     * @var int
     */
    protected $limit = 25;

    /**
     * @var bool
     */
    protected $showTimestamp = false;

    /**
     * @var bool
     */
    protected $showValue = true;

    public function __construct() {
        parent::__construct( 'ArticleScores' );
    }

    public function doesWrites() {
        return true;
    }

    public function execute( $subPage ) {
        $this->setHeaders();

        // Set default limit depending on whether page is being included or not
        $this->limit = $this->including() ? 10 : 25;

        $this->initialize( $subPage );

        if( $this->including() ) {
            $this->showInclude();
        } else {
            if( $this->submetric ) {
                $this->showSubmetric();
            } elseif( $this->metric ) {
                $this->showMetric();
            } else {
                $this->showMetricList();
            }
        }
    }

    public function showInclude() {
        $this->showTable();
    }

    public function showMetric() {
        $this->getOutput()->setPageTitle( $this->metric->getName() );

        $this->getOutput()->setSubtitle(
            $this->msg( 'backlinksubtitle' )->rawParams(
                $this->getLinkRenderer()->makeKnownLink(
                    self::getTitleFor( 'ArticleScores' ),
                    $this->msg( 'articlescores' )->text()
                )
            )
        );

        $submetrics = $this->metric->getSubmetrics();

        foreach( $submetrics as $submetric ) {
            $this->submetric = $submetric;

            if( !$submetric->isPerUser() ) {
                $this->showTable();
            }
        }

        $this->submetric = null;
    }

    public function showMetricList() {
        $html = '';

        $metrics = ArticleScores::getMetrics();

        $linkRenderer = $this->getLinkRenderer();

        $html .= Html::openElement( 'ul' );

        foreach( $metrics as $metric ) {
            $html .= Html::rawElement( 'li', [], $linkRenderer->makeKnownLink(
                self::getTitleFor( 'ArticleScores/' . $metric->getId() ),
                $metric->getName()
            ) );
        }

        $html .= Html::closeElement( 'ul' );

        $this->getOutput()->addHTML( $html );
    }

    public function showTable( int $userId = 0 ) {
        if( !$this->metric || !$this->submetric ) {
            return;
        }

        $pager = new ArticleScoresTablePager(
            $this->submetric,
            $userId,
            [
                'limit' => $this->limit,
                'value' => $this->showValue,
                'timestamp' => $this->showTimestamp
            ]
        );

        $pager->setLimit( $this->limit );

        $pager->doQuery();

        $this->getOutput()->addHtml( $pager->getFullOutput()->getText() );
    }

    public function showSubmetric() {
        if( $this->submetric->isPerUser() ) {
            $pageTitle = wfMessage( 'articlescores-my', $this->metric->getName() )->text();
        } else {
            $pageTitle = $this->submetric->getName();
        }

        $this->getOutput()->setPageTitle( $pageTitle );

        $this->showTable( $this->getUser()->getId() );
    }

    protected function initialize( $subPage ) {
        $paramValues = [
            'metric' => $this->metric,
            'submetric' => $this->submetric,
            'limit' => $this->limit,
            'value' => $this->showValue,
            'timestamp' => $this->showTimestamp
        ];

        $subPageParamsOrder = array_keys( $paramValues );
        $subPageParamValues = explode( '/', $subPage );

        for( $iParam = 0; $iParam < min( count( $subPageParamsOrder ), count( $subPageParamValues ) ); $iParam++ ) {
            $param = $subPageParamsOrder[ $iParam ];
            $paramValues[ $param ] = $subPageParamValues[ $iParam ];
        }

        $reqParamValues = $this->getRequest()->getValues();

        foreach( array_keys( $paramValues ) as $param ) {
            if( isset( $reqParamValues[ $param ] ) ) {
                $paramValues[ $param ] = $reqParamValues[ $param ];
            }
        }

        $this->metric = $paramValues[ 'metric' ] ? ArticleScores::getMetric( $paramValues[ 'metric' ] ) : null;
        $this->submetric = $paramValues[ 'submetric' ] && $this->metric ? $this->metric->getSubmetric( $paramValues[ 'submetric' ] ) : null;
        $this->limit = $paramValues[ 'limit' ];
        $this->showValue = $paramValues[ 'value' ];
        $this->showTimestamp = $paramValues[ 'timestamp' ];
    }
}