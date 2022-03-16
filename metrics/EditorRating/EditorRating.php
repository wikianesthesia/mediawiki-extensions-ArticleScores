<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use Html;
use MediaWiki\Extension\ArticleScores\AbstractMetric;
use Parser;
use Title;

class EditorRating extends AbstractMetric {
    public function addResourceLoaderModules( Parser $parser ): void {
        $parser->getOutput()->addModules( 'ext.articleScores.editorRating' );
    }

    public function getArticleScoreHtml( Title $title, bool $includeLabel = true, bool $includeInput = true ): string {
        $html = '';

        $articleScoreValues = $this->getArticleScoreValues( $title, false, true );

        if( isset( $articleScoreValues[ 'main' ] ) ) {
            $editorRatingValue = '';

            if( $articleScoreValues[ 'main' ]->icon ) {
                $editorRatingValue .= Html::rawElement( 'i', [
                    'class' => $articleScoreValues[ 'main' ]->icon . ' ' . $this->getMsgKeyPrefix() . '-icon',
                    'style' => 'color: ' . $articleScoreValues[ 'main' ]->iconColor
                ] );
            }

            $editorRatingValue .= $articleScoreValues[ 'main' ]->name;

            if( $includeLabel ) {
                $html .= $this->getName() . ': ';
            }

            $html .= Html::rawElement( 'span', [
                'class' => $this->getMsgKeyPrefix() . '-value',
                'data-value' => $articleScoreValues[ 'main' ]->value,
                'title' => $articleScoreValues[ 'main' ]->description
            ], $editorRatingValue );

            if( $includeInput ) {
                $html .= Html::rawElement( 'div', [
                    'class' => 'articlescores-input ' . $this->getMsgKeyPrefix() . '-input'
                ] );
            }
        }

        return $html;
    }

    public function getLinkFlairHtml( Title $title ): string {
        $html = '';

        $articleScoreValues = $this->getArticleScoreValues( $title, false, true );

        if( isset( $articleScoreValues[ 'main' ] ) && $articleScoreValues[ 'main' ]->icon ) {
            $html .= Html::rawElement( 'i', [
                'class' => $articleScoreValues[ 'main' ]->icon . ' ' . $this->getMsgKeyPrefix() . '-icon',
                'style' => 'color: ' . $articleScoreValues[ 'main' ]->iconColor
            ] );
        }

        return $html;
    }

    public function hasLinkFlair(): bool {
        return true;
    }
}