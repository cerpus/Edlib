import React from 'react';
import PropTypes from 'prop-types';
import WarningIcon from '@material-ui/icons/Warning';
import Fade from '@material-ui/core/Fade';
import Switch from '@material-ui/core/Switch';
import { useForm, FormActions } from '../../../../contexts/FormContext';

const Publish = ({ label, initialPublish = false }) => {
    const { dispatch, state: { isPublished = initialPublish } } = useForm();

    return (
        <div className="publish-box">
            <span>{label}</span>
            <Fade in={!isPublished}>
                <WarningIcon
                    htmlColor={'#fec63d'}
                    style={{ fontSize: 25 }}
                />
            </Fade>
            <Switch
                checked={isPublished}
                onChange={() => {
                    dispatch({ type: FormActions.setPublish, payload: { published: !isPublished } });
                }}
                color="primary"
            />
        </div>
    );
};

Publish.propTypes = {
    label: PropTypes.string,
    initialPublish: PropTypes.bool,
};

export default Publish;
