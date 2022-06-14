import React, { useState } from 'react';
import EditorContainer from '../EditorContainer/EditorContainer';
import { QuestionContentType } from './index';
import { FormActions, useForm } from '../../contexts/FormContext';
import Sidebar from '../Sidebar';

const QuestionContentContainer = () => {
    const {
        state: { links, contentTypes, questionset },
        state: formState,
        dispatch,
    } = useForm();

    const [saveCallback, setSaveCallback] = useState();

    const submit = (isDraft) => {
        try {
            saveCallback({
                isValid: true,
                values: {
                    ...formState,
                    questionSetJsonData: JSON.stringify(
                        formState.questionSetJsonData
                    ),
                    isDraft,
                },
            });
        } catch (error) {
            saveCallback({
                errorMessages: [error],
                isValid: false,
            });
        }
    };

    const isFormDataReady = () => {
        const jsonParams = formState.questionSetJsonData;
        const questionsNotReady = jsonParams.cards.filter(
            (card) => !card.question.readyForSubmit
        );
        const answersNotReady = jsonParams.cards.filter(
            (card) =>
                card.answers.filter((answer) => !answer.readyForSubmit).length >
                0
        );

        return questionsNotReady.length === 0 && answersNotReady.length === 0;
    };

    const onSave = (isDraft = false) => {
        let attempts = 0;
        const loaderInterval = setInterval(() => {
            if (isFormDataReady() === true || attempts >= 20) {
                clearInterval(loaderInterval);
                submit(isDraft);
            }
            attempts++;
        }, 50);
    };

    return (
        <EditorContainer
            sidebar={
                <Sidebar
                    onSave={onSave}
                    onSaveCallback={(callback) =>
                        setSaveCallback(() => callback)
                    }
                />
            }
        >
            <QuestionContentType
                onChange={(data) =>
                    dispatch({
                        type: FormActions.setQuestionSetData,
                        payload: { content: data },
                    })
                }
                links={links}
                contentTypes={contentTypes}
                questionset={questionset}
                onSave={onSave}
            />
        </EditorContainer>
    );
};

export default QuestionContentContainer;
