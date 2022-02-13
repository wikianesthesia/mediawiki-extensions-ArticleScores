( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores = mw.articleScores || {};

    mw.articleScores.common = {
        getScores: function( callback ) {
            var asaction = 'getscores';
            var pageId = mw.config.get( 'wgArticleId' );

            if( !pageId ) {
                return;
            }

            var apiParams = {
                action: 'articlescores',
                asaction: asaction,
                pageid: pageId
            };

            new mw.Api().get( apiParams ).then( function( response ) {
                if( callback ) {
                    callback( response.articlescores[ asaction ] );
                }
            } ).fail( function( a, b, c ) {
                console.log( b );
            } );
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
}() );