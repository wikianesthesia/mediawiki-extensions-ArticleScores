<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use MediaWiki\Extension\ArticleScores\AbstractMetric;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Status;
use Title;

class Likes extends AbstractMetric {
    public function getArticleScoreHtml( Title $title ): string {
        $html = '';

        $articleScoreValues = $this->getArticleScoreValues( $title );

        $html .= 'User score: ';

        if( $articleScoreValues[ 'main' ]->value > 0 ) {
            $html .= '+';
        }

        $html .= $articleScoreValues[ 'main' ]->value;

        $html .= ' (' . $articleScoreValues[ 'percentLikes' ]->value . '%)';

        return $html;
    }

    protected function getUpdatedDerivedValue( Title $title, string $submetricId ) {
        $validSubmetricIds = [ 'main', 'dislikes', 'likes', 'percentLikes' ];

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
                $vars = 'COUNT(score_id) as value';

                $conds[ 'value' ] = $submetricId === 'likes' ? 1 : -1;
            } elseif( $submetricId === 'percentLikes' ) {
                $vars = 'ROUND(100 * SUM(CASE WHEN value=1 THEN 1 ELSE 0 END) / COUNT(score_id), 1) as value';
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

    protected function doSetArticleScoreValue( Title $title, $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): Status {
        $result = parent::doSetArticleScoreValue( $title, $value, $submetricId );

        $db = ArticleScores::getDB( DB_MASTER );

        // Delete any user rows with value 0 (0 effectively means unset)
        $db->delete(
            'articlescores_scores',
            [
                'page_id' => $title->getArticleID(),
                'metric_id' => $this->getId(),
                'submetric_id' => 'user',
                'value' => 0
            ]
        );

        return $result;
    }
}