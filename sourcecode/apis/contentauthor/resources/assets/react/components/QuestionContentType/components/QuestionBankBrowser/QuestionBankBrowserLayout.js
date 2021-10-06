import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';


function QuestionBankBrowserLayout(props) {
    const {questionSets, onSelect, previewDialog, status} = props;

    let statusIcon = '';
    switch(status){
        case 'error':
            statusIcon = <i className="material-icons">warning</i>;
            break;
        case 'fetching':
            statusIcon = <i className="material-icons">import_export</i>;
            break;
    }

    return (
        <div className="questionbankbrowser-container">
            <div className="questiobankbrowser-header">
                <FormattedMessage id="QUESTIONBANKBROWSER.MAIN_TITLE" />
                <div className="questiobankbrowser-header-icon">{statusIcon}</div>
            </div>
            <div className="questionbankbrowser-list">
                {questionSets.length === 0 && (
                    <div className="questionbankbrowser-question">
                        <FormattedMessage id="QUESTIONBANKBROWSER.ADD_TITLE_OR_TAGS_TO_SEARCH" />
                    </div>
                )}
                {questionSets.map((qSet) => {
                    return (
                        <span key={qSet.id} onClick={ () => onSelect(qSet.id) }>
                            <div className="questionbankbrowser-question">
                                { qSet.title }
                                <span className="questionbankbrowser-count">
                                    { qSet.numberOfQuestions } <FormattedMessage id="QUESTIONBANKBROWSER.NUM_QUESTIONS_POSTFIX" />
                                </span>
                            </div>
                            <hr className="questionbankbrowser-divider" />
                        </span>
                    );
                })}
            </div>
            {previewDialog}
        </div>
    );
}

QuestionBankBrowserLayout.propTypes = {
    questionSets: PropTypes.array,
    onSelect: PropTypes.func,
    previewDialog: PropTypes.node,
    status: PropTypes.string,
};

QuestionBankBrowserLayout.defaultProps = {
    questionSets: [],
    onSelect: null,
    previewDialog: null,
    status: 'success',
};

export default QuestionBankBrowserLayout;
