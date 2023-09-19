import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import { CKEditor } from 'ckeditor4-react';

const HtmlEditor = ({ value, onChange, name, config }) => {
    const [instanceReady, setInstanceReady] = useState(false);
    const [newValue, setNewValue] = useState(value);

    useEffect(() => {
        if (instanceReady) {
            onChange(newValue);
        }
    }, [newValue]);

    return (
        <CKEditor
            config={config}
            initData={value}
            name={name}
            onChange={({editor}) => setNewValue(editor.getData())}
            onInstanceReady={() => setInstanceReady(true)}
        />
    );
};

HtmlEditor.propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func,
    name: PropTypes.string,
    config: PropTypes.object,
};

HtmlEditor.defaultProps = {
    value: '',
    onChange: () => {},
    config: {},
};

export default HtmlEditor;
