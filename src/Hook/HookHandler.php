<?php

namespace MediaWiki\Extension\ArticleScores\Hook;

use HtmlArmor;
use Html;
use MediaWiki\Extension\ArticleScores\ArticleScores;
use MediaWiki\Extension\ArticleScores\MetricSchema;
use MediaWiki\Extension\JsonClasses\Hook\JsonClassRegistrationHook;
use MediaWiki\Hook\BeforeInitializeHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MediaWiki\Linker\Hook\HtmlPageLinkRendererEndHook;
use Title;

class HookHandler implements
    BeforeInitializeHook,
    HtmlPageLinkRendererEndHook,
    JsonClassRegistrationHook,
    LoadExtensionSchemaUpdatesHook,
    ParserFirstCallInitHook,
    SidebarBeforeOutputHook,
    SkinTemplateNavigation__UniversalHook {
    /**
     * @inheritDoc
     */
    public function onBeforeInitialize( $title, $unused, $output, $user, $request, $mediaWiki ) {
        global $wgArticleScoresLinkFlairTitles;

        if( in_array( $title->getFullText(), $wgArticleScoresLinkFlairTitles ) ) {
            ArticleScores::setUseLinkFlair( true );
        }
    }

    /**
     * @inheritDoc
     */
    public function onHtmlPageLinkRendererEnd( $linkRenderer, $target, $isKnown, &$text, &$attribs, &$ret ) {
        if( !ArticleScores::getUseLinkFlair() || !$isKnown ) {
            return;
        }

        $title = Title::newFromLinkTarget( $target );

        if( !ArticleScores::canTitleHaveArticleScore( $title ) ) {
            return;
        }

        $text = new HtmlArmor(
            HtmlArmor::getHtml( $text ) .
            Html::rawElement( 'span', [
                    'class' => 'articlescores-linkflair',
                    'data-pageid' => $title->getArticleID()
                ]
            )
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function onJsonClassRegistration( $classManager ) {
        $classManager->registerSchema( MetricSchema::class );
        $classManager->loadClassDirectory( MetricSchema::class, ArticleScores::getMetricsLocalDirectory(), true );
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function onParserFirstCallInit( $parser ) {
        $parser->setHook( 'articlescores', 'MediaWiki\\Extension\\ArticleScores\\Parser\\ArticleScores::render' );
        $parser->setHook( 'articlescoreslinkflair', 'MediaWiki\\Extension\\ArticleScores\\Parser\\ArticleScoresLinkFlair::render' );
    }

    /**
     * @inheritDoc
     */
    public function onSidebarBeforeOutput( $skin, &$sidebar ): void {
        global $wgArticleScoresTopArticlesDefaultMetric;

        $target = 'ArticleScores';

        if( $wgArticleScoresTopArticlesDefaultMetric ) {
            $target .= '/' . $wgArticleScoresTopArticlesDefaultMetric;
        }

        $sidebar[ 'TOOLBOX' ][ 'articlescores' ] = [
            'text' => wfMessage( 'articlescores-toparticles' )->text(),
            'href' => $skin::makeSpecialUrl( $target )
        ];
    }

    /**
     * @inheritDoc
     */
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