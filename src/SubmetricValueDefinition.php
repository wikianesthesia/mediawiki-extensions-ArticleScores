<?php

namespace MediaWiki\Extension\ArticleScores;

class SubmetricValueDefinition {
    /**
     * @var mixed|null
     */
    protected $default;

    /**
     * @var bool
     */
    protected $derived;

    /**
     * @var mixed|null
     */
    protected $max;

    /**
     * @var mixed|null
     */
    protected $min;

    /**
     * @var mixed|null
     */
    protected $options;

    /**
     * @var mixed|null
     */
    protected $step;

    /**
     * @var mixed|null
     */
    protected $unset;

    /**
     * @param array $definition
     */
    public function __construct( array $definition, Submetric $submetric ) {
        $this->_submetric = $submetric;

        $this->default = $definition[ 'default' ] ?? null;
        $this->derived = (bool) ( $definition[ 'derived' ] ?? null );
        $this->max = $definition[ 'max' ] ?? null;
        $this->min = $definition[ 'min' ] ?? null;
        $this->step = $definition[ 'step' ] ?? null;
        $this->unset = $definition[ 'unset' ] ?? null;

        if( isset( $definition[ 'options' ] ) ) {
            $this->options = [];

            foreach( $definition[ 'options' ] as $value => $optionDefinition ) {
                $optionDefinition[ 'value' ] = $value;

                if( isset( $optionDefinition[ 'icon' ] ) && !isset( $optionDefinition[ 'iconColor' ] ) ) {
                    $optionDefinition[ 'iconColor' ] = $submetric->getMetric()->getConfig( 'IconColor' );
                }

                $this->options[ $value ] = new SubmetricValueOption( $optionDefinition, $this );
            }
        }
    }

    /**
     * @return mixed|null
     */
    public function getDefault() {
        return $this->default;
    }

    /**
     * @return mixed|null
     */
    public function getMax() {
        return $this->max;
    }

    /**
     * @return AbstractMetric
     */
    public function getMetric(): AbstractMetric {
        return $this->getSubmetric()->getMetric();
    }

    /**
     * @return mixed|null
     */
    public function getMin() {
        return $this->min;
    }

    /**
     * @return SubmetricValueOption|null
     */
    public function getOption( string $value ): ?SubmetricValueOption {
        return $this->options[ $value ] ?? null;
    }

    /**
     * @return SubmetricValueOption[]|null
     */
    public function getOptions(): ?array {
        return $this->options;
    }

    /**
     * @return array|null
     */
    public function getRange(): ?array {
        return $this->hasRange() ?
            range( $this->getMin(), $this->getMax(), $this->getStep() ) :
            null;
    }

    /**
     * @return mixed|null
     */
    public function getStep() {
        return $this->step;
    }

    /**
     * @return Submetric
     */
    public function getSubmetric(): Submetric {
        return $this->_submetric;
    }

    /**
     * @return mixed|null
     */
    public function getUnset() {
        return $this->unset;
    }

    /**
     * @param $value
     * @return string
     */
    public function getValueIconHtml( $value, string $addClasses = '' ): string {
        return $this->hasOptions() ?
            $this->getOption( $value )->getIconHtml( $addClasses ) :
            '';
    }

    /**
     * @param $value
     * @return string
     */
    public function getValueString( $value ): string {
        return $this->hasOptions() ?
            $this->getOption( $value )->getName() :
            (string) $value;
    }

    /**
     * @return bool
     */
    public function hasOptions(): bool {
        return !is_null( $this->options );
    }

    /**
     * @return bool
     */
    public function hasRange(): bool {
        return !is_null( $this->getMax() ) && !is_null( $this->getMin() ) && !is_null( $this->getStep() );
    }

    /**
     * @return bool
     */
    public function isDerived(): bool {
        return $this->derived;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isValueValid( $value ): bool {
        if( $this->hasOptions() ) {
            if( !in_array( $value, array_keys( $this->options ) ) ) {
                return false;
            }
        }

        if( !is_null( $this->getMax() ) ) {
            if( $value > $this->getMax() ) {
                return false;
            }
        }

        if( !is_null( $this->getMin() ) ) {
            if( $value < $this->getMin() ) {
                return false;
            }
        }

        $range = $this->getRange();

        // If the submetric has a limited range of valid values, make sure value exists in range
        if( $range && !in_array( $value, $range ) ) {
            return false;
        }

        return true;
    }
}