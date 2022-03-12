( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores.editorRating = {
        initialize: function() {
            mw.hook( 'articleScores.getLinkFlairScores' ).add( mw.articleScores.editorRating.renderLinkFlair );
            mw.trackSubscribe( 'mediawiki.searchSuggest', mw.articleScores.editorRating.renderSearchSuggest );
            mw.trackSubscribe( 'mw.widgets.SearchInputWidget', mw.articleScores.editorRating.renderSearchInputWidget );

            var selectClass = 'articlescores-editorrating-input-select';

            $( '.' + selectClass ).change( function() {
                var newValue = $( this ).val();

                var callback = function( response ) {
                    if( response.status === 'ok' ) {
                        mw.articleScores.editorRating.renderValue( response.value );
                    }
                };

                mw.articleScores.common.setScore(
                    'EditorRating',
                    'main',
                    newValue,
                    callback
                );
            } );
        },
        generateIcon: function( properties ) {
            var iconAttribs = {
                'class': properties.icon,
                'title': properties.description
            };

            if( properties.iconColor ) {
                iconAttribs.style = 'color: ' + properties.iconColor;
            }

            return $( '<i>', iconAttribs );
        },
        renderLinkFlair: function( linkFlairScores ) {
            for( var pageId in linkFlairScores ) {
                if( linkFlairScores[ pageId ].hasOwnProperty( 'EditorRating' ) ) {
                    $( '.articlescores-linkflair[data-pageid="' + pageId + '"]' ).append(
                        mw.articleScores.editorRating.generateIcon( linkFlairScores[ pageId ].EditorRating.main )
                    );
                }
            }
        },
        renderSearchSuggest: function( topic, data ) {
            if( data.action === 'impression-results' ) {
                var titles = [];

                $( '.suggestions-results a' ).each( function() {
                    titles.push( $( this ).attr( 'title' ) );
                } );

                if( titles.length ) {
                    var callback = function( response ) {
                        if( response.status === 'ok' ) {
                            for( var title in response.result ) {
                                if( response.result[ title ].hasOwnProperty( 'EditorRating' ) ) {
                                    $( '.suggestions-results a[title="' + title + '"] .suggestions-result' ).append(
                                        '&nbsp;',
                                        mw.articleScores.editorRating.generateIcon( response.result[ title ].EditorRating.main )
                                    );
                                }
                            }
                        }
                    };

                    mw.articleScores.common.getScoresForTitles( titles, callback );
                }

            }
        },
        renderSearchInputWidget: function( topic, data ) {
            if( data.action === 'impression-results' ) {
                // We need a tiny delay to change the html of the ooui widget or it will get changed back immediately
                setTimeout( function() {
                    var titles = [];

                    $( '.mw-widget-titleWidget-menu > .mw-widget-titleOptionWidget a' ).each( function() {
                        titles.push( $( this ).attr( 'title' ) );
                    } );

                    if( titles.length ) {
                        var callback = function( response ) {
                            if( response.status === 'ok' ) {
                                for( var title in response.result ) {
                                    if( response.result[ title ].hasOwnProperty( 'EditorRating' ) ) {
                                        $( '.mw-widget-titleWidget-menu > .mw-widget-titleOptionWidget a[title="' + title + '"]' ).append(
                                            '&nbsp;',
                                            mw.articleScores.editorRating.generateIcon( response.result[ title ].EditorRating.main )
                                        );
                                    }
                                }
                            }
                        };

                        mw.articleScores.common.getScoresForTitles( titles, callback );
                    }
                }, 1 );
            }
        },
        renderValue: function( value ) {
            var $valueElement = $( '.articlescores-editorrating-value' );

            $valueElement.empty();
            if( value.icon ) {
                $valueElement.append( $( '<i>', {
                    'class': value.icon + ' ' + 'articlescores-editorrating-icon',
                    'style': 'color: ' + value.iconColor
                } ) );
            }

            $valueElement.append( value.name );
        }
    };

    mw.articleScores.editorRating.initialize();
}() );