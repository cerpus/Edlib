import * as React from 'react';

const MathJaxContext = React.createContext({
    MathJax: null,
    registerNode: () => {},
});

export default MathJaxContext;
