<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\JsonSchemaClasses\AbstractJsonSchemaClass;
use Title;

abstract class AbstractMetric extends AbstractJsonSchemaClass {

    public function getArticleScore( Title $title ) {
        // Have to think about how to do this efficiently.
        // Before page display: if articlescorelinks, extract all links from html, one sql query with in() list for all
        // page ids/titles on page.
    }

    /**
     * @return string
     */
    public function getMsgKeyPrefix(): string {
        return strtolower( ArticleScores::getExtensionName() . '-' . $this->getId() );
    }

    /**
     * Need to distinguish between single row in table (editor rating) vs. multiple (user votes)
     * Multiple could also use two metrics (userratingvote to store each vote, userrating to store mean)
     * @return bool
     */
    abstract public function isSingular(): bool;

    protected function getSchemaClass(): string {
        return MetricSchema::class;
    }
}