<?php

namespace MediaWiki\Extension\ArticleScores;

class Submetric {
    /**
     * @var AbstractMetric
     */
    protected $_metric;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $logEvents;

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

        $this->logEvents = (bool) ( $definition[ 'LogEvents' ] ?? true );
        $this->perUser = (bool) ( $definition[ 'PerUser' ] ?? null );
        $this->requiresRight = $definition[ 'RequiresRight' ] ?? null;

        $this->valueDefinition = new SubmetricValueDefinition( $definition[ 'value' ] ?? [], $this );
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