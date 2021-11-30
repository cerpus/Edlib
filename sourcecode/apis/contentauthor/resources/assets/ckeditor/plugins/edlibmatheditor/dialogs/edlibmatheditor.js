CKEDITOR.dialog.add( 'edlibmatheditorDialog', function( editor ) {
    var mathEditor,
    lang = editor.lang.edlibmatheditor;

    return {
        title: lang.title,
        minWidth: 200,
        minHeight: 100,
        contents: [
            {
                id: 'info',
                elements: [
                    {
                        id: "mathpreview",
                        type: "html",
                        html: "<div id=\"answer\"></div>",
                        onLoad: function() {
                            mathEditor = new MathEditor('answer', lang);
                        },
                        commit: function( widget ) {
                            widget.setData( 'math', mathEditor.getLatex() );
                        },
                        setup: function( widget ) {
                            mathEditor.setLatex(widget.data.math);
                        },
                    }
                ]
            }
        ]
    };
});