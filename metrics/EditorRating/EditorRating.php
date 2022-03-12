<?php

namespace MediaWiki\Extension\ArticleScores\Metric;

use Html;
use MediaWiki\Extension\ArticleScores\AbstractMetric;
use Parser;
use RequestContext;
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
                'class' => $this->getMsgKeyPrefix() . '-value'
            ], $editorRatingValue );

            if( $includeInput && $this->userCanSetArticleScore( $title ) ) {
                $html .= Html::openElement( 'div', [
                    'class' => $this->getMsgKeyPrefix() . '-input'
                ] );

                $submetricValueOptions = $this->getSubmetric( 'main' )->getValueDefinition()->getOptions();

                $html .= wfMessage( $this->getMsgKeyPrefix() . '-changerating' )->text() . ': ';

                $html .= Html::openElement( 'select', [
                    'class' => $this->getMsgKeyPrefix() . '-input-select'
                ] );

                foreach( $submetricValueOptions as $submetricValueOption ) {
                    $optionAttribs = [
                        'value' => $submetricValueOption->getValue()
                    ];

                    if( $articleScoreValues[ 'main' ]->value == $submetricValueOption->getValue() ) {
                        $optionAttribs[ 'selected' ] = 'true';
                    }

                    $html .= Html::rawElement( 'option', $optionAttribs, $submetricValueOption->getName() );
                }

                $html .= Html::closeElement( 'select' );
                $html .= Html::closeElement( 'div' );
            }
        }

        return $html;
    }

    public function hasLinkFlair(): bool {
        return true;
    }
}