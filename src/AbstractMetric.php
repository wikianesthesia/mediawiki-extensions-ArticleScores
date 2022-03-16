<?php

namespace MediaWiki\Extension\ArticleScores;

use Database;
use ManualLogEntry;
use MediaWiki\Extension\JsonClasses\AbstractJsonClass;
use MediaWiki\MediaWikiServices;
use MWTimestamp;
use Parser;
use RequestContext;
use Status;
use Title;
use User;

abstract class AbstractMetric extends AbstractJsonClass {
    /**
     * @var Submetric[]
     */
    protected $submetrics = [];

    protected $titleScoreValues = [];
    protected $userScoreValues = [];

    public function addResourceLoaderModules( Parser $parser ): void {}

    /**
     * @param Title $title
     * @param bool $includeLabel
     * @param bool $includeInput
     * @return string
     */
    abstract public function getArticleScoreHtml( Title $title, bool $includeLabel = true, bool $includeInput = true ): string;

    /**
     * @param Title $title
     * @param string $submetricId
     * @return ArticleScoreValue|null
     */
    public function getArticleScoreValue( Title $title, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): ?ArticleScoreValue {
        $articleScoreValues = $this->getArticleScoreValues( $title, true, true );

        return $articleScoreValues[ $submetricId ] ?? null;
    }

    /**
     * @param Title $title
     * @return ArticleScoreValue[]
     */
    public function getArticleScoreValues( Title $title, bool $includeUserscores = false, bool $includeDefaultValues = false ): array {
        $articleScoreValues = [];

        if( !$title->exists() ) {
            return $articleScoreValues;
        }

        $titleId = $title->getArticleID();

        $db = ArticleScores::getDB();

        $queryInfo = [
            'tables' => 'articlescores_scores',
            'vars' => [
                'submetric_id',
                'value'
            ],
            'conds' => [
                'page_id' => $title->getArticleID(),
                'metric_id' => $this->getId(),
                'userscore' => 0
            ],
            'options' => [
                'ORDER BY' => 'timestamp DESC'
            ],
            'join_conds' => []
        ];

        if( !isset( $this->titleScoreValues[ $titleId ] ) ) {
            $titleScoresCallback = function( $oldValue, &$ttl, array &$setOpts ) use ( $db, $queryInfo ) {
                $titleScoreValues = [];

                $setOpts += Database::getCacheSetOptions( $db );

                $res = $db->select(
                    $queryInfo[ 'tables' ],
                    $queryInfo[ 'vars' ],
                    $queryInfo[ 'conds' ],
                    __METHOD__,
                    $queryInfo[ 'options' ],
                    $queryInfo[ 'join_conds' ]
                );

                $submetrics = $this->getSubmetrics();

                foreach( $res as $row ) {
                    // The most recent (i.e. relevant) value for each submetric will be returned first due to ORDER BY
                    // Thus, only set this value once and skip subsequent rows. Also only set this value if it is a valid
                    // submetric.
                    // TODO should be able to use some sort of subquery with GROUP BY to avoid returning old rows
                    if( !isset( $titleScoreValues[ $row->submetric_id ] ) && array_key_exists( $row->submetric_id, $submetrics ) ) {
                        $titleScoreValues[ $row->submetric_id ] = new ArticleScoreValue( $submetrics[ $row->submetric_id ], $row->value, true );
                    }
                }

                return $titleScoreValues;
            };

            $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

            $this->titleScoreValues[ $titleId ] = $cache->getWithSetCallback(
                $this->getCacheKey( $title ),
                ArticleScores::CACHE_TTL,
                $titleScoresCallback
            );
        }

        $articleScoreValues = array_merge( $articleScoreValues, $this->titleScoreValues[ $titleId ] );

        $user = RequestContext::getMain()->getUser();

        if( $includeUserscores && $user->isRegistered() ) {
            if( !isset( $this->userScoreValues[ $titleId ] ) ) {
                $this->userScoreValues[ $titleId ] = [];

                unset( $queryInfo[ 'conds' ][ 'userscore' ] );
                $queryInfo[ 'conds' ][] = '(userscore = 1 AND user_id = ' . $db->addQuotes( $user->getId() ) . ')';

                $res = $db->select(
                    $queryInfo[ 'tables' ],
                    $queryInfo[ 'vars' ],
                    $queryInfo[ 'conds' ],
                    __METHOD__,
                    $queryInfo[ 'options' ],
                    $queryInfo[ 'join_conds' ]
                );

                $submetrics = $this->getSubmetrics();

                foreach( $res as $row ) {
                    // The most recent (i.e. relevant) value for each submetric will be returned first due to ORDER BY
                    // Thus, only set this value once and skip subsequent rows. Also only set this value if it is a valid
                    // submetric.
                    // TODO should be able to use some sort of subquery with GROUP BY to avoid returning old rows
                    if( !isset( $titleValues[ $row->submetric_id ] ) && array_key_exists( $row->submetric_id, $submetrics ) ) {
                        $this->userScoreValues[ $titleId ][ $row->submetric_id ] = new ArticleScoreValue( $submetrics[ $row->submetric_id ], $row->value, true );
                    }
                }
            }

            $articleScoreValues = array_merge( $articleScoreValues, $this->userScoreValues[ $titleId ] );
        }

        // Default values for undefined submetrics
        if( $includeDefaultValues ) {
            foreach( $this->getSubmetrics() as $submetricId => $submetric ) {
                if( !isset( $articleScoreValues[ $submetricId] ) ) {
                    $articleScoreValues[ $submetricId ] = new ArticleScoreValue( $submetric, $submetric->getValueDefinition()->getDefault(), false );
                }
            }
        }

        return $articleScoreValues;
    }

