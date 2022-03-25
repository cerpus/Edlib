export default (blockKey) => {
    const elements = document.querySelectorAll(
        `[data-block="true"][data-offset-key="${blockKey}-0-0"]`
    );
    if (elements.length === 0) {
        return null;
    }

    return elements[0];
};
