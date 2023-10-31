import './QuestionContainer.scss';

import React from 'react';
import PropTypes from 'prop-types';
import TextField from '@material-ui/core/TextField';
import { FormattedMessage } from 'react-intl';

import TagsManager from '../../../TagsManager';
import LoadingModal from '../LoadingModal';

const QuestionContainerLayout = props => {
    const {
        onTitleChange,
        title,
        tags,
        onTagsChange,
        cardsComponents,
        displayDialog,
        loadingIcon,
        loadingText,
        loadingTitle,
        placeholder,
    } = props;
    return (
        <div className="questionSetSurface">
            <div>
                <TextField
                    className="placeholder"
                    placeholder={placeholder}
                    label={<FormattedMessage id="QUESTIONCONTAINER.TITLE_LABEL" />}
                    fullWidth={true}
                    onChange={event => onTitleChange(event.currentTarget.value, true)}
                    value={title}
                    InputLabelProps={{
                        shrink: true
                    }}
                    margin="normal"
                    inputProps={{
                        onBlur: event => onTitleChange(event.currentTarget.value, false),
                        style: { fontSize: "2.3rem" }
                    }}
                />
                <TagsManager
                    tags={tags}
                    onChange={onTagsChange}
                />
                {cardsComponents}
            </div>
            <LoadingModal
                open={displayDialog}
                contentTitle={loadingTitle}
                contentIcon={loadingIcon}
                contentText={loadingText}
            />
        </div>
    );
};

QuestionContainerLayout.propTypes = {
    onTitleChange: PropTypes.func,
    title: PropTypes.string,
    tags: PropTypes.array,
    onTagsChange: PropTypes.func,
    cardsComponents: PropTypes.array,
    displayDialog: PropTypes.bool,
    loadingTitle: PropTypes.string,
    loadingIcon: PropTypes.string,
    loadingText: PropTypes.oneOfType([PropTypes.string, PropTypes.object]),
    placeholder: PropTypes.string,
};

export default QuestionContainerLayout;
