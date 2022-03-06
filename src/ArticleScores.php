<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\JsonClasses\JsonClassManager;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;
use Title;
use Wikimedia\Rdbms\DBConnRef;
use WANObjectCache;

class ArticleScores {
    public const CACHE_TTL = WANObjectCache::TTL_DAY;
    public const DEFAULT_SUBMETRIC = 'main';
    protected const SCHEMA_CLASS = MetricSchema::class;

    /**
     * @var string
     */
    protected static $extensionLocalDirectory;

    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * @var string
     */
    protected static $metricsLocalDirectory;

    /**
     * @var bool
     */
    protected static $useLinkFlair = false;



    public static function canTitleHaveArticleScore( Title $title ): bool {
        global $wgArticleScoresEnabledNamespaces;

        if( !$title ||
            !$title->exists() ||
            !in_array( $title->getNamespace(), $wgArticleScoresEnabledNamespaces )) {
            return false;
        }

        return true;
    }

    /**
     * @param int $pageId
     * @param bool $includeUserscores
     * @param bool $includeDefaultValues
     * @return array
     */
    public static function getArticleScoresForPageId( int $pageId, bool $includeUserscores = false, bool $includeDefaultValues = false ): array {
        $articleScores = [];

        $title = Title::newFromID( $pageId );

        if( !static::canTitleHaveArticleScore( $title ) ) {
            return $articleScores;
        }

        foreach( static::getMetrics() as $metric ) {
            $articleScores[ $metric->getId() ] = $metric->getArticleScoreValues( $title, $includeUserscores, $includeDefaultValues );
        }

        return $articleScores;
    }


    /**
     * @param int $i
     * @return DBConnRef
     */
    public static function getDB( $i = DB_REPLICA ): DBConnRef {
        $lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
        return $lb->getConnectionRef( $i );
    }



    /**
     * @return string
     */
    public static function getExtensionLocalDirectory(): string {
        if( !static::$extensionLocalDirectory ) {
            static::$extensionLocalDirectory = realpath( __DIR__ . '/..' );
        }

        return static::$extensionLocalDirectory;
    }



    /**
     * @return string
     */
    public static function getExtensionName(): string {
        return 'ArticleScores';
    }


    public static function getLinkFlairForPageId( int $pageId ): string {
        $linkFlair = '';

        $title = Title::newFromID( $pageId );

        if( !static::canTitleHaveArticleScore( $title ) ) {
            return $linkFlair;
        }

        foreach( static::getMetrics() as $metric ) {
            $linkFlair .= $metric->getLinkFlairHtml( $title );
        }

        return $linkFlair;
    }



    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface {
        if( !static::$logger ) {
            static::$logger = LoggerFactory::getInstance( static::getExtensionName() );
        }

        return static::$logger;
    }


    /**
     * @param string $metricId
     * @return AbstractMetric|null
     */
    public static function getMetric( string $metricId ): ?AbstractMetric {
        return MediaWikiServices::getInstance()->get( 'JsonClassManager' )
            ->getClassInstanceForSchema( static::SCHEMA_CLASS, $metricId );
    }



    /**
     * @return AbstractMetric[]
     */
    public static function getMetrics(): array {
        return MediaWikiServices::getInstance()->get( 'JsonClassManager' )
            ->getClassInstancesForSchema( static::SCHEMA_CLASS );
    }



    /**
     * @return string
     */
    public static function getMetricsLocalDirectory(): string {
        if( !static::$metricsLocalDirectory ) {
            $metricsLocalDirectory = static::getExtensionLocalDirectory() . '/metrics';

            if( !is_dir( $metricsLocalDirectory ) ) {
                // TODO throw error metrics directory not found

                return false;
            }

            static::$metricsLocalDirectory = $metricsLocalDirectory;
        }

        return static::$metricsLocalDirectory;
    }



    /**
     * @return bool
     */
    public static function getUseLinkFlair(): bool {
        return static::$useLinkFlair;
    }



    /**
     * @param bool $useLinkFlair
     */
    public static function setUseLinkFlair( bool $useLinkFlair = false ): void {
        static::$useLinkFlair = $useLinkFlair;
    }
}