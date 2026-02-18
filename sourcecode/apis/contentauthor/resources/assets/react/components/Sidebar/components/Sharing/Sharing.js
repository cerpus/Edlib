import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import Switch from '@material-ui/core/Switch';

const Sharing = ({ onChange, shared }) => (
    <div className="sharinglayout-container">
        <FormattedMessage id="SHARINGCOMPONENT.SHOWINSHAREDCONTENT" />
        <Switch
            checked={shared}
            onChange={() => onChange(!shared)}
            color="primary"
        />
    </div>
);

Sharing.propTypes = {
    shared: PropTypes.bool,
    onChange: PropTypes.func,
};

export default Sharing;
