import { findIframeByWindow } from "./helpers";

/**
 * H5P compatibility. A handshake has to be performed before resize requests are
 * sent.
 */
addEventListener('message', (event) => {
    if (event.data?.action !== 'hello') {
        return;
    }

    const iframe = findIframeByWindow(event.source);

    if (iframe) {
        event.source.postMessage({
            action: 'hello',
            context: 'h5p',
        }, { targetOrigin: '*' });
    }
});

addEventListener('message', (event) => {
    const action = event.data?.action;

    if (!['resize', 'prepareResize'].includes(action) || !event.data?.scrollHeight) {
        return;
    }

    const iframe = findIframeByWindow(event.source);

    console.debug('Received a resize request', event.data, iframe);

    if (!iframe) {
        return;
    }

    const border = iframe.getBoundingClientRect().height - iframe.scrollHeight;
    iframe.height = String(event.data.scrollHeight + border);

    if (window.parent && iframe.closest('.forwards-resize-messages')) {
        console.debug('Forwarding the resize request');

        parent.postMessage({
            action: 'resize',
            scrollHeight: event.data.scrollHeight,
        }, '*');
    }
});
