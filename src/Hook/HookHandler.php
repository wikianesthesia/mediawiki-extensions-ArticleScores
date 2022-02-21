<?php

namespace MediaWiki\Extension\ArticleScores\Hook;

use HtmlArmor;
use Html;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use MediaWiki\Extension\JsonSchemaClasses\ClassRegistry;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Linker\Hook\HtmlPageLinkRendererEndHook;
use RequestContext;
use Title;

class HookHandler implements
    ArticleScoresRegisterMetricsHook,
    HtmlPageLinkRendererEndHook,
    LoadExtensionSchemaUpdatesHook,
    ParserFirstCallInitHook,
    SkinTemplateNavigation__UniversalHook{

    public function onArticleScoresRegisterMetrics( ClassRegistry $metricRegistry ) {
        $metricRegistry->register( ArticleScores::getMetricsLocalDirectory(), true );
    }

    public function onHtmlPageLinkRendererEnd( $linkRenderer, $target, $isKnown, &$text, &$attribs, &$ret ) {
        if( !ArticleScores::getUseLinkFlair() || !$isKnown ) {
            return;
        }

        RequestContext::getMain()->getOutput()->addModules( 'ext.articleScores.common' );

        $title = Title::newFromLinkTarget( $target );

        if( $title->isRedirect() ) {
            return;
        }

        $text = new HtmlArmor(
            HtmlArmor::getHtml( $text ) .
            Html::rawElement( 'span', [ 'class' => 'articlescores-linkflair' ],
                ArticleScores::getLinkFlairForPageId( $title->getArticleID() )
            )
        );

        return true;
    }

    public function onLoadExtensionSchemaUpdates( $updater ) {
        # Make sure these are in the order you want them added to the database. The keys are the table names and the
        # values are any field in the table (used to see if the table is empty to insert the default data).
        $tableNames = [
            'articlescores_metrics' => 'metric_id',
            'articlescores_scores' => 'score_id'
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
        $parser->setHook( 'articlescores', 'MediaWiki\\Extension\\ArticleScores\\Parser\\ArticleScores::render' );
        $parser->setHook( 'articlescoreslinkflair', 'MediaWiki\\Extension\\ArticleScores\\Parser\\ArticleScoresLinkFlair::render' );
    }

    public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
        $title = $sktemplate->getRelevantTitle();

        if( ArticleScores::canTitleHaveArticleScore( $title ) ) {
            $request = $sktemplate->getRequest();

            $links[ 'actions' ][ 'score' ] = [
                'class' => $request->getVal( 'action' ) === 'score' ? 'selected' : false,
                'text' => wfMessage( 'articlescores-action' )->text(),
                'href' => $title->getLocalURL( 'action=score' )
            ];
        }
    }
}