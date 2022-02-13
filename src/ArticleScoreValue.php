<?php

namespace MediaWiki\Extension\ArticleScores;

class ArticleScoreValue {
    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $icon = '';

    /**
     * @var string
     */
    public $iconColor = '';

    /**
     * @var int
     */
    public $isSet = 0;

    /**
     * @var int
     */
    public $isUserscore = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var
     */
    public $value;


    /**
     * @param $value
     * @param bool $isSet
     * @param bool $isUserscore
     */
    public function __construct( Submetric $submetric, $value, bool $isSet = false  ) {
        $this->value = $value;
        $this->isSet = (int) $isSet;

        $this->isUserscore = (int) $submetric->isPerUser();

        if( $submetric->getValueDefinition()->hasOptions() ) {
            $option = $submetric->getValueDefinition()->getOption( $value );

            if( $option ) {
                $this->description = $option->getDescription();
                $this->icon = $option->getIcon();
                $this->iconColor = $option->getIconColor();
                $this->name = $option->getName();
            }
        }
    }
}