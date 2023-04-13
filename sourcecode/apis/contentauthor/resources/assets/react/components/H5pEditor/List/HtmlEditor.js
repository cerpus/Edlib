import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { CKEditor } from 'ckeditor4-react';

const HtmlEditor = ({ value, onChange, name, config }) => {
    const [focus, setFocus] = useState(false);

    const handleOnChange = e => {
        if (focus) {
            onChange(e.editor.getData());
        }
    };

    const handleFocus = () => {
        setFocus(true);
        onChange(value);
    };

    return (
        <CKEditor
            config={config}
            initData={value}
            name={name}
            onChange={handleOnChange}
            onFocus={handleFocus}
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
