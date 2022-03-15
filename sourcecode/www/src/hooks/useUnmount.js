import React from 'react';

const useUnmount = (fn) => {
    const fnRef = React.useRef(fn);
    fnRef.current = fn;

    React.useEffect(() => () => fnRef.current(), []);
};

export default useUnmount;