    /**
     * @param Title $title
     * @return string
     */
    public function getLinkFlairHtml( Title $title ): string {
        return '';
    }

    /**
     * @return string
     */
    public function getMsgKeyPrefix(): string {
        return strtolower( ArticleScores::getExtensionName() . '-' . $this->getId() );
    }

    /**
     * @param string $submetricId
     * @return Submetric|null
     */
    public function getSubmetric( string $submetricId ): ?Submetric {
        return $this->submetrics[ $submetricId ] ?? null;
    }

    /**
     * @return Submetric[]
     */
    public function getSubmetrics(): array {
        return $this->submetrics;
    }

    public function getTopTitles( string $submetricId, int $limit, int $fromArticleId = 0 ): array {
        // Might need to add an `archived` column to scores table to keep query efficient?
        return [];
    }

    /**
     * @return bool
     */
    public function hasLinkFlair(): bool {
        return false;
    }

    /**
     * @param string $submetricId
     * @return bool
     */
    public function hasSubmetric( string $submetricId ): bool {
        return isset( $this->submetrics[ $submetricId ] );
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool {
        return true;
    }

    /**
     * @param $value
     * @param string $submetricId
     * @return bool
     */
    public function isScoreValueValid( $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): bool {
        $submetric = $this->getSubmetric( $submetricId );

        if( !$submetric ) {
            return false;
        }

        return $submetric->getValueDefinition()->isValueValid( $value );
    }

    /**
     * @param Title $title
     * @param $value
     * @param string $submetricId
     * @return Status
     */
    public function setArticleScoreValue( Title $title, $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): Status {
        $result = Status::newGood();

        if( !$this->hasSubmetric( $submetricId ) ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-nosubmetric',
                $this->getId(),
                $submetricId
            );

            return $result;
        }

        $submetric = $this->getSubmetric( $submetricId );

        if( $submetric->getValueDefinition()->isDerived() ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-derivedvalueerror',
                $this->getId(),
                $submetricId
            );

            return $result;
        }

        $result = $this->doSetArticleScoreValue( $title, $value, $submetricId );

        if( $result->isOK() && $submetric->logEvents() ) {
            $logAction = $this->getLogAction( $value, $submetricId );

            $logEntry = new ManualLogEntry( 'articlescores', $logAction );
            $logEntry->setPerformer( RequestContext::getMain()->getUser() );
            $logEntry->setTarget( $title );

            $logValue = $submetric->getValueDefinition()->getValueString( $value );

            $logParams = [
                '4::metric' => $submetric->getName(),
                '5::value' => $logValue
            ];

            $logEntry->setParameters( $logParams );

            $logId = $logEntry->insert();
            $logEntry->publish( $logId );
        }

        $this->updateDerivedValues( $title );

        $this->purgeTitle( $title );

