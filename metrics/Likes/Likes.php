<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use Elastica\Request;
use Html;
use MediaWiki\Extension\ArticleScores\AbstractMetric;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Parser;
use RequestContext;
use Title;

class Likes extends AbstractMetric {
    public function addResourceLoaderModules( Parser $parser ): void {
        $parser->getOutput()->addModules( 'ext.articleScores.likes' );
    }

    public function getArticleScoreHtml( Title $title, bool $includeLabel = true, bool $includeInput = true ): string {
        $html = '';

        $enableDislikes = $this->getConfig( 'EnableDislikes' );

        $articleScoreValues = $this->getArticleScoreValues( $title, true, true );

        if( $includeLabel ) {
            $html .= $this->getName() . ': ';
        }

        $html .= Html::openElement( 'span', [
            'class' => $this->getMsgKeyPrefix() . '-value'
        ] );

        $html .= Html::rawElement( 'span', [
            'class' => $this->getMsgKeyPrefix() . '-likes-value',
            'data-value' => $articleScoreValues[ 'likes' ]->value
        ], $articleScoreValues[ 'likes' ]->value );

        if( $enableDislikes ) {
            $html .= ' (' .
                Html::rawElement( 'span', [
                    'class' => $this->getMsgKeyPrefix() . '-percentLikes-value',
                    'data-value' => $articleScoreValues[ 'percentLikes' ]->value
                ], $articleScoreValues[ 'percentLikes' ]->value )
                . '%)';
        }

        $html .= Html::closeElement( 'span' );

        if( $includeInput ) {
            $tag = $enableDislikes ? 'div' : 'span';

            $html .= Html::rawElement( $tag, [
                'class' => 'articlescores-input ' . $this->getMsgKeyPrefix() . '-input'
            ] );
        }

        return $html;
    }

    public function isScoreValueValid( $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): bool {
        if( $submetricId === 'user' && !$this->getConfig( 'EnableDislikes' ) && $value == -1 ) {
            // Override disallow -1 (the value for dislike)
            return false;
        } else {
            return parent::isScoreValueValid( $value, $submetricId );
        }
    }

    protected function getUpdatedDerivedValue( Title $title, string $submetricId ) {
        $validSubmetricIds = [ 'likes' ];

        if( $this->getConfig( 'EnableDislikes' ) ) {
            $validSubmetricIds = array_merge( $validSubmetricIds, [ 'main', 'dislikes', 'percentLikes' ] );
        }

        if( in_array( $submetricId, $validSubmetricIds ) ) {
            $db = ArticleScores::getDB();

            $conds = [
                'page_id' => $title->getArticleId(),
                'metric_id' => $this->getId(),
                'submetric_id' => 'user',
            ];

            if( $submetricId === 'main' ) {
                $vars = 'SUM(value) as value';
            } elseif( $submetricId === 'likes' || $submetricId === 'dislikes' ) {
                $vars = 'COUNT(value) as value';

                $conds[ 'value' ] = $submetricId === 'likes' ? 1 : -1;
            } elseif( $submetricId === 'percentLikes' ) {
                $vars = 'ROUND(100 * SUM(CASE WHEN value=1 THEN 1 ELSE 0 END) / COUNT(value), 1) as value';
            }

            $res = $db->select(
                'articlescores_scores',
                $vars,
                $conds,
                __METHOD__
            );

            $row = $res->fetchRow();

            return $row[ 'value' ];
        }

        return false;
    }

    protected function getLogAction( $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): string {
        if( $value == 1 ) {
            return 'like';
        } elseif( $value == -1 ) {
            return 'dislike';
        } else {
            return 'unlike';
        }
    }

    /**
     * @param array $definition
     */
    protected function postprocessDefinition( array &$definition ) {
        parent::postprocessDefinition( $definition );

        if( !$this->getConfig( 'EnableDislikes' ) ) {
            unset( $this->submetrics[ 'main' ] );
            unset( $this->submetrics[ 'dislikes' ] );
            unset( $this->submetrics[ 'percentLikes' ] );
        }
    }
}