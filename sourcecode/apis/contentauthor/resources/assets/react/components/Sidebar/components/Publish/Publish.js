import React from 'react';
import PropTypes from 'prop-types';
import WarningIcon from '@material-ui/icons/Warning';
import { Fade } from '@material-ui/core';
import { Switch } from '@cerpus/ui';
import { useForm, FormActions } from 'contexts/FormContext';

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
                onToggle={() => {
                    dispatch({ type: FormActions.setPublish, payload: { published: !isPublished } });
                }}
                color={'tertiary'}
            />
        </div>
    );
};

Publish.propTypes = {
    label: PropTypes.string,
    initialPublish: PropTypes.bool,
};

export default Publish;
