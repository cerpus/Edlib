const blockStyleFn = (contentBlock) => {
    const type = contentBlock.getType();

    if (type === 'unstyled') {
        return 'paragraph';
    }
};

export default blockStyleFn;
