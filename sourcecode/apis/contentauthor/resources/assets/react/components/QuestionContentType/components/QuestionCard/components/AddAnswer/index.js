import React from 'react';
import PropTypes from 'prop-types';
import Button from '@material-ui/core/Button';
import AddCircle from '@material-ui/icons/AddCircle';
import { useIntl} from 'react-intl';

const AddAnswer = props => {
    const {
        onClick,
        icon = <AddCircle>add_circle</AddCircle>,
        label,
    } = props;

    const { formatMessage }  = useIntl();

    return (
        <div className="addAnswerContainer">
            <Button
                aria-label={formatMessage({ id: 'ANSWER.LABEL_ADD_ANSWER' })}
                onClick={onClick}
            >
                {icon}
            </Button>
        </div>
    );
};

AddAnswer.propTypes = {
    onClick: PropTypes.func.isRequired,
    icon: PropTypes.object,
    label: PropTypes.node,
};

export default AddAnswer;
