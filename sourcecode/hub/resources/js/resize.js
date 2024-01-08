/**
 * @param {MessageEventSource} source
 * @returns {?HTMLIFrameElement}
 */
function findIframeByWindow(source) {
    return [...document.querySelectorAll('iframe')]
        .find(iframe => iframe.contentWindow === source);
}

addEventListener('message', (event) => {
    if (event.data?.action !== 'resize' || !event.data?.scrollHeight) {
        return;
    }

    const iframe = findIframeByWindow(event.source);

    console.debug('Received a resize request', event.data, iframe);

    if (iframe) {
        const border = iframe.getBoundingClientRect().height - iframe.scrollHeight;

        iframe.height = String(event.data.scrollHeight + border);
    }
});
