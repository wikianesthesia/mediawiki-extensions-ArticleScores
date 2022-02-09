<?php

namespace MediaWiki\Extension\ArticleScores;

class ArticleScoreValue {
    /**
     * @var int
     */
    public $isSet;

    /**
     * @var int
     */
    public $isUserscore;

    /**
     * @var
     */
    public $value;

    /**
     * @param $value
     * @param bool $isSet
     * @param bool $isUserscore
     */
    public function __construct( $value, bool $isSet = false, bool $isUserscore = false ) {
        $this->value = $value;
        $this->isSet = (int) $isSet;
        $this->isUserscore = (int) $isUserscore;
    }
}