(function () {
    const scoreIframes = [];
    let delayEvent;

    function triggerResize() {
        let resizeEvent;
        if (typeof Event !== 'function') {
            resizeEvent = window.document.createEvent('UIEvents');
            resizeEvent.initUIEvent('resize', true, false, window, 0);
        } else {
            resizeEvent = new Event('resize');
        }
        window.dispatchEvent(resizeEvent);
    }

    function iframeParent() {
        // Send 'resize' message to make parent container as large as we need
        const parent = window.parent;
        if (typeof parent !== 'undefined' && parent !== window) {
            const html = document.getElementsByTagName('html').item(0);
            const body = document.body;
            // Make iframe responsive
            body.style.height = 'auto';

            if (typeof isPreview !== 'undefined' && !!isPreview !== true) {
                // Hide scrollbars for correct size
                body.style.overflow = 'hidden';
            }

            clearTimeout(delayEvent);
            delayEvent = setTimeout(function () {
                const data = {
                    context: 'h5p',
                    action: 'resize',
                    scrollHeight: html.scrollHeight,
                    height: body.clientHeight,
                };
                parent.postMessage(data, '*');
            }, 100);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        iframeParent();
        // Respond to H5P resize events, do not relay.
        // Relay xAPI and result events to the parent.
        window.addEventListener('message', function (event) {
            if (event.data && event.data.context && event.data.context === 'h5p') {
                const parent = window.parent;
                const action = event.data.action || '';
                switch (action) {
                    case 'hello':
                        event.source.postMessage(
                            {
                                context: 'h5p',
                                action: 'hello'
                            },
                            event.origin
                        );

                    //No break
                    case 'getScoreSetting':
                        event.source.postMessage({
                            context: 'h5p',
                            action: 'getScoreSetting'
                        }, event.origin);
                        break;
                    case 'registerScoreSetting':
                        if (event.data.canGiveScore === true) {
                            const iframeData = {
                                'source': event.source.location,
                                'score': null
                            };
                            if (scoreIframes.filter(function (scoreIframe) {
                                return scoreIframe.source === iframeData.source;
                            }).length === 0) {
                                scoreIframes.push(iframeData);
                            }
                        }
                        break;
                    case 'prepareResize':
                        event.source.postMessage(
                            {
                                context: 'h5p',
                                action: 'resizePrepared'
                            },
                            event.origin
                        );
                        break;
                    case 'resize':
                        // Find out who sent the message
                        const iframes = document.getElementsByTagName('iframe');
                        for (let iframe of iframes) {
                            if (iframe.contentWindow === event.source) {
                                iframe.height = event.data.scrollHeight;
                                iframe.flexBasis = event.data.scrollHeight;
                                iframe.frameBorder = 0;
                            }
                        }
                        iframeParent();
                        break;
                    case 'statement':
                        if (typeof event.data !== 'undefined') {
                            // Inject ourselves to the context
                            const context = event.data.statement.context || {};
                            const contextActivities = context.contextActivities || {};
                            const contextCategory = contextActivities.category || [];
                            const contextParent = contextActivities.parent || [];

                            contextParent.push({
                                objectType: 'Activity',
                                id: window.location.href
                            });
                            contextCategory.push({
                                objectType: 'Activity',
                                id: 'http://edlib.com/resources/types/Article'
                            });

                            contextActivities.parent = contextParent;
                            contextActivities.category = contextCategory;

                            context.contextActivities = contextActivities;
                            event.data.statement.context = context;
                        }
                        if (typeof parent !== 'undefined' && parent !== window) {
                            if (typeof event.data !== 'undefined') {
                                parent.postMessage(event.data, '*');
                            }
                        }
                        break;
                    case 'score':
                        const scores = scoreIframes
                            .map(function (scoreIframe) {
                                if (event.source.location === scoreIframe.source) {
                                    scoreIframe.score = event.data.score;
                                }
                                return scoreIframe;
                            }).filter(function (scoreIframe) {
                                return scoreIframe.score !== null;
                            });

                        if (scores.length !== scoreIframes.length) {
                            return false;
                        }

                        const accumulatedScore = scores.reduce(function (total, scoreIframe) {
                            return total + scoreIframe.score;
                        }, 0);

                        event.data.score = accumulatedScore / scoreIframes.length;
                        if (typeof parent !== 'undefined' && parent !== window) {
                            if (typeof event.data !== 'undefined') {
                                parent.postMessage(event.data, '*');
                            }
                        }
                        break;
                }
            }
        }, false);

        window.addEventListener('resize', iframeParent);

        $('iframe.ndla-iframe').each(function () {
            if (this.src.match(/players\.brightcove\.net\/|youtu\.be|youtube\.com|player\.vimeo\.com|static\.nrk\.no|embed\.ted\.com|ndla\.filmiundervisning\.no/)) {
                this.frameBorder = 0;
                this.width = '100%';
                this.height = this.clientWidth * (9 / 16);
            }

            if (this.src.match(/www\.geogebra\.org|geogebra\.org/)) {
                this.frameBorder = 0;

                try {
                    let ratio = 1200 / 600;

                    const width = parseInt(this.width.replace(/px/g, ''), 10);
                    this.width = '100%';

                    const height = parseInt(this.height.replace(/px/g, ''), 10) + 1;

                    const iframeRatio = width / height;
                    if ((typeof iframeRatio) === 'number') {
                        ratio = iframeRatio;
                    }
                    this.height = this.clientWidth / ratio;
                } catch (err) {
                }
            }
        });

        // This makes images inside <details> tags visible. Images are H5Ps in EdLib, and is embedded as an iframe.
        // Iframes get a height of 0, probably because the details section is collapsed by default.
        // The height is just a guess since we don't really know anything about the content.
        document.querySelectorAll('details.ndla-details').forEach(function (dnode) {
            dnode.addEventListener('click', function () {
                if (!dnode.open) {
                    dnode.childNodes.forEach(function (inode) {
                        if (inode.nodeName.toUpperCase() === 'IFRAME') {
                            const height = inode.parentElement.getBoundingClientRect().width;
                            inode.frameBorder = 0;
                            inode.width = '100%';
                            inode.height = height;
                        }
                    });
                }
            });
        });

        $('table.ndla-table:not(.table)').each(function () {
            this.classList.add('table');
        });

        if (typeof MathJax !== 'undefined') {
            MathJax.Hub.Config({ messageStyle: 'none' });
            MathJax.Hub.Queue(function () {
                triggerResize();
            });
        }
    });
})();
