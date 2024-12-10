/**
 * Forward score and xAPI statements to parent
 */
addEventListener('message', (event) => {
    if (window.parent && window.location !== window.parent.location && ['score', 'statement'].includes(event.data?.action)) {
        parent.postMessage(event.data, '*');
    }
});
