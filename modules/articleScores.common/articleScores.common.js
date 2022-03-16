( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores = mw.articleScores || {};

    mw.articleScores.common = {
        linkFlairScores: {},
        scoreInfo: {},
        getScores: function( apiParams, callback ) {
            var asaction = 'getscores';

            apiParams = Object.assign( apiParams, {
                'action': 'articlescores',
                'asaction': asaction
            } );

            new mw.Api().get( apiParams ).then( function( response ) {
                if( callback ) {
                    callback( response.articlescores[ asaction ] );
                }
            } ).fail( function( a, b, c ) {
                console.log( b );
            } );
        },
        getScoresForPageIds: function( pageIds, callback ) {
            if( !Array.isArray( pageIds ) ) {
                pageIds = [ pageIds ];
            }

            mw.articleScores.common.getScores( {
                'pageids': pageIds.join( '|' )
            }, callback );
        },
        getScoresForTitles: function( titles, callback ) {
            if( !Array.isArray( titles ) ) {
                titles = [ titles ];
            }

            mw.articleScores.common.getScores( {
                'titles': titles.join( '|' )
            }, callback );
        },
        loadLinkFlairScores: function() {
            var pageIds = [];

            $( '.articlescores-linkflair' ).each( function() {
                pageIds.push( $( this ).attr( 'data-pageid' ) );
            } );

            // Only query if there are any link flair tags
            if( pageIds.length ) {
                mw.articleScores.common.getScoresForPageIds( pageIds, function( response ) {
                    if( response.status === 'ok' ) {
                        mw.articleScores.common.linkFlairScores = response.result;

                        mw.hook( 'articleScores.loadLinkFlairScores' ).fire();
                    }
                } );
            }
        },
        loadScoreInfo: function() {
            // Only load score info if a user is logged in and at least one articlescore input exists
            if( mw.config.get( 'wgUserId' ) && $( '.articlescores-input' ).length ) {
                var asaction = 'getscoreinfo';
                var pageid = mw.config.get( 'wgArticleId' );

                var apiParams = {
                    'action': 'articlescores',
                    'asaction': asaction,
                    'pageid': pageid
                };

                new mw.Api().get( apiParams ).then( function( response ) {
                    if( response.articlescores[ asaction ].status === 'ok' ) {
                        mw.articleScores.common.scoreInfo = response.articlescores[ asaction ].result;

                        mw.hook( 'articleScores.loadScoreInfo' ).fire();
                    }
                } ).fail( function( a, b, c ) {
                    console.log( b );
                } );
            }
        },
        initialize: function() {
            mw.articleScores.common.loadLinkFlairScores();
            mw.articleScores.common.loadScoreInfo();
        },
        setScore: function( metricId, submetricId, value, callback ) {
            var asaction = 'setscore';
            var pageId = mw.config.get( 'wgArticleId' );

            if( !pageId ) {
                return;
            }

            var apiParams = {
                'action': 'articlescores',
                'asaction': asaction,
                'pageid': pageId,
                'metricid': metricId,
                'submetricid': submetricId,
                'value': value
            };

            new mw.Api().postWithEditToken( apiParams ).then( function( response ) {
                if( callback ) {
                    callback( response.articlescores[ asaction ] );
                }
            } ).fail( function( a, b, c ) {
                console.log( b );
            } );
        }
    };

    mw.articleScores.common.initialize();
}() );