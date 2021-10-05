import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage } from 'react-intl';
import { Switch } from '@cerpus/ui';

const ListedOption = ({ isPrivate, onToggle }) => {
    return (
        <div className="sharinglayout-container">
            <FormattedMessage id="SHARINGCOMPONENT.SHOWINSHAREDCONTENT" />
            <Switch
                checked={!isPrivate}
                onToggle={onToggle}
                color={'tertiary'}
            />
        </div>
    );
};

ListedOption.propTypes = {
    isPrivate: PropTypes.bool,
    onToggle: PropTypes.func,
};

export default injectIntl(ListedOption);
