import * as React from 'react';
import MathJaxContext from './context';

class NodeWithMathJax extends React.Component {
    constructor(props) {
        super(props);

        this.container = React.createRef();
    }

    componentDidMount() {
        this.typeset();
    }

    componentDidUpdate(prevProps) {
        const forceUpdate =
            prevProps.inline !== this.props.inline ||
            prevProps.formula !== this.props.formula ||
            prevProps.type !== this.props.type;
        this.typeset(forceUpdate, prevProps.type !== this.props.type);
    }

    shouldComponentUpdate(nextProps, nextState, nextContext) {
        return (
            nextProps.formula !== this.props.formula ||
            nextProps.inline !== this.props.inline ||
            nextProps.type !== this.props.type
        );
    }

    componentWillUnmount() {
        this.clear();
    }

    clear(clearScript = false) {
        const { MathJax } = this.props;

        if (!this.script) {
            return;
        }

        const jax = MathJax.Hub.getJaxFor(this.script);

        if (jax) {
            jax.Remove();
        }

        if (clearScript) {
            this.script.remove();
            this.script = undefined;
        }
    }

    typeset(forceUpdate, clearScript = false) {
        const { MathJax, formula, onRender } = this.props;

        if (!MathJax) {
            return;
        }

        if (forceUpdate) {
            this.clear(clearScript);
        }

        if (forceUpdate || !this.script) {
            this.setScriptText(formula);
        }

        MathJax.Hub.Queue(MathJax.Hub.Reprocess(this.script, onRender));
    }

    setScriptText(text) {
        const { inline, type } = this.props;

        if (!this.script) {
            this.script = document.createElement('script');
            this.script.type = `${type === 'mml' ? 'math/mml' : 'math/tex'}; ${
                inline ? '' : 'mode=display'
            }`;
            this.container.current.appendChild(this.script);
        }

        if ('text' in this.script) {
            this.script.text = text;
        } else {
            this.script.textContent = text;
        }

        return this.script;
    }

    render() {
        // eslint-disable-next-line no-unused-vars
        const {
            MathJax,
            formula,
            inline,
            onRender,
            type,
            ...rest
        } = this.props;

        if (this.props.inline) {
            return <span ref={this.container} {...rest} />;
        }

        return <div ref={this.container} {...rest} />;
    }
}

NodeWithMathJax.defaultProps = {
    inline: false,
    onRender: () => {},
    type: 'tex',
};

export default (props) => {
    const { MathJax, registerNode } = React.useContext(MathJaxContext);

    React.useEffect(() => {
        registerNode();
    }, []);

    if (!MathJax) {
        return <></>;
    }

    return <NodeWithMathJax {...props} MathJax={MathJax} />;
};
