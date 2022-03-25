const getSelectedBlockNode = (root) => {
    const selection = root.getSelection();
    if (selection.rangeCount === 0) {
        return null;
    }

    let node = selection.getRangeAt(0).startContainer;
    do {
        if (node.getAttribute && node.getAttribute('data-block') === 'true') {
            return node;
        }
        node = node.parentNode;
    } while (node !== null);

    return null;
};

export default getSelectedBlockNode;
