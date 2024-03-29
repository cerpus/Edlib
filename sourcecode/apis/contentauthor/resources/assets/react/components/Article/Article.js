/* eslint-disable no-prototype-builtins */
import React from 'react';
import PropTypes from 'prop-types';
import Sidebar from '../Sidebar';
import EditorContainer from '../EditorContainer/EditorContainer';
import { FormActions, useForm } from '../../contexts/FormContext';
import  { CKEditor } from 'ckeditor4-react';
import { injectIntl } from 'react-intl';
import TextField from '@material-ui/core/TextField';
import FormLabel from '@material-ui/core/FormLabel';

const editorMessageHandler = (event) => {
    const originalEvent = event.data.$;

    if (
        originalEvent.data &&
        originalEvent.data.context &&
        originalEvent.data.context === 'h5p'
    ) {
        const action = originalEvent.data.action || '';

        switch (action) {
            case 'hello':
                originalEvent.source.postMessage(
                    {
                        context: 'h5p',
                        action: 'hello',
                    },
                    originalEvent.origin
                );
                break;
            case 'prepareResize':
                originalEvent.source.postMessage(
                    {
                        context: 'h5p',
                        action: 'resizePrepared',
                    },
                    originalEvent.origin
                );
                break;
        }
    }
};

const Article = (props) => {
    const { dispatch, state: formState } = useForm();
    const { articleSetup, uploadUrl, intl } = props;

    let title,
        content = '';
    if (typeof articleSetup.article === 'object') {
        title = articleSetup.article.title;
        content = articleSetup.article.content;
    }

    const getFormState = (isDraft) => ({
        ...formState,
        isDraft,
    });

    const save = (isDraft) => {
        try {
            return {
                values: getFormState(isDraft),
                isValid: true,
            };
        } catch (error) {
            return {
                errorMessages: [error],
                isValid: false,
            };
        }
    };

    return (
        <EditorContainer sidebar={<Sidebar onSave={save} />}>
            <TextField
                label={intl.formatMessage({ id: 'ARTICLE.TITLE' })}
                type="text"
                value={title}
                onChange={(event) =>
                    dispatch({
                        type: FormActions.setTitle,
                        payload: { title: event.target.value },
                    })
                }
                placeholder={intl.formatMessage({
                    id: 'ARTICLE.TITLEPLACEHOLDER',
                })}
                variant="outlined"
                fullWidth
            />
            <div style={{margin: '10px 0'}}>
                <FormLabel>{intl.formatMessage({ id: 'ARTICLE.CONTENT' })}</FormLabel>
                <CKEditor
                    initData={content}
                    onChange={(e) =>
                        dispatch({
                            type: FormActions.setContent,
                            payload: { content: e.editor.getData() },
                        })
                    }
                    onNamespaceLoaded={(CKEDITOR) => {
                        if (
                            typeof articleSetup.editor.wirisPath !== 'undefined'
                        ) {
                            // Add wiris plugin
                            CKEDITOR.plugins.addExternal(
                                'ckeditor_wiris',
                                articleSetup.editor.wirisPath
                            );
                        }

                        if (
                            articleSetup.hasOwnProperty('editor') &&
                            articleSetup.editor.hasOwnProperty(
                                'extraAllowedContent'
                            )
                        ) {
                            if (
                                CKEDITOR.hasOwnProperty(
                                    'extraAllowedContent'
                                ) !== true
                            ) {
                                CKEDITOR.config.extraAllowedContent = '';
                            }
                            CKEDITOR.config.extraAllowedContent +=
                                articleSetup.editor.extraAllowedContent;
                        }

                        if (
                            articleSetup.hasOwnProperty('editor') &&
                            articleSetup.editor.hasOwnProperty(
                                'editorBodyClass'
                            )
                        ) {
                            CKEDITOR.config.bodyClass =
                                articleSetup.editor.editorBodyClass;
                        }
                        CKEDITOR.config.uploadUrl = uploadUrl;
                        CKEDITOR.on('instanceReady', (event) => {
                            event.editor.window.on(
                                'message',
                                editorMessageHandler
                            );
                        });
                    }}
                />
            </div>
        </EditorContainer>
    );
};

Article.propTypes = {
    uploadUrl: PropTypes.string,
    articleSetup: PropTypes.object,
};

export default injectIntl(Article);
