'use strict';

(function () {

    var template = '<iframe class="edlib_resource" src="" />';
    var regexPercent = /^\s*(\d+\%)\s*$/i;

    CKEDITOR.plugins.add('edlib', {
        requires: 'widget,dialog',
        icons: 'edlib',
        hidpi: true,

        onLoad: function () {
            CKEDITOR.addCss(
                '.edlib_container{' +
                'line-height:0;' +
                '}' +
                '.edlib_container iframe{' +
                'outline-style:dashed;' +
                'outline-width:1px;' +
                'border-width:0;' +
                '}' +
                '.cke_editable.edlib_sw, .cke_editable.edlib_sw *{cursor:sw-resize !important}' +
                '.cke_editable.edlib_se, .cke_editable.edlib_se *{cursor:se-resize !important}' +
                '.edlib_resizer{' +
                'display:none;' +
                'position:absolute;' +
                'width:10px;' +
                'height:10px;' +
                'bottom:-5px;' +
                'right:-5px;' +
                'background:#000;' +
                'outline:1px solid #fff;' +
                'line-height:0;' +
                'cursor:se-resize;' +
                '}' +
                '.edlib_resizer_wrapper{' +
                'position:relative;' +
                'display:inline-block;' +
                'line-height:0;' +
                '}' +
                '.edlib_resizer.edlib_resizer_left{' +
                'right:auto;' +
                'left:-5px;' +
                'cursor:sw-resize;' +
                '}' +
                '.cke_widget_wrapper:hover .edlib_resizer,' +
                '.edlib_resizer.edlib_resizing{' +
                'display:block;' +
                '}' +
                '.edlib_nomouse{' +
                'pointer-events:none;' +
                '}'
            );
        },

        init: function (editor) {
            var edlib = widgetDef(editor);


            editor.on('contentDom', function () {
                this.window.on("message", function (event) {
                    var messageData = event.data.$;
                    if (messageData.data.context !== 'h5p' || messageData.data.action != "resize") {
                        return; // Only handle resize event of h5p requests.
                    }

                    // Find out who sent the message
                    var iframe, iframes = editor.document.find('iframe');
                    for (var i = 0; i < iframes.count(); i++) {
                        iframe = iframes.getItem(i).$;
                        if (iframe.contentWindow === messageData.source) {
                            iframe.height = messageData.data.scrollHeight;
                            break;
                        }
                    }
                    if (editor.plugins.autogrow) {
                        editor.execCommand("autogrow");
                    } else {
                        editor.execCommand("maximize");
                    }

                });
            });


            // Register the widget.
            editor.widgets.add('edlib', edlib);

            // Add toolbar button for this plugin.
            editor.ui.addButton && editor.ui.addButton('edlib', {
                label: 'edlib.com',
                command: 'edlib',
                toolbar: 'insert,1'
            });

            editor.config.contentsCss = ['/css/article-plugin.css'];

            CKEDITOR.dialog.add('edlibDialog', this.path + 'dialogs/edlib.js');
        },

        afterInit: function (editor) {
            // Integrate with align commands (justify plugin).
            var align = {
                left: 1,
                right: 1,
                center: 1,
                block: 1
            };
            var integrate = alignCommandIntegrator(editor);

            for (var value in align) {
                integrate(value);
            }
        }
    });

    // @param {CKEDITOR.editor}
    // @returns {Object}
    function widgetDef(editor) {
        function deflate() {
            if (this.deflated) {
                return;
            }

            // Remember whether widget was focused before destroyed.
            if (editor.widgets.focused == this.widget) {
                this.focused = true;
            }

            editor.widgets.destroy(this.widget);

            // Mark widget was destroyed.
            this.deflated = true;
        }

        function inflate() {
            var editable = editor.editable();
            var doc = editor.document;

            // Create a new widget according to what is the
            // new state of the widget.
            if (this.deflated) {
                this.widget = editor.widgets.initOn(this.element, 'iframe', this.widget.data);

                if (this.widget.inline && !( new CKEDITOR.dom.elementPath(this.widget.wrapper, editable).block )) {
                    var block = doc.createElement(editor.activeEnterMode == CKEDITOR.ENTER_P ? 'p' : 'div');
                    block.replace(this.widget.wrapper);
                    this.widget.wrapper.move(block);
                }

                // The focus must be transferred from the old one (destroyed)
                // to the new one (just created).
                if (this.focused) {
                    this.widget.focus();
                    delete this.focused;
                }

                delete this.deflated;
            }
        }

        return {
            allowedContent: getWidgetAllowedContent(editor),

            requiredContent: 'iframe[src]',

            features: getWidgetFeatures(editor),

            styleableElements: 'iframe',

            // This widget converts style-driven dimensions to attributes.
            contentTransformations: [
                ['iframe[width]: sizeToAttribute']
            ],

            parts: {
                iframe: 'iframe'
            },

            // The name of this widget's dialog.
            dialog: 'edlibDialog',

            // Template of the widget
            template: template,

            data: function () {
                var features = this.features;

                // can't be aligned when floating is disallowed
                if (this.data.align != 'none' && !editor.filter.checkFeature(features.align)) {
                    this.data.align = 'none';
                }

                // Convert the internal form of the widget from the old state to the new one.
                this.shiftState({
                    widget: this,
                    element: this.element,
                    oldData: this.oldData,
                    newData: this.data,
                    deflate: deflate,
                    inflate: inflate
                });

                this.parts.iframe.setAttributes({
                    src: this.data.src,
                    class: this.data.class,

                    // This internal is required by the editor.
                    'data-cke-saved-src': this.data.src
                });

                // Set dimensions
                if (editor.filter.checkFeature(features.dimension)) {
                    setDimensions(this);
                }

                // Cache current data.
                this.oldData = CKEDITOR.tools.extend({}, this.data);
            },

            init: function () {
                var helpers = CKEDITOR.plugins.edlib;
                var iframe = this.parts.iframe;
                var data = {
                    src: iframe.getAttribute('src'),
                    width: iframe.getAttribute('width') || '',
                    height: iframe.getAttribute('height') || '',
                    'class': iframe.getAttribute('class') || 'edlib_resource',
                    lock: false
                };

                // Depending on configuration, read style/class from element and
                // then remove it. Removed style/class will be set on wrapper in #data listener.
                // Note: Center alignment is detected during upcast, so only left/right cases
                // are checked below.
                if (!data.align) {
                    data.align = iframe.getStyle('float') || 'none';
                    iframe.removeStyle('float');
                }

                this.wrapper['addClass']('edlib_container');

                this.setData(data);

                // Setup dynamic resizing with mouse.
                setupResizer(this);

                this.shiftState = helpers.stateShifter(this.editor);

                // Pass the reference to this widget to the dialog.
                this.on('dialog', function (evt) {
                    evt.data.widget = this;
                }, this);
            },

            // Overrides default method to handle internal mutability
            // @see CKEDITOR.plugins.widget#addClass
            addClass: function (className) {
                this.parts.iframe.addClass(className);
            },

            // Overrides default method to handle internal mutability
            // @see CKEDITOR.plugins.widget#hasClass
            hasClass: function (className) {
                return this.parts.iframe.hasClass(className);
            },

            // Overrides default method to handle internal mutability
            // @see CKEDITOR.plugins.widget#removeClass
            removeClass: function (className) {
                this.parts.iframe.removeClass(className);
            },

            // Overrides default method to handle internal mutability
            // @see CKEDITOR.plugins.widget#getClasses
            getClasses: (function () {
                return function () {
                    return this.repository.parseElementClasses(this.parts.iframe.getAttribute('class'));
                }
            })(),

            upcast: upcastWidgetElement(editor),
            downcast: downcastWidgetElement(editor)
        };
    }

    /**
     * A set of plugin helpers.
     *
     * @class
     * @singleton
     */
    CKEDITOR.plugins.edlib = {
        stateShifter: function (editor) {
            // The order that stateActions get executed. It matters!
            var shiftables = [];

            // Atomic procedures, one per state variable.
            var stateActions = {};
            return function (shift) {
                var name, i;

                shift.changed = {};

                for (i = 0; i < shiftables.length; i++) {
                    name = shiftables[i];

                    shift.changed[name] = shift.oldData ?
                    shift.oldData[name] !== shift.newData[name] : false;
                }

                // Iterate over possible state variables.
                for (i = 0; i < shiftables.length; i++) {
                    name = shiftables[i];

                    stateActions[name](shift,
                        shift.oldData ? shift.oldData[name] : null,
                        shift.newData[name]);
                }

                shift.inflate();
            };
        },
        getNewElement: function () {
            return CKEDITOR.dom.element.createFromHtml(template);
        }
    };

    // Returns a function that creates widgets from all <iframe> elements.
    //
    // @param {CKEDITOR.editor} editor
    // @returns {Function}
    function upcastWidgetElement(editor) {
        var isCenterWrapper = centerWrapperChecker(editor);

        // @param {CKEDITOR.htmlParser.element} el
        // @param {Object} data
        return function (el, data) {
            var dimensions = {width: 1, height: 1};
            var iframe;

            // Don't initialize on pasted fake objects.
            if (el.attributes['data-cke-realelement']) {
                return;
            }

            if (isCenterWrapper(el)) {
                // If there's a centering wrapper, save it in data.
                data.align = 'center';
                iframe = el.getFirst('iframe');
            } else if (isLinkedOrStandaloneIframe(el)) {
                iframe = el;
            }

            if (!iframe) {
                return;
            }

            // If there's an iframe we got a widget.
            // Now just remove dimension attributes expressed with %.
            for (var d in dimensions) {
                var dimension = iframe.attributes[d];

                if (dimension) {
                    if (dimension.match(regexPercent)) {
                        delete iframe.attributes[d];
                    }
                }
            }
            if (iframe.attributes['width']) {
                iframe.attributes['width'] = Math.min(iframe.attributes['width'], editor.config.edlib_maxWidth);
            }

            return el;
        };
    }

    // Returns a function which transforms the widget to the external format
    // according to the current configuration.
    //
    // @param {CKEDITOR.editor}
    function downcastWidgetElement(editor) {
        // @param {CKEDITOR.htmlParser.element} el
        return function (el) {
            var attrs = el.attributes;
            var align = this.data.align;

            // De-wrap the iframe from resize handle wrapper.
            // Only block widgets have one.
            if (!this.inline) {
                var resizeWrapper = el.getFirst('span');

                if (resizeWrapper) {
                    resizeWrapper.replaceWith(resizeWrapper.getFirst({iframe: 1, a: 1}));
                }
            }

            if (align && align != 'none') {
                var styles = CKEDITOR.tools.parseCssText(attrs.style || '');

                // If left/right, add float style to the downcasted element.
                if (align in {left: 1, right: 1}) {
                    styles['float'] = align;
                }

                // Update element styles.
                if (!CKEDITOR.tools.isEmpty(styles)) {
                    attrs.style = CKEDITOR.tools.writeCssText(styles);
                }
            }

            return el;
        };
    }

    // Returns a function that checks if an element is a centering wrapper.
    //
    // @param {CKEDITOR.editor} editor
    // @returns {Function}
    function centerWrapperChecker(editor) {
        var validChildren = {a: 1, iframe: 1};

        return function (el) {
            // Wrapper must be either <div> or <p>.
            if (!( el.name in {div: 1, p: 1} )) {
                return false;
            }

            var children = el.children;

            // Centering wrapper can have only one child.
            if (children.length !== 1) {
                return false;
            }

            var child = children[0];

            // Only <iframe> can be first (only) child of centering wrapper,
            // regardless of its type.
            if (!( child.name in validChildren )) {
                return false;
            }

            // If centering wrapper is <p>
            //   <p style="text-align:center"><iframe /></p>
            if (el.name == 'p') {
                if (!isLinkedOrStandaloneIframe(child)) {
                    return false;
                }
            }
            // Centering <div>
            else {
                //   <div style="text-align:center"><iframe /></div>
                if (editor.enterMode == CKEDITOR.ENTER_P) {
                    return false;
                }

                if (!isLinkedOrStandaloneIframe(child)) {
                    return false;
                }
            }

            return (CKEDITOR.tools.parseCssText(el.attributes.style || '', true)['text-align'] == 'center');
        };
    }

    // Checks whether element is <iframe/>.
    //
    // @param {CKEDITOR.htmlParser.element}
    function isLinkedOrStandaloneIframe(el) {
        if (el.name == 'iframe' && el.hasClass('edlib_resource')) {
            return true;
        }

        return false;
    }

    // Sets width and height of the widget according to current widget data.
    //
    // @param {CKEDITOR.plugins.widget} widget
    function setDimensions(widget) {
        var data = widget.data;
        var dimensions = {width: data.width, height: data.height};
        var iframe = widget.parts.iframe;

        for (var d in dimensions) {
            if (dimensions[d]) {
                iframe.setAttribute(d, dimensions[d]);
            } else {
                iframe.removeAttribute(d);
            }
        }
    }

    // Defines all features related to drag-driven resizing.
    //
    // @param {CKEDITOR.plugins.widget} widget
    function setupResizer(widget) {
        var editor = widget.editor;
        var editable = editor.editable();
        var doc = editor.document;

        // Store the resizer in a widget for testing
        var resizer = widget.resizer = doc.createElement('span');

        resizer.addClass('edlib_resizer');
        resizer.append(new CKEDITOR.dom.text('\u200b', doc));

        // Inline widgets don't need a resizer wrapper
        if (!widget.inline) {
            var oldResizeWrapper = widget.parts.iframe.getParent();
            var resizeWrapper = doc.createElement('span');

            resizeWrapper.addClass('edlib_resizer_wrapper');
            resizeWrapper.append(widget.parts.iframe);
            resizeWrapper.append(resizer);
            widget.element.append(resizeWrapper, true);

            // Remove the old wrapper which could came from e.g. pasted HTML
            // and which could be corrupted (e.g. resizer span has been lost).
            if (oldResizeWrapper.is('span')) {
                oldResizeWrapper.remove();
            }
        } else {
            widget.wrapper.append(resizer);
        }

        // Calculate values of size variables and mouse offsets.
        resizer.on('mousedown', function (evt) {
            var iframe = widget.parts.iframe;

            // Don't allow element to be wider than the current width of the editor
            var maxWidth = editor.config.edlib_maxWidth;

            // "factor" can be either 1 or -1. I.e.: For right-aligned, we need to
            // subtract the difference to get proper width, etc. Without "factor",
            // resizer starts working the opposite way.
            var factor = widget.data.align == 'right' ? -1 : 1;

            // The x-coordinate of the mouse relative to the screen
            // when button gets pressed.
            var startX = evt.data.$.screenX;
            var startY = evt.data.$.screenY;

            // The initial dimensions and aspect ratio.
            var startWidth = iframe.$.clientWidth;
            var startHeight = iframe.$.clientHeight;
            var ratio = startWidth / startHeight;

            var listeners = [];

            // A class applied to editable during resizing.
            var cursorClass = 'edlib_s' + ( !~factor ? 'w' : 'e' );

            var nativeEvt, newWidth, newHeight, updateData, moveDiffX, moveDiffY, moveRatio;

            // Save the undo snapshot first: before resizing.
            editor.fire('saveSnapshot');

            // Mousemove listeners are removed on mouseup.
            attachToDocuments('mousemove', onMouseMove, listeners);

            // Clean up the mousemove listener. Update widget data if valid.
            attachToDocuments('mouseup', onMouseUp, listeners);

            // Prevent iFrame from getting events during resize
            iframe.addClass('edlib_nomouse');

            // The entire editable will have the special cursor while resizing goes on.
            editable.addClass(cursorClass);

            // This is to always keep the resizer element visible while resizing.
            resizer.addClass('edlib_resizing');

            // Attaches an event to a global document if inline editor.
            // Additionally, if classic (`iframe`-based) editor, also attaches the same event to `iframe`'s document.
            function attachToDocuments(name, callback, collection) {
                var globalDoc = CKEDITOR.document;
                var listeners = [];

                if (!doc.equals(globalDoc)) {
                    listeners.push(globalDoc.on(name, callback));
                }

                listeners.push(doc.on(name, callback));

                if (collection) {
                    for (var i = listeners.length; i--;) {
                        collection.push(listeners.pop());
                    }
                }
            }

            // Calculate with first, and then adjust height, preserving ratio if locked.
            function adjustToX() {
                newWidth = Math.min(startWidth + factor * moveDiffX, maxWidth);
                if (widget.data.lock) {
                    newHeight = Math.round(newWidth / ratio);
                } else {
                    newHeight = startHeight - moveDiffY;
                }
            }

            // Calculate height first, and then adjust width, preserving ratio if locked.
            function adjustToY() {
                newHeight = startHeight - moveDiffY;
                if (widget.data.lock) {
                    newWidth = Math.round(newHeight * ratio);
                } else {
                    newWidth = startWidth + factor * moveDiffX;
                }
                newWidth = Math.min(newWidth, maxWidth);
            }

            // This is how variables refer to the geometry.
            // Note: x corresponds to moveOffset, this is the position of mouse
            // Note: o corresponds to [startX, startY].
            //
            // 	+--------------+--------------+
            // 	|              |              |
            // 	|      I       |      II      |
            // 	|              |              |
            // 	+------------- o -------------+ _ _ _
            // 	|              |              |      ^
            // 	|      VI      |     III      |      | moveDiffY
            // 	|              |         x _ _ _ _ _ v
            // 	+--------------+---------|----+
            // 	               |         |
            // 	                <------->
            // 	                moveDiffX
            function onMouseMove(evt) {
                nativeEvt = evt.data.$;

                // This is how far the mouse is from the point the button was pressed.
                moveDiffX = nativeEvt.screenX - startX;
                moveDiffY = startY - nativeEvt.screenY;

                // This is the aspect ratio of the move difference.
                moveRatio = Math.abs(moveDiffX / moveDiffY);

                // Left, center or none-aligned widget.
                if (factor == 1) {
                    if (moveDiffX <= 0) {
                        // Case: IV.
                        if (moveDiffY <= 0) {
                            adjustToX();
                        }
                        // Case: I.
                        else {
                            if (moveRatio >= ratio) {
                                adjustToX();
                            } else {
                                adjustToY();
                            }
                        }
                    } else {
                        // Case: III.
                        if (moveDiffY <= 0) {
                            if (moveRatio >= ratio) {
                                adjustToY();
                            } else {
                                adjustToX();
                            }
                        }

                        // Case: II.
                        else {
                            adjustToY();
                        }
                    }
                }

                // Right-aligned widget. It mirrors behaviours, so I becomes II,
                // IV becomes III and vice-versa.
                else {
                    if (moveDiffX <= 0) {
                        // Case: IV.
                        if (moveDiffY <= 0) {
                            if (moveRatio >= ratio) {
                                adjustToY();
                            } else {
                                adjustToX();
                            }
                        }

                        // Case: I.
                        else {
                            adjustToY();
                        }
                    } else {
                        // Case: III.
                        if (moveDiffY <= 0) {
                            adjustToX();

                            // Case: II.
                        } else {
                            if (moveRatio >= ratio) {
                                adjustToX();
                            } else {
                                adjustToY();
                            }
                        }
                    }
                }

                // Don't update attributes if too small.
                // This is to prevent iframes to visually disappear.
                if (newWidth >= 30 && newHeight >= 30) {
                    iframe.setAttributes({width: newWidth, height: newHeight});
                    iframe.$.contentWindow.postMessage(
                        {
                            context: 'h5p',
                            action: 'resize'
                        },
                        '*'
                    );
                    updateData = true;
                } else {
                    updateData = false;
                }
            }

            function onMouseUp() {
                var l;

                while (( l = listeners.pop() )) {
                    l.removeListener();
                }
                // Allow interaction with iFrame contents
                iframe.removeClass('edlib_nomouse');

                // Restore default cursor by removing special class.
                editable.removeClass(cursorClass);

                // This is to bring back the regular behaviour of the resizer.
                resizer.removeClass('edlib_resizing');

                if (updateData) {
                    widget.setData({width: newWidth, height: newHeight});

                    // Save another undo snapshot: after resizing.
                    editor.fire('saveSnapshot');
                }

                // Don't update data twice or more.
                updateData = false;
            }
        });

        // Change the position of the widget resizer when data changes.
        widget.on('data', function () {
            resizer[widget.data.align == 'right' ? 'addClass' : 'removeClass']('edlib_resizer_left');
        });
    }

    // Integrates widget alignment setting with justify
    // plugin's commands (execution and refreshment).
    // @param {CKEDITOR.editor} editor
    // @param {String} value 'left', 'right', 'center' or 'block'
    function alignCommandIntegrator(editor) {
        var execCallbacks = [];
        var enabled;

        return function (value) {
            var command = editor.getCommand('justify' + value);

            // Most likely, the justify plugin isn't loaded.
            if (!command) {
                return;
            }
            // This command will be manually refreshed along with
            // other commands after exec.
            execCallbacks.push(function () {
                command.refresh(editor, editor.elementPath());
            });

            if (value in {right: 1, left: 1, center: 1}) {
                command.on('exec', function (evt) {
                    var widget = getFocusedWidget(editor);

                    if (widget) {
                        widget.setData('align', value);

                        // Once the widget changed its align, all the align commands
                        // must be refreshed: the event is to be cancelled.
                        for (var i = execCallbacks.length; i--;)
                            execCallbacks[i]();

                        evt.cancel();
                    }
                });
            }

            command.on('refresh', function (evt) {
                var widget = getFocusedWidget(editor);
                var allowed = {right: 1, left: 1, center: 1};

                if (!widget) {
                    return;
                }

                // Cache "enabled" on first use. This is because filter#checkFeature may
                // not be available during plugin's afterInit in the future — a moment when
                // alignCommandIntegrator is called.
                if (enabled === undefined) {
                    enabled = editor.filter.checkFeature(editor.widgets.registered.edlib.features.align);
                }

                // Don't allow justify commands when widget alignment is disabled
                if (!enabled) {
                    this.setState(CKEDITOR.TRISTATE_DISABLED);
                } else {
                    this.setState(
                        ( widget.data.align == value ) ? (
                            CKEDITOR.TRISTATE_ON
                        ) : (
                            ( value in allowed ) ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_DISABLED
                        )
                    );
                }

                evt.cancel();
            });
        };
    }

    // Returns the focused widget, if of the type specific for this plugin.
    // If no widget is focused, `null` is returned.
    //
    // @param {CKEDITOR.editor}
    // @returns {CKEDITOR.plugins.widget}
    function getFocusedWidget(editor) {
        var widget = editor.widgets.focused;

        if (widget && widget.name == 'edlib') {
            return widget;
        }
        return null;
    }

    // Returns a set of widget allowedContent rules
    //
    // @param {CKEDITOR.editor}
    // @returns {Object}
    function getWidgetAllowedContent(editor) {
        return {
            // Widget may need <div> or <p> centering wrapper.
            div: {
                match: centerWrapperChecker(editor),
                styles: 'text-align'
            },
            p: {
                match: centerWrapperChecker(editor),
                styles: 'text-align'
            },
            iframe: {
                attributes: '!src,width,height,!class',
                classes: 'edlib_resource',
                styles: 'float'
            }
        };
    }

    // Returns a set of widget feature rules, depending
    // on editor configuration. Note that the following may not cover
    // all the possible cases since requiredContent supports a single
    // tag only.
    //
    // @param {CKEDITOR.editor}
    // @returns {Object}
    function getWidgetFeatures(editor) {
        return {
            dimension: {
                requiredContent: 'iframe[width,height]'
            },
            align: {
                requiredContent: 'iframe{float}'
            }
        };
    }
})();
