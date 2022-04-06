import * as React from 'react';
import loadScript from 'load-script';
import MathJaxContext from './context';

const Provider = ({ children, script, options }) => {
    const [MathJax, setMathJax] = React.useState(null);
    const [hasNode, setHasNode] = React.useState(null);

    React.useEffect(() => {
        if (hasNode) {
            loadScript(script, () => {
                window.MathJax.Hub.Config(options);
                setMathJax(window.MathJax);
            });
        }
    }, [hasNode]);

    return (
        <MathJaxContext.Provider
            value={{
                MathJax,
                registerNode: () => !hasNode && setHasNode(true),
            }}
        >
            {children}
        </MathJaxContext.Provider>
    );
};

Provider.defaultProps = {
    script:
        'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-MML-AM_CHTML',
    options: {
        tex2jax: {
            inlineMath: [],
        },
        showMathMenu: false,
        showMathMenuMSIE: false,
    },
};

export default Provider;
