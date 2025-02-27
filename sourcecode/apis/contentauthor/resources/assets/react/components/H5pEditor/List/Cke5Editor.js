import 'ckeditor5/ckeditor5.css';
import { useState, useEffect, useRef, useMemo } from 'react';
import PropTypes from 'prop-types';
import { CKEditor } from '@ckeditor/ckeditor5-react';
import { ClassicEditor, SourceEditing } from 'ckeditor5';

export default function HtmlEditor({ value, onChange, name, config, language }) {
    const LICENSE_KEY = 'GPL';
    const editorContainerRef = useRef(null);
    const editorRef = useRef(null);
    const [isLayoutReady, setIsLayoutReady] = useState(false);
    const [translation, setTranslation] = useState([]);

    useEffect(() => {
        (async () => {
            if (language) {
                const lang = ['nb', 'nn'].includes(language) ? 'no' : language;
                const {default: translation } = await import('ckeditor5/translations/' + lang + '.js');
                setTranslation(translation ?? []);
            }
            setIsLayoutReady(true);
        })();

        return () => setIsLayoutReady(false);
    }, []);

    const { editorConfig } = useMemo(() => {
        if (!isLayoutReady) {
            return {};
        }

        // Add some extra tools
        config.toolbar.items.unshift('sourceEditing', '|');
        config.plugins.push(SourceEditing);

        return {
            name: name,
            editorConfig: {
                licenseKey: LICENSE_KEY,
                initialData: value,
                ui: {
                    poweredBy: {
                        // Removes the 'Powered by' text next to the CKEditor logo
                        label: null,
                    },
                },
                translations: [translation],
                ...config,
            },
        };
    }, [isLayoutReady]);

    return (
        <div className="html-editor--container">
            <div className="editor-container editor-container_classic-editor" ref={editorContainerRef}>
                <div className="editor-container__editor">
                    <div ref={editorRef}>
                        {editorConfig && (
                            <CKEditor
                                onChange={(e, editor) => {
                                    onChange(editor.getData());
                                }}
                                editor={ClassicEditor}
                                config={editorConfig}
                            />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

HtmlEditor.propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func,
    name: PropTypes.string,
    config: PropTypes.object,
    language: PropTypes.string,
};

HtmlEditor.defaultProps = {
    value: '',
    onChange: () => {},
    config: {},
    language: 'en',
};
