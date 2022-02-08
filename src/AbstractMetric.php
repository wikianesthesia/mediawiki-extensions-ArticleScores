<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\JsonSchemaClasses\AbstractJsonSchemaClass;
use MediaWiki\MediaWikiServices;
use MWTimestamp;
use RequestContext;
use Status;
use Title;
use User;

abstract class AbstractMetric extends AbstractJsonSchemaClass {
    /**
     * @var Submetric[]
     */
    protected $submetrics = [];

    /**
     * @param Title $title
     * @return array
     */
    public function getArticleScoreValues( Title $title ): array {
        $values = [];
        $dbValues = $this->getScoreValuesFromQueryInfo( $this->getArticleScoreQueryInfo( $title ) );

        foreach( $this->getSubmetrics() as $submetricId => $submetric ) {
            if( $submetric->perUser ) {
                continue;
            }

            $values[ $submetricId ] = $dbValues[ $submetricId ] ?? $submetric->defaultValue;
        }

        return $values;
    }

    /**
     * @param Title $title
     * @param User $user
     * @return array
     */
    public function getArticleUserScoreValues( Title $title, User $user ): array {
        $values = [];
        $dbValues = $this->getScoreValuesFromQueryInfo( $this->getArticleUserScoreQueryInfo( $title, $user ) );

        foreach( $this->getSubmetrics() as $submetricId => $submetric ) {
            if( !$submetric->perUser ) {
                continue;
            }

            $values[ $submetricId ] = $dbValues[ $submetricId ] ?? $submetric->defaultValue;
        }

        return $values;
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

    /**
     * @param string $submetricId
     * @return bool
     */
    public function hasSubmetric( string $submetricId ): bool {
        return isset( $this->submetrics[ $submetricId ] );
    }

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

        if( !is_null( $submetric->maxValue ) ) {
            if( $value > $submetric->maxValue ) {
                return false;
            }
        }

        if( !is_null( $submetric->minValue ) ) {
            if( $value < $submetric->minValue ) {
                return false;
            }
        }

        if( !is_null( $submetric->maxValue ) && !is_null( $submetric->minValue ) && !is_null( $submetric->stepValue ) ) {
            if( !in_array( $value, range( $submetric->minValue, $submetric->maxValue, $submetric->stepValue ) ) ) {
                return false;
            }
        }

        return true;
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

        if( $submetric->derivedValue ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-derivedvalueerror',
                $this->getId(),
                $submetricId
            );

            return $result;
        }

        $result = $this->doSetArticleScoreValue( $title, $value, $submetricId );

        $this->updateDerivedValues( $title );

        ArticleScore::clearCache( $title );

        return $result;
    }

    /**
     * @param Title $title
     * @param string $submetricId
     * @return bool
     */
    public function userCanSetArticleScore( Title $title, string $submetricId = ArticleScores::DEFAULT_SUBMETRIC ): bool {
        $submetric = $this->getSubmetric( $submetricId );

        if( !$submetric ) {
            return false;
        }

        $user = RequestContext::getMain()->getUser();
        $permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

        // Prevent setting score for pages the user cannot read
        if( !$permissionManager->userCan( 'read', $user, $title ) ) {
            return false;
        }

        // If a right is defined, make sure the user has that right. Otherwise allow.
        if( $submetric->requiresRight ) {
            return $permissionManager->userHasRight(
                $user,
                $submetric->requiresRight
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
        } elseif( !$this->userCanSetArticleScore( $title, $submetricId ) ) {
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

        $submetric = $this->getSubmetric( $submetricId );

        $row = [
            'page_id' => $title->getArticleID(),
            'metric_id' => $this->getId(),
            'submetric' => $submetricId,
            'userscore' => $submetric->perUser,
            'user_id' => $user->getId()
        ];

        // If the metric does not keep a history of score changes, remove old rows from the database
        if( !$submetric->keepHistory ) {
            $deleteConds = $row;

            if( !$submetric->perUser ) {
                // If the score is not per user, remove the user_id condition
                unset( $deleteConds[ 'user_id' ] );
            }

            // TODO error handling
            $db->delete(
                'articlescores_scores',
                $deleteConds,
                __METHOD__
            );
        }

        $row[ 'value' ] = $value;
        $row[ 'timestamp' ] = MWTimestamp::now();

        $db->insert(
            'articlescores_scores',
            $row,
            __METHOD__
        );

        if( $db->affectedRows() !== 1 ) {
            $result->fatal(
                ArticleScores::getExtensionName() . '-databaseerror',
                $db->lastError()
            );

            return $result;
        }

        return $result;
    }

    /**
     * @param Title $title
     * @return array|null
     */
    protected function getArticleScoreQueryInfo( Title $title ): ?array {
        if( !$title->getArticleID() ) {
            return null;
        }

        return [
            'tables' => 'articlescores_scores',
            'vars' => [
                'submetric',
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
    }

    /**
     * @param Title $title
     * @param User $user
     * @return array|null
     */
    protected function getArticleUserScoreQueryInfo( Title $title, User $user ): ?array {
        $articleScoreQueryInfo = $this->getArticleScoreQueryInfo( $title );

        if( !is_array( $articleScoreQueryInfo ) || !$user->isRegistered() ) {
            return null;
        }

        $articleScoreQueryInfo[ 'conds' ][ 'userscore' ] = 1;
        $articleScoreQueryInfo[ 'conds' ][ 'user_id' ] = $user->getId();

        return $articleScoreQueryInfo;
    }

    /**
     * @return string
     */
    protected function getSchemaClass(): string {
        return MetricSchema::class;
    }

    /**
     * @param array $queryInfo
     * @return array
     */
    protected function getScoreValuesFromQueryInfo( array $queryInfo ): array {
        $values = [];

        $db = ArticleScores::getDB();

        var_dump( $db->selectSQLText(
            $queryInfo[ 'tables' ],
            $queryInfo[ 'vars' ],
            $queryInfo[ 'conds' ],
            __METHOD__,
            $queryInfo[ 'options' ],
            $queryInfo[ 'join_conds']
        ) );

        $res = $db->select(
            $queryInfo[ 'tables' ],
            $queryInfo[ 'vars' ],
            $queryInfo[ 'conds' ],
            __METHOD__,
            $queryInfo[ 'options' ],
            $queryInfo[ 'join_conds']
        );

        foreach( $res as $row ) {
            if( isset( $values[ $row->submetric ] ) ) {
                // The most recent (i.e. relevant) value for each submetric will be returned first due to ORDER BY
                // TODO should be able to use some sort of subquery with GROUP BY to avoid returning old rows
                continue;
            }

            $values[ $row->submetric ] = $row->value;
        }

        return $values;
    }

    protected function getUpdatedDerivedValue( Title $title, string $submetricId ) {
        return false;
    }


    /**
     * @param array $definition
     */
    protected function postprocessDefinition( array &$definition ) {
        // Create a Submetric instance for each submetric
        foreach( $definition[ 'score' ] as $submetricId => $submetricDefinition ) {
            $this->submetrics[ $submetricId ] = new Submetric( $submetricDefinition );
        }
    }

    protected function updateDerivedValues( Title $title ) {
        $submetrics = $this->getSubmetrics();

        foreach( $submetrics as $submetricId => $submetric ) {
            if( $submetric->derivedValue ) {
                $updatedValue = $this->getUpdatedDerivedValue( $title, $submetricId );

                if( $updatedValue !== false ) {
                    $this->doSetArticleScoreValue( $title, $updatedValue, $submetricId );
                }
            }
        }
    }
}