'use strict';

import React, { Component } from 'react';
import PropTypes from 'prop-types';

/**
 * @external "react-intl"
 * @see {@link https://github.com/yahoo/react-intl/wiki/API#injectintl|React Intl Wiki: injectIntl}
 */
import { FormattedMessage, injectIntl } from 'react-intl';
import HelpIcon from '../HelpIcon';

class LicenseText extends Component {
    static displayName = 'LicenseText';

    static propTypes = {
        license: PropTypes.string.isRequired
    };

    static defaultProps = {
        license: null
    };

    getLicenseString() {
        if (this.props.license) {
            return <FormattedMessage id={'LICENSE.' + this.props.license}/>;
        }
        return null;
    };

    render() {
        return (
            <div className={'license-text-container'}>
                {this.getLicenseString()}
                <HelpIcon messageString={
                    this.props.intl.formatMessage(
                        {
                            id: 'LICENSE.' + this.props.license + '.HELP',
                        }, {
                            p: chunks => <p>{chunks}</p>,
                            strong: chunks => <strong>{chunks}</strong>,
                        }
                    )
                }/>
            </div>
        );
    }
}

export default injectIntl(LicenseText);
