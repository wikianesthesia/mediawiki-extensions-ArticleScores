<?php

namespace MediaWiki\Extension\ArticleScores;

use Database;
use MediaWiki\MediaWikiServices;
use Title;

class ArticleScore {
    protected $values = [];

    public static function clearCache( Title $title ) {
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        $cache->delete( self::getCacheKey( $title ) );
    }

    public static function newFromTitle( Title $title ): ?ArticleScore {
        $callback = function( $oldValue, &$ttl, array &$setOpts ) use ( $title ) {
            $setOpts += Database::getCacheSetOptions( ArticleScores::getDB() );

            $articleScore = new ArticleScore();

            $metrics = ArticleScores::getMetrics();

            foreach( $metrics as $metric ) {
                if( !$metric->isEnabled() ) {
                    continue;

                }

                $articleScore->values[ $metric->getId() ] = $metric->getArticleScoreValues( $title );
            }

            return $articleScore;
        };

        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

        return $cache->getWithSetCallback(
            self::getCacheKey( $title ),
            ArticleScores::CACHE_TTL,
            $callback
        );
    }

    public static function newFromTitleId( int $titleId ): ?ArticleScore {
        $title = Title::newFromID( $titleId );

        if( !$title || !$title->exists() ) {
            return null;
        }

        return static::newFromTitle( $title );
    }

    public function getValue( string $submetricId = '' ) {
        return $this->values[ $submetricId ] ?? null;
    }

    public function getValues(): array {
        return $this->values;
    }

    protected static function getCacheKey( Title $title ): string {
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        return $cache->makeKey( self::class, $title->getArticleID() );
    }
}