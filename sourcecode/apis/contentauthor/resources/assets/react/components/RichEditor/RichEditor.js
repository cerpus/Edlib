import React from 'react';
import PropTypes from 'prop-types';
import { rerenderMathJax } from '../QuestionContentType/components/utils';
import { injectIntl } from 'react-intl';

class RichEditor extends React.Component {
    static propTypes = {
        value: PropTypes.string,
        placeholder: PropTypes.node,
        onChange: PropTypes.func,
        defaultValue: PropTypes.string,
        name: PropTypes.string,
        className: PropTypes.string,
        onFocus: PropTypes.func,
        onBlur: PropTypes.func,
        customConfigFile: PropTypes.string,
        language: PropTypes.string,
    };

    static defaultProps = {
        value: '',
        placeholder: null,
        onChange: null,
        defaultValue: '',
        name: null,
        className: '',
        onFocus: () => {},
        onBlur: () => {},
        customConfigFile: '/js/ckeditor/inline-config.js',
        language: null,
    };

    /**
     * @var {object} _inputReference    Reference to the div that the editor will be connected to
     */
    _inputReference = null;

    /**
     * @var {object} _editorReference   Reference to the editor instance
     */
    _editorReference = null;

    _isMounted = false;

    constructor(props) {
        super(props);

        this.state = {
            hasFocus: false,
        };

        window.CKEDITOR.disableAutoInline = true;

        this.handleAttachEditor = this.handleAttachEditor.bind(this);
        this.handleEditorFocus = this.handleEditorFocus.bind(this);
        this.handleEditorBlur = this.handleEditorBlur.bind(this);
        this.insertMathClass = this.insertMathClass.bind(this);
    }

    componentWillUnmount() {
        this._isMounted = false;
        if (this._editorReference !== null) {
            if (this._editorReference.focusManager.hasFocus) {
                this._editorReference.focusManager.blur(true);
            }
            this._editorReference = null;
        }
    }

    componentDidMount() {
        this._isMounted = true;
        if (this._inputReference !== null) {
            this._inputReference.innerHTML = this.insertMathClass(this.props.value);
        }
    }

    shouldComponentUpdate(nextProps, nextState) {
        return !(
            this._editorReference &&
            this._editorReference.focusManager.hasFocus &&
            nextState.hasFocus === this.state.hasFocus
        );
    }

    componentDidUpdate(prevProps) {
        if (prevProps.value !== this.props.value) {
            this._inputReference.innerHTML = this.props.value;
        }
    }

    insertMathClass(text) {
        const regex = /(?:<span[^>]+class=.math_container[^>]+>)*\s*(\\\((.+?)\\\)|(\${1,2})(.+?)(?:\3))(?:<\/span>)*/gi;
        const subst = '<span class="math_container">\\( $4$2 \\)</span>';
        return text.replace(regex, subst);
    }

    handleAttachEditor() {
        if (this.props.onChange) {
            if ((this._editorReference === null || this._editorReference.status === 'destroyed') && this._inputReference !== null) {
                this._inputReference.innerHTML = this.insertMathClass(this.props.value);
                const config = {
                    customConfig: this.props.customConfigFile,
                    startupFocus: true,
                    on: {
                        blur: this.handleEditorBlur,
                    },
                    floatSpaceDockedOffsetY: 10,
                    floatSpaceDockedOffsetX: 5,
                };

                if (this.props.name) {
                    config.title = this.props.name;
                }
                if (this.props.language) {
                    config.language = this.props.language;
                } else {
                    config.language = this.props.intl.locale.toLocaleLowerCase();
                }

                this._editorReference = window.CKEDITOR.inline(
                    this._inputReference,
                    config
                );
            }
            this.handleEditorFocus();
        }
    }

    handleEditorFocus() {
        if (this._editorReference) {
            if ( this.props.onChange ) {
                this.props.onChange(null, false);
            }
            this.setState({
                hasFocus: true,
            }, () => {
                this._editorReference.focus();
                this.props.onFocus();
            });
        }
    }

    handleEditorBlur(e) {
        let data;
        if ( this._editorReference !== null ) {
            this._editorReference.destroy(true);
            data = this._editorReference.getData();
        } else {
            e.editor.destroy(true);
            data = e.editor.getData();
        }
        if (this.props.onChange) {
            if ( data !== this.props.value ) {
                this.props.onChange(data, true);
            } else {
                this.props.onChange(null, true);
            }
        }
        if (this._isMounted) {
            rerenderMathJax();
            this.setState({
                hasFocus: false,
            }, () => {
                this.props.onBlur();
            });
        }
    }

    render() {
        let placeholder = null;
        let editable = false;
        let containerClasses = 'richeditor-container';
        let inputClasses = 'richeditor-input';

        if (this.state.hasFocus) {
            if (this.props.value === null || this.props.value.length === 0) {
                if (this._editorReference) {
                    this._editorReference.setData(this.props.defaultValue, { noSnapshot: true });
                }
            }
        } else if (this.props.placeholder !== null &&
            (this.props.value === null || this.props.value.length === 0) &&
            (this._inputReference === null || this._inputReference.innerText.length === 0 ||
                (this._inputReference.innerText.length === 1 && this._inputReference.innerText.charCodeAt(0) === 10)
            )
        ) {
            placeholder = (
                <div className="richeditor-placeholder" onClick={this.handleAttachEditor}>
                    {this.props.placeholder}
                </div>
            );
            inputClasses += ' richeditor-invisible';
        }

        if (this.props.className) {
            containerClasses += ' ' + this.props.className;
        }

        if (this.props.onChange) {
            editable = true;
            containerClasses += ' richeditor-editable';
        } else {
            containerClasses += ' richeditor-readonly';
        }

        return (
            <div className={containerClasses}>
                <div className="richeditor-content">
                    {placeholder}
                    <div
                        className={inputClasses}
                        contentEditable={editable}
                        ref={input => {this._inputReference = input;}}
                        onFocus={this.handleAttachEditor}
                    />
                </div>
            </div>
        );
    }
}

RichEditor = injectIntl(RichEditor);
export default RichEditor;
