<?php

namespace MediaWiki\Extension\ArticleScores;

class Submetric {
    /**
     * @var mixed|null
     */
    public $defaultValue;

    /**
     * @var bool
     */
    public $derivedValue;

    /**
     * @var bool
     */
    public $keepHistory;

    /**
     * @var mixed|null
     */
    public $maxValue;

    /**
     * @var mixed|null
     */
    public $minValue;

    /**
     * @var bool
     */
    public $perUser;

    /**
     * @var mixed|null
     */
    public $stepValue;

    /**
     * @var mixed|null
     */
    public $requiresRight;


    public function __construct( array $definition ) {
        $this->defaultValue = $definition[ 'DefaultValue' ] ?? null;
        $this->derivedValue = (bool) ( $definition[ 'DerivedValue' ] ?? false );
        $this->keepHistory = (bool) ( $definition[ 'KeepHistory' ] ?? true );
        $this->maxValue = $definition[ 'MaxValue' ] ?? null;
        $this->minValue = $definition[ 'MinValue' ] ?? null;
        $this->perUser = (bool) ( $definition[ 'PerUser' ] ?? false );
        $this->stepValue = $definition[ 'StepValue' ] ?? null;
        $this->requiresRight = $definition[ 'RequiresRight' ] ?? null;
    }
}