( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores.likes = {
        dislikeValue: '-1',
        likeValue: '1',
        initialize: function() {
            // Don't bother setting anything up if there isn't any user input
            if( !$( '.articlescores-likes-button-like' ).length ) {
                return;
            }

            $( '.articlescores-likes-button-like' ).click( function () {
                // If already liked, set value to 0, otherwise set to 1.
                var newValue = $( '.articlescores-likes-value-user' ).val() === mw.articleScores.likes.likeValue ? 0 : 1;

                var callback = function( response ) {
                    if( response.status === 'ok' ) {
                        mw.articleScores.likes.updateValues();
                    }
                };

                mw.articleScores.common.setScore(
                    'Likes',
                    'user',
                    newValue,
                    callback
                );
            } );

            // Dislike button may or may not exist depending on configuration
            $( '.articlescores-likes-button-dislike' ).click( function () {
                // If already liked, set value to 0, otherwise set to 1.
                var newValue = $( '.articlescores-likes-value-user' ).val() === mw.articleScores.likes.dislikeValue ? 0 : -1;

                var callback = function( response ) {
                    if( response.status === 'ok' ) {
                        mw.articleScores.likes.updateValues();
                    }
                };

                mw.articleScores.common.setScore(
                    'Likes',
                    'user',
                    newValue,
                    callback
                );
            } );

            mw.articleScores.likes.renderValue();
        },
        renderValue: function() {
            $( '.articlescores-likes-value-main' ).html( $( '.articlescores-likes-value-likes' ).val() );

            var userValue = $( '.articlescores-likes-value-user' ).val();

            var likeIcon = $( '<i>', {
                'class': ( userValue === mw.articleScores.likes.likeValue ? 'fas' : 'far' ) + ' fa-thumbs-up fa-fw'
            } );

            var likeLabel = mw.msg( 'articlescores-likes-' +
                ( userValue === mw.articleScores.likes.likeValue ? 'liked' : 'like' ) );

            $( '.articlescores-likes-button-like' ).empty().append( likeIcon, likeLabel );

            if( $( '.articlescores-likes-button-dislike' ).length ) {
                $( '.articlescores-likes-value-main' ).append(
                    ' (' + $( '.articlescores-likes-value-percentLikes' ).val() + '%)'
                );

                var dislikeIcon = $( '<i>', {
                    'class': ( userValue === mw.articleScores.likes.dislikeValue ? 'fas' : 'far' ) + ' fa-thumbs-down fa-fw'
                } );

                var dislikeLabel = mw.msg( 'articlescores-likes-' +
                    ( userValue === mw.articleScores.likes.dislikeValue ? 'disliked' : 'dislike' ) );

                $( '.articlescores-likes-button-dislike' ).empty().append( dislikeIcon, dislikeLabel );
            }
        },
        updateValues: function() {
            var pageId = mw.config.get( 'wgArticleId' );

            var callback = function( response ) {
                if( response.status === 'ok' ) {
                    for( var submetricId in response.result[ pageId ].Likes ) {
                        $( '.articlescores-likes-value-' + submetricId ).val( response.result[ pageId ].Likes[ submetricId ].value );
                    }

                    mw.articleScores.likes.renderValue();
                }
            };

            mw.articleScores.common.getScoresForPageIds( pageId, callback );
        }
    };

    mw.articleScores.likes.initialize();
}() );