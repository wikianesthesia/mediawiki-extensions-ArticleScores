<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use MediaWiki\Extension\ArticleScores\AbstractMetric;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use Title;

class Likes extends AbstractMetric {
    protected function getUpdatedDerivedValue( Title $title, string $submetricId ) {
        $validSubmetricIds = [ 'main', 'dislikes', 'likes' ];

        if( in_array( $submetricId, $validSubmetricIds ) ) {
            $db = ArticleScores::getDB();

            $conds = [
                'page_id' => $title->getArticleId(),
                'metric_id' => $this->getId(),
                'submetric' => 'user',
            ];

            if( $submetricId === 'main' ) {
                $vars = 'SUM(value) as value';
            } elseif( $submetricId === 'likes' || $submetricId === 'dislikes' ) {
                $vars = 'COUNT(score_id) as value';

                $conds[ 'value' ] = $submetricId === 'likes' ? 1 : -1;
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
}