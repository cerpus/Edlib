document.addEventListener('click', async (event) => {
    if (event.ctrlKey || event.altKey || event.shiftKey || event.metaKey) {
        return;
    }

    const element = event.target.closest('.share-button');

    if (element) {
        event.preventDefault();
        event.stopImmediatePropagation();

        const shareUrl = element.href;
        const successMessage = element.getAttribute('data-share-success-message');
        const failureMessage = element.getAttribute('data-share-failure-message');

        // TODO: use a bootstrap toast or something nicer instead
        try {
            await navigator.clipboard.writeText(shareUrl);

            alert(successMessage);
        } catch (e) {
            console.error('An error occurred while copying the URL', e);

            alert(failureMessage)
        }
    }
});
