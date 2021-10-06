import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import ListedOption from './ListedOption';

const SharingContainer = props => {
    const {
        onChange,
        isPrivate,
    } = props;

    return (
        <Fragment>
            <ListedOption
                isPrivate={isPrivate}
                onToggle={() => onChange(!isPrivate)}
            />
        </Fragment>
    );
};

SharingContainer.propTypes = {
    isPrivate: PropTypes.bool,
    onChange: PropTypes.func,
};

export default SharingContainer;