        return $result;
    }

    /**
     * @param User $user
     * @param Title $title
     * @param string $submetricId
     * @return bool
     */
    public function userCanSetArticleScore( User $user, Title $title, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): bool {
        $submetric = $this->getSubmetric( $submetricId );

        if( !$submetric || $submetric->getValueDefinition()->isDerived() ) {
            return false;
        }

        $permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

        // Prevent setting score for pages the user cannot read
        if( !$permissionManager->userCan( 'read', $user, $title ) ) {
            return false;
        }

        // If a right is defined, make sure the user has that right. Otherwise allow.
        if( $submetric->requiresRight() ) {
            return $permissionManager->userHasRight(
                $user,
                $submetric->requiresRight()
            );
        }

        return true;
    }

    /**
     * @param Title $title
     * @param $value
     * @param string $submetricId
     * @return Status
     */
    protected function doSetArticleScoreValue( Title $title, $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): Status {
        $result = Status::newGood();

        $user = RequestContext::getMain()->getUser();
        $submetric = $this->getSubmetric( $submetricId );
        $valueDefinition = $submetric->getValueDefinition();

        if( !$user->isRegistered() ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-nouser'
            );

            return $result;
        } elseif( !$title->exists() ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-invalidpageid',
                $title->getArticleID()
            );

            return $result;
        } elseif( !$this->hasSubmetric( $submetricId ) ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-nosubmetric',
                $this->getId(),
                $submetricId
            );

            return $result;
        } elseif( !$this->userCanSetArticleScore( $user, $title, $submetricId ) && !$valueDefinition->isDerived() ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-permissiondenied'
            );

            return $result;
        } elseif( !$this->isScoreValueValid( $value, $submetricId ) ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-valuenotvalid',
                $value,
                $this->getId(),
                $submetricId
            );

            return $result;
        }

        $db = ArticleScores::getDB( DB_MASTER );

        $rowConds = [
            'page_id' => $title->getArticleID(),
            'metric_id' => $this->getId(),
            'submetric_id' => $submetricId,
            'user_id' => $user->getId(),
        ];

        $rowValues = [
            'userscore' => $submetric->isPerUser(),
            'value' => $value,
            'timestamp' => MWTimestamp::now()
        ];

        if( !$submetric->isPerUser() ) {
            $rowValues[ 'user_id' ] = $rowConds[ 'user_id' ];
            unset( $rowConds[ 'user_id' ] );
        }

        $db->delete(
            'articlescores_scores',
            $rowConds,
            __METHOD__
        );

        if( is_null( $valueDefinition->getUnset() ) || $value != $valueDefinition->getUnset() ) {
            $db->insert(
                'articlescores_scores',
                array_merge( $rowConds, $rowValues ),
                __METHOD__
            );

            if( $db->affectedRows() !== 1 ) {
                $result->fatal(
                    ArticleScores::getExtensionName() . '-databaseerror',
                    $db->lastError()
                );

                return $result;
            }
        }

        return $result;
    }

    protected function getCacheKey( Title $title ): string {
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

        return $cache->makeKey( static::class, $title->getArticleID() );
    }

    protected function getLogAction( $value, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): string {
        return 'set';
    }

    /**
     * @return string
     */
    protected function getSchemaClass(): string {
        return MetricSchema::class;
    }

    /**
     * @param Title $title
     * @param string $submetricId
     * @return mixed
     */
    protected function getUpdatedDerivedValue( Title $title, string $submetricId ) {
        return false;
    }


    /**
     * @param array $definition
     */
    protected function postprocessDefinition( array &$definition ) {
        // Create a Submetric instance for each submetric
        foreach( $definition[ 'score' ] as $submetricId => $submetricDefinition ) {
            $submetricDefinition[ 'id' ] = $submetricId;

            $this->submetrics[ $submetricId ] = new Submetric( $submetricDefinition, $this );
        }
    }

    protected function purgeTitle( Title $title ) {
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        $cache->delete( $this->getCacheKey( $title ) );

        // Purge this title
        $purgeTitles = [ $title ];

        // Purge any titles that link to this title (in case they use link flair)
        foreach( $title->getLinksTo() as $linkingTitle ) {
            if( $linkingTitle ) {
                $purgeTitles[] = $linkingTitle;
            }
        }

        $logger = ArticleScores::getLogger();
        foreach( $purgeTitles as $purgeTitle ) {
            $logger->debug( $purgeTitle->getFullText() );
            if( $purgeTitle->exists() ) {
                MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $purgeTitle )->doPurge();
            }
        }

        unset( $this->titleScoreValues[ $title->getArticleID() ] );
        unset( $this->userScoreValues[ $title->getArticleID() ] );
    }

    protected function updateDerivedValues( Title $title ) {
        $submetrics = $this->getSubmetrics();

        foreach( $submetrics as $submetricId => $submetric ) {
            if( $submetric->getValueDefinition()->isDerived() ) {
                $updatedDerivedValue = $this->getUpdatedDerivedValue( $title, $submetricId );

                if( $updatedDerivedValue !== false ) {
                    $this->doSetArticleScoreValue( $title, $updatedDerivedValue, $submetricId );
                }
            }
        }
    }
}