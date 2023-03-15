import './QuestionContainer.scss';

import React from 'react';
import PropTypes from 'prop-types';
import TextField from '@material-ui/core/TextField';
import { FormattedMessage } from 'react-intl';

import { QuestionBankBrowser } from '../QuestionBankBrowser';
import TagsManager from '../../../TagsManager';
import LoadingModal from '../LoadingModal';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';

const QuestionContainerLayout = props => {
    const {
        cards,
        onTitleChange,
        title,
        onQuestionBankSelect,
        tags,
        onTagsChange,
        cardsComponents,
        displayDialog,
        loadingIcon,
        loadingText,
        loadingTitle,
        editMode,
        handleDragEnd,
        searchTitle,
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
                        shrink: true,
                    }}
                    margin="normal"
                    inputProps={{
                        onBlur: event => onTitleChange(event.currentTarget.value, false),
                    }}
                />
                <TagsManager
                    tags={tags}
                    onChange={onTagsChange}
                />
                <DragDropContext onDragEnd={handleDragEnd}>
                    <Droppable
                        droppableId="questionSetDropZone"
                    >
                        {(provided, snapshot) => (
                            <div
                                ref={provided.innerRef}
                                {...provided.droppableProps}
                            >
                                {cardsComponents}
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>
            </div>
            {typeof onQuestionBankSelect === 'function' && (
                <div>
                    <QuestionBankBrowser
                        onSelect={onQuestionBankSelect}
                        cards={cards}
                        title={searchTitle}
                        tags={tags}
                    />
                </div>
            )}
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
    cards: PropTypes.array,
    onTitleChange: PropTypes.func,
    title: PropTypes.string,
    searchTitle: PropTypes.string,
    onQuestionBankSelect: PropTypes.func,
    tags: PropTypes.array,
    onTagsChange: PropTypes.func,
    cardsComponents: PropTypes.array,
    displayDialog: PropTypes.bool,
    loadingTitle: PropTypes.string,
    loadingIcon: PropTypes.string,
    loadingText: PropTypes.oneOfType([PropTypes.string, PropTypes.object]),
    editMode: PropTypes.bool,
    handleDragEnd: PropTypes.func,
    placeholder: PropTypes.string,
};

export default QuestionContainerLayout;
