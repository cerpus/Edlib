import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import Switch from '@material-ui/core/Switch';

const ListedOption = ({ isPrivate, onToggle }) => {
    return (
        <div className="sharinglayout-container">
            <FormattedMessage id="SHARINGCOMPONENT.SHOWINSHAREDCONTENT" />
            <Switch
                checked={!isPrivate}
                onChange={onToggle}
                color="primary"
            />
        </div>
    );
};

ListedOption.propTypes = {
    isPrivate: PropTypes.bool,
    onToggle: PropTypes.func,
};

export default injectIntl(ListedOption);
