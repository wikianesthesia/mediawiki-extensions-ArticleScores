<?php

namespace MediaWiki\Extension\ArticleScores;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\DBConnRef;

class ArticleScores {

    /**
     * @param int $i
     * @return DBConnRef
     */
    public static function getDB( $i = DB_REPLICA ): DBConnRef {
        $lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
        return $lb->getConnectionRef( $i );
    }

    public static function initialize() {

    }
}