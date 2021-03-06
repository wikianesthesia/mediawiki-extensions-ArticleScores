<?php

namespace MediaWiki\Extension\ArticleScores\Pager;

use IContextSource;
use IDatabase;
use MediaWiki\Extension\ArticleScores\Submetric;
use TablePager;
use Title;

class ArticleScoresTablePager extends TablePager {

    protected $options;
    protected $submetric;
    protected $userId;

    public function __construct(
        Submetric $submetric,
        int $userId = 0,
        array $options = [
            'limit' => 10,
            'value' => true,
            'timestamp' => true
        ],
        IContextSource $context = null,
        IDatabase $readDb = null
    ) {
        if ( $readDb !== null ) {
            $this->mDb = $readDb;
        }

        $this->mIndexField = 'page_title';

        $this->submetric = $submetric;
        $this->userId = $userId;
        $this->options = $options;

        $this->setLimit( $this->options[ 'limit' ] );

        $this->mDefaultDirection = true;

        parent::__construct( $context );
    }

    /**
     * @inheritDoc
     */
    public function getQueryInfo() {
        $queryInfo = [
            'tables' => [
                'articlescores_scores',
                'page'
            ],
            'fields' => [
                'page_title',
                'value',
                'timestamp'
            ],
            'conds' => [
                'metric_id' => $this->submetric->getMetric()->getId(),
                'submetric_id' => $this->submetric->getId()
            ],
            'options' => [
                'ORDER BY' => 'value DESC, page_title ASC'
            ],
            'join_conds' => [
                'page' => [
                    'JOIN',
                    'articlescores_scores.page_id = page.page_id'
                ]
            ]
        ];

        if( $this->userId ) {
            $queryInfo[ 'conds' ][ 'user_id' ] = $this->userId;
        }

        return $queryInfo;
    }

    /**
     * @inheritDoc
     */
    protected function isFieldSortable( $field ) {
        $sortable_fields = [ 'page_title', 'value', 'timestamp' ];

        return in_array( $field, $sortable_fields );
    }

    public function formatRow( $row ) {
        $title = Title::newFromText( $row->page_title );

        if( !$title || !$title->exists() ) {
            return '';
        } else {
            return parent::formatRow( $row );
        }
    }

    /**
     * @inheritDoc
     */
    public function formatValue( $name, $value ) {
        $formatted = $value;

        $language = $this->getLanguage();

        if( $name === 'page_title' ) {
            // Value is an article Id
            $title = Title::newFromText( $value );
            $formatted = $this->getLinkRenderer()->makeKnownLink( $title );
        } elseif( $name === 'value' ) {
            $iconHtml = $this->submetric->getValueDefinition()->getValueIconHtml( $value );
            $formatted = $iconHtml ?: $this->submetric->getValueDefinition()->getValueString( $value );
        } elseif( $name === 'timestamp' ) {
            $formatted = htmlspecialchars(
                $language->userTimeAndDate( $value, $this->getUser() )
            );
        }

        return $formatted;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultSort() {
        return 'value';
    }

    /**
     * @inheritDoc
     */
    protected function getFieldNames() {
        $fieldNames = [
            'page_title' => $this->msg( 'articlescores-article' )->escaped()
        ];

        if( isset( $this->options[ 'value' ] ) && $this->options[ 'value' ] ) {
            $fieldNames[ 'value' ] = $this->submetric->getName();
        }

        if( isset( $this->options[ 'timestamp' ] ) && $this->options[ 'timestamp' ] ) {
            $fieldNames[ 'timestamp' ] = wfMessage( 'articlescores-timestamp' )->escaped();
        }

        return $fieldNames;
    }
}