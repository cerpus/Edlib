/**
 * @param {MessageEventSource} source
 * @returns {?HTMLIFrameElement}
 */
export function findIframeByWindow(source) {
    return [...document.querySelectorAll('iframe')]
        .find(iframe => iframe.contentWindow === source);
}

