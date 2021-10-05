import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';

function PresentationLayout(props) {
    const {
        actions,
        onDisplayToggle,
        handleRenderIcon,
    } = props;

    return (
        <div className="selectPresentationContainer">
            <p>
                <FormattedMessage id="PRESENTATION.CHOOSE_A_PRESENTATION_FORM" />
            </p>
            <div className="presentationList">
                {actions.map(action => (
                    <div
                        key={action.label}
                        className="presentationButton"
                        onClick={() => onDisplayToggle({
                            presentation: action.outcome,
                            icon: action.img,
                            title: action.label,
                            text: <FormattedMessage id="PRESENTATION.WE_ARE_TWEAKING_YOUR_QUESTION_SET" />,
                        })}
                    >
                        {handleRenderIcon(action.outcome)}
                        <div className="presentationLabel">{action.label}</div>
                    </div>
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
