import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import Button from '@material-ui/core/Button';

function PresentationLayout(props) {
    const {
        actions,
        onDisplayToggle,
        handleRenderIcon,
    } = props;

    return (
        <div className="selectPresentationContainer">
            <h2><FormattedMessage id="PRESENTATION.CHOOSE_A_PRESENTATION_FORM" /></h2>
            <div className="presentationList">
                {actions.map(action => (
                    <Button
                        key={action.label}
                        className="presentationButton"
                        size="large"
                        onClick={() => onDisplayToggle({
                            presentation: action.outcome,
                            icon: action.img,
                            title: action.label,
                            text: <FormattedMessage id="PRESENTATION.WE_ARE_TWEAKING_YOUR_QUESTION_SET" />,
                        })}
                        classes={{
                            label: "presentationButtonLabel",
                        }}
                    >
                        {handleRenderIcon(action.outcome)}
                        <span className="presentationLabel">{action.label}</span>
                    </Button>
                ))}
            </div>
        </div>
    );
}

PresentationLayout.propTypes = {
    actions: PropTypes.array,
    onDisplayToggle: PropTypes.func,
    handleRenderIcon: PropTypes.func,
};

PresentationLayout.defaultProps = {
    actions: [],
};

export default PresentationLayout;
