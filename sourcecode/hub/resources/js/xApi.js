/**
 * Forward score and xAPI statements to parent
 */
addEventListener('message', (event) => {
    if (['score', 'statement'].includes(event.data?.action) && window.parent) {
        parent.postMessage(event.data, '*');
    }
});
