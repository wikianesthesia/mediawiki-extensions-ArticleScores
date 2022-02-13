( function () {
    if( typeof mw === 'undefined' || mw === null ) {
        throw "";
    }

    mw.articleScores.editorRating = {
        initialize: function() {
            var selectClass = 'articlescores-editorrating-input-select';
            $( '.' + selectClass ).change( function() {
                var newValue = $( this ).val();
                var newHtml = $( '.' + selectClass + ' option:selected' ).text();

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
        renderValue: function( value ) {
            var $valueElement = $( '.articlescores-editorrating-value' );

            $valueElement.empty();
            var newValueHtml = '';

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