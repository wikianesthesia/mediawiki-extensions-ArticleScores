<?php

namespace MediaWiki\Extension\ArticleScores;

use Html;

class SubmetricValueOption {
    protected $_valueDefinition;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $descriptionmsg;

    /**
     * @var string|null
     */
    protected $icon;

    /**
     * @var string|null
     */
    protected $iconColor;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $namemsg;

    /**
     * @var mixed
     */
    protected $value;

    public function __construct( array $definition, SubmetricValueDefinition $valueDefinition) {
        $this->_valueDefinition = $valueDefinition;

        $this->description = $definition[ 'description' ] ?? null;
        $this->descriptionmsg = $definition[ 'descriptionmsg' ] ?? null;
        $this->icon = $definition[ 'icon' ] ?? null;
        $this->iconColor = $definition[ 'iconColor' ] ?? null;
        $this->name = $definition[ 'name' ] ?? null;
        $this->namemsg = $definition[ 'namemsg' ] ?? null;

        $this->value = $definition[ 'value' ];
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
     * @return mixed
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * @return mixed
     */
    public function getIconColor() {
        return $this->iconColor;
    }

    /**
     * @return mixed
     */
    public function getIconHtml( string $addClasses = '' ) {
        $html = '';

        $icon = $this->getIcon();
        if( $icon ) {
            $attribs = [
                'class' => $icon
            ];

            $attribs[ 'class' ] .= $addClasses ? ' ' . $addClasses : '';

            $iconColor = $this->getIconColor();
            if( $iconColor ) {
                $attribs[ 'style' ] = 'color: ' . $iconColor;
            }

            $html .= Html::rawElement( 'i', $attribs );
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getMsgKeyPrefix(): string {
        return $this->getValueDefinition()->getSubmetric()->getMsgKeyPrefix() .
            '-' .
            $this->value;
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
                    $this->name = $this->value;
                }
            }
        }

        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return SubmetricValueDefinition
     */
    public function getValueDefinition(): SubmetricValueDefinition {
        return $this->_valueDefinition;
    }
}