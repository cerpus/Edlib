import React from 'react';
import EditorContainer from '../EditorContainer/EditorContainer';
import Sidebar from '../Sidebar';
import EmbedContentTypeContainer from './EmbedContentTypeContainer';
import { FormActions, useForm } from '../../contexts/FormContext';

const EmbedContentType = () => {
    const {
        state: formState,
        dispatch,
    } = useForm();

    const onSave = () => {
        try {
            return {
                values: formState,
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
        <EditorContainer
            sidebar={(
                <Sidebar
                    onSave={onSave}
                />)}
        >
            <EmbedContentTypeContainer
                onChange={link => dispatch({ type: FormActions.setEmbed, payload: { link } })}
            />
        </EditorContainer>

    );
};

export default EmbedContentType;
