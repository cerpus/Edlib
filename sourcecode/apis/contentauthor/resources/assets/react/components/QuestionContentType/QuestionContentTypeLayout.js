import React from 'react';
import PropTypes from 'prop-types';
import { ThemeProvider as MuiThemeProvider } from '@material-ui/core/styles';
import theme from './theme';

import { QuestionContainer } from './components';

const QuestionContentTypeLayout = props => {
    const {
        links,
        questions,
        onChange,
        title,
        tags,
        currentContainer,
        onReset,
        contentTypes,
        editMode,
        onSave,
    } = props;
    return (
        <MuiThemeProvider
            theme={theme}
        >
            <QuestionContainer
                links={links}
                questions={questions}
                onChange={onChange}
                title={title}
                tags={tags}
                currentContainer={currentContainer}
                onResetToOriginal={onReset}
                contentTypes={contentTypes}
                editMode={editMode}
                onSave={onSave}
            />
        </MuiThemeProvider>
    );
};

QuestionContentTypeLayout.propTypes = {
    links: PropTypes.object,
    onChange: PropTypes.func,
    title: PropTypes.string,
    questions: PropTypes.array,
    tags: PropTypes.array,
    currentContainer: PropTypes.string,
    onReset: PropTypes.func,
    onSave: PropTypes.func,
    contentTypes: PropTypes.array,
    editMode: PropTypes.bool,
};

export default QuestionContentTypeLayout;
