'use strict';

( function() {
    /**
     * Namespace providing a set of helper functions for working with tables, exposed by
     * [Table Selection](https://ckeditor.com/cke4/addon/tableselection) plugin.
     *
     * @since 4.7.0
     * @singleton
     * @class CKEDITOR.plugins.tableselection
     */
    CKEDITOR.plugins.edlibmatheditor = {};

    CKEDITOR.plugins.edlibmatheditor.isSupportedEnvironment = !( CKEDITOR.env.ie && CKEDITOR.env.version < 11 );

    CKEDITOR.plugins.edlibmatheditor.fixSrc =
        // In Firefox src must exist and be different than about:blank to emit load event.
        CKEDITOR.env.gecko ? 'javascript:true' : // jshint ignore:line
            // Support for custom document.domain in IE.
            CKEDITOR.env.ie ? 'javascript:' + // jshint ignore:line
                'void((function(){' + encodeURIComponent(
                    'document.open();' +
                    '(' + CKEDITOR.tools.fixDomain + ')();' +
                    'document.close();'
                ) + '})())' :
                // In Chrome src must be undefined to emit load event.
                'javascript:void(0)'; // jshint ignore:line

    CKEDITOR.plugins.edlibmatheditor.trim = function( value ) {
        var begin = value.indexOf( '\\(' ) + 2,
            end = value.lastIndexOf( '\\)' );

        return value.substring( begin, end );
    };

    CKEDITOR.plugins.edlibmatheditor.renderMathJax = function(el){
        MathJax.Hub.Queue(['Typeset', MathJax.Hub, el]);
    };

    CKEDITOR.plugins.add( 'edlibmatheditor', {
        lang: 'nb-no,en-gb',
        requires: 'widget,dialog',
        icons: 'edlibmatheditor',
        init: function( editor ) {
            // Disable unsupported browsers.
            if ( !CKEDITOR.plugins.edlibmatheditor.isSupportedEnvironment ) {
                return;
            }

            var mathClass = editor.config.edlibMathClass || 'math_container';

            CKEDITOR.scriptLoader.load([
                '/js/mathquillEditor/mathquill.min.js',
                '/js/mathquillEditor/matheditor.js',
            ]);

            editor.addCommand( 'insertEdlibMath', new CKEDITOR.dialogCommand( 'edlibmatheditorDialog' ) );

            editor.ui.addButton( 'Edlibmatheditor', {
                label: 'Insert Edlib math',
                command: 'insertEdlibMath',
                toolbar: 'insert'
            });

            CKEDITOR.dialog.add( 'edlibmatheditorDialog', this.path + 'dialogs/edlibmatheditor.js' );

            editor.on( 'paste', function( evt ) {
                var regex = /(\\\((.+?)\\\)|(\${1,2})(.+?)(?:\3))/ig;
                evt.data.dataValue = evt.data.dataValue.replace( regex, function( match, first, second, third, fourth ) {
                    const math = second || fourth;
                    return '<span class="' + mathClass + '">\\( ' + math + '\\)</span>';
                } );

            });


            editor.widgets.add( 'edlibmatheditor', {
                button: editor.lang.edlibmatheditor.buttonLabel,
                dialog: 'edlibmatheditorDialog',
                draggable: false,
                template: '<span class="' + mathClass + '"></span>',
                allowedContent: 'span(!' + mathClass + ')',
                requiredContent: 'span(' + mathClass + ')',
                parts: {
                    span: 'span'
                },
                upcast: function( el, data ) {
                    if ( !( el.name == 'span' && el.hasClass( mathClass ) ) )
                        return;

                    if ( el.children.length > 1 || el.children[ 0 ].type != CKEDITOR.NODE_TEXT )
                        return;

                    const trimmedValue = CKEDITOR.plugins.edlibmatheditor.trim(el.children[0].value);
                    data.math = CKEDITOR.tools.htmlDecode( trimmedValue );

                    el.children[ 0 ].remove();
                    CKEDITOR.plugins.edlibmatheditor.renderMathJax(el.parentNode);
                    return el;
                },
                init: function () {
                    this.data.math = this.parts.span.getText();
                },
                defaults: {
                    math: '',
                },
                data: function(){
                    const element = this.element.$;
                    if( this.data.math.length > 0){
                        this.parts.span.setText('\\( ' + this.data.math + ' \\)');
                        setTimeout(function () {
                            CKEDITOR.plugins.edlibmatheditor.renderMathJax(element.parentNode);
                        }, 0);
                    } else {
                        this.parts.span.setText('');
                    }
                },
                downcast: function(el){
                    el.setHtml('\\( ' + this.data.math + ' \\)');
                    return el;
                }
            } );
        },
        onLoad: function () {
            CKEDITOR.document.appendStyleSheet( '/js/mathquillEditor/mathquill.css' );
            CKEDITOR.document.appendStyleSheet( '/js/mathquillEditor/matheditor.css' );
        }
    });
}());