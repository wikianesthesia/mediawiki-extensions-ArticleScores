<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\JsonSchemaClasses\JsonSchemaClassManager;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;
use Wikimedia\Rdbms\DBConnRef;

class ArticleScores {
    protected const SCHEMA_CLASS = MetricSchema::class;

    /**
     * @var JsonSchemaClassManager
     */
    protected static $classManager;

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
        return static::$classManager->getClassInstanceForSchema( static::SCHEMA_CLASS, $metricId );
    }



    /**
     * @return AbstractMetric[]
     */
    public static function getMetrics(): array {
        return static::$classManager->getClassInstancesForSchema( static::SCHEMA_CLASS );
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
     *
     */
    public static function initialize() {
        static::$classManager = MediaWikiServices::getInstance()->get( 'JsonSchemaClassManager' );
        static::$classManager->registerSchema(MetricSchema::class );
    }
}