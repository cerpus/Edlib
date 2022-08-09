import React, {Component} from 'react';
import PropTypes from 'prop-types';
import PresentationLayout from './PresentationLayout';


class PresentationContainer extends Component {
    static propTypes = {
        onHandleChange: PropTypes.func,
        contentTypes: PropTypes.array,
    };

    static defaultProps = {
        onHandleChange: function () {
        },
        contentTypes: [],
    };

    constructor(props) {
        super(props);

        this.handleOpenDialog = this.handleOpenDialog.bind(this);
    }

    handleOpenDialog(presentation) {
        this.props.onHandleChange(presentation);
    }

    handleRenderIcon(iconType) {
        switch (iconType) {
            case 'H5P.QuestionSet':
                return <img src="/graphical/QuizIcon.png" width="226" height="226" alt="" aria-hidden="true" />;
            case 'CERPUS.MILLIONAIRE':
                return <img src="/graphical/MillionaireIcon.png" width="128" height="128" alt="" aria-hidden="true" />;
            default:
                return <i className="fa resourceicon-3x h5p-icon h5p-icon-Quiz" />;
        }
    }

    render() {
        const {contentTypes} = this.props;
        if (!Array.isArray(contentTypes) || contentTypes.length === 0) {
            return null;
        }
        return (
            <PresentationLayout
                actions={contentTypes}
                onDisplayToggle={this.handleOpenDialog}
                handleRenderIcon={this.handleRenderIcon}
            />
        );
    }
}

export default PresentationLayout;
export {
    PresentationContainer
};

export {messages as messagesEnGb} from './language/en-gb';
export {messages as messagesNbNo} from './language/nb-no';
