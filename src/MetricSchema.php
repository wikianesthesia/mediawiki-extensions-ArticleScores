<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\JsonSchemaClasses\AbstractSchema;
use MediaWiki\MediaWikiServices;

class MetricSchema extends AbstractSchema {
    public function getBaseClass(): string {
        return AbstractMetric::class;
    }

    public function getClassDefinitionFileName(): string {
        return 'metric.json';
    }

    public function getExtensionName(): string {
        return ArticleScores::getExtensionName();
    }

    public function getSchemaFile(): string {
        return ArticleScores::getExtensionLocalDirectory() . '/resources/schema/metric.schema.json';
    }

    public function getSchemaName(): string {
        return 'Metric';
    }

    public function registerClasses( &$classRegistry ) {
        MediaWikiServices::getInstance()->get( 'ArticleScoresHookRunner' )
            ->onArticleScoresRegisterMetrics( $classRegistry );
    }
}