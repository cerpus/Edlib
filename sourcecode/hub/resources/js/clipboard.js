document.addEventListener('click', async (event) => {
    if (event.ctrlKey || event.altKey || event.shiftKey || event.metaKey) {
        return;
    }

    const element = event.target.closest('.copy-to-clipboard');

    if (element) {
        event.preventDefault();
        event.stopImmediatePropagation();

        const value = element.getAttribute('data-share-value');
        const successMessage = element.getAttribute('data-share-success-message');
        const failureMessage = element.getAttribute('data-share-failure-message');

        // TODO: use a bootstrap toast or something nicer instead
        try {
            await navigator.clipboard.writeText(value);

            alert(successMessage);
        } catch (e) {
            console.error('An error occurred while copying to clipboard', e);

            alert(failureMessage)
        }
    }
});
