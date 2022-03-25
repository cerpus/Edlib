import React from 'react';

export default (ref) => {
    const getSize = () => ({
        height: ref.current ? ref.current.offsetHeight : 0,
        width: ref.current ? ref.current.offsetWidth : 0,
    });

    const [size, setSize] = React.useState(getSize);

    React.useLayoutEffect(() => {
        if (!ref.current) return;

        const handleResize = () => {
            setSize(getSize());
        };

        if (typeof ResizeObserver === 'function') {
            let resizeObserver = new ResizeObserver(function () {
                handleResize();
            });
            resizeObserver.observe(ref.current);

            return function () {
                resizeObserver.disconnect(ref.current);
                resizeObserver = null;
            };
        }

        window.addEventListener('resize', handleResize);

        return function () {
            window.removeEventListener('resize', handleResize);
        };
    }, [ref.current]);

    return size;
};
