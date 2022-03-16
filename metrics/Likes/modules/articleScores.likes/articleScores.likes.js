( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores.likes = {
        dislikeValue: '-1',
        likeValue: '1',
        initialize: function() {
            mw.hook( 'articleScores.loadScoreInfo' ).add( mw.articleScores.likes.renderInputs );
        },
        renderInputs: function() {
            var $inputElement = $( '.articlescores-likes-input' );

            if( !$inputElement.length ||
                !mw.articleScores.common.scoreInfo.hasOwnProperty( 'EditorRating' ) ||
                !mw.articleScores.common.scoreInfo.Likes.hasOwnProperty( 'user' ) ||
                !mw.articleScores.common.scoreInfo.Likes.user.hasOwnProperty( 'userCanSet' ) ||
                !mw.articleScores.common.scoreInfo.Likes.user.userCanSet ) {
                return;
            }

            var $likeButton = $( '<a>', {
                'class': 'articlescores-likes-like-input'
            } ).click( function () {
                // If already liked, set value to 0, otherwise set to 1.
                var newValue = mw.articleScores.common.scoreInfo.Likes.user.value === mw.articleScores.likes.likeValue ? 0 : 1;

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

            $inputElement.empty();

            $inputElement.append( $likeButton );

            // Only create dislikes button if dislikes are enabled
            if( mw.articleScores.common.scoreInfo.Likes.hasOwnProperty( 'dislikes' ) ) {
                var $dislikeButton = $( '<a>', {
                    'class': 'articlescores-likes-dislike-input'
                } ).click( function () {
                    // If already disliked, set value to 0, otherwise set to -1.
                    var newValue = mw.articleScores.common.scoreInfo.Likes.user.value === mw.articleScores.likes.dislikeValue ? 0 : -1;

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

                $inputElement.append( $dislikeButton );
            }

            mw.articleScores.likes.renderValue();
        },
        renderValue: function() {
            var userValue = mw.articleScores.common.scoreInfo.Likes.user.value;

            $( '.articlescores-likes-likes-value' ).html( mw.articleScores.common.scoreInfo.Likes.likes.value );

            var $likeIcon = $( '<i>', {
                'class': ( userValue === mw.articleScores.likes.likeValue ? 'fas' : 'far' ) + ' fa-thumbs-up fa-fw'
            } );

            var likeLabel = mw.msg( 'articlescores-likes-' +
                ( userValue === mw.articleScores.likes.likeValue ? 'liked' : 'like' ) );

            $( '.articlescores-likes-like-input' ).empty().append( $likeIcon, likeLabel );

            if( mw.articleScores.common.scoreInfo.Likes.hasOwnProperty( 'dislikes' ) ) {
                $( '.articlescores-likes-percentLikes-value' ).html( mw.articleScores.common.scoreInfo.Likes.percentLikes.value );

                var dislikeIcon = $( '<i>', {
                    'class': ( userValue === mw.articleScores.likes.dislikeValue ? 'fas' : 'far' ) + ' fa-thumbs-down fa-fw'
                } );

                var dislikeLabel = mw.msg( 'articlescores-likes-' +
                    ( userValue === mw.articleScores.likes.dislikeValue ? 'disliked' : 'dislike' ) );

                $( '.articlescores-likes-dislike-input' ).empty().append( dislikeIcon, dislikeLabel );
            }
        },
        updateValues: function() {
            var pageId = mw.config.get( 'wgArticleId' );

            var callback = function( response ) {
                if( response.status === 'ok' ) {
                    for( var submetricId in response.result[ pageId ].Likes ) {
                        mw.articleScores.common.scoreInfo.Likes[ submetricId ].value = response.result[ pageId ].Likes[ submetricId ].value;
                    }

                    mw.articleScores.likes.renderValue();
                }
            };

            mw.articleScores.common.getScoresForPageIds( pageId, callback );
        }
    };

    mw.articleScores.likes.initialize();
}() );