<?php

namespace MediaWiki\Extension\ArticleScores\Hook;

use MediaWiki\Extension\ArticleScores\ArticleScores;
use MediaWiki\Extension\JsonSchemaClasses\ClassRegistry;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MediaWiki\Hook\ParserFirstCallInitHook;

class HookHandler implements
    ArticleScoresRegisterMetricsHook,
    LoadExtensionSchemaUpdatesHook,
    ParserFirstCallInitHook {

    public function onArticleScoresRegisterMetrics( ClassRegistry $metricRegistry ) {
        $metricRegistry->register( ArticleScores::getMetricsLocalDirectory(), true );
    }

    public function onLoadExtensionSchemaUpdates( $updater ) {
        # Make sure these are in the order you want them added to the database. The keys are the table names and the
        # values are any field in the table (used to see if the table is empty to insert the default data).
        $tableNames = [
            'articlescore' => 'articlescore_id'
        ];

        $db = $updater->getDB();

        $sqlDir = __DIR__ . '/../../sql';

        # Create extension tables
        foreach( $tableNames as $tableName => $selectField) {
            if( file_exists( $sqlDir . "/tables/$tableName.sql" ) ) {
                $updater->addExtensionTable( $tableName, $sqlDir . "/tables/$tableName.sql" );

                # Import default data for tables if data exists
                if( file_exists( $sqlDir . "/data/$tableName.sql" ) ) {
                    $importTableData = false;

                    if( $updater->tableExists( $tableName ) ) {
                        $res = $db->select( $tableName, $selectField );

                        if( $res->numRows() === 0 ) {
                            $importTableData = true;
                        }
                    } else {
                        $importTableData = true;
                    }

                    if( $importTableData ) {
                        $updater->addExtensionUpdate( array( 'applyPatch', $sqlDir . "/data/$tableName.sql", true ) );
                    }
                }
            }
        }
    }

    public function onParserFirstCallInit( $parser ) {

    }
}