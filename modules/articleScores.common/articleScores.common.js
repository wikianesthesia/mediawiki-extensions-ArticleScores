( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores = mw.articleScores || {};

    mw.articleScores.common = {
        getScores: function( apiParams, callback ) {
            var asaction = 'getscores';

            apiParams = Object.assign( apiParams, {
                action: 'articlescores',
                asaction: asaction
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
                pageids: pageIds.join( '|' )
            }, callback );
        },
        getScoresForTitles: function( titles, callback ) {
            if( !Array.isArray( titles ) ) {
                titles = [ titles ];
            }

            mw.articleScores.common.getScores( {
                titles: titles.join( '|' )
            }, callback );
        },
        getLinkFlairScores: function() {
            var pageIds = [];

            $( '.articlescores-linkflair' ).each( function() {
                pageIds.push( $( this ).attr( 'data-pageid' ) );
            } );

            if( pageIds.length ) {
                mw.articleScores.common.getScoresForPageIds( pageIds, function( response ) {
                    if( response.status === 'ok' ) {
                        mw.hook( 'articleScores.getLinkFlairScores' ).fire( response.result );
                    }
                } );
            }
        },
        initialize: function() {
            mw.articleScores.common.getLinkFlairScores();
        },
        setScore: function( metricId, submetricId, value, callback ) {
            var asaction = 'setscore';
            var pageId = mw.config.get( 'wgArticleId' );

            if( !pageId ) {
                return;
            }

            var apiParams = {
                action: 'articlescores',
                asaction: asaction,
                pageid: pageId,
                metricid: metricId,
                submetricid: submetricId,
                value: value
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