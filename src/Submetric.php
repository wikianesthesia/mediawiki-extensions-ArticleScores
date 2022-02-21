<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\Extension\ArticleScores\ArticleScores;

class Submetric {
    /**
     * @var AbstractMetric
     */
    protected $_metric;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $descriptionmsg;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $logEvents;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $namemsg;

    /**
     * @var bool
     */
    protected $perUser;

    /**
     * @var string|null
     */
    protected $requiresRight;

    /**
     * @var SubmetricValueDefinition
     */
    protected $valueDefinition;

    /**
     * @param array $definition
     */
    public function __construct( array $definition, AbstractMetric $metric) {
        $this->_metric = $metric;

        $this->id = $definition[ 'id' ];

        $this->description = $definition[ 'description' ] ?? null;
        $this->descriptionmsg = $definition[ 'descriptionmsg' ] ?? null;
        $this->logEvents = (bool) ( $definition[ 'LogEvents' ] ?? true );
        $this->name = $definition[ 'name' ] ?? null;
        $this->namemsg = $definition[ 'namemsg' ] ?? null;
        $this->perUser = (bool) ( $definition[ 'PerUser' ] ?? null );
        $this->requiresRight = $definition[ 'RequiresRight' ] ?? null;

        $this->valueDefinition = new SubmetricValueDefinition( $definition[ 'value' ] ?? [], $this );
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        if( is_null( $this->description ) ) {
            if( $this->descriptionmsg ) {
                $this->description = wfMessage( $this->descriptionmsg )->text();
            } else {
                $defaultMsg = wfMessage( $this->getMsgKeyPrefix() . '-desc' );

                if( $defaultMsg->exists() ) {
                    $this->description = $defaultMsg->text();
                } else {
                    $this->description = '';
                }
            }
        }

        return $this->description;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return AbstractMetric
     */
    public function getMetric(): AbstractMetric {
        return $this->_metric;
    }

    /**
     * @return string
     */
    public function getMsgKeyPrefix(): string {
        return $this->getMetric()->getMsgKeyPrefix() .
            '-' .
            $this->getId();
    }

    /**
     * @return string
     */
    public function getName(): string {
        if( is_null( $this->name ) ) {
            if( $this->namemsg ) {
                $this->name = wfMessage( $this->namemsg )->text();
            } else {
                $defaultMsg = wfMessage( $this->getMsgKeyPrefix() . '-name' );

                if( $defaultMsg->exists() ) {
                    $this->name = $defaultMsg->text();
                } else {
                    $this->name = $this->getMetric()->getName();

                    if( $this->getId() !== ArticleScores::DEFAULT_SUBMETRIC ) {
                        $this->name .= ' ' . wfMessage( 'parentheses', $this->getId() )->text();
                    }
                }
            }
        }

        return $this->name;
    }

    /**
     * @return SubmetricValueDefinition
     */
    public function getValueDefinition(): SubmetricValueDefinition {
        return $this->valueDefinition;
    }

    /**
     * @return bool
     */
    public function isPerUser(): bool {
        return $this->perUser;
    }

    /**
     * @return bool
     */
    public function logEvents(): bool {
        return $this->logEvents;
    }

    /**
     * @return string|null
     */
    public function requiresRight(): ?string {
        return $this->requiresRight;
    }
}