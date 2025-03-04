import React, { useState } from 'react';
import PropTypes from 'prop-types';
import clsx from 'clsx';
import { FormattedMessage } from 'react-intl';
import Cke5Editor from './Cke5Editor';
import getConfig from './Cke5ConfigFromSemantics';
import { useEditorSetupContext } from '../../../contexts/EditorSetupContext';

const prettifyPath = (path, glue = ' > ') =>
    path
        .map(node => (node.type === 'arrayIndex' ? node.index + 1 : node))
        .join(glue);

const getInputType = (type, widget) => {
    if (type === 'text' && widget === 'html') {
        return 'html';
    }

    return 'textarea';
};

const ListItem = ({ path, value, onChange, type, widget, startValue, shouldIndent, editorSemantics, label }) => {
    const [viewOldValue, setViewOldValue] = useState(false);
    const inputType = getInputType(type, widget);
    const { editorLanguage } = useEditorSetupContext();

    function encode (value) {
        if (!value || value === '') {
            return '';
        }
        return value.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/'/g, '&#039;')
            .replace(/"/g, '&quot;')
            ;
    }

    function decode (value) {
        if (!value || value === '') {
            return '';
        }
        const elm = document.createElement('div');
        elm.innerHTML = value;

        return elm.innerText;
    }

    return (
        <div
            className={clsx('h5p-editor-list-item', {
                indent: shouldIndent,
            })}
        >
            <div className="path">{prettifyPath(path)}</div>
            <div><strong><FormattedMessage id="H5P_EDITOR.FIELD_NAME" /></strong>: {label}</div>
            {(viewOldValue || startValue !== value) && typeof startValue !== 'undefined' && startValue !== '' && (
                <div className="start-value">
                    <i><FormattedMessage id="H5P_EDITOR.SAVED_TEXT" />: </i>
                    {inputType === 'html' && (
                        <div
                            // eslint-disable-next-line react/no-danger
                            dangerouslySetInnerHTML={{ __html: startValue }}
                            className="listitem-original-text"
                        />
                    )}
                    {inputType === 'textarea' && <span>{decode(startValue)}</span>}
                </div>
            )}
            {viewOldValue && (typeof startValue === 'undefined' || startValue === '') && (
                <div className="start-value">
                    <i><FormattedMessage id="H5P_EDITOR.INFO_NEW_UNSAVED" /></i>
                </div>
            )}
            <div className="translation-input">
                {inputType === 'textarea' && (
                    <textarea
                        className="translation-textarea"
                        rows={2}
                        value={decode(value)}
                        onChange={e => {
                            onChange(encode(e.target.value));
                            setViewOldValue(true);
                        }}
                    />
                )}
                {inputType === 'html' && (
                    <Cke5Editor
                        value={value}
                        onChange={value => {
                            onChange(value);
                            setViewOldValue(true);
                        }}
                        config={getConfig(editorSemantics)}
                        name={prettifyPath(path, '_')}
                        language={editorLanguage}
                    />
                )}
            </div>
        </div>
    );
};

ListItem.propTypes = {
    path: PropTypes.array.isRequired,
    value: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    type: PropTypes.string.isRequired,
    widget: PropTypes.string,
    startValue: PropTypes.string,
    shouldIndent: PropTypes.bool.isRequired,
    editorSemantics: PropTypes.object,
};

export default ListItem;
